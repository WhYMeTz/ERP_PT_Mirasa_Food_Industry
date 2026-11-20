<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$gudang = App\Models\MasterData\MstGudang::where('active_st', true)->get();
echo "Total Gudang: " . $gudang->count() . "\n";
foreach ($gudang as $g) {
    echo "- [{$g->gudang_id}] {$g->gudang_cd} - {$g->gudang_nm} (Tipe/Kategori: {$g->tipe_gudang})\n";
}

