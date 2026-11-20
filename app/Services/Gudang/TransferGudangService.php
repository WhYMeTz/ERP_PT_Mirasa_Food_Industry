<?php

namespace App\Services\Gudang;

use App\Models\Gudang\DatStokBatch;
use App\Models\Gudang\DatTransferDtl;
use App\Models\Gudang\DatTransferHdr;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Models\User;
use App\Services\Common\CodeGeneratorService;
use Carbon\Carbon;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TransferGudangService
{
    public function __construct(
        protected StokService $stokService,
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Ambil daftar dokumen transfer dengan filter & paginasi
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DatTransferHdr::with(['gudangAsal', 'gudangTujuan', 'details.barang.satuanDasar'])
            ->where('deleted_st', false);

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('transfer_no', 'ILIKE', "%{$search}%")
                  ->orWhere('no_surat_jalan', 'ILIKE', "%{$search}%")
                  ->orWhere('sopir_nama', 'ILIKE', "%{$search}%")
                  ->orWhere('nopol_kendaraan', 'ILIKE', "%{$search}%")
                  ->orWhere('catatan_txt', 'ILIKE', "%{$search}%");
            });
        }

        if (!empty($filters['gudang_asal_id'])) {
            $query->where('gudang_asal_id', $filters['gudang_asal_id']);
        }

        if (!empty($filters['gudang_tujuan_id'])) {
            $query->where('gudang_tujuan_id', $filters['gudang_tujuan_id']);
        }

        if (!empty($filters['status_st'])) {
            $query->where('status_st', $filters['status_st']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('transfer_tgl', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('transfer_tgl', '<=', $filters['end_date']);
        }

        return $query->orderBy('transfer_tgl', 'desc')
            ->orderBy('transfer_id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Dapatkan detail transfer berdasarkan ID
     */
    public function getById(int $id): DatTransferHdr
    {
        return DatTransferHdr::with([
            'gudangAsal', 
            'gudangTujuan', 
            'details.barang.satuanDasar', 
            'creator'
        ])
        ->where('deleted_st', false)
        ->findOrFail($id);
    }

    /**
     * Ambil daftar batch barang yang tersedia (sisa_qty > 0) di gudang asal
     */
    public function getAvailableStockBatches(int $gudangId, ?int $barangId = null)
    {
        $query = DatStokBatch::with(['barang.satuanDasar', 'barang.jenisBarang'])
            ->where('gudang_id', $gudangId)
            ->where('sisa_qty', '>', 0);

        if ($barangId) {
            $query->where('barang_id', $barangId);
        }

        return $query->orderBy('barang_id', 'asc')
            ->orderBy('created_at', 'asc') // FIFO
            ->get();
    }

    /**
     * Simpan transaksi Transfer Antar-Gudang
     * Mengurangi stok di Gudang Asal dan Menambah stok di Gudang Tujuan secara real-time.
     */
    public function storeTransfer(array $data, User $user): DatTransferHdr
    {
        return DB::transaction(function () use ($data, $user) {
            $gudangAsalId = (int) $data['gudang_asal_id'];
            $gudangTujuanId = (int) $data['gudang_tujuan_id'];

            if ($gudangAsalId === $gudangTujuanId) {
                throw new Exception("Gudang asal dan gudang tujuan tidak boleh sama.");
            }

            $gudangAsal = MstGudang::findOrFail($gudangAsalId);
            $gudangTujuan = MstGudang::findOrFail($gudangTujuanId);

            $items = $data['items'] ?? [];
            if (empty($items)) {
                throw new Exception("Minimal harus ada 1 barang yang ditransfer.");
            }

            $transferTgl = !empty($data['transfer_tgl']) ? $data['transfer_tgl'] : date('Y-m-d');
            $transferNo = !empty($data['transfer_no']) ? trim($data['transfer_no']) : $this->codeGenerator->generateTransferNo($transferTgl);

            // Cek duplikasi nomor transfer
            if (DatTransferHdr::where('transfer_no', $transferNo)->exists()) {
                $transferNo = $this->codeGenerator->generateTransferNo($transferTgl);
            }

            // 1. Buat Header Transfer
            $header = DatTransferHdr::create([
                'transfer_no'      => $transferNo,
                'transfer_tgl'     => $transferTgl,
                'gudang_asal_id'   => $gudangAsalId,
                'gudang_tujuan_id' => $gudangTujuanId,
                'no_surat_jalan'   => !empty($data['no_surat_jalan']) ? trim($data['no_surat_jalan']) : null,
                'sopir_nama'       => !empty($data['sopir_nama']) ? trim($data['sopir_nama']) : null,
                'nopol_kendaraan'  => !empty($data['nopol_kendaraan']) ? trim($data['nopol_kendaraan']) : null,
                'catatan_txt'      => !empty($data['catatan_txt']) ? trim($data['catatan_txt']) : null,
                'status_st'        => 'POSTED',
                'total_qty'        => 0,
                'total_nilai'      => 0,
                'created_by'       => $user->name ?? 'SYSTEM',
                'updated_by'       => $user->name ?? 'SYSTEM',
            ]);

            $totalQty = 0;
            $totalNilai = 0;

            // 2. Loop detail item transfer
            foreach ($items as $item) {
                $barangId = (int) $item['barang_id'];
                $batchNo = trim($item['batch_no'] ?? '');
                $gradeCd = !empty($item['grade_cd']) ? strtoupper(trim($item['grade_cd'])) : 'A';
                $qtyTransfer = (float) ($item['qty_transfer'] ?? 0);

                if ($qtyTransfer <= 0) {
                    continue;
                }

                if (empty($batchNo)) {
                    throw new Exception("Nomor batch wajib dipilih untuk setiap barang yang dipindahkan.");
                }

                // Kunci & periksa batch di gudang asal
                $stokAsal = DatStokBatch::where('gudang_id', $gudangAsalId)
                    ->where('barang_id', $barangId)
                    ->where('batch_no', $batchNo)
                    ->where('grade_cd', $gradeCd)
                    ->lockForUpdate()
                    ->first();

                if (!$stokAsal) {
                    $barang = MstBarang::find($barangId);
                    $barangNm = $barang ? $barang->barang_nm : "Barang #{$barangId}";
                    throw new Exception("Batch '{$batchNo}' untuk barang '{$barangNm}' tidak ditemukan di gudang asal ({$gudangAsal->gudang_nm}).");
                }

                $sisaQtyAsal = (float) $stokAsal->sisa_qty;
                if ($qtyTransfer > $sisaQtyAsal) {
                    $barang = MstBarang::find($barangId);
                    $barangNm = $barang ? $barang->barang_nm : "Barang #{$barangId}";
                    throw new Exception("Stok batch '{$batchNo}' ({$barangNm}) di {$gudangAsal->gudang_nm} tidak mencukupi (Tersedia: {$sisaQtyAsal}, Diminta: {$qtyTransfer}).");
                }

                $hargaSatuan = (float) $stokAsal->harga_satuan;
                $expiredTgl = $stokAsal->expired_tgl;
                $totalHarga = $qtyTransfer * $hargaSatuan;

                // A. Potong stok di Gudang Asal
                $ketOut = "Transfer Keluar ({$transferNo}) ke {$gudangTujuan->gudang_nm}";
                $this->stokService->deductStock(
                    gudangId: $gudangAsalId,
                    barangId: $barangId,
                    batchNo: $batchNo,
                    qty: $qtyTransfer,
                    dokumenNo: $transferNo,
                    keterangan: $ketOut,
                    gradeCd: $gradeCd
                );

                // B. Tambah stok di Gudang Tujuan
                $ketIn = "Transfer Masuk ({$transferNo}) dari {$gudangAsal->gudang_nm}";
                $this->stokService->addStock(
                    gudangId: $gudangTujuanId,
                    barangId: $barangId,
                    batchNo: $batchNo,
                    qty: $qtyTransfer,
                    hargaSatuan: $hargaSatuan,
                    dokumenNo: $transferNo,
                    expiredTgl: $expiredTgl ? $expiredTgl->format('Y-m-d') : null,
                    keterangan: $ketIn,
                    gradeCd: $gradeCd
                );

                // C. Catat baris detail transfer
                DatTransferDtl::create([
                    'transfer_id'  => $header->transfer_id,
                    'barang_id'    => $barangId,
                    'batch_no'     => $batchNo,
                    'grade_cd'     => $gradeCd,
                    'expired_tgl'  => $expiredTgl,
                    'qty_transfer' => $qtyTransfer,
                    'harga_satuan' => $hargaSatuan,
                    'total_harga'  => $totalHarga,
                    'catatan_txt'  => !empty($item['catatan_txt']) ? trim($item['catatan_txt']) : null,
                    'created_by'   => $user->name ?? 'SYSTEM',
                    'updated_by'   => $user->name ?? 'SYSTEM',
                ]);

                $totalQty += $qtyTransfer;
                $totalNilai += $totalHarga;
            }

            if ($totalQty <= 0) {
                throw new Exception("Total barang yang ditransfer harus lebih besar dari 0.");
            }

            // Update total di Header
            $header->update([
                'total_qty'   => $totalQty,
                'total_nilai' => $totalNilai,
            ]);

            return $header;
        });
    }

    /**
     * Batalkan / Void Transaksi Transfer Antar-Gudang
     * Membalikkan mutasi stok: mengurangi dari gudang tujuan dan mengembalikan ke gudang asal.
     */
    public function voidTransfer(int $transferId, string $alasan, User $user): DatTransferHdr
    {
        return DB::transaction(function () use ($transferId, $alasan, $user) {
            $header = DatTransferHdr::with(['details.barang', 'gudangAsal', 'gudangTujuan'])
                ->lockForUpdate()
                ->findOrFail($transferId);

            if ($header->status_st === 'VOID') {
                throw new Exception("Dokumen transfer '{$header->transfer_no}' sudah berstatus VOID sebelumnya.");
            }

            $gudangAsal = $header->gudangAsal;
            $gudangTujuan = $header->gudangTujuan;

            // 1. Validasi stok di gudang tujuan masih cukup untuk dibalikkan
            foreach ($header->details as $dtl) {
                $stokTujuan = DatStokBatch::where('gudang_id', $header->gudang_tujuan_id)
                    ->where('barang_id', $dtl->barang_id)
                    ->where('batch_no', $dtl->batch_no)
                    ->where('grade_cd', $dtl->grade_cd)
                    ->lockForUpdate()
                    ->first();

                $tersedia = $stokTujuan ? (float) $stokTujuan->sisa_qty : 0;
                $dibutuhkan = (float) $dtl->qty_transfer;

                if ($tersedia < $dibutuhkan) {
                    $barangNm = $dtl->barang?->barang_nm ?? "Barang #{$dtl->barang_id}";
                    throw new Exception("Tidak dapat membatalkan transfer. Barang '{$barangNm}' batch '{$dtl->batch_no}' di gudang tujuan ({$gudangTujuan->gudang_nm}) sudah terpakai/berkurang (Tersedia: {$tersedia}, Dibutuhkan: {$dibutuhkan}).");
                }
            }

            // 2. Balikkan mutasi stok
            foreach ($header->details as $dtl) {
                $qty = (float) $dtl->qty_transfer;
                $harga = (float) $dtl->harga_satuan;

                // Kurangi dari Gudang Tujuan (Rollback)
                $ketOut = "VOID Transfer ({$header->transfer_no}) - Batal kirim ke {$gudangTujuan->gudang_nm}";
                $this->stokService->deductStock(
                    gudangId: $header->gudang_tujuan_id,
                    barangId: $dtl->barang_id,
                    batchNo: $dtl->batch_no,
                    qty: $qty,
                    dokumenNo: $header->transfer_no,
                    keterangan: $ketOut,
                    gradeCd: $dtl->grade_cd
                );

                // Tambahkan kembali ke Gudang Asal
                $ketIn = "VOID Transfer ({$header->transfer_no}) - Pemulihan stok di {$gudangAsal->gudang_nm}";
                $this->stokService->addStock(
                    gudangId: $header->gudang_asal_id,
                    barangId: $dtl->barang_id,
                    batchNo: $dtl->batch_no,
                    qty: $qty,
                    hargaSatuan: $harga,
                    dokumenNo: $header->transfer_no,
                    expiredTgl: $dtl->expired_tgl ? $dtl->expired_tgl->format('Y-m-d') : null,
                    keterangan: $ketIn,
                    gradeCd: $dtl->grade_cd
                );
            }

            // 3. Update status header menjadi VOID
            $catatanVoid = trim($header->catatan_txt . "\n[VOID oleh " . ($user->name ?? 'User') . " pada " . now()->format('d/m/Y H:i') . "]: " . $alasan);
            $header->update([
                'status_st'   => 'VOID',
                'catatan_txt' => $catatanVoid,
                'updated_by'  => $user->name ?? 'SYSTEM',
            ]);

            return $header;
        });
    }
}
