<?php

namespace App\Services\Produksi;

use App\Models\Produksi\DatLaporanKemasOperator;
use App\Models\Produksi\DatProduksiDtl;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class LaporanKemasOperatorService
{
    /**
     * Generate Nomor Dokumen Laporan Kemas Operator: LKO-YYYYMM-XXXX
     */
    public function generateLaporanNo(?string $date = null): string
    {
        $dt = $date ? Carbon::parse($date) : now();
        $prefix = 'LKO-' . $dt->format('Ym') . '-';

        $last = DatLaporanKemasOperator::where('laporan_no', 'LIKE', "{$prefix}%")
            ->orderBy('laporan_no', 'desc')
            ->value('laporan_no');

        if ($last) {
            $lastNumber = (int) substr($last, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }

    /**
     * Mengambil daftar laporan pengemasan lapangan dengan filter & paginasi
     */
    public function getLaporanListPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DatLaporanKemasOperator::with([
            'barangJadi.satuanDasar',
            'wipBarang.satuanDasar',
            'produksi',
            'karyawan',
            'bumbuBarang',
            'plastikBarang',
            'kartonBarang',
            'lakbanBarang',
            'kemasanLainBarang',
            'packagingHdr',
        ])->where('deleted_st', false);

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('laporan_no', 'ILIKE', "%{$search}%")
                  ->orWhere('operator_nama', 'ILIKE', "%{$search}%")
                  ->orWhere('catatan', 'ILIKE', "%{$search}%")
                  ->orWhereHas('barangJadi', function ($bq) use ($search) {
                      $bq->where('barang_nm', 'ILIKE', "%{$search}%")
                         ->orWhere('barang_cd', 'ILIKE', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['tgl_kemas'])) {
            $query->whereDate('tgl_kemas', $filters['tgl_kemas']);
        }

        if (!empty($filters['shift'])) {
            $query->where('shift', $filters['shift']);
        }

        if (!empty($filters['status_cd'])) {
            $query->where('status_cd', $filters['status_cd']);
        }

        if (!empty($filters['barang_jadi_id'])) {
            $query->where('barang_jadi_id', $filters['barang_jadi_id']);
        }

        return $query->orderByDesc('tgl_kemas')
                     ->orderByDesc('laporan_id')
                     ->paginate($perPage)
                     ->withQueryString();
    }

    /**
     * Mengambil daftar laporan yang BELUM ditarik oleh Admin Office (Status: SUBMITTED)
     * Digunakan oleh form HPP FG (Packaging Office) untuk 1-click import.
     */
    public function getUnprocessedLaporanList(): Collection
    {
        return DatLaporanKemasOperator::with([
            'barangJadi.satuanDasar',
            'wipBarang.satuanDasar',
            'produksi',
            'karyawan',
            'bumbuBarang',
            'plastikBarang',
            'kartonBarang',
            'lakbanBarang',
            'kemasanLainBarang',
        ])
        ->where('deleted_st', false)
        ->where('status_cd', 'SUBMITTED')
        ->orderByDesc('tgl_kemas')
        ->orderByDesc('laporan_id')
        ->get();
    }

    /**
     * Dapatkan detail laporan berdasarkan ID
     */
    public function getById(int $id): DatLaporanKemasOperator
    {
        return DatLaporanKemasOperator::with([
            'barangJadi.satuanDasar',
            'wipBarang.satuanDasar',
            'produksi',
            'karyawan',
            'bumbuBarang',
            'plastikBarang',
            'kartonBarang',
            'lakbanBarang',
            'kemasanLainBarang',
            'packagingHdr',
        ])
        ->where('deleted_st', false)
        ->findOrFail($id);
    }

    /**
     * Simpan Laporan Pengemasan Baru dari Operator Lapangan
     */
    public function storeLaporan(array $payload, User $user): DatLaporanKemasOperator
    {
        return DB::transaction(function () use ($payload, $user) {
            $tglKemas = $payload['tgl_kemas'] ?? now()->toDateString();
            $laporanNo = $this->generateLaporanNo($tglKemas);

            $lakbanAtasGr = (float) ($payload['lakban_atas_gr'] ?? 0);
            $lakbanBawahGr = (float) ($payload['lakban_bawah_gr'] ?? 0);
            $lakbanQty = (float) ($payload['lakban_qty'] ?? 0);
            if ($lakbanQty <= 0 && ($lakbanAtasGr > 0 || $lakbanBawahGr > 0)) {
                $lakbanQty = $lakbanAtasGr + $lakbanBawahGr;
            } elseif ($lakbanQty > 0 && $lakbanAtasGr <= 0 && $lakbanBawahGr <= 0) {
                $lakbanAtasGr = round($lakbanQty / 2, 1);
                $lakbanBawahGr = round($lakbanQty / 2, 1);
            }

            $outputId = !empty($payload['output_id']) ? (int) $payload['output_id'] : null;
            $produksiId = !empty($payload['produksi_id']) ? (int) $payload['produksi_id'] : null;
            $wipBarangId = !empty($payload['wip_barang_id']) ? (int) $payload['wip_barang_id'] : null;
            $wipBatchNo = !empty($payload['wip_batch_no']) ? trim($payload['wip_batch_no']) : null;

            if ($outputId) {
                $output = DatProduksiDtl::find($outputId);
                if ($output) {
                    $produksiId = $output->produksi_id;
                    $wipBarangId = $output->barang_id;
                    if (empty($wipBatchNo)) {
                        $wipBatchNo = $output->batch_no;
                    }
                }
            }

            $laporan = DatLaporanKemasOperator::create([
                'laporan_no'             => $laporanNo,
                'tgl_kemas'              => $tglKemas,
                'shift'                  => $payload['shift'] ?? '1',
                'karyawan_id'            => !empty($payload['karyawan_id']) ? $payload['karyawan_id'] : null,
                'operator_nama'          => trim($payload['operator_nama'] ?? $user->name ?? 'Operator Kemas'),
                'jumlah_personil'        => (int) ($payload['jumlah_personil'] ?? 1),
                'jumlah_mesin'           => (int) ($payload['jumlah_mesin'] ?? 1),
                'produksi_id'            => $produksiId,
                'wip_barang_id'          => $wipBarangId,
                'output_id'              => $outputId,
                'wip_batch_no'           => $wipBatchNo,
                'wip_qty_kg'             => (float) ($payload['wip_qty_kg'] ?? 0),
                'wip_satuan'             => strtoupper(trim($payload['wip_satuan'] ?? 'KG')),
                'barang_jadi_id'         => (int) $payload['barang_jadi_id'],
                'hasil_karton_qty'       => (float) ($payload['hasil_karton_qty'] ?? 0),
                'hasil_pack_qty'         => (float) ($payload['hasil_pack_qty'] ?? 0),
                'satuan_hasil'           => $payload['satuan_hasil'] ?? 'KARTON',
                'bumbu_barang_id'        => !empty($payload['bumbu_barang_id']) ? $payload['bumbu_barang_id'] : null,
                'bumbu_qty_kg'           => (float) ($payload['bumbu_qty_kg'] ?? 0),
                'plastik_barang_id'      => !empty($payload['plastik_barang_id']) ? $payload['plastik_barang_id'] : null,
                'plastik_qty'            => (float) ($payload['plastik_qty'] ?? 0),
                'plastik_rusak_pcs'      => (float) ($payload['plastik_rusak_pcs'] ?? 0),
                'karton_barang_id'       => !empty($payload['karton_barang_id']) ? $payload['karton_barang_id'] : null,
                'karton_qty'             => (float) ($payload['karton_qty'] ?? 0),
                'karton_rusak_lembar'    => (float) ($payload['karton_rusak_lembar'] ?? 0),
                'lakban_atas_gr'         => $lakbanAtasGr,
                'lakban_bawah_gr'        => $lakbanBawahGr,
                'lakban_barang_id'       => !empty($payload['lakban_barang_id']) ? $payload['lakban_barang_id'] : null,
                'lakban_qty'             => $lakbanQty,
                'kemasan_lain_barang_id' => !empty($payload['kemasan_lain_barang_id']) ? $payload['kemasan_lain_barang_id'] : null,
                'kemasan_lain_qty'       => (float) ($payload['kemasan_lain_qty'] ?? 0),
                'catatan'                => !empty($payload['catatan']) ? trim($payload['catatan']) : null,
                'status_cd'              => 'SUBMITTED',
                'created_by'             => $user->name ?? 'OPERATOR',
                'updated_by'             => $user->name ?? 'OPERATOR',
            ]);

            return $laporan;
        });
    }

    /**
     * Tautkan Laporan Operator dengan Dokumen HPP FG yang telah dibuat Admin Office
     */
    public function markAsProcessed(int $laporanId, ?int $packId = null, ?User $user = null): DatLaporanKemasOperator
    {
        $laporan = DatLaporanKemasOperator::findOrFail($laporanId);
        $laporan->update([
            'status_cd'  => 'PROCESSED',
            'pack_id'    => $packId,
            'updated_by' => $user->name ?? 'OFFICE',
        ]);

        return $laporan;
    }
}
