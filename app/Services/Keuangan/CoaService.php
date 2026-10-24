<?php

namespace App\Services\Keuangan;

use App\Models\Keuangan\MstCoa;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CoaService
{
    /**
     * Ambil seluruh daftar akun dengan filter pencarian dan kategori
     */
    public function getAll(array $filters = []): Collection
    {
        $query = MstCoa::active()->orderBy('kode_akun', 'asc');

        if (!empty($filters['search'])) {
            $search = strtolower(trim($filters['search']));
            $query->where(function ($q) use ($search) {
                $q->where('kode_akun', 'ILIKE', "%{$search}%")
                  ->orWhere('nama_akun', 'ILIKE', "%{$search}%")
                  ->orWhere('sub_kategori', 'ILIKE', "%{$search}%");
            });
        }

        if (!empty($filters['kategori']) && $filters['kategori'] !== 'ALL') {
            $query->where('kategori_akun', strtoupper($filters['kategori']));
        }

        if (isset($filters['is_kas_bank']) && $filters['is_kas_bank'] !== '') {
            $query->where('is_kas_bank', filter_var($filters['is_kas_bank'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->get();
    }

    /**
     * Hitung rekap jumlah akun per kategori untuk badge tab
     */
    public function getSummaryCounts(): array
    {
        $all = MstCoa::active()->select('kategori_akun', 'is_kas_bank')->get();

        return [
            'total'      => $all->count(),
            'aktiva'     => $all->where('kategori_akun', 'AKTIVA')->count(),
            'kas_bank'   => $all->where('is_kas_bank', true)->count(),
            'pasiva'     => $all->where('kategori_akun', 'PASIVA')->count(),
            'modal'      => $all->where('kategori_akun', 'MODAL')->count(),
            'pendapatan' => $all->where('kategori_akun', 'PENDAPATAN')->count(),
            'hpp'        => $all->where('kategori_akun', 'HPP')->count(),
            'beban'      => $all->where('kategori_akun', 'BEBAN')->count(),
        ];
    }

    /**
     * Simpan akun COA baru
     */
    public function store(array $data, ?User $user = null): MstCoa
    {
        return DB::transaction(function () use ($data, $user) {
            $userId = $user?->name ?? 'SYSTEM';

            $saldoAwal = isset($data['saldo_awal']) ? (float) $data['saldo_awal'] : 0;

            return MstCoa::create([
                'kode_akun'        => trim($data['kode_akun']),
                'nama_akun'        => trim($data['nama_akun']),
                'kategori_akun'    => strtoupper(trim($data['kategori_akun'])),
                'sub_kategori'     => !empty($data['sub_kategori']) ? trim($data['sub_kategori']) : null,
                'posisi_keuangan'  => strtoupper(trim($data['posisi_keuangan'] ?? 'NERACA')),
                'saldo_normal'     => strtoupper(trim($data['saldo_normal'] ?? 'DEBET')),
                'saldo_awal'       => $saldoAwal,
                'saldo_berjalan'   => $saldoAwal,
                'is_kas_bank'      => !empty($data['is_kas_bank']),
                'keterangan'       => $data['keterangan'] ?? null,
                'created_by'       => $userId,
                'updated_by'       => $userId,
                'active_st'        => true,
                'deleted_st'       => false,
            ]);
        });
    }

    /**
     * Perbarui akun COA
     */
    public function update(int $id, array $data, ?User $user = null): MstCoa
    {
        return DB::transaction(function () use ($id, $data, $user) {
            $coa = MstCoa::active()->findOrFail($id);
            $userId = $user?->name ?? 'SYSTEM';

            $saldoAwal = isset($data['saldo_awal']) ? (float) $data['saldo_awal'] : $coa->saldo_awal;
            // Jika saldo berjalan masih sama dengan saldo awal lama, ikut sesuaikan
            $saldoBerjalan = ($coa->saldo_berjalan == $coa->saldo_awal) ? $saldoAwal : $coa->saldo_berjalan;

            $coa->update([
                'kode_akun'        => trim($data['kode_akun']),
                'nama_akun'        => trim($data['nama_akun']),
                'kategori_akun'    => strtoupper(trim($data['kategori_akun'])),
                'sub_kategori'     => !empty($data['sub_kategori']) ? trim($data['sub_kategori']) : null,
                'posisi_keuangan'  => strtoupper(trim($data['posisi_keuangan'] ?? $coa->posisi_keuangan)),
                'saldo_normal'     => strtoupper(trim($data['saldo_normal'] ?? $coa->saldo_normal)),
                'saldo_awal'       => $saldoAwal,
                'saldo_berjalan'   => $saldoBerjalan,
                'is_kas_bank'      => !empty($data['is_kas_bank']),
                'keterangan'       => $data['keterangan'] ?? null,
                'updated_by'       => $userId,
            ]);

            return $coa->fresh();
        });
    }

    /**
     * Soft delete akun COA
     */
    public function delete(int $id, ?User $user = null): bool
    {
        return DB::transaction(function () use ($id, $user) {
            $coa = MstCoa::active()->findOrFail($id);

            // Validasi: Jangan hapus jika saldo berjalan tidak nol
            if (abs($coa->saldo_berjalan) > 0.0001) {
                throw new Exception("Akun {$coa->kode_akun} ({$coa->nama_akun}) tidak dapat dihapus karena masih memiliki saldo berjalan sebesar Rp " . number_format($coa->saldo_berjalan, 0, ',', '.') . ".");
            }

            $userId = $user?->name ?? 'SYSTEM';

            return $coa->update([
                'deleted_st' => true,
                'active_st'  => false,
                'deleted_at' => now(),
                'deleted_by' => $userId,
            ]);
        });
    }
}
