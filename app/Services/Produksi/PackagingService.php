<?php

namespace App\Services\Produksi;

use App\Models\Gudang\DatStokBatch;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Models\Produksi\DatPackagingDtl;
use App\Models\Produksi\DatPackagingHdr;
use App\Models\Produksi\DatProduksiDtl;
use App\Models\Produksi\DatProduksiHdr;
use App\Models\Produksi\MstBomHdr;
use App\Services\Common\CodeGeneratorService;
use App\Services\Gudang\StokService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

use App\Services\Produksi\TarifKemasanService;

class PackagingService
{
    public function __construct(
        protected StokService $stokService,
        protected CodeGeneratorService $codeGenerator,
        protected TarifKemasanService $tarifKemasanService
    ) {}

    /**
     * Mengambil daftar dokumen pengemasan (Level Header) terpaginasi.
     */
    public function getAllPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = DatPackagingHdr::with(['barangJadi.satuanDasar', 'gudang', 'details.barang', 'produksi'])
            ->where('deleted_st', false);

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('pack_no', 'ILIKE', "%{$search}%")
                  ->orWhere('batch_no', 'ILIKE', "%{$search}%")
                  ->orWhere('catatan_txt', 'ILIKE', "%{$search}%")
                  ->orWhereHas('barangJadi', function (Builder $bq) use ($search) {
                      $bq->where('barang_nm', 'ILIKE', "%{$search}%")
                         ->orWhere('barang_cd', 'ILIKE', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['pack_tgl'])) {
            $query->whereDate('pack_tgl', $filters['pack_tgl']);
        }

        if (!empty($filters['barang_jadi_id'])) {
            $query->where('barang_jadi_id', $filters['barang_jadi_id']);
        }

        if (!empty($filters['status_cd'])) {
            $query->where('status_cd', $filters['status_cd']);
        }

        return $query->orderByDesc('pack_tgl')->orderByDesc('pack_id')->paginate($perPage);
    }

    /**
     * Mengambil ringkasan metrik statistik Packaging & HPP.
     */
    public function getMetrics(array $filters = []): array
    {
        $base = DatPackagingHdr::where('deleted_st', false);

        if (!empty($filters['pack_tgl'])) {
            $base->whereDate('pack_tgl', $filters['pack_tgl']);
        }
        if (!empty($filters['barang_jadi_id'])) {
            $base->where('barang_jadi_id', $filters['barang_jadi_id']);
        }

        $totalDokumen = (clone $base)->count();
        $totalKarton = (float) (clone $base)->where('status_cd', 'POSTED')->sum('hasil_karton_qty');
        $totalBiaya = (float) (clone $base)->where('status_cd', 'POSTED')->sum('total_biaya_produksi');
        $rataHppKarton = $totalKarton > 0 ? round($totalBiaya / $totalKarton, 2) : 0;

        return [
            'total_dokumen'        => $totalDokumen,
            'total_karton'         => $totalKarton,
            'total_biaya_produksi' => $totalBiaya,
            'rata_hpp_karton'      => $rataHppKarton,
        ];
    }

    /**
     * Pratinjau Cerdas Resep Kemasan (BOM Smart Preview) & Auto-Kalkulasi HPP.
     * Secara otomatis menarik saldo stok fisik gudang & HPP riil keripik curah (FIFO).
     */
    public function getBomPreview(int $barangJadiId, float $targetKartonQty, int $produksiId = 0): array
    {
        $barangJadi = MstBarang::with('satuanDasar')->findOrFail($barangJadiId);

        $linkedProduksi = null;
        if ($produksiId > 0) {
            $linkedProduksi = DatProduksiHdr::where('deleted_st', false)->find($produksiId);
        }

        // Cari Formula Resep (BOM) terkait produk jadi ini
        $bom = MstBomHdr::with(['details.barangMentah.satuanDasar', 'details.barangMentah.jenisBarang'])
            ->where('barang_jadi_id', $barangJadiId)
            ->where('active_st', true)
            ->where('deleted_st', false)
            ->first();

        if (!$bom || (float) $bom->batch_ukuran_qty <= 0) {
            return [
                'has_bom'              => false,
                'message'              => "Formula resep standar (BOM) belum ditetapkan untuk produk '{$barangJadi->barang_nm}'. Anda dapat memasukkan komponen bahan secara manual.",
                'barang_jadi'          => [
                    'barang_id' => $barangJadi->barang_id,
                    'barang_nm' => $barangJadi->barang_nm,
                    'barang_cd' => $barangJadi->barang_cd,
                ],
                'target_karton'        => $targetKartonQty,
                'items'                => [],
                'estimasi_biaya'       => 0,
                'estimasi_hpp'         => 0,
            ];
        }

        $rasio = $targetKartonQty / (float) $bom->batch_ukuran_qty;
        $items = [];
        $totalWip = 0;
        $totalKemasan = 0;
        $totalBumbu = 0;
        $totalLain = 0;

        foreach ($bom->details as $dtl) {
            $b = $dtl->barangMentah;
            if (!$b) continue;

            $qtyStandar = round((float) $dtl->kebutuhan_qty * $rasio, 4);
            $jenisCd = strtoupper($b->jenisBarang?->jenis_barang_cd ?? '');

            // Klasifikasi kategori bahan
            if ($jenisCd === 'WIP' || str_contains(strtoupper($b->barang_nm), 'CURAH') || str_contains(strtoupper($b->barang_nm), 'BERKO')) {
                $kategori = 'WIP';
            } elseif ($jenisCd === 'PACK' || preg_match('/(PLASTIK|KARTON|DUS|BOX|ROLL|LAKBAN|RAFIA|POUCH)/i', $b->barang_nm)) {
                $kategori = 'KEMASAN';
            } elseif (in_array($jenisCd, ['BP', 'BUMBU', 'SUPP'])) {
                $kategori = 'BUMBU';
            } else {
                $kategori = 'LAIN';
            }

            $stokTotal = (float) DatStokBatch::where('barang_id', $b->barang_id)
                ->where('deleted_st', false)
                ->sum('sisa_qty');

            // Ambil batch stok aktif gudang secara FIFO (sisa_qty > 0)
            $activeBatch = DatStokBatch::where('barang_id', $b->barang_id)
                ->where('deleted_st', false)
                ->where('sisa_qty', '>', 0)
                ->orderBy('created_at', 'asc')
                ->first();

            $hargaSatuan = 0;
            $batchNo = $activeBatch?->batch_no ?? null;

            if ($kategori === 'WIP' && $linkedProduksi && (float) $linkedProduksi->hpp_per_kg > 0) {
                // Prioritas Tertinggi: Sinkronisasi langsung dengan Dokumen HPP Produksi Hari Terkait
                $hargaSatuan = (float) $linkedProduksi->hpp_per_kg;
                $batchNo = $linkedProduksi->batch_wip_no 
                    ?: ($linkedProduksi->produksi_tgl ? $linkedProduksi->produksi_tgl->format('d m Y') : $batchNo);
            } elseif ($activeBatch && (float) $activeBatch->harga_satuan > 0) {
                $hargaSatuan = (float) $activeBatch->harga_satuan;
            } else {
                // Fallback: cek HPP produksi terakhir (untuk WIP) atau master harga beli standar
                if ($kategori === 'WIP') {
                    $lastProd = DatProduksiDtl::where('barang_id', $b->barang_id)
                        ->whereHas('produksi', fn($q) => $q->where('status_cd', 'POSTED')->where('deleted_st', false))
                        ->latest('output_id')
                        ->first();
                    $hargaSatuan = $lastProd && (float) $lastProd->hpp_satuan > 0 
                        ? (float) $lastProd->hpp_satuan 
                        : (float) $b->harga_beli_standar;
                    if (!$batchNo) {
                        $batchNo = $lastProd?->batch_no ?? null;
                    }
                } else {
                    $hargaSatuan = (float) $b->harga_beli_standar;
                }
            }

            $subtotal = round($qtyStandar * $hargaSatuan, 2);

            if ($kategori === 'WIP') {
                $totalWip += $subtotal;
            } elseif ($kategori === 'KEMASAN') {
                $totalKemasan += $subtotal;
            } elseif ($kategori === 'BUMBU') {
                $totalBumbu += $subtotal;
            } else {
                $totalLain += $subtotal;
            }

            $items[] = [
                'barang_id'        => $b->barang_id,
                'barang_cd'        => $b->barang_cd,
                'barang_nm'        => $b->barang_nm,
                'satuan_cd'        => $b->satuanDasar?->satuan_cd ?? 'PCS',
                'kategori_bahan'   => $kategori,
                'is_wip'           => ($kategori === 'WIP'),
                'qty_standar'      => $qtyStandar,
                'qty_aktual'       => $qtyStandar,
                'harga_satuan'     => $hargaSatuan,
                'subtotal_nilai'   => $subtotal,
                'stok_tersedia'    => $stokTotal,
                'batch_no'         => $batchNo,
                'cukup_stok'       => $stokTotal >= $qtyStandar,
            ];
        }

        $totalEstimasiBiaya = $totalWip + $totalKemasan + $totalBumbu + $totalLain;
        $estimasiHppKarton = $targetKartonQty > 0 ? round($totalEstimasiBiaya / $targetKartonQty, 2) : 0;

        return [
            'has_bom'              => true,
            'bom_id'               => $bom->bom_id,
            'bom_no'               => $bom->bom_no,
            'bom_nm'               => $bom->bom_nm,
            'batch_ukuran_qty'     => (float) $bom->batch_ukuran_qty,
            'barang_jadi'          => [
                'barang_id' => $barangJadi->barang_id,
                'barang_nm' => $barangJadi->barang_nm,
                'barang_cd' => $barangJadi->barang_cd,
            ],
            'target_karton'        => $targetKartonQty,
            'items'                => $items,
            'total_biaya_wip'      => $totalWip,
            'total_biaya_kemasan'  => $totalKemasan,
            'total_biaya_bumbu'    => $totalBumbu,
            'total_biaya_lain'     => $totalLain,
            'total_estimasi_biaya' => $totalEstimasiBiaya,
            'estimasi_hpp_karton'  => $estimasiHppKarton,
        ];
    }

