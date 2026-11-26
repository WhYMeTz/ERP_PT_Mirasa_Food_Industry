<?php

namespace App\Services\Gudang;

use App\Models\Gudang\DatPoDtl;
use App\Models\Gudang\DatPoHdr;
use App\Models\Gudang\DatTerimaDtl;
use App\Models\Gudang\DatTerimaHdr;
use App\Models\Gudang\DatTerimaRevisiLog;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstSupplier;
use App\Services\Common\CodeGeneratorService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TerimaBarangService
{
    public function __construct(
        protected StokService $stokService,
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Mengambil riwayat penerimaan barang dengan pagination.
     */
    public function getAllPaginated(int $perPage = 15, ?string $search = null, int|array|null $gudangId = null, bool $hasReject = false): LengthAwarePaginator
    {
        $query = DatTerimaHdr::with(['supplier', 'gudang', 'po', 'details.barang.satuanDasar', 'qc.details'])
            ->where('deleted_st', false);

        if (is_array($gudangId)) {
            $query->whereIn('gudang_id', $gudangId);
        } elseif ($gudangId !== null) {
            $query->where('gudang_id', $gudangId);
        }

        if ($hasReject) {
            $query->where(function ($q) {
                $q->whereHas('details', function ($dq) {
                    $dq->where('reject_qty', '>', 0);
                })->orWhereHas('qc.details', function ($qq) {
                    $qq->where('qty_reject', '>', 0);
                });
            });
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('terima_no', 'ILIKE', "%{$search}%")
                  ->orWhere('suratjalan_no', 'ILIKE', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('supplier_nm', 'ILIKE', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('terima_tgl', 'desc')
            ->orderBy('terima_id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Mengambil daftar per-item barang masuk sesuai Sheet "Barang Masuk" di Excel operasional:
     * Kolom: Tanggal | Kode Batch | Kode Barang | Nama Barang | Jenis | Keterangan | Qty Masuk | Satuan | Harga Satuan | Total Harga
     */
    public function getBarangMasukListPaginated(int $perPage = 25, ?string $search = null, int|array|null $gudangId = null, bool $hasReject = false): LengthAwarePaginator
    {
        $query = DatTerimaDtl::with(['header.supplier', 'header.gudang', 'header.po', 'header.qc.details', 'barang.jenisBarang', 'barang.satuanDasar'])
            ->whereHas('header', function ($q) use ($gudangId) {
                $q->where('deleted_st', false);
                if (is_array($gudangId)) {
                    $q->whereIn('gudang_id', $gudangId);
                } elseif ($gudangId !== null) {
                    $q->where('gudang_id', $gudangId);
                }
            });

        if ($hasReject) {
            $query->where(function ($q) {
                $q->where('reject_qty', '>', 0)
                  ->orWhereHas('header.qc.details', function ($qq) {
                      $qq->where('qty_reject', '>', 0);
                  });
            });
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('batch_no', 'ILIKE', "%{$search}%")
                  ->orWhere('catatan_txt', 'ILIKE', "%{$search}%")
                  ->orWhereHas('barang', function ($bq) use ($search) {
                      $bq->where('barang_nm', 'ILIKE', "%{$search}%")
                         ->orWhere('barang_cd', 'ILIKE', "%{$search}%");
                  })
                  ->orWhereHas('header', function ($hq) use ($search) {
                      $hq->where('terima_no', 'ILIKE', "%{$search}%")
                         ->orWhere('suratjalan_no', 'ILIKE', "%{$search}%")
                         ->orWhereHas('supplier', function ($sq) use ($search) {
                             $sq->where('supplier_nm', 'ILIKE', "%{$search}%");
                         });
                  });
            });
        }

        return $query->orderBy('terimadtl_id', 'desc')->paginate($perPage);
    }

    /**
     * Mengambil detail satu penerimaan barang beserta barang, batch, dan PO terkait.
     */
    public function getById(int $id): DatTerimaHdr
    {
        return DatTerimaHdr::with([
            'supplier',
            'gudang',
            'po',
            'qc.details',
            'details.barang.satuanDasar',
            'details.poDetail',
            'hutangDetails.header',
            'revisiLog',
        ])->where('terima_id', $id)->firstOrFail();
    }

    /**
     * Memproses penerimaan fisik barang ke gudang via DB Transaction:
     * 1. Simpan Header & Detail Penerimaan
     * 2. Suntik stok fisik & kartu stok via StokService::addStock()
     * 3. Update progres kuantitas diterima & status PO (jika ada PO).
     */
    public function store(array $data): DatTerimaHdr
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            if (empty($items)) {
                throw new Exception("Minimal harus ada 1 item barang yang diterima.");
            }

            $terimaNo = !empty($data['terima_no']) ? trim($data['terima_no']) : $this->codeGenerator->generateTerimaNo($data['terima_tgl'] ?? null);
            $gudangId = (int) $data['gudang_id'];
            $supplierId = (int) $data['supplier_id'];
            $poId = !empty($data['po_id']) ? (int) $data['po_id'] : null;

            // 1. Hitung akumulasi subtotal, diskon, potongan, dan PPN per item
            $subtotalNominal = 0;
            $totalSubtotalBruto = 0;
            $totalDiskonNominal = 0;
            $totalItemPotongan = 0;
            $totalDppPpn = 0;
            $totalDppNonPpn = 0;
            $totalPpnNominal = 0;
            $hasPpn = false;
            $hasNonPpn = false;
            $processedItems = [];

            foreach ($items as $item) {
                $terimaQty = max(0, (float) ($item['terima_qty'] ?? 0));
                $rejectQty = max(0, (float) ($item['reject_qty'] ?? 0));
                if ($terimaQty <= 0 && $rejectQty <= 0) {
                    continue; // Skip jika terima dan reject sama-sama 0
                }

                // Kuantitas Netto bersih yang lolos & masuk gudang
                $nettoQty = max(0, round($terimaQty - $rejectQty, 4));

                $barangId = (int) $item['barang_id'];
                $hargaNominal = (float) ($item['harga_nominal'] ?? 0);
                $diskonPersen = (float) ($item['diskon_persen'] ?? 0);
                $diskonNominalUnit = round($hargaNominal * ($diskonPersen / 100), 4);
                $hargaSetelahDiskon = max(0, $hargaNominal - $diskonNominalUnit);

                // Potongan khusus per-item (Rp)
                $potonganItem = max(0, (float) ($item['potongan_nominal'] ?? 0));
                
                // Subtotal Netto = Nilai Fisik Barang (Netto Qty * Harga Satuan PO)
                // Diskon komersial, potongan kas, dan pajak ditangani oleh Departemen Keuangan saat penerbitan tagihan
                $subtotalNetto = round($nettoQty * $hargaNominal, 4);
                // Harga perolehan unit fisik
                $hargaNetto = $hargaNominal;

                // Pajak PPN per item (Bisa NON_PPN atau PPN_11)
                $itemPpnTipe = ($item['ppn_tipe'] ?? 'NON_PPN') === 'PPN_11' ? 'PPN_11' : 'NON_PPN';
                $itemPpnPersen = ($itemPpnTipe === 'PPN_11') ? 11.00 : 0.00;
                $itemPpnNominal = 0.00;
                $subtotalTagihan = $subtotalNetto;

                if ($nettoQty > 0) {
                    if ($itemPpnTipe === 'PPN_11') {
                        $hasPpn = true;
                        $totalDppPpn += $subtotalNetto;
                    } else {
                        $hasNonPpn = true;
                        $totalDppNonPpn += $subtotalNetto;
                    }

                    $totalSubtotalBruto += round($nettoQty * $hargaNominal, 4);
                    $subtotalNominal += $subtotalNetto;
                    $totalDiskonNominal += round($nettoQty * $diskonNominalUnit, 4);
                    $totalItemPotongan += $potonganItem;
                    $totalPpnNominal += $itemPpnNominal;
                }

                $batchNo = !empty($item['batch_no']) ? trim($item['batch_no']) : null;
                if (empty($batchNo) && $terimaQty <= 0) {
                    $batchNo = 'REJECT-' . date('Ymd');
                }

                $barangObj = MstBarang::find($barangId);
                $itemGrade = !empty($item['grade_cd']) ? trim($item['grade_cd']) : ($barangObj?->hasGrade() ? 'A' : 'NON-GRADE');
                if ($barangObj && !$barangObj->hasGrade() && strtoupper($itemGrade) !== 'REJECT') {
                    $itemGrade = 'NON-GRADE';
                }

                $processedItems[] = [
                    'barang_id'        => $barangId,
                    'podtl_id'         => !empty($item['podtl_id']) ? (int) $item['podtl_id'] : null,
                    'qcdtl_id'         => !empty($item['qcdtl_id']) ? (int) $item['qcdtl_id'] : null,
                    'batch_no'         => $batchNo,
                    'expired_tgl'      => !empty($item['expired_tgl']) ? $item['expired_tgl'] : null,
                    'grade_cd'         => $itemGrade,
                    'kadar_air_persen' => !empty($item['kadar_air_persen']) ? (float) $item['kadar_air_persen'] : 0,
                    'refraksi_persen'  => !empty($item['refraksi_persen']) ? (float) $item['refraksi_persen'] : 0,
                    'terima_qty'       => $terimaQty,
                    'reject_qty'       => $rejectQty,
                    'harga_nominal'    => $hargaNominal,
                    'diskon_persen'    => $diskonPersen,
                    'diskon_nominal'   => $diskonNominalUnit,
                    'potongan_nominal' => $potonganItem,
                    'harga_netto'      => $hargaNetto,
                    'subtotal_netto'   => $subtotalNetto,
                    'ppn_tipe'         => $itemPpnTipe,
                    'ppn_persen'       => $itemPpnPersen,
                    'ppn_nominal'      => $itemPpnNominal,
                    'subtotal_tagihan' => $subtotalTagihan,
                    'catatan_txt'      => $item['catatan_txt'] ?? null,
                ];
            }

            if (empty($processedItems)) {
                throw new \Exception('Minimal harus ada 1 barang dengan kuantitas terima lebih dari 0 yang valid.');
            }

            // Potongan tambahan faktur global jika ada
            $potonganGlobal = max(0, (float) ($data['potongan_nominal'] ?? 0));
            $totalSemuaPotongan = $totalItemPotongan + $potonganGlobal;

            // Status PPN di Header: PPN_11 (jika semua PPN), NON_PPN (jika semua non PPN), atau MIXED (jika campuran)
            $headerPpnTipe = 'NON_PPN';
            if ($hasPpn && $hasNonPpn) {
                $headerPpnTipe = 'MIXED';
            } elseif ($hasPpn) {
                $headerPpnTipe = 'PPN_11';
            }

            $dppNominal = $totalSubtotalBruto;
            $totalTagihan = round($totalSubtotalBruto, 2);

            $terimaTglCarbon = !empty($data['terima_tgl']) ? \Carbon\Carbon::parse($data['terima_tgl']) : now();
            $supplierObj = MstSupplier::find($supplierId);
            $defaultTopHari = (int) ($supplierObj?->top_hari ?? 14);
            $tglJatuhTempoCalc = !empty($data['tgl_jatuh_tempo']) 
                ? $data['tgl_jatuh_tempo'] 
                : (clone $terimaTglCarbon)->addDays($defaultTopHari)->toDateString();

            // 2. Simpan Header Penerimaan
            $header = DatTerimaHdr::create([
                'terima_no'        => $terimaNo,
                'terima_tgl'       => $data['terima_tgl'] ?? date('Y-m-d'),
                'tgl_jatuh_tempo'  => $tglJatuhTempoCalc,
                'po_id'            => $poId,
                'qc_id'            => !empty($data['qc_id']) ? (int)$data['qc_id'] : null,
                'supplier_id'      => $supplierId,
                'gudang_id'        => $gudangId,
                'suratjalan_no'    => $data['suratjalan_no'] ?? null,
                'status_cd'        => 'COMPLETED',
                'catatan_txt'      => $data['catatan_txt'] ?? null,
                'subtotal_nominal' => $totalSubtotalBruto,
                'potongan_nominal' => 0,
                'dpp_nominal'      => $dppNominal,
                'ppn_tipe'         => $headerPpnTipe,
                'ppn_persen'       => $hasPpn ? 11.00 : 0.00,
                'ppn_nominal'      => 0.00,
                'total_tagihan'    => $totalTagihan,
            ]);

            // 3. Loop detail item & suntik stok
            foreach ($processedItems as $row) {
                $barangId = $row['barang_id'];
                $batchNo = $row['batch_no'];

                if ($row['terima_qty'] > 0) {
                    // Validasi nomor batch fisik supplier tidak boleh kosong atau hanya berupa prefix strip
                    if (empty($batchNo) || trim($batchNo) === '' || str_ends_with(trim($batchNo), '-')) {
                        $barang = MstBarang::find($barangId);
                        $barangNm = $barang?->barang_nm ?? "ID #{$barangId}";
                        throw new \Exception("Nomor batch dari faktur supplier untuk komoditas '{$barangNm}' wajib diisi lengkap.");
                    }
                } else {
                    $batchNo = $batchNo ?: ('REJECT-' . date('Ymd'));
                }

                // Simpan detail penerimaan (baik diterima maupun ditolak dicatat resmi pada dokumen GRN)
                $dtl = DatTerimaDtl::create([
                    'terima_id'        => $header->terima_id,
                    'podtl_id'         => $row['podtl_id'],
                    'qcdtl_id'         => $row['qcdtl_id'] ?? null,
                    'barang_id'        => $barangId,
                    'batch_no'         => $batchNo,
                    'expired_tgl'      => $row['expired_tgl'],
                    'terima_qty'       => $row['terima_qty'],
                    'reject_qty'       => $row['reject_qty'],
                    'grade_cd'         => $row['grade_cd'],
                    'kadar_air_persen' => $row['kadar_air_persen'] ?? 0,
                    'refraksi_persen'  => $row['refraksi_persen'] ?? 0,
                    'harga_nominal'    => $row['harga_nominal'],
                    'diskon_persen'    => $row['diskon_persen'],
                    'diskon_nominal'   => $row['diskon_nominal'],
                    'potongan_nominal' => $row['potongan_nominal'],
                    'harga_netto'      => $row['harga_netto'],
                    'subtotal_netto'   => $row['subtotal_netto'],
                    'ppn_tipe'         => $row['ppn_tipe'],
                    'ppn_persen'       => $row['ppn_persen'],
                    'ppn_nominal'      => $row['ppn_nominal'],
                    'subtotal_tagihan' => $row['subtotal_tagihan'],
                    'catatan_txt'      => $row['catatan_txt'],
                ]);

                // Suntik stok fisik & kartu stok HANYA jika kuantitas netto > 0 (barang afkir/reject tidak masuk stok)
                $nettoQtyRow = max(0, (float) $row['terima_qty'] - (float) ($row['reject_qty'] ?? 0));
                if ($nettoQtyRow > 0) {
                    $this->stokService->addStock(
                        $gudangId,
                        $barangId,
                        $batchNo,
                        $nettoQtyRow,
                        $row['expired_tgl'],
                        $terimaNo,
                        "Penerimaan Barang Fisik No {$terimaNo}",
                        $row['harga_netto'],
                        $row['grade_cd'] ?: 'NON-GRADE'
                    );

                    // Update terima_qty di PO Detail jika barang ini terkait PO
                    if (!empty($dtl->podtl_id)) {
                        $poDtl = DatPoDtl::find($dtl->podtl_id);
                        if ($poDtl) {
                            $poDtl->terima_qty = (float) $poDtl->terima_qty + $nettoQtyRow;
                            $poDtl->save();
                        }
                    }

                    // Update harga beli acuan di Master Barang dengan harga terbaru
                    if ($row['harga_netto'] > 0) {
                        $barang = MstBarang::find($barangId);
                        if ($barang && (float) $barang->harga_beli_standar != $row['harga_netto']) {
                            $barang->harga_beli_standar = $row['harga_netto'];
                            $barang->save();
                        }
                    }
                }
            }

            // 3. Update status PO jika terkait
            if ($poId) {
                $poHdr = DatPoHdr::with('details')->find($poId);
                if ($poHdr) {
                    $semuaTuntas = true;
                    $adaYangDiterima = false;

                    foreach ($poHdr->details as $pdtl) {
                        if ((float) $pdtl->terima_qty < (float) $pdtl->pesan_qty) {
                            $semuaTuntas = false;
                        }
                        if ((float) $pdtl->terima_qty > 0) {
                            $adaYangDiterima = true;
                        }
                    }

                    if ($semuaTuntas) {
                        $poHdr->status_cd = 'COMPLETED';
                    } elseif ($adaYangDiterima) {
                        $poHdr->status_cd = 'PARTIAL';
                    }
                    $poHdr->save();
                }
            }

            // 4. Sinkronisasi status dan detail kuantitas tiket QC jika penerimaan berasal dari tiket QC
            if (!empty($data['qc_id'])) {
                app(\App\Services\Gudang\QcInboundService::class)->syncFromTerimaBarang(
                    (int) $data['qc_id'],
                    $header,
                    $items
                );
            }

            // 5. Faktur Tagihan Hutang (AP):
            // Berdasarkan alur bisnis Opsi 1 (Pemisahan Operasional Gudang & Keuangan),
            // penerimaan fisik barang (GRN) ini tidak langsung auto-generate hutang,
            // melainkan masuk ke antrean 'Unbilled GRN' untuk diverifikasi oleh staf Keuangan
            // dengan mencocokkan dokumen fisik dan menginput nomor faktur vendor asli.

            return $header->fresh(['details.barang']);
        });
    }

    /**
     * Memeriksa status pemakaian stok fisik dari dokumen penerimaan barang:
     * - Apakah batch barang penerimaan ini telah digunakan di SPK/Produksi/Mutasi Keluar?
     * - Apakah sisa stok di gudang masih mencukupi untuk dilakukan rollback?
     * - Apakah dokumen penerimaan aman untuk diedit / dibatalkan?
     */
    public function checkStockUsage(int|DatTerimaHdr $terima): array
    {
        if (is_numeric($terima)) {
            $terima = DatTerimaHdr::with(['details.barang.satuanDasar', 'gudang'])->findOrFail($terima);
        } elseif (!$terima->relationLoaded('details')) {
            $terima->load(['details.barang.satuanDasar', 'gudang']);
        }

        $isUsed = false;
        $canBeEdited = true;
        $canBeDeleted = true;
        $items = [];
        $reasons = [];
        $totalTerimaNetto = 0;
        $totalSisaNetto = 0;
        $totalUsedQty = 0;

        foreach ($terima->details as $dtl) {
            $nettoQty = max(0, (float) $dtl->terima_qty - (float) ($dtl->reject_qty ?? 0));
            $totalTerimaNetto += $nettoQty;

            if ($nettoQty <= 0) {
                continue;
            }

            $batchNo = $dtl->batch_no;
            $gradeCd = !empty($dtl->grade_cd) ? strtoupper(trim($dtl->grade_cd)) : null;

            // 1. Ambil stok batch saat ini di gudang
            $stokBatch = \App\Models\Gudang\DatStokBatch::where('gudang_id', $terima->gudang_id)
                ->where('barang_id', $dtl->barang_id)
                ->where('batch_no', $batchNo)
                ->when(!empty($gradeCd), fn($q) => $q->where('grade_cd', $gradeCd))
                ->first();

            $sisaQty = $stokBatch ? (float) $stokBatch->sisa_qty : 0;
            $totalSisaNetto += $sisaQty;

            // 2. Ambil catatan mutasi OUT nyata dari kartu stok (BPPB Produksi, SPK, Packaging, Retur, Pengeluaran)
            $outLedgers = \App\Models\Gudang\DatStokLedger::where('gudang_id', $terima->gudang_id)
                ->where('barang_id', $dtl->barang_id)
                ->where('batch_no', $batchNo)
                ->when(!empty($gradeCd), fn($q) => $q->where('grade_cd', $gradeCd))
                ->where('tipe_transaksi_cd', 'OUT')
                ->where('keterangan_txt', 'NOT ILIKE', '%Rollback Edit%')
                ->where('keterangan_txt', 'NOT ILIKE', '%Pembatalan%')
                ->where('dokumen_no', 'NOT ILIKE', '%/GRN/%')
                ->orderBy('transaksi_tgl', 'desc')
                ->get();

            $ledgerUsedQty = (float) $outLedgers->sum('qty');
            $usedQty = $ledgerUsedQty;
            if ($sisaQty < $nettoQty && $usedQty <= 0) {
                $usedQty = round($nettoQty - $sisaQty, 4);
            }
            $totalUsedQty += $usedQty;

            $barangNm = $dtl->barang?->barang_nm ?? "Barang #{$dtl->barang_id}";
            $satuanNm = $dtl->barang?->satuanDasar?->satuan_nm ?? 'KG';

            $itemLocked = false;
            // Jika ada pemakaian pada batch ini (> 0) atau sisa batch fisik di bawah netto masuk
            if ($usedQty > 0 || $sisaQty < $nettoQty) {
                $itemLocked = true;
                $isUsed = true;
                $canBeEdited = false;
                $canBeDeleted = false;

                $docRefs = $outLedgers->pluck('dokumen_no')->filter()->unique()->take(3)->implode(', ');
                $refInfo = $docRefs ? " (Ref: {$docRefs})" : "";
                $formattedUsed = number_format($usedQty, 2, ',', '.');
                $reasons[] = "Stok pada Batch '{$batchNo}' ({$barangNm}) sudah terpakai sebanyak {$formattedUsed} {$satuanNm}{$refInfo}. Dokumen penerimaan ini tidak dapat diubah.";
            }

            $items[] = [
                'barang_id'     => $dtl->barang_id,
                'barang_nm'     => $barangNm,
                'satuan_nm'     => $satuanNm,
                'batch_no'      => $batchNo,
                'grade_cd'      => $gradeCd ?? 'NON-GRADE',
                'terima_netto'  => $nettoQty,
                'sisa_qty'      => $sisaQty,
                'used_qty'      => $usedQty,
                'is_locked'     => $itemLocked,
                'usage_logs'    => $outLedgers->map(fn($l) => [
                    'dokumen_no'     => $l->dokumen_no,
                    'qty'            => (float) $l->qty,
                    'transaksi_tgl'  => $l->transaksi_tgl ? $l->transaksi_tgl->format('d/m/Y H:i') : '-',
                    'keterangan_txt' => $l->keterangan_txt,
                ])->toArray(),
            ];
        }

        // 3. Cek tagihan hutang (AP) terkait
        $hasPaidHutang = false;
        $hutangDtls = \App\Models\Keuangan\DatHutangDtl::where('deleted_st', false)
            ->where('terima_id', $terima->terima_id)
            ->get();

        foreach ($hutangDtls as $hDtl) {
            $hHdr = \App\Models\Keuangan\DatHutangHdr::find($hDtl->hutang_id);
            if ($hHdr && (float) $hHdr->total_dibayar > 0) {
                $hasPaidHutang = true;
                $canBeDeleted = false;
                $reasons[] = "Dokumen penerimaan terkait dengan Faktur Hutang {$hHdr->hutang_no} yang telah memiliki riwayat pembayaran di Keuangan.";
            }
        }

        return [
            'is_used'            => $isUsed,
            'can_be_edited'      => $canBeEdited,
            'can_be_deleted'     => $canBeDeleted,
            'total_terima_netto' => $totalTerimaNetto,
            'total_sisa_netto'   => $totalSisaNetto,
            'total_used_qty'     => $totalUsedQty,
            'has_paid_hutang'    => $hasPaidHutang,
            'items'              => $items,
            'reasons'            => $reasons,
        ];
    }

    /**
     * Memeriksa apakah payload edit memiliki perubahan nyata terhadap data penerimaan tersimpan.
     * Mencegah pemrosesan ulang stok, mutasi kartu stok, dan pencatatan log revisi jika tidak ada data yang berubah.
     */
    public function hasTerimaChanges(DatTerimaHdr $terima, array $data): bool
    {
        // 1. Cek Header Fields
        if (!empty($data['terima_tgl'])) {
            $existingDate = $terima->terima_tgl ? $terima->terima_tgl->format('Y-m-d') : null;
            $newDate = date('Y-m-d', strtotime($data['terima_tgl']));
            if ($existingDate !== $newDate) {
                return true;
            }
        }

        if (array_key_exists('suratjalan_no', $data)) {
            if (trim((string) $data['suratjalan_no']) !== trim((string) $terima->suratjalan_no)) {
                return true;
            }
        }

        if (array_key_exists('tgl_jatuh_tempo', $data)) {
            $existingTempo = $terima->tgl_jatuh_tempo ? $terima->tgl_jatuh_tempo->format('Y-m-d') : null;
            $newTempo = !empty($data['tgl_jatuh_tempo']) ? date('Y-m-d', strtotime($data['tgl_jatuh_tempo'])) : null;
            if ($existingTempo !== $newTempo) {
                return true;
            }
        }

        if (!empty($data['supplier_id']) && (int) $data['supplier_id'] !== (int) $terima->supplier_id) {
            $oldSupNm = $terima->supplier?->supplier_nm;
            $newSupNm = \App\Models\Master\MstSupplier::find($data['supplier_id'])?->supplier_nm;
            if (!$oldSupNm || !$newSupNm || trim(strtolower($oldSupNm)) !== trim(strtolower($newSupNm))) {
                return true;
            }
        }

        if (!empty($data['gudang_id']) && (int) $data['gudang_id'] !== (int) $terima->gudang_id) {
            return true;
        }

        if (array_key_exists('catatan_txt', $data)) {
            if (trim((string) $data['catatan_txt']) !== trim((string) $terima->catatan_txt)) {
                return true;
            }
        }

        // 2. Cek Item Rows
        $items = $data['items'] ?? [];
        if (count($items) !== $terima->details->count()) {
            return true;
        }

        $existingDetails = $terima->details->values();
        foreach ($items as $idx => $item) {
            $oldDtl = $existingDetails->get($idx);
            if (!$oldDtl) {
                return true;
            }

            if ((int) $item['barang_id'] !== (int) $oldDtl->barang_id) {
                return true;
            }

            $newTerimaQty = max(0, (float) ($item['terima_qty'] ?? 0));
            if (abs($newTerimaQty - (float) $oldDtl->terima_qty) > 0.0001) {
                return true;
            }

            $newRejectQty = max(0, (float) ($item['reject_qty'] ?? 0));
            if (abs($newRejectQty - (float) ($oldDtl->reject_qty ?? 0)) > 0.0001) {
                return true;
            }

            if (array_key_exists('batch_no', $item) && trim((string) $item['batch_no']) !== trim((string) $oldDtl->batch_no)) {
                return true;
            }

            if (array_key_exists('grade_cd', $item) && trim((string) $item['grade_cd']) !== trim((string) $oldDtl->grade_cd)) {
                return true;
            }

            if (array_key_exists('expired_tgl', $item)) {
                $oldExp = $oldDtl->expired_tgl ? date('Y-m-d', strtotime($oldDtl->expired_tgl)) : null;
                $newExp = !empty($item['expired_tgl']) ? date('Y-m-d', strtotime($item['expired_tgl'])) : null;
                if ($oldExp !== $newExp) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Membuat snapshot array lengkap data penerimaan sebelum/sesudah perubahan.
     */
    protected function buildTerimaSnapshot(DatTerimaHdr $terima): array
    {
        return [
            'terima_no'        => $terima->terima_no,
            'terima_tgl'       => $terima->terima_tgl ? $terima->terima_tgl->format('Y-m-d') : null,
            'suratjalan_no'    => $terima->suratjalan_no,
            'tgl_jatuh_tempo'  => $terima->tgl_jatuh_tempo ? $terima->tgl_jatuh_tempo->format('Y-m-d') : null,
            'supplier_id'      => $terima->supplier_id,
            'supplier_nm'      => $terima->supplier?->supplier_nm,
            'gudang_id'        => $terima->gudang_id,
            'gudang_nm'        => $terima->gudang?->gudang_nm,
            'catatan_txt'      => $terima->catatan_txt,
            'items'            => $terima->details->map(function ($d) {
                return [
                    'terimadtl_id'  => $d->terimadtl_id,
                    'barang_id'     => $d->barang_id,
                    'barang_cd'     => $d->barang?->barang_cd,
                    'barang_nm'     => $d->barang?->barang_nm,
                    'satuan_nm'     => $d->barang?->satuanDasar?->satuan_nm ?? ($d->barang?->satuanDasar?->satuan_cd ?? 'Unit'),
                    'batch_no'      => $d->batch_no,
                    'grade_cd'      => $d->grade_cd,
                    'expired_tgl'   => $d->expired_tgl ? date('Y-m-d', strtotime($d->expired_tgl)) : null,
                    'terima_qty'    => (float) $d->terima_qty,
                    'reject_qty'    => (float) ($d->reject_qty ?? 0),
                    'netto_qty'     => max(0, (float) $d->terima_qty - (float) ($d->reject_qty ?? 0)),
                    'harga_nominal' => (float) $d->harga_nominal,
                ];
            })->values()->toArray(),
        ];
    }

    /**
     * Menghitung rincian komparasi sebelum vs sesudah (Diffs).
     * Murni mencatat perubahan fisik operasional logistik (Bebas dari unsur finansial / tagihan).
     */
    protected function computeTerimaDiff(array $before, array $after): array
    {
        $diffs = [];
        $summaryParts = [];

        // 1. Header Fields
        $headerFields = [
            'terima_tgl'      => 'Tanggal Masuk Fisik',
            'suratjalan_no'   => 'Nomor Surat Jalan (SJ)',
            'tgl_jatuh_tempo' => 'Jatuh Tempo Tagihan',
            'supplier_id'     => 'Supplier Pengirim',
            'gudang_id'       => 'Gudang Masuk',
            'catatan_txt'     => 'Keterangan Armada / Catatan',
        ];

        foreach ($headerFields as $field => $label) {
            $oldVal = $before[$field] ?? null;
            $newVal = $after[$field] ?? null;

            if ($field === 'supplier_id' && (int)$oldVal !== (int)$newVal) {
                $oldName = $before['supplier_nm'] ?? "ID {$oldVal}";
                $newName = $after['supplier_nm'] ?? "ID {$newVal}";
                $diffs[] = [
                    'field'   => $field,
                    'label'   => $label,
                    'sebelum' => $oldName,
                    'sesudah' => $newName,
                    'tipe'    => 'HEADER',
                ];
                $summaryParts[] = "Supplier diubah dari '{$oldName}' menjadi '{$newName}'";
            } elseif ($field === 'gudang_id' && (int)$oldVal !== (int)$newVal) {
                $oldName = $before['gudang_nm'] ?? "ID {$oldVal}";
                $newName = $after['gudang_nm'] ?? "ID {$newVal}";
                $diffs[] = [
                    'field'   => $field,
                    'label'   => $label,
                    'sebelum' => $oldName,
                    'sesudah' => $newName,
                    'tipe'    => 'HEADER',
                ];
                $summaryParts[] = "Gudang tujuan diubah dari '{$oldName}' menjadi '{$newName}'";
            } elseif (!in_array($field, ['supplier_id', 'gudang_id']) && trim((string)$oldVal) !== trim((string)$newVal)) {
                $diffs[] = [
                    'field'   => $field,
                    'label'   => $label,
                    'sebelum' => (string) ($oldVal ?: '-'),
                    'sesudah' => (string) ($newVal ?: '-'),
                    'tipe'    => 'HEADER',
                ];
                $summaryParts[] = "{$label} diubah: '" . ($oldVal ?: '-') . "' ➔ '" . ($newVal ?: '-') . "'";
            }
        }

        // 2. Item Rows
        $beforeItems = collect($before['items'] ?? []);
        $afterItems = collect($after['items'] ?? []);

        $maxRows = max($beforeItems->count(), $afterItems->count());
        for ($i = 0; $i < $maxRows; $i++) {
            $b = $beforeItems->get($i);
            $a = $afterItems->get($i);

            if (!$b && $a) {
                $bNm = $a['barang_nm'] ?? "Barang #{$a['barang_id']}";
                $qtyFmt = number_format((float)$a['terima_qty'], 2, ',', '.') . ' ' . ($a['satuan_nm'] ?? 'KG');
                $diffs[] = [
                    'field'   => 'item_tambah',
                    'label'   => "Tambah Item: {$bNm}",
                    'sebelum' => '-',
                    'sesudah' => "Qty: {$qtyFmt} (Batch: {$a['batch_no']})",
                    'tipe'    => 'ITEM_TAMBAH',
                ];
                $summaryParts[] = "Menambahkan barang baru '{$bNm}' ({$qtyFmt})";
            } elseif ($b && !$a) {
                $bNm = $b['barang_nm'] ?? "Barang #{$b['barang_id']}";
                $qtyFmt = number_format((float)$b['terima_qty'], 2, ',', '.') . ' ' . ($b['satuan_nm'] ?? 'KG');
                $diffs[] = [
                    'field'   => 'item_hapus',
                    'label'   => "Hapus Item: {$bNm}",
                    'sebelum' => "Qty: {$qtyFmt} (Batch: {$b['batch_no']})",
                    'sesudah' => '-',
                    'tipe'    => 'ITEM_HAPUS',
                ];
                $summaryParts[] = "Menghapus barang '{$bNm}'";
            } elseif ($b && $a) {
                $bNm = $a['barang_nm'] ?? ($b['barang_nm'] ?? "Barang #{$a['barang_id']}");
                $satuan = $a['satuan_nm'] ?? ($b['satuan_nm'] ?? 'KG');

                if ((int)$b['barang_id'] !== (int)$a['barang_id']) {
                    $diffs[] = [
                        'field'   => "item_{$i}_barang",
                        'label'   => "Barang Baris #".($i+1),
                        'sebelum' => $b['barang_nm'] ?? "ID #{$b['barang_id']}",
                        'sesudah' => $a['barang_nm'] ?? "ID #{$a['barang_id']}",
                        'tipe'    => 'ITEM_TAMBAH',
                    ];
                    $summaryParts[] = "Ganti barang baris #".($i+1)." menjadi '{$a['barang_nm']}'";
                }

                if (abs((float)$b['terima_qty'] - (float)$a['terima_qty']) > 0.0001) {
                    $oldQty = number_format((float)$b['terima_qty'], 2, ',', '.') . " {$satuan}";
                    $newQty = number_format((float)$a['terima_qty'], 2, ',', '.') . " {$satuan}";
                    $diffs[] = [
                        'field'   => "item_{$i}_terima_qty",
                        'label'   => "Qty Terima: {$bNm}",
                        'sebelum' => $oldQty,
                        'sesudah' => $newQty,
                        'tipe'    => 'ITEM_QTY',
                    ];
                    $summaryParts[] = "Qty terima {$bNm}: {$oldQty} ➔ {$newQty}";
                }

                if (abs((float)$b['reject_qty'] - (float)$a['reject_qty']) > 0.0001) {
                    $oldRej = number_format((float)$b['reject_qty'], 2, ',', '.') . " {$satuan}";
                    $newRej = number_format((float)$a['reject_qty'], 2, ',', '.') . " {$satuan}";
                    $diffs[] = [
                        'field'   => "item_{$i}_reject_qty",
                        'label'   => "Qty Reject/Afkir: {$bNm}",
                        'sebelum' => $oldRej,
                        'sesudah' => $newRej,
                        'tipe'    => 'ITEM_QTY',
                    ];
                    $summaryParts[] = "Qty reject {$bNm}: {$oldRej} ➔ {$newRej}";
                }

                if (trim((string)$b['batch_no']) !== trim((string)$a['batch_no'])) {
                    $diffs[] = [
                        'field'   => "item_{$i}_batch",
                        'label'   => "Batch No: {$bNm}",
                        'sebelum' => $b['batch_no'] ?: '-',
                        'sesudah' => $a['batch_no'] ?: '-',
                        'tipe'    => 'HEADER',
                    ];
                    $summaryParts[] = "Batch {$bNm}: '{$b['batch_no']}' ➔ '{$a['batch_no']}'";
                }

                if (trim((string)$b['grade_cd']) !== trim((string)$a['grade_cd'])) {
                    $diffs[] = [
                        'field'   => "item_{$i}_grade",
                        'label'   => "Grade: {$bNm}",
                        'sebelum' => $b['grade_cd'] ?: '-',
                        'sesudah' => $a['grade_cd'] ?: '-',
                        'tipe'    => 'HEADER',
                    ];
                    $summaryParts[] = "Grade {$bNm}: '{$b['grade_cd']}' ➔ '{$a['grade_cd']}'";
                }
            }
        }

        if (empty($diffs)) {
            $diffs[] = [
                'field'   => 'terima_update',
                'label'   => 'Pembaruan Data Penerimaan',
                'sebelum' => 'Versi Sebelumnya',
                'sesudah' => 'Diperbarui',
                'tipe'    => 'HEADER',
            ];
            $summaryParts[] = 'Pembaruan data penerimaan barang berhasil disimpan';
        }

        return [
            'diffs'        => $diffs,
            'summaryParts' => $summaryParts,
        ];
    }

    /**
     * Memperbarui dokumen penerimaan barang:
     * 1. Validasi pemakaian stok (Smart Guard)
     * 2. Rollback stok lama & PO progress lama
     * 3. Hapus detail lama
     * 4. Update header & buat detail baru
     * 5. Suntik stok baru & update PO progress baru
     */
    public function update(int $id, array $data): DatTerimaHdr
    {
        return DB::transaction(function () use ($id, $data) {
            $terima = DatTerimaHdr::with(['supplier', 'gudang', 'details.barang.satuanDasar', 'po.details'])->lockForUpdate()->findOrFail($id);

            if ($terima->deleted_st) {
                throw new Exception("Dokumen penerimaan {$terima->terima_no} sudah dihapus dan tidak dapat diubah.");
            }

            // 0. Validasi status pemakaian stok (Smart Guard)
            $usage = $this->checkStockUsage($terima);
            if (!$usage['can_be_edited']) {
                $reasonText = !empty($usage['reasons']) ? implode(' ', $usage['reasons']) : 'Bahan baku telah digunakan di lini produksi.';
                throw new Exception("Dokumen penerimaan {$terima->terima_no} terkunci dan tidak dapat diubah: {$reasonText} Gunakan formulir Penyesuaian Stok (Stock Adjustment) untuk mengoreksi selisih fisik riil.");
            }

            // Proteksi: Jika tidak ada perubahan data nyata sama sekali, jangan ubah database & jangan catat revisi log
            if (!$this->hasTerimaChanges($terima, $data)) {
                $terimaFresh = $terima->fresh(['supplier', 'gudang', 'details.barang.satuanDasar', 'revisiLog']);
                $terimaFresh->was_recently_changed = false;
                return $terimaFresh;
            }

            // Ambil Snapshot SEBELUM pengubahan
            $beforeSnapshot = $this->buildTerimaSnapshot($terima);

            // 1. Rollback stok lama untuk setiap item detail
            foreach ($terima->details as $dtl) {
                $oldNetto = max(0, (float) $dtl->terima_qty - (float) ($dtl->reject_qty ?? 0));
                if ($oldNetto > 0) {
                    $this->stokService->deductStock(
                        $terima->gudang_id,
                        $dtl->barang_id,
                        $dtl->batch_no,
                        $oldNetto,
                        $terima->terima_no,
                        "Penyesuaian (Rollback Edit) Dokumen Penerimaan {$terima->terima_no}",
                        $dtl->grade_cd ?? 'A'
                    );

                    // Kembalikan terima_qty di PO Detail jika terkait PO lama
                    if (!empty($dtl->podtl_id)) {
                        $poDtl = DatPoDtl::find($dtl->podtl_id);
                        if ($poDtl) {
                            $poDtl->terima_qty = max(0, (float) $poDtl->terima_qty - $oldNetto);
                            $poDtl->save();
                        }
                    }
                }
            }

            // Hapus detail lama
            DatTerimaDtl::where('terima_id', $terima->terima_id)->delete();

            $items = $data['items'] ?? [];
            if (empty($items)) {
                throw new Exception("Minimal harus ada 1 item barang yang diterima.");
            }

            $gudangId = (int) ($data['gudang_id'] ?? $terima->gudang_id);
            $supplierId = (int) ($data['supplier_id'] ?? $terima->supplier_id);
            $poId = !empty($data['po_id']) ? (int) $data['po_id'] : null;

            // Hitung akumulasi subtotal, diskon, potongan, dan PPN per item
            $subtotalNominal = 0;
            $totalSubtotalBruto = 0;
            $totalDiskonNominal = 0;
            $totalItemPotongan = 0;
            $totalDppPpn = 0;
            $totalDppNonPpn = 0;
            $totalPpnNominal = 0;
            $hasPpn = false;
            $hasNonPpn = false;
            $processedItems = [];

            foreach ($items as $item) {
                $terimaQty = max(0, (float) ($item['terima_qty'] ?? 0));
                $rejectQty = max(0, (float) ($item['reject_qty'] ?? 0));
                if ($terimaQty <= 0 && $rejectQty <= 0) {
                    continue;
                }

                // Kuantitas Netto bersih yang lolos & masuk gudang
                $nettoQty = max(0, round($terimaQty - $rejectQty, 4));

                $barangId = (int) $item['barang_id'];
                $hargaNominal = (float) ($item['harga_nominal'] ?? 0);
                $diskonPersen = (float) ($item['diskon_persen'] ?? 0);
                $diskonNominalUnit = round($hargaNominal * ($diskonPersen / 100), 4);
                $hargaSetelahDiskon = max(0, $hargaNominal - $diskonNominalUnit);

                $potonganItem = max(0, (float) ($item['potongan_nominal'] ?? 0));
                // Subtotal Netto = Nilai Fisik Barang (Netto Qty * Harga Satuan PO)
                $subtotalNetto = round($nettoQty * $hargaNominal, 4);
                $hargaNetto = $hargaNominal;

                $itemPpnTipe = ($item['ppn_tipe'] ?? 'NON_PPN') === 'PPN_11' ? 'PPN_11' : 'NON_PPN';
                $itemPpnPersen = ($itemPpnTipe === 'PPN_11') ? 11.00 : 0.00;
                $itemPpnNominal = 0.00;
                $subtotalTagihan = $subtotalNetto;

                if ($nettoQty > 0) {
                    if ($itemPpnTipe === 'PPN_11') {
                        $hasPpn = true;
                        $totalDppPpn += $subtotalNetto;
                    } else {
                        $hasNonPpn = true;
                        $totalDppNonPpn += $subtotalNetto;
                    }

                    $totalSubtotalBruto += round($nettoQty * $hargaNominal, 4);
                    $subtotalNominal += $subtotalNetto;
                    $totalDiskonNominal += round($nettoQty * $diskonNominalUnit, 4);
                    $totalItemPotongan += $potonganItem;
                    $totalPpnNominal += $itemPpnNominal;
                }

                $batchNo = !empty($item['batch_no']) ? trim($item['batch_no']) : null;
                if (empty($batchNo) && $terimaQty <= 0) {
                    $batchNo = 'REJECT-' . date('Ymd');
                }

                $barangObj = MstBarang::find($barangId);
                $itemGrade = !empty($item['grade_cd']) ? trim($item['grade_cd']) : ($barangObj?->hasGrade() ? 'A' : 'NON-GRADE');
                if ($barangObj && !$barangObj->hasGrade() && strtoupper($itemGrade) !== 'REJECT') {
                    $itemGrade = 'NON-GRADE';
                }

                $processedItems[] = [
                    'barang_id'        => $barangId,
                    'podtl_id'         => !empty($item['podtl_id']) ? (int) $item['podtl_id'] : null,
                    'qcdtl_id'         => !empty($item['qcdtl_id']) ? (int) $item['qcdtl_id'] : null,
                    'batch_no'         => $batchNo,
                    'expired_tgl'      => !empty($item['expired_tgl']) ? $item['expired_tgl'] : null,
                    'grade_cd'         => $itemGrade,
                    'kadar_air_persen' => !empty($item['kadar_air_persen']) ? (float) $item['kadar_air_persen'] : 0,
                    'refraksi_persen'  => !empty($item['refraksi_persen']) ? (float) $item['refraksi_persen'] : 0,
                    'terima_qty'       => $terimaQty,
                    'reject_qty'       => $rejectQty,
                    'harga_nominal'    => $hargaNominal,
                    'diskon_persen'    => $diskonPersen,
                    'diskon_nominal'   => $diskonNominalUnit,
                    'potongan_nominal' => $potonganItem,
                    'harga_netto'      => $hargaNetto,
                    'subtotal_netto'   => $subtotalNetto,
                    'ppn_tipe'         => $itemPpnTipe,
                    'ppn_persen'       => $itemPpnPersen,
                    'ppn_nominal'      => $itemPpnNominal,
                    'subtotal_tagihan' => $subtotalTagihan,
                    'catatan_txt'      => $item['catatan_txt'] ?? null,
                ];
            }

            if (empty($processedItems)) {
                throw new Exception('Minimal harus ada 1 barang dengan kuantitas terima lebih dari 0 yang valid.');
            }

            $potonganGlobal = max(0, (float) ($data['potongan_nominal'] ?? 0));
            $totalSemuaPotongan = $totalItemPotongan + $potonganGlobal;

            $headerPpnTipe = 'NON_PPN';
            if ($hasPpn && $hasNonPpn) {
                $headerPpnTipe = 'MIXED';
            } elseif ($hasPpn) {
                $headerPpnTipe = 'PPN_11';
            }

            $dppNominal = $totalSubtotalBruto;
            $totalTagihan = round($totalSubtotalBruto, 2);

            // Update Header
            $terima->update([
                'terima_no'        => !empty($data['terima_no']) ? trim($data['terima_no']) : $terima->terima_no,
                'terima_tgl'       => $data['terima_tgl'] ?? $terima->terima_tgl,
                'po_id'            => $poId,
                'supplier_id'      => $supplierId,
                'gudang_id'        => $gudangId,
                'suratjalan_no'    => $data['suratjalan_no'] ?? null,
                'tgl_jatuh_tempo'  => !empty($data['tgl_jatuh_tempo']) ? $data['tgl_jatuh_tempo'] : $terima->tgl_jatuh_tempo,
                'catatan_txt'      => $data['catatan_txt'] ?? null,
                'subtotal_nominal' => $totalSubtotalBruto,
                'potongan_nominal' => 0,
                'dpp_nominal'      => $dppNominal,
                'ppn_tipe'         => $headerPpnTipe,
                'ppn_persen'       => $hasPpn ? 11.00 : 0.00,
                'ppn_nominal'      => 0.00,
                'total_tagihan'    => $totalTagihan,
            ]);

            // Simpan detail baru & suntik stok baru
            foreach ($processedItems as $row) {
                $barangId = $row['barang_id'];
                $batchNo = $row['batch_no'];

                if ($row['terima_qty'] > 0) {
                    if (empty($batchNo) || trim($batchNo) === '' || str_ends_with(trim($batchNo), '-')) {
                        $barang = MstBarang::find($barangId);
                        $barangNm = $barang?->barang_nm ?? "ID #{$barangId}";
                        throw new Exception("Nomor batch dari faktur supplier untuk komoditas '{$barangNm}' wajib diisi lengkap.");
                    }
                } else {
                    $batchNo = $batchNo ?: ('REJECT-' . date('Ymd'));
                }

                $dtl = DatTerimaDtl::create([
                    'terima_id'        => $terima->terima_id,
                    'podtl_id'         => $row['podtl_id'],
                    'qcdtl_id'         => $row['qcdtl_id'] ?? null,
                    'barang_id'        => $barangId,
                    'batch_no'         => $batchNo,
                    'expired_tgl'      => $row['expired_tgl'],
                    'terima_qty'       => $row['terima_qty'],
                    'reject_qty'       => $row['reject_qty'],
                    'grade_cd'         => $row['grade_cd'],
                    'kadar_air_persen' => $row['kadar_air_persen'] ?? 0,
                    'refraksi_persen'  => $row['refraksi_persen'] ?? 0,
                    'harga_nominal'    => $row['harga_nominal'],
                    'diskon_persen'    => $row['diskon_persen'],
                    'diskon_nominal'   => $row['diskon_nominal'],
                    'potongan_nominal' => $row['potongan_nominal'],
                    'harga_netto'      => $row['harga_netto'],
                    'subtotal_netto'   => $row['subtotal_netto'],
                    'ppn_tipe'         => $row['ppn_tipe'],
                    'ppn_persen'       => $row['ppn_persen'],
                    'ppn_nominal'      => $row['ppn_nominal'],
                    'subtotal_tagihan' => $row['subtotal_tagihan'],
                    'catatan_txt'      => $row['catatan_txt'],
                ]);

                // Suntik stok fisik & kartu stok HANYA jika kuantitas netto > 0
                $nettoQtyRow = max(0, (float) $row['terima_qty'] - (float) ($row['reject_qty'] ?? 0));
                if ($nettoQtyRow > 0) {
                    $this->stokService->addStock(
                        $gudangId,
                        $barangId,
                        $batchNo,
                        $nettoQtyRow,
                        $row['expired_tgl'],
                        $terima->terima_no,
                        "Penerimaan Barang Fisik No {$terima->terima_no} (Update)",
                        $row['harga_netto'],
                        $row['grade_cd'] ?: 'NON-GRADE'
                    );

                    // Update terima_qty di PO Detail jika terkait PO
                    if (!empty($dtl->podtl_id)) {
                        $poDtl = DatPoDtl::find($dtl->podtl_id);
                        if ($poDtl) {
                            $poDtl->terima_qty = (float) $poDtl->terima_qty + $nettoQtyRow;
                            $poDtl->save();
                        }
                    }
                }
            }

            // Update status PO jika terkait
            $affectedPoIds = array_filter(array_unique([$terima->po_id, $poId]));
            foreach ($affectedPoIds as $pId) {
                $poHdr = DatPoHdr::with('details')->find($pId);
                if ($poHdr) {
                    $semuaTuntas = true;
                    $adaYangDiterima = false;

                    foreach ($poHdr->details as $pdtl) {
                        if ((float) $pdtl->terima_qty < (float) $pdtl->pesan_qty) {
                            $semuaTuntas = false;
                        }
                        if ((float) $pdtl->terima_qty > 0) {
                            $adaYangDiterima = true;
                        }
                    }

                    if ($semuaTuntas) {
                        $poHdr->status_cd = 'COMPLETED';
                    } elseif ($adaYangDiterima) {
                        $poHdr->status_cd = 'PARTIAL';
                    } else {
                        $poHdr->status_cd = 'OPEN';
                    }
                    $poHdr->save();
                }
            }

            // Sinkronisasi status dan detail kuantitas tiket QC jika penerimaan berasal dari tiket QC
            $targetQcId = !empty($data['qc_id']) ? (int) $data['qc_id'] : $terima->qc_id;
            if (!empty($targetQcId)) {
                app(\App\Services\Gudang\QcInboundService::class)->syncFromTerimaBarang(
                    (int) $targetQcId,
                    $terima,
                    $items
                );
            }

            // 3. Ambil Snapshot SESUDAH pengubahan & catat riwayat revisi (Audit Trail)
            $terimaFresh = $terima->fresh(['supplier', 'gudang', 'details.barang.satuanDasar']);
            $afterSnapshot = $this->buildTerimaSnapshot($terimaFresh);
            $diffResult = $this->computeTerimaDiff($beforeSnapshot, $afterSnapshot);

            $user = auth()->user();
            $userName = $user ? $user->name : ($data['editor_nama'] ?? 'SYSTEM');
            $userRole = $user ? ($user->role?->role_nm ?? ($user->isSuperAdmin() ? 'Super Administrator' : 'Staff Gudang')) : 'Petugas Gudang';

            $nextRevNumber = (int) ($terima->total_revisi_count ?? 0) + 1;
            $revisiNo = "REV-{$nextRevNumber}";

            $diffTypes = collect($diffResult['diffs'])->pluck('tipe')->unique()->toArray();
            if (count($diffTypes) === 1 && in_array('HEADER', $diffTypes)) {
                $kategoriPerubahan = 'PERUBAHAN_HEADER';
            } elseif (count($diffTypes) === 1 && in_array('ITEM_QTY', $diffTypes)) {
                $kategoriPerubahan = 'PERUBAHAN_QTY';
            } elseif (count($diffTypes) === 1 && in_array('ITEM_HARGA', $diffTypes)) {
                $kategoriPerubahan = 'PERUBAHAN_HARGA';
            } elseif (in_array('ITEM_TAMBAH', $diffTypes) || in_array('ITEM_HAPUS', $diffTypes)) {
                $kategoriPerubahan = 'PERUBAHAN_ITEM';
            } else {
                $kategoriPerubahan = 'KOMPREHENSIF';
            }

            $alasanEdit = !empty($data['alasan_edit']) ? trim($data['alasan_edit']) : 'Pembaruan Dokumen Penerimaan Barang';
            $ringkasanPerubahan = implode("; ", $diffResult['summaryParts']);

            DatTerimaRevisiLog::create([
                'terima_id'           => $terima->terima_id,
                'revisi_no'           => $revisiNo,
                'revisi_tgl'          => now(),
                'user_id'             => $user?->id,
                'user_nama'           => $userName,
                'user_role'           => $userRole,
                'kategori_perubahan'  => $kategoriPerubahan,
                'alasan_edit'         => $alasanEdit,
                'ringkasan_perubahan' => $ringkasanPerubahan,
                'data_sebelum_json'   => $beforeSnapshot,
                'data_sesudah_json'   => $afterSnapshot,
                'perubahan_json'      => $diffResult['diffs'],
                'created_by'          => $userName,
            ]);

            $terima->update([
                'is_ada_revisi'        => true,
                'total_revisi_count'   => $nextRevNumber,
                'terakhir_direvisi_at' => now(),
                'terakhir_direvisi_by' => $userName,
            ]);

            $result = $terima->fresh(['supplier', 'gudang', 'details.barang.satuanDasar', 'revisiLog']);
            $result->was_recently_changed = true;
            return $result;
        });
    }

    /**
     * Alias method untuk update dokumen penerimaan barang
     */
    public function updateTerima(int $id, array $data): DatTerimaHdr
    {
        return $this->update($id, $data);
    }

    /**
     * Membatalkan / menghapus dokumen penerimaan barang secara aman.
     * Mengurangi kembali saldo stok fisik di gudang dan mengembalikan progres PO.
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $terima = DatTerimaHdr::with(['details', 'po.details'])->lockForUpdate()->findOrFail($id);

            if ($terima->deleted_st) {
                throw new \Exception("Dokumen penerimaan {$terima->terima_no} sudah pernah dihapus.");
            }

            // 0. Validasi status pemakaian stok & hutang sebelum pembatalan (Smart Guard)
            $usage = $this->checkStockUsage($terima);
            if (!$usage['can_be_deleted']) {
                $reasonText = !empty($usage['reasons']) ? implode(' ', $usage['reasons']) : 'Bahan baku telah digunakan di lini produksi atau telah terbit pembayaran.';
                throw new \Exception("Dokumen penerimaan {$terima->terima_no} terkunci dan tidak dapat dibatalkan: {$reasonText} Gunakan Berita Acara / formulir Penyesuaian Stok untuk koreksi persediaan.");
            }

            // 1. Kurangi kembali stok untuk setiap item detail (validasi ketersediaan batch otomatis oleh deductStock)
            foreach ($terima->details as $dtl) {
                $dtlNetto = max(0, (float) $dtl->terima_qty - (float) ($dtl->reject_qty ?? 0));
                if ($dtlNetto > 0) {
                    $this->stokService->deductStock(
                        $terima->gudang_id,
                        $dtl->barang_id,
                        $dtl->batch_no,
                        $dtlNetto,
                        $terima->terima_no,
                        "Pembatalan Dokumen Penerimaan Barang No {$terima->terima_no}",
                        $dtl->grade_cd ?? 'A'
                    );

                    // 2. Kembalikan terima_qty di PO Detail jika terkait PO
                    if (!empty($dtl->podtl_id)) {
                        $poDtl = DatPoDtl::find($dtl->podtl_id);
                        if ($poDtl) {
                            $poDtl->terima_qty = max(0, (float) $poDtl->terima_qty - $dtlNetto);
                            $poDtl->save();
                        }
                    }
                }
            }

            // 3. Recalculate status PO jika terkait PO
            if ($terima->po_id) {
                $poHdr = DatPoHdr::with('details')->find($terima->po_id);
                if ($poHdr) {
                    $semuaTuntas = true;
                    $adaYangDiterima = false;

                    foreach ($poHdr->details as $pdtl) {
                        if ((float) $pdtl->terima_qty < (float) $pdtl->pesan_qty) {
                            $semuaTuntas = false;
                        }
                        if ((float) $pdtl->terima_qty > 0) {
                            $adaYangDiterima = true;
                        }
                    }

                    if ($semuaTuntas) {
                        $poHdr->status_cd = 'COMPLETED';
                    } elseif ($adaYangDiterima) {
                        $poHdr->status_cd = 'PARTIAL';
                    } else {
                        $poHdr->status_cd = 'OPEN';
                    }
                    $poHdr->save();
                }
            }

            // 4. Kembalikan status tiket QC jika penerimaan berasal dari tiket QC
            if (!empty($terima->qc_id)) {
                app(\App\Services\Gudang\QcInboundService::class)->revertProcessed((int) $terima->qc_id);
            }

            // 5. Cek & Batalkan Tagihan Hutang (AP) terkait jika belum dibayarkan
            $hutangDtls = \App\Models\Keuangan\DatHutangDtl::where('deleted_st', false)
                ->where('terima_id', $terima->terima_id)
                ->get();

            foreach ($hutangDtls as $hDtl) {
                $hHdr = \App\Models\Keuangan\DatHutangHdr::find($hDtl->hutang_id);
                if ($hHdr && $hHdr->total_dibayar > 0) {
                    throw new \Exception("Dokumen penerimaan {$terima->terima_no} tidak dapat dibatalkan karena tagihan hutangnya sudah memiliki riwayat pembayaran di Keuangan (BKK).");
                }
                if ($hHdr && !$hHdr->deleted_st) {
                    app(\App\Services\Keuangan\HutangService::class)->destroy($hHdr, auth()->user() ?? new \App\Models\User(['name' => 'SYSTEM']));
                }
            }

            // 6. Tandai dokumen penerimaan sebagai terhapus (soft delete)
            $terima->deleted_st = true;
            $terima->save();

            return true;
        });
    }
}
