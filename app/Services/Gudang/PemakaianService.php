<?php

namespace App\Services\Gudang;

use App\Models\Gudang\DatPakaiDtl;
use App\Models\Gudang\DatPakaiHdr;
use App\Models\Gudang\DatStokBatch;
use App\Models\MasterData\MstBarang;
use App\Services\Common\CodeGeneratorService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PemakaianService
{
    public function __construct(
        protected StokService $stokService,
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Mengambil daftar header pemakaian / pengeluaran barang.
     */
    public function getAllPaginated(
        int $perPage = 15,
        ?string $search = null,
        int|array|null $gudangId = null,
        ?string $tujuan = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): LengthAwarePaginator {
        $query = DatPakaiHdr::with(['gudang', 'details.barang.satuanDasar', 'details.barang.jenisBarang'])
            ->where('deleted_st', false);

        if (is_array($gudangId)) {
            $query->whereIn('gudang_id', $gudangId);
        } elseif ($gudangId !== null) {
            $query->where('gudang_id', $gudangId);
        }

        if (!empty($tujuan)) {
            $query->where('tujuan_pemakaian', $tujuan);
        }

        if (!empty($startDate)) {
            $query->whereDate('pakai_tgl', '>=', $startDate);
        }

        if (!empty($endDate)) {
            $query->whereDate('pakai_tgl', '<=', $endDate);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('pakai_no', 'ILIKE', "%{$search}%")
                  ->orWhere('tujuan_pemakaian', 'ILIKE', "%{$search}%")
                  ->orWhere('catatan_txt', 'ILIKE', "%{$search}%");
            });
        }

        return $query->orderBy('pakai_tgl', 'desc')
            ->orderBy('pakai_id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Mengambil daftar per-item barang keluar sesuai Sheet "Barang Keluar" di Excel operasional:
     * Kolom: Tanggal | Kode Batch | Kode Barang | Nama Barang | Jenis | Keterangan | Qty Keluar | Harga Satuan | Total Harga
     */
    public function getBarangKeluarListPaginated(
        int $perPage = 25,
        ?string $search = null,
        int|array|null $gudangId = null,
        ?string $tujuan = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $kategori = null
    ): LengthAwarePaginator {
        $query = DatPakaiDtl::with(['header.gudang', 'barang.jenisBarang', 'barang.satuanDasar'])
            ->whereHas('header', function ($q) use ($gudangId, $tujuan, $startDate, $endDate) {
                $q->where('deleted_st', false);
                if (is_array($gudangId)) {
                    $q->whereIn('gudang_id', $gudangId);
                } elseif ($gudangId !== null) {
                    $q->where('gudang_id', $gudangId);
                }
                if (!empty($tujuan)) {
                    $q->where('tujuan_pemakaian', $tujuan);
                }
                if (!empty($startDate)) {
                    $q->whereDate('pakai_tgl', '>=', $startDate);
                }
                if (!empty($endDate)) {
                    $q->whereDate('pakai_tgl', '<=', $endDate);
                }
            });

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('batch_no', 'ILIKE', "%{$search}%")
                  ->orWhere('keterangan_txt', 'ILIKE', "%{$search}%")
                  ->orWhereHas('barang', function ($bq) use ($search) {
                      $bq->where('barang_nm', 'ILIKE', "%{$search}%")
                         ->orWhere('barang_cd', 'ILIKE', "%{$search}%");
                  })
                  ->orWhereHas('header', function ($hq) use ($search) {
                      $hq->where('tujuan_pemakaian', 'ILIKE', "%{$search}%")
                         ->orWhere('pakai_no', 'ILIKE', "%{$search}%");
                  });
            });
        }

        if (!empty($kategori)) {
            $query->whereHas('barang', function ($bq) use ($kategori) {
                if ($kategori === 'BAHAN_BAKU') {
                    $bq->where(function ($sq) {
                        $sq->whereHas('jenisBarang', fn($jq) => $jq->whereIn('jenis_barang_cd', ['BB', 'RAW']))
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%SINGKONG%'")
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%UBI%'")
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%OPAK%'")
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%PUYUR%'");
                    });
                } elseif ($kategori === 'KEMASAN') {
                    $bq->where(function ($sq) {
                        $sq->whereHas('jenisBarang', fn($jq) => $jq->where('jenis_barang_cd', 'PACK'))
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%KARTON%'")
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%PLASTIK%'")
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%ROLL%'")
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%LAKBAN%'")
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%RAFIA%'")
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%SARUNG TANGAN%'");
                    });
                } elseif ($kategori === 'BAHAN_PENOLONG') {
                    $bq->where(function ($sq) {
                        $sq->whereRaw("UPPER(barang_nm) LIKE '%BUMBU%'")
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%MINYAK%'")
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%PERENYAH%'")
                           ->orWhereRaw("UPPER(barang_nm) LIKE '%GARAM%'")
                           ->orWhere(function ($sub) {
                               $sub->whereHas('jenisBarang', fn($jq) => $jq->whereIn('jenis_barang_cd', ['BP', 'SUPP']))
                                   ->whereRaw("UPPER(barang_nm) NOT LIKE '%KARTON%'")
                                   ->whereRaw("UPPER(barang_nm) NOT LIKE '%PLASTIK%'")
                                   ->whereRaw("UPPER(barang_nm) NOT LIKE '%LAKBAN%'")
                                   ->whereRaw("UPPER(barang_nm) NOT LIKE '%ROLL%'")
                                   ->whereRaw("UPPER(barang_nm) NOT LIKE '%RAFIA%'");
                           });
                    });
                }
            });
        }

        $paginated = $query->orderBy('pakaidtl_id', 'desc')->paginate($perPage);

        // Kumpulkan ID gudang dan barang untuk kalkulasi sisa stok
        $items = $paginated->items();
        if (!empty($items)) {
            $gudangIds = [];
            $barangIds = [];

            foreach ($items as $item) {
                $gId = $item->header?->gudang_id;
                $bId = $item->barang_id;

                if ($gId) {
                    $gudangIds[$gId] = $gId;
                }
                if ($bId) {
                    $barangIds[$bId] = $bId;
                }
            }

            // 1. Ambil total sisa stok barang di gudang terkait
            $totalStokMap = DatStokBatch::whereIn('gudang_id', array_values($gudangIds))
                ->whereIn('barang_id', array_values($barangIds))
                ->where('deleted_st', false)
                ->selectRaw('gudang_id, barang_id, SUM(sisa_qty) as total_sisa')
                ->groupBy('gudang_id', 'barang_id')
                ->get()
                ->keyBy(fn($r) => $r->gudang_id . '_' . $r->barang_id);

            // 2. Ambil sisa stok per batch spesifik & grade
            $batchStokMap = DatStokBatch::whereIn('gudang_id', array_values($gudangIds))
                ->whereIn('barang_id', array_values($barangIds))
                ->where('deleted_st', false)
                ->get()
                ->keyBy(fn($r) => $r->gudang_id . '_' . $r->barang_id . '_' . $r->batch_no . '_' . ($r->grade_cd ?? 'A'));

            // Assign ke setiap item
            foreach ($items as $item) {
                $gId = $item->header?->gudang_id;
                $bId = $item->barang_id;
                $bt = $item->batch_no;
                $gr = $item->grade_cd ?? 'A';

                $item->sisa_gudang_qty = (float) ($totalStokMap->get($gId . '_' . $bId)?->total_sisa ?? 0);
                $item->sisa_batch_qty = (float) ($batchStokMap->get($gId . '_' . $bId . '_' . $bt . '_' . $gr)?->sisa_qty ?? 0);
            }
        }

        return $paginated;
    }

    /**
     * Mengambil detail satu transaksi pemakaian barang.
     */
    public function getById(int $id): DatPakaiHdr
    {
        $hdr = DatPakaiHdr::with(['gudang', 'details.barang.satuanDasar', 'details.barang.jenisBarang'])
            ->where('pakai_id', $id)
            ->firstOrFail();

        $gId = $hdr->gudang_id;
        $barangIds = $hdr->details->pluck('barang_id')->unique()->toArray();

        $totalStokMap = DatStokBatch::where('gudang_id', $gId)
            ->whereIn('barang_id', $barangIds)
            ->where('deleted_st', false)
            ->selectRaw('barang_id, SUM(sisa_qty) as total_sisa')
            ->groupBy('barang_id')
            ->pluck('total_sisa', 'barang_id');

        $batchStokMap = DatStokBatch::where('gudang_id', $gId)
            ->whereIn('barang_id', $barangIds)
            ->where('deleted_st', false)
            ->get()
            ->keyBy(fn($r) => $r->barang_id . '_' . $r->batch_no . '_' . ($r->grade_cd ?? 'A'));

        foreach ($hdr->details as $dtl) {
            $dtl->sisa_gudang_qty = (float) ($totalStokMap->get($dtl->barang_id) ?? 0);
            $dtl->sisa_batch_qty = (float) ($batchStokMap->get($dtl->barang_id . '_' . $dtl->batch_no . '_' . ($dtl->grade_cd ?? 'A'))?->sisa_qty ?? 0);
        }

        return $hdr;
    }

    /**
     * Menghitung ringkasan metrik pengeluaran bahan (Singkong, Minyak, Bumbu, Kemasan)
     * untuk sinkronisasi akuntansi biaya produksi / HPP
     */
    public function getRingkasanPengeluaran(
        int|array|null $gudangId = null,
        ?string $tujuan = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $query = DatPakaiDtl::with(['barang.satuanDasar', 'barang.jenisBarang'])
            ->whereHas('header', function ($q) use ($gudangId, $tujuan, $startDate, $endDate) {
                $q->where('deleted_st', false);
                if (is_array($gudangId)) {
                    $q->whereIn('gudang_id', $gudangId);
                } elseif ($gudangId !== null) {
                    $q->where('gudang_id', $gudangId);
                }
                if (!empty($tujuan)) {
                    $q->where('tujuan_pemakaian', $tujuan);
                }
                if (!empty($startDate)) {
                    $q->whereDate('pakai_tgl', '>=', $startDate);
                }
                if (!empty($endDate)) {
                    $q->whereDate('pakai_tgl', '<=', $endDate);
                }
            });

        $items = $query->get();

        $singkongQty = 0;
        $singkongNilai = 0;
        $minyakQty = 0;
        $minyakNilai = 0;
        $bumbuQty = 0;
        $bumbuNilai = 0;
        $kemasanQty = 0;
        $kemasanNilai = 0;
        $lainnyaQty = 0;
        $lainnyaNilai = 0;
        $grandTotalNilai = 0;

        foreach ($items as $item) {
            $nama = strtoupper((string) ($item->barang?->barang_nm ?? ''));
            $kode = strtoupper((string) ($item->barang?->barang_cd ?? ''));
            $qty = (float) $item->qty_keluar;
            $nilai = (float) $item->total_harga;
            $grandTotalNilai += $nilai;

            if (str_contains($nama, 'SINGKONG') || str_contains($nama, 'UBI') || str_starts_with($kode, 'BB-SK')) {
                $singkongQty += $qty;
                $singkongNilai += $nilai;
            } elseif (str_contains($nama, 'MINYAK') || str_contains($nama, 'SAWIT') || str_contains($nama, 'KELAPA') || str_contains($kode, 'MYK')) {
                $minyakQty += $qty;
                $minyakNilai += $nilai;
            } elseif (str_contains($nama, 'BUMBU') || str_contains($nama, 'PERENYAH') || str_contains($nama, 'GARAM') || str_contains($nama, 'SEASONING') || str_starts_with($kode, 'BJB')) {
                $bumbuQty += $qty;
                $bumbuNilai += $nilai;
            } elseif (str_contains($nama, 'KARTON') || str_contains($nama, 'KARDUS') || str_contains($nama, 'PLASTIK') || str_contains($nama, 'LAKBAN') || str_contains($nama, 'TALI') || str_starts_with($kode, 'KEB') || str_starts_with($kode, 'LKB')) {
                $kemasanQty += $qty;
                $kemasanNilai += $nilai;
            } else {
                $lainnyaQty += $qty;
                $lainnyaNilai += $nilai;
            }
        }

        return [
            'singkong_qty'      => $singkongQty,
            'singkong_nilai'    => $singkongNilai,
            'minyak_qty'        => $minyakQty,
            'minyak_nilai'      => $minyakNilai,
            'bumbu_qty'         => $bumbuQty,
            'bumbu_nilai'       => $bumbuNilai,
            'kemasan_qty'       => $kemasanQty,
            'kemasan_nilai'     => $kemasanNilai,
            'lainnya_qty'       => $lainnyaQty,
            'lainnya_nilai'     => $lainnyaNilai,
            'grand_total_nilai' => $grandTotalNilai,
            'total_item_count'  => $items->count(),
        ];
    }

    /**
     * Memproses pengeluaran / pemakaian barang ke proses produksi atau packing via DB Transaction.
     * Mengurangi saldo batch di dat_stok_batch & mencatat riwayat kartu stok mutasi (OUT).
     */
    public function store(array $data): DatPakaiHdr
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            if (empty($items)) {
                throw new Exception("Minimal harus ada 1 item barang yang dikeluarkan.");
            }

            $pakaiNo = !empty($data['pakai_no']) ? trim($data['pakai_no']) : $this->codeGenerator->generatePakaiNo();
            $gudangId = (int) $data['gudang_id'];
            $tujuanPemakaian = !empty($data['tujuan_pemakaian']) ? trim($data['tujuan_pemakaian']) : 'PRODUKSI';

            // 1. Simpan Header Pengeluaran
            $header = DatPakaiHdr::create([
                'pakai_no'         => $pakaiNo,
                'pakai_tgl'        => $data['pakai_tgl'] ?? date('Y-m-d'),
                'gudang_id'        => $gudangId,
                'tujuan_pemakaian' => $tujuanPemakaian,
                'catatan_txt'      => $data['catatan_txt'] ?? null,
            ]);

            // 2. Loop detail item & kurangi stok
            foreach ($items as $item) {
                $barangId = (int) $item['barang_id'];
                $batchNo = trim($item['batch_no'] ?? '');
                $qtyKeluar = (float) $item['qty_keluar'];

                if ($qtyKeluar <= 0) {
                    continue;
                }

                if (empty($batchNo)) {
                    throw new Exception("Nomor batch wajib dipilih untuk setiap item barang yang dikeluarkan.");
                }

                $gradeCd = !empty($item['grade_cd']) ? strtoupper(trim($item['grade_cd'])) : 'A';

                // Cari batch untuk ambil harga satuan jika tidak diisi & validasi grade
                $stokBatchQuery = DatStokBatch::where('gudang_id', $gudangId)
                    ->where('barang_id', $barangId)
                    ->where('batch_no', $batchNo);

                if (!empty($item['stok_id'])) {
                    $stokBatch = (clone $stokBatchQuery)->where('stok_id', $item['stok_id'])->first();
                } else {
                    $stokBatch = (clone $stokBatchQuery)->where('grade_cd', $gradeCd)->first();
                }

                if (!$stokBatch) {
                    $stokBatch = $stokBatchQuery->first();
                }

                if (!$stokBatch) {
                    throw new Exception("Batch '{$batchNo}' tidak ditemukan di gudang yang dipilih.");
                }

                $gradeCd = $stokBatch->grade_cd ?? $gradeCd;

                $hargaSatuan = !empty($item['harga_satuan']) && (float) $item['harga_satuan'] > 0
                    ? (float) $item['harga_satuan']
                    : (float) $stokBatch->harga_satuan;

                if ($hargaSatuan <= 0) {
                    $barang = MstBarang::find($barangId);
                    $hargaSatuan = (float) ($barang?->harga_beli_standar ?? 0);
                }

                $totalHarga = $qtyKeluar * $hargaSatuan;
                $keteranganDetail = !empty($item['keterangan_txt']) ? trim($item['keterangan_txt']) : $tujuanPemakaian;

                // Simpan baris detail pemakaian
                DatPakaiDtl::create([
                    'pakai_id'       => $header->pakai_id,
                    'barang_id'      => $barangId,
                    'batch_no'       => $batchNo,
                    'grade_cd'       => $gradeCd,
                    'qty_keluar'     => $qtyKeluar,
                    'harga_satuan'   => $hargaSatuan,
                    'total_harga'    => $totalHarga,
                    'keterangan_txt' => $keteranganDetail,
                ]);

                // Kurangi stok fisik per batch & grade serta catat kartu stok OUT
                $this->stokService->deductStock(
                    $gudangId,
                    $barangId,
                    $batchNo,
                    $qtyKeluar,
                    $pakaiNo,
                    "Pengeluaran ({$tujuanPemakaian}) - {$keteranganDetail}",
                    $gradeCd
                );
            }

            return $header->fresh(['details.barang']);
        });
    }
}
