<?php

namespace App\Models\Produksi;

use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstKaryawan;
use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatLaporanKemasOperator extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'dat_laporan_kemas_operator';
    protected $primaryKey = 'laporan_id';

    protected $fillable = [
        'laporan_no',
        'tgl_kemas',
        'shift',
        'karyawan_id',
        'operator_nama',
        'jumlah_personil',
        'jumlah_mesin',
        'produksi_id',
        'wip_barang_id',
        'output_id',
        'wip_batch_no',
        'wip_qty_kg',
        'wip_satuan',
        'barang_jadi_id',
        'hasil_karton_qty',
        'hasil_pack_qty',
        'satuan_hasil',
        'bumbu_barang_id',
        'bumbu_qty_kg',
        'plastik_barang_id',
        'plastik_qty',
        'plastik_rusak_pcs',
        'karton_barang_id',
        'karton_qty',
        'karton_rusak_lembar',
        'lakban_atas_gr',
        'lakban_bawah_gr',
        'lakban_barang_id',
        'lakban_qty',
        'expired_barang_id',
        'expired_qty',
        'kemasan_lain_barang_id',
        'kemasan_lain_qty',
        'kemasan_tambahan_items',
        'catatan',
        'status_cd',
        'pack_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
        'version_no',
    ];

    protected $casts = [
        'tgl_kemas'              => 'date',
        'jumlah_personil'        => 'integer',
        'jumlah_mesin'           => 'integer',
        'wip_qty_kg'             => 'float',
        'hasil_karton_qty'       => 'float',
        'hasil_pack_qty'         => 'float',
        'bumbu_qty_kg'           => 'float',
        'plastik_qty'            => 'float',
        'plastik_rusak_pcs'      => 'float',
        'karton_qty'             => 'float',
        'karton_rusak_lembar'    => 'float',
        'lakban_atas_gr'         => 'float',
        'lakban_bawah_gr'        => 'float',
        'lakban_qty'             => 'float',
        'expired_qty'            => 'float',
        'kemasan_lain_qty'       => 'float',
        'kemasan_tambahan_items' => 'array',
        'deleted_st'             => 'boolean',
        'active_st'              => 'boolean',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(MstKaryawan::class, 'karyawan_id', 'karyawan_id');
    }

    public function expiredBarang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'expired_barang_id', 'barang_id');
    }

    public function produksi(): BelongsTo
    {
        return $this->belongsTo(DatProduksiHdr::class, 'produksi_id', 'produksi_id');
    }

    public function wipBarang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'wip_barang_id', 'barang_id');
    }

    public function outputDtl(): BelongsTo
    {
        return $this->belongsTo(DatProduksiDtl::class, 'output_id', 'output_id');
    }

    public function barangJadi(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'barang_jadi_id', 'barang_id');
    }

    public function bumbuBarang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'bumbu_barang_id', 'barang_id');
    }

    public function plastikBarang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'plastik_barang_id', 'barang_id');
    }

    public function kartonBarang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'karton_barang_id', 'barang_id');
    }

    public function lakbanBarang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'lakban_barang_id', 'barang_id');
    }

    public function kemasanLainBarang(): BelongsTo
    {
        return $this->belongsTo(MstBarang::class, 'kemasan_lain_barang_id', 'barang_id');
    }

    public function packagingHdr(): BelongsTo
    {
        return $this->belongsTo(DatPackagingHdr::class, 'pack_id', 'pack_id');
    }

    protected $appends = [
        'status_badge',
        'formatted_kemasan_tambahan',
    ];

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status_cd) {
            'PROCESSED' => [
                'label'  => 'Sudah Jadi HPP FG',
                'color'  => '#15803d',
                'bg'     => '#f0fdf4',
                'border' => '#bbf7d0',
                'icon'   => '✅',
            ],
            'SUBMITTED' => [
                'label'  => 'Menunggu Office',
                'color'  => '#0369a1',
                'bg'     => '#f0f9ff',
                'border' => '#bae6fd',
                'icon'   => '📩',
            ],
            default => [
                'label'  => 'Draft',
                'color'  => '#475569',
                'bg'     => '#f8fafc',
                'border' => '#cbd5e1',
                'icon'   => '📝',
            ],
        };
    }

    public function getFormattedKemasanTambahanAttribute(): array
    {
        $items = $this->kemasan_tambahan_items;
        if (is_string($items)) {
            $items = json_decode($items, true);
            if (is_string($items)) {
                $items = json_decode($items, true);
            }
        }

        if (!is_array($items)) {
            $items = [];
        }

        if (empty($items) && $this->kemasan_lain_barang_id && $this->kemasan_lain_qty > 0) {
            $b = $this->kemasanLainBarang;
            $items[] = [
                'barang_id'     => $this->kemasan_lain_barang_id,
                'barang_cd'     => $b?->barang_cd ?? '',
                'barang_nm'     => $b?->barang_nm ?? 'Kemasan Tambahan',
                'satuan_nm'     => $b?->satuanDasar?->satuan_nm ?? 'PCS',
                'harga_standar' => (float) ($b?->harga_beli_standar ?? 0),
                'qty'           => (float) $this->kemasan_lain_qty,
                'catatan'       => '',
            ];
        } else {
            foreach ($items as &$it) {
                if (!empty($it['barang_id']) && (empty($it['barang_nm']) || empty($it['harga_standar']) || empty($it['satuan_nm']))) {
                    $b = MstBarang::with('satuanDasar')->find($it['barang_id']);
                    if ($b) {
                        $it['barang_cd'] = $b->barang_cd;
                        $it['barang_nm'] = $b->barang_nm;
                        $it['satuan_nm'] = $b->satuanDasar?->satuan_nm ?? 'PCS';
                        $it['harga_standar'] = (float) ($b->harga_beli_standar ?? 0);
                    }
                }
            }
        }

        return $items;
    }
}
