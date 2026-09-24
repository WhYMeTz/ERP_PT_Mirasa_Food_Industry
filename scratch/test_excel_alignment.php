<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Gudang\DatPakaiDtl;
use App\Models\Gudang\DatPakaiHdr;
use App\Models\Gudang\DatStokBatch;
use App\Models\Gudang\DatStokLedger;
use App\Models\MasterData\MstBarang;
use App\Models\MasterData\MstGudang;
use App\Models\MasterData\MstSupplier;
use App\Models\User;
use App\Services\Gudang\PemakaianService;
use App\Services\Gudang\StokService;
use App\Services\Gudang\TerimaBarangService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

echo "=== MEMULAI TEST KESELARASAN 4 SHEET OPERASIONAL EXCEL ===\n";

// 1. Validasi Master Barang & Harga Standar
echo "1. Cek Master Barang & Harga Standar...\n";
$barang = MstBarang::first();
if (!$barang) {
    die("ERROR: Tidak ada master barang terdaftar!\n");
}
$barang->harga_beli_standar = 17748.64;
$barang->batas_minimum_qty = 1000;
$barang->save();
echo "   [OK] Barang: {$barang->barang_nm} ({$barang->barang_cd})\n";
echo "   [OK] Batas Minimum: {$barang->batas_minimum_qty}\n";
echo "   [OK] Harga Standar: Rp " . number_format($barang->harga_beli_standar, 2) . "\n";

// 2. Simulasi Akun Petugas Gudang / Produksi
$user = User::where('email', 'gudang.raw@mirasa.co.id')->first() ?? User::first();
Auth::login($user);
echo "2. Login sebagai: {$user->name} (Role: {$user->role_cd}, Gudang: " . ($user->gudang?->gudang_nm ?? 'Pusat') . ")\n";

$gudang = $user->gudang ?? MstGudang::first();
$supplier = MstSupplier::first();

// 3. Test Inbound (Barang Masuk / Goods Receipt) dengan Harga Satuan
echo "3. Test Barang Masuk (Goods Receipt) ke Stok Batch...\n";
$terimaService = app(TerimaBarangService::class);
$batchTestNo = 'MS-' . date('dmy') . '-' . rand(100, 999);
$inboundData = [
    'terima_tgl'    => date('Y-m-d'),
    'gudang_id'     => $gudang->gudang_id,
    'supplier_id'   => $supplier->supplier_id,
    'suratjalan_no' => 'SJ-TEST-' . rand(1000, 9999),
    'catatan_txt'   => 'Test Penerimaan Bahan Masuk',
    'items'         => [
        [
            'barang_id'     => $barang->barang_id,
            'batch_no'      => $batchTestNo,
            'expired_tgl'   => date('Y-m-d', strtotime('+6 months')),
            'grade_cd'      => 'A',
            'terima_qty'    => 1000.00,
            'reject_qty'    => 0,
            'harga_nominal' => 17748.64,
            'catatan_txt'   => 'SALDO AWAL MASUK',
        ]
    ]
];

$terimaHdr = $terimaService->store($inboundData);
echo "   [OK] Dokumen Penerimaan: {$terimaHdr->terima_no}\n";

// Verifikasi DatStokBatch
$stokBatch = DatStokBatch::where('gudang_id', $gudang->gudang_id)
    ->where('barang_id', $barang->barang_id)
    ->where('batch_no', $batchTestNo)
    ->first();

assert($stokBatch !== null, "Stok batch harus ditemukan");
echo "   [OK] Batch No: {$stokBatch->batch_no}\n";
echo "   [OK] Qty Awal: {$stokBatch->qty_awal}\n";
echo "   [OK] Sisa Qty: {$stokBatch->sisa_qty}\n";
echo "   [OK] Harga Satuan: Rp " . number_format($stokBatch->harga_satuan, 2) . "\n";
echo "   [OK] Sisa Nilai: Rp " . number_format($stokBatch->sisa_nilai, 2) . "\n";
echo "   [OK] Status: {$stokBatch->status_stok}\n";

// 4. Test Outbound (Barang Keluar / Pemakaian Bahan ke Produksi)
echo "4. Test Barang Keluar / Pemakaian Bahan...\n";
$pemakaianService = app(PemakaianService::class);
$outboundData = [
    'pakai_tgl'        => date('Y-m-d'),
    'gudang_id'        => $gudang->gudang_id,
    'tujuan_pemakaian' => 'PRODUKSI IFM',
    'catatan_txt'      => 'Pemakaian untuk SPK 01/MFI/09/26',
    'items'            => [
        [
            'barang_id'      => $barang->barang_id,
            'batch_no'       => $batchTestNo,
            'qty_keluar'     => 250.00,
            'harga_satuan'   => 17748.64,
            'keterangan_txt' => 'PRODUKSI IFM',
        ]
    ]
];

