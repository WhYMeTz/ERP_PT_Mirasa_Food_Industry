<?php
// app/Models/Manajemen/MstKodeShift.php
namespace App\Models\Manajemen;

use Illuminate\Database\Eloquent\Model;

class MstKodeShift extends Model
{
    protected $table = 'mst_kode_shift';
    protected $guarded = [];
    protected $casts = ['lintas_hari' => 'boolean', 'aktif' => 'boolean'];
}