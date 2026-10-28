<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RoleController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Common\CodeGeneratorController;
use App\Http\Controllers\Gudang\AdjustmentController;
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
use App\Http\Controllers\MasterData\MesinController;
use App\Http\Controllers\MasterData\PajakController;
use App\Http\Controllers\MasterData\SatuanController;
use App\Http\Controllers\MasterData\SupplierController;
use App\Http\Controllers\MasterData\TarifProduksiController;
use App\Http\Controllers\MasterData\TarifKemasanController;
use App\Http\Controllers\Keuangan\BayarHutangController;
use App\Http\Controllers\Keuangan\CoaController;
use App\Http\Controllers\Keuangan\HutangController;
use App\Http\Controllers\Keuangan\KasBankController;
use App\Http\Controllers\Keuangan\KasMasukController;
use App\Http\Controllers\Keuangan\JurnalController;
use App\Http\Controllers\Keuangan\BukuBesarController;
use App\Http\Controllers\Keuangan\VerifikasiPemakaianController;
use App\Http\Controllers\Penjualan\SoController;
use App\Http\Controllers\Penjualan\PengeluaranBarangController;
use App\Http\Controllers\Penjualan\FakturPenjualanController;
use App\Http\Controllers\Penjualan\SoReturController;
use App\Http\Controllers\Produksi\BomController;
use App\Http\Controllers\Produksi\PackagingController;
use App\Http\Controllers\Produksi\PackagingOperatorController;
use App\Http\Controllers\Produksi\ProduksiController;
use App\Http\Controllers\Produksi\ProduksiStokController;
use App\Http\Controllers\Manajemen\FingerprintController;
use App\Http\Controllers\Manajemen\AbsensiController;
use App\Http\Controllers\Manajemen\PayrollController;
use App\Http\Controllers\Manajemen\PayrollHarianController;
use App\Http\Controllers\Manajemen\JadwalController;
use App\Http\Controllers\Manajemen\KodeShiftController;
use App\Http\Controllers\Manajemen\MultiPayrollController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public / Authentication Routes
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');
Route::get('quick-login/{id}', [AuthController::class, 'quickLogin'])->name('quick.login');

