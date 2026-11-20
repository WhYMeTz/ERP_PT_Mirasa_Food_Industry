<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$demoUsers = App\Models\User::take(6)->get();
$html = view('auth.login', compact('demoUsers'))->render();

echo "Login view rendered successfully! Output length: " . strlen($html) . " bytes." . PHP_EOL;