    /**
     * Menyimpan dan memposting transaksi Pengemasan Barang Jadi (Packaging & HPP).
     * Wajib DB::transaction() sesuai standar AGENTS.md.
     */
    public function storePackaging(array $data, ?string $userName = null): DatPackagingHdr
    {
        return DB::transaction(function () use ($data, $userName) {
            $packTgl = $data['pack_tgl'] ?? now()->format('Y-m-d');
            $barangJadiId = (int) $data['barang_jadi_id'];
            $bomId = !empty($data['bom_id']) ? (int) $data['bom_id'] : null;
            $gudangId = (int) ($data['gudang_id'] ?? 0);

            // Validasi Gudang Barang Jadi (FG)
            if ($gudangId <= 0) {
                $fgGudang = MstGudang::where('tipe_gudang_cd', 'FG')->active()->first();
                $gudangId = $fgGudang ? $fgGudang->gudang_id : MstGudang::active()->first()?->gudang_id;
            }

            $targetKarton = (float) ($data['target_karton_qty'] ?? 0);
            $hasilKarton = (float) ($data['hasil_karton_qty'] ?? $targetKarton);
            $isiPerKarton = (float) ($data['isi_per_karton_pcs'] ?? 1);
            if ($isiPerKarton <= 0) $isiPerKarton = 1;
            $hasilPcs = $hasilKarton * $isiPerKarton;

            // Generate Nomor Dokumen: PCK-YYYYMM-0001
            $prefix = 'PCK-' . date('Ym', strtotime($packTgl)) . '-';
            $packNo = $this->codeGenerator->generate('dat_packaging_hdr', 'pack_no', $prefix, 4);

            // Generate Nomor Batch FG jika kosong
            $batchNo = !empty($data['batch_no']) 
                ? trim($data['batch_no']) 
                : ('LOT-FG-' . date('ymd', strtotime($packTgl)) . '-' . $barangJadiId);

            $expiredTgl = !empty($data['expired_tgl']) ? $data['expired_tgl'] : null;
            $upahTk = (float) ($data['total_biaya_tenaga_kerja'] ?? 0);
            $biayaLain = (float) ($data['total_biaya_lain'] ?? 0);

            $rawItems = $data['items'] ?? [];
            if (empty($rawItems)) {
                throw new Exception("Minimal harus ada satu komponen bahan/WIP yang dikonsumsi untuk proses pengemasan.");
            }

            $totalWip = 0;
            $totalKemasan = 0;
            $totalBumbu = 0;
            $processedDetails = [];

            // 1. Hitung Detail Bahan & Validasi Stok
            foreach ($rawItems as $item) {
                $barangId = (int) $item['barang_id'];
                $kategori = strtoupper(trim($item['kategori_bahan'] ?? 'KEMASAN'));
                $qtyAktual = (float) ($item['qty_aktual'] ?? 0);
                $qtyStandar = (float) ($item['qty_standar'] ?? $qtyAktual);
                $hargaSatuan = (float) ($item['harga_satuan'] ?? 0);
                $subtotal = round($qtyAktual * $hargaSatuan, 2);
                $batchAsal = !empty($item['batch_no']) ? trim($item['batch_no']) : null;
                $satuanCd = $item['satuan_cd'] ?? 'PCS';

                if ($qtyAktual <= 0) {
                    continue; // Skip jika 0
                }

                if ($kategori === 'WIP') {
                    $totalWip += $subtotal;
                } elseif ($kategori === 'KEMASAN') {
                    $totalKemasan += $subtotal;
                } elseif ($kategori === 'BUMBU') {
                    $totalBumbu += $subtotal;
                } else {
                    $biayaLain += $subtotal;
                }

                $processedDetails[] = [
                    'kategori_bahan' => $kategori,
                    'barang_id'      => $barangId,
                    'batch_no'       => $batchAsal,
                    'qty_standar'    => $qtyStandar,
                    'qty_aktual'     => $qtyAktual,
                    'satuan_cd'      => $satuanCd,
                    'harga_satuan'   => $hargaSatuan,
                    'subtotal_nilai' => $subtotal,
                    'catatan_txt'    => $item['catatan_txt'] ?? null,
                ];
            }

            $totalBiayaProduksi = $totalWip + $totalKemasan + $totalBumbu + $upahTk + $biayaLain;
            $hppPerKarton = $hasilKarton > 0 ? round($totalBiayaProduksi / $hasilKarton, 4) : 0;
            $hppPerPcs = $hasilPcs > 0 ? round($totalBiayaProduksi / $hasilPcs, 4) : 0;

            $prodId = !empty($data['produksi_id']) ? (int) $data['produksi_id'] : null;
            if (!$prodId) {
                foreach ($processedDetails as $pd) {
                    if ($pd['kategori_bahan'] === 'WIP') {
                        $linkedDtl = DatProduksiDtl::where('barang_id', $pd['barang_id'])
                            ->when(!empty($pd['batch_no']), fn($q) => $q->where('batch_no', $pd['batch_no']))
                            ->latest('output_id')
                            ->first();
                        if ($linkedDtl && $linkedDtl->produksi_id) {
                            $prodId = $linkedDtl->produksi_id;
                            break;
                        }
                    }
                }
            }

            $pakaiId = !empty($data['pakai_id']) ? (int) $data['pakai_id'] : null;

            // Perhitungan Timbangan Lakban Atas & Bawah (Presisi Gramatur & Rupiah)
            $lakbanAtasGr = (float) ($data['lakban_atas_gr'] ?? 0);
            $lakbanBawahGr = (float) ($data['lakban_bawah_gr'] ?? 0);
            $lakbanJumlahGr = (float) ($data['lakban_jumlah_gr'] ?? 0);
            $lakbanNilai = (float) ($data['lakban_nilai'] ?? 0);

            $lakbanDetailSub = 0;
            $lakbanDetailQty = 0;
            foreach ($processedDetails as $pd) {
                $brg = MstBarang::find($pd['barang_id']);
                $bNm = strtoupper($brg?->barang_nm ?? '');
                if (str_contains($bNm, 'LAKBAN') || $pd['kategori_bahan'] === 'LAKBAN') {
                    $lakbanDetailSub += (float) $pd['subtotal_nilai'];
                    $lakbanDetailQty += (float) $pd['qty_aktual'];
                }
            }

            if ($lakbanNilai <= 0) {
                $lakbanNilai = $lakbanDetailSub;
            }
            if ($lakbanJumlahGr <= 0) {
                if ($lakbanAtasGr > 0 || $lakbanBawahGr > 0) {
                    $lakbanJumlahGr = $lakbanAtasGr + $lakbanBawahGr;
                } else {
                    $lakbanJumlahGr = ($lakbanDetailQty > 50 ? $lakbanDetailQty : $lakbanDetailQty * 100);
                    $lakbanAtasGr = round($lakbanJumlahGr / 2, 1);
                    $lakbanBawahGr = round($lakbanJumlahGr / 2, 1);
                }
            }

            // 2. Buat Header Dokumen Pengemasan
            $header = DatPackagingHdr::create([
                'pack_no'                  => $packNo,
                'pack_tgl'                 => $packTgl,
                'gudang_id'                => $gudangId,
                'barang_jadi_id'           => $barangJadiId,
                'bom_id'                   => $bomId,
                'produksi_id'              => $prodId,
                'target_karton_qty'        => $targetKarton,
                'hasil_karton_qty'         => $hasilKarton,
                'isi_per_karton_pcs'       => $isiPerKarton,
                'gramatur_gr'              => (float) ($data['gramatur_gr'] ?? 0),
                'hasil_pcs_qty'            => $hasilPcs,
                'batch_no'                 => $batchNo,
                'expired_tgl'              => $expiredTgl,
                'total_biaya_wip'          => $totalWip,
                'total_biaya_kemasan'      => $totalKemasan + $totalBumbu,
                'lakban_atas_gr'           => $lakbanAtasGr,
                'lakban_bawah_gr'          => $lakbanBawahGr,
                'lakban_jumlah_gr'         => $lakbanJumlahGr,
                'lakban_nilai'             => $lakbanNilai,
                'total_biaya_tenaga_kerja' => $upahTk,
                'total_biaya_lain'         => $biayaLain,
                'total_biaya_produksi'     => $totalBiayaProduksi,
                'hpp_per_karton'           => $hppPerKarton,
                'hpp_per_pcs'              => $hppPerPcs,
                'catatan_txt'              => $data['catatan_txt'] ?? null,
                'status_cd'                => 'POSTED',
                'created_by'               => $userName ?? auth()->user()?->name ?? 'System',
                'updated_by'               => $userName ?? auth()->user()?->name ?? 'System',
            ]);

            // 3. Simpan Detail Konsumsi & Eksekusi Pemotongan Stok Bahan Fisik (Outbound)
            foreach ($processedDetails as $dtlData) {
                DatPackagingDtl::create([
                    'pack_id'        => $header->pack_id,
                    'kategori_bahan' => $dtlData['kategori_bahan'],
                    'barang_id'      => $dtlData['barang_id'],
                    'batch_no'       => $dtlData['batch_no'],
                    'qty_standar'    => $dtlData['qty_standar'],
                    'qty_aktual'     => $dtlData['qty_aktual'],
                    'satuan_cd'      => $dtlData['satuan_cd'],
                    'harga_satuan'   => $dtlData['harga_satuan'],
                    'subtotal_nilai' => $dtlData['subtotal_nilai'],
                    'catatan_txt'    => $dtlData['catatan_txt'],
                    'created_by'     => $userName ?? auth()->user()?->name ?? 'System',
                    'updated_by'     => $userName ?? auth()->user()?->name ?? 'System',
                ]);

                // Tentukan gudang sumber bahan yang dipotong
                $brgTarget = MstBarang::with('jenisBarang')->find($dtlData['barang_id']);
                
                // Cari batch stok fisik di gudang terkait
                $stokBatch = DatStokBatch::where('barang_id', $dtlData['barang_id'])
                    ->where('deleted_st', false)
                    ->when(!empty($dtlData['batch_no']), fn($q) => $q->where('batch_no', $dtlData['batch_no']))
                    ->where('sisa_qty', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->first();

                $gudangSumberId = $stokBatch ? $stokBatch->gudang_id : $gudangId;
                $batchTarget = $stokBatch ? $stokBatch->batch_no : ($dtlData['batch_no'] ?? 'BATCH-DEF');

                // Potong stok bahan terpakai
                $ket = "Konsumsi Pengemasan ({$packNo}): {$dtlData['qty_aktual']} {$dtlData['satuan_cd']} untuk {$hasilKarton} Karton {$header->barangJadi?->barang_nm}";
                
                try {
                    $this->stokService->deductStock(
                        gudangId: $gudangSumberId,
                        barangId: $dtlData['barang_id'],
                        batchNo: $batchTarget,
                        qty: $dtlData['qty_aktual'],
                        dokumenNo: $packNo,
                        keterangan: $ket
                    );
                } catch (Exception $e) {
                    // Fallback jika stok fisik kurang tapi operasional tetap mencatat konsumsi
                }
            }

            // 4. OTOMATIS TERCATAT DI MENU "GUDANG -> PEMAKAIAN BAHAN" (AUTO-ISSUANCE)
            // Memudahkan operasional: Orang gudang tidak perlu input manual, dokumen pengeluaran otomatis terbit!
            try {
                $prefixPakai = 'OUT-' . date('Ym', strtotime($packTgl)) . '-';
                $pakaiNo = $this->codeGenerator->generate('dat_pakai_hdr', 'pakai_no', $prefixPakai, 4);
                $targetBarang = MstBarang::find($barangJadiId);
                $targetBarangNm = $targetBarang?->barang_nm ?? 'BARANG JADI';

                $pakaiHdr = \App\Models\Gudang\DatPakaiHdr::create([
                    'pakai_no'         => $pakaiNo,
                    'pakai_tgl'        => $packTgl,
                    'gudang_id'        => $gudangId,
                    'produksi_id'      => $prodId,
                    'tujuan_pemakaian' => 'PENGEMASAN ' . strtoupper($targetBarangNm),
                    'catatan_txt'      => "Otomatis terbentuk dari Pengemasan No. {$packNo} ({$hasilKarton} Karton)",
                    'created_by'       => $userName ?? auth()->user()?->name ?? 'System',
                    'updated_by'       => $userName ?? auth()->user()?->name ?? 'System',
                ]);

                foreach ($processedDetails as $dtlData) {
                    \App\Models\Gudang\DatPakaiDtl::create([
                        'pakai_id'       => $pakaiHdr->pakai_id,
                        'barang_id'      => $dtlData['barang_id'],
                        'batch_no'       => $dtlData['batch_no'] ?: 'BATCH-DEF',
                        'grade_cd'       => 'A',
                        'qty_keluar'     => $dtlData['qty_aktual'],
                        'harga_satuan'   => $dtlData['harga_satuan'],
                        'total_harga'    => $dtlData['subtotal_nilai'],
                        'keterangan_txt' => "Konsumsi Pengemasan {$packNo}: {$hasilKarton} Karton {$targetBarangNm}",
                        'created_by'     => $userName ?? auth()->user()?->name ?? 'System',
                        'updated_by'     => $userName ?? auth()->user()?->name ?? 'System',
                    ]);
                }

                $header->update(['pakai_id' => $pakaiHdr->pakai_id]);
            } catch (Exception $e) {
                // Log atau lanjutkan jika tabel gudang memiliki kendala non-fatal
            }

            // 5. Tambah Stok Masuk Barang Jadi (FG) ke Gudang FG (Inbound)
            $ketIn = "Hasil Pengemasan Barang Jadi ({$packNo}) - HPP Rp " . number_format($hppPerKarton, 0, ',', '.') . "/Karton";
            $this->stokService->addStock(
                gudangId: $gudangId,
                barangId: $barangJadiId,
                batchNo: $batchNo,
                qty: $hasilKarton,
                expiredTgl: $expiredTgl,
                dokumenNo: $packNo,
                keterangan: $ketIn,
                hargaSatuan: $hppPerKarton
            );

            return $header;
        });
    }

    /**
     * Membatalkan (Void) Dokumen Pengemasan & Mengembalikan Mutasi Stok.
     */
    public function voidPackaging(int $packId, string $reason, ?string $userName = null): DatPackagingHdr
    {
        return DB::transaction(function () use ($packId, $reason, $userName) {
            $header = DatPackagingHdr::with(['details.barang', 'barangJadi'])->findOrFail($packId);

            if ($header->status_cd === 'VOID') {
                throw new Exception("Dokumen {$header->pack_no} sudah dalam status dibatalkan (VOID).");
            }

            // 1. Tarik kembali stok Barang Jadi (FG) yang sebelumnya ditambah
            $ketVoidFg = "Pembatalan Pengemasan ({$header->pack_no}): {$reason}";
            try {
                $this->stokService->deductStock(
                    gudangId: $header->gudang_id,
                    barangId: $header->barang_jadi_id,
                    batchNo: $header->batch_no,
                    qty: (float) $header->hasil_karton_qty,
                    dokumenNo: $header->pack_no,
                    keterangan: $ketVoidFg
                );
            } catch (Exception $e) {
                throw new Exception("Gagal membatalkan dokumen: Stok barang jadi telah terpakai atau tidak mencukupi untuk ditarik kembali ({$e->getMessage()}).");
            }

            // 2. Kembalikan stok bahan & WIP yang sebelumnya dipotong
            foreach ($header->details as $dtl) {
                $ketVoidBahan = "Pengembalian Konsumsi Bahan Batal Packaging ({$header->pack_no})";
                $this->stokService->addStock(
                    gudangId: $header->gudang_id,
                    barangId: $dtl->barang_id,
                    batchNo: $dtl->batch_no ?: 'RESTORE',
                    qty: (float) $dtl->qty_aktual,
                    expiredTgl: null,
                    dokumenNo: $header->pack_no,
                    keterangan: $ketVoidBahan,
                    hargaSatuan: (float) $dtl->harga_satuan
                );
            }

            // 3. Batalkan Dokumen Pemakaian Gudang yang otomatis terbentuk
            if ($header->pakai_id) {
                $pakaiHdr = \App\Models\Gudang\DatPakaiHdr::find($header->pakai_id);
                if ($pakaiHdr) {
                    $pakaiHdr->update([
                        'deleted_st'  => true,
                        'catatan_txt' => trim($pakaiHdr->catatan_txt . "\n[DIBATALKAN PENGEMASAN]: " . $reason),
                        'updated_by'  => $userName ?? auth()->user()?->name ?? 'System',
                    ]);
                    $pakaiHdr->details()->update([
                        'deleted_st' => true,
                        'updated_by' => $userName ?? auth()->user()?->name ?? 'System',
                    ]);
                }
            }

            // 3. Update Status Header
            $header->update([
                'status_cd'   => 'VOID',
                'catatan_txt' => trim($header->catatan_txt . "\n[DIBATALKAN]: " . $reason),
                'updated_by'  => $userName ?? auth()->user()?->name ?? 'System',
            ]);

            return $header;
        });
    }

    /**
     * Mengambil laporan bulanan konsolidasi HPP Pengemasan (Packaging) per Hari (1 s/d 31).
     */
    public function getMonthlyReport(int $year, int $month, ?int $barangJadiId = null): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $query = DatPackagingHdr::with([
            'barangJadi.satuanDasar',
            'gudang',
            'details.barang.satuanDasar'
        ])
            ->whereBetween('pack_tgl', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('deleted_st', false)
            ->where('status_cd', 'POSTED');

        if ($barangJadiId) {
            $query->where('barang_jadi_id', $barangJadiId);
        }

        $records = $query->orderBy('pack_tgl', 'asc')->orderBy('pack_id', 'asc')->get();

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $monthNames[$month] ?? 'Januari';

        $days = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateObj = Carbon::createFromDate($year, $month, $day);
            $dateStr = $dateObj->toDateString();
            $dayRecords = $records->filter(fn($r) => Carbon::parse($r->pack_tgl)->day === $day);

            $hariIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $hariNm = $hariIndo[$dateObj->dayOfWeek] ?? $dateObj->format('l');

            if ($dayRecords->isNotEmpty()) {
                $totalWipNilai = (float) $dayRecords->sum('total_biaya_wip');
                $totalKemasNilai = (float) $dayRecords->sum('total_biaya_kemasan');
                $totalTkNilai = (float) $dayRecords->sum('total_biaya_tenaga_kerja');
                $totalLainNilai = (float) $dayRecords->sum('total_biaya_lain');
                $totalBiaya = (float) $dayRecords->sum('total_biaya_produksi');
                $hasilKarton = (float) $dayRecords->sum('hasil_karton_qty');
                $hasilPcs = (float) $dayRecords->sum('hasil_pcs_qty');
                $targetKarton = (float) $dayRecords->sum('target_karton_qty');

                $hppKarton = $hasilKarton > 0 ? ($totalBiaya / $hasilKarton) : 0;
                $hppPcs = $hasilPcs > 0 ? ($totalBiaya / $hasilPcs) : 0;

                // Ekstrak rincian konsumsi bahan dan output Ping-Ping & Maksi
                $allDetails = collect();
                $wipKgTotal = 0;
                $kartonPcsTotal = 0;
                $plastikPcsTotal = 0;
                $lakbanRollTotal = 0;

                $berkoKg = 0;
                $wipKg = 0;
                $bahanKeripikNilai = 0;

                $kartonKkQty = 0;
                $kartonMkQty = 0;
                $kartonNilai = 0;

                $baladoKg = 0;
                $baladoNilai = 0;

                $rollKkKg = 0;
                $rollMkKg = 0;
                $rollNilai = 0;

                $lakbanJumlahGr = 0;
                $lakbanNilai = 0;

                $kertasExpQty = 0;
                $kertasExpNilai = 0;

                $kerusakanPlastikQty = 0;
                $kerusakanPlastikNilai = 0;

                $kerusakanKartonQty = 0;
                $kerusakanKartonNilai = 0;

                $hasilPingPing = 0;
                $hasilMaksi = 0;

                foreach ($dayRecords as $rec) {
                    $targetNm = strtoupper($rec->barangJadi?->barang_nm ?? '');
                    $isMaksi = str_contains($targetNm, 'MAKSI') || str_contains($targetNm, 'MX') || str_contains($targetNm, 'MK');

                    if ($isMaksi) {
                        $hasilMaksi += (float) $rec->hasil_pcs_qty;
                    } else {
                        $hasilPingPing += (float) $rec->hasil_pcs_qty;
                    }

                    foreach ($rec->details as $dtl) {
                        $allDetails->push($dtl);
                        $bNm = strtoupper($dtl->barang?->barang_nm ?? '');
                        $kat = strtoupper($dtl->kategori_bahan ?? '');
                        $sub = (float) $dtl->subtotal_nilai;
                        $qty = (float) $dtl->qty_aktual;

                        if ($kat === 'WIP' || str_contains($bNm, 'KS') || str_contains($bNm, 'BERKO') || str_contains($bNm, 'KERIPIK')) {
                            $wipKgTotal += $qty;
                            if (str_contains($bNm, 'BERKO')) {
                                $berkoKg += $qty;
                            } else {
                                $wipKg += $qty;
                            }
                            $bahanKeripikNilai += $sub;
                        } elseif ($kat === 'KARTON' || str_contains($bNm, 'KARTON') || str_contains($bNm, 'BOX') || str_contains($bNm, 'DUS')) {
                            $kartonPcsTotal += $qty;
                            if (str_contains($bNm, 'MAKSI') || str_contains($bNm, 'MX') || str_contains($bNm, 'MK')) {
                                $kartonMkQty += $qty;
                            } else {
                                $kartonKkQty += $qty;
                            }
                            $kartonNilai += $sub;
                        } elseif ($kat === 'BUMBU' || str_contains($bNm, 'BUMBU') || str_contains($bNm, 'BALADO')) {
                            $baladoKg += $qty;
                            $baladoNilai += $sub;
                        } elseif (str_contains($bNm, 'ROLL') || $kat === 'PLASTIK') {
                            $plastikPcsTotal += $qty;
                            if (str_contains($bNm, 'MAKSI') || str_contains($bNm, 'MX') || str_contains($bNm, 'MK')) {
                                $rollMkKg += $qty;
                            } else {
                                $rollKkKg += $qty;
                            }
                            $rollNilai += $sub;
                        } elseif (str_contains($bNm, 'LAKBAN') || $kat === 'LAKBAN') {
                            $lakbanRollTotal += $qty;
                            $lakbanJumlahGr += ($qty > 50 ? $qty : $qty * 100);
                            $lakbanNilai += $sub;
                        } elseif (str_contains($bNm, 'EXP') || str_contains($bNm, 'KERTAS')) {
                            $kertasExpQty += $qty;
                            $kertasExpNilai += $sub;
                        } elseif (str_contains($bNm, 'RUSAK') || str_contains($bNm, 'REJECT') || str_contains($bNm, 'AFKIR')) {
                            if (str_contains($bNm, 'KARTON')) {
                                $kerusakanKartonQty += $qty;
                                $kerusakanKartonNilai += $sub;
                            } else {
                                $kerusakanPlastikQty += $qty;
                                $kerusakanPlastikNilai += $sub;
                            }
                        } else {
                            $kartonNilai += $sub;
                        }
                    }
                }

                // Estimasi standar balado jika tidak diinput terpisah (5% dari Berko sesuai parameter Excel)
                if ($baladoKg == 0 && $berkoKg > 0) {
                    $baladoKg = round($berkoKg * 0.05, 2);
                    $baladoNilai = round($baladoKg * 39450, 0);
                }

                // Perhitungan lakban atas & bawah (Prioritaskan input timbangan riil Atas & Bawah)
                $hdrAtas = (float) $dayRecords->sum('lakban_atas_gr');
                $hdrBawah = (float) $dayRecords->sum('lakban_bawah_gr');
                $hdrNilai = (float) $dayRecords->sum('lakban_nilai');

                if ($hdrAtas > 0 || $hdrBawah > 0) {
                    $lakbanAtasGr = $hdrAtas;
                    $lakbanBawahGr = $hdrBawah;
                    $lakbanJumlahGr = $hdrAtas + $hdrBawah;
                    if ($hdrNilai > 0) {
                        $lakbanNilai = $hdrNilai;
                    }
                } else {
                    $lakbanAtasGr = round($lakbanJumlahGr / 2, 1);
                    $lakbanBawahGr = round($lakbanJumlahGr / 2, 1);
                }

                $hasilJumlah = $hasilPingPing + $hasilMaksi;
                if ($hasilJumlah == 0 && $hasilPcs > 0) {
                    $hasilPingPing = $hasilPcs;
                    $hasilJumlah = $hasilPcs;
                }

                // Gramasi bersih hasil kemasan (Kg) berdasarkan gramatur aktual dokumen
                $gramasiTotalKg = 0;
                foreach ($dayRecords as $rec) {
                    $grPcs = (float) $rec->gramatur_gr > 0 ? (float) $rec->gramatur_gr : 16.0;
                    $gramasiTotalKg += ((float) $rec->hasil_pcs_qty * ($grPcs / 1000));
                }
                $gramasiTotalKg = round($gramasiTotalKg, 2);
                if ($gramasiTotalKg == 0 && $wipKgTotal > 0) {
                    $gramasiTotalKg = $wipKgTotal;
                }

                $hppPerUnit = $hasilJumlah > 0 ? ($totalBiaya / $hasilJumlah) : 0;

                $productNames = $dayRecords->map(fn($r) => $r->barangJadi?->barang_nm)->unique()->filter()->values()->toArray();
                $batchList = $dayRecords->pluck('batch_no')->unique()->filter()->values()->toArray();
                $packNoList = $dayRecords->pluck('pack_no')->unique()->filter()->values()->toArray();

                $days[] = [
                    'day'                     => $day,
                    'date'                    => $dateStr,
                    'hari_nm'                 => $hariNm,
                    'has_data'                => true,
                    'count_dokumen'           => $dayRecords->count(),
                    'pack_records'            => $dayRecords,
                    'primary_pack_id'         => $dayRecords->first()->pack_id,
                    'primary_pack_no'         => $dayRecords->first()->pack_no,
                    'pack_no_list'            => $packNoList,
                    'product_names'           => $productNames,
                    'primary_product_nm'      => !empty($productNames) ? implode(', ', $productNames) : '-',
                    'batch_list'              => $batchList,
                    'primary_batch_no'        => !empty($batchList) ? implode(', ', $batchList) : '-',
                    'target_karton'           => $targetKarton,
                    'hasil_karton'            => $hasilKarton,
                    'hasil_pcs'               => $hasilPcs,
                    'total_biaya_wip'         => $totalWipNilai,
                    'total_biaya_kemasan'     => $totalKemasNilai,
                    'total_biaya_tenaga_kerja'=> $totalTkNilai,
                    'total_biaya_lain'        => $totalLainNilai,
                    'total_biaya_produksi'    => $totalBiaya,
                    'hpp_per_karton'          => $hppKarton,
                    'hpp_per_pcs'             => $hppPcs,
                    'wip_kg_total'            => $wipKgTotal,
                    'karton_pcs_total'        => $kartonPcsTotal,
                    'plastik_pcs_total'       => $plastikPcsTotal,
                    'lakban_roll_total'       => $lakbanRollTotal,

                    // 28 Atribut Kolom Spesifik Ping-Ping & Maksi
                    'berko_kg'                => $berkoKg,
                    'wip_kg'                  => $wipKg,
                    'bahan_keripik_nilai'     => $bahanKeripikNilai ?: $totalWipNilai,
                    'karton_kk_qty'           => $kartonKkQty,
                    'karton_mk_qty'           => $kartonMkQty,
                    'karton_xx_qty'           => $kartonKkQty,
                    'karton_mx_qty'           => $kartonMkQty,
                    'karton_nilai'            => $kartonNilai,
                    'balado_kg'               => $baladoKg,
                    'balado_nilai'            => $baladoNilai,
                    'roll_kk_kg'              => $rollKkKg,
                    'roll_mk_kg'              => $rollMkKg,
                    'roll_xx_kg'              => $rollKkKg,
                    'roll_mx_kg'              => $rollMkKg,
                    'roll_nilai'              => $rollNilai,
                    'lakban_atas_gr'          => $lakbanAtasGr,
                    'lakban_bawah_gr'         => $lakbanBawahGr,
                    'lakban_jumlah_gr'        => $lakbanJumlahGr,
                    'lakban_nilai'            => $lakbanNilai,
                    'kertas_exp_qty'          => $kertasExpQty,
                    'kertas_exp_nilai'        => $kertasExpNilai,
                    'kerusakan_plastik_qty'   => $kerusakanPlastikQty,
                    'kerusakan_plastik_nilai' => $kerusakanPlastikNilai,
                    'kerusakan_karton_qty'    => $kerusakanKartonQty,
                    'kerusakan_karton_nilai'  => $kerusakanKartonNilai,
                    'hasil_ping_ping'         => $hasilPingPing,
                    'hasil_maksi'             => $hasilMaksi,
                    'hasil_jumlah'            => $hasilJumlah,
                    'gramasi_kg'              => $gramasiTotalKg,
                    'hpp_per_unit'            => $hppPerUnit ?: $hppPcs,

                    'details'                 => $allDetails,
                ];
            } else {
                $days[] = [
                    'day'                     => $day,
                    'date'                    => $dateStr,
                    'hari_nm'                 => $hariNm,
                    'has_data'                => false,
                    'count_dokumen'           => 0,
                    'pack_records'            => collect(),
                    'primary_pack_id'         => null,
                    'primary_pack_no'         => null,
                    'pack_no_list'            => [],
                    'product_names'           => [],
                    'primary_product_nm'      => '-',
                    'batch_list'              => [],
                    'primary_batch_no'        => '-',
                    'target_karton'           => 0,
                    'hasil_karton'            => 0,
                    'hasil_pcs'               => 0,
                    'total_biaya_wip'         => 0,
                    'total_biaya_kemasan'     => 0,
                    'total_biaya_tenaga_kerja'=> 0,
                    'total_biaya_lain'        => 0,
                    'total_biaya_produksi'    => 0,
                    'hpp_per_karton'          => 0,
                    'hpp_per_pcs'             => 0,
                    'wip_kg_total'            => 0,
                    'karton_pcs_total'        => 0,
                    'plastik_pcs_total'       => 0,
                    'lakban_roll_total'       => 0,

                    // 28 Atribut Kolom Spesifik Ping-Ping & Maksi (Kosong)
                    'berko_kg'                => 0,
                    'wip_kg'                  => 0,
                    'bahan_keripik_nilai'     => 0,
                    'karton_kk_qty'           => 0,
                    'karton_mk_qty'           => 0,
                    'karton_xx_qty'           => 0,
                    'karton_mx_qty'           => 0,
                    'karton_nilai'            => 0,
                    'balado_kg'               => 0,
                    'balado_nilai'            => 0,
                    'roll_kk_kg'              => 0,
                    'roll_mk_kg'              => 0,
                    'roll_xx_kg'              => 0,
                    'roll_mx_kg'              => 0,
                    'roll_nilai'              => 0,
                    'lakban_atas_gr'          => 0,
                    'lakban_bawah_gr'         => 0,
                    'lakban_jumlah_gr'        => 0,
                    'lakban_nilai'            => 0,
                    'kertas_exp_qty'          => 0,
                    'kertas_exp_nilai'        => 0,
                    'kerusakan_plastik_qty'   => 0,
                    'kerusakan_plastik_nilai' => 0,
                    'kerusakan_karton_qty'    => 0,
                    'kerusakan_karton_nilai'  => 0,
                    'hasil_ping_ping'         => 0,
                    'hasil_maksi'             => 0,
                    'hasil_jumlah'            => 0,
                    'gramasi_kg'              => 0,
                    'hpp_per_unit'            => 0,

                    'details'                 => collect(),
                ];
            }
        }

        // Totals & Averages
        $activeDays = collect($days)->where('has_data', true);
        $totalWip = (float) $activeDays->sum('total_biaya_wip');
        $totalKemasan = (float) $activeDays->sum('total_biaya_kemasan');
        $totalTk = (float) $activeDays->sum('total_biaya_tenaga_kerja');
        $totalLain = (float) $activeDays->sum('total_biaya_lain');
        $totalBiaya = (float) $activeDays->sum('total_biaya_produksi');
        $totalKarton = (float) $activeDays->sum('hasil_karton');
        $totalPcs = (float) $activeDays->sum('hasil_pcs');
        $totalWipKg = (float) $activeDays->sum('wip_kg_total');

        $rataHppKarton = $totalKarton > 0 ? round($totalBiaya / $totalKarton, 2) : 0;
        $rataHppPcs = $totalPcs > 0 ? round($totalBiaya / $totalPcs, 2) : 0;
        $biayaKemasPerKarton = $totalKarton > 0 ? round($totalKemasan / $totalKarton, 2) : 0;

        // Akumulasi Matriks Ping-Ping & Maksi
        $totBerkoKg = (float) $activeDays->sum('berko_kg');
        $totWipKg = (float) $activeDays->sum('wip_kg');
        $totBahanKeripikNilai = (float) $activeDays->sum('bahan_keripik_nilai');
        $totKartonKk = (float) $activeDays->sum('karton_kk_qty');
        $totKartonMk = (float) $activeDays->sum('karton_mk_qty');
        $totKartonNilai = (float) $activeDays->sum('karton_nilai');
        $totBaladoKg = (float) $activeDays->sum('balado_kg');
        $totBaladoNilai = (float) $activeDays->sum('balado_nilai');
        $totRollKkKg = (float) $activeDays->sum('roll_kk_kg');
        $totRollMkKg = (float) $activeDays->sum('roll_mk_kg');
        $totRollNilai = (float) $activeDays->sum('roll_nilai');
        $totLakbanAtas = (float) $activeDays->sum('lakban_atas_gr');
        $totLakbanBawah = (float) $activeDays->sum('lakban_bawah_gr');
        $totLakbanJumlah = (float) $activeDays->sum('lakban_jumlah_gr');
        $totLakbanNilai = (float) $activeDays->sum('lakban_nilai');
        $totKertasExpQty = (float) $activeDays->sum('kertas_exp_qty');
        $totKertasExpNilai = (float) $activeDays->sum('kertas_exp_nilai');
        $totKerusakanPlastikQty = (float) $activeDays->sum('kerusakan_plastik_qty');
        $totKerusakanPlastikNilai = (float) $activeDays->sum('kerusakan_plastik_nilai');
        $totKerusakanKartonQty = (float) $activeDays->sum('kerusakan_karton_qty');
        $totKerusakanKartonNilai = (float) $activeDays->sum('kerusakan_karton_nilai');
        $totHasilPingPing = (float) $activeDays->sum('hasil_ping_ping');
        $totHasilMaksi = (float) $activeDays->sum('hasil_maksi');
        $totHasilJumlah = (float) $activeDays->sum('hasil_jumlah');
        $totGramasiKg = (float) $activeDays->sum('gramasi_kg');

        $activeCount = $activeDays->count();
        $avgBerkoKg = $activeCount > 0 ? round($totBerkoKg / $activeCount, 2) : 0;
        $avgBahanNilai = $activeCount > 0 ? round($totBahanKeripikNilai / $activeCount, 0) : 0;
        $avgKartonNilai = $activeCount > 0 ? round($totKartonNilai / $activeCount, 0) : 0;
        $avgTotalBiaya = $activeCount > 0 ? round($totalBiaya / $activeCount, 0) : 0;
        $avgHasilJumlah = $activeCount > 0 ? round($totHasilJumlah / $activeCount, 0) : 0;
        $avgGramasiKg = $activeCount > 0 ? round($totGramasiKg / $activeCount, 2) : 0;
        $rataHppUnit = $totHasilJumlah > 0 ? round($totalBiaya / $totHasilJumlah, 2) : 0;

        // Parameter Standar Acuan Harga (Daftar Harga Atas Excel) diambil dinamis dari Master Standar Harga Kemasan
        $daftarHarga = $this->tarifKemasanService->getDaftarHargaPackaging();

        return [
            'year'                   => $year,
            'month'                  => $month,
            'month_name'             => $monthName,
            'count'                  => $activeDays->count(),
            'total_dokumen'          => $records->count(),
            'records'                => $records,
            'days'                   => $days,
            'daftar_harga'           => $daftarHarga,
            'totals'                 => [
                'total_biaya_wip'          => $totalWip,
                'total_biaya_kemasan'      => $totalKemasan,
                'total_biaya_tenaga_kerja' => $totalTk,
                'total_biaya_lain'         => $totalLain,
                'total_biaya_produksi'     => $totalBiaya,
                'total_karton'             => $totalKarton,
                'total_pcs'                => $totalPcs,
                'total_wip_kg'             => $totalWipKg,
                'rata_hpp_karton'          => $rataHppKarton,
                'rata_hpp_pcs'             => $rataHppPcs,
                'biaya_kemas_per_karton'   => $biayaKemasPerKarton,

                // Akumulasi Matriks Ping-Ping & Maksi
                'tot_berko_kg'             => $totBerkoKg,
                'tot_wip_kg'               => $totWipKg,
                'tot_bahan_keripik_nilai'  => $totBahanKeripikNilai,
                'tot_karton_kk'            => $totKartonKk,
                'tot_karton_mk'            => $totKartonMk,
                'tot_karton_xx'            => $totKartonKk,
                'tot_karton_mx'            => $totKartonMk,
                'tot_karton_nilai'         => $totKartonNilai,
                'tot_balado_kg'            => $totBaladoKg,
                'tot_balado_nilai'         => $totBaladoNilai,
                'tot_roll_kk_kg'           => $totRollKkKg,
                'tot_roll_mk_kg'           => $totRollMkKg,
                'tot_roll_xx_kg'           => $totRollKkKg,
                'tot_roll_mx_kg'           => $totRollMkKg,
                'tot_roll_nilai'           => $totRollNilai,
                'tot_lakban_atas'          => $totLakbanAtas,
                'tot_lakban_bawah'         => $totLakbanBawah,
                'tot_lakban_jumlah'        => $totLakbanJumlah,
                'tot_lakban_nilai'         => $totLakbanNilai,
                'tot_kertas_exp_qty'       => $totKertasExpQty,
                'tot_kertas_exp_nilai'     => $totKertasExpNilai,
                'tot_kerusakan_plastik_qty'=> $totKerusakanPlastikQty,
                'tot_kerusakan_plastik_nilai'=> $totKerusakanPlastikNilai,
                'tot_kerusakan_karton_qty' => $totKerusakanKartonQty,
                'tot_kerusakan_karton_nilai'=> $totKerusakanKartonNilai,
                'tot_hasil_ping_ping'      => $totHasilPingPing,
                'tot_hasil_maksi'          => $totHasilMaksi,
                'tot_hasil_jumlah'         => $totHasilJumlah,
                'tot_gramasi_kg'           => $totGramasiKg,
                'rata_hpp_unit'            => $rataHppUnit,
            ],
            'averages'               => [
                'avg_berko_kg'             => $avgBerkoKg,
                'avg_bahan_nilai'          => $avgBahanNilai,
                'avg_karton_nilai'         => $avgKartonNilai,
                'avg_total_biaya'          => $avgTotalBiaya,
                'avg_hasil_jumlah'         => $avgHasilJumlah,
                'avg_gramasi_kg'           => $avgGramasiKg,
                'avg_hpp_unit'             => $rataHppUnit,
            ],
        ];
    }

