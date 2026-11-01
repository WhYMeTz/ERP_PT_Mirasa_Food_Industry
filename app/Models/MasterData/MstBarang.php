<?php

namespace App\Models\MasterData;

use App\Traits\AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MstBarang extends Model
{
    use HasFactory, AuditableTrait;

    protected $table = 'mst_barang';
    protected $primaryKey = 'barang_id';

    protected $fillable = [
        'barang_cd',
        'barang_nm',
        'jenis_barang_id',
        'satuan_dasar_id',
        'satuan_besar_id',
        'konversi_qty',
        'batas_minimum_qty',
        'harga_beli_standar',
        'harga_jual',
        'harga_jual_idm_alfa',
        'harga_jual_alfamidi',
        'harga_jual_ifm_smg',
        'harga_jual_ifm_ckl',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_st',
        'active_st',
    ];

    protected $casts = [
        'konversi_qty'        => 'decimal:4',
        'batas_minimum_qty'   => 'decimal:4',
        'harga_beli_standar'  => 'decimal:4',
        'harga_jual'          => 'decimal:4',
        'harga_jual_idm_alfa' => 'decimal:4',
        'harga_jual_alfamidi' => 'decimal:4',
        'harga_jual_ifm_smg'  => 'decimal:4',
        'harga_jual_ifm_ckl'  => 'decimal:4',
        'deleted_st'          => 'boolean',
        'active_st'           => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     * Auto-sync harga beli standar Karton Puteran (KIF00G-PUT) = 1/5 dari Karton Baru (KIF00G-BP1).
     */
    protected static function booted(): void
    {
        static::saved(function (MstBarang $barang) {
            if ($barang->barang_cd === 'KIF00G-BP1') {
                $hargaBaru = (float) $barang->harga_beli_standar;
                $hargaPuteran = round($hargaBaru / 5, 2);

                $puteran = static::where('barang_cd', 'KIF00G-PUT')->first();
                if ($puteran && abs((float) $puteran->harga_beli_standar - $hargaPuteran) > 0.001) {
                    $puteran->harga_beli_standar = $hargaPuteran;
                    $puteran->updated_by = $barang->updated_by ?? 'SYSTEM_SYNC';
                    $puteran->saveQuietly();
                }
            }
        });
    }

    /**
     * Resolusi harga jual barang berdasarkan mitra pelanggan (Customer Tier Pricing).
     */
    public function getHargaForCustomer($customer = null): float
    {
        if (!$customer) {
            return (float) ($this->harga_jual ?: $this->harga_beli_standar);
        }

        $cId = is_object($customer) ? ($customer->customer_id ?? null) : (int)$customer;
        $cKlas = strtoupper(is_object($customer) ? ($customer->klasifikasi ?? '') : '');
        $cNm = strtoupper(is_object($customer) ? ($customer->customer_nm ?? '') : '');
        $cCd = strtoupper(is_object($customer) ? ($customer->customer_cd ?? '') : '');

        // 1. Alfamidi (PT Midi Utama Indonesia Tbk)
        if ($cId == 12 || str_contains($cNm, 'MIDI') || str_contains($cCd, 'MIDI')) {
            if ($this->harga_jual_alfamidi && (float)$this->harga_jual_alfamidi > 0) {
                return (float) $this->harga_jual_alfamidi;
            }
        }

        // 2. Indomaret & Alfamart
        if (in_array($cKlas, ['IDM', 'MIDI_ALFA', 'IDM_ALFA']) || 
            str_contains($cNm, 'INDOMARET') || str_contains($cNm, 'ALFARIA') || str_contains($cNm, 'ALFAMART') ||
            str_contains($cCd, 'IDM') || str_contains($cCd, 'ALFA')) {
            if ($this->harga_jual_idm_alfa && (float)$this->harga_jual_idm_alfa > 0) {
                return (float) $this->harga_jual_idm_alfa;
            }
        }

        // 3. Indofood (IFM)
        if ($cKlas === 'IFM' || str_contains($cNm, 'INDOFOOD')) {
            if ($cId == 4 || str_contains($cNm, 'CIKOKOL') || str_contains($cNm, 'CIKUPA') || str_contains($cCd, 'CKL')) {
                if ($this->harga_jual_ifm_ckl && (float)$this->harga_jual_ifm_ckl > 0) {
                    return (float) $this->harga_jual_ifm_ckl;
                }
            }
            if ($this->harga_jual_ifm_smg && (float)$this->harga_jual_ifm_smg > 0) {
                return (float) $this->harga_jual_ifm_smg;
            }
        }

        // Fallback default
        return (float) ($this->harga_jual ?: $this->harga_beli_standar);
    }

    /**
     * Scope barang khusus Bahan Baku & Bahan Penolong (bukan barang jadi FG atau setengah jadi WIP)
     */
    public function scopeBahanBaku($query)
    {
        return $query->whereHas('jenisBarang', function ($q) {
            $q->whereIn('jenis_barang_cd', ['RAW', 'SUPP', 'PACK', 'BUMBU', 'BB', 'BP'])
              ->whereNotIn('jenis_barang_cd', ['WIP', 'FG']);
        });
    }

    /**
     * Scope barang input untuk bahan produksi (Bahan Baku, Bahan Penolong, Kemasan; mengecualikan Produk Jadi FG dan Hasil Setengah Jadi WIP).
     */
    public function scopeBahanProduksi($query)
    {
        return $query->whereDoesntHave('jenisBarang', function ($q) {
            $q->whereIn('jenis_barang_cd', ['FG', 'WIP']);
        });
    }

    /**
     * Scope barang hasil produksi pabrik yang dijual ke customer:
     * - Barang Jadi (FG): untuk pasar eceran/distributor (misal: Ping-Ping, Maksi, KS Jumbo)
     * - Barang Setengah Jadi (WIP): untuk pasokan industri B2B (misal: PT Indofood CBP Sukses Makmur Tbk)
     */
    public function scopeHasilProduksi($query)
    {
        return $query->whereHas('jenisBarang', function ($q) {
            $q->whereIn('jenis_barang_cd', ['FG', 'WIP']);
        });
    }

    /**
     * Alias scope untuk barang yang dapat dijual di PO Penjualan.
     */
    public function scopeProdukJadi($query)
    {
        return $query->whereHas('jenisBarang', function ($q) {
            $q->whereIn('jenis_barang_cd', ['FG', 'WIP']);
        });
    }

    /**
     * Relasi ke Jenis Barang
     */
    public function jenisBarang(): BelongsTo
    {
        return $this->belongsTo(MstJenisBarang::class, 'jenis_barang_id', 'jenis_barang_id');
    }

    /**
     * Relasi ke Satuan Dasar (misal: KG, PCS)
     */
    public function satuanDasar(): BelongsTo
    {
        return $this->belongsTo(MstSatuan::class, 'satuan_dasar_id', 'satuan_id');
    }

    /**
     * Alias relasi ke Satuan Dasar (memastikan kompatibilitas modul gudang/produksi)
     */
    public function satuan(): BelongsTo
    {
        return $this->satuanDasar();
    }

    /**
     * Relasi ke Satuan Besar (misal: SAK, DUS, BAL)
     */
    public function satuanBesar(): BelongsTo
    {
        return $this->belongsTo(MstSatuan::class, 'satuan_besar_id', 'satuan_id');
    }

    /**
     * Relasi ke Batch Stok Gudang
     */
    public function stokBatches(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Gudang\DatStokBatch::class, 'barang_id', 'barang_id');
    }

    /**
     * Cek apakah barang ini termasuk Finish Good (FG) berdasarkan jenis_barang.
     */
    public function isFinishGood(): bool
    {
        return $this->jenisBarang?->jenis_barang_cd === 'FG';
    }

    /**
     * Cek apakah barang ini termasuk Setengah Jadi (WIP) berdasarkan jenis_barang.
     */
    public function isWip(): bool
    {
        return $this->jenisBarang?->jenis_barang_cd === 'WIP';
    }

    /**
     * Cek apakah barang ini merupakan hasil produksi (FG atau WIP).
     */
    public function isHasilProduksi(): bool
    {
        return in_array($this->jenisBarang?->jenis_barang_cd, ['FG', 'WIP']);
    }
}



