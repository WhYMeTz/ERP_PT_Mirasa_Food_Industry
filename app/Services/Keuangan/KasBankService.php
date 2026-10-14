<?php

namespace App\Services\Keuangan;

use App\Models\Keuangan\DatBayarHutangHdr;
use App\Models\Keuangan\DatBayarHutangDtl;
use App\Models\Keuangan\DatHutangHdr;
use App\Models\Keuangan\DatKasMutasi;
use App\Models\Keuangan\MstKasBank;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class KasBankService
{
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
            $supplierId = $payload['supplier_id'] ?? null;

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