    /**
     * Mengambil laporan tahunan konsolidasi HPP Pengemasan (Packaging) per Bulan (Januari s/d Desember).
     */
    public function getYearlyReport(int $year, ?int $barangJadiId = null): array
    {
        $query = DatPackagingHdr::with(['barangJadi'])
            ->whereYear('pack_tgl', $year)
            ->where('deleted_st', false)
            ->where('status_cd', 'POSTED');

        if ($barangJadiId) {
            $query->where('barang_jadi_id', $barangJadiId);
        }

        $allRecords = $query->orderBy('pack_tgl', 'asc')->get();

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthRecords = $allRecords->filter(fn($r) => Carbon::parse($r->pack_tgl)->month === $m);
            $count = $monthRecords->count();
            $totalWip = (float) $monthRecords->sum('total_biaya_wip');
            $totalKemasan = (float) $monthRecords->sum('total_biaya_kemasan');
            $totalTk = (float) $monthRecords->sum('total_biaya_tenaga_kerja');
            $totalLain = (float) $monthRecords->sum('total_biaya_lain');
            $totalBiaya = (float) $monthRecords->sum('total_biaya_produksi');
            $totalKarton = (float) $monthRecords->sum('hasil_karton_qty');
            $totalPcs = (float) $monthRecords->sum('hasil_pcs_qty');

            $hppKarton = $totalKarton > 0 ? round($totalBiaya / $totalKarton, 2) : 0;
            $hppPcs = $totalPcs > 0 ? round($totalBiaya / $totalPcs, 2) : 0;

            $months[$m] = [
                'month_num'               => $m,
                'month_name'              => $monthNames[$m],
                'has_data'                => $count > 0,
                'count_dokumen'           => $count,
                'total_biaya_wip'         => $totalWip,
                'total_biaya_kemasan'     => $totalKemasan,
                'total_biaya_tenaga_kerja'=> $totalTk,
                'total_biaya_lain'        => $totalLain,
                'total_biaya_produksi'    => $totalBiaya,
                'total_karton'            => $totalKarton,
                'total_pcs'               => $totalPcs,
                'hpp_per_karton'          => $hppKarton,
                'hpp_per_pcs'             => $hppPcs,
            ];
        }

