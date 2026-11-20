<?php

namespace Tests\Unit;

use App\Services\Pajak\Bp21Xml;
use App\Services\Pajak\TerPph21;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class Bp21XmlTest extends TestCase
{
    private function baris(array $ubah = []): array
    {
        return array_merge([
            'masa' => 8, 'tahun' => 2026, 'tin' => '3308094104690007', 'ptkp' => 'TK/0',
            'kode_objek' => '21-100-35', 'gross' => 2584275, 'rate' => 0,
            'no_dok' => '82026-AFKIR', 'tgl_dok' => '2026-08-30', 'tgl_potong' => '2026-09-01',
        ], $ubah);
    }

    public function test_format_xml_sama_dengan_hasil_excel(): void
    {
        $xml = (new Bp21Xml)->buat(['tin' => '0763570041524000'], [$this->baris()]);

        $harapan = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\r\n"
            . "<Bp21Bulk xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\r\n"
            . "\t<TIN>0763570041524000</TIN>\r\n"
            . "\t<ListOfBp21>\r\n"
            . "\t\t<Bp21>\r\n"
            . "\t\t\t<TaxPeriodMonth>8</TaxPeriodMonth>\r\n"
            . "\t\t\t<TaxPeriodYear>2026</TaxPeriodYear>\r\n"
            . "\t\t\t<CounterpartTin>3308094104690007</CounterpartTin>\r\n"
            . "\t\t\t<IDPlaceOfBusinessActivityOfIncomeRecipient>3308094104690007000000</IDPlaceOfBusinessActivityOfIncomeRecipient>\r\n"
            . "\t\t\t<StatusTaxExemption>TK/0</StatusTaxExemption>\r\n"
            . "\t\t\t<TaxCertificate>N/A</TaxCertificate>\r\n"
            . "\t\t\t<TaxObjectCode>21-100-35</TaxObjectCode>\r\n"
            . "\t\t\t<Gross>2584275</Gross>\r\n"
            . "\t\t\t<Deemed>100</Deemed>\r\n"
            . "\t\t\t<Rate>0</Rate>\r\n"
            . "\t\t\t<Document>Other</Document>\r\n"
            . "\t\t\t<DocumentNumber>82026-AFKIR</DocumentNumber>\r\n"
            . "\t\t\t<DocumentDate>2026-08-30</DocumentDate>\r\n"
            . "\t\t\t<IDPlaceOfBusinessActivity>0763570041524000000000</IDPlaceOfBusinessActivity>\r\n"
            . "\t\t\t<WithholdingDate>2026-09-01</WithholdingDate>\r\n"
            . "\t\t</Bp21>\r\n"
            . "\t</ListOfBp21>\r\n"
            . '</Bp21Bulk>';

        $this->assertSame($harapan, $xml);
    }

    public function test_angka_desimal_maksimal_15_digit_signifikan(): void
    {
        $xml = (new Bp21Xml)->buat(['tin' => '0763570041524000'], [$this->baris(['gross' => 2699451.342857143, 'rate' => 0.25])]);

        $this->assertStringContainsString('<Gross>2699451.34285714</Gross>', $xml);
        $this->assertStringContainsString('<Rate>0.25</Rate>', $xml);
    }

    public function test_npwp_bukan_16_digit_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Bp21Xml)->buat(['tin' => '0763570041524000'], [$this->baris(['tin' => '12345'])]);
    }

    public function test_tarif_ter_per_kategori(): void
    {
        $this->assertSame('A', TerPph21::kategori('TK/1'));
        $this->assertSame('B', TerPph21::kategori('K/2'));
        $this->assertSame('C', TerPph21::kategori('K/3'));

        $this->assertSame(0.0, TerPph21::tarif(5400000, 'TK/0'));
        $this->assertSame(0.25, TerPph21::tarif(5400001, 'TK/0'));
        $this->assertSame(2.0, TerPph21::tarif(10000000, 'TK/0'));
        $this->assertSame(0.75, TerPph21::tarif(7000000, 'TK/2'));
        $this->assertSame(34.0, TerPph21::tarif(2000000000, 'TK/0'));
    }
}
