<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$accounts = App\Models\Keuangan\MstCoa::where('active_st', true)->orderBy('kode_akun')->get(['kode_akun', 'nama_akun', 'kategori_akun']);
foreach ($accounts as $a) {
    echo "{$a->kode_akun} - {$a->nama_akun} ({$a->kategori_akun})\n";
}
