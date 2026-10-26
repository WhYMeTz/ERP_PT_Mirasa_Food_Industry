<?php

namespace App\Models\MasterData;

use App\Models\User;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MstKaryawan extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_karyawan';
    protected $primaryKey = 'karyawan_id';

    protected $fillable = [
        'nik',
        'id_absensi', // Tambahan baru
        'karyawan_nm',
        'kelompok',    // Tambahan baru
        'shift',       // Tambahan baru
        'status_kerja',// Tambahan baru
        'departemen_cd',
        'jabatan_nm',
        'tgl_masuk',   // Tambahan baru
        'telepon_no',
        'email',
        'bank_name',   // Tambahan baru
        'no_rekening', // Tambahan baru
        'alamat_txt',  // Kolom alamat bawaan Anda
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'deleted_st' => 'boolean',
        'active_st'  => 'boolean',
    ];

    public const KELOMPOK = [
        'LANGSUNG IFM'     => 'Langsung IFM',
        'LANGSUNG MANUAL'  => 'Langsung Manual',
        'LANGSUNG PACKING' => 'Langsung Packing',
        'TIDAK LANGSUNG'   => 'Tidak Langsung',
    ];

    public const DEPARTEMEN = [
        'PRODUKSI'   => 'Departemen Produksi',
        'GUDANG'     => 'Departemen Gudang & Logistik',
        'PURCHASING' => 'Departemen Pengadaan (Purchasing)',
        'FINANCE'    => 'Departemen Keuangan & Kasir',
        'QC'         => 'Departemen Quality Control (QC)',
        'HRD'        => 'Departemen SDM / HRD',
        'MANAJEMEN'  => 'Manajemen / Direksi',
        'UMUM'       => 'Departemen Umum',
        'DRIVER'     => 'Driver',
        'CLEANING'   => 'Cleaning Service',
        'SECURITY'   => 'Security',
    ];

    public const DIVISI = [
        'UMUM', 'POT.RENDEM', 'SORTIR AC', 'BATCH FRYER', 'PACKING',
        'MAKARONI', 'DRIVER', 'SECURITY', 'KEUANGAN', 'QUALITYCONTROL',
    ];

    /**
     * Relasi ke akun user jika karyawan memiliki hak akses sistem
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'karyawan_id', 'karyawan_id');
    }
}
