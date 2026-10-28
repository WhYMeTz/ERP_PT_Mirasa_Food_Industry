<?php

namespace App\Services\MasterData;

use App\Models\MasterData\MstPajak;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PajakService
{
    /**
     * Mengambil daftar master pajak dengan paginasi dan filter pencarian.
     */
    public function getAllPaginated(
        int $perPage = 15,
        ?string $search = null,
        string $kategori = 'SEMUA',
        string $status = 'SEMUA'
    ): LengthAwarePaginator {
        $query = MstPajak::with(['coaPembelian', 'coaPenjualan'])->where('deleted_st', false);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('pajak_cd', 'ILIKE', "%{$search}%")
                  ->orWhere('pajak_nm', 'ILIKE', "%{$search}%")
                  ->orWhere('keterangan', 'ILIKE', "%{$search}%");
            });
        }

        if ($kategori !== 'SEMUA' && !empty($kategori)) {
            $query->where('kategori_pajak', $kategori);
        }

        if ($status === 'AKTIF') {
            $query->where('active_st', true);
        } elseif ($status === 'NONAKTIF') {
            $query->where('active_st', false);
        }

        return $query->orderBy('is_default', 'desc')
            ->orderBy('sifat_pajak', 'asc')
            ->orderBy('pajak_cd', 'asc')
            ->paginate($perPage);
    }

    /**
     * Mengambil ringkasan jumlah pajak untuk KPI Cards.
     */
    public function getSummaryCounts(): array
    {
        $base = MstPajak::where('deleted_st', false);

        return [
            'total'       => (clone $base)->count(),
            'ppn'         => (clone $base)->where('is_pembelian', true)->where('is_penjualan', true)->count(),
            'pembelian'   => (clone $base)->where('is_pembelian', true)->count(),
            'penjualan'   => (clone $base)->where('is_penjualan', true)->count(),
            'pph'         => (clone $base)->where('sifat_pajak', 'POTONG')->count(),
            'withholding' => (clone $base)->where('sifat_pajak', 'POTONG')->count(),
            'aktif'       => (clone $base)->where('active_st', true)->count(),
            'nonaktif'    => (clone $base)->where('active_st', false)->count(),
        ];
    }

    /**
     * Mengambil data pajak berdasarkan ID.
     */
    public function getById(int $id): MstPajak
    {
        return MstPajak::with(['coaPembelian', 'coaPenjualan'])
            ->where('deleted_st', false)
            ->findOrFail($id);
    }

    /**
     * Menyimpan data master pajak baru.
     */
    public function store(array $data): MstPajak
    {
        return DB::transaction(function () use ($data) {
            $data['pajak_cd'] = strtoupper(trim($data['pajak_cd']));
            $data['pajak_nm'] = trim($data['pajak_nm']);
            $data['sifat_pajak'] = strtoupper(trim($data['sifat_pajak'] ?? 'TAMBAH'));
            $data['tarif_persen'] = (float) ($data['tarif_persen'] ?? 0);
            
            $data['is_pembelian'] = isset($data['is_pembelian']) ? (bool) $data['is_pembelian'] : true;
            $data['is_penjualan'] = isset($data['is_penjualan']) ? (bool) $data['is_penjualan'] : false;
            $data['coa_pembelian_id'] = $data['is_pembelian'] ? ($data['coa_pembelian_id'] ?? null) : null;
            $data['coa_penjualan_id'] = $data['is_penjualan'] ? ($data['coa_penjualan_id'] ?? null) : null;

            // Sinkronkan coa_id kompatibilitas
            $data['coa_id'] = $data['coa_pembelian_id'] ?? $data['coa_penjualan_id'] ?? null;
            $data['active_st'] = isset($data['active_st']) ? (bool) $data['active_st'] : true;
            $data['is_default'] = !empty($data['is_default']);

            // Jika diset default, nonaktifkan is_default pada pajak lain dalam sifat yang sama
            if ($data['is_default']) {
                MstPajak::where('sifat_pajak', $data['sifat_pajak'])
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            return MstPajak::create($data);
        });
    }

    /**
     * Memperbarui data master pajak.
     */
    public function update(int $id, array $data): MstPajak
    {
        return DB::transaction(function () use ($id, $data) {
            $pajak = $this->getById($id);

            $data['pajak_cd'] = strtoupper(trim($data['pajak_cd']));
            $data['pajak_nm'] = trim($data['pajak_nm']);
            $data['sifat_pajak'] = strtoupper(trim($data['sifat_pajak'] ?? $pajak->sifat_pajak));
            $data['tarif_persen'] = (float) ($data['tarif_persen'] ?? $pajak->tarif_persen);
            
            $data['is_pembelian'] = isset($data['is_pembelian']) ? (bool) $data['is_pembelian'] : false;
            $data['is_penjualan'] = isset($data['is_penjualan']) ? (bool) $data['is_penjualan'] : false;
            $data['coa_pembelian_id'] = $data['is_pembelian'] ? ($data['coa_pembelian_id'] ?? null) : null;
            $data['coa_penjualan_id'] = $data['is_penjualan'] ? ($data['coa_penjualan_id'] ?? null) : null;

            $data['coa_id'] = $data['coa_pembelian_id'] ?? $data['coa_penjualan_id'] ?? null;
            if (isset($data['active_st'])) {
                $data['active_st'] = (bool) $data['active_st'];
            }
            $data['is_default'] = !empty($data['is_default']);

            if ($data['is_default']) {
                MstPajak::where('sifat_pajak', $data['sifat_pajak'])
                    ->where('pajak_id', '!=', $id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $pajak->update($data);

            return $pajak->fresh(['coa']);
        });
    }

    /**
     * Toggle status aktif / nonaktif master pajak.
     */
    public function toggleStatus(int $id): MstPajak
    {
        return DB::transaction(function () use ($id) {
            $pajak = $this->getById($id);

            $newStatus = !$pajak->active_st;
            $pajak->active_st = $newStatus;
            if (!$newStatus) {
                $pajak->is_default = false; // Jika dinonaktifkan, hilangkan status default
            }
            $pajak->save();

            return $pajak;
        });
    }

    /**
     * Menghapus (Soft Delete) master pajak.
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $pajak = $this->getById($id);

            if ($pajak->is_default) {
                throw new Exception("Pajak '{$pajak->pajak_nm}' adalah tarif default sistem dan tidak dapat dihapus langsung. Harap ganti default terlebih dahulu.");
            }

            $pajak->deleted_st = true;
            $pajak->active_st = false;
            $pajak->save();

            return true;
        });
    }

    /**
     * Mengambil daftar pajak aktif untuk dropdown transaksi (PO, Faktur, Kas/Bank).
     */
    public function getActivePajakList(?string $sifat = null): Collection
    {
        $query = MstPajak::where('deleted_st', false)
            ->where('active_st', true)
            ->with('coa');

        if (!empty($sifat)) {
            $query->where('sifat_pajak', $sifat);
        }

        return $query->orderBy('is_default', 'desc')
            ->orderBy('tarif_persen', 'desc')
            ->get();
    }
}
