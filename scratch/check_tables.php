<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
echo "=== TABEL PENJUALAN / INVOICE / PRODUKSI ===\n";
foreach ($tables as $t) {
    $name = $t->table_name;
    if (preg_match('/(jual|so|inv|faktur|prod|spk|surat_jalan|piutang|ar_|kas|bank|karton)/i', $name)) {
        $count = DB::table($name)->count();
        echo "- $name ($count rows)\n";
    }
}
