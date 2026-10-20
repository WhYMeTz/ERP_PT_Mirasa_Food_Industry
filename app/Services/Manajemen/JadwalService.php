<?php

namespace App\Services\Manajemen;

use App\Models\Manajemen\TrxJadwalKaryawan;
use Illuminate\Support\Facades\DB;

class JadwalService
{
    public function simpan(array $rows): array
    {
        $now  = now();
        $data = array_map(fn ($r) => $r + ['created_at' => $now, 'updated_at' => $now], $rows);

        DB::transaction(function () use ($data) {
            foreach (array_chunk($data, 500) as $chunk) {
                TrxJadwalKaryawan::upsert(
                    $chunk,
                    ['tanggal', 'id_absen'],
                    ['nama', 'kelompok_kerja', 'kode_shift', 'istirahat_jam', 'updated_at']
                );
            }
        });

        return [
            'baris'  => count($rows),
            'dari'   => collect($rows)->min('tanggal'),
            'sampai' => collect($rows)->max('tanggal'),
        ];
    }
}