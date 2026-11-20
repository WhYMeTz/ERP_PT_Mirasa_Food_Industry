<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$barang = App\Models\MasterData\MstBarang::where('barang_nm', 'ILIKE', '%gas%')->get();
echo "Barang matching 'gas': " . $barang->count() . "\n";
foreach ($barang as $b) {
    echo "- [{$b->barang_id}] {$b->barang_cd} - {$b->barang_nm} (Kategori: {$b->jenis_id})\n";
}

$suppliers = App\Models\MasterData\MstSupplier::where('supplier_nm', 'ILIKE', '%gas%')->orWhere('supplier_nm', 'ILIKE', '%pgn%')->orWhere('supplier_nm', 'ILIKE', '%samator%')->get();
echo "\nSuppliers matching gas/pgn/samator: " . $suppliers->count() . "\n";
foreach ($suppliers as $s) {
    echo "- [{$s->supplier_id}] {$s->supplier_cd} - {$s->supplier_nm}\n";
}

