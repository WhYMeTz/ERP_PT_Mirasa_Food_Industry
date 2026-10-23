<?php

namespace App\Services\Manajemen;

/**
 * Rumus payroll di SATU tempat, tanpa database.
 * - Gaji pokok hari itu = gaji harian x kehadiran. Kehadiran = (standar - potongan) / standar, maks 1, tidak dibulatkan.
 * - Tarif per jam = gaji harian / standar jam shift (7 bila shift tidak punya standar, mis. Minggu).
 * - Lembur 1 = jam lembur pertama (x1,5). Lembur 2 = sisanya (x2).
 * - Hari Minggu: seluruh jam jadi Lembur 2, gaji pokok hari itu tidak dihitung.
 * - Tunjangan masa kerja = tarif per hari menurut masa kerja x total kehadiran.
 * - Potongan jam kerja bukan baris potongan, sudah tercermin di kehadiran.
 * - Pembulatan hanya di akhir: tiap baris rupiah dibulatkan, total = jumlah baris yang sudah bulat.
 */
class PayrollHitung
{
    public const STANDAR_DEFAULT = 7.0;
    public const KALI_LEMBUR_1   = 1.5;
    public const KALI_LEMBUR_2   = 2.0;

    /** Status absensi yang boleh masuk perhitungan. */
    public const STATUS_VALID = ['OK', 'Tidak hadir'];

    /** Batas bawah masa kerja (tahun) => tunjangan per hari kerja (Rp). */
    public const TUNJANGAN_MASA_KERJA = [20 => 2550, 15 => 2200, 10 => 1700, 5 => 1000];

    public static function tunjanganMasaKerjaPerHari(int $tahun): int
    {
        foreach (self::TUNJANGAN_MASA_KERJA as $batas => $nominal) {
            if ($tahun >= $batas) {
                return $nominal;
            }
        }
        return 0;
    }

    /** Masa kerja tahun penuh, dihitung sampai akhir periode payroll. */
    public static function masaKerjaTahun(?string $tglMasuk, string $akhirPeriode): int
    {
        if (!$tglMasuk) {
            return 0;
        }
        try {
            $masuk = new \DateTimeImmutable(substr($tglMasuk, 0, 10));
            $akhir = new \DateTimeImmutable(substr($akhirPeriode, 0, 10));
        } catch (\Exception $e) {
            return 0;
        }
        if ($masuk > $akhir) {
            return 0;
        }
        return (int) $masuk->diff($akhir)->y;
    }

    /**
     * Rumus SATU hari absensi, belum dibulatkan. Mengembalikan null bila status absensi belum valid.
     * hitung() memakai method ini juga, jadi angka harian dan bulanan selalu sejalan.
     */
    public static function hitungHari(float $gaji, array $r): ?array
    {
        if (!in_array($r['status'] ?? '', self::STATUS_VALID, true)) {
            return null;
        }

        $jenis  = $r['jenis'] ?? 'jam';
        $lembur = max(0.0, (float) ($r['lembur_jam'] ?? 0));
        $kurang = 0.0;

        if ($jenis === 'minggu') {
            $divisor = self::STANDAR_DEFAULT;
            $l1 = 0.0;
            $l2 = $lembur;
            $k  = 0.0;
        } elseif ($jenis === 'driver') {
            $divisor = self::STANDAR_DEFAULT;
            $l1 = $l2 = 0.0;
            $k  = min(1.0, max(0.0, (float) ($r['kehadiran'] ?? 0)));
        } else {
            $standar = (float) ($r['standar_jam'] ?? 0);
            $divisor = $standar > 0 ? $standar : self::STANDAR_DEFAULT;
            $potong  = max(0.0, (float) ($r['potongan_jam'] ?? 0));
            $k  = $standar > 0 ? min(1.0, max(0.0, ($standar - $potong) / $standar)) : 0.0;
            $l1 = min(1.0, $lembur);
            $l2 = max(0.0, $lembur - 1.0);
            $kurang = min($potong, $standar);
        }

        $tarif = $gaji / $divisor;

        return [
            'jenis'  => $jenis,
            'k'      => $k,
            'l1'     => $l1,
            'l2'     => $l2,
            'kurang' => $kurang,
            'pokok'  => $gaji * $k,
            'honor1' => $tarif * self::KALI_LEMBUR_1 * $l1,
            'honor2' => $tarif * self::KALI_LEMBUR_2 * $l2,
        ];
    }

