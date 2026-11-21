@extends('layouts.app')

@section('title', 'Pengeluaran Sampel / Promosi (Non-PO) - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/pengeluaran/pengeluaran-form.css') }}?v={{ filemtime(public_path('css/penjualan/pengeluaran/pengeluaran-form.css')) }}">
@endpush

@section('content')
<div class="sj-form-container">

    {{-- PAGE HEADER --}}
    <div class="sj-page-header">
        <a href="{{ route('penjualan.pengeluaran.index') }}" class="sj-back-link">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Pengeluaran Barang
        </a>
        <div class="sj-title-row">
            <h1 class="sj-page-title">Pengeluaran Sampel &amp; Promosi</h1>
            <span class="badge" style="background: #faf5ff; color: #7c3aed; font-weight: 700; font-size: 0.75rem; padding: 4px 10px; border-radius: 6px; border: 1px solid #e9d5ff;">
                Non-Komersial (Rp 0)
            </span>
        </div>
        <p class="sj-page-subtitle">
            Penerbitan Surat Jalan resmi dan pemotongan stok Gudang FG untuk sampel calon buyer, pameran, promosi, atau hadiah relasi tanpa dokumen PO Penjualan.
        </p>
    </div>

    @if (isset($errors) && $errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; color: #991b1b;">
            <div style="font-weight: 700; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Terdapat kesalahan pengisian data pengeluaran sampel:
            </div>
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.825rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="sampleCreateForm" method="POST" action="{{ route('penjualan.pengeluaran.store-sample') }}">
        @csrf

        {{-- 2 KOLOM KARTU HEADER --}}
        <div class="sj-grid-2-col">
            
            {{-- KARTU 1: IDENTITAS PENERIMA & TUJUAN --}}
            <div class="sj-card">
                <div class="sj-card-header">
                    <div class="sj-card-header-left">
                        <span class="sj-card-icon" style="background: #faf5ff; color: #7c3aed;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </span>
                        <div>
                            <h3 class="sj-card-title">Penerima &amp; Keperluan Sampel</h3>
                            <div class="sj-card-subtitle">Tujuan pengeluaran barang non-komersial</div>
                        </div>
                    </div>
                </div>

                <div class="sj-card-body">
                    {{-- Jenis Pengeluaran --}}
                    <div class="sj-form-group">
                        <label for="tipePengeluaran" class="sj-form-label">
                            Jenis Pengeluaran Sampel <span class="req">*</span>
                        </label>
                        <select name="tipe_pengeluaran_cd" id="tipePengeluaran" required class="sj-form-select">
                            <option value="SAMPLE" selected>Sampel Uji Coba Calon Buyer / Lab</option>
                            <option value="PROMOSI">Promosi / Pameran / Event</option>
                            <option value="HADIAH">Hadiah Relasi / CSR / Bingkisan</option>
                            <option value="INTERNAL">Pemakaian Internal Pabrik Lainnya</option>
                        </select>
                    </div>

                    {{-- Customer Terdaftar (Opsional) --}}
                    <div class="sj-form-group">
                        <label for="customerSelect" class="sj-form-label">
                            Pilih Mitra / Customer Terdaftar (Opsional)
                        </label>
                        <select name="customer_id" id="customerSelect" class="sj-form-select">
                            <option value="">-- Bukan Customer Terdaftar (Input Manual) --</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->customer_id }}" 
                                        data-name="{{ $c->customer_nm }}" 
                                        data-alamat="{{ $c->alamat_txt ?: $c->wilayah }}"
                                        {{ old('customer_id') == $c->customer_id ? 'selected' : '' }}>
                                    {{ $c->customer_nm }} ({{ $c->customer_cd ?: 'Manual' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Nama Penerima --}}
                    <div class="sj-form-group">
                        <label for="penerimaInput" class="sj-form-label">
                            Nama Penerima / Instansi / Calon Buyer <span class="req">*</span>
                        </label>
                        <input type="text" name="penerima_nm" id="penerimaInput" 
                               value="{{ old('penerima_nm') }}" 
                               placeholder="Contoh: PT Boga Sejahtera (Up. Ibu Maya) / Lab Sucofindo..." 
                               required class="sj-form-input">
                    </div>

                    {{-- Keperluan / Alasan --}}
                    <div class="sj-form-group">
                        <label for="keperluanInput" class="sj-form-label">
                            Keperluan / Keterangan Pengeluaran <span class="req">*</span>
                        </label>
                        <input type="text" name="keperluan_txt" id="keperluanInput" 
                               value="{{ old('keperluan_txt', 'Uji Rasa Calon Distributor') }}" 
                               placeholder="Contoh: Uji organoleptik calon distributor Jawa Barat..." 
                               required class="sj-form-input">
                    </div>

                    {{-- Disetujui / Otorisasi --}}
                    <div class="sj-form-group">
                        <label for="otorisasiInput" class="sj-form-label">
                            Disetujui / Diinstruksikan Oleh
                        </label>
                        <input type="text" name="otorisasi_oleh" id="otorisasiInput" 
                               value="{{ old('otorisasi_oleh', auth()->user()?->nama_lengkap ?? 'Manajer Sales') }}" 
                               placeholder="Nama manajer atau pejabat yang menyetujui..." 
                               class="sj-form-input">
                    </div>
                </div>
            </div>

            {{-- KARTU 2: DOKUMEN SURAT JALAN & LOGISTIK --}}
            <div class="sj-card">
                <div class="sj-card-header">
                    <div class="sj-card-header-left">
                        <span class="sj-card-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </span>
                        <div>
                            <h3 class="sj-card-title">Surat Jalan &amp; Pengiriman</h3>
                            <div class="sj-card-subtitle">Administrasi fisik pengeluaran gudang</div>
                        </div>
                    </div>
                </div>

                <div class="sj-card-body">
                    <div class="sj-form-group">
                        <label for="pengeluaranNo" class="sj-form-label">
                            Nomor Surat Jalan Resmi <span class="req">*</span>
                        </label>
                        <input type="text" name="pengeluaran_no" id="pengeluaranNo" 
                               value="{{ old('pengeluaran_no', $defaultSjNo) }}" 
                               required class="sj-form-input" style="font-weight: 700; color: #0f172a; font-family: monospace;">
                    </div>

                    <div class="sj-form-group">
                        <label for="pengeluaranTgl" class="sj-form-label">
                            Tanggal Pengeluaran Fisik <span class="req">*</span>
                        </label>
                        <input type="date" name="pengeluaran_tgl" id="pengeluaranTgl" 
                               value="{{ old('pengeluaran_tgl', now()->toDateString()) }}" 
                               required class="sj-form-input">
                    </div>

                    <div class="sj-grid-2-col" style="margin-bottom: 0;">
                        <div class="sj-form-group">
                            <label for="supirNm" class="sj-form-label">Nama Pengemudi / Pembawa</label>
                            <input type="text" name="supir_nm" id="supirNm" 
                                   value="{{ old('supir_nm') }}" 
                                   placeholder="Contoh: Kurir / Mandiri / Supir Pabrik" class="sj-form-input">
                        </div>

                        <div class="sj-form-group">
                            <label for="nopolKendaraan" class="sj-form-label">Plat Nomor / Ekspedisi</label>
                            <input type="text" name="nopol_kendaraan" id="nopolKendaraan" 
                                   value="{{ old('nopol_kendaraan') }}" 
                                   placeholder="Contoh: AA 1234 XX / JNE" class="sj-form-input" style="font-family: monospace;">
                        </div>
                    </div>

                    <div class="sj-form-group">
                        <label for="alamatTujuanInput" class="sj-form-label">Alamat / Lokasi Tujuan Pengiriman</label>
                        <input type="text" name="catatan_txt" id="alamatTujuanInput" 
                               value="{{ old('catatan_txt') }}" 
                               placeholder="Alamat lengkap atau catatan pengiriman..." class="sj-form-input">
                    </div>
                </div>
            </div>

        </div>

        {{-- KARTU 3: RINCIAN PRODUK JADI & ALOKASI BATCH FEFO --}}
        <div class="sj-card">
            <div class="sj-card-header" style="justify-content: space-between;">
                <div class="sj-card-header-left">
                    <span class="sj-card-icon" style="background: #ecfdf5; color: #047857;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </span>
                    <div>
                        <h3 class="sj-card-title">Rincian Barang Jadi yang Dikeluarkan</h3>
                        <div class="sj-card-subtitle">Pilih produk jadi berstok dan tentukan batch fisik FEFO</div>
                    </div>
                </div>
                <div>
                    <button type="button" id="btnAddSampleRow" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 700;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Tambah Baris Barang</span>
                    </button>
                </div>
            </div>

            <div class="sj-table-responsive">
                <table class="sj-table">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No</th>
                            <th style="min-width: 250px;">Pilih Produk Jadi (Gudang FG)</th>
                            <th style="min-width: 240px;">Nomor Batch Fisik (FEFO)</th>
                            <th style="width: 130px; text-align: center;">Stok Tersedia</th>
                            <th style="width: 140px;">Kuantitas Keluar</th>
                            <th style="min-width: 160px;">Catatan Baris</th>
                            <th style="width: 60px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="sampleItemsBody">
                        {{-- Diisi secara dinamis via JavaScript --}}
                    </tbody>
                    <tfoot>
                        <tr style="background: #f8fafc; font-weight: 700;">
                            <td colspan="4" style="text-align: right; padding: 0.75rem 1rem;">
                                Total Kuantitas Sampel yang Dikeluarkan:
                            </td>
                            <td colspan="3" style="padding: 0.75rem 1rem; color: #0f172a; font-size: 0.95rem;">
                                <span id="totalSampleKartonText">0</span> <span style="font-size: 0.8rem; font-weight: 600; color: #64748b;">Karton</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="sj-action-bar">
                <div style="font-size: 0.8125rem; color: #64748b; display: flex; align-items: center; gap: 0.4rem;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Stok fisik Gudang FG akan otomatis dipotong via metode FEFO tanpa membuat tagihan piutang komersial.</span>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="{{ route('penjualan.pengeluaran.index') }}" class="btn btn-secondary">
                        Batal
                    </a>
                    <button type="submit" id="btnSubmitSample" class="btn btn-primary" style="font-weight: 700; background: #059669; border-color: #047857;">
                        Terbitkan Surat Jalan Sampel &amp; Potong Stok
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
    window.sampleProducts = @json($availableProducts);
</script>
<script src="{{ asset('js/penjualan/pengeluaran/pengeluaran-sample.js') }}?v={{ filemtime(public_path('js/penjualan/pengeluaran/pengeluaran-sample.js')) }}"></script>
@endpush
