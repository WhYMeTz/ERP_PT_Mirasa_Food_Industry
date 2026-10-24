<?php

namespace App\Services\Manajemen;

use App\Models\Manajemen\MstKodeShift;
use App\Models\Manajemen\TrxAbsensiKaryawan;
use App\Models\Manajemen\TrxPayroll;
use App\Models\MasterData\MstKaryawan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Orkestrasi payroll per periode (bulan kalender). Rumusnya ada di PayrollHitung.
 * Draft  : dihitung langsung dari absensi setiap halaman dibuka.
 * Final  : hasil disimpan (snapshot), tidak berubah walau absensi, tarif, atau Kode Shift berubah.
 * Dibayar: final yang sudah ditandai dibayar.
 */
class PayrollService
{
    public const INPUT = ['gaji_harian', 'tunjangan_jabatan', 'insentif', 'potongan_bpjs_kes', 'potongan_bpjs_tk', 'potongan_lainnya'];

    /** Disalin dari bulan sebelumnya sebagai isian awal. Kasbon sengaja tidak disalin. */
    public const SALIN = ['gaji_harian', 'tunjangan_jabatan', 'insentif', 'potongan_bpjs_kes', 'potongan_bpjs_tk'];

    public static function rentang(string $periode): array
    {
        $awal = Carbon::createFromFormat('!Y-m', $periode)->startOfMonth();
        return [$awal->toDateString(), $awal->copy()->endOfMonth()->toDateString()];
    }

    public function statusPeriode(string $periode): string
    {
        $status = TrxPayroll::where('periode', $periode)->distinct()->pluck('status');
        if ($status->isEmpty() || $status->contains('draft')) {
            return 'draft';
        }
        return $status->every(fn ($s) => $s === 'dibayar') ? 'dibayar' : 'final';
    }

    public function daftar(string $periode, ?string $cari = null): array
    {
        [$awal, $akhir] = self::rentang($periode);
        $status   = $this->statusPeriode($periode);
        $terkunci = $status !== 'draft';

        $rows = $terkunci ? $this->barisTerkunci($periode) : $this->barisDraft($periode, $awal, $akhir);

        $ringkasan = [
            'karyawan'         => $rows->count(),
            'total_pendapatan' => (int) $rows->sum('hasil.total_pendapatan'),
            'total_potongan'   => (int) $rows->sum('hasil.total_potongan'),
            'total_bersih'     => (int) $rows->sum('hasil.gaji_bersih'),
            'perlu_dicek'      => (int) $rows->sum('perlu_dicek'),
            'tanpa_jadwal'     => (int) $rows->sum('tanpa_jadwal'),
            'tanpa_gaji'       => $terkunci ? 0 : $rows->where('siap', false)->count(),
        ];
        $ringkasan['bisa_final'] = !$terkunci && $rows->isNotEmpty()
            && $ringkasan['perlu_dicek'] === 0 && $ringkasan['tanpa_jadwal'] === 0 && $ringkasan['tanpa_gaji'] === 0;

        if (filled($cari)) {
            $c = mb_strtolower(trim($cari));
            $rows = $rows->filter(fn ($r) => str_contains(mb_strtolower($r['nama'] . ' ' . $r['id_absen'] . ' ' . ($r['nik'] ?? '')), $c))->values();
        }

        return compact('status', 'terkunci', 'rows', 'ringkasan', 'awal', 'akhir');
    }

    public function baris(string $periode, string $idAbsen): ?array
    {
        return $this->daftar($periode)['rows']->firstWhere('id_absen', $idAbsen);
    }

    public function simpanKomponen(string $periode, string $idAbsen, array $komponen, ?string $catatan = null): TrxPayroll
    {
        if ($this->statusPeriode($periode) !== 'draft') {
            throw new \DomainException('Periode ini sudah difinalkan. Buka kunci dulu untuk mengubah komponen.');
        }

        [$awal, $akhir] = self::rentang($periode);
        $meta = $this->meta($idAbsen, $awal, $akhir);

        $nilai = [];
        foreach (self::INPUT as $f) {
            $nilai[$f] = max(0, (float) ($komponen[$f] ?? 0));
        }

        return TrxPayroll::updateOrCreate(
            ['periode' => $periode, 'id_absen' => $idAbsen],
            $meta + $nilai + ['catatan' => $catatan, 'status' => 'draft']
        );
    }

    /** Isi komponen dari file Excel. Semua baris harus sah, kalau tidak, tidak ada yang disimpan. */
    public function importKomponen(string $periode, Collection $baris): array
    {
        if ($this->statusPeriode($periode) !== 'draft') {
            throw new \DomainException('Periode ini sudah difinalkan. Buka kunci dulu untuk import komponen.');
        }

        $rows  = $this->daftar($periode)['rows']->keyBy(fn ($r) => PayrollImportParser::kunci($r['nik'] ?? $r['id_absen']));
        $valid = $rows->map(fn () => true)->all();

        $baca = PayrollImportParser::baca($baris->map(fn ($b) => $b instanceof Collection ? $b->toArray() : (array) $b)->all(), $valid);
        if ($baca['errors']) {
            return ['berhasil' => 0, 'kosong' => $baca['kosong'], 'errors' => $baca['errors']];
        }

        DB::transaction(function () use ($baca, $rows, $periode) {
            foreach ($baca['perubahan'] as $kunci => $p) {
                $row = $rows[$kunci];
                $this->simpanKomponen($periode, $row['id_absen'], array_merge($row['input'], $p['komponen']), $p['catatan'] ?? $row['catatan']);
            }
        });

        return ['berhasil' => count($baca['perubahan']), 'kosong' => $baca['kosong'], 'errors' => []];
    }

