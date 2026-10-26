<?php
// app/Models/Manajemen/TrxMultiPayroll.php
namespace App\Models\Manajemen;

use Illuminate\Database\Eloquent\Model;

class TrxMultiPayroll extends Model
{
    protected $table = 'trx_multi_payroll';
    protected $guarded = [];
    protected $casts = [
        'tanggal_transfer' => 'date',
    ];

    /** Nomor header yang disarankan: terakhir dipakai + 1, minimal header_awal di config. */
    public static function nomorBerikutnya(): int
    {
        $awal  = (int) config('multipayroll.header_awal', 1);
        $akhir = (int) static::max('header_id');

        return max($awal, $akhir + 1);
    }
}