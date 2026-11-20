<?php
// app/Models/Manajemen/TrxSlipWa.php
namespace App\Models\Manajemen;

use Illuminate\Database\Eloquent\Model;

class TrxSlipWa extends Model
{
    protected $table = 'trx_slip_wa';
    protected $guarded = [];

    /** Status yang berarti slip sudah diterima WhatsApp API (jangan kirim ulang tanpa konfirmasi). */
    public const BERHASIL = ['terkirim_api', 'delivered', 'read'];
}
