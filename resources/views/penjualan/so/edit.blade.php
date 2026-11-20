@extends('layouts.app')

@section('title', 'Edit Dokumen PO Penjualan ' . $order->so_no . ' - ERP PT Mirasa')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/so/so-form.css') }}">
@endpush

<div class="so-form-container">
    {{-- PAGE HEADER --}}
    <div class="so-page-header">
        <a href="{{ route('penjualan.so.show', $order->so_id) }}" class="so-back-link">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Detail Dokumen
        </a>
        <div class="so-title-row">
            <h1 class="so-page-title">Edit Dokumen PO Penjualan: {{ $order->so_no }}</h1>
            @if ($order->status_cd == 'COMPLETED')
                <span class="badge badge-success">Selesai (100%)</span>
            @elseif ($order->status_cd == 'PARTIAL')
                <span class="badge badge-info">Sebagian Kirim</span>
            @elseif ($order->status_cd == 'PROCESSING')
                <span class="badge" style="background:#e0f2fe; color:#0369a1;">Diproses</span>
            @elseif ($order->status_cd == 'APPROVED')
                <span class="badge" style="background:#dbeafe; color:#1d4ed8;">Disetujui</span>
            @elseif ($order->status_cd == 'DRAFT')
                <span class="badge" style="background:#f1f5f9; color:#475569;">Draft</span>
            @else
                <span class="badge" style="background:#fee2e2; color:#b91c1c;">Dibatalkan</span>
            @endif
        </div>
        <p class="so-page-subtitle">
            Perbarui rincian produk yang dipesan, harga satuan komersial, diskon, atau potongan sebelum barang dikirim.
        </p>
    </div>

    @if (isset($errors) && $errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; color: #991b1b;">
            <div style="font-weight: 700; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Terdapat kesalahan pengisian data pesanan:
            </div>
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.825rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('penjualan.so.update', $order->so_id) }}" id="formPo">
        @csrf
        @method('PUT')

        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            
            {{-- BAGIAN 1: GRID DUA KOLOM (DATA PELANGGAN & PARAMETER DOKUMEN) --}}
            <div class="so-header-grid">
                
                {{-- KARTU 1A: DATA PELANGGAN & PEMESAN --}}
                <div class="so-card">
                    <div class="so-card-header">
                        <div class="so-card-header-left">
                            <span class="so-card-icon">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </span>
                            <div>
                                <h3 class="so-card-title">Data Pelanggan &amp; Pemesan</h3>
                                <div class="so-card-subtitle">Identitas mitra pembeli dan referensi pesanan</div>
                            </div>
                        </div>
                        <div id="badgeKlasifikasiContainer" style="display: flex; align-items: center; gap: 0.4rem;">
                            <span id="badgeKlasifikasi" style="display: inline-block; background: #f1f5f9; color: #475569; font-weight: 700; font-size: 0.725rem; padding: 3px 8px; border-radius: 4px;">
                                Belum Memilih Customer
                            </span>
                            <span id="textWilayah" style="font-size: 0.75rem; color: #64748b; font-weight: 600;">-</span>
                        </div>
                    </div>

                    <div class="so-card-body">
                        {{-- Pilih Pelanggan --}}
                        <div class="so-form-group">
                            <label for="customerSelect" class="so-form-label">
                                <span>Pilih Pelanggan / Customer <span class="req">*</span></span>
                            </label>
                            <select name="customer_id" id="customerSelect" required class="form-control">
                                <option value="">-- Pilih Mitra Pelanggan --</option>
                                @foreach($customers as $cust)
                                    <option value="{{ $cust->customer_id }}"
                                            data-code="{{ $cust->customer_cd }}"
                                            data-contact="{{ $cust->kontak_no }}"
                                            data-address="{{ $cust->alamat_txt }}"
                                            data-klasifikasi="{{ $cust->klasifikasi }}"
                                            data-wilayah="{{ $cust->wilayah }}"
                                            data-kode-pelanggan="{{ $cust->kode_pelanggan }}"
                                            data-top="{{ $cust->top_txt }}"
                                            data-default-ppn="{{ $cust->default_ppn }}"
                                            {{ old('customer_id', $order->customer_id) == $cust->customer_id ? 'selected' : '' }}>
                                        {{ $cust->customer_nm }} ({{ $cust->customer_cd }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Grid: Kode Pelanggan & Syarat Pembayaran (TOP) --}}
                        <div class="so-grid-2">
                            <div class="so-form-group">
                                <label for="customerCdInput" class="so-form-label">
                                    <span>Kode Pelanggan</span>
                                    <span class="so-label-badge primary">Auto</span>
                                </label>
                                <input type="text" id="customerCdInput" readonly class="form-control input-customer-code" value="{{ $order->customer?->customer_cd }}" placeholder="Pilih customer...">
                            </div>

                            <div class="so-form-group">
                                <label for="top_txt" class="so-form-label">
                                    <span>Syarat Pembayaran (TOP)</span>
                                </label>
                                <input type="text" id="top_txt" name="top_txt" value="{{ old('top_txt', $order->top_txt ?: $order->customer?->top_txt) }}" placeholder="Contoh: 7 Hari / 1 Nota" class="form-control" style="font-weight: 600;">
                                <small class="so-help-text">Default master (dapat disesuaikan)</small>
                            </div>
                        </div>

                        {{-- No. Referensi PO / Nota Sales --}}
                        <div class="so-form-group">
                            <label for="customer_po_no" id="lblCustomerPoNo" class="so-form-label">
                                <span>No. Referensi PO / Nota</span>
                            </label>
                            <input type="text" id="customer_po_no" name="customer_po_no" value="{{ old('customer_po_no', $order->customer_po_no) }}" placeholder="Contoh: 450123 / J.02.03 / PO Mart..." class="form-control" style="font-weight: 600; font-family: monospace;">
                            <small id="helpCustomerPoNo" class="so-help-text">Untuk IFM: No PO | Untuk Manual: No Nota/Sales | Untuk Modern Trade: PO Mart</small>
                        </div>

                        {{-- Customer Info Preview Box --}}
                        <div id="customerInfoBox" class="so-customer-preview">
                            <div class="so-preview-grid">
                                <div>
                                    <span class="so-preview-label">Alamat Pengiriman</span>
                                    <div class="so-preview-value" id="custAddressText">{{ $order->customer?->alamat_txt ?: '-' }}</div>
                                </div>
                                <div>
                                    <span class="so-preview-label">Kontak / Telepon</span>
                                    <div class="so-preview-value" id="custContactText">{{ $order->customer?->kontak_no ?: '-' }}</div>
                                </div>
                                <div>
                                    <span class="so-preview-label">Ref. Mentor / Wilayah</span>
                                    <div class="so-preview-value" id="custRefText">{{ ($order->customer?->kode_pelanggan ?: '-') . ' (' . ($order->customer?->wilayah ?: '-') . ')' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KARTU 1B: PARAMETER DOKUMEN & PERPAJAKAN --}}
                <div class="so-card">
                    <div class="so-card-header">
                        <div class="so-card-header-left">
                            <span class="so-card-icon emerald">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            <div>
                                <h3 class="so-card-title">Parameter Dokumen &amp; Pajak</h3>
                                <div class="so-card-subtitle">Nomor identifikasi, tanggal transaksi, &amp; PPN</div>
                            </div>
                        </div>
                    </div>

                    <div class="so-card-body">
                        {{-- Grid: Nomor Pesanan (SO) & Nomor Faktur --}}
                        <div class="so-grid-2">
                            <div class="so-form-group">
                                <label for="so_no" class="so-form-label">
                                    <span>Nomor Pesanan (SO)</span>
                                    <span class="so-label-badge primary">Otomatis</span>
                                </label>
                                <input type="text" id="so_no" name="so_no" value="{{ $order->so_no }}" readonly class="form-control input-so-no">
                                <small class="so-help-text">Nomor identifikasi unik pesanan</small>
                            </div>

                            <div class="so-form-group">
                                <label for="faktur_no" class="so-form-label">
                                    <span>Nomor Faktur Penjualan</span>
                                    <span id="badgeFakturFormat" class="so-label-badge success">Auto</span>
                                </label>
                                <input type="text" id="faktur_no" name="faktur_no" value="{{ old('faktur_no', $order->faktur_no) }}" class="form-control input-faktur-no" required>
                                <small id="helpFakturFormat" class="so-help-text">Format otomatis sesuai kategori &amp; PPN</small>
                            </div>
                        </div>

                        {{-- Grid: Tanggal Pesanan & Estimasi Kirim --}}
                        <div class="so-grid-2">
                            <div class="so-form-group">
                                <label for="so_tgl" class="so-form-label">
                                    <span>Tanggal Pesanan <span class="req">*</span></span>
                                </label>
                                <input type="date" id="so_tgl" name="so_tgl" value="{{ old('so_tgl', $order->so_tgl?->format('Y-m-d')) }}" class="form-control" required>
                            </div>

                            <div class="so-form-group">
                                <label for="tgl_kirim_estimasi" class="so-form-label">
                                    <span>Estimasi Pengiriman</span>
                                </label>
                                <input type="date" id="tgl_kirim_estimasi" name="tgl_kirim_estimasi" value="{{ old('tgl_kirim_estimasi', $order->tgl_kirim_estimasi?->format('Y-m-d')) }}" class="form-control">
                            </div>
                        </div>

                        {{-- Status PPN Faktur --}}
                        <div class="so-form-group">
                            <label for="ppnTipeHeader" class="so-form-label">
                                <span>Pajak Faktur (PPN)</span>
                            </label>
                            <select name="ppn_tipe_header" id="ppnTipeHeader" class="form-control" style="font-weight: 600;">
                                <option value="PPN_11" {{ old('ppn_tipe_header', $order->ppn_tipe === 'PPN_11' ? 'PPN_11' : ($order->ppn_nominal > 0 ? 'PPN_11' : 'NON_PPN')) === 'PPN_11' ? 'selected' : '' }}>Ber-PPN (11%)</option>
                                <option value="NON_PPN" {{ old('ppn_tipe_header', $order->ppn_tipe === 'PPN_11' ? 'PPN_11' : ($order->ppn_nominal > 0 ? 'PPN_11' : 'NON_PPN')) === 'NON_PPN' ? 'selected' : '' }}>Non-PPN</option>
                            </select>
                            <small class="so-help-text">Menentukan format seri faktur (PM.A / PM.B) dan tarif pajak per baris produk</small>
                        </div>

                        {{-- Grid: Parameter Armada Pengiriman (Supir & Nopol Truk) --}}
                        <div class="so-grid-2">
                            <div class="so-form-group">
                                <label for="supir_nm" class="so-form-label">
                                    <span>Nama Supir / Pengemudi</span>
                                    <span class="so-label-badge success">Armada</span>
                                </label>
                                <input type="text" id="supir_nm" name="supir_nm" value="{{ old('supir_nm', $order->supir_nm) }}" placeholder="Contoh: Pak Slamet / Budi..." class="form-control">
                                <small class="so-help-text">Diteruskan otomatis ke Surat Jalan Gudang</small>
                            </div>

                            <div class="so-form-group">
                                <label for="nopol_kendaraan" class="so-form-label">
                                    <span>No. Polisi Armada Truk</span>
                                </label>
                                <input type="text" id="nopol_kendaraan" name="nopol_kendaraan" value="{{ old('nopol_kendaraan', $order->nopol_kendaraan) }}" placeholder="Contoh: AD 8234 KL" class="form-control" style="text-transform: uppercase; font-family: monospace;">
                                <small class="so-help-text">Plat armada yang ditugaskan</small>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- KARTU 2: TABEL INPUTAN PRODUK HASIL PRODUKSI --}}
            <div class="so-card">
                <div class="so-card-header">
                    <div class="so-card-header-left">
                        <span class="so-card-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </span>
                        <div>
                            <h3 class="so-card-title">Rincian Produk Pesanan</h3>
                            <div class="so-card-subtitle">Daftar produk hasil produksi yang dipesan (Barang Jadi / Setengah Jadi)</div>
                        </div>
                    </div>
                    <button type="button" id="btnAddRow" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 700; background: #f8fafc; border: 1.5px solid #cbd5e1; color: #0284c7;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Tambah Baris Barang</span>
                    </button>
                </div>

                <div class="so-table-wrapper">
                    <table class="excel-grid-table" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width: 35px; text-align: center;">No</th>
                                <th style="width: 110px; text-align: left;">Kategori</th>
                                <th style="min-width: 250px; text-align: left;">Nama Produk / Barang Jadi</th>
                                <th style="width: 110px; text-align: left;">Kode Barang</th>
                                <th style="width: 95px; text-align: right;">Jumlah</th>
                                <th style="width: 75px; text-align: center;">Satuan</th>
                                <th style="width: 125px; text-align: right;">Harga Satuan</th>
                                <th style="width: 80px; text-align: right;">Diskon (%)</th>
                                <th style="width: 105px; text-align: right;">Potongan (Rp)</th>
                                <th style="width: 100px; text-align: center;">PPN</th>
                                <th style="width: 135px; text-align: right;">Total Harga</th>
                                <th style="width: 40px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            {{-- Baris produk ditambahkan secara dinamis via JavaScript --}}
                        </tbody>
                    </table>
                </div>

                {{-- Empty Notice --}}
                <div id="noItemsNotice" style="display: none; padding: 2.5rem; text-align: center; color: #94a3b8; font-size: 0.85rem;">
                    Belum ada barang pada tabel inputan. Klik tombol <strong>"+ Tambah Baris Barang"</strong> di atas.
                </div>
                {{-- INTEGRATED COMPACT FOOTER (Catatan di Kiri, Ringkasan Finansial di Kanan) --}}
                <div class="so-table-footer-bar">
                    {{-- KIRI: Catatan Pesanan Ringkas & Badge FEFO --}}
                    <div class="so-footer-notes">
                        <div class="so-notes-header">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Catatan Pesanan &amp; Instruksi Khusus (Opsional)</span>
                        </div>
                        <textarea name="catatan_txt" rows="2" class="form-control so-notes-textarea" placeholder="Instruksi pengiriman, toleransi mutu, catatan tempo pembayaran, dll...">{{ old('catatan_txt', $order->catatan_txt) }}</textarea>
                        
                        <div class="so-fefo-pill">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Alokasi batch barang jadi otomatis memprioritaskan tanggal kadaluarsa terdekat (FEFO).</span>
                        </div>
                    </div>

                    {{-- KANAN: Financial Ledger Ramping & Presisi --}}
                    <div class="so-footer-financial">
                        <div class="so-fin-row">
                            <span>Subtotal Nilai Bruto</span>
                            <strong class="so-fin-val" id="lblSubtotalBruto">Rp {{ number_format($order->subtotal_bruto, 0, ',', '.') }}</strong>
                        </div>
                        <div class="so-fin-row discount">
                            <span>Akumulasi Diskon</span>
                            <strong class="so-fin-val" id="lblTotalDiskon">- Rp {{ number_format($order->diskon_total, 0, ',', '.') }}</strong>
                        </div>
                        <div class="so-fin-row potongan">
                            <span>Potongan Faktur Langsung</span>
                            <div class="so-potongan-input-wrapper">
                                <span class="so-potongan-prefix">Rp</span>
                                <input type="number" step="any" min="0" name="potongan_nominal" id="headerPotonganInput" value="{{ old('potongan_nominal', $order->potongan_nominal ?? 0) }}" class="form-control so-potongan-input">
                            </div>
                        </div>
                        <div class="so-fin-row dpp">
                            <span>Dasar Pengenaan Pajak (DPP)</span>
                            <strong class="so-fin-val" id="lblDppNominal">Rp {{ number_format($order->dpp_nominal, 0, ',', '.') }}</strong>
                        </div>
                        <div class="so-fin-row ppn">
                            <span>PPN (11%)</span>
                            <strong class="so-fin-val" id="lblTotalPpn">+ Rp {{ number_format($order->ppn_nominal, 0, ',', '.') }}</strong>
                        </div>
                        <div class="so-fin-total">
                            <span class="so-total-label">Total Tagihan Akhir:</span>
                            <strong class="so-total-val" id="lblTotalTagihan">Rp {{ number_format($order->total_tagihan, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>

                {{-- TOMBOL AKSI LANGSUNG DI BAWAH KARTU DOKUMEN --}}
                <div class="so-table-action-bar">
                    <div style="display: flex; align-items: center; gap: 0.5rem; color: #64748b; font-size: 0.8125rem;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Perubahan rincian pesanan akan memperbarui tagihan komersial secara otomatis</span>
                    </div>
                    <div class="so-action-buttons-group">
                        <a href="{{ route('penjualan.so.show', $order->so_id) }}" class="btn-so-cancel">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Batal</span>
                        </a>
                        <button type="submit" class="btn-so-submit">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Perbarui PO Penjualan</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@push('scripts')
    @php
        $existingItems = $order->details->map(function($d) {
            return [
                'barang_id' => $d->barang_id,
                'pesan_qty' => (float)$d->pesan_qty,
                'harga_satuan' => (float)$d->harga_satuan,
                'diskon_persen' => (float)$d->diskon_persen,
                'potongan_nominal' => (float)$d->potongan_nominal,
                'ppn_tipe' => $d->ppn_tipe,
                'catatan_txt' => $d->catatan_txt
            ];
        });
    @endphp
    <script>
        window.soConfig = {
            barangs: @json($barangs),
            stockMap: @json($stockMap ?? []),
            existingItems: @json($existingItems),
            generateFakturUrl: '{{ route("penjualan.so.ajax.generate-faktur-no") }}',
            generateSoCodeUrl: '{{ route("ajax.generate_code") }}'
        };
    </script>
    <script src="{{ asset('js/penjualan/so/so-form.js') }}"></script>
@endpush
