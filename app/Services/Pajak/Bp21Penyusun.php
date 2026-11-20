<?php

namespace App\Services\Pajak;

use App\Models\MasterData\MstKaryawan;

/**
 * Mengubah daftar karyawan yang ikut Multi Payroll menjadi baris BP21 (siap dimasukkan ke Bp21Xml).
 * Penghasilan (Gross) = gaji bersih yang ditransfer; tarif = TER menurut status PTKP.
 */
class Bp21Penyusun
{
    /**
     * @param string $periode  'YYYY-MM'
     * @param iterable<array{nik:?string,nama:string,jumlah:int|float}> $layak  karyawan layak transfer
     * @param array<string,array{npwp_nik:?string,status_ptkp:?string}> $dataPajak  dikunci dengan NIK karyawan
     * @param array{nomor_dokumen:string,tanggal_dokumen:string,tanggal_pemotongan:string,kode_objek?:string,fasilitas?:string,dokumen?:string,deemed?:int|float} $opsi
     * @return array{baris:array<int,array<string,mixed>>,masalah:array<int,string>}
     */
    public function susun(string $periode, iterable $layak, array $dataPajak, array $opsi): array
    {
        [$tahun, $bulan] = array_map('intval', explode('-', $periode));
        $deemed = (float) ($opsi['deemed'] ?? 100);

        $baris   = [];
        $masalah = [];

        foreach ($layak as $r) {
            $nik   = (string) ($r['nik'] ?? '');
            $nama  = (string) ($r['nama'] ?? $nik);
            $pajak = $dataPajak[$nik] ?? [];

            $npwp = preg_replace('/\D/', '', (string) ($pajak['npwp_nik'] ?? ''));
            $ptkp = strtoupper(trim((string) ($pajak['status_ptkp'] ?? '')));

            $kurang = [];
            if (strlen($npwp) !== 16) {
                $kurang[] = $npwp === '' ? 'NPWP/NIK belum diisi' : 'NPWP/NIK bukan 16 digit';
            }
            if (! in_array($ptkp, MstKaryawan::PTKP, true)) {
                $kurang[] = $ptkp === '' ? 'Status PTKP belum diisi' : "Status PTKP \"{$ptkp}\" tidak dikenal";
            }
            if ($kurang) {
                $masalah[] = "{$nama} ({$nik}): " . implode(', ', $kurang);
                continue;
            }

            $gross = (float) $r['jumlah'];

            $baris[] = [
                'masa'       => $bulan,
                'tahun'      => $tahun,
                'tin'        => $npwp,
                'ptkp'       => $ptkp,
                'fasilitas'  => $opsi['fasilitas'] ?? 'N/A',
                'kode_objek' => $opsi['kode_objek'] ?? '21-100-35',
                'gross'      => $gross,
                'deemed'     => $deemed,
                'rate'       => TerPph21::tarif($gross * $deemed / 100, $ptkp),
                'dokumen'    => $opsi['dokumen'] ?? 'Other',
                'no_dok'     => $opsi['nomor_dokumen'],
                'tgl_dok'    => $opsi['tanggal_dokumen'],
                'tgl_potong' => $opsi['tanggal_pemotongan'],
            ];
        }

        return ['baris' => $baris, 'masalah' => $masalah];
    }
}