    public static function hitung(array $input, array $absensi, int $masaKerjaTahun): array
    {
        $gaji = (float) ($input['gaji_harian'] ?? 0);

        $kehadiran = 0.0;
        $gajiPokok = 0.0;
        $honor1 = $honor2 = 0.0;
        $jamLembur1 = $jamLembur2 = 0.0;
        $jamKurang = 0.0;
        $hariMinggu = 0;
        $dilewati = 0;

        foreach ($absensi as $r) {
            if (!in_array($r['status'] ?? '', self::STATUS_VALID, true)) {
                $dilewati++;
                continue;
            }

            $jenis  = $r['jenis'] ?? 'jam';
            $lembur = max(0.0, (float) ($r['lembur_jam'] ?? 0));

            if ($jenis === 'minggu') {
                $hariMinggu++;
                $divisor = self::STANDAR_DEFAULT;
                $l1 = 0.0;
                $l2 = $lembur;
                $k  = 0.0;
            } elseif ($jenis === 'driver') {
                $divisor = self::STANDAR_DEFAULT;
                $l1 = $l2 = 0.0;
                $k  = min(1.0, max(0.0, (float) ($r['kehadiran'] ?? 0)));
            } else {
                $standar = (float) ($r['standar_jam'] ?? 0);
                $divisor = $standar > 0 ? $standar : self::STANDAR_DEFAULT;
                $potong  = max(0.0, (float) ($r['potongan_jam'] ?? 0));
                $k  = $standar > 0 ? min(1.0, max(0.0, ($standar - $potong) / $standar)) : 0.0;
                $l1 = min(1.0, $lembur);
                $l2 = max(0.0, $lembur - 1.0);
                $jamKurang += min($potong, $standar);
            }

            $tarif = $gaji / $divisor;
            $kehadiran  += $k;
            $gajiPokok  += $gaji * $k;
            $honor1     += $tarif * self::KALI_LEMBUR_1 * $l1;
            $honor2     += $tarif * self::KALI_LEMBUR_2 * $l2;
            $jamLembur1 += $l1;
            $jamLembur2 += $l2;
        }

        $tunjMasaPerHari = self::tunjanganMasaKerjaPerHari($masaKerjaTahun);

        $baris = [
            'gaji_pokok'           => (int) round($gajiPokok),
            'honor_lembur_1'       => (int) round($honor1),
            'honor_lembur_2'       => (int) round($honor2),
            'tunjangan_masa_kerja' => (int) round($tunjMasaPerHari * $kehadiran),
            'tunjangan_jabatan'    => (int) round((float) ($input['tunjangan_jabatan'] ?? 0)),
            'insentif'             => (int) round((float) ($input['insentif'] ?? 0)),
        ];
        $potongan = [
            'potongan_bpjs_kes' => (int) round((float) ($input['potongan_bpjs_kes'] ?? 0)),
            'potongan_bpjs_tk'  => (int) round((float) ($input['potongan_bpjs_tk'] ?? 0)),
            'potongan_lainnya'  => (int) round((float) ($input['potongan_lainnya'] ?? 0)),
        ];

        $totalPendapatan = array_sum($baris);
        $totalPotongan   = array_sum($potongan);

        return $baris + $potongan + [
            'total_pendapatan'        => $totalPendapatan,
            'total_potongan'          => $totalPotongan,
            'gaji_bersih'             => $totalPendapatan - $totalPotongan,
            'total_kehadiran'         => round($kehadiran, 4),
            'jam_lembur_1'            => round($jamLembur1, 2),
            'jam_lembur_2'            => round($jamLembur2, 2),
            'jam_kurang'              => round($jamKurang, 2),
            'hari_minggu'             => $hariMinggu,
            'baris_dilewati'          => $dilewati,
            'masa_kerja_tahun'        => $masaKerjaTahun,
            'tunjangan_masa_per_hari' => $tunjMasaPerHari,
            'tarif_jam'               => $gaji / self::STANDAR_DEFAULT,
        ];
    }
}