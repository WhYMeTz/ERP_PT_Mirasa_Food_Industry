@extends('layouts.app')

@section('title', 'Edit PO Penjualan ' . $order->so_no . ' - ERP PT Mirasa')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/so/so-form.css') }}">
@endpush

<div style="margin-bottom: 1.25rem;">
    <a href="{{ route('penjualan.so.show', $order->so_id) }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Detail Dokumen
    </a>
    <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Edit Dokumen PO (Penjualan): {{ $order->so_no }}</h1>
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
    <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
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

<form method="POST" action="{{ route('penjualan.so.update', $order->so_id) }}" id="formPo" class="order-form-container">
    @csrf
    @method('PUT')

    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        
        {{-- KARTU 1: DATA UTAMA PEMESAN --}}
        <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                <strong style="color: #0f172a; font-size: 0.95rem;">1. Informasi Dokumen &amp; Pelanggan (Customer)</strong>
            </div>

            <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1.25rem;">
                {{-- BARIS 1: a. Tanggal, b. Kode Pesanan --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem;">
                    {{-- a. Tanggal --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="so_tgl" class="form-label" style="font-weight: 600; font-size: 0.85rem;">a. Tanggal <span style="color:#ef4444;">*</span></label>
                        <input type="date" id="so_tgl" name="so_tgl" value="{{ old('so_tgl', $order->so_tgl?->format('Y-m-d')) }}" class="form-control" required>
                    </div>

                    {{-- b. Kode Pesanan --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="so_no" class="form-label" style="font-weight: 600; font-size: 0.85rem;">b. Kode Pesanan</label>
                        <input type="text" id="so_no" name="so_no" value="{{ $order->so_no }}" readonly class="form-control" style="background: #f8fafc; font-weight: 700; font-family: monospace; color: #0284c7; cursor: not-allowed;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="tgl_kirim_estimasi" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Estimasi Tanggal Kirim</label>
                        <input type="date" id="tgl_kirim_estimasi" name="tgl_kirim_estimasi" value="{{ old('tgl_kirim_estimasi', $order->tgl_kirim_estimasi?->format('Y-m-d')) }}" class="form-control">
                    </div>
                </div>

                {{-- BARIS 2: c. Nama Customer, d. Kode Customer (Otomatis) --}}
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1.25rem; align-items: start;">
                    {{-- c. Nama Customer --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="customerSelect" class="form-label" style="font-weight: 600; font-size: 0.85rem;">c. Nama Customer <span style="color:#ef4444;">*</span></label>
                        <select name="customer_id" id="customerSelect" required class="form-control">
                            <option value="">-- Pilih Customer Mitra --</option>
                            @foreach($customers as $cust)
                                <option value="{{ $cust->customer_id }}"
                                        data-code="{{ $cust->customer_cd }}"
                                        data-contact="{{ $cust->kontak_no }}"
                                        data-address="{{ $cust->alamat_txt }}"
                                        {{ old('customer_id', $order->customer_id) == $cust->customer_id ? 'selected' : '' }}>
                                    {{ $cust->customer_nm }} ({{ $cust->customer_cd }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- d. Kode Customer (Otomatis) --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="customerCdInput" class="form-label" style="font-weight: 600; font-size: 0.85rem;">d. Kode Customer <span style="color:#0284c7;">*Otomatis</span></label>
                        <input type="text" id="customerCdInput" readonly class="form-control" value="{{ $order->customer?->customer_cd }}" style="background: #f8fafc; font-family: monospace; font-weight: 700; color: #0284c7; cursor: not-allowed;">
                    </div>

                    {{-- Nomor Referensi PO Customer --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="customer_po_no" class="form-label" style="font-weight: 600; font-size: 0.85rem;">No PO Customer (Opsional)</label>
                        <input type="text" id="customer_po_no" name="customer_po_no" value="{{ old('customer_po_no', $order->customer_po_no) }}" placeholder="PO-CUST/09..." class="form-control">
                    </div>
                </div>

                {{-- CUSTOMER INFO PREVIEW --}}
                <div id="customerInfoBox" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem 1rem; font-size: 0.825rem; color: #334155;">
                    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1rem;">
                        <div>
                            <span style="color: #64748b; font-size: 0.75rem; display: block;">Alamat Pengiriman Customer:</span>
                            <strong id="custAddressText">{{ $order->customer?->alamat_txt ?: '-' }}</strong>
                        </div>
                        <div>
                            <span style="color: #64748b; font-size: 0.75rem; display: block;">Kontak / No Telepon:</span>
                            <strong id="custContactText">{{ $order->customer?->kontak_no ?: '-' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 2: TABEL INPUTAN PRODUK HASIL PRODUKSI (12 KOLOM MAKSIMAL LEBAR 100%) --}}
        <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: gap: 0.5rem;">
                <div>
                    <strong style="color: #0f172a; font-size: 0.95rem;">2. Tabel Inputan Rincian Barang</strong>
                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                        Daftar produk hasil produksi yang dipesan (Barang Jadi / Setengah Jadi).
                    </div>
                </div>
                <button type="button" id="btnAddRow" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Tambah Baris Barang</span>
                </button>
            </div>

            <div style="overflow-x: auto;">
                <table class="excel-grid-table" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width: 35px; text-align: center;">No</th>
                            <th style="width: 110px; text-align: left;">e. Jenis Barang</th>
                            <th style="min-width: 250px; text-align: left;">f. Nama Barang</th>
                            <th style="width: 110px; text-align: left;">g. Kode Barang</th>
                            <th style="width: 95px; text-align: right;">h. Jumlah Pesanan</th>
                            <th style="width: 75px; text-align: center;">i. Satuan</th>
                            <th style="width: 125px; text-align: right;">j. @Harga satuan</th>
                            <th style="width: 80px; text-align: right;">k. Diskon %</th>
                            <th style="width: 105px; text-align: right;">l. Potongan Rp</th>
                            <th style="width: 105px; text-align: center;">m. PPN 11% / Non</th>
                            <th style="width: 135px; text-align: right;">n. Total Harga</th>
                            <th style="width: 40px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        {{-- Baris produk ditambahkan secara dinamis via JavaScript --}}
                    </tbody>
                </table>
            </div>

            {{-- Empty Notice --}}
            <div id="noItemsNotice" style="display: none; padding: 2rem; text-align: center; color: #94a3b8; font-size: 0.85rem;">
                Belum ada barang pada tabel inputan. Klik tombol <strong>"+ Tambah Baris Barang"</strong> di atas.
            </div>
        </div>

        {{-- BAGIAN BAWAH: CATATAN (KIRI) & RINGKASAN NILAI PESANAN (KANAN) --}}
        <div class="order-bottom-grid">
            {{-- KIRI: CATATAN PESANAN & SYARAT PENGIRIMAN --}}
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                    <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                        <strong style="color: #0f172a; font-size: 0.9rem;">Catatan Pesanan &amp; Instruksi Khusus</strong>
                    </div>
                    <div style="padding: 1rem 1.25rem;">
                        <textarea name="catatan_txt" rows="4" class="form-control" placeholder="Instruksi pengiriman, toleransi mutu, dll..." style="font-size: 0.825rem; width: 100%; box-sizing: border-box;">{{ old('catatan_txt', $order->catatan_txt) }}</textarea>
                        <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.35rem;">
                            Catatan ini akan tercetak otomatis pada Faktur dan Surat Jalan.
                        </div>
                    </div>
                </div>

                {{-- PETUNJUK PENGISIAN --}}
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.85rem 1.15rem; font-size: 0.8rem; color: #166534;">
                    <div style="font-weight: 700; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.35rem;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Informasi Real-Time Stok &amp; FEFO</span>
                    </div>
                    <p style="margin: 0; line-height: 1.45;">
                        Pilihan nama barang menampilkan sisa stok fisik di Gudang Barang Jadi (GDG-FG). Saat konfirmasi pengiriman dilakukan nanti, sistem akan otomatis memotong batch dengan tanggal kadaluarsa terdekat (FEFO).
                    </p>
                </div>
            </div>

            {{-- KANAN: RINGKASAN NILAI PESANAN (FINANCIAL BREAKDOWN) --}}
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                    <strong style="color: #0f172a; font-size: 0.9rem;">Ringkasan Nilai Pesanan</strong>
                    <span class="badge" style="background: #e0f2fe; color: #0284c7; font-size: 0.7rem; font-weight: 700;">Live Realtime</span>
                </div>
                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 0.65rem; font-size: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; color: #64748b;">
                        <span>Subtotal Nilai Bruto:</span>
                        <strong style="color: #0f172a; font-family: monospace;" id="lblSubtotalBruto">Rp {{ number_format($order->subtotal_bruto, 0, ',', '.') }}</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; color: #d97706;">
                        <span>Akumulasi Diskon:</span>
                        <strong style="font-family: monospace;" id="lblTotalDiskon">- Rp {{ number_format($order->diskon_total, 0, ',', '.') }}</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; color: #dc2626;">
                        <span>Potongan Faktur Langsung:</span>
                        <input type="number" step="any" min="0" name="potongan_nominal" id="headerPotonganInput" value="{{ old('potongan_nominal', $order->potongan_nominal ?? 0) }}" class="form-control" style="width: 130px; text-align: right; padding: 0.2rem 0.45rem; font-size: 0.825rem; height: 30px; font-weight: 700; font-family: monospace;">
                    </div>

                    <div style="display: flex; justify-content: space-between; color: #475569; padding-top: 0.35rem; border-top: 1px dashed #cbd5e1;">
                        <span>Dasar Pengenaan Pajak (DPP):</span>
                        <strong style="color: #0f172a; font-family: monospace;" id="lblDppNominal">Rp {{ number_format($order->dpp_nominal, 0, ',', '.') }}</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; color: #0284c7;">
                        <span>PPN (11%):</span>
                        <strong style="font-family: monospace;" id="lblTotalPpn">+ Rp {{ number_format($order->ppn_nominal, 0, ',', '.') }}</strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.65rem; border-top: 2px solid #0f172a; font-size: 1rem;">
                        <span style="font-weight: 700; color: #0f172a;">Total Tagihan Akhir:</span>
                        <strong style="color: #0284c7; font-size: 1.25rem; font-family: monospace;" id="lblTotalTagihan">Rp {{ number_format($order->total_tagihan, 0, ',', '.') }}</strong>
                    </div>
                </div>

                {{-- TOMBOL AKSI SIMPAN DI BAWAH RINGKASAN --}}
                <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 1rem 1.25rem; display: flex; gap: 0.75rem;">
                    <a href="{{ route('penjualan.so.show', $order->so_id) }}" class="btn btn-secondary" style="flex: 1; padding: 0.6rem; text-align: center; justify-content: center; box-sizing: border-box; font-weight: 600;">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary" style="flex: 2; padding: 0.65rem; font-weight: 700; font-size: 0.9rem; justify-content: center; background: #0284c7; border-color: #0284c7;">
                        Perbarui PO Penjualan
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

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
            existingItems: @json($existingItems)
        };
    </script>
    <script src="{{ asset('js/penjualan/so/so-form.js') }}"></script>
@endpush