        $annualBiaya = (float) $allRecords->sum('total_biaya_produksi');
        $annualKarton = (float) $allRecords->sum('hasil_karton_qty');
        $annualPcs = (float) $allRecords->sum('hasil_pcs_qty');
        $annualWip = (float) $allRecords->sum('total_biaya_wip');
        $annualKemasan = (float) $allRecords->sum('total_biaya_kemasan');
        $annualTk = (float) $allRecords->sum('total_biaya_tenaga_kerja');
        $annualLain = (float) $allRecords->sum('total_biaya_lain');

        return [
            'year'       => $year,
            'months'     => $months,
            'totals'     => [
                'annual_biaya'          => $annualBiaya,
                'annual_karton'         => $annualKarton,
                'annual_pcs'            => $annualPcs,
                'annual_wip'            => $annualWip,
                'annual_kemasan'        => $annualKemasan,
                'annual_tk'             => $annualTk,
                'annual_lain'           => $annualLain,
                'annual_hpp_karton'     => $annualKarton > 0 ? round($annualBiaya / $annualKarton, 2) : 0,
                'annual_hpp_pcs'        => $annualPcs > 0 ? round($annualBiaya / $annualPcs, 2) : 0,
                'annual_count'          => $allRecords->count(),
            ],
        ];
    }
}
