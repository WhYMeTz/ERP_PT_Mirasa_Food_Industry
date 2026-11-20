<?php

namespace Tests\Unit;

use App\Imports\Karyawan\KaryawanImport;
use PHPUnit\Framework\TestCase;

class KaryawanImportTest extends TestCase
{
    public function test_hanya_kolom_yang_ada_dan_terisi_yang_dibaca(): void
    {
        // File hanya berisi ID Karyawan + No. HP; kolom lain kosong / tidak ada
        $hasil = (new KaryawanImport)->baca(['id_karyawan' => 'STP3N25', 'no_hp' => '085277070354', 'email' => null, 'bank' => '']);

        $this->assertSame(['telepon_no' => '085277070354'], $hasil['data']);
        $this->assertSame([], $hasil['masalah']);
    }

    public function test_npwp_dan_ptkp_dinormalkan(): void
    {
        $hasil = (new KaryawanImport)->baca(['npwp_nik' => '3308 0941 0469 0007', 'status_ptkp' => 'tk 2']);
        $this->assertSame(['npwp_nik' => '3308094104690007', 'status_ptkp' => 'TK/2'], $hasil['data']);

        $angka = (new KaryawanImport)->baca(['npwp' => 3308094104690007.0, 'ptkp' => 'K-1']);
        $this->assertSame(['npwp_nik' => '3308094104690007', 'status_ptkp' => 'K/1'], $angka['data']);
    }

    public function test_npwp_salah_dan_ptkp_tidak_dikenal_ditolak(): void
    {
        $hasil = (new KaryawanImport)->baca(['npwp_nik' => '12345', 'status_ptkp' => 'ABC']);

        $this->assertSame([], $hasil['data']);
        $this->assertCount(2, $hasil['masalah']);
    }

    public function test_kolom_pajak_diabaikan_bila_tidak_berhak(): void
    {
        $hasil = (new KaryawanImport)->baca(['npwp_nik' => '3308094104690007', 'status_ptkp' => 'TK/0', 'no_hp' => '0812'], false);

        $this->assertSame(['telepon_no' => '0812'], $hasil['data']);
    }

    public function test_status_dan_shift_dinormalkan_hanya_bila_diisi(): void
    {
        $hasil = (new KaryawanImport)->baca(['status_karyawan' => 'KONTRAK', 'shift' => 'a']);
        $this->assertSame(['shift' => 'SHIFT A', 'status_kerja' => 'Kontrak'], $hasil['data']);

        // Tanpa kolom status: tidak boleh muncul 'Tetap' otomatis
        $kosong = (new KaryawanImport)->baca(['no_hp' => '0812']);
        $this->assertSame(false, array_key_exists('status_kerja', $kosong['data']));
        $this->assertSame(false, array_key_exists('bank_name', $kosong['data']));
    }

    public function test_divisi_tidak_dikenal_ditolak(): void
    {
        $hasil = (new KaryawanImport)->baca(['divisi_bagian' => 'ABCD']);
        $this->assertCount(1, $hasil['masalah']);

        $ok = (new KaryawanImport)->baca(['divisi' => 'pot rendam']);
        $this->assertSame(['jabatan_nm' => 'POT.RENDAM'], $ok['data']);
    }
}
