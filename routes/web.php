<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Common\CodeGeneratorController;
use App\Http\Controllers\Gudang\PemakaianController;
use App\Http\Controllers\Gudang\PoController;
use App\Http\Controllers\Gudang\ReturPembelianController;
use App\Http\Controllers\Gudang\StokController;
use App\Http\Controllers\Gudang\TerimaBarangController;
use App\Http\Controllers\MasterData\BarangController;
use App\Http\Controllers\MasterData\CustomerController;
use App\Http\Controllers\MasterData\GudangController;
use App\Http\Controllers\MasterData\JenisBarangController;
use App\Http\Controllers\MasterData\JenisSupplierController;
use App\Http\Controllers\MasterData\KaryawanController;
use App\Http\Controllers\MasterData\SatuanController;
use App\Http\Controllers\MasterData\SupplierController;
use App\Http\Controllers\Produksi\BomController;
use App\Http\Controllers\Produksi\ProduksiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public / Authentication Routes
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');
Route::get('quick-login/{id}', [AuthController::class, 'quickLogin'])->name('quick.login');

// Root Redirection
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route(Auth::user()->getDashboardRoute());
    }
    return redirect()->route('login');
});

// Utility Routes (Bebas diakses oleh formulir web untuk live generator kode/batch)
Route::get('/ajax/generate-code', [CodeGeneratorController::class, 'generate'])->name('ajax.generate_code');

