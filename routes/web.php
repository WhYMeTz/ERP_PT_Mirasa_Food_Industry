<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RoleController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Common\CodeGeneratorController;
use App\Http\Controllers\Gudang\PemakaianController;
use App\Http\Controllers\Gudang\PoController;
use App\Http\Controllers\Gudang\QcInboundController;
use App\Http\Controllers\Gudang\ReturPembelianController;
use App\Http\Controllers\Gudang\StokController;
use App\Http\Controllers\Gudang\TerimaBarangController;
use App\Http\Controllers\MasterData\BarangController;
use App\Http\Controllers\MasterData\CustomerController;
use App\Http\Controllers\MasterData\GudangController;
use App\Http\Controllers\MasterData\JenisBarangController;
use App\Http\Controllers\MasterData\JenisSupplierController;
use App\Http\Controllers\MasterData\KaryawanController;
use App\Http\Controllers\MasterData\LiniProduksiController;
use App\Http\Controllers\MasterData\SatuanController;
use App\Http\Controllers\MasterData\SupplierController;
use App\Http\Controllers\Penjualan\SoController;
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

    // Manajemen Peran (Roles) Dinamis
    Route::get('pengguna-sistem/roles', [RoleController::class, 'index'])
        ->name('admin.roles.index')
        ->middleware('role:user_manage,SUPERADMIN');
    Route::post('pengguna-sistem/roles', [RoleController::class, 'store'])
        ->name('admin.roles.store')
        ->middleware('role:user_manage,SUPERADMIN');
    Route::put('pengguna-sistem/roles/{id}', [RoleController::class, 'update'])
        ->name('admin.roles.update')
        ->middleware('role:user_manage,SUPERADMIN');
    Route::delete('pengguna-sistem/roles/{id}', [RoleController::class, 'destroy'])
        ->name('admin.roles.destroy')
        ->middleware('role:user_manage,SUPERADMIN');

    Route::resource('pengguna-sistem', UserController::class)
        ->names('admin.users')
        ->middleware('role:user_manage,SUPERADMIN');

    // Master Data Routes
    Route::get('master-barang/export-pdf', [BarangController::class, 'exportPdf'])->name('master.barang.export.pdf');
    Route::get('master-barang/export', [BarangController::class, 'export'])->name('master.barang.export');
    Route::get('master-barang/template', [BarangController::class, 'template'])->name('master.barang.template');
    Route::post('master-barang/import', [BarangController::class, 'import'])->name('master.barang.import');
    Route::resource('master-barang', BarangController::class)->names('master.barang');
    Route::resource('master-satuan', SatuanController::class)->names('master.satuan');
    Route::resource('master-jenis', JenisBarangController::class)->names('master.jenis');
    Route::resource('master-perusahaan', GudangController::class)->names('master.perusahaan');
    Route::resource('master-gudang', GudangController::class)->names('master.gudang');
    Route::resource('master-resep', BomController::class)
        ->names('master.resep')
        ->middleware('role:SUPERADMIN,STAFF_PRODUKSI,master_resep_view,master_resep_manage');
    Route::resource('master-lini-produksi', LiniProduksiController::class)
        ->names('master.lini_produksi');

    // Master Supplier
    Route::resource('master-jenis-supplier', JenisSupplierController::class)->names('master.jenis_supplier');
    Route::resource('master-supplier', SupplierController::class)
        ->names('master.supplier')
        ->middleware('role:SUPERADMIN,PURCHASING');

    // Master Customer & Karyawan
    Route::resource('master-customer', CustomerController::class)->names('master.customer');
    Route::resource('master-karyawan', KaryawanController::class)
        ->names('master.karyawan')
        ->middleware('role:SUPERADMIN,HRD,master_karyawan_view,master_karyawan_manage');

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
        Route::get('po/{id}/edit', [PoController::class, 'edit'])
            ->name('po.edit')
            ->middleware('role:po_edit,po_create');
        Route::put('po/{id}', [PoController::class, 'update'])
            ->name('po.update')
            ->middleware('role:po_edit,po_create');
        Route::patch('po/{id}', [PoController::class, 'update']);
        Route::delete('po/{id}', [PoController::class, 'destroy'])
            ->name('po.destroy')
            ->middleware('role:po_delete');
        Route::get('po/{id}/export-pdf', [PoController::class, 'exportPdf'])
            ->name('po.export-pdf')
            ->middleware('role:po_view');
        Route::resource('po', PoController::class)->only(['index', 'show'])->middleware('role:po_view');

        // Antrean Tiket QC Inbound -> Dialihkan terpadu ke Riwayat QC & Dokumen HACCP
        Route::get('qc-antrean', function () {
            return redirect()->route('qc.inbound.index', ['status_qc' => 'SIAP_GUDANG']);
        })->name('qc.antrean')->middleware('role:terima_view,terima_create,gudang,qc_view');
        Route::get('qc-antrean/{id}/haccp-cetak', [QcInboundController::class, 'gudangHaccpCetak'])
            ->name('qc.haccp_cetak')
            ->middleware('role:terima_view,terima_create,gudang,qc_view');

        // Barang Masuk (GRN / Inbound): Terima Barang & Batch (Berdasarkan izin 'terima_create')
        Route::get('terima/create', [TerimaBarangController::class, 'create'])
            ->name('terima.create')
            ->middleware('role:terima_create');
        Route::post('terima', [TerimaBarangController::class, 'store'])
            ->name('terima.store')
            ->middleware('role:terima_create');
        Route::get('terima/{terima}/edit', [TerimaBarangController::class, 'edit'])
            ->name('terima.edit')
            ->middleware('role:terima_edit,terima_create');
        Route::put('terima/{terima}', [TerimaBarangController::class, 'update'])
            ->name('terima.update')
            ->middleware('role:terima_edit,terima_create');
        Route::delete('terima/{terima}', [TerimaBarangController::class, 'destroy'])
            ->name('terima.destroy')
            ->middleware('role:terima_delete,terima_create');
        Route::get('terima/export-rekap-pdf', [TerimaBarangController::class, 'exportRekapPdf'])
            ->name('terima.export-rekap-pdf')
            ->middleware('role:terima_view');
        Route::get('terima/export-excel', [TerimaBarangController::class, 'exportExcel'])
            ->name('terima.export-excel')
            ->middleware('role:terima_view');
        Route::get('terima/download-template', [TerimaBarangController::class, 'downloadTemplate'])
            ->name('terima.download-template')
            ->middleware('role:terima_view');
        Route::post('terima/import-excel', [TerimaBarangController::class, 'importExcel'])
            ->name('terima.import-excel')
            ->middleware('role:terima_create');
        Route::get('terima/{id}/export-pdf', [TerimaBarangController::class, 'exportPdf'])
            ->name('terima.export-pdf')
            ->middleware('role:terima_view');
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
        Route::get('retur/{id}/berita-acara', [ReturPembelianController::class, 'beritaAcara'])
            ->name('retur.berita_acara')
            ->middleware('role:retur_view');
        Route::resource('retur', ReturPembelianController::class)->only(['index', 'show'])->middleware('role:retur_view');

        // Barang Keluar (Pemakaian Bahan Baku / Outbound): (Berdasarkan izin 'pemakaian_create')
        Route::get('pemakaian/batches', [PemakaianController::class, 'getBatches'])->name('pemakaian.batches');
        Route::get('pemakaian/alokasi-resep', [PemakaianController::class, 'alokasiResepFifo'])->name('pemakaian.alokasi-resep');
        Route::get('pemakaian/export-rekap-pdf', [PemakaianController::class, 'exportRekapPdf'])
            ->name('pemakaian.export-rekap-pdf')
            ->middleware('role:pemakaian_view');
        Route::get('pemakaian/export-excel', [PemakaianController::class, 'exportExcel'])
            ->name('pemakaian.export-excel')
            ->middleware('role:pemakaian_view');
        Route::get('pemakaian/download-template', [PemakaianController::class, 'downloadTemplate'])
            ->name('pemakaian.download-template')
            ->middleware('role:pemakaian_view');
        Route::post('pemakaian/import-excel', [PemakaianController::class, 'importExcel'])
            ->name('pemakaian.import-excel')
            ->middleware('role:pemakaian_create');
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
        Route::get('/rekap', [ProduksiController::class, 'rekap'])->name('rekap');
        Route::get('/export-hasil', [ProduksiController::class, 'exportHasilProduksi'])->name('export-hasil');
        Route::get('/download-template', [ProduksiController::class, 'downloadHasilTemplate'])->name('download-template');
        Route::post('/import-excel', [ProduksiController::class, 'importHasilProduksi'])->name('import-excel')->middleware('role:produksi_create');
        Route::get('/export-rekap-excel', [ProduksiController::class, 'exportRekapExcel'])->name('export-rekap-excel');
        Route::get('/export-rekap-pdf', [ProduksiController::class, 'exportRekapPdf'])->name('export-rekap-pdf');
        Route::get('/download-rekap-template', [ProduksiController::class, 'downloadRekapTemplate'])->name('download-rekap-template');
        Route::post('/import-rekap-excel', [ProduksiController::class, 'importRekapExcel'])->name('import-rekap-excel')->middleware('role:produksi_create');
        Route::post('/adjust-utilitas', [ProduksiController::class, 'adjustUtilitas'])->name('adjust-utilitas')->middleware('role:produksi_create');
        Route::get('/create', [ProduksiController::class, 'create'])->name('create')->middleware('role:produksi_create');
        Route::post('/', [ProduksiController::class, 'store'])->name('store')->middleware('role:produksi_create');
        Route::get('/pakai-data/{pakaiId}', [ProduksiController::class, 'getPakaiData'])->name('pakai-data');
        Route::get('/next-karton', [ProduksiController::class, 'getNextKarton'])->name('next-karton');
        Route::get('/{id}', [ProduksiController::class, 'show'])->name('show')->whereNumber('id');
        Route::get('/{id}/cetak-stiker', [ProduksiController::class, 'cetakStiker'])->name('cetak-stiker')->whereNumber('id');
        Route::delete('/{id}', [ProduksiController::class, 'destroy'])->name('destroy')->middleware('role:produksi_create')->whereNumber('id');
    });

    // Quality Control (QC) Inbound Bahan Baku (Mobile-First / Google Form Style)
    Route::prefix('qc')->name('qc.')->group(function () {
        Route::get('inbound/siap-gudang', [QcInboundController::class, 'getSiapGudang'])->name('inbound.siap_gudang');
        Route::get('inbound/ticket-data/{id}', [QcInboundController::class, 'getTicketData'])->name('inbound.ticket_data');
        Route::get('inbound/create', [QcInboundController::class, 'create'])->name('inbound.create')->middleware('role:qc_create');
        Route::post('inbound', [QcInboundController::class, 'store'])->name('inbound.store')->middleware('role:qc_create');
        Route::get('inbound/{id}/edit', [QcInboundController::class, 'edit'])->name('inbound.edit')->middleware('role:qc_edit,GUDANG');
        Route::put('inbound/{id}', [QcInboundController::class, 'update'])->name('inbound.update')->middleware('role:qc_edit,GUDANG');
        Route::delete('inbound/{id}', [QcInboundController::class, 'destroy'])->name('inbound.destroy')->middleware('role:qc_delete');
        Route::get('inbound/{id}/berita-acara', [QcInboundController::class, 'beritaAcara'])->name('inbound.berita_acara')->middleware('role:qc_view');
        Route::post('inbound/{id}/uji-goreng', [QcInboundController::class, 'updateUjiGoreng'])->name('inbound.update_uji_goreng')->middleware('role:qc_create');
        Route::resource('inbound', QcInboundController::class)->only(['index', 'show'])->names('inbound')->middleware('role:qc_view');
    });

    // Modul Transaksi Penjualan (PO Penjualan / Sales Order)
    Route::prefix('penjualan')->name('penjualan.')->group(function () {
        Route::get('so/{id}/export-faktur', [SoController::class, 'exportFakturPdf'])->name('so.export-faktur');
        Route::get('so/{id}/export-surat-jalan', [SoController::class, 'exportSuratJalanPdf'])->name('so.export-surat-jalan');
        Route::post('so/{id}/cancel', [SoController::class, 'cancel'])->name('so.cancel');
        Route::resource('so', SoController::class);
    });
});
