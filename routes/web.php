<?php

use App\Http\Controllers\MasterData\BarangController;
use App\Http\Controllers\Common\CodeGeneratorController;
use App\Http\Controllers\Gudang\PoController;
use App\Http\Controllers\Gudang\StokController;
use App\Http\Controllers\Gudang\TerimaBarangController;
use App\Http\Controllers\MasterData\CustomerController;
use App\Http\Controllers\MasterData\GudangController;
use App\Http\Controllers\MasterData\JenisBarangController;
use App\Http\Controllers\MasterData\JenisSupplierController;
use App\Http\Controllers\MasterData\SatuanController;
use App\Http\Controllers\MasterData\SupplierController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\UserController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');
Route::get('quick-login/{id}', [AuthController::class, 'quickLogin'])->name('quick.login');

Route::get('/', function () {
    return redirect()->route('master.barang.index');
});

// Utility Routes
Route::get('/ajax/generate-code', [CodeGeneratorController::class, 'generate'])->name('ajax.generate_code');

// Master Data Routes
Route::resource('master-barang', BarangController::class)->names('master.barang');
Route::resource('master-satuan', SatuanController::class)->names('master.satuan');
Route::resource('master-jenis', JenisBarangController::class)->names('master.jenis');
Route::resource('master-jenis-supplier', JenisSupplierController::class)->names('master.jenis_supplier');
Route::resource('master-gudang', GudangController::class)->names('master.gudang');
Route::resource('master-supplier', SupplierController::class)->names('master.supplier');
Route::resource('master-customer', CustomerController::class)->names('master.customer');
Route::resource('master-karyawan', \App\Http\Controllers\MasterData\KaryawanController::class)->names('master.karyawan');

// Manajemen Pengguna & Hak Akses
Route::resource('pengguna-sistem', \App\Http\Controllers\Auth\UserController::class)->names('admin.users');

use App\Http\Controllers\Gudang\PemakaianController;

// Modul Transaksi Gudang (Inbound & Inventory Engine)
Route::prefix('gudang')->name('gudang.')->group(function () {
    Route::post('po/{id}/cancel', [PoController::class, 'cancel'])->name('po.cancel');
    Route::post('po/{id}/force-close', [PoController::class, 'forceClose'])->name('po.force_close');
    Route::resource('po', PoController::class);

    Route::resource('terima', TerimaBarangController::class);

    Route::get('pemakaian/batches', [PemakaianController::class, 'getBatches'])->name('pemakaian.batches');
    Route::resource('pemakaian', PemakaianController::class);

    Route::get('stok', [StokController::class, 'index'])->name('stok.index');
    Route::get('stok/ledger', [StokController::class, 'ledger'])->name('stok.ledger');
});
