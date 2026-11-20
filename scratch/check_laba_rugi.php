<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== SEMUA DAFTAR COA DI DATABASE (RESMI PERUSAHAAN) ===\n";
$coas = App\Models\Keuangan\MstCoa::orderBy('kode_akun')->get();
foreach ($coas as $c) {
    echo sprintf("%-6s | %-35s | %-12s | %s\n", $c->kode_akun, $c->nama_akun, $c->kategori_akun, $c->saldo_normal);
}

echo "\n=== DATA JURNAL TRANSAKSI SAAT INI ===\n";
$jurnals = App\Models\Keuangan\DatJurnalHdr::with('details.coa')->where('deleted_st', false)->get();
echo "Total Jurnal Header: " . $jurnals->count() . "\n";
foreach ($jurnals as $j) {
    echo "- [{$j->jurnal_no}] {$j->tgl_jurnal} | {$j->keterangan}\n";
    foreach ($j->details as $d) {
        $coaName = $d->coa ? "{$d->coa->kode_akun} - {$d->coa->nama_akun}" : "COA #{$d->coa_id}";
        echo "   * {$coaName} | Debet: Rp " . number_format($d->debet, 0, ',', '.') . " | Kredit: Rp " . number_format($d->kredit, 0, ',', '.') . "\n";
    }
}

echo "\n=== TEST KALKULASI LAPORAN LAINNYA ===\n";
$service = app(App\Services\Keuangan\LaporanKeuanganService::class);
$asOfDate = '2026-11-30';
$neraca = $service->getNeraca($asOfDate);
echo "1. NERACA per {$asOfDate}:\n";
echo "   Total Aktiva: Rp " . number_format($neraca['total_aktiva'] ?? 0, 0, ',', '.') . "\n";
echo "   Total Kewajiban: Rp " . number_format($neraca['total_kewajiban'] ?? 0, 0, ',', '.') . "\n";
echo "   Total Ekuitas: Rp " . number_format($neraca['total_ekuitas'] ?? 0, 0, ',', '.') . "\n";
echo "   Total Pasiva (Kewajiban + Ekuitas): Rp " . number_format($neraca['total_pasiva'] ?? 0, 0, ',', '.') . "\n";
echo "   Selisih: Rp " . number_format($neraca['selisih'] ?? 0, 0, ',', '.') . "\n";
echo "   Status Balance: " . (($neraca['is_balanced'] ?? false) ? 'SEIMBANG (OK)' : 'TIDAK SEIMBANG') . "\n";

$neracaSaldo = $service->getNeracaSaldo('2026-01-01', '2026-11-30');
echo "2. NERACA SALDO:\n";
echo "   Sum Awal Debet: Rp " . number_format($neracaSaldo['sum_awal_debet'] ?? 0, 0, ',', '.') . "\n";
echo "   Sum Mutasi Debet: Rp " . number_format($neracaSaldo['sum_mutasi_debet'] ?? 0, 0, ',', '.') . "\n";
echo "   Sum Mutasi Kredit: Rp " . number_format($neracaSaldo['sum_mutasi_kredit'] ?? 0, 0, ',', '.') . "\n";
echo "   Sum Akhir Debet: Rp " . number_format($neracaSaldo['sum_akhir_debet'] ?? 0, 0, ',', '.') . "\n";
echo "   Sum Akhir Kredit: Rp " . number_format($neracaSaldo['sum_akhir_kredit'] ?? 0, 0, ',', '.') . "\n";
echo "   Status Mutasi Balance: " . (($neracaSaldo['is_mutasi_balanced'] ?? false) ? 'SEIMBANG (OK)' : 'TIDAK SEIMBANG') . "\n";
echo "   Status Akhir Balance: " . (($neracaSaldo['is_akhir_balanced'] ?? false) ? 'SEIMBANG (OK)' : 'TIDAK SEIMBANG') . "\n";

$arusKas = $service->getArusKas('2026-01-01', '2026-11-30');
echo "3. ARUS KAS:\n";
echo "   Saldo Awal Total: Rp " . number_format($arusKas['saldo_awal_total'] ?? 0, 0, ',', '.') . "\n";
echo "   Total Operasi Masuk: Rp " . number_format($arusKas['total_operasi_masuk'] ?? 0, 0, ',', '.') . "\n";
echo "   Total Operasi Keluar: Rp " . number_format($arusKas['total_operasi_keluar'] ?? 0, 0, ',', '.') . "\n";
echo "   Kenaikan Bersih Kas: Rp " . number_format($arusKas['kenaikan_bersih_kas'] ?? 0, 0, ',', '.') . "\n";
echo "   Saldo Akhir Total: Rp " . number_format($arusKas['saldo_akhir_total'] ?? 0, 0, ',', '.') . "\n";
