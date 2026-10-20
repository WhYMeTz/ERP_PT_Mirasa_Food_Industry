<?php

namespace App\Services\Keuangan;

use App\Models\Keuangan\DatKasMasukDtl;
use App\Models\Keuangan\DatKasMasukHdr;
use App\Models\Keuangan\DatKasMutasi;
use App\Models\Keuangan\MstKasBank;
use App\Models\User;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class KasMasukService
{
    /**
     * Generate Nomor Bukti Kas Masuk (BKM): BKM-YYYYMM-XXXX
     */
    public function generateBkmNo(?string $date = null): string
    {
        $dt = $date ? \Carbon\Carbon::parse($date) : now();
        $prefix = 'BKM-' . $dt->format('Ym') . '-';

        $last = DatKasMasukHdr::where('bkm_no', 'LIKE', "{$prefix}%")
            ->orderBy('bkm_no', 'desc')
            ->value('bkm_no');

        if ($last) {
            $lastNumber = (int) substr($last, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }

    /**
     * Dapatkan daftar transaksi BKM dengan paginasi dan filter
     */
    public function getKasMasukListPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DatKasMasukHdr::with(['kasBank', 'customer', 'details'])
            ->where('deleted_st', false);

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('bkm_no', 'ILIKE', "%{$search}%")
                  ->orWhere('penyetor_nama', 'ILIKE', "%{$search}%")
                  ->orWhere('no_referensi_bank', 'ILIKE', "%{$search}%")
                  ->orWhere('catatan', 'ILIKE', "%{$search}%");
            });
        }

        if (!empty($filters['kas_bank_id'])) {
            $query->where('kas_bank_id', $filters['kas_bank_id']);
        }

        if (!empty($filters['kategori'])) {
            $query->where('kategori_penerimaan', $filters['kategori']);
        }

        if (!empty($filters['metode_terima'])) {
            $query->where('metode_terima', $filters['metode_terima']);
        }

        if (!empty($filters['tgl_mulai'])) {
            $query->whereDate('tgl_terima', '>=', $filters['tgl_mulai']);
        }

        if (!empty($filters['tgl_selesai'])) {
            $query->whereDate('tgl_terima', '<=', $filters['tgl_selesai']);
        }

        return $query->orderBy('tgl_terima', 'desc')
                     ->orderBy('bkm_id', 'desc')
                     ->paginate($perPage)
                     ->withQueryString();
    }

    /**
     * Metrik KPI Penerimaan Kas & Bank
     */
    public function getKpiMetrics(): array
    {
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        $bulanIniQuery = DatKasMasukHdr::where('deleted_st', false)
            ->where('status_cd', 'POSTED')
            ->whereBetween('tgl_terima', [$startOfMonth, $endOfMonth]);

        $totalBulanIni = (float) (clone $bulanIniQuery)->sum('total_terima');
        $transaksiBulanIni = (int) (clone $bulanIniQuery)->count();
        $limbahBulanIni = (float) (clone $bulanIniQuery)->where('kategori_penerimaan', 'PENJUALAN_LIMBAH')->sum('total_terima');
        $modalBulanIni = (float) (clone $bulanIniQuery)->where('kategori_penerimaan', 'SETORAN_MODAL')->sum('total_terima');
        $tunaiBulanIni = (float) (clone $bulanIniQuery)->where('kategori_penerimaan', 'PENJUALAN_TUNAI')->sum('total_terima');
        $lainBulanIni  = (float) (clone $bulanIniQuery)->where('kategori_penerimaan', 'PENDAPATAN_LAIN')->sum('total_terima');

        return [
            'total_bulan_ini'     => $totalBulanIni,
            'count_transaksi'     => $transaksiBulanIni,
            'total_limbah'        => $limbahBulanIni,
            'total_modal'         => $modalBulanIni,
            'total_tunai'         => $tunaiBulanIni,
            'total_lain'          => $lainBulanIni,
        ];
    }

    /**
     * Dapatkan detail BKM berdasarkan ID
     */
    public function getById(int $id): DatKasMasukHdr
    {
        return DatKasMasukHdr::with(['kasBank', 'customer', 'details'])
            ->where('deleted_st', false)
            ->findOrFail($id);
    }

    /**
     * Simpan Transaksi Bukti Kas Masuk (BKM)
     */
    public function storeKasMasuk(array $payload, User $user): DatKasMasukHdr
    {
        return DB::transaction(function () use ($payload, $user) {
            $kasBankId = (int) $payload['kas_bank_id'];
            $tglTerima = $payload['tgl_terima'] ?? now()->toDateString();
            $kategori  = $payload['kategori_penerimaan'] ?? 'PENDAPATAN_LAIN';
            $items     = $payload['items'] ?? [];

            if (empty($items)) {
                throw new Exception("Minimal harus ada 1 baris rincian penerimaan kas.");
            }

            // Hitung total terima dari rincian
            $totalTerima = 0;
            foreach ($items as $item) {
                $nom = (float) ($item['nominal'] ?? 0);
                if ($nom <= 0) {
                    throw new Exception("Nominal pada setiap rincian harus lebih besar dari Rp 0.");
                }
                $totalTerima += $nom;
            }

            if ($totalTerima <= 0) {
                throw new Exception("Total nominal penerimaan harus lebih besar dari Rp 0.");
            }

            // Lock rekening bank tujuan
            $kasBank = MstKasBank::where('deleted_st', false)
                ->where('active_st', true)
                ->lockForUpdate()
                ->findOrFail($kasBankId);

            $bkmNo = !empty($payload['bkm_no']) ? trim($payload['bkm_no']) : $this->generateBkmNo($tglTerima);

            // 1. Buat Header BKM
            $bkm = DatKasMasukHdr::create([
                'bkm_no'              => $bkmNo,
                'tgl_terima'          => $tglTerima,
                'kas_bank_id'         => $kasBank->kas_bank_id,
                'kategori_penerimaan' => $kategori,
                'customer_id'         => !empty($payload['customer_id']) ? $payload['customer_id'] : null,
                'penyetor_nama'       => !empty($payload['penyetor_nama']) ? trim($payload['penyetor_nama']) : null,
                'metode_terima'       => $payload['metode_terima'] ?? 'TRANSFER',
                'no_referensi_bank'   => !empty($payload['no_referensi_bank']) ? trim($payload['no_referensi_bank']) : null,
                'total_terima'        => $totalTerima,
                'catatan'             => !empty($payload['catatan']) ? trim($payload['catatan']) : null,
                'status_cd'           => 'POSTED',
                'created_by'          => $user->name ?? 'SYSTEM',
                'updated_by'          => $user->name ?? 'SYSTEM',
            ]);

            // 2. Buat Rincian Item BKM
            foreach ($items as $item) {
                DatKasMasukDtl::create([
                    'bkm_id'       => $bkm->bkm_id,
                    'so_id'        => !empty($item['so_id']) ? $item['so_id'] : null,
                    'uraian'       => trim($item['uraian']),
                    'qty'          => (float) ($item['qty'] ?? 1),
                    'satuan'       => !empty($item['satuan']) ? trim($item['satuan']) : null,
                    'harga_satuan' => (float) ($item['harga_satuan'] ?? 0),
                    'nominal'      => (float) $item['nominal'],
                    'catatan'      => !empty($item['catatan']) ? trim($item['catatan']) : null,
                    'created_by'   => $user->name ?? 'SYSTEM',
                    'updated_by'   => $user->name ?? 'SYSTEM',
                ]);
            }

            // 3. Tambah Saldo Berjalan Rekening Kas & Bank
            $saldoSebelum = (float) $kasBank->saldo_berjalan;
            $saldoSesudah = $saldoSebelum + $totalTerima;

            $kasBank->update([
                'saldo_berjalan' => $saldoSesudah,
                'updated_by'     => $user->name ?? 'SYSTEM',
            ]);

            // 4. Catat Buku Mutasi Kas & Bank Masuk
            $namaAkun = $kasBank->kas_bank_nm ?? 'Kas/Bank';
            DatKasMutasi::create([
                'kas_bank_id'     => $kasBank->kas_bank_id,
                'tgl_mutasi'      => $tglTerima,
                'tipe_mutasi'     => 'MASUK',
                'nominal'         => $totalTerima,
                'saldo_sebelum'   => $saldoSebelum,
                'saldo_sesudah'   => $saldoSesudah,
                'jenis_transaksi' => 'PENERIMAAN_KAS',
                'ref_no'          => $bkm->bkm_no,
                'ref_id'          => $bkm->bkm_id,
                'keterangan'      => "Penerimaan Kas/Bank ({$bkm->bkm_no}) - " . ($payload['penyetor_nama'] ?? $kategori) . " via {$namaAkun}",
                'created_by'      => $user->name ?? 'SYSTEM',
                'updated_by'      => $user->name ?? 'SYSTEM',
            ]);

            return $bkm;
        });
    }

    /**
     * Batalkan / Void Transaksi Bukti Kas Masuk (BKM)
     */
    public function voidKasMasuk(int $bkmId, string $alasan, User $user): DatKasMasukHdr
    {
        return DB::transaction(function () use ($bkmId, $alasan, $user) {
            $bkm = DatKasMasukHdr::lockForUpdate()->findOrFail($bkmId);

            if ($bkm->status_cd === 'VOID') {
                throw new Exception("Dokumen Kas Masuk {$bkm->bkm_no} sudah berstatus VOID.");
            }

            $kasBank = MstKasBank::lockForUpdate()->findOrFail($bkm->kas_bank_id);

            $saldoSebelum = (float) $kasBank->saldo_berjalan;
            $saldoSesudah = $saldoSebelum - (float) $bkm->total_terima;

            // Kurangi saldo kembali
            $kasBank->update([
                'saldo_berjalan' => $saldoSesudah,
                'updated_by'     => $user->name ?? 'SYSTEM',
            ]);

            // Catat pembalik mutasi kas (KELUAR)
            DatKasMutasi::create([
                'kas_bank_id'     => $kasBank->kas_bank_id,
                'tgl_mutasi'      => now()->toDateString(),
                'tipe_mutasi'     => 'KELUAR',
                'nominal'         => (float) $bkm->total_terima,
                'saldo_sebelum'   => $saldoSebelum,
                'saldo_sesudah'   => $saldoSesudah,
                'jenis_transaksi' => 'VOID_KAS_MASUK',
                'ref_no'          => 'VOID-' . $bkm->bkm_no,
                'ref_id'          => $bkm->bkm_id,
                'keterangan'      => "Pembatalan (VOID) Bukti Kas Masuk {$bkm->bkm_no}: {$alasan}",
                'created_by'      => $user->name ?? 'SYSTEM',
                'updated_by'      => $user->name ?? 'SYSTEM',
            ]);

            // Ubah status BKM jadi VOID beserta audit trail void
            $bkm->update([
                'status_cd'   => 'VOID',
                'alasan_void' => trim($alasan),
                'void_by'     => $user->name ?? 'SYSTEM',
                'void_dt'     => now(),
                'updated_by'  => $user->name ?? 'SYSTEM',
            ]);

            return $bkm;
        });
    }
}
