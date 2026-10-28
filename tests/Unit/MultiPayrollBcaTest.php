<?php

namespace Tests\Unit;

use App\Services\Manajemen\MultiPayrollBca;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MultiPayrollBcaTest extends TestCase
{
    private function param(array $o = []): array
    {
        return array_merge([
            'header_id' => 69, 'tanggal_transfer' => '2026-07-01',
            'corporate_id' => 'IBSMIRASAF', 'company_code' => '01040276', 'rekening_sumber' => '1049173332',
            'remarks1' => 'AFKIR', 'remarks2' => 'JUNI 2026', 'kode' => 'AC',
            'tahun' => 2026, 'tanggal' => 30, 'bulan' => 6, 'jam' => 13, 'maks_nama' => 70,
        ], $o);
    }

    public function test_format_sama_dengan_file_juni_asli(): void
    {
        $txt = MultiPayrollBca::buat($this->param(), [
            ['rekening' => '1040631042', 'nama' => 'NOK SITI MARHUMAH', 'jumlah' => 2318550],
            ['rekening' => '1040631204', 'nama' => 'GENDUK KHOLISOH', 'jumlah' => 2565944],
        ]);

        $this->assertSame(
            "0|PY|IBSMIRASAF|01040276|00000069|20260701||1049173332|00002||AFKIR|JUNI 2026\r\n"
            . "1|00002026300613AC01|BCA||1040631042|NOK SITI MARHUMAH|2318550.00||JUNI 2026||||\r\n"
            . "1|00002026300613AC02|BCA||1040631204|GENDUK KHOLISOH|2565944.00||JUNI 2026||||\r\n",
            $txt
        );
    }

    public function test_id_transaksi_selalu_18_karakter(): void
    {
        $this->assertSame('00002026300613AC01', MultiPayrollBca::idTransaksi(2026, 30, 6, 13, 'AC', 1));
        $this->assertSame('0002026310610MKR01', MultiPayrollBca::idTransaksi(2026, 31, 6, 10, 'MKR', 1));
    }

    public function test_nama_dibersihkan_dan_dipotong(): void
    {
        $this->assertSame('Marifah DArc Putri', MultiPayrollBca::bersihNama("  Ma'rifah  D'Arc | Putri   Sari ", 18));
        $this->assertSame('HILDA GIOVANNY DWI', MultiPayrollBca::bersihNama('HILDA GIOVANNY DWI CAHYANI', 18));
    }

    public function test_jumlah_nol_atau_minus_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        MultiPayrollBca::buat($this->param(), [['rekening' => '123', 'nama' => 'A', 'jumlah' => 0]]);
    }

    public function test_tanpa_baris_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);
        MultiPayrollBca::buat($this->param(), []);
    }
}
