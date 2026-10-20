<?php

namespace App\Models\Manajemen;
use Illuminate\Database\Eloquent\Model;

class TrxAbsensiHarian extends Model
{
    protected $table = 'trx_absensi_harian';
    protected $guarded = [];
    protected $casts = [
        'tanggal' => 'date',
        'masuk_asli' => 'datetime', 'pulang_asli' => 'datetime',
        'jam_masuk' => 'datetime',  'jam_pulang' => 'datetime',
    ];

    /** Total jam kerja (desimal) dari jam masuk & pulang yang sudah dibulatkan. */
    public function getTotalJamAttribute(): ?float
    {
        if (!$this->jam_masuk || !$this->jam_pulang) {
            return null;
        }

        $menit = $this->jam_masuk->diffInMinutes($this->jam_pulang, false);

        return $menit > 0 ? round($menit / 60, 2) : 0.0;
    }

    /** Format tampilan: "8 jam" atau "8 jam 30 menit". */
    public function getTotalJamLabelAttribute(): ?string
    {
        if ($this->total_jam === null) {
            return null;
        }

        $menit = (int) round($this->total_jam * 60);
        $jam   = intdiv($menit, 60);
        $sisa  = $menit % 60;

        return $sisa > 0 ? "{$jam} jam {$sisa} menit" : "{$jam} jam";
    }
}
