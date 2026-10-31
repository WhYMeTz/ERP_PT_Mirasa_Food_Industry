<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

App\Models\User::where('role_cd', 'DIREKSI')->update(['signature_path' => 'ttd_stempel_1.png']);
App\Models\User::where('role_cd', 'MANAJER')->update(['signature_path' => 'ttd_stempel_2.png']);
App\Models\User::where('id', 6)->update(['signature_path' => null]);

$users = App\Models\User::all(['id', 'name', 'role_cd', 'signature_path']);
foreach ($users as $u) {
    echo "ID: {$u->id} | {$u->name} | {$u->role_cd} | Signature: {$u->signature_path}" . PHP_EOL;
}
