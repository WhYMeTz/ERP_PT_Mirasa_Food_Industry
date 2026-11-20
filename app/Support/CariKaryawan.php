<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Pencarian karyawan yang seragam di semua halaman:
 * - tidak peka huruf besar/kecil
 * - dicocokkan dari AWAL teks (awalan), bukan di sembarang tempat
 * - ID Absen tidak peduli angka nol di depan (ketik 504 menemukan 000000504)
 */
class CariKaryawan
{
    public static function terapkan($query, ?string $kata, string $kolNama = 'nama', string $kolId = 'id_absen', ?string $kolNik = null)
    {
        $kata = trim((string) $kata);
        if ($kata === '') {
            return $query;
        }

        $op      = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $aman    = addcslashes($kata, '\\%_');               // karakter % dan _ dari pengguna diperlakukan biasa
        $idAman  = addcslashes(ltrim($kata, '0'), '\\%_');   // abaikan nol di depan

        return $query->where(function ($w) use ($op, $aman, $idAman, $kolNama, $kolId, $kolNik) {
            $w->where($kolNama, $op, $aman . '%');
            if ($kolNik) {
                $w->orWhere($kolNik, $op, $aman . '%');
            }
            if ($idAman !== '') {
                $w->orWhereRaw("ltrim({$kolId}, '0') {$op} ?", [$idAman . '%']);
            }
        });
    }
}