<?php

namespace App\Models\Produksi;

use App\Models\Gudang\DatPakaiHdr;
use App\Models\MasterData\MstGudang;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatProduksiHarian extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_produksi_harian';
    protected $primaryKey = 'produksi_id';

    protected $fillable = [
        'produksi_id',
        'produksi_no',
        'produksi_tgl',
        'hari_nm',
        'pakai_id',
        'gudang_id',
        'lini_produksi',
        'shift_cd',
        'jam_produksi',
        'varietas_singkong',
        'qty_karton',
        'no_karton_awal',
        'no_karton_akhir',
        'status_cd',

        // Biaya Bahan
        'singkong_qty',
        'singkong_nilai',
        'minyak_sawit_qty',
        'minyak_kelapa_qty',
        'minyak_nilai',
        'minyak_rasio_persen',
        'bumbu_nilai',
        'karton_baru_nilai',
        'karton_bekas_nilai',
        'plastik_hd_nilai',
        'lakban_besar_nilai',
        'lakban_kecil_nilai',
        'tali_rafia_nilai',
        'total_bahan_nilai',

        // Energi & CNG
        'cng_mmbtu',
        'cng_tarif',
        'cng_nilai',

        // Tenaga Kerja
        'tk_langsung_org',
        'tk_tidak_langsung_org',
        'tk_training_org',
        'tk_tarif_per_org',
        'tk_total_nilai',

        // Overhead FOH
        'fotocopy_nilai',
        'sarung_tangan_plastik_nilai',
        'sarung_tangan_kain_nilai',
        'qc_pengawasan_nilai',
        'listrik_air_telp_nilai',
        'pemeliharaan_mesin_nilai',
        'penyusutan_mesin_nilai',
        'limbah_padat_nilai',
        'limbah_kimia_nilai',
        'total_overhead_nilai',

        // Total Biaya
        'total_biaya_produksi',

        // Output Timbangan WIP (Kg)
        'asin_barco_qty',
        'asin_sawit_qty',
        'no_salt_qty',
        'balo_gelombang_qty',
        'berko_qty',
        'berko_me_qty',
        'total_berko_qty',
        'berko_persen',
        'total_wip_qty',

        // Metrik
        'rendemen_persen',
        'hpp_per_kg',
        'batch_wip_no',
        'catatan_txt',

        // Audit Trail
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'produksi_tgl'                => 'date',
        'qty_karton'                  => 'integer',
        'no_karton_awal'              => 'integer',
        'no_karton_akhir'             => 'integer',
        'singkong_qty'                => 'decimal:4',
        'singkong_nilai'              => 'decimal:2',
        'minyak_sawit_qty'            => 'decimal:4',
        'minyak_kelapa_qty'           => 'decimal:4',
        'minyak_nilai'                => 'decimal:2',
        'minyak_rasio_persen'         => 'decimal:2',
        'bumbu_nilai'                 => 'decimal:2',
        'karton_baru_nilai'           => 'decimal:2',
        'karton_bekas_nilai'          => 'decimal:2',
        'plastik_hd_nilai'            => 'decimal:2',
        'lakban_besar_nilai'          => 'decimal:2',
        'lakban_kecil_nilai'          => 'decimal:2',
        'tali_rafia_nilai'            => 'decimal:2',
        'total_bahan_nilai'           => 'decimal:2',
        'cng_mmbtu'                   => 'decimal:4',
        'cng_tarif'                   => 'decimal:2',
        'cng_nilai'                   => 'decimal:2',
        'tk_langsung_org'             => 'integer',
        'tk_tidak_langsung_org'       => 'integer',
        'tk_training_org'             => 'integer',
        'tk_tarif_per_org'            => 'decimal:2',
        'tk_total_nilai'              => 'decimal:2',
        'fotocopy_nilai'              => 'decimal:2',
        'sarung_tangan_plastik_nilai' => 'decimal:2',
        'sarung_tangan_kain_nilai'    => 'decimal:2',
        'qc_pengawasan_nilai'         => 'decimal:2',
        'listrik_air_telp_nilai'      => 'decimal:2',
        'pemeliharaan_mesin_nilai'    => 'decimal:2',
        'penyusutan_mesin_nilai'      => 'decimal:2',
        'limbah_padat_nilai'          => 'decimal:2',
        'limbah_kimia_nilai'          => 'decimal:2',
        'total_overhead_nilai'        => 'decimal:2',
        'total_biaya_produksi'        => 'decimal:2',
        'asin_barco_qty'              => 'decimal:4',
        'asin_sawit_qty'              => 'decimal:4',
        'no_salt_qty'                 => 'decimal:4',
        'balo_gelombang_qty'          => 'decimal:4',
        'berko_qty'                   => 'decimal:4',
        'berko_me_qty'                => 'decimal:4',
        'total_berko_qty'             => 'decimal:4',
        'berko_persen'                => 'decimal:2',
        'total_wip_qty'               => 'decimal:4',
        'rendemen_persen'             => 'decimal:2',
        'hpp_per_kg'                  => 'decimal:2',
        'deleted_st'                  => 'boolean',
        'active_st'                   => 'boolean',
    ];

    /**
     * Relasi ke Dokumen Pemakaian Bahan Gudang
     */
    public function pemakaianBahan(): BelongsTo
    {
        return $this->belongsTo(DatPakaiHdr::class, 'pakai_id', 'pakai_id');
    }

    /**
     * Relasi ke Gudang Hasil Olahan WIP
     */
    public function gudang(): BelongsTo
    {
        return $this->belongsTo(MstGudang::class, 'gudang_id', 'gudang_id');
    }

    /**
     * Relasi ke Rincian Output Barang WIP
     */
    public function outputs(): HasMany
    {
        return $this->hasMany(DatProduksiOutput::class, 'produksi_id', 'produksi_id')
            ->where('deleted_st', false);
    }
}
