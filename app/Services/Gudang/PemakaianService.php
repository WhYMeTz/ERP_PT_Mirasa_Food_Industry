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
    public function getAllPaginated(int $perPage = 15, ?string $search = null, int|array|null $gudangId = null): LengthAwarePaginator
    {
        $query = DatPakaiHdr::with(['gudang', 'details.barang.satuanDasar', 'details.barang.jenisBarang'])
            ->where('deleted_st', false);

        if (is_array($gudangId)) {
            $query->whereIn('gudang_id', $gudangId);
        } elseif ($gudangId !== null) {
            $query->where('gudang_id', $gudangId);
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
    public function getBarangKeluarListPaginated(int $perPage = 25, ?string $search = null, int|array|null $gudangId = null): LengthAwarePaginator
    {
        $query = DatPakaiDtl::with(['header.gudang', 'barang.jenisBarang', 'barang.satuanDasar'])
            ->whereHas('header', function ($q) use ($gudangId) {
                $q->where('deleted_st', false);
                if (is_array($gudangId)) {
                    $q->whereIn('gudang_id', $gudangId);
                } elseif ($gudangId !== null) {
                    $q->where('gudang_id', $gudangId);
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

        return $query->orderBy('pakaidtl_id', 'desc')->paginate($perPage);
    }

    /**
     * Mengambil detail satu transaksi pemakaian barang.
     */
    public function getById(int $id): DatPakaiHdr
    {
        return DatPakaiHdr::with(['gudang', 'details.barang.satuanDasar', 'details.barang.jenisBarang'])
            ->where('pakai_id', $id)
            ->firstOrFail();
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

                // Cari batch untuk ambil harga satuan jika tidak diisi
                $stokBatch = DatStokBatch::where('gudang_id', $gudangId)
                    ->where('barang_id', $barangId)
                    ->where('batch_no', $batchNo)
                    ->first();

                if (!$stokBatch) {
                    throw new Exception("Batch '{$batchNo}' tidak ditemukan di gudang yang dipilih.");
                }

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
                    'qty_keluar'     => $qtyKeluar,
                    'harga_satuan'   => $hargaSatuan,
                    'total_harga'    => $totalHarga,
                    'keterangan_txt' => $keteranganDetail,
                ]);

                // Kurangi stok fisik per batch & catat kartu stok OUT
                $this->stokService->deductStock(
                    $gudangId,
                    $barangId,
                    $batchNo,
                    $qtyKeluar,
                    $pakaiNo,
                    "Pengeluaran ({$tujuanPemakaian}) - {$keteranganDetail}"
                );
            }

            return $header->fresh(['details.barang']);
        });
    }
}
