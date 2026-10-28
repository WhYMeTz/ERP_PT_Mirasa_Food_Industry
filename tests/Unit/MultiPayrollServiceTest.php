<?php

namespace Tests\Unit;

use App\Services\Manajemen\MultiPayrollService as S;
use PHPUnit\Framework\TestCase;

class MultiPayrollServiceTest extends TestCase
{
    private array $bagian = ['SORTIR AC' => ['kode' => 'AC'], 'POT.RENDEM' => ['kode' => 'PT'], 'UMUM' => ['kode' => 'UM', 'alias' => ['DRIVER', 'Security', 'QUALITYCONTROL']]];

    private function kar(array $o = []): array
    {
        return array_merge(['nama' => 'SITI', 'jabatan' => 'Umum', 'bank' => 'BCA', 'rekening' => '1040631042'], $o);
    }

    public function test_kunci_bagian_tidak_membedakan_huruf_dan_spasi(): void
    {
        $this->assertSame('UMUM', S::kunciBagian('  umum ', $this->bagian));
        $this->assertSame('SORTIR AC', S::kunciBagian('Sortir   ac', $this->bagian));
        $this->assertSame('UMUM', S::kunciBagian('driver', $this->bagian));
        $this->assertSame('UMUM', S::kunciBagian('SECURITY', $this->bagian));
        $this->assertNull(S::kunciBagian('Kantin', $this->bagian));
        $this->assertNull(S::kunciBagian(null, $this->bagian));
    }

    public function test_karyawan_layak(): void
    {
        $r = S::nilai('SITI', null, $this->kar(), 2318550, $this->bagian);
        $this->assertNull($r['alasan']);
        $this->assertSame('UMUM', $r['bagian']);
        $this->assertSame('1040631042', $r['rekening']);
    }

    public function test_alasan_dilewati(): void
    {
        $this->assertSame('Tidak ada nomor rekening', S::nilai('A', null, $this->kar(['rekening' => '-']), 100, $this->bagian)['alasan']);
        $this->assertSame('Gaji bersih nol atau minus', S::nilai('A', null, $this->kar(), -6906, $this->bagian)['alasan']);
        $this->assertSame('Bagian tidak dikenali (Kantin)', S::nilai('A', null, $this->kar(['jabatan' => 'Kantin']), 100, $this->bagian)['alasan']);
        $this->assertSame('Bank bukan BCA (BRI)', S::nilai('A', null, $this->kar(['bank' => 'BRI']), 100, $this->bagian)['alasan']);
        $this->assertSame('Data karyawan tidak ditemukan', S::nilai('A', 'UMUM', null, 100, $this->bagian)['alasan']);
    }
}