// Authenticated Routes (Harus Login)
Route::middleware('auth')->group(function () {
    // Manajemen Pengguna & Pengaturan Hak Akses Sistem (Khusus Superadmin)
    Route::get('pengguna-sistem/hak-akses', [UserController::class, 'permissions'])
        ->name('admin.users.permissions')
        ->middleware('role:user_manage,SUPERADMIN');
    Route::post('pengguna-sistem/hak-akses', [UserController::class, 'updatePermissions'])
        ->name('admin.users.permissions.update')
        ->middleware('role:user_manage,SUPERADMIN');
    Route::resource('pengguna-sistem', UserController::class)
        ->names('admin.users')
        ->middleware('role:user_manage,SUPERADMIN');

    // Master Data Routes
    Route::get('master-barang/export', [BarangController::class, 'export'])->name('master.barang.export');
    Route::get('master-barang/template', [BarangController::class, 'template'])->name('master.barang.template');
    Route::post('master-barang/import', [BarangController::class, 'import'])->name('master.barang.import');
    Route::resource('master-barang', BarangController::class)->names('master.barang');
    Route::resource('master-satuan', SatuanController::class)->names('master.satuan');
    Route::resource('master-jenis', JenisBarangController::class)->names('master.jenis');
    Route::resource('master-gudang', GudangController::class)->names('master.gudang');
    Route::resource('master-resep', BomController::class)->names('master.resep');

    // Master Supplier (Superadmin & Purchasing)
    Route::resource('master-jenis-supplier', JenisSupplierController::class)
        ->names('master.jenis_supplier')
        ->middleware('role:SUPERADMIN,PURCHASING');
    Route::resource('master-supplier', SupplierController::class)
        ->names('master.supplier')
        ->middleware('role:SUPERADMIN,PURCHASING');

    // Master Customer & Karyawan (Khusus Superadmin)
    Route::resource('master-customer', CustomerController::class)
        ->names('master.customer')
        ->middleware('role:SUPERADMIN');
    Route::resource('master-karyawan', KaryawanController::class)
        ->names('master.karyawan')
        ->middleware('role:SUPERADMIN');

    // Modul Transaksi Gudang (Inbound, Outbound & Inventory Engine)
    Route::prefix('gudang')->name('gudang.')->group(function () {
        // Purchase Order (PO): Create & Manage (Berdasarkan izin 'po_create')
        Route::post('po/{id}/cancel', [PoController::class, 'cancel'])
            ->name('po.cancel')
            ->middleware('role:po_create');
        Route::post('po/{id}/force-close', [PoController::class, 'forceClose'])
            ->name('po.force_close')
            ->middleware('role:po_create');
        Route::get('po/create', [PoController::class, 'create'])
            ->name('po.create')
            ->middleware('role:po_create');
        Route::post('po', [PoController::class, 'store'])
            ->name('po.store')
            ->middleware('role:po_create');
        Route::get('po/{po}/edit', [PoController::class, 'edit'])
            ->name('po.edit')
            ->middleware('role:po_create');
        Route::put('po/{po}', [PoController::class, 'update'])
            ->name('po.update')
            ->middleware('role:po_create');
        Route::patch('po/{po}', [PoController::class, 'update']);
        Route::delete('po/{po}', [PoController::class, 'destroy'])
            ->name('po.destroy')
            ->middleware('role:po_create');
        Route::resource('po', PoController::class)->only(['index', 'show'])->middleware('role:po_view');

        // Barang Masuk (GRN / Inbound): Terima Barang & Batch (Berdasarkan izin 'terima_create')
        Route::get('terima/create', [TerimaBarangController::class, 'create'])
            ->name('terima.create')
            ->middleware('role:terima_create');
        Route::post('terima', [TerimaBarangController::class, 'store'])
            ->name('terima.store')
            ->middleware('role:terima_create');
        Route::get('terima/{terima}/edit', [TerimaBarangController::class, 'edit'])
            ->name('terima.edit')
            ->middleware('role:terima_create');
        Route::put('terima/{terima}', [TerimaBarangController::class, 'update'])
            ->name('terima.update')
            ->middleware('role:terima_create');
        Route::delete('terima/{terima}', [TerimaBarangController::class, 'destroy'])
            ->name('terima.destroy')
            ->middleware('role:terima_create');
        Route::resource('terima', TerimaBarangController::class)->only(['index', 'show'])->middleware('role:terima_view');

        // Retur Pembelian ke Supplier (Outbound Retur Cacat/Reject): (Berdasarkan izin 'retur_create')
        Route::get('retur/batches', [ReturPembelianController::class, 'getBatches'])->name('retur.batches');
        Route::get('retur/po-data/{poId}', [ReturPembelianController::class, 'getPoData'])->name('retur.po-data');
        Route::get('retur/create', [ReturPembelianController::class, 'create'])
            ->name('retur.create')
            ->middleware('role:retur_create');
        Route::post('retur', [ReturPembelianController::class, 'store'])
            ->name('retur.store')
            ->middleware('role:retur_create');
        Route::resource('retur', ReturPembelianController::class)->only(['index', 'show'])->middleware('role:retur_view');

        // Barang Keluar (Pemakaian Bahan Baku / Outbound): (Berdasarkan izin 'pemakaian_create')
        Route::get('pemakaian/batches', [PemakaianController::class, 'getBatches'])->name('pemakaian.batches');
        Route::get('pemakaian/alokasi-resep', [PemakaianController::class, 'alokasiResepFifo'])->name('pemakaian.alokasi-resep');
        Route::get('pemakaian/create', [PemakaianController::class, 'create'])
            ->name('pemakaian.create')
            ->middleware('role:pemakaian_create');
        Route::post('pemakaian', [PemakaianController::class, 'store'])
            ->name('pemakaian.store')
            ->middleware('role:pemakaian_create');
        Route::resource('pemakaian', PemakaianController::class)->only(['index', 'show'])->middleware('role:pemakaian_view');

        // Monitoring Persediaan: Lacak Stok & Kartu Stok (Berdasarkan izin 'stok_view')
        Route::get('stok', [StokController::class, 'index'])->name('stok.index')->middleware('role:stok_view');
        Route::get('stok/ledger', [StokController::class, 'ledger'])->name('stok.ledger')->middleware('role:stok_view');
    });

    // Produksi & HPP Harian (Sesuai Excel Asli PT Mirasa)
    Route::prefix('produksi')->name('produksi.')->middleware('role:produksi_view')->group(function () {
        Route::get('/', [ProduksiController::class, 'index'])->name('index');
        Route::get('/rekap', [ProduksiController::class, 'index'])->name('rekap');
        Route::get('/create', [ProduksiController::class, 'create'])->name('create')->middleware('role:produksi_create');
        Route::post('/', [ProduksiController::class, 'store'])->name('store')->middleware('role:produksi_create');
        Route::get('/pakai-data/{pakaiId}', [ProduksiController::class, 'getPakaiData'])->name('pakai-data');
        Route::delete('/{id}', [ProduksiController::class, 'destroy'])->name('destroy')->middleware('role:produksi_create');
    });
});