// Mobile (QC Lapangan & Operator Shopfloor) Authentication Routes
Route::get('mobile/login', [AuthController::class, 'showMobileLogin'])->name('mobile.login');
Route::post('mobile/login', [AuthController::class, 'mobileLogin'])->name('mobile.login.attempt');
Route::post('mobile/logout', [AuthController::class, 'mobileLogout'])->name('mobile.logout');
Route::get('qc/login', fn () => redirect()->route('mobile.login'))->name('qc.login');

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
    Route::patch('master-mesin/{id}/toggle-status', [MesinController::class, 'toggleStatus'])
        ->name('master.mesin.toggle_status');
    Route::resource('master-mesin', MesinController::class)
        ->names('master.mesin');
    Route::patch('master-pajak/{id}/toggle-status', [PajakController::class, 'toggleStatus'])
        ->name('master.pajak.toggle_status');
    Route::resource('master-pajak', PajakController::class)
        ->names('master.pajak');

    // Master Standar Tarif Produksi & FOH Pabrik
    Route::get('master-tarif-produksi/api-rates', [TarifProduksiController::class, 'apiRates'])
        ->name('master.tarif_produksi.api-rates');
    Route::post('master-tarif-produksi/quick-update', [TarifProduksiController::class, 'quickUpdate'])
        ->name('master.tarif_produksi.quick-update');
    Route::get('master-tarif-produksi', [TarifProduksiController::class, 'index'])
        ->name('master.tarif_produksi.index');
    Route::put('master-tarif-produksi/{id}', [TarifProduksiController::class, 'update'])
        ->name('master.tarif_produksi.update');

    // Master Standar Harga Kemasan & Packaging (Ping-Ping, Maksi, Lakban, Roll, Bumbu, dll)
    Route::get('master-tarif-kemasan/api-rates', [TarifKemasanController::class, 'apiRates'])
        ->name('master.tarif_kemasan.api-rates');
    Route::post('master-tarif-kemasan/quick-update', [TarifKemasanController::class, 'quickUpdate'])
        ->name('master.tarif_kemasan.quick-update');
    Route::get('master-tarif-kemasan', [TarifKemasanController::class, 'index'])
        ->name('master.tarif_kemasan.index');
    Route::put('master-tarif-kemasan/{id}', [TarifKemasanController::class, 'update'])
        ->name('master.tarif_kemasan.update');

    // Master Supplier
    Route::resource('master-jenis-supplier', JenisSupplierController::class)->names('master.jenis_supplier');
    Route::resource('master-supplier', SupplierController::class)
        ->names('master.supplier')
        ->middleware('role:SUPERADMIN,PURCHASING');

    // Master Customer
    Route::resource('master-customer', CustomerController::class)->names('master.customer');

    // Master Karyawan
    // Rute Master data karyawan
    Route::get('master-karyawan/export-excel', [KaryawanController::class, 'exportExcel'])->name('master.karyawan.export-excel');
    Route::post('master-karyawan/import', [KaryawanController::class, 'importExcel'])->name('master.karyawan.import');
    Route::get('master-karyawan/cetak-pdf', [KaryawanController::class, 'cetakPdf'])->name('master.karyawan.cetak-pdf');
    Route::get('master-karyawan/template-import', [KaryawanController::class, 'downloadTemplate'])
        ->name('master.karyawan.template-import');

    Route::resource('master-karyawan', KaryawanController::class)
        ->names('master.karyawan')
        ->middleware('role:SUPERADMIN,HRD,master_karyawan_view,master_karyawan_manage');

     // Rute Manajemen Karyawan
        Route::prefix('manajemen')->name('manajemen.')
        ->middleware(['auth', 'role:SUPERADMIN,HRD,fingerprint_view,absensi_view,payroll_view'])
        ->group(function () {
            Route::get('/fingerprint',     [FingerprintController::class, 'index'])->name('fingerprint.index');
            Route::post('/fingerprint/import', [FingerprintController::class, 'import'])->name('fingerprint.import');
            Route::post('/fingerprint/manual', [FingerprintController::class, 'store'])->name('fingerprint.store');
            Route::post('/fingerprint/bulk-update', [FingerprintController::class, 'bulkUpdate'])->name('fingerprint.bulk-update');
            Route::post('/fingerprint/bulk-delete', [FingerprintController::class, 'bulkDelete'])->name('fingerprint.bulk-delete');
            Route::get('/absensi',         [AbsensiController::class, 'index'])->name('absensi.index');
            Route::post('/absensi/hitung', [AbsensiController::class, 'hitung'])->name('absensi.hitung');
            Route::get('/absensi/export', [AbsensiController::class, 'exportExcel'])->name('absensi.export');
            Route::get('/absensi/cetak-pdf', [AbsensiController::class, 'cetakPdf'])->name('absensi.cetak-pdf');
            Route::get('/jadwal',         [JadwalController::class, 'index'])->name('jadwal.index');
            Route::post('/jadwal/import', [JadwalController::class, 'import'])->name('jadwal.import');
            Route::get('/jadwal/template', [JadwalController::class, 'template'])->name('jadwal.template');
            Route::post('/jadwal/bulk-update', [JadwalController::class, 'bulkUpdate'])->name('jadwal.bulk-update');
            Route::post('/jadwal/bulk-delete', [JadwalController::class, 'bulkDelete'])->name('jadwal.bulk-delete');
            Route::post('/jadwal/tambah', [JadwalController::class, 'store'])->name('jadwal.store');
            Route::get('/kode-shift',     [KodeShiftController::class, 'index'])->name('kode-shift.index');
            Route::post('/kode-shift',                [KodeShiftController::class, 'store'])->name('kode-shift.store');
            Route::put('/kode-shift/{kodeShift}',     [KodeShiftController::class, 'update'])->name('kode-shift.update');
            Route::delete('/kode-shift/{kodeShift}',  [KodeShiftController::class, 'destroy'])->name('kode-shift.destroy');
            Route::get('/payroll-harian', [PayrollHarianController::class, 'index'])->name('payroll_harian.index');

            Route::middleware('role:SUPERADMIN,HRD,payroll_view')->group(function () {
                Route::get('/payroll',            [PayrollController::class, 'index'])->name('payroll.index');
                Route::get('/payroll/slip',       [PayrollController::class, 'slip'])->name('payroll.slip');
                Route::get('/payroll/slip-semua', [PayrollController::class, 'slipSemua'])->name('payroll.slip-semua');
                Route::get('/payroll/export', [PayrollController::class, 'export'])->name('payroll.export');
                Route::get('/payroll/template', [PayrollController::class, 'template'])->name('payroll.template');
                Route::post('/payroll/import',  [PayrollController::class, 'import'])->name('payroll.import');
                Route::get('/payroll-harian/rincian', [PayrollHarianController::class, 'rincian'])->name('payroll_harian.rincian');
                Route::get('/payroll-harian/export',  [PayrollHarianController::class, 'export'])->name('payroll_harian.export');
                Route::get('/payroll/multi-payroll',       [MultiPayrollController::class, 'index'])->name('payroll.multi.index');
                Route::get('/payroll/multi-payroll/excel', [MultiPayrollController::class, 'excel'])->name('payroll.multi.excel');
            });

            Route::middleware('role:SUPERADMIN,HRD')->group(function () {
                Route::post('/payroll/komponen',   [PayrollController::class, 'simpan'])->name('payroll.simpan');
                Route::post('/payroll/finalkan',   [PayrollController::class, 'finalkan'])->name('payroll.finalkan');
                Route::post('/payroll/buka-kunci', [PayrollController::class, 'bukaKunci'])->name('payroll.buka-kunci');
                Route::post('/payroll/dibayar',    [PayrollController::class, 'dibayar'])->name('payroll.dibayar');
                Route::post('/payroll/multi-payroll/unduh', [MultiPayrollController::class, 'unduh'])->name('payroll.multi.unduh');
            });
        });

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
        Route::get('pemakaian/{id}/edit', [PemakaianController::class, 'edit'])
            ->name('pemakaian.edit')
            ->middleware('role:pemakaian_edit,SUPERADMIN');
        Route::put('pemakaian/{id}', [PemakaianController::class, 'update'])
            ->name('pemakaian.update')
            ->middleware('role:pemakaian_edit,SUPERADMIN');
        Route::post('pemakaian/{id}/post', [PemakaianController::class, 'postPemakaian'])
            ->name('pemakaian.post')
            ->middleware('role:pemakaian_verify,SUPERADMIN');
        Route::delete('pemakaian/{id}', [PemakaianController::class, 'destroy'])
            ->name('pemakaian.destroy')
            ->middleware('role:pemakaian_delete,SUPERADMIN');
        Route::get('pemakaian/{id}/export-pdf', [PemakaianController::class, 'exportPdf'])
            ->name('pemakaian.export-pdf')
            ->middleware('role:pemakaian_view');
        Route::resource('pemakaian', PemakaianController::class)->only(['index', 'show'])->middleware('role:pemakaian_view');

        // Monitoring Persediaan: Lacak Stok, Kartu Stok & Rekapitulasi Persediaan (Berdasarkan izin 'stok_view')
        Route::get('stok', [StokController::class, 'index'])->name('stok.index')->middleware('role:stok_view');
        Route::get('stok/rekap', [StokController::class, 'rekap'])->name('stok.rekap')->middleware('role:stok_view');
        Route::get('stok/rekap/export-excel', [StokController::class, 'exportRekapExcel'])->name('stok.rekap.export-excel')->middleware('role:stok_view');
        Route::get('stok/rekap/export-pdf', [StokController::class, 'exportRekapPdf'])->name('stok.rekap.export-pdf')->middleware('role:stok_view');
        Route::get('stok/ledger', [StokController::class, 'ledger'])->name('stok.ledger')->middleware('role:stok_view');
        Route::get('stok/batch-detail/{batch_no}', [StokController::class, 'batchDetail'])->name('stok.batch-detail')->middleware('role:stok_view');

        // Penyesuaian Persediaan (Stock Adjustment / Opname)
        Route::get('adjustment/ajax-item-info', [AdjustmentController::class, 'ajaxItemInfo'])->name('adjustment.ajax-item-info')->middleware('role:stok_view,gudang');
        Route::get('adjustment/ajax-barang-by-gudang', [AdjustmentController::class, 'ajaxBarangByGudang'])->name('adjustment.ajax-barang-by-gudang')->middleware('role:stok_view,gudang');
        Route::get('adjustment', [AdjustmentController::class, 'index'])->name('adjustment.index')->middleware('role:stok_view,gudang');
        Route::get('adjustment/create', [AdjustmentController::class, 'create'])->name('adjustment.create')->middleware('role:stok_view,gudang');
        Route::post('adjustment', [AdjustmentController::class, 'store'])->name('adjustment.store')->middleware('role:stok_view,gudang');
        Route::get('adjustment/{id}', [AdjustmentController::class, 'show'])->name('adjustment.show')->middleware('role:stok_view,gudang');
        Route::post('adjustment/{id}/void', [AdjustmentController::class, 'void'])->name('adjustment.void')->middleware('role:stok_view,gudang');
    });

    // Produksi & HPP Harian (Sesuai Excel Asli PT Mirasa)
    Route::prefix('produksi')->name('produksi.')->middleware('role:produksi_view')->group(function () {
        Route::get('/', [ProduksiController::class, 'index'])->name('index');
        Route::get('/stok', [ProduksiStokController::class, 'index'])->name('stok.index');
        Route::get('/stok/ledger', [ProduksiStokController::class, 'ledger'])->name('stok.ledger');
        Route::get('/stok/batch-detail/{batch_no}', [ProduksiStokController::class, 'batchDetail'])->name('stok.batch-detail');
        Route::get('/rekap', [ProduksiController::class, 'rekap'])->name('rekap');
        Route::get('/export-hasil', [ProduksiController::class, 'exportHasilProduksi'])->name('export-hasil');
        Route::get('/download-template', [ProduksiController::class, 'downloadHasilTemplate'])->name('download-template');
        Route::post('/import-excel', [ProduksiController::class, 'importHasilProduksi'])->name('import-excel')->middleware('role:produksi_create');
        Route::get('/export-rekap-excel', [ProduksiController::class, 'exportRekapExcel'])->name('export-rekap-excel');
        Route::get('/export-rekap-pdf', [ProduksiController::class, 'exportRekapPdf'])->name('export-rekap-pdf');
        Route::get('/download-rekap-template', [ProduksiController::class, 'downloadRekapTemplate'])->name('download-rekap-template');
        Route::post('/import-rekap-excel', [ProduksiController::class, 'importRekapExcel'])->name('import-rekap-excel')->middleware('role:produksi_create');
        Route::get('/adjust-utilitas', [ProduksiController::class, 'adjustUtilitasPage'])->name('adjust-utilitas');
        Route::post('/adjust-utilitas', [ProduksiController::class, 'adjustUtilitas'])->name('adjust-utilitas.store')->middleware('role:produksi_create');
        Route::get('/create', [ProduksiController::class, 'create'])->name('create')->middleware('role:produksi_create');
        Route::post('/', [ProduksiController::class, 'store'])->name('store')->middleware('role:produksi_create');
        Route::get('/pakai-data/{pakaiId}', [ProduksiController::class, 'getPakaiData'])->name('pakai-data');
        Route::get('/next-karton', [ProduksiController::class, 'getNextKarton'])->name('next-karton');
        Route::get('/sync-karyawan', [ProduksiController::class, 'getKaryawanDataByDate'])->name('sync-karyawan');
        Route::get('/{id}', [ProduksiController::class, 'show'])->name('show')->whereNumber('id');
        Route::get('/{id}/adjust', [ProduksiController::class, 'adjustPage'])->name('adjust')->middleware('role:produksi_create')->whereNumber('id');
        Route::post('/{id}/adjust-hpp', [ProduksiController::class, 'adjustHpp'])->name('adjust-hpp')->middleware('role:produksi_create')->whereNumber('id');
        Route::get('/{id}/cetak-stiker', [ProduksiController::class, 'cetakStiker'])->name('cetak-stiker')->whereNumber('id');
        Route::delete('/{id}', [ProduksiController::class, 'destroy'])->name('destroy')->middleware('role:produksi_create')->whereNumber('id');

        // Pengemasan Barang Jadi & HPP (Packaging)
        Route::get('/packaging/ajax-bom-preview', [PackagingController::class, 'ajaxBomPreview'])->name('packaging.ajax-bom-preview');
        Route::get('/packaging/ajax-produksi-details', [PackagingController::class, 'ajaxProduksiDetails'])->name('packaging.ajax-produksi-details');
        Route::get('/packaging/ajax-pakai-details', [PackagingController::class, 'ajaxPakaiDetails'])->name('packaging.ajax-pakai-details');
        Route::get('/packaging', [PackagingController::class, 'index'])->name('packaging.index');
        Route::get('/packaging/rekap', [PackagingController::class, 'rekap'])->name('packaging.rekap');
        Route::get('/packaging/export-rekap-excel', [PackagingController::class, 'exportRekapExcel'])->name('packaging.export-rekap-excel');
        Route::get('/packaging/export-rekap-pdf', [PackagingController::class, 'exportRekapPdf'])->name('packaging.export-rekap-pdf');
        Route::get('/packaging/catatan-operator', [PackagingController::class, 'catatanOperator'])->name('packaging.catatan-operator');
        Route::get('/packaging/catatan-operator/{id}', [PackagingController::class, 'showCatatanOperator'])->name('packaging.catatan-operator.show')->whereNumber('id');
        Route::get('/packaging/create', [PackagingController::class, 'create'])->name('packaging.create')->middleware('role:produksi_create');
        Route::post('/packaging', [PackagingController::class, 'store'])->name('packaging.store')->middleware('role:produksi_create');
        Route::get('/packaging/{id}', [PackagingController::class, 'show'])->name('packaging.show')->whereNumber('id');
        Route::get('/packaging/{id}/adjust', [PackagingController::class, 'adjustPage'])->name('packaging.adjust')->middleware('role:produksi_create')->whereNumber('id');
        Route::post('/packaging/{id}/adjust-hpp', [PackagingController::class, 'adjustHpp'])->name('packaging.adjust-hpp')->middleware('role:produksi_create')->whereNumber('id');
        Route::post('/packaging/{id}/void', [PackagingController::class, 'void'])->name('packaging.void')->middleware('role:produksi_create')->whereNumber('id');
    });

    // Laporan Pengemasan Lapangan (Mobile-First Operator HP & Admin Office)
    Route::prefix('produksi')->name('produksi.')->group(function () {
        Route::get('/packaging/mobile', [PackagingOperatorController::class, 'create'])->name('packaging.mobile');
        Route::post('/packaging/mobile', [PackagingOperatorController::class, 'store'])->name('packaging.mobile.store');
        Route::get('/packaging/mobile/riwayat', [PackagingOperatorController::class, 'index'])->name('packaging.mobile.index');
        Route::get('/packaging/mobile/{id}', [PackagingOperatorController::class, 'show'])->name('packaging.mobile.show')->whereNumber('id');
        Route::get('/packaging/ajax-unprocessed-laporan', [PackagingOperatorController::class, 'ajaxUnprocessed'])->name('packaging.ajax-unprocessed-laporan');
    });

    // Quality Control (QC) Inbound Bahan Baku (Mobile-First / Google Form Style)
    Route::prefix('qc')->name('qc.')->group(function () {
        Route::get('inbound/siap-gudang', [QcInboundController::class, 'getSiapGudang'])->name('inbound.siap_gudang');
        Route::get('inbound/ticket-data/{id}', [QcInboundController::class, 'getTicketData'])->name('inbound.ticket_data');
        Route::get('inbound/pengujian-2', [QcInboundController::class, 'createPengujian2'])->name('inbound.create_pengujian_2')->middleware('role:qc_mobile_create,qc_create');
        Route::post('inbound/pengujian-2', [QcInboundController::class, 'storePengujian2'])->name('inbound.store_pengujian_2')->middleware('role:qc_mobile_create,qc_create');
        Route::get('inbound/create', [QcInboundController::class, 'create'])->name('inbound.create')->middleware('role:qc_mobile_create,qc_create');
        Route::post('inbound', [QcInboundController::class, 'store'])->name('inbound.store')->middleware('role:qc_mobile_create,qc_create');
        Route::get('inbound/{id}/edit', [QcInboundController::class, 'edit'])->name('inbound.edit')->middleware('role:qc_mobile_edit,qc_web_edit,qc_edit');
        Route::put('inbound/{id}', [QcInboundController::class, 'update'])->name('inbound.update')->middleware('role:qc_mobile_edit,qc_web_edit,qc_edit');
        Route::delete('inbound/{id}', [QcInboundController::class, 'destroy'])->name('inbound.destroy')->middleware('role:qc_mobile_delete,qc_web_delete,qc_delete');
        Route::get('inbound/{id}/berita-acara', [QcInboundController::class, 'beritaAcara'])->name('inbound.berita_acara')->middleware('role:qc_web_view,qc_view');
        Route::post('inbound/{id}/uji-goreng', [QcInboundController::class, 'updateUjiGoreng'])->name('inbound.update_uji_goreng')->middleware('role:qc_mobile_create,qc_create');
        Route::resource('inbound', QcInboundController::class)->only(['index', 'show'])->names('inbound')->middleware('role:qc_mobile_view,qc_web_view,qc_view');
    });

    // Modul Transaksi Penjualan (PO Penjualan / Sales Order)
    Route::prefix('penjualan')->name('penjualan.')->group(function () {
        Route::get('so/{id}/export-faktur', [SoController::class, 'exportFakturPdf'])->name('so.export-faktur');
        Route::get('so/{id}/export-surat-jalan', [SoController::class, 'exportSuratJalanPdf'])->name('so.export-surat-jalan');
        Route::post('so/{id}/confirm-delivery', [SoController::class, 'confirmDelivery'])->name('so.confirm-delivery');
        Route::post('so/{id}/void-delivery', [SoController::class, 'voidDelivery'])->name('so.void-delivery');
        Route::post('so/{id}/record-payment', [SoController::class, 'recordPayment'])->name('so.record-payment');
        Route::post('so/{id}/retur', [SoController::class, 'storeReturn'])->name('so.retur');
        Route::post('so/retur/{returId}/void', [SoController::class, 'voidReturn'])->name('so.retur-void');
        Route::post('so/{id}/cancel', [SoController::class, 'cancel'])->name('so.cancel');
        Route::get('so/ajax/stock-check', [SoController::class, 'ajaxStockCheck'])->name('so.ajax.stock-check');
        Route::get('so/ajax/generate-faktur-no', [SoController::class, 'ajaxGenerateFakturNo'])->name('so.ajax.generate-faktur-no');
        Route::resource('so', SoController::class);

        // Modul Pengeluaran Barang / Surat Jalan (Gudang FG - Outbound Delivery)
        Route::get('pengeluaran/ajax-so-detail/{id}', [PengeluaranBarangController::class, 'ajaxSoDetail'])->name('pengeluaran.ajax-so-detail');
        Route::get('pengeluaran/{id}/export-pdf', [PengeluaranBarangController::class, 'exportPdf'])->name('pengeluaran.export-pdf');
        Route::post('pengeluaran/{id}/void', [PengeluaranBarangController::class, 'void'])->name('pengeluaran.void');
        Route::resource('pengeluaran', PengeluaranBarangController::class)->except(['edit', 'update', 'destroy']);

        // Modul Faktur Penjualan (Commercial Invoice & AR Recognition)
        Route::get('faktur/ajax-surat-jalan-detail/{id}', [FakturPenjualanController::class, 'ajaxSuratJalanDetail'])->name('faktur.ajax-surat-jalan-detail');
        Route::get('faktur/{id}/export-pdf', [FakturPenjualanController::class, 'exportPdf'])->name('faktur.export-pdf');
        Route::post('faktur/{id}/void', [FakturPenjualanController::class, 'void'])->name('faktur.void');
        Route::resource('faktur', FakturPenjualanController::class)->except(['edit', 'update', 'destroy']);

        // Rekapitulasi Terpusat Retur Penjualan (Customer Returns)
        Route::get('retur', [SoReturController::class, 'index'])->name('retur.index');
        Route::get('retur/{id}', [SoReturController::class, 'show'])->name('retur.show');
        Route::get('retur/{id}/export-pdf', [SoReturController::class, 'exportPdf'])->name('retur.export-pdf');
        Route::post('retur/{id}/void', [SoReturController::class, 'void'])->name('retur.void');
    });

    // Modul Keuangan: Hutang Usaha (AP), Pelunasan Kas Keluar & Rekening Bank
    Route::prefix('keuangan')->name('keuangan.')->group(function () {
        // 1. Hutang Usaha & Tagihan Pembelian (AP)
        Route::get('hutang/ajax-unbilled-grn', [HutangController::class, 'ajaxUnbilledGrn'])->name('hutang.ajax-unbilled-grn')->middleware('role:hutang_view,hutang_create');
        Route::get('hutang/ajax-gas-summary', [HutangController::class, 'ajaxGasSummary'])->name('hutang.ajax-gas-summary')->middleware('role:hutang_view,hutang_create');
        Route::get('hutang/create-gas', [HutangController::class, 'createGas'])->name('hutang.create_gas')->middleware('role:hutang_create');
        Route::post('hutang/store-gas', [HutangController::class, 'storeGas'])->name('hutang.store_gas')->middleware('role:hutang_create');
        Route::patch('hutang/{id}/update-tempo', [HutangController::class, 'updateTempo'])->name('hutang.update-tempo')->middleware('role:hutang_create');
        Route::resource('hutang', HutangController::class)->except(['edit', 'update']);

        // 2. Pelunasan Hutang (Bukti Kas Keluar - BKK)
        Route::post('bayar/{id}/void', [BayarHutangController::class, 'void'])->name('bayar.void')->middleware('role:bayar_hutang_void');
        Route::resource('bayar', BayarHutangController::class)->only(['index', 'create', 'store', 'show']);

        // 3. Kas & Rekening Bank
        Route::get('kas-bank/mutasi', [KasBankController::class, 'mutasi'])->name('kas_bank.mutasi')->middleware('role:kas_bank_view');
        Route::get('kas-bank', [KasBankController::class, 'index'])->name('kas_bank.index')->middleware('role:kas_bank_view');
        Route::post('kas-bank', [KasBankController::class, 'store'])->name('kas_bank.store')->middleware('role:kas_bank_manage');
        Route::put('kas-bank/{id}', [KasBankController::class, 'update'])->name('kas_bank.update')->middleware('role:kas_bank_manage');
        Route::delete('kas-bank/{id}', [KasBankController::class, 'destroy'])->name('kas_bank.destroy')->middleware('role:kas_bank_manage');
        Route::post('kas-bank/transfer', [KasBankController::class, 'transfer'])->name('kas_bank.transfer')->middleware('role:kas_bank_manage');
        Route::post('kas-bank/penyesuaian', [KasBankController::class, 'penyesuaian'])->name('kas_bank.penyesuaian')->middleware('role:kas_bank_manage');

        // 4. Penerimaan Kas & Bank (Bukti Kas Masuk - BKM)
        Route::post('kas-masuk/{id}/void', [KasMasukController::class, 'void'])->name('kas_masuk.void')->middleware('role:kas_masuk_void');
        Route::resource('kas-masuk', KasMasukController::class)->names('kas_masuk')->only(['index', 'create', 'store', 'show']);

        // 5. Bagan Akun (Chart of Accounts / COA)
        Route::resource('coa', CoaController::class)->except(['create', 'show', 'edit']);

        // 6. Jurnal Umum (General Journal Ledger)
        Route::resource('jurnal', JurnalController::class)->only(['index', 'show'])->middleware('role:jurnal_view,coa_view,hutang_view,kas_bank_view');

        // 7. Buku Besar Interaktif (Interactive General Ledger)
        Route::get('buku-besar', [BukuBesarController::class, 'index'])
            ->name('buku_besar.index')
            ->middleware('role:jurnal_view,coa_view,hutang_view,kas_bank_view');

        // 8. Verifikasi Pemakaian Bahan & Terbitkan Jurnal HPP
        Route::get('verifikasi-pemakaian', [VerifikasiPemakaianController::class, 'index'])
            ->name('verifikasi_pemakaian.index')
            ->middleware('role:jurnal_view,coa_view,hutang_view,kas_bank_view');
        Route::get('verifikasi-pemakaian/{id}', [VerifikasiPemakaianController::class, 'show'])
            ->name('verifikasi_pemakaian.show')
            ->middleware('role:jurnal_view,coa_view,hutang_view,kas_bank_view');
        Route::get('verifikasi-pemakaian/{id}/edit', [VerifikasiPemakaianController::class, 'edit'])
            ->name('verifikasi_pemakaian.edit')
            ->middleware('role:jurnal_view,coa_view,hutang_view,kas_bank_view');
        Route::put('verifikasi-pemakaian/{id}', [VerifikasiPemakaianController::class, 'update'])
            ->name('verifikasi_pemakaian.update')
            ->middleware('role:jurnal_view,coa_view,hutang_view,kas_bank_view');
        Route::post('verifikasi-pemakaian/{id}/approve', [VerifikasiPemakaianController::class, 'approve'])
            ->name('verifikasi_pemakaian.approve')
            ->middleware('role:jurnal_view,coa_view,hutang_view,kas_bank_view');
        Route::post('verifikasi-pemakaian/{id}/reject', [VerifikasiPemakaianController::class, 'reject'])
            ->name('verifikasi_pemakaian.reject')
            ->middleware('role:jurnal_view,coa_view,hutang_view,kas_bank_view');
    });
});