    /** Hitung semua karyawan lalu simpan sebagai snapshot. Ditolak bila masih ada yang belum beres. */
    public function finalkan(string $periode, ?string $oleh = null): int
    {
        if ($this->statusPeriode($periode) !== 'draft') {
            throw new \DomainException('Periode ini sudah difinalkan.');
        }

        $data = $this->daftar($periode);
        $rows = $data['rows'];
        $r    = $data['ringkasan'];

        if ($rows->isEmpty()) {
            throw new \DomainException('Belum ada data absensi pada periode ini.');
        }
        $masalah = [];
        if ($r['perlu_dicek'] > 0) {
            $masalah[] = "{$r['perlu_dicek']} baris absensi berstatus \"Perlu dicek\"";
        }
        if ($r['tanpa_jadwal'] > 0) {
            $masalah[] = "{$r['tanpa_jadwal']} baris absensi berstatus \"Tanpa jadwal\"";
        }
        if ($r['tanpa_gaji'] > 0) {
            $nama = $rows->where('siap', false)->take(5)->pluck('nama')->implode(', ');
            $masalah[] = "{$r['tanpa_gaji']} karyawan belum diisi gaji harian ({$nama}" . ($r['tanpa_gaji'] > 5 ? ', dst' : '') . ')';
        }
        if ($masalah) {
            throw new \DomainException('Belum bisa difinalkan: ' . implode('; ', $masalah) . '.');
        }

        DB::transaction(function () use ($rows, $periode, $oleh) {
            foreach ($rows as $row) {
                TrxPayroll::updateOrCreate(
                    ['periode' => $periode, 'id_absen' => $row['id_absen']],
                    [
                        'nik'              => $row['nik'],
                        'nama'             => $row['nama'],
                        'kelompok_kerja'   => $row['kelompok'],
                        'jabatan'          => $row['jabatan'],
                        'catatan'          => $row['catatan'],
                        'status'           => 'final',
                        'hasil'            => $row['hasil'] + ['tgl_masuk' => $row['tgl_masuk']],
                        'total_pendapatan' => $row['hasil']['total_pendapatan'],
                        'total_potongan'   => $row['hasil']['total_potongan'],
                        'gaji_bersih'      => $row['hasil']['gaji_bersih'],
                        'final_at'         => now(),
                        'final_oleh'       => $oleh,
                    ] + $row['input']
                );
            }
        });

        return $rows->count();
    }

    public function bukaKunci(string $periode): void
    {
        if ($this->statusPeriode($periode) !== 'final') {
            throw new \DomainException('Hanya periode berstatus Final yang bisa dibuka kuncinya.');
        }
        TrxPayroll::where('periode', $periode)->update([
            'status' => 'draft', 'hasil' => null, 'total_pendapatan' => null, 'total_potongan' => null,
            'gaji_bersih' => null, 'final_at' => null, 'final_oleh' => null,
        ]);
    }

    public function tandaiDibayar(string $periode): void
    {
        if ($this->statusPeriode($periode) !== 'final') {
            throw new \DomainException('Hanya periode berstatus Final yang bisa ditandai dibayar.');
        }
        TrxPayroll::where('periode', $periode)->update(['status' => 'dibayar', 'dibayar_at' => now()]);
    }

    // ---------------------------------------------------------------------------------------------

    private function barisTerkunci(string $periode): Collection
    {
        return TrxPayroll::where('periode', $periode)->orderBy('nama')->get()->map(function (TrxPayroll $p) {
            $hasil = $p->hasil ?? [];
            return [
                'id_absen' => $p->id_absen, 'nik' => $p->nik, 'nama' => $p->nama,
                'kelompok' => $p->kelompok_kerja, 'jabatan' => $p->jabatan,
                'tgl_masuk' => $hasil['tgl_masuk'] ?? null,
                'input'    => collect(self::INPUT)->mapWithKeys(fn ($f) => [$f => (float) $p->$f])->all(),
                'catatan'  => $p->catatan,
                'hasil'    => $hasil,
                'tersimpan' => true, 'siap' => true, 'status' => $p->status,
                'dibayar_at' => $p->dibayar_at, 'final_at' => $p->final_at,
                'perlu_dicek' => 0, 'tanpa_jadwal' => 0, 'tidak_hadir' => 0,
            ];
        })->values();
    }

