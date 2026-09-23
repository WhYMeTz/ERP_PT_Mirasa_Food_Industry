<?php

use App\Http\Controllers\MasterData\BarangController;
use App\Http\Controllers\Common\CodeGeneratorController;
use App\Http\Controllers\MasterData\CustomerController;
use App\Http\Controllers\MasterData\GudangController;
use App\Http\Controllers\MasterData\JenisBarangController;
use App\Http\Controllers\MasterData\JenisSupplierController;
use App\Http\Controllers\MasterData\SatuanController;
use App\Http\Controllers\MasterData\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('master.barang.index');
});

// Utility Routes
Route::get('/ajax/generate-code', [CodeGeneratorController::class, 'generate'])->name('ajax.generate_code');

Route::resource('master-barang', BarangController::class)->names('master.barang');
Route::resource('master-satuan', SatuanController::class)->names('master.satuan');
Route::resource('master-jenis', JenisBarangController::class)->names('master.jenis');
Route::resource('master-jenis-supplier', JenisSupplierController::class)->names('master.jenis_supplier');
Route::resource('master-gudang', GudangController::class)->names('master.gudang');
Route::resource('master-supplier', SupplierController::class)->names('master.supplier');
Route::resource('master-customer', CustomerController::class)->names('master.customer');
