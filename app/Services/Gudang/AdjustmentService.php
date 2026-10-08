<?php

namespace App\Services\Gudang;

use App\Models\Gudang\DatAdjustmentDtl;
use App\Models\Gudang\DatAdjustmentHdr;
use App\Models\Gudang\DatStokBatch;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Services\Common\CodeGeneratorService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdjustmentService
{
    public function __construct(
        protected StokService $stokService,
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Mengambil query daftar rincian adjustment sesuai blueprint.
     */
    public function getDetailsQuery(array $filters = []): Builder
    {
        $query = DatAdjustmentDtl::with(['header.gudang', 'barang.satuanDasar'])
            ->whereHas('header', function (Builder $q) {
                $q->where('deleted_st', false);
            });

        // Filter Pencarian Teks (Search: Nama Barang atau Kode Barang atau No Dokumen)
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->whereHas('barang', function (Builder $bq) use ($search) {
                    $bq->where('barang_nm', 'ILIKE', "%{$search}%")
                       ->orWhere('barang_cd', 'ILIKE', "%{$search}%");
                })->orWhereHas('header', function (Builder $hq) use ($search) {
                    $hq->where('adj_no', 'ILIKE', "%{$search}%")
                       ->orWhere('catatan_txt', 'ILIKE', "%{$search}%");
                });
            });
        }

        // Filter Spesifik: Nama Barang
        if (!empty($filters['barang_nm'])) {
            $nm = trim($filters['barang_nm']);
            $query->whereHas('barang', function (Builder $bq) use ($nm) {
                $bq->where('barang_nm', 'ILIKE', "%{$nm}%");
            });
        }

        // Filter Spesifik: Kode Barang
        if (!empty($filters['barang_cd'])) {
            $cd = trim($filters['barang_cd']);
            $query->whereHas('barang', function (Builder $bq) use ($cd) {
                $bq->where('barang_cd', 'ILIKE', "%{$cd}%");
            });
        }

        // Filter Tanggal
        if (!empty($filters['tgl_mulai']) && !empty($filters['tgl_selesai'])) {
            $query->whereHas('header', function (Builder $hq) use ($filters) {
                $hq->whereBetween('adj_tgl', [$filters['tgl_mulai'], $filters['tgl_selesai']]);
            });
        } elseif (!empty($filters['adj_tgl'])) {
            $query->whereHas('header', function (Builder $hq) use ($filters) {
                $hq->whereDate('adj_tgl', $filters['adj_tgl']);
            });
        }

        // Filter Gudang
        if (!empty($filters['gudang_id'])) {
            $query->whereHas('header', function (Builder $hq) use ($filters) {
                $hq->where('gudang_id', $filters['gudang_id']);
            });
        }

        // Filter Tipe Selisih
        if (!empty($filters['tipe_selisih'])) {
            if ($filters['tipe_selisih'] === 'DEFISIT') {
                $query->where('selisih_qty', '<', 0);
            } elseif ($filters['tipe_selisih'] === 'SURPLUS') {
                $query->where('selisih_qty', '>', 0);
            } elseif ($filters['tipe_selisih'] === 'MATCH') {
                $query->where('selisih_qty', '=', 0);
            }
        }

        return $query->orderByDesc('created_at');
    }

    /**
     * Mengambil ringkasan metrik statistik Adjustment.
     */
    public function getMetrics(array $filters = []): array
    {
        $base = DatAdjustmentDtl::whereHas('header', function (Builder $q) use ($filters) {
            $q->where('deleted_st', false);
            if (!empty($filters['gudang_id'])) {
                $q->where('gudang_id', $filters['gudang_id']);
            }
            if (!empty($filters['tgl_mulai']) && !empty($filters['tgl_selesai'])) {
                $q->whereBetween('adj_tgl', [$filters['tgl_mulai'], $filters['tgl_selesai']]);
            }
        });

        $totalDokumen = DatAdjustmentHdr::active()
            ->when(!empty($filters['gudang_id']), fn($q) => $q->where('gudang_id', $filters['gudang_id']))
            ->when(!empty($filters['tgl_mulai']) && !empty($filters['tgl_selesai']), fn($q) => $q->whereBetween('adj_tgl', [$filters['tgl_mulai'], $filters['tgl_selesai']]))
            ->count();

        $totalItem = (clone $base)->count();

        $totalDefisitNilai = (float) (clone $base)
            ->where('selisih_qty', '<', 0)
            ->sum('total_selisih_nilai');

        $totalSurplusNilai = (float) (clone $base)
            ->where('selisih_qty', '>', 0)
            ->sum('total_selisih_nilai');

        $netSelisihNilai = (float) (clone $base)->sum('total_selisih_nilai');

        return [
            'total_dokumen'        => $totalDokumen,
            'total_item'           => $totalItem,
            'total_defisit_nilai'  => abs($totalDefisitNilai),
            'total_surplus_nilai'  => $totalSurplusNilai,
            'net_selisih_nilai'    => $netSelisihNilai,
        ];
    }

    /**
     * Mengambil informasi real-time stok sistem & harga untuk suatu barang di gudang tertentu.
     */
    public function getStokAndHargaInfo(int $gudangId, int $barangId): array
    {
        $barang = MstBarang::with('satuanDasar')->findOrFail($barangId);

        // Ambil total stok on-hand aktif di gudang tersebut
        $stokSistem = (float) DatStokBatch::where('gudang_id', $gudangId)
            ->where('barang_id', $barangId)
            ->where('deleted_st', false)
            ->sum('sisa_qty');

        // Ambil harga satuan rata-rata atau batch terakhir atau standar
        $latestBatch = DatStokBatch::where('gudang_id', $gudangId)
            ->where('barang_id', $barangId)
            ->where('harga_satuan', '>', 0)
            ->orderByDesc('created_at')
            ->first();

        $hargaSatuan = $latestBatch ? (float) $latestBatch->harga_satuan : (float) $barang->harga_beli_standar;

        // Ambil daftar batch aktif jika ada
        $batches = DatStokBatch::where('gudang_id', $gudangId)
            ->where('barang_id', $barangId)
            ->where('sisa_qty', '>', 0)
            ->orderBy('batch_no')
            ->get(['batch_no', 'sisa_qty', 'harga_satuan', 'expired_tgl']);

        return [
            'barang_id'         => $barang->barang_id,
            'barang_cd'         => $barang->barang_cd,
            'barang_nm'         => $barang->barang_nm,
            'satuan_cd'         => $barang->satuanDasar->satuan_cd ?? 'KG',
            'stok_sistem_qty'   => $stokSistem,
            'harga_satuan'      => $hargaSatuan,
            'total_sistem_nilai'=> round($stokSistem * $hargaSatuan, 2),
            'batches'           => $batches,
        ];
    }

    /**
     * Membuat dan memposting dokumen Penyesuaian Stok (Adjustment).
     * Wajib DB::transaction() sesuai AGENTS.md.
     */
    public function storeAdjustment(array $data, ?string $userName = null): DatAdjustmentHdr
    {
        return DB::transaction(function () use ($data, $userName) {
            $gudangId = (int) $data['gudang_id'];
            $adjTgl = $data['adj_tgl'] ?? now()->format('Y-m-d');
            $kategoriAdj = $data['kategori_adj'] ?? 'OPNAME_RUTIN';
            $catatanTxt = $data['catatan_txt'] ?? null;
            $items = $data['items'] ?? [];

            if (empty($items)) {
                throw new Exception("Minimal harus ada satu barang yang disesuaikan.");
            }

            // Generate Nomor Dokumen: ADJ-YYYYMM-0001
            $prefix = 'ADJ-' . date('Ym', strtotime($adjTgl)) . '-';
            $adjNo = $this->codeGenerator->generate('dat_adjustment_hdr', 'adj_no', $prefix, 4);

            $totalItem = 0;
            $totalSelisihQty = 0;
            $totalSelisihNilai = 0;

            // 1. Buat Header Dokumen
            $header = DatAdjustmentHdr::create([
                'adj_no'             => $adjNo,
                'adj_tgl'            => $adjTgl,
                'gudang_id'          => $gudangId,
                'kategori_adj'       => $kategoriAdj,
                'catatan_txt'        => $catatanTxt,
                'total_item'         => count($items),
                'total_selisih_qty'  => 0,
                'total_selisih_nilai'=> 0,
                'status_cd'          => 'POSTED',
                'created_by'         => $userName ?? auth()->user()?->name ?? 'System',
                'updated_by'         => $userName ?? auth()->user()?->name ?? 'System',
            ]);

            // 2. Proses Setiap Item Barang
            foreach ($items as $item) {
                $barangId = (int) $item['barang_id'];
                $batchNo = !empty($item['batch_no']) ? trim($item['batch_no']) : null;
                $stokSistem = (float) ($item['stok_sistem_qty'] ?? 0);
                $hargaSatuan = (float) ($item['harga_satuan'] ?? 0);
                $stokFisik = (float) ($item['stok_fisik_qty'] ?? 0);
                $alasanTxt = !empty($item['alasan_txt']) ? trim($item['alasan_txt']) : null;

                // Perhitungan matematis blueprint
                // f = d * e
                $totalSistemNilai = round($stokSistem * $hargaSatuan, 2);
                // i = g * h
                $totalFisikNilai = round($stokFisik * $hargaSatuan, 2);
                // j = g - d
                $selisihQty = $stokFisik - $stokSistem;
                // l = j * k
                $totalSelisihNilaiItem = round($selisihQty * $hargaSatuan, 2);

                $totalItem++;
                $totalSelisihQty += $selisihQty;
                $totalSelisihNilai += $totalSelisihNilaiItem;

                // Simpan Detail
                DatAdjustmentDtl::create([
                    'adj_id'             => $header->adj_id,
                    'barang_id'          => $barangId,
                    'batch_no'           => $batchNo,
                    'stok_sistem_qty'    => $stokSistem,
                    'harga_satuan'       => $hargaSatuan,
                    'total_sistem_nilai' => $totalSistemNilai,
                    'stok_fisik_qty'     => $stokFisik,
                    'total_fisik_nilai'  => $totalFisikNilai,
                    'selisih_qty'        => $selisihQty,
                    'total_selisih_nilai'=> $totalSelisihNilaiItem,
                    'alasan_txt'         => $alasanTxt,
                    'created_by'         => $userName ?? auth()->user()?->name ?? 'System',
                    'updated_by'         => $userName ?? auth()->user()?->name ?? 'System',
                ]);

                // Eksekusi Pembaruan Stok Nyata ke StokService & Ledger
                if ($selisihQty > 0) {
                    // Surplus: Stok Fisik lebih banyak -> Tambahkan stok ke sistem
                    $batchTarget = $batchNo ?: ('ADJ-' . date('ymd', strtotime($adjTgl)) . '-' . $barangId);
                    $ket = "Penyesuaian Stok Surplus ({$adjNo})" . ($alasanTxt ? ": {$alasanTxt}" : "");

                    $this->stokService->addStock(
                        gudangId: $gudangId,
                        barangId: $barangId,
                        batchNo: $batchTarget,
                        qty: $selisihQty,
                        expiredTgl: null,
                        dokumenNo: $adjNo,
                        keterangan: $ket,
                        hargaSatuan: $hargaSatuan
                    );
                } elseif ($selisihQty < 0) {
                    // Defisit (Penyusutan/Kerusakan): Kurangi stok dari sistem
                    $qtyToDeduct = abs($selisihQty);
                    $ket = "Penyesuaian Stok Susut/Defisit ({$adjNo})" . ($alasanTxt ? ": {$alasanTxt}" : "");

                    if ($batchNo) {
                        // Jika spesifik nomor batch
                        $this->stokService->deductStock(
                            gudangId: $gudangId,
                            barangId: $barangId,
                            batchNo: $batchNo,
                            qty: $qtyToDeduct,
                            dokumenNo: $adjNo,
                            keterangan: $ket
                        );
                    } else {
                        // Jika tidak ada nomor batch spesifik, gunakan FIFO pemotongan batch yang ada
                        $this->stokService->deductStockFifo(
                            gudangId: $gudangId,
                            barangId: $barangId,
                            totalQty: $qtyToDeduct,
                            dokumenNo: $adjNo,
                            keterangan: $ket
                        );
                    }
                }
            }

            // Update akumulasi total di Header
            $header->update([
                'total_item'          => $totalItem,
                'total_selisih_qty'   => $totalSelisihQty,
                'total_selisih_nilai' => $totalSelisihNilai,
            ]);

            return $header;
        });
    }

    /**
     * Membatalkan (Void) Dokumen Adjustment & Mengembalikan mutasi stok.
     */
    public function voidAdjustment(int $adjId, ?string $reason = null, ?string $userName = null): DatAdjustmentHdr
    {
        return DB::transaction(function () use ($adjId, $reason, $userName) {
            $header = DatAdjustmentHdr::with('details')->findOrFail($adjId);

            if ($header->status_cd === 'VOID') {
                throw new Exception("Dokumen penyesuaian ini sudah dibatalkan sebelumnya.");
            }

            $user = $userName ?? auth()->user()?->name ?? 'System';

            // Rollback mutasi stok untuk tiap detail
            foreach ($header->details as $dtl) {
                $selisihQty = (float) $dtl->selisih_qty;
                $barangId = $dtl->barang_id;
                $gudangId = $header->gudang_id;
                $dokVoid = "VOID-" . $header->adj_no;

                if ($selisihQty > 0) {
                    // Waktu itu surplus (tambah stok), sekarang kurangi kembali
                    $batchTarget = $dtl->batch_no ?: ('ADJ-' . date('ymd', strtotime($header->adj_tgl)) . '-' . $barangId);
                    $this->stokService->deductStock(
                        gudangId: $gudangId,
                        barangId: $barangId,
                        batchNo: $batchTarget,
                        qty: $selisihQty,
                        dokumenNo: $dokVoid,
                        keterangan: "Pembatalan Adjustment Masuk {$header->adj_no}: " . ($reason ?? 'Batal Opname')
                    );
                } elseif ($selisihQty < 0) {
                    // Waktu itu defisit (kurangi stok), sekarang kembalikan stok
                    $qtyToRestore = abs($selisihQty);
                    $batchTarget = $dtl->batch_no ?: ('ADJ-RESTORE-' . $barangId);
                    $this->stokService->addStock(
                        gudangId: $gudangId,
                        barangId: $barangId,
                        batchNo: $batchTarget,
                        qty: $qtyToRestore,
                        expiredTgl: null,
                        dokumenNo: $dokVoid,
                        keterangan: "Pembatalan Adjustment Susut {$header->adj_no}: " . ($reason ?? 'Batal Opname'),
                        hargaSatuan: (float) $dtl->harga_satuan
                    );
                }
            }

            $header->update([
                'status_cd'   => 'VOID',
                'catatan_txt' => trim($header->catatan_txt . " [DIBATALKAN: " . ($reason ?? 'Tanpa alasan') . "]"),
                'updated_by'  => $user,
            ]);

            return $header;
        });
    }
}
