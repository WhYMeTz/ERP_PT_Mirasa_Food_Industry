<?php

namespace App\Services\Gudang;

use App\Models\Gudang\DatKemasanSirkularDtl;
use App\Models\Gudang\DatKemasanSirkularHdr;
use App\Models\MasterData\MstBarang;
use App\Models\Penjualan\DatPengeluaranHdr;
use App\Services\Common\CodeGeneratorService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KemasanSirkularService
{
    public function __construct(
        protected StokService $stokService,
        protected CodeGeneratorService $codeGenerator
    ) {}

    /**
     * Mengambil daftar riwayat penerimaan kemasan sirkular dengan filter & paginasi.
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DatKemasanSirkularHdr::with(['customer', 'pengeluaran', 'gudang', 'details.barangAsal', 'details.barangMulus', 'details.barangBmg', 'details.barangRusak'])
            ->where('deleted_st', false);

        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->where('sirkular_no', 'ILIKE', "%{$s}%")
                  ->orWhere('surat_jalan_customer_no', 'ILIKE', "%{$s}%")
                  ->orWhere('nopol_kendaraan', 'ILIKE', "%{$s}%")
                  ->orWhere('supir_nm', 'ILIKE', "%{$s}%")
                  ->orWhereHas('customer', fn($c) => $c->where('customer_nm', 'ILIKE', "%{$s}%"))
                  ->orWhereHas('pengeluaran', fn($p) => $p->where('pengeluaran_no', 'ILIKE', "%{$s}%"));
            });
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (!empty($filters['tgl_mulai'])) {
            $query->whereDate('sirkular_tgl', '>=', $filters['tgl_mulai']);
        }

        if (!empty($filters['tgl_selesai'])) {
            $query->whereDate('sirkular_tgl', '<=', $filters['tgl_selesai']);
        }

        return $query->orderBy('sirkular_tgl', 'desc')
            ->orderBy('kemasan_sirkular_id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Dapatkan detail dokumen berdasarkan ID.
     */
    public function getById(int $id): DatKemasanSirkularHdr
    {
        return DatKemasanSirkularHdr::with([
            'customer',
            'pengeluaran.details.barang',
            'gudang',
            'details.barangAsal',
            'details.barangMulus',
            'details.barangBmg',
            'details.barangRusak'
        ])->where('deleted_st', false)->findOrFail($id);
    }

    /**
     * Generate nomor dokumen otomatis (Format Standar PT Mirasa: {seq}/PKB/MFI/{mm}/{yy}).
     */
    public function generateSirkularNo(?string $date = null): string
    {
        return $this->codeGenerator->generateKartonBalikanNo($date);
    }

    /**
     * Mengambil daftar surat jalan pengeluaran yang relevan untuk dropdown referensi.
     */
    public function getAvailablePengeluaranList()
    {
        return DatPengeluaranHdr::with(['customer', 'details.barang'])
            ->where('deleted_st', false)
            ->where('status_cd', 'DELIVERED')
            ->whereDoesntHave('kemasanSirkular')
            ->orderBy('pengeluaran_tgl', 'desc')
            ->limit(50)
            ->get();
    }

    /**
     * Simpan transaksi penerimaan & sortir kemasan sirkular serta mutasi stok fisik.
     */
    public function store(array $data, ?string $userName = null): DatKemasanSirkularHdr
    {
        return DB::transaction(function () use ($data, $userName) {
            $user = $userName ?? (Auth::user()?->name ?? 'SYSTEM');
            $tgl = $data['sirkular_tgl'] ?? Carbon::now()->toDateString();
            $sirkularNo = !empty($data['sirkular_no']) ? trim($data['sirkular_no']) : $this->generateSirkularNo($tgl);

            $gudangId = (int) $data['gudang_id'];
            $pengeluaranId = !empty($data['pengeluaran_id']) ? (int) $data['pengeluaran_id'] : null;
            $customerId = !empty($data['customer_id']) ? (int) $data['customer_id'] : null;

            // Jika pengeluaran_id ada tapi customer_id kosong, ambil dari pengeluaran
            if ($pengeluaranId && !$customerId) {
                $pengeluaran = DatPengeluaranHdr::find($pengeluaranId);
                $customerId = $pengeluaran?->customer_id;
            }

            $totalKirim = 0;
            $totalKembali = 0;
            $totalBaru = 0;
            $totalMulus = 0;
            $totalBmg = 0;
            $totalRusak = 0;
            $totalAfkirSiklus = 0;
            $totalSelisih = 0;

            $items = $data['items'] ?? [];
            if (empty($items)) {
                throw new Exception("Rincian kemasan yang diterima tidak boleh kosong.");
            }

            // SKU Default Kemasan Sirkular IFM
            $defaultBarangAsal = MstBarang::where('barang_cd', 'KIF00G-BP1')->first();
            $defaultBarangMulus = MstBarang::where('barang_cd', 'KIF00G-PUT')->first();
            $defaultBarangBmg = MstBarang::where('barang_cd', 'KIF00G-BMG')->first();
            $defaultBarangRusak = MstBarang::where('barang_cd', 'KMS-KTN-REJ')->first();

            $header = DatKemasanSirkularHdr::create([
                'sirkular_no'              => $sirkularNo,
                'sirkular_tgl'             => $tgl,
                'customer_id'              => $customerId,
                'pengeluaran_id'           => $pengeluaranId,
                'gudang_id'                => $gudangId,
                'surat_jalan_customer_no'  => $data['surat_jalan_customer_no'] ?? null,
                'supir_nm'                 => $data['supir_nm'] ?? null,
                'nopol_kendaraan'          => $data['nopol_kendaraan'] ?? null,
                'ekspedisi_nm'             => $data['ekspedisi_nm'] ?? null,
                'status_cd'                => 'COMPLETED',
                'catatan_txt'              => $data['catatan_txt'] ?? null,
                'created_by'               => $user,
                'updated_by'               => $user,
            ]);

            $dateSuffix = Carbon::parse($tgl)->format('ymd');

            foreach ($items as $idx => $row) {
                $barangAsalId = !empty($row['barang_asal_id']) ? (int)$row['barang_asal_id'] : ($defaultBarangAsal?->barang_id ?? null);
                $barangMulusId = !empty($row['barang_mulus_id']) ? (int)$row['barang_mulus_id'] : ($defaultBarangMulus?->barang_id ?? null);
                $barangBmgId = !empty($row['barang_bmg_id']) ? (int)$row['barang_bmg_id'] : ($defaultBarangBmg?->barang_id ?? null);
                $barangRusakId = !empty($row['barang_rusak_id']) ? (int)$row['barang_rusak_id'] : ($defaultBarangRusak?->barang_id ?? null);

                $kirimQty = (float) ($row['kirim_qty'] ?? 0);
                $baruQty = (float) ($row['baru_qty'] ?? 0);
                $mulusQty = (float) ($row['mulus_qty'] ?? 0);
                $puteranKe = (int) ($row['puteran_ke'] ?? 1);
                $bmgQty = (float) ($row['bmg_qty'] ?? 0);
                $rusakQty = (float) ($row['rusak_qty'] ?? 0);
                $afkirSiklusQty = (float) ($row['afkir_siklus_qty'] ?? 0);

                $kembaliQty = $baruQty + $mulusQty + $bmgQty + $rusakQty + $afkirSiklusQty;
                $selisihQty = max(0, $kirimQty - $kembaliQty);

                $seqStr = str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT);
                $batchBaru = $baruQty > 0 ? "BARU-{$dateSuffix}-{$seqStr}" : null;
                $batchMulus = $mulusQty > 0 ? "PUT-{$dateSuffix}-{$seqStr}" : null;
                $batchBmg = $bmgQty > 0 ? "BMG-{$dateSuffix}-{$seqStr}" : null;
                $batchRusak = $rusakQty > 0 ? "REJ-{$dateSuffix}-{$seqStr}" : null;
                $batchAfkir5x = $afkirSiklusQty > 0 ? "AFK-{$dateSuffix}-{$seqStr}" : null;

                DatKemasanSirkularDtl::create([
                    'kemasan_sirkular_id' => $header->kemasan_sirkular_id,
                    'barang_asal_id'      => $barangAsalId,
                    'barang_mulus_id'     => $barangMulusId,
                    'barang_bmg_id'       => $barangBmgId,
                    'barang_rusak_id'     => $barangRusakId,
                    'kirim_qty'           => $kirimQty,
                    'kembali_qty'         => $kembaliQty,
                    'baru_qty'            => $baruQty,
                    'mulus_qty'           => $mulusQty,
                    'puteran_ke'          => $puteranKe,
                    'bmg_qty'             => $bmgQty,
                    'rusak_qty'           => $rusakQty,
                    'afkir_siklus_qty'    => $afkirSiklusQty,
                    'selisih_qty'         => $selisihQty,
                    'batch_no_mulus'      => $batchMulus,
                    'batch_no_bmg'        => $batchBmg,
                    'batch_no_rusak'      => $batchRusak,
                    'catatan_txt'         => $row['catatan_txt'] ?? null,
                    'created_by'          => $user,
                    'updated_by'          => $user,
                ]);

                // MUTASI FISIK 1: Tambah Stok Karton Masih Baru ke Gudang (Harga Baru Rp 8.741)
                if ($baruQty > 0 && $barangAsalId) {
                    $hargaBaru = (float) ($defaultBarangAsal?->harga_beli_standar ?? 8741.00);
                    $this->stokService->addStock(
                        gudangId: $gudangId,
                        barangId: $barangAsalId,
                        batchNo: $batchBaru,
                        qty: $baruQty,
                        expiredTgl: null,
                        dokumenNo: $sirkularNo,
                        keterangan: "Penerimaan Kemasan Sirkular Kondisi Baru (Doc: {$sirkularNo})",
                        hargaSatuan: $hargaBaru,
                        gradeCd: 'A'
                    );
                }

                // MUTASI FISIK 2: Tambah Stok Karton Puteran Layak (Putaran 1 s/d 4)
                if ($mulusQty > 0 && $barangMulusId) {
                    $hargaStandar = (float) ($defaultBarangMulus?->harga_beli_standar ?? 1748.20);
                    $this->stokService->addStock(
                        gudangId: $gudangId,
                        barangId: $barangMulusId,
                        batchNo: $batchMulus,
                        qty: $mulusQty,
                        expiredTgl: null,
                        dokumenNo: $sirkularNo,
                        keterangan: "Balikan Kemasan Puteran Layak Pakai (Doc: {$sirkularNo})",
                        hargaSatuan: $hargaStandar,
                        gradeCd: 'A'
                    );
                }

                // MUTASI FISIK 3: Tambah Stok Karton Afkir Limit 5x Pemakaian (Otomatis Masuk Reject)
                if ($afkirSiklusQty > 0 && $barangRusakId) {
                    $this->stokService->addStock(
                        gudangId: $gudangId,
                        barangId: $barangRusakId,
                        batchNo: $batchAfkir5x,
                        qty: $afkirSiklusQty,
                        expiredTgl: null,
                        dokumenNo: $sirkularNo,
                        keterangan: "Balikan Kemasan Sirkular Pensiun/Afkir Limit 5x Pemakaian (Doc: {$sirkularNo})",
                        hargaSatuan: 0.00,
                        gradeCd: 'R'
                    );
                }

                // MUTASI FISIK 4: Tambah Stok Karton BMG ke Gudang
                if ($bmgQty > 0 && $barangBmgId) {
                    $this->stokService->addStock(
                        gudangId: $gudangId,
                        barangId: $barangBmgId,
                        batchNo: $batchBmg,
                        qty: $bmgQty,
                        expiredTgl: null,
                        dokumenNo: $sirkularNo,
                        keterangan: "Balikan Kemasan Sirkular BMG (Doc: {$sirkularNo})",
                        hargaSatuan: 0.00,
                        gradeCd: 'B'
                    );
                }

                // MUTASI FISIK 5: Tambah Stok Karton Reject Manual (Rusak Fisik) ke Gudang
                if ($rusakQty > 0 && $barangRusakId) {
                    $this->stokService->addStock(
                        gudangId: $gudangId,
                        barangId: $barangRusakId,
                        batchNo: $batchRusak,
                        qty: $rusakQty,
                        expiredTgl: null,
                        dokumenNo: $sirkularNo,
                        keterangan: "Balikan Kemasan Sirkular Rusak Fisik / Sobek (Doc: {$sirkularNo})",
                        hargaSatuan: 0.00,
                        gradeCd: 'R'
                    );
                }

                $totalKirim += $kirimQty;
                $totalKembali += $kembaliQty;
                $totalBaru += $baruQty;
                $totalMulus += $mulusQty;
                $totalBmg += $bmgQty;
                $totalRusak += $rusakQty;
                $totalAfkirSiklus += $afkirSiklusQty;
                $totalSelisih += $selisihQty;
            }

            $header->update([
                'total_kirim_qty'        => $totalKirim,
                'total_kembali_qty'      => $totalKembali,
                'total_baru_qty'         => $totalBaru,
                'total_mulus_qty'        => $totalMulus,
                'total_bmg_qty'          => $totalBmg,
                'total_rusak_qty'        => $totalRusak,
                'total_afkir_siklus_qty' => $totalAfkirSiklus,
                'total_selisih_qty'      => $totalSelisih,
            ]);

            return $header->fresh(['customer', 'pengeluaran', 'gudang', 'details']);
        });
    }

    /**
     * Batalkan (Void) dokumen penerimaan kemasan sirkular dan rollback stok gudang.
     */
    public function void(int $id, string $reason, ?string $userName = null): DatKemasanSirkularHdr
    {
        return DB::transaction(function () use ($id, $reason, $userName) {
            $user = $userName ?? (Auth::user()?->name ?? 'SYSTEM');
            $header = DatKemasanSirkularHdr::with('details')->lockForUpdate()->findOrFail($id);

            if ($header->status_cd === 'VOID') {
                throw new Exception("Dokumen penerimaan ini sudah dibatalkan sebelumnya.");
            }

            foreach ($header->details as $dtl) {
                // Rollback stok baru
                if ($dtl->baru_qty > 0 && $dtl->barang_asal_id) {
                    $this->stokService->deductStock(
                        gudangId: $header->gudang_id,
                        barangId: $dtl->barang_asal_id,
                        batchNo: "BARU-" . Carbon::parse($header->sirkular_tgl)->format('ymd') . "-01",
                        qty: (float) $dtl->baru_qty,
                        dokumenNo: $header->sirkular_no,
                        keterangan: "Rollback Pembatalan (Void) Penerimaan Kemasan Baru: {$reason}",
                        gradeCd: 'A'
                    );
                }

                // Rollback stok mulus
                if ($dtl->mulus_qty > 0 && $dtl->barang_mulus_id) {
                    $this->stokService->deductStock(
                        gudangId: $header->gudang_id,
                        barangId: $dtl->barang_mulus_id,
                        batchNo: $dtl->batch_no_mulus,
                        qty: (float) $dtl->mulus_qty,
                        dokumenNo: $header->sirkular_no,
                        keterangan: "Rollback Pembatalan (Void) Penerimaan Kemasan Sirkular Mulus: {$reason}",
                        gradeCd: 'A'
                    );
                }

                // Rollback stok BMG
                if ($dtl->bmg_qty > 0 && $dtl->barang_bmg_id) {
                    $this->stokService->deductStock(
                        gudangId: $header->gudang_id,
                        barangId: $dtl->barang_bmg_id,
                        batchNo: $dtl->batch_no_bmg,
                        qty: (float) $dtl->bmg_qty,
                        dokumenNo: $header->sirkular_no,
                        keterangan: "Rollback Pembatalan (Void) Penerimaan Kemasan BMG: {$reason}",
                        gradeCd: 'B'
                    );
                }

                // Rollback stok reject fisik
                if ($dtl->rusak_qty > 0 && $dtl->barang_rusak_id) {
                    $this->stokService->deductStock(
                        gudangId: $header->gudang_id,
                        barangId: $dtl->barang_rusak_id,
                        batchNo: $dtl->batch_no_rusak,
                        qty: (float) $dtl->rusak_qty,
                        dokumenNo: $header->sirkular_no,
                        keterangan: "Rollback Pembatalan (Void) Penerimaan Kemasan Sirkular Reject: {$reason}",
                        gradeCd: 'R'
                    );
                }

                // Rollback stok afkir limit 5x
                if ($dtl->afkir_siklus_qty > 0 && $dtl->barang_rusak_id) {
                    $this->stokService->deductStock(
                        gudangId: $header->gudang_id,
                        barangId: $dtl->barang_rusak_id,
                        batchNo: "AFK5-" . Carbon::parse($header->sirkular_tgl)->format('ymd') . "-01",
                        qty: (float) $dtl->afkir_siklus_qty,
                        dokumenNo: $header->sirkular_no,
                        keterangan: "Rollback Pembatalan (Void) Penerimaan Kemasan Afkir Limit 5x: {$reason}",
                        gradeCd: 'R'
                    );
                }
            }

            $header->update([
                'status_cd'   => 'VOID',
                'catatan_txt' => trim(($header->catatan_txt ? $header->catatan_txt . "\n" : '') . "[VOID oleh {$user}]: {$reason}"),
                'updated_by'  => $user,
            ]);

            return $header;
        });
    }
}

