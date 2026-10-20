<?php

namespace App\Services\MasterData;

use App\Models\MasterData\MstMesin;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MesinService
{
    /**
     * Mengambil daftar master mesin dengan pagination, filter pencarian, dan filter kategori.
     */
    public function getAllPaginated(int $perPage = 15, ?string $search = null, ?string $kategori = null): LengthAwarePaginator
    {
        $query = MstMesin::where('deleted_st', false);

        if (!empty($search)) {
            $query->search($search);
        }

        if (!empty($kategori) && strtoupper($kategori) !== 'SEMUA') {
            $query->kategori($kategori);
        }

        return $query->orderBy('kategori_mesin', 'asc')
            ->orderBy('mesin_id', 'asc')
            ->paginate($perPage);
    }

    /**
     * Mengambil ringkasan statistik total mesin untuk KPI card di header.
     */
    public function getSummaryCounts(): array
    {
        $base = MstMesin::where('deleted_st', false);

        return [
            'total'       => (clone $base)->count(),
            'produksi'    => (clone $base)->where('kategori_mesin', 'PRODUKSI')->count(),
            'packaging'   => (clone $base)->where('kategori_mesin', 'PACKAGING')->count(),
            'siap'        => (clone $base)->where('status_kondisi', 'SIAP')->where('active_st', true)->count(),
            'maintenance' => (clone $base)->whereIn('status_kondisi', ['MAINTENANCE', 'RUSAK'])->count(),
        ];
    }

    /**
     * Mengambil seluruh mesin operasional aktif.
     */
    public function getAllActive(): Collection
    {
        return MstMesin::where('deleted_st', false)
            ->where('active_st', true)
            ->orderBy('kategori_mesin', 'asc')
            ->orderBy('mesin_nm', 'asc')
            ->get();
    }

    /**
     * Mengambil seluruh mesin kategori PRODUKSI yang aktif dan siap.
     */
    public function getProduksiActive(): Collection
    {
        return MstMesin::where('deleted_st', false)
            ->where('active_st', true)
            ->produksi()
            ->orderBy('mesin_nm', 'asc')
            ->get();
    }

    /**
     * Mengambil seluruh mesin kategori PACKAGING yang aktif dan siap.
     */
    public function getPackagingActive(): Collection
    {
        return MstMesin::where('deleted_st', false)
            ->where('active_st', true)
            ->packaging()
            ->orderBy('mesin_nm', 'asc')
            ->get();
    }

    /**
     * Mengambil satu data mesin berdasarkan ID.
     */
    public function getById(int $id): MstMesin
    {
        return MstMesin::where('deleted_st', false)
            ->where('mesin_id', $id)
            ->firstOrFail();
    }

    /**
     * Menyimpan data mesin baru dengan DB Transaction.
     */
    public function store(array $data): MstMesin
    {
        return DB::transaction(function () use ($data) {
            $data['mesin_cd'] = strtoupper(trim($data['mesin_cd']));
            $data['mesin_nm'] = trim($data['mesin_nm']);
            $data['kategori_mesin'] = strtoupper(trim($data['kategori_mesin'] ?? 'PRODUKSI'));
            $data['jenis_proses'] = !empty($data['jenis_proses']) ? strtoupper(trim($data['jenis_proses'])) : null;
            $data['satuan_kapasitas'] = !empty($data['satuan_kapasitas']) ? strtoupper(trim($data['satuan_kapasitas'])) : ($data['kategori_mesin'] === 'PRODUKSI' ? 'KG/JAM' : 'KEMASAN/MENIT');
            $data['tipe_energi'] = !empty($data['tipe_energi']) ? strtoupper(trim($data['tipe_energi'])) : null;
            $data['status_kondisi'] = strtoupper(trim($data['status_kondisi'] ?? 'SIAP'));

            return MstMesin::create($data);
        });
    }

    /**
     * Memperbarui data mesin dengan DB Transaction.
     */
    public function update(int $id, array $data): MstMesin
    {
        return DB::transaction(function () use ($id, $data) {
            $mesin = MstMesin::findOrFail($id);

            if (isset($data['mesin_cd'])) {
                $data['mesin_cd'] = strtoupper(trim($data['mesin_cd']));
            }
            if (isset($data['mesin_nm'])) {
                $data['mesin_nm'] = trim($data['mesin_nm']);
            }
            if (isset($data['kategori_mesin'])) {
                $data['kategori_mesin'] = strtoupper(trim($data['kategori_mesin']));
            }
            if (isset($data['jenis_proses'])) {
                $data['jenis_proses'] = !empty($data['jenis_proses']) ? strtoupper(trim($data['jenis_proses'])) : null;
            }
            if (isset($data['satuan_kapasitas'])) {
                $data['satuan_kapasitas'] = !empty($data['satuan_kapasitas']) ? strtoupper(trim($data['satuan_kapasitas'])) : null;
            }
            if (isset($data['tipe_energi'])) {
                $data['tipe_energi'] = !empty($data['tipe_energi']) ? strtoupper(trim($data['tipe_energi'])) : null;
            }
            if (isset($data['status_kondisi'])) {
                $data['status_kondisi'] = strtoupper(trim($data['status_kondisi']));
            }

            $mesin->update($data);
            return $mesin->fresh();
        });
    }

    /**
     * Toggle status aktif mesin (Aktif / Nonaktif).
     */
    public function toggleStatus(int $id): MstMesin
    {
        return DB::transaction(function () use ($id) {
            $mesin = MstMesin::findOrFail($id);
            $userId = Auth::id() ?? 'SYSTEM';

            $mesin->update([
                'active_st'  => !$mesin->active_st,
                'updated_by' => (string) $userId,
            ]);

            return $mesin->fresh();
        });
    }

    /**
     * Melakukan Soft Delete dengan menandai deleted_st = true.
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $mesin = MstMesin::findOrFail($id);
            $userId = Auth::id() ?? 'SYSTEM';

            return $mesin->update([
                'deleted_st' => true,
                'active_st'  => false,
                'deleted_at' => now(),
                'deleted_by' => (string) $userId,
            ]);
        });
    }
}