    private function barisDraft(string $periode, string $awal, string $akhir): Collection
    {
        $absensi = TrxAbsensiKaryawan::whereBetween('tanggal', [$awal, $akhir])->orderBy('tanggal')->get()->groupBy('id_absen');
        $saved   = TrxPayroll::where('periode', $periode)->get()->keyBy('id_absen');
        $ids     = $absensi->keys()->merge($saved->keys())->unique()->values();

        $karyawan = MstKaryawan::whereNotNull('id_absensi')->get()
            ->keyBy(fn ($k) => str_pad(trim($k->id_absensi), 9, '0', STR_PAD_LEFT));
        $shifts = MstKodeShift::all()->keyBy('kode');

        // isian bulan lalu (terbaru per karyawan), untuk karyawan yang belum punya baris di periode ini
        $lalu = TrxPayroll::where('periode', '<', $periode)->whereIn('id_absen', $ids)
            ->orderByDesc('periode')->get()->unique('id_absen')->keyBy('id_absen');

        return $ids->map(function ($id) use ($absensi, $saved, $lalu, $karyawan, $shifts, $akhir) {
            $k   = $karyawan[$id] ?? null;
            $abs = $absensi->get($id, collect());
            $p   = $saved[$id] ?? null;
            $src = $lalu[$id] ?? null;

            $input = [];
            foreach (self::INPUT as $f) {
                $input[$f] = $p ? (float) $p->$f
                    : (($src && in_array($f, self::SALIN, true)) ? (float) $src->$f : 0.0);
            }

            $tglMasuk = $k?->tgl_masuk ? substr((string) $k->tgl_masuk, 0, 10) : null;
            $hasil = PayrollHitung::hitung($input, $this->petaAbsensi($abs, $shifts), PayrollHitung::masaKerjaTahun($tglMasuk, $akhir));

            return [
                'id_absen' => $id,
                'nik'      => $k?->nik,
                'nama'     => $k?->karyawan_nm ?? $p?->nama ?? $abs->first()?->nama ?? $id,
                'kelompok' => $abs->last()?->kelompok_kerja ?? $k?->kelompok,
                'jabatan'  => $k?->jabatan_nm,
                'tgl_masuk' => $tglMasuk,
                'input'    => $input,
                'catatan'  => $p?->catatan,
                'hasil'    => $hasil,
                'tersimpan' => (bool) $p,
                'siap'     => $input['gaji_harian'] > 0,
                'status'   => 'draft',
                'dibayar_at' => null, 'final_at' => null,
                'perlu_dicek'  => $abs->where('status', 'Perlu dicek')->count(),
                'tanpa_jadwal' => $abs->where('status', 'Tanpa jadwal')->count(),
                'tidak_hadir'  => $abs->where('status', 'Tidak hadir')->count(),
            ];
        })->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    /** Satu baris absensi menjadi bentuk sederhana yang dipahami PayrollHitung. */
    public function petaBaris($r, Collection $shifts): array
    {
        $sj    = $shifts[$r->kode_shift] ?? null;
        $jenis = ($sj && in_array($sj->jenis, ['minggu', 'driver'], true)) ? $sj->jenis
            : ($r->departemen_cd === 'DRIVER' ? 'driver' : 'jam');   // sama dengan AbsensiService
        return [
            'jenis'        => $jenis,
            'status'       => $r->status,
            'standar_jam'  => (float) $r->standar_jam,
            'potongan_jam' => (float) $r->potongan_jam,
            'lembur_jam'   => (float) $r->lembur_jam,
            'kehadiran'    => (float) $r->kehadiran,
        ];
    }

    /** Ubah baris absensi menjadi bentuk sederhana yang dipahami PayrollHitung. */
    private function petaAbsensi(Collection $abs, Collection $shifts): array
    {
        return $abs->map(function ($r) use ($shifts) {
            $sj    = $shifts[$r->kode_shift] ?? null;
            $jenis = ($sj && in_array($sj->jenis, ['minggu', 'driver'], true)) ? $sj->jenis
                   : ($r->departemen_cd === 'DRIVER' ? 'driver' : 'jam');   // sama dengan AbsensiService
            return [
                'jenis'        => $jenis,
                'status'       => $r->status,
                'standar_jam'  => (float) $r->standar_jam,
                'potongan_jam' => (float) $r->potongan_jam,
                'lembur_jam'   => (float) $r->lembur_jam,
                'kehadiran'    => (float) $r->kehadiran,
            ];
        })->all();
    }
    

    /** Data identitas karyawan untuk disimpan bersama komponen. */
    private function meta(string $idAbsen, string $awal, string $akhir): array
    {
        $k = MstKaryawan::whereNotNull('id_absensi')->get()
            ->first(fn ($x) => str_pad(trim($x->id_absensi), 9, '0', STR_PAD_LEFT) === $idAbsen);
        $a = TrxAbsensiKaryawan::where('id_absen', $idAbsen)->whereBetween('tanggal', [$awal, $akhir])->orderByDesc('tanggal')->first();

        return [
            'nik'            => $k?->nik,
            'nama'           => $k?->karyawan_nm ?? $a?->nama ?? $idAbsen,
            'kelompok_kerja' => $a?->kelompok_kerja ?? $k?->kelompok,
            'jabatan'        => $k?->jabatan_nm,
        ];
    }
}