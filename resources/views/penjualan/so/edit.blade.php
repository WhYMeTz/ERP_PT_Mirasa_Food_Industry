@extends('layouts.app')

@section('title', 'Edit PO Penjualan ' . $order->so_no . ' - ERP PT Mirasa')

@section('content')
<style>
    .order-station-grid {
        display: grid;
        grid-template-columns: 2.3fr 1fr;
        gap: 1.5rem;
        align-items: start;
        margin-bottom: 2rem;
    }
    @media (max-width: 1100px) {
        .order-station-grid {
            grid-template-columns: 1fr;
        }
        .sticky-action-sidebar {
            position: static !important;
            top: auto !important;
        }
    }
    .excel-grid-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.825rem;
    }
    .excel-grid-table th {
        background: #0f172a;
        color: #f8fafc;
        font-weight: 600;
        padding: 0.55rem 0.45rem;
        border: 1px solid #334155;
        font-size: 0.775rem;
        letter-spacing: 0.02em;
    }
    .excel-grid-table td {
        padding: 0.35rem 0.45rem;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        vertical-align: middle;
    }
    .excel-grid-table tr:nth-child(even) td {
        background: #fafafa;
    }
    .excel-grid-table tr:hover td {
        background: #f0f9ff;
    }
    .excel-grid-table .form-control {
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 0.35rem 0.5rem !important;
        font-size: 0.825rem !important;
        height: 32px;
        box-sizing: border-box;
        width: 100%;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .excel-grid-table .form-control:focus {
        border-color: #0284c7 !important;
        box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.25) !important;
        background: #ffffff !important;
    }
</style>

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

