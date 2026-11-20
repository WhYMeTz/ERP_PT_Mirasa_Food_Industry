<?php

namespace Tests\Unit;

use App\Services\Pajak\Bp21Penyusun;
use PHPUnit\Framework\TestCase;

class Bp21PenyusunTest extends TestCase
{
    private array $opsi = ['nomor_dokumen' => '82026-AFKIR', 'tanggal_dokumen' => '2026-08-31', 'tanggal_pemotongan' => '2026-09-01'];

    public function test_susun_baris_dengan_tarif_ter(): void
    {
        $hasil = (new Bp21Penyusun)->susun(
            '2026-08',
            [['nik' => 'AC1', 'nama' => 'A', 'jumlah' => 10000000], ['nik' => 'AC2', 'nama' => 'B', 'jumlah' => 2584275]],
            ['AC1' => ['npwp_nik' => '3308094104690007', 'status_ptkp' => 'TK/0'], 'AC2' => ['npwp_nik' => '3308 0941 0469 0008', 'status_ptkp' => 'TK/2']],
            $this->opsi
        );

        $this->assertSame([], $hasil['masalah']);
        $this->assertSame(2.0, $hasil['baris'][0]['rate']);   // TER A, Rp10 juta
        $this->assertSame(0.0, $hasil['baris'][1]['rate']);   // di bawah batas
        $this->assertSame('3308094104690008', $hasil['baris'][1]['tin']);
        $this->assertSame(8, $hasil['baris'][0]['masa']);
        $this->assertSame(2026, $hasil['baris'][0]['tahun']);
    }

    public function test_data_pajak_kurang_dilaporkan_dan_baris_dilewati(): void
    {
        $hasil = (new Bp21Penyusun)->susun(
            '2026-08',
            [['nik' => 'AC1', 'nama' => 'A', 'jumlah' => 1], ['nik' => 'AC2', 'nama' => 'B', 'jumlah' => 1], ['nik' => 'AC3', 'nama' => 'C', 'jumlah' => 1]],
            ['AC1' => ['npwp_nik' => '123', 'status_ptkp' => 'TK/0'], 'AC2' => ['npwp_nik' => '3308094104690008', 'status_ptkp' => null]],
            $this->opsi
        );

        $this->assertSame([], $hasil['baris']);
        $this->assertCount(3, $hasil['masalah']);
        $this->assertStringContainsString('bukan 16 digit', $hasil['masalah'][0]);
        $this->assertStringContainsString('Status PTKP belum diisi', $hasil['masalah'][1]);
        $this->assertStringContainsString('NPWP/NIK belum diisi', $hasil['masalah'][2]);
    }
}
