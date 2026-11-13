<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Keuangan\DatKasKeluarHdr;
use App\Models\User;
use App\Services\Keuangan\KasKeluarService;
use App\Services\Keuangan\LaporanKeuanganService;

$user = User::first();
$service = app(KasKeluarService::class);
$bkk = DatKasKeluarHdr::where('status_cd', 'POSTED')->latest('bkk_id')->first();

echo "=== TEST VOID BKK OPERASIONAL ===\n";
echo "Membatalkan BKK: {$bkk->bkk_no} (Total: Rp " . number_format($bkk->total_keluar, 0, ',', '.') . ")\n";
$voided = $service->voidKasKeluar($bkk->bkk_id, 'Salah input nominal token listrik untuk uji coba sistem', $user);

echo "- Status BKK Sekarang: {$voided->status_cd}\n";
echo "- Saldo Kas Rekening Kembali Jadi: Rp " . number_format($voided->kasBank->fresh()->saldo_berjalan, 0, ',', '.') . "\n";

$laporanService = app(LaporanKeuanganService::class);
$lr = $laporanService->getLabaRugi('2026-01-01', '2026-12-31');
echo "- Total Beban Operasional di Laba Rugi Kembali: Rp " . number_format($lr['total_beban_ops'], 0, ',', '.') . "\n";