<form method="POST" action="{{ route('penjualan.so.update', $order->so_id) }}" id="formPo">
    @csrf
    @method('PUT')

    <div class="order-station-grid">
        {{-- KOLOM KIRI: FORM DATA DOKUMEN & TABEL INPUTAN BARANG --}}
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

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="customer_po_no" class="form-label" style="font-weight: 600; font-size: 0.85rem;">No PO Customer (Opsional)</label>
                            <input type="text" id="customer_po_no" name="customer_po_no" value="{{ old('customer_po_no', $order->customer_po_no) }}" placeholder="PO-CUST/09..." class="form-control">
                        </div>
                    </div>

                    {{-- CUSTOMER INFO PREVIEW --}}
                    <div id="customerInfoBox" style="display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem 1rem; font-size: 0.825rem; color: #334155;">
                        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1rem;">
                            <div>
                                <span style="color: #64748b; font-size: 0.75rem; display: block;">Alamat Pengiriman Customer:</span>
                                <strong id="custAddressText">-</strong>
                            </div>
                            <div>
                                <span style="color: #64748b; font-size: 0.75rem; display: block;">Kontak / No Telepon:</span>
                                <strong id="custContactText">-</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KARTU 2: TABEL INPUTAN PRODUK HASIL PRODUKSI --}}
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <strong style="color: #0f172a; font-size: 0.95rem;">2. Tabel Inputan Rincian Barang</strong>
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                            Daftar produk hasil produksi yang dipesan (Barang Jadi / Setengah Jadi).
                        </div>
                    </div>
                    <button type="button" id="btnAddRow" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        + Tambah Baris Barang
                    </button>
                </div>

                <div style="overflow-x: auto;">
                    <table class="excel-grid-table" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width: 30px; text-align: center;">No</th>
                                <th style="width: 100px; text-align: left;">e. Jenis Barang</th>
                                <th style="min-width: 210px; text-align: left;">f. Nama Barang</th>
                                <th style="width: 100px; text-align: left;">g. Kode Barang</th>
                                <th style="width: 85px; text-align: right;">h. Jumlah Pesanan</th>
                                <th style="width: 70px; text-align: left;">i. Satuan</th>
                                <th style="width: 110px; text-align: right;">j. @Harga satuan</th>
                                <th style="width: 70px; text-align: right;">k. Diskon %</th>
                                <th style="width: 90px; text-align: right;">l. Potongan</th>
                                <th style="width: 95px; text-align: center;">m. PPN 11% / Non</th>
                                <th style="width: 125px; text-align: right;">n. Total Harga</th>
                                <th style="width: 35px; text-align: center;">Aksi</th>
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
        </div>

        {{-- KOLOM KANAN: SIDEBAR RINGKASAN & AKSI DOKUMEN --}}
        <div class="sticky-action-sidebar" style="display: flex; flex-direction: column; gap: 1.25rem; position: sticky; top: 1rem;">
            
            {{-- PANEL 1: RINGKASAN KALKULASI OTOMATIS --}}
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
                        <span>Potongan Faktur:</span>
                        <input type="number" step="any" min="0" name="potongan_nominal" id="headerPotonganInput" value="{{ old('potongan_nominal', $order->potongan_nominal ?? 0) }}" class="form-control" style="width: 120px; text-align: right; padding: 0.2rem 0.45rem; font-size: 0.825rem; height: 28px;">
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
                        <span style="font-weight: 700; color: #0f172a;">Total Harga Akhir:</span>
                        <strong style="color: #0284c7; font-size: 1.15rem; font-family: monospace;" id="lblTotalTagihan">Rp {{ number_format($order->total_tagihan, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>

            {{-- PANEL 2: CATATAN PESANAN --}}
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                    <strong style="color: #0f172a; font-size: 0.9rem;">Catatan Pesanan</strong>
                </div>
                <div style="padding: 1rem 1.25rem;">
                    <textarea name="catatan_txt" rows="3" class="form-control" placeholder="Instruksi pengiriman, toleransi mutu, dll..." style="font-size: 0.825rem; width: 100%; box-sizing: border-box;">{{ old('catatan_txt', $order->catatan_txt) }}</textarea>
                    <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.35rem;">
                        Tercetak otomatis pada Faktur dan Surat Jalan.
                    </div>
                </div>
            </div>

            {{-- PANEL 3: TOMBOL AKSI SIMPAN --}}
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.65rem; font-weight: 700; font-size: 0.9rem; justify-content: center;">
                    Perbarui PO Penjualan
                </button>
                <a href="{{ route('penjualan.so.show', $order->so_id) }}" class="btn btn-secondary" style="width: 100%; padding: 0.6rem; text-align: center; justify-content: center; box-sizing: border-box;">
                    Batal
                </a>
            </div>
        </div>
    </div>
</form>

<script>
    const MASTER_BARANG = @json($barangs);
    const EXISTING_ITEMS = @json($order->details);
    let rowCounter = 0;

    function formatRupiah(amount) {
        return 'Rp ' + Number(amount || 0).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    // Customer Selection Change Listener -> Mengisi d. Kode Customer (Otomatis)
    document.getElementById('customerSelect').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const code = selected.getAttribute('data-code') || '';
        const address = selected.getAttribute('data-address') || '-';
        const contact = selected.getAttribute('data-contact') || '-';

        document.getElementById('customerCdInput').value = code;

        const infoBox = document.getElementById('customerInfoBox');
        if (this.value) {
            document.getElementById('custAddressText').textContent = address;
            document.getElementById('custContactText').textContent = contact;
            infoBox.style.display = 'block';
        } else {
            infoBox.style.display = 'none';
        }
    });

    // Tambah Baris Baru pada Tabel Inputan
    function addRow(data = {}) {
        rowCounter++;
        const idx = rowCounter;
        const tbody = document.getElementById('itemsBody');

        const tr = document.createElement('tr');
        tr.id = `row_${idx}`;

        tr.innerHTML = `
            <td style="text-align: center; color: #64748b; font-size: 0.8rem;">${idx}</td>
            {{-- e. Jenis Barang --}}
            <td>
                <input type="text" class="form-control item-jenis" readonly placeholder="-" style="background: #f8fafc; font-size: 0.75rem; cursor: not-allowed;">
            </td>
            {{-- f. Nama Barang --}}
            <td>
                <select name="items[${idx}][barang_id]" class="form-control item-barang-select" required style="font-size: 0.8rem;">
                    <option value="">-- Pilih Nama Barang --</option>
                    ${MASTER_BARANG.map(b => `
                        <option value="${b.barang_id}" 
                                data-code="${b.barang_cd || ''}" 
                                data-jenis="${b.jenis_barang ? b.jenis_barang.jenis_barang_nm : (b.jenis_barang_cd || 'FG')}" 
                                data-satuan="${b.satuan_dasar ? b.satuan_dasar.satuan_nm : 'Pcs'}"
                                data-harga="${b.harga_jual || b.harga_standar || 0}"
                                ${data.barang_id == b.barang_id ? 'selected' : ''}>
                            ${b.barang_nm}
                        </option>
                    `).join('')}
                </select>
            </td>
            {{-- g. Kode Barang (Otomatis) --}}
            <td>
                <input type="text" class="form-control item-barang-code" readonly placeholder="Otomatis..." style="background: #f8fafc; font-family: monospace; font-size: 0.75rem; color: #0284c7; font-weight: 700; cursor: not-allowed;">
            </td>
            {{-- h. Jumlah Pesanan --}}
            <td>
                <input type="number" step="any" min="0.001" name="items[${idx}][pesan_qty]" value="${data.pesan_qty || 1}" required class="form-control item-qty" style="text-align: right;">
            </td>
            {{-- i. Satuan --}}
            <td>
                <input type="text" class="form-control item-satuan" readonly placeholder="-" style="background: #f8fafc; font-size: 0.75rem; cursor: not-allowed;">
            </td>
            {{-- j. @Harga satuan --}}
            <td>
                <input type="number" step="any" min="0" name="items[${idx}][harga_satuan]" value="${data.harga_satuan || 0}" required class="form-control item-harga" style="text-align: right;">
            </td>
            {{-- k. Diskon % --}}
            <td>
                <input type="number" step="any" min="0" max="100" name="items[${idx}][diskon_persen]" value="${data.diskon_persen || 0}" class="form-control item-diskon" style="text-align: right;">
            </td>
            {{-- l. Potongan Harga --}}
            <td>
                <input type="number" step="any" min="0" name="items[${idx}][potongan_nominal]" value="${data.potongan_nominal || 0}" class="form-control item-potongan" style="text-align: right;">
            </td>
            {{-- m. PPN 11% / Non PPN --}}
            <td>
                <select name="items[${idx}][ppn_tipe]" class="form-control item-ppn" style="font-size: 0.75rem; padding: 0.2rem 0.25rem;">
                    <option value="NON_PPN" ${data.ppn_tipe === 'NON_PPN' ? 'selected' : ''}>Non PPN</option>
                    <option value="PPN_11" ${data.ppn_tipe === 'PPN_11' ? 'selected' : ''}>PPN 11%</option>
                </select>
            </td>
            {{-- n. Total Harga --}}
            <td style="text-align: right;">
                <span class="item-subtotal-text" style="font-weight: 700; color: #0f172a; font-family: monospace; font-size: 0.825rem;">Rp 0</span>
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn-remove-row" style="background: transparent; border: none; color: #ef4444; cursor: pointer; padding: 2px;" title="Hapus Baris">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </td>
        `;

        tbody.appendChild(tr);

        const selBarang = tr.querySelector('.item-barang-select');
        const inCode = tr.querySelector('.item-barang-code');
        const inJenis = tr.querySelector('.item-jenis');
        const inSatuan = tr.querySelector('.item-satuan');
        const inQty = tr.querySelector('.item-qty');
        const inHarga = tr.querySelector('.item-harga');
        const inDiskon = tr.querySelector('.item-diskon');
        const inPotongan = tr.querySelector('.item-potongan');
        const inPpn = tr.querySelector('.item-ppn');
        const btnRemove = tr.querySelector('.btn-remove-row');

        selBarang.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            inCode.value = opt.getAttribute('data-code') || '';
            inJenis.value = opt.getAttribute('data-jenis') || '';
            inSatuan.value = opt.getAttribute('data-satuan') || '';
            if (Number(inHarga.value) === 0 && !data.harga_satuan) {
                inHarga.value = opt.getAttribute('data-harga') || 0;
            }
            calculateRow(tr);
            calculateGrandTotal();
        });

        if (data.barang_id) {
            selBarang.dispatchEvent(new Event('change'));
        }

        [inQty, inHarga, inDiskon, inPotongan, inPpn].forEach(el => {
            el.addEventListener('input', () => {
                calculateRow(tr);
                calculateGrandTotal();
            });
            el.addEventListener('change', () => {
                calculateRow(tr);
                calculateGrandTotal();
            });
        });

        btnRemove.addEventListener('click', function() {
            tr.remove();
            renumberRows();
            calculateGrandTotal();
        });

        calculateRow(tr);
        calculateGrandTotal();
        checkEmptyState();
    }

    function calculateRow(tr) {
        const qty = parseFloat(tr.querySelector('.item-qty').value) || 0;
        const harga = parseFloat(tr.querySelector('.item-harga').value) || 0;
        const diskonPersen = parseFloat(tr.querySelector('.item-diskon').value) || 0;
        const potonganRp = parseFloat(tr.querySelector('.item-potongan').value) || 0;
        const ppnTipe = tr.querySelector('.item-ppn').value;

        const bruto = qty * harga;
        const diskonNominal = bruto * (diskonPersen / 100);
        const netto = Math.max(0, bruto - diskonNominal - potonganRp);
        const ppnNominal = ppnTipe === 'PPN_11' ? (netto * 0.11) : 0;
        const subtotal = netto + ppnNominal;

        tr.querySelector('.item-subtotal-text').textContent = formatRupiah(subtotal);
        tr.dataset.bruto = bruto;
        tr.dataset.diskon = diskonNominal;
        tr.dataset.netto = netto;
        tr.dataset.ppn = ppnNominal;
        tr.dataset.subtotal = subtotal;
    }

    function calculateGrandTotal() {
        const rows = document.querySelectorAll('#itemsBody tr');
        let totalBruto = 0;
        let totalDiskon = 0;
        let totalDpp = 0;
        let totalPpn = 0;

        rows.forEach(tr => {
            totalBruto += parseFloat(tr.dataset.bruto || 0);
            totalDiskon += parseFloat(tr.dataset.diskon || 0);
            totalDpp += parseFloat(tr.dataset.netto || 0);
            totalPpn += parseFloat(tr.dataset.ppn || 0);
        });

        const headerPotongan = parseFloat(document.getElementById('headerPotonganInput').value) || 0;
        const finalDpp = Math.max(0, totalDpp - headerPotongan);
        const grandTotal = finalDpp + totalPpn;

        document.getElementById('lblSubtotalBruto').textContent = formatRupiah(totalBruto);
        document.getElementById('lblTotalDiskon').textContent = '- ' + formatRupiah(totalDiskon);
        document.getElementById('lblDppNominal').textContent = formatRupiah(finalDpp);
        document.getElementById('lblTotalPpn').textContent = '+ ' + formatRupiah(totalPpn);
        document.getElementById('lblTotalTagihan').textContent = formatRupiah(grandTotal);
    }

    function renumberRows() {
        const rows = document.querySelectorAll('#itemsBody tr');
        rows.forEach((tr, i) => {
            tr.children[0].textContent = i + 1;
        });
        checkEmptyState();
    }

    function checkEmptyState() {
        const rows = document.querySelectorAll('#itemsBody tr');
        const notice = document.getElementById('noItemsNotice');
        if (rows.length === 0) {
            notice.style.display = 'block';
        } else {
            notice.style.display = 'none';
        }
    }

    document.getElementById('btnAddRow').addEventListener('click', () => addRow());
    document.getElementById('headerPotonganInput').addEventListener('input', calculateGrandTotal);

    document.addEventListener('DOMContentLoaded', () => {
        if (EXISTING_ITEMS && EXISTING_ITEMS.length > 0) {
            EXISTING_ITEMS.forEach(it => {
                addRow({
                    barang_id: it.barang_id,
                    pesan_qty: it.pesan_qty,
                    harga_satuan: it.harga_satuan,
                    diskon_persen: it.diskon_persen,
                    potongan_nominal: it.potongan_nominal,
                    ppn_tipe: it.ppn_tipe,
                });
            });
        } else {
            addRow();
        }
        document.getElementById('customerSelect').dispatchEvent(new Event('change'));
    });
</script>
@endsection
