<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$columns = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'dat_bayar_hutang_hdr'");
echo "=== KOLOM dat_bayar_hutang_hdr ===\n";
foreach ($columns as $c) {
    echo "- {$c->column_name} ({$c->data_type})\n";
}

$columnsMutasi = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'dat_kas_mutasi'");
echo "\n=== KOLOM dat_kas_mutasi ===\n";
foreach ($columnsMutasi as $c) {
    echo "- {$c->column_name} ({$c->data_type})\n";
}

$cols = DB::select("SELECT column_name, is_nullable, data_type FROM information_schema.columns WHERE table_name = 'dat_bayar_hutang_hdr'");
echo "=== NULLABILITY dat_bayar_hutang_hdr ===\n";
foreach ($cols as $c) {
    echo "- {$c->column_name}: {$c->is_nullable} ({$c->data_type})\n";
}

echo "\n=== MST KAS BANK & COA ===\n";
$kbs = App\Models\Keuangan\MstKasBank::all();
foreach ($kbs as $kb) {
    echo "ID: {$kb->kas_bank_id} | {$kb->nama_akun} | Kode: {$kb->kode_akun} | coa_id: {$kb->coa_id}\n";
}
