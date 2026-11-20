<?php

namespace App\Models\Keuangan;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MstPengaturanJurnal extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_pengaturan_jurnal';
    protected $primaryKey = 'pengaturan_id';

    protected $fillable = [
        'grup',
        'kunci',
        'label',
        'deskripsi',
        'tipe_nilai',
        'nilai_coa_id',
        'nilai_text',
        'urutan',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'deleted_st' => 'boolean',
        'active_st'  => 'boolean',
        'urutan'     => 'integer',
        'version_no' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('deleted_st', false)->where('active_st', true);
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(MstCoa::class, 'nilai_coa_id', 'coa_id');
    }
}
