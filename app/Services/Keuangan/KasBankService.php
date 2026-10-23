<?php

namespace App\Services\Keuangan;

use App\Models\Keuangan\DatBayarHutangHdr;
use App\Models\Keuangan\DatBayarHutangDtl;
use App\Models\Keuangan\DatHutangHdr;
use App\Models\Keuangan\DatJurnalHdr;
use App\Models\Keuangan\DatKasMutasi;
use App\Models\Keuangan\MstKasBank;
use App\Models\MasterData\MstSupplier;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class KasBankService
{
    protected JurnalService $jurnalService;

    public function __construct(JurnalService $jurnalService)
    {
        $this->jurnalService = $jurnalService;
    }

    /**
     * Dapatkan semua rekening kas & bank aktif
     */
    public function getAllKasBank()
    {
        return MstKasBank::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('tipe_akun', 'desc')
            ->orderBy('nama_akun', 'asc')
            ->get();
    }

    /**
     * Dapatkan daftar bukti pembayaran (BKK) terpaginasi
     */
    public function getBayarListPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DatBayarHutangHdr::with(['supplier', 'kasBank', 'details.hutang'])
            ->where('deleted_st', false);

        if (!empty($filters['kas_bank_id'])) {
            $query->where('kas_bank_id', $filters['kas_bank_id']);
        }

        if (!empty($filters['metode_bayar'])) {
            $query->where('metode_bayar', $filters['metode_bayar']);
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('bayar_no', 'like', "%{$search}%")
                  ->orWhere('no_referensi_bank', 'like', "%{$search}%")
                  ->orWhere('penerima_nama', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn($sq) => $sq->where('supplier_nm', 'like', "%{$search}%"));
            });
        }

        if (!empty($filters['tgl_mulai'])) {
            $query->where('tgl_bayar', '>=', $filters['tgl_mulai']);
        }
        if (!empty($filters['tgl_selesai'])) {
            $query->where('tgl_bayar', '<=', $filters['tgl_selesai']);
        }

        return $query->orderBy('tgl_bayar', 'desc')
            ->orderBy('bayar_id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Dapatkan daftar mutasi arus kas & bank
     */
    public function getMutasiPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = DatKasMutasi::with('kasBank')->where('deleted_st', false);

        if (!empty($filters['kas_bank_id'])) {
            $query->where('kas_bank_id', $filters['kas_bank_id']);
        }

        if (!empty($filters['tipe_mutasi'])) {
            $query->where('tipe_mutasi', $filters['tipe_mutasi']);
        }

        if (!empty($filters['jenis_transaksi'])) {
            $query->where('jenis_transaksi', $filters['jenis_transaksi']);
        }

        if (!empty($filters['tgl_mulai'])) {
            $query->where('tgl_mutasi', '>=', $filters['tgl_mulai']);
        }
        if (!empty($filters['tgl_selesai'])) {
            $query->where('tgl_mutasi', '<=', $filters['tgl_selesai']);
        }

        return $query->orderBy('tgl_mutasi', 'desc')
            ->orderBy('mutasi_id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Generate Nomor Bukti Kas/Bank Keluar (BKK-YYYYMM-XXXX)
     */
    public function generateBayarNo(): string
    {
        $prefix = 'BKK-' . date('Ym') . '-';
        $last = DatBayarHutangHdr::where('bayar_no', 'like', $prefix . '%')
            ->orderBy('bayar_no', 'desc')
            ->first();

        if ($last) {
            $lastNum = (int) substr($last->bayar_no, -4);
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $nextNum;
    }

    /**
     * Eksekusi Pembayaran Tagihan Hutang (Support Lunas, Cash, Cicil/Parsial, Batch)
     */
    public function bayarHutang(array $payload, User $user): DatBayarHutangHdr
    {
        return DB::transaction(function () use ($payload, $user) {
            $kasBank = MstKasBank::where('deleted_st', false)->findOrFail($payload['kas_bank_id']);

            $allocations = $payload['allocations'] ?? [];
            if (empty($allocations)) {
                throw new Exception("Minimal harus memilih 1 tagihan hutang yang hendak dibayar.");
            }

            $totalBayar = 0;
            foreach ($allocations as $alloc) {
                $nominal = (float)($alloc['nominal_bayar'] ?? 0);
                if ($nominal <= 0) {
                    continue;
                }
                $totalBayar += $nominal;
            }

            if ($totalBayar <= 0) {
                throw new Exception("Nominal pembayaran yang dialokasikan harus lebih besar dari Rp 0.");
            }

            $bayarNo = $this->generateBayarNo();
            $tglBayar = $payload['tgl_bayar'] ?? now()->toDateString();
            $supplierId = !empty($payload['supplier_id']) ? $payload['supplier_id'] : null;

            // Jika supplier_id belum terpilih (misal opsi Semua Supplier), ambil dari invoice hutang pertama yang dibayar
            if (empty($supplierId) && !empty($allocations)) {
                $firstHutangId = $allocations[0]['hutang_id'] ?? null;
                if ($firstHutangId) {
                    $firstHutang = DatHutangHdr::find($firstHutangId);
                    $supplierId = $firstHutang?->supplier_id;
                }
            }

            // 1. Catat Header Pembayaran BKK
            $bkk = DatBayarHutangHdr::create([
                'bayar_no'          => $bayarNo,
                'tgl_bayar'         => $tglBayar,
                'supplier_id'       => $supplierId,
                'kas_bank_id'       => $kasBank->kas_bank_id,
                'metode_bayar'      => $payload['metode_bayar'] ?? 'TRANSFER',
                'no_referensi_bank' => $payload['no_referensi_bank'] ?? null,
                'total_bayar'       => $totalBayar,
                'penerima_nama'     => $payload['penerima_nama'] ?? null,
                'catatan'           => $payload['catatan'] ?? null,
                'status_cd'         => 'POSTED',
                'created_by'        => $user->name ?? 'SYSTEM',
                'updated_by'        => $user->name ?? 'SYSTEM',
            ]);

            // 2. Alokasikan ke masing-masing Invoice Hutang
            foreach ($allocations as $alloc) {
                $nominal = (float)($alloc['nominal_bayar'] ?? 0);
                if ($nominal <= 0) {
                    continue;
                }

                $hutang = DatHutangHdr::where('deleted_st', false)->lockForUpdate()->findOrFail($alloc['hutang_id']);

                $sisaSebelum = (float) $hutang->sisa_hutang;
                $sisaSesudah = max(0, $sisaSebelum - $nominal);
                $totalDibayarBaru = (float) $hutang->total_dibayar + $nominal;

                $newStatus = ($sisaSesudah <= 0) ? 'LUNAS' : 'SEBAGIAN';

                DatBayarHutangDtl::create([
                    'bayar_id'            => $bkk->bayar_id,
                    'hutang_id'           => $hutang->hutang_id,
                    'nominal_bayar'       => $nominal,
                    'sisa_hutang_sebelum' => $sisaSebelum,
                    'sisa_hutang_sesudah' => $sisaSesudah,
                    'catatan'             => $alloc['catatan'] ?? "Pembayaran {$bkk->bayar_no}",
                    'created_by'          => $user->name ?? 'SYSTEM',
                    'updated_by'          => $user->name ?? 'SYSTEM',
                ]);

                $hutang->update([
                    'total_dibayar'     => $totalDibayarBaru,
                    'sisa_hutang'       => $sisaSesudah,
                    'status_pembayaran' => $newStatus,
                    'updated_by'        => $user->name ?? 'SYSTEM',
                ]);
            }

            // 3. Potong Saldo Berjalan Rekening Kas & Bank
            $saldoSebelum = (float) $kasBank->saldo_berjalan;
            $saldoSesudah = $saldoSebelum - $totalBayar;

            $kasBank->update([
                'saldo_berjalan' => $saldoSesudah,
                'updated_by'     => $user->name ?? 'SYSTEM',
            ]);

            // 4. Catat Buku Mutasi Kas Keluar
            DatKasMutasi::create([
                'kas_bank_id'     => $kasBank->kas_bank_id,
                'tgl_mutasi'      => $tglBayar,
                'tipe_mutasi'     => 'KELUAR',
                'nominal'         => $totalBayar,
                'saldo_sebelum'   => $saldoSebelum,
                'saldo_sesudah'   => $saldoSesudah,
                'jenis_transaksi' => 'BAYAR_HUTANG',
                'ref_no'          => $bkk->bayar_no,
                'ref_id'          => $bkk->bayar_id,
                'keterangan'      => "Pelunasan tagihan supplier via {$kasBank->nama_akun} ({$bkk->bayar_no})",
                'created_by'      => $user->name ?? 'SYSTEM',
                'updated_by'      => $user->name ?? 'SYSTEM',
            ]);

            // 5. Catat Jurnal Double-Entry ke Jurnal Umum
            $kodeKas = !empty($kasBank->kode_akun) ? $kasBank->kode_akun : '1101';
            $suppModel = $supplierId ? MstSupplier::find($supplierId) : null;
            $suppName = $suppModel?->supplier_nm ?? 'Supplier';

            $keteranganJurnal = "Pembayaran Hutang - {$suppName} - {$bkk->bayar_no}";

            $jurnalEntries = [
                [
                    'kode_akun'  => '2100', // (Debet) Mengurangi Hutang Usaha
                    'debet'      => $totalBayar,
                    'kredit'     => 0,
                    'keterangan' => $keteranganJurnal,
                ],
                [
                    'kode_akun'  => $kodeKas, // (Kredit) Mengurangi Saldo Kas / Rekening Bank
                    'debet'      => 0,
                    'kredit'     => $totalBayar,
                    'keterangan' => $keteranganJurnal,
                ],
            ];

            $this->jurnalService->catatJurnal(
                tipeTransaksi: 'BAYAR_HUTANG',
                tglJurnal: $tglBayar,
                refNo: $bkk->bayar_no,
                refId: $bkk->bayar_id,
                keterangan: $keteranganJurnal,
                entries: $jurnalEntries,
                user: $user
            );

            return $bkk;
        });
    }

    /**
     * Batalkan / Void Transaksi Pembayaran BKK
     */
    public function voidPembayaran(DatBayarHutangHdr $bkk, User $user): void
    {
        if ($bkk->status_cd === 'VOID') {
            throw new Exception("Bukti bayar {$bkk->bayar_no} sudah dalam status VOID.");
        }

        DB::transaction(function () use ($bkk, $user) {
            $kasBank = MstKasBank::lockForUpdate()->findOrFail($bkk->kas_bank_id);

            // 1. Kembalikan sisa hutang di masing-masing invoice
            foreach ($bkk->details as $dtl) {
                $hutang = DatHutangHdr::lockForUpdate()->find($dtl->hutang_id);
                if ($hutang) {
                    $sisaBaru = (float) $hutang->sisa_hutang + (float) $dtl->nominal_bayar;
                    $dibayarBaru = max(0, (float) $hutang->total_dibayar - (float) $dtl->nominal_bayar);

                    $newStatus = ($dibayarBaru <= 0) ? 'BELUM_LUNAS' : 'SEBAGIAN';

                    $hutang->update([
                        'total_dibayar'     => $dibayarBaru,
                        'sisa_hutang'       => $sisaBaru,
                        'status_pembayaran' => $newStatus,
                        'updated_by'        => $user->name ?? 'SYSTEM',
                    ]);
                }
            }

            // 2. Kembalikan Saldo Rekening Kas & Bank (+ totalBayar)
            $saldoSebelum = (float) $kasBank->saldo_berjalan;
            $saldoSesudah = $saldoSebelum + (float) $bkk->total_bayar;

            $kasBank->update([
                'saldo_berjalan' => $saldoSesudah,
                'updated_by'     => $user->name ?? 'SYSTEM',
            ]);

            // 3. Catat Mutasi Pengembalian Dana
            DatKasMutasi::create([
                'kas_bank_id'     => $kasBank->kas_bank_id,
                'tgl_mutasi'      => now()->toDateString(),
                'tipe_mutasi'     => 'MASUK',
                'nominal'         => (float) $bkk->total_bayar,
                'saldo_sebelum'   => $saldoSebelum,
                'saldo_sesudah'   => $saldoSesudah,
                'jenis_transaksi' => 'MUTASI_KAS',
                'ref_no'          => $bkk->bayar_no . '-VOID',
                'ref_id'          => $bkk->bayar_id,
                'keterangan'      => "Pembatalan / VOID pembayaran BKK #{$bkk->bayar_no}",
                'created_by'      => $user->name ?? 'SYSTEM',
                'updated_by'      => $user->name ?? 'SYSTEM',
            ]);

            // 4. Update Status BKK
            $bkk->update([
                'status_cd'  => 'VOID',
                'updated_by' => $user->name ?? 'SYSTEM',
            ]);

            // 5. Batalkan Jurnal Terkait BKK
            DatJurnalHdr::where('ref_no', $bkk->bayar_no)
                ->where('deleted_st', false)
                ->update([
                    'deleted_st' => true,
                    'deleted_by' => $user->name ?? 'SYSTEM',
                    'updated_by' => $user->name ?? 'SYSTEM',
                ]);
        });
    }

    /**
     * Buat Akun Kas & Bank Baru
     */
    public function createKasBank(array $payload, User $user): MstKasBank
    {
        return DB::transaction(function () use ($payload, $user) {
            $saldoAwal = (float)($payload['saldo_awal'] ?? 0);

            $kasBank = MstKasBank::create([
                'kode_akun'      => strtoupper(trim($payload['kode_akun'])),
                'nama_akun'      => trim($payload['nama_akun']),
                'tipe_akun'      => $payload['tipe_akun'],
                'nomor_rekening' => !empty($payload['nomor_rekening']) ? trim($payload['nomor_rekening']) : null,
                'atas_nama'      => !empty($payload['atas_nama']) ? trim($payload['atas_nama']) : null,
                'bank_nama'      => !empty($payload['bank_nama']) ? strtoupper(trim($payload['bank_nama'])) : ($payload['tipe_akun'] === 'KAS' ? 'TUNAI' : null),
                'cabang'         => !empty($payload['cabang']) ? trim($payload['cabang']) : null,
                'saldo_awal'     => $saldoAwal,
                'saldo_berjalan' => $saldoAwal,
                'keterangan'     => !empty($payload['keterangan']) ? trim($payload['keterangan']) : null,
                'created_by'     => $user->name ?? 'SYSTEM',
                'updated_by'     => $user->name ?? 'SYSTEM',
                'active_st'      => true,
                'deleted_st'     => false,
                'version_no'     => 1,
            ]);

            // Jika ada saldo awal > 0, catat mutasi pembukaan saldo
            if ($saldoAwal > 0) {
                DatKasMutasi::create([
                    'kas_bank_id'     => $kasBank->kas_bank_id,
                    'tgl_mutasi'      => now()->toDateString(),
                    'tipe_mutasi'     => 'MASUK',
                    'nominal'         => $saldoAwal,
                    'saldo_sebelum'   => 0,
                    'saldo_sesudah'   => $saldoAwal,
                    'jenis_transaksi' => 'SALDO_AWAL',
                    'ref_no'          => 'INIT-' . $kasBank->kode_akun,
                    'ref_id'          => $kasBank->kas_bank_id,
                    'keterangan'      => "Saldo awal pembukuan akun {$kasBank->nama_akun}",
                    'created_by'      => $user->name ?? 'SYSTEM',
                    'updated_by'      => $user->name ?? 'SYSTEM',
                ]);
            }

            return $kasBank;
        });
    }

    /**
     * Perbarui Akun Kas & Bank
     */
    public function updateKasBank(MstKasBank $kasBank, array $payload, User $user): MstKasBank
    {
        return DB::transaction(function () use ($kasBank, $payload, $user) {
            $kasBank->update([
                'nama_akun'      => trim($payload['nama_akun']),
                'nomor_rekening' => !empty($payload['nomor_rekening']) ? trim($payload['nomor_rekening']) : null,
                'atas_nama'      => !empty($payload['atas_nama']) ? trim($payload['atas_nama']) : null,
                'bank_nama'      => !empty($payload['bank_nama']) ? strtoupper(trim($payload['bank_nama'])) : ($kasBank->tipe_akun === 'KAS' ? 'TUNAI' : null),
                'cabang'         => !empty($payload['cabang']) ? trim($payload['cabang']) : null,
                'keterangan'     => !empty($payload['keterangan']) ? trim($payload['keterangan']) : null,
                'active_st'      => isset($payload['active_st']) ? (bool)$payload['active_st'] : $kasBank->active_st,
                'updated_by'     => $user->name ?? 'SYSTEM',
                'version_no'     => $kasBank->version_no + 1,
            ]);

            return $kasBank;
        });
    }

    /**
     * Hapus / Nonaktifkan Akun Kas & Bank
     */
    public function deleteKasBank(MstKasBank $kasBank, User $user): bool
    {
        return DB::transaction(function () use ($kasBank, $user) {
            $hasMutasi = DatKasMutasi::where('kas_bank_id', $kasBank->kas_bank_id)
                ->where('deleted_st', false)
                ->where('jenis_transaksi', '!=', 'SALDO_AWAL')
                ->exists();

            $hasBayar = DatBayarHutangHdr::where('kas_bank_id', $kasBank->kas_bank_id)
                ->where('deleted_st', false)
                ->exists();

            if ($hasMutasi || $hasBayar) {
                // Jangan hapus permanen karena ada jejak audit transaksi moneter, nonaktifkan saja
                $kasBank->update([
                    'active_st'  => false,
                    'updated_by' => $user->name ?? 'SYSTEM',
                ]);
                return false; // return false artinya dinonaktifkan bukan dihapus permanen
            }

            // Hapus soft delete
            $kasBank->update([
                'deleted_st' => true,
                'active_st'  => false,
                'deleted_by' => $user->name ?? 'SYSTEM',
                'deleted_at' => now(),
            ]);

            return true;
        });
    }

    /**
     * Transfer Dana Antar Rekening Kas & Bank
     */
    public function transferDana(array $payload, User $user): array
    {
        return DB::transaction(function () use ($payload, $user) {
            $asalId = (int)$payload['kas_bank_asal_id'];
            $tujuanId = (int)$payload['kas_bank_tujuan_id'];
            $nominal = (float)$payload['nominal'];
            $tglMutasi = $payload['tgl_mutasi'] ?? now()->toDateString();
            $keterangan = trim($payload['keterangan'] ?? 'Transfer antar kas/bank');

            if ($asalId === $tujuanId) {
                throw new Exception("Rekening sumber dan rekening tujuan tidak boleh sama.");
            }

            if ($nominal <= 0) {
                throw new Exception("Nominal transfer harus lebih besar dari Rp 0.");
            }

            $kasAsal = MstKasBank::where('deleted_st', false)->where('active_st', true)->lockForUpdate()->findOrFail($asalId);
            $kasTujuan = MstKasBank::where('deleted_st', false)->where('active_st', true)->lockForUpdate()->findOrFail($tujuanId);

            if ((float)$kasAsal->saldo_berjalan < $nominal) {
                $saldoFormat = 'Rp ' . number_format($kasAsal->saldo_berjalan, 0, ',', '.');
                throw new Exception("Saldo rekening sumber {$kasAsal->nama_akun} tidak mencukupi ({$saldoFormat}).");
            }

            // Generate Ref No
            $refNo = 'TRF-' . date('Ym') . '-' . strtoupper(substr(uniqid(), -5));

            // 1. Kurangi Saldo Asal & Catat Mutasi Keluar
            $saldoAsalSebelum = (float)$kasAsal->saldo_berjalan;
            $saldoAsalSesudah = $saldoAsalSebelum - $nominal;
            $kasAsal->update([
                'saldo_berjalan' => $saldoAsalSesudah,
                'updated_by'     => $user->name ?? 'SYSTEM',
            ]);

            $mutasiKeluar = DatKasMutasi::create([
                'kas_bank_id'     => $kasAsal->kas_bank_id,
                'tgl_mutasi'      => $tglMutasi,
                'tipe_mutasi'     => 'KELUAR',
                'nominal'         => $nominal,
                'saldo_sebelum'   => $saldoAsalSebelum,
                'saldo_sesudah'   => $saldoAsalSesudah,
                'jenis_transaksi' => 'MUTASI_KAS',
                'ref_no'          => $refNo,
                'keterangan'      => "Transfer keluar ke {$kasTujuan->nama_akun}: {$keterangan}",
                'created_by'      => $user->name ?? 'SYSTEM',
                'updated_by'      => $user->name ?? 'SYSTEM',
            ]);

            // 2. Tambah Saldo Tujuan & Catat Mutasi Masuk
            $saldoTujuanSebelum = (float)$kasTujuan->saldo_berjalan;
            $saldoTujuanSesudah = $saldoTujuanSebelum + $nominal;
            $kasTujuan->update([
                'saldo_berjalan' => $saldoTujuanSesudah,
                'updated_by'     => $user->name ?? 'SYSTEM',
            ]);

            $mutasiMasuk = DatKasMutasi::create([
                'kas_bank_id'     => $kasTujuan->kas_bank_id,
                'tgl_mutasi'      => $tglMutasi,
                'tipe_mutasi'     => 'MASUK',
                'nominal'         => $nominal,
                'saldo_sebelum'   => $saldoTujuanSebelum,
                'saldo_sesudah'   => $saldoTujuanSesudah,
                'jenis_transaksi' => 'MUTASI_KAS',
                'ref_no'          => $refNo,
                'keterangan'      => "Transfer masuk dari {$kasAsal->nama_akun}: {$keterangan}",
                'created_by'      => $user->name ?? 'SYSTEM',
                'updated_by'      => $user->name ?? 'SYSTEM',
            ]);

            return [
                'ref_no'        => $refNo,
                'nominal'       => $nominal,
                'kas_asal'      => $kasAsal,
                'kas_tujuan'    => $kasTujuan,
                'mutasi_keluar' => $mutasiKeluar,
                'mutasi_masuk'  => $mutasiMasuk,
            ];
        });
    }

    /**
     * Penyesuaian / Koreksi Saldo Fisik (Opname Kas & Rekonsiliasi Bank)
     */
    public function penyesuaianSaldo(MstKasBank $kasBank, float $saldoFisikBaru, string $tglMutasi, string $keterangan, User $user): DatKasMutasi
    {
        return DB::transaction(function () use ($kasBank, $saldoFisikBaru, $tglMutasi, $keterangan, $user) {
            $kasBank = MstKasBank::lockForUpdate()->findOrFail($kasBank->kas_bank_id);
            $saldoSebelum = (float)$kasBank->saldo_berjalan;
            $selisih = $saldoFisikBaru - $saldoSebelum;

            if ($selisih == 0) {
                throw new Exception("Saldo fisik baru sama persis dengan saldo berjalan sistem saat ini. Tidak ada penyesuaian yang perlu dicatat.");
            }

            $tipeMutasi = ($selisih > 0) ? 'MASUK' : 'KELUAR';
            $nominalAbs = abs($selisih);
            $refNo = 'ADJ-' . date('Ym') . '-' . strtoupper(substr(uniqid(), -5));

            $kasBank->update([
                'saldo_berjalan' => $saldoFisikBaru,
                'updated_by'     => $user->name ?? 'SYSTEM',
            ]);

            return DatKasMutasi::create([
                'kas_bank_id'     => $kasBank->kas_bank_id,
                'tgl_mutasi'      => $tglMutasi,
                'tipe_mutasi'     => $tipeMutasi,
                'nominal'         => $nominalAbs,
                'saldo_sebelum'   => $saldoSebelum,
                'saldo_sesudah'   => $saldoFisikBaru,
                'jenis_transaksi' => 'PENYESUAIAN',
                'ref_no'          => $refNo,
                'keterangan'      => "Koreksi/Opname Saldo: {$keterangan} (Selisih: " . ($selisih > 0 ? '+' : '-') . "Rp " . number_format($nominalAbs, 0, ',', '.') . ")",
                'created_by'      => $user->name ?? 'SYSTEM',
                'updated_by'      => $user->name ?? 'SYSTEM',
            ]);
        });
    }

    /**
     * Konversi angka nominal ke kalimat terbilang Rupiah
     */
    public static function terbilang(float $nominal): string
    {
        $nominal = floor(abs($nominal));
        if ($nominal <= 0) {
            return 'Nol Rupiah';
        }

        $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

        if ($nominal < 12) {
            $hasil = $huruf[(int)$nominal];
        } elseif ($nominal < 20) {
            $hasil = self::terbilang($nominal - 10) . ' Belas';
        } elseif ($nominal < 100) {
            $hasil = self::terbilang($nominal / 10) . ' Puluh ' . self::terbilang($nominal % 10);
        } elseif ($nominal < 200) {
            $hasil = 'Seratus ' . self::terbilang($nominal - 100);
        } elseif ($nominal < 1000) {
            $hasil = self::terbilang($nominal / 100) . ' Ratus ' . self::terbilang($nominal % 100);
        } elseif ($nominal < 2000) {
            $hasil = 'Seribu ' . self::terbilang($nominal - 1000);
        } elseif ($nominal < 1000000) {
            $hasil = self::terbilang($nominal / 1000) . ' Ribu ' . self::terbilang($nominal % 1000);
        } elseif ($nominal < 1000000000) {
            $hasil = self::terbilang($nominal / 1000000) . ' Juta ' . self::terbilang($nominal % 1000000);
        } elseif ($nominal < 1000000000000) {
            $hasil = self::terbilang($nominal / 1000000000) . ' Miliar ' . self::terbilang(fmod($nominal, 1000000000));
        } else {
            $hasil = self::terbilang($nominal / 1000000000000) . ' Triliun ' . self::terbilang(fmod($nominal, 1000000000000));
        }

        $hasilClean = str_replace('Rupiah', '', $hasil);
        return trim(preg_replace('/\s+/', ' ', $hasilClean)) . ' Rupiah';
    }
}
