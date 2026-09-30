<?php

namespace App\Services\Gudang;

use App\Models\Gudang\DatPoDtl;
use App\Models\Gudang\DatPoHdr;
use App\Models\Gudang\DatReturDtl;
use App\Models\Gudang\DatReturHdr;
use App\Models\Gudang\DatStokBatch;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstSupplier;
use App\Services\Common\CodeGeneratorService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ReturPembelianService
{
    public function __construct(
        protected StokService $stokService,
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Mengambil daftar histori dokumen retur pembelian (Header View) dengan pagination dan filter.
     */
    public function getAllPaginated(
        int $perPage = 15,
        ?string $search = null,
        ?int $supplierId = null,
        int|array|null $gudangId = null,
        ?string $tindakan = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $barangId = null,
        ?string $kategori = null
    ): LengthAwarePaginator {
        $query = DatReturHdr::with(['supplier', 'gudang', 'po', 'details.barang.satuanDasar', 'details.barang.jenisBarang'])
            ->where('deleted_st', false);

        if (is_array($gudangId)) {
            $query->whereIn('gudang_id', $gudangId);
        } elseif ($gudangId !== null) {
            $query->where('gudang_id', $gudangId);
        }

        if (!empty($supplierId)) {
            $query->where('supplier_id', $supplierId);
        }

        if (!empty($tindakan)) {
            $query->where('tindakan_cd', $tindakan);
        }

        if (!empty($startDate)) {
            $query->whereDate('retur_tgl', '>=', $startDate);
        }

        if (!empty($endDate)) {
            $query->whereDate('retur_tgl', '<=', $endDate);
        }

        if (!empty($barangId)) {
            $query->whereHas('details', fn($dq) => $dq->where('barang_id', $barangId));
        }

        if (!empty($kategori)) {
            $query->whereHas('details.barang', function ($bq) use ($kategori) {
                if ($kategori === 'BAHAN_BAKU') {
                    $bq->where(function ($sq) {
                        $sq->whereHas('jenisBarang', fn($jq) => $jq->whereIn('jenis_barang_cd', ['BB', 'RAW']))
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%SINGKONG%'")
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%UBI%'");
                    });
                } elseif ($kategori === 'KEMASAN') {
                    $bq->where(function ($sq) {
                        $sq->whereHas('jenisBarang', fn($jq) => $jq->where('jenis_barang_cd', 'PACK'))
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%KARTON%'")
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%PLASTIK%'")
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%LAKBAN%'");
                    });
                } elseif ($kategori === 'BAHAN_PENOLONG') {
                    $bq->where(function ($sq) {
                        $sq->whereRaw("UPPER(barang_nm) LIKE '%BUMBU%'")
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%MINYAK%'")
                           ->orWhere(function ($sub) {
                               $sub->whereHas('jenisBarang', fn($jq) => $jq->whereIn('jenis_barang_cd', ['BP', 'SUPP']));
                           });
                    });
                }
            });
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('retur_no', 'ILIKE', "%{$search}%")
                  ->orWhere('suratjalan_supplier_no', 'ILIKE', "%{$search}%")
                  ->orWhere('alasan_txt', 'ILIKE', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('supplier_nm', 'ILIKE', "%{$search}%")
                         ->orWhere('supplier_cd', 'ILIKE', "%{$search}%");
                  })
                  ->orWhereHas('details.barang', function ($bq) use ($search) {
                      $bq->where('barang_nm', 'ILIKE', "%{$search}%")
                         ->orWhere('barang_cd', 'ILIKE', "%{$search}%");
                  })
                  ->orWhereHas('details', function ($dq) use ($search) {
                      $dq->where('batch_no', 'ILIKE', "%{$search}%")
                         ->orWhere('alasan_reject', 'ILIKE', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('retur_tgl', 'desc')
            ->orderBy('retur_id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Mengambil daftar rincian per-item retur (Item View / Excel Grid) sesuai standar operasional.
     * Kolom: Tanggal | No Retur | Supplier | Kode Batch | Kode Barang | Nama Barang | Kategori | Alasan Cacat | Qty Retur | Satuan | Harga | Total Nilai
     */
    public function getItemListPaginated(
        int $perPage = 25,
        ?string $search = null,
        ?int $supplierId = null,
        int|array|null $gudangId = null,
        ?string $tindakan = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $barangId = null,
        ?string $kategori = null
    ): LengthAwarePaginator {
        $query = DatReturDtl::with([
            'header.supplier',
            'header.gudang',
            'header.po',
            'barang.satuanDasar',
            'barang.jenisBarang'
        ])->whereHas('header', function ($hq) use ($gudangId, $supplierId, $tindakan, $startDate, $endDate) {
            $hq->where('deleted_st', false);
            if (is_array($gudangId)) {
                $hq->whereIn('gudang_id', $gudangId);
            } elseif ($gudangId !== null) {
                $hq->where('gudang_id', $gudangId);
            }
            if (!empty($supplierId)) {
                $hq->where('supplier_id', $supplierId);
            }
            if (!empty($tindakan)) {
                $hq->where('tindakan_cd', $tindakan);
            }
            if (!empty($startDate)) {
                $hq->whereDate('retur_tgl', '>=', $startDate);
            }
            if (!empty($endDate)) {
                $hq->whereDate('retur_tgl', '<=', $endDate);
            }
        });

        if (!empty($barangId)) {
            $query->where('barang_id', $barangId);
        }

        if (!empty($kategori)) {
            $query->whereHas('barang', function ($bq) use ($kategori) {
                if ($kategori === 'BAHAN_BAKU') {
                    $bq->where(function ($sq) {
                        $sq->whereHas('jenisBarang', fn($jq) => $jq->whereIn('jenis_barang_cd', ['BB', 'RAW']))
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%SINGKONG%'")
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%UBI%'");
                    });
                } elseif ($kategori === 'KEMASAN') {
                    $bq->where(function ($sq) {
                        $sq->whereHas('jenisBarang', fn($jq) => $jq->where('jenis_barang_cd', 'PACK'))
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%KARTON%'")
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%PLASTIK%'")
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%LAKBAN%'");
                    });
                } elseif ($kategori === 'BAHAN_PENOLONG') {
                    $bq->where(function ($sq) {
                        $sq->whereRaw("UPPER(barang_nm) LIKE '%BUMBU%'")
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%MINYAK%'")
                           ->orWhere(function ($sub) {
                               $sub->whereHas('jenisBarang', fn($jq) => $jq->whereIn('jenis_barang_cd', ['BP', 'SUPP']));
                           });
                    });
                }
            });
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('batch_no', 'ILIKE', "%{$search}%")
                  ->orWhere('alasan_reject', 'ILIKE', "%{$search}%")
                  ->orWhere('catatan_txt', 'ILIKE', "%{$search}%")
                  ->orWhereHas('barang', function ($bq) use ($search) {
                      $bq->where('barang_nm', 'ILIKE', "%{$search}%")
                         ->orWhere('barang_cd', 'ILIKE', "%{$search}%");
                  })
                  ->orWhereHas('header', function ($hq) use ($search) {
                      $hq->where('retur_no', 'ILIKE', "%{$search}%")
                         ->orWhere('suratjalan_supplier_no', 'ILIKE', "%{$search}%")
                         ->orWhereHas('supplier', function ($sq) use ($search) {
                             $sq->where('supplier_nm', 'ILIKE', "%{$search}%")
                                ->orWhere('supplier_cd', 'ILIKE', "%{$search}%");
                         });
                  });
            });
        }

        return $query->orderBy('returdtl_id', 'desc')->paginate($perPage);
    }

    /**
     * Menghitung metrik ringkasan kuantitas & nominal per kategori bahan yang diretur.
     */
    public function getRingkasan(
        int|array|null $gudangId = null,
        ?int $supplierId = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $query = DatReturDtl::with('barang')
            ->whereHas('header', function ($hq) use ($gudangId, $supplierId, $startDate, $endDate) {
                $hq->where('deleted_st', false);
                if (is_array($gudangId)) {
                    $hq->whereIn('gudang_id', $gudangId);
                } elseif ($gudangId !== null) {
                    $hq->where('gudang_id', $gudangId);
                }
                if (!empty($supplierId)) {
                    $hq->where('supplier_id', $supplierId);
                }
                if (!empty($startDate)) {
                    $hq->whereDate('retur_tgl', '>=', $startDate);
                }
                if (!empty($endDate)) {
                    $hq->whereDate('retur_tgl', '<=', $endDate);
                }
            });

        $items = $query->get();

        $singkongQty = 0;
        $bumbuMinyakQty = 0;
        $kemasanQty = 0;
        $grandTotalNilai = 0;

        foreach ($items as $dtl) {
            $qty = (float) $dtl->retur_qty;
            $subtotal = (float) $dtl->subtotal_nominal;
            $nama = strtoupper((string) ($dtl->barang?->barang_nm ?? ''));

            $grandTotalNilai += $subtotal;

            if (str_contains($nama, 'SINGKONG') || str_contains($nama, 'UBI')) {
                $singkongQty += $qty;
            } elseif (str_contains($nama, 'KARTON') || str_contains($nama, 'PLASTIK') || str_contains($nama, 'DUS') || str_contains($nama, 'LAKBAN')) {
                $kemasanQty += $qty;
            } else {
                $bumbuMinyakQty += $qty;
            }
        }

        return [
            'total_item_count' => $items->count(),
            'singkong_qty'     => $singkongQty,
            'bumbu_minyak_qty' => $bumbuMinyakQty,
            'kemasan_qty'      => $kemasanQty,
            'grand_total_nilai'=> $grandTotalNilai,
        ];
    }

    /**
     * Mengambil detail satu dokumen retur pembelian.
     */
    public function getById(int $id): DatReturHdr
    {
        return DatReturHdr::with([
            'supplier',
            'gudang',
            'po.details.barang',
            'penerimaan',
            'details.barang.satuanDasar',
            'details.poDetail'
        ])->where('retur_id', $id)->firstOrFail();
    }

    /**
     * Menyimpan transaksi retur pembelian:
     * 1. Validasi ketersediaan batch & sisa stok fisik.
     * 2. Kurangi stok fisik gudang via StokService::deductStock().
     * 3. Jika PO & tindakan REPLACE: kurangi terima_qty di PO Detail & buka kembali status PO ke PARTIAL.
     * 4. Simpan Header & Detail Retur.
     */
    public function store(array $data): DatReturHdr
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            if (empty($items)) {
                throw new Exception("Minimal harus ada 1 item barang yang diretur.");
            }

            $gudangId = (int) $data['gudang_id'];
            $supplierId = (int) $data['supplier_id'];
            $poId = !empty($data['po_id']) ? (int) $data['po_id'] : null;
            $terimaId = !empty($data['terima_id']) ? (int) $data['terima_id'] : null;
            $tindakanCd = $data['tindakan_cd'] ?? 'REPLACE'; // REPLACE atau CREDIT_NOTE

            $supplier = MstSupplier::findOrFail($supplierId);
            $returNo = !empty($data['retur_no']) ? trim($data['retur_no']) : $this->codeGenerator->generateReturNo();

            // Hitung total nominal & simpan rincian yang valid
            $totalNominal = 0;
            $validatedItems = [];

            foreach ($items as $item) {
                $barangId = (int) $item['barang_id'];
                $batchNo = trim((string) ($item['batch_no'] ?? ''));
                $returQty = (float) ($item['retur_qty'] ?? 0);
                $hargaSatuan = (float) ($item['harga_satuan'] ?? 0);
                $alasanReject = trim((string) ($item['alasan_reject'] ?? 'Cacat Mutu'));

                if ($returQty <= 0) {
                    continue;
                }

                if (empty($batchNo)) {
                    throw new Exception("Nomor batch wajib ditentukan untuk setiap item barang yang diretur.");
                }

                // Cek ketersediaan stok batch di gudang tersebut
                $stokBatch = DatStokBatch::where('gudang_id', $gudangId)
                    ->where('barang_id', $barangId)
                    ->where('batch_no', $batchNo)
                    ->first();

                $sisaStok = $stokBatch ? (float) $stokBatch->sisa_qty : 0;
                if ($sisaStok < $returQty) {
                    $barang = MstBarang::find($barangId);
                    $namaBarang = $barang ? $barang->barang_nm : "Barang ID #{$barangId}";
                    throw new Exception("Stok batch '{$batchNo}' ({$namaBarang}) tidak mencukupi untuk diretur. Stok tersedia: {$sisaStok}, diminta retur: {$returQty}.");
                }

                // Jika harga satuan tidak diisi, ambil harga dari stok batch atau master barang
                if ($hargaSatuan <= 0 && $stokBatch && (float) $stokBatch->harga_satuan > 0) {
                    $hargaSatuan = (float) $stokBatch->harga_satuan;
                }
                if ($hargaSatuan <= 0) {
                    $barang = MstBarang::find($barangId);
                    $hargaSatuan = (float) ($barang?->harga_beli_standar ?? 0);
                }

                $subtotal = $returQty * $hargaSatuan;
                $totalNominal += $subtotal;

                $validatedItems[] = [
                    'barang_id'        => $barangId,
                    'podtl_id'         => !empty($item['podtl_id']) ? (int) $item['podtl_id'] : null,
                    'batch_no'         => $batchNo,
                    'retur_qty'        => $returQty,
                    'harga_satuan'     => $hargaSatuan,
                    'subtotal_nominal' => $subtotal,
                    'alasan_reject'    => $alasanReject,
                    'catatan_txt'      => $item['catatan_txt'] ?? null,
                ];
            }

            if (empty($validatedItems)) {
                throw new Exception("Tidak ada item dengan kuantitas retur valid (> 0).");
            }

            // 1. Simpan Header Retur
            $header = DatReturHdr::create([
                'retur_no'               => $returNo,
                'retur_tgl'              => $data['retur_tgl'] ?? date('Y-m-d'),
                'supplier_id'            => $supplierId,
                'gudang_id'              => $gudangId,
                'po_id'                  => $poId,
                'terima_id'              => $terimaId,
                'tindakan_cd'            => $tindakanCd,
                'suratjalan_supplier_no' => $data['suratjalan_supplier_no'] ?? null,
                'total_nominal'          => $totalNominal,
                'status_cd'              => 'COMPLETED',
                'alasan_txt'             => $data['alasan_txt'] ?? null,
            ]);

            // 2. Loop detail & eksekusi pemotongan stok fisik
            foreach ($validatedItems as $valItem) {
                DatReturDtl::create(array_merge($valItem, [
                    'retur_id' => $header->retur_id,
                ]));

                // Potong stok batch fisik
                $ket = "Retur Barang {$header->retur_no} ke {$supplier->supplier_nm} (Batch: {$valItem['batch_no']}, Alasan: {$valItem['alasan_reject']})";
                $this->stokService->deductStock(
                    $gudangId,
                    $valItem['barang_id'],
                    $valItem['batch_no'],
                    $valItem['retur_qty'],
                    $header->retur_no,
                    $ket
                );

                // 3. Efek terhadap PO (jika barang berasal dari PO)
                if ($poId && !empty($valItem['podtl_id'])) {
                    $poDtl = DatPoDtl::find($valItem['podtl_id']);
                    if ($poDtl) {
                        // Jika supplier berjanji mengganti barang baru (REPLACE):
                        // Kurangi kembali terima_qty agar kuota PO terbuka kembali untuk pengiriman pengganti
                        if ($tindakanCd === 'REPLACE') {
                            $poDtl->terima_qty = max(0, (float) $poDtl->terima_qty - $valItem['retur_qty']);
                            $poDtl->save();
                        }
                    }
                }
            }

            // Jika ada PO dan tindakan REPLACE, evaluasi ulang status PO:
            // Jika sebelumnya COMPLETED, otomatis kembalikan ke PARTIAL karena ada kuota yang menunggu pengganti
            if ($poId && $tindakanCd === 'REPLACE') {
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

                    if (!$semuaTuntas && $adaYangDiterima) {
                        $poHdr->status_cd = 'PARTIAL';
                        $poHdr->catatan_txt = trim(($poHdr->catatan_txt ? $poHdr->catatan_txt . "\n" : "") . 
                            "[RETUR - " . date('d/m/Y') . "] Sebagian barang diretur ke supplier via Dokumen {$header->retur_no}. Menunggu pengiriman barang pengganti.");
                        $poHdr->save();
                    }
                }
            }

            return $header->fresh(['details.barang', 'supplier', 'gudang', 'po']);
        });
    }

    /**
     * Mengambil daftar batch barang yang tersedia (sisa_qty > 0) di suatu gudang.
     * Digunakan untuk dropdown interaktif pemilihan batch retur.
     */
    public function getAvailableBatches(int $gudangId, ?int $barangId = null): Collection
    {
        $query = DatStokBatch::with(['barang.satuanDasar'])
            ->where('gudang_id', $gudangId)
            ->where('deleted_st', false)
            ->where('sisa_qty', '>', 0);

        if ($barangId) {
            $query->where('barang_id', $barangId);
        }

        return $query->orderBy('barang_id')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
