<?php

namespace App\Models\MasterData;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MstJenisSupplier extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_jenis_supplier';
    protected $primaryKey = 'jenis_supplier_id';

    protected $fillable = [
        'jenis_supplier_cd',
        'jenis_supplier_nm',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'deleted_st' => 'boolean',
        'active_st' => 'boolean',
    ];

    /**
     * Relasi ke master supplier
     */
    public function supplier(): HasMany
    {
        return $this->hasMany(MstSupplier::class, 'jenis_supplier_id', 'jenis_supplier_id');
    }
}