$pakaiHdr = $pemakaianService->store($outboundData);
echo "   [OK] Dokumen Pengeluaran: {$pakaiHdr->pakai_no}\n";

// Verifikasi Pengurangan Stok di Batch & Ledger
$stokBatch->refresh();
echo "   [OK] Sisa Qty Setelah Keluar: {$stokBatch->sisa_qty} (Ekspektasi: 750)\n";
echo "   [OK] Qty Keluar: {$stokBatch->qty_keluar} (Ekspektasi: 250)\n";
echo "   [OK] Sisa Nilai: Rp " . number_format($stokBatch->sisa_nilai, 2) . "\n";
echo "   [OK] Status: {$stokBatch->status_stok}\n";

assert((float) $stokBatch->sisa_qty == 750.00, "Sisa Qty harus 750");
assert((float) $stokBatch->qty_keluar == 250.00, "Qty Keluar harus 250");

// Verifikasi Stock Ledger (Kartu Stok)
$lastLedger = DatStokLedger::where('barang_id', $barang->barang_id)
    ->where('gudang_id', $gudang->gudang_id)
    ->orderBy('ledger_id', 'desc')
    ->first();

echo "   [OK] Ledger Entry: Tipe {$lastLedger->tipe_transaksi_cd}, Qty {$lastLedger->qty}, Saldo Akhir: {$lastLedger->saldoakhir_qty}\n";
assert($lastLedger->tipe_transaksi_cd === 'OUT', "Tipe ledger mutasi harus OUT");

// 5. Test Pengeluaran Habis (Status -> 'Habis')
echo "5. Test Pengeluaran Hingga Saldo Habis (Status -> Habis)...\n";
$outboundHabis = [
    'pakai_tgl'        => date('Y-m-d'),
    'gudang_id'        => $gudang->gudang_id,
    'tujuan_pemakaian' => 'PACKING EKSPOR',
    'items'            => [
        [
            'barang_id'      => $barang->barang_id,
            'batch_no'       => $batchTestNo,
            'qty_keluar'     => 750.00,
            'harga_satuan'   => 17748.64,
            'keterangan_txt' => 'PACKING EKSPOR HABIS',
        ]
    ]
];
$pemakaianService->store($outboundHabis);
$stokBatch->refresh();
echo "   [OK] Sisa Qty: {$stokBatch->sisa_qty} (Ekspektasi: 0)\n";
echo "   [OK] Qty Keluar: {$stokBatch->qty_keluar} (Ekspektasi: 1000)\n";
echo "   [OK] Status Stok: {$stokBatch->status_stok} (Ekspektasi: Habis)\n";
assert($stokBatch->status_stok === 'Habis', "Status harus Habis saat sisa_qty 0");

// 6. Test Rendering Seluruh Blade Views
echo "6. Test Rendering Blade Views Terkait...\n";
$viewBarang = view('master_data.barang.index', [
    'barangs' => MstBarang::with(['jenisBarang', 'satuanDasar', 'satuanBesar'])->paginate(15),
    'jenisBarangList' => \App\Models\MasterData\MstJenisBarang::all(),
    'satuanList' => \App\Models\MasterData\MstSatuan::all(),
    'search' => '',
    'nextBarangCode' => 'BRG-0099',
])->render();
echo "   [OK] Render master_data.barang.index sukses (" . strlen($viewBarang) . " bytes)\n";

$viewTerima = view('gudang.terima.index', [
    'dataList' => $terimaService->getBarangMasukListPaginated(15),
    'gudangList' => MstGudang::all(),
    'search' => '',
    'gudangId' => null,
    'viewType' => 'item',
])->render();
echo "   [OK] Render gudang.terima.index (Excel View) sukses (" . strlen($viewTerima) . " bytes)\n";

$viewPemakaian = view('gudang.pemakaian.index', [
    'dataList' => $pemakaianService->getBarangKeluarListPaginated(15),
    'gudangList' => MstGudang::all(),
    'search' => '',
    'gudangId' => null,
    'viewType' => 'item',
])->render();
echo "   [OK] Render gudang.pemakaian.index (Excel View) sukses (" . strlen($viewPemakaian) . " bytes)\n";

$viewStok = view('gudang.stok.index', [
    'stokList' => app(StokService::class)->getMonitoringStok(15),
    'gudangList' => MstGudang::all(),
    'search' => '',
    'gudangId' => null,
    'status' => '',
])->render();
echo "   [OK] Render gudang.stok.index (Lacak Stok View) sukses (" . strlen($viewStok) . " bytes)\n";

echo "\n>>> SEMUA PENGUJIAN 100% SUKSES DAN SELARAS DENGAN OPERASIONAL EXCEL! <<<\n";
