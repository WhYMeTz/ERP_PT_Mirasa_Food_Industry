<?php

namespace App\Models\Keuangan;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatKasMutasi extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_kas_mutasi';
    protected $primaryKey = 'mutasi_id';

    protected $fillable = [
        'kas_bank_id',
        'tgl_mutasi',
        'tipe_mutasi',
        'nominal',
        'saldo_sebelum',
        'saldo_sesudah',
        'jenis_transaksi',
        'ref_no',
        'ref_id',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'tgl_mutasi'    => 'date',
        'nominal'       => 'float',
        'saldo_sebelum' => 'float',
        'saldo_sesudah' => 'float',
        'deleted_st'    => 'boolean',
        'active_st'     => 'boolean',
    ];

    public function kasBank(): BelongsTo
    {
        return $this->belongsTo(MstKasBank::class, 'kas_bank_id', 'kas_bank_id');
    }
}
