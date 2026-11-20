<?php

namespace App\Models\MasterData;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MstSupplier extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_supplier';
    protected $primaryKey = 'supplier_id';

    protected $fillable = [
        'supplier_cd',
        'supplier_nm',
        'jenis_supplier_id',
        'kontak_no',
        'email',
        'alamat_txt',
        'top_hari',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'top_hari'   => 'integer',
        'deleted_st' => 'boolean',
        'active_st'  => 'boolean',
    ];

    /**
     * Relasi ke master jenis supplier
     */
    public function jenisSupplier(): BelongsTo
    {
        return $this->belongsTo(MstJenisSupplier::class, 'jenis_supplier_id', 'jenis_supplier_id');
    }
}
