<?php

namespace App\Services\MasterData;

use App\Models\Keuangan\MstCoa;
use App\Models\Keuangan\MstKategoriPengeluaran;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KategoriPengeluaranService
{
    /**
     * Mengambil daftar master kategori pengeluaran dengan paginasi dan filter pencarian.
     */
    public function getAllPaginated(
        int $perPage = 15,
        ?string $search = null,
        string $status = 'SEMUA'
    ): LengthAwarePaginator {
        $query = MstKategoriPengeluaran::with('coaDefault')
            ->where('deleted_st', false);

        if (!empty($search)) {
            $search = trim($search);
            $query->where(function ($q) use ($search) {
                $q->where('kode_kategori', 'ILIKE', "%{$search}%")
                  ->orWhere('nama_kategori', 'ILIKE', "%{$search}%")
                  ->orWhere('keterangan', 'ILIKE', "%{$search}%");
            });
        }

        if ($status === 'AKTIF') {
            $query->where('active_st', true);
        } elseif ($status === 'NONAKTIF') {
            $query->where('active_st', false);
        }

        return $query->orderBy('nama_kategori', 'asc')
            ->paginate($perPage);
    }

    /**
     * Mengambil ringkasan jumlah kategori untuk Metric Cards.
     */
    public function getSummaryCounts(): array
    {
        $base = MstKategoriPengeluaran::where('deleted_st', false);

        return [
            'total'      => (clone $base)->count(),
            'aktif'      => (clone $base)->where('active_st', true)->count(),
            'nonaktif'   => (clone $base)->where('active_st', false)->count(),
            'coa_mapped' => (clone $base)->whereNotNull('coa_id_default')->count(),
        ];
    }

    /**
     * Mengambil daftar akun COA beban untuk dropdown pemetaan akun default.
     */
    public function getCoaBebanList(): Collection
    {
        return MstCoa::where('deleted_st', false)
            ->where('active_st', true)
            ->where(function ($q) {
                $q->where('kategori_akun', 'BEBAN')
                  ->orWhere('kategori_akun', 'HPP')
                  ->orWhere('kode_akun', 'LIKE', '5%')
                  ->orWhere('kode_akun', 'LIKE', '6%');
            })
            ->orderBy('kode_akun', 'asc')
            ->get(['coa_id', 'kode_akun', 'nama_akun', 'kategori_akun']);
    }

    /**
     * Mengambil detail satu data kategori berdasarkan ID.
     */
    public function getById(int $id): MstKategoriPengeluaran
    {
        return MstKategoriPengeluaran::with('coaDefault')
            ->where('deleted_st', false)
            ->findOrFail($id);
    }

    /**
     * Menyimpan data master kategori pengeluaran baru.
     */
    public function store(array $data): MstKategoriPengeluaran
    {
        return DB::transaction(function () use ($data) {
            $userId = Auth::id() ?? 1;

            $kodeKategori = !empty($data['kode_kategori'])
                ? strtoupper(trim($data['kode_kategori']))
                : strtoupper(Str::slug($data['nama_kategori'], '_'));

            $kategori = new MstKategoriPengeluaran();
            $kategori->kode_kategori = $kodeKategori;
            $kategori->nama_kategori = trim($data['nama_kategori']);
            $kategori->coa_id_default = !empty($data['coa_id_default']) ? (int) $data['coa_id_default'] : null;
            $kategori->keterangan = !empty($data['keterangan']) ? trim($data['keterangan']) : null;
            $kategori->active_st = isset($data['active_st']) ? (bool) $data['active_st'] : true;
            $kategori->deleted_st = false;
            $kategori->version_no = 1;
            $kategori->created_by = (string) $userId;
            $kategori->updated_by = (string) $userId;
            $kategori->save();

            return $kategori->load('coaDefault');
        });
    }

    /**
     * Memperbarui data master kategori pengeluaran.
     */
    public function update(int $id, array $data): MstKategoriPengeluaran
    {
        return DB::transaction(function () use ($id, $data) {
            $userId = Auth::id() ?? 1;
            $kategori = $this->getById($id);

            $kodeKategori = !empty($data['kode_kategori'])
                ? strtoupper(trim($data['kode_kategori']))
                : $kategori->kode_kategori;

            $kategori->kode_kategori = $kodeKategori;
            $kategori->nama_kategori = trim($data['nama_kategori']);
            $kategori->coa_id_default = !empty($data['coa_id_default']) ? (int) $data['coa_id_default'] : null;
            $kategori->keterangan = !empty($data['keterangan']) ? trim($data['keterangan']) : null;

            if (isset($data['active_st'])) {
                $kategori->active_st = (bool) $data['active_st'];
            }

            $kategori->version_no = ($kategori->version_no ?? 1) + 1;
            $kategori->updated_by = (string) $userId;
            $kategori->save();

            return $kategori->load('coaDefault');
        });
    }

    /**
     * Menghapus (Soft Delete) data kategori pengeluaran.
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $userId = Auth::id() ?? 1;
            $kategori = $this->getById($id);

            $kategori->deleted_st = true;
            $kategori->active_st = false;
            $kategori->deleted_by = (string) $userId;
            $kategori->deleted_at = now();
            $kategori->version_no = ($kategori->version_no ?? 1) + 1;
            $kategori->save();

            return true;
        });
    }

    /**
     * Toggle status aktif / nonaktif.
     */
    public function toggleStatus(int $id): MstKategoriPengeluaran
    {
        return DB::transaction(function () use ($id) {
            $userId = Auth::id() ?? 1;
            $kategori = $this->getById($id);

            $kategori->active_st = !$kategori->active_st;
            $kategori->updated_by = (string) $userId;
            $kategori->version_no = ($kategori->version_no ?? 1) + 1;
            $kategori->save();

            return $kategori;
        });
    }
}
