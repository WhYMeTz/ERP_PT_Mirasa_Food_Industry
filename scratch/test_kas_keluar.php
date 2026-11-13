<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Keuangan\MstCoa;
use App\Models\Keuangan\MstKasBank;
use App\Models\User;
use App\Services\Keuangan\KasKeluarService;
use App\Services\Keuangan\LaporanKeuanganService;

$user = User::first();
$service = app(KasKeluarService::class);
$kasBank = MstKasBank::where('deleted_st', false)->first();
$coaListrik = MstCoa::where('kode_akun', '6102')->first(); // Beban air & Listrik

echo "=== TEST PENYIMPANAN BKK OPERASIONAL ===\n";
echo "Akun Kas: {$kasBank->nama_akun} (Saldo awal: Rp " . number_format($kasBank->saldo_berjalan, 0, ',', '.') . ")\n";
echo "COA Beban: {$coaListrik->kode_akun} - {$coaListrik->nama_akun}\n";

$payload = [
    'tgl_keluar'           => date('Y-m-d'),
    'kas_bank_id'          => $kasBank->kas_bank_id,
    'coa_id'               => $coaListrik->coa_id,
    'kategori_pengeluaran' => 'LISTRIK_AIR',
    'penerima_nama'        => 'PLN Persero (Token Listrik Line Penggorengan)',
    'metode_bayar'         => 'CASH',
    'no_referensi_bank'    => 'STRUK-PLN-121126',
    'catatan'              => 'Pembelian token listrik 500rb untuk mesin penggorengan keripik.',
    'items'                => [
        [
            'uraian'       => 'Token Listrik Pabrik Line A (500 kWh)',
            'qty'          => 1,
            'satuan'       => 'Token',
            'harga_satuan' => 500000,
            'nominal'      => 500000,
            'coa_id'       => $coaListrik->coa_id,
        ],
    ],
];

$bkk = $service->storeKasKeluar($payload, $user);
echo "\nHASIL SIMPAN BKK:\n";
echo "- No BKK: {$bkk->bkk_no} (ID: {$bkk->bkk_id})\n";
echo "- Penerima: {$bkk->penerima_nama}\n";
echo "- Total Keluar: Rp " . number_format($bkk->total_keluar, 0, ',', '.') . "\n";
echo "- Jurnal ID: {$bkk->jurnal_id} (No: {$bkk->jurnal?->jurnal_no})\n";
echo "- Saldo Kas Sekarang: Rp " . number_format($kasBank->fresh()->saldo_berjalan, 0, ',', '.') . "\n";

echo "\n=== CEK LAPORAN LABA RUGI SETELAH BKK ===\n";
$laporanService = app(LaporanKeuanganService::class);
$lr = $laporanService->getLabaRugi('2026-01-01', '2026-12-31');
echo "Total Beban Operasional: Rp " . number_format($lr['total_beban_ops'], 0, ',', '.') . "\n";
echo "Rincian Beban Terdaftar:\n";
foreach ($lr['beban_operasional'] as $bo) {
    echo "  * {$bo['kode_akun']} - {$bo['nama_akun']}: Rp " . number_format($bo['nominal'], 0, ',', '.') . "\n";
}
echo "Laba Bersih Sekarang: Rp " . number_format($lr['laba_bersih'], 0, ',', '.') . "\n";

