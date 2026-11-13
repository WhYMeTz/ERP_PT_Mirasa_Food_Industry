<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Keuangan\BayarHutangController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

try {
    $user = User::first();
    Auth::login($user);

    $controller = app(BayarHutangController::class);

    // Test 1: Tab default
    $request = Request::create('/keuangan/bayar', 'GET');
    $view = $controller->index($request);
    $html = $view->render();
    echo "1. Tab Default Rendered! HTML length: " . strlen($html) . " bytes\n";

    // Test 2: Tab operasional
    $requestOps = Request::create('/keuangan/bayar?tab=operasional', 'GET');
    $viewOps = $controller->index($requestOps);
    $htmlOps = $viewOps->render();
    echo "2. Tab Operasional Rendered! HTML length: " . strlen($htmlOps) . " bytes\n";

    echo "ALL VIEWS COMPILED CLEANLY!\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " on line " . $e->getLine() . " of " . $e->getFile() . "\n";
}

