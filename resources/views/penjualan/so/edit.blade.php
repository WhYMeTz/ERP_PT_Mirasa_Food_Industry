@extends('layouts.app')

@section('title', 'Edit PO Penjualan ' . $order->so_no . ' - ERP PT Mirasa')

@section('content')
<div class="page-container">
    {{-- Breadcrumb & Title --}}
    <div style="margin-bottom: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
            <a href="{{ route('penjualan.so.index') }}" style="color: #0284c7; text-decoration: none; font-size: 0.825rem; font-weight: 600;">
                PO Penjualan
            </a>
            <span style="color: #94a3b8; font-size: 0.8rem;">/</span>
            <a href="{{ route('penjualan.so.show', $order->so_id) }}" style="color: #0284c7; text-decoration: none; font-size: 0.825rem; font-weight: 600;">
                {{ $order->so_no }}
            </a>
            <span style="color: #94a3b8; font-size: 0.8rem;">/</span>
            <span style="color: #64748b; font-size: 0.825rem; font-weight: 600;">Edit Dokumen</span>
        </div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
            Perbarui Dokumen PO Penjualan: {{ $order->so_no }}
        </h1>
        <p style="font-size: 0.85rem; color: #64748b; margin: 0.2rem 0 0 0;">
            Sesuaikan data item pesanan, harga satuan, diskon, atau potongan sebelum barang dikirim.
        </p>
    </div>

    @if ($errors->any())
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

    <form method="POST" action="{{ route('penjualan.so.update', $order->so_id) }}" id="soForm">
        @csrf
        @method('PUT')

        {{-- Section 1: Header Pesanan & Data Customer --}}
        <div class="card" style="border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; border-radius: 10px 10px 0 0; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="18" height="18" fill="none" stroke="#0284c7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span style="font-weight: 700; color: #0f172a; font-size: 0.925rem;">1. Informasi Utama &amp; Data Customer</span>
                </div>
                {!! $order->status_badge !!}
            </div>

            <div style="padding: 1.25rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem;">
                {{-- Tanggal Pesanan --}}
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.4rem;">
                        Tanggal Pesanan <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="date" name="so_tgl" id="soTglInput" value="{{ old('so_tgl', $order->so_tgl?->format('Y-m-d')) }}" required class="form-control" style="font-size: 0.875rem;">
                </div>

                {{-- Kode Pesanan (Readonly) --}}
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.4rem;">
                        Kode Pesanan (Standar ERP)
                    </label>
                    <input type="text" name="so_no" value="{{ $order->so_no }}" readonly class="form-control" style="background: #f1f5f9; font-family: monospace; font-weight: 800; color: #0284c7; font-size: 0.95rem; cursor: not-allowed;">
                </div>

                {{-- Nama Customer --}}
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.4rem;">
                        Pilih Customer <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="customer_id" id="customerSelect" required class="form-control" style="font-size: 0.875rem;">
                        <option value="">-- Pilih Customer Pemesan --</option>
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

                {{-- Kode Customer (Otomatis) --}}
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.4rem;">
                        Kode Customer <span style="color: #0284c7;">*Otomatis</span>
                    </label>
                    <input type="text" id="customerCdInput" readonly class="form-control" value="{{ $order->customer?->customer_cd }}" style="background: #f8fafc; font-family: monospace; font-weight: 700; color: #334155; cursor: not-allowed;">
                </div>

                {{-- Nomor PO Asli dari Customer --}}
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.4rem;">
                        Nomor PO Customer (Opsional)
                    </label>
                    <input type="text" name="customer_po_no" value="{{ old('customer_po_no', $order->customer_po_no) }}" placeholder="Contoh: PO-CUST/2026/091" class="form-control" style="font-size: 0.875rem;">
                </div>

                {{-- Estimasi Tanggal Kirim --}}
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.4rem;">
                        Estimasi Tanggal Kirim
                    </label>
                    <input type="date" name="tgl_kirim_estimasi" value="{{ old('tgl_kirim_estimasi', $order->tgl_kirim_estimasi?->format('Y-m-d')) }}" class="form-control" style="font-size: 0.875rem;">
                </div>
            </div>

            {{-- Info Customer Info Banner --}}
            <div id="customerInfoBox" style="padding: 0.85rem 1.25rem; background: #f0fdf4; border-top: 1px solid #bbf7d0; font-size: 0.8rem; color: #166534;">
                <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                    <div><strong>Alamat Pengiriman:</strong> <span id="custAddressText">{{ $order->customer?->alamat_txt ?? '-' }}</span></div>
                    <div><strong>Kontak / HP:</strong> <span id="custContactText">{{ $order->customer?->kontak_no ?? '-' }}</span></div>
                </div>
            </div>
        </div>

        {{-- Section 2: Grid Input Barang Pesanan --}}
        <div class="card" style="border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02); overflow: hidden;">
            <div style="padding: 1rem 1.25rem; background: #0f172a; color: #ffffff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="18" height="18" fill="none" stroke="#38bdf8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span style="font-weight: 700; font-size: 0.925rem;">2. Rincian Produk Jadi / Hasil Produksi (Finish Goods)</span>
                </div>
                <button type="button" id="btnAddRow" class="btn btn-sm" style="background: #0284c7; color: #ffffff; font-weight: 700; border-radius: 6px; padding: 0.4rem 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; border: none; cursor: pointer;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Tambah Baris Barang</span>
                </button>
            </div>

            <div style="overflow-x: auto;">
                <table class="table" id="itemsTable" style="margin: 0; width: 100%; border-collapse: collapse; font-size: 0.825rem;">
                    <thead>
                        <tr style="background: #1e293b; color: #f8fafc; text-align: left;">
                            <th style="padding: 0.65rem 0.5rem; width: 35px; text-align: center;">#</th>
                            <th style="padding: 0.65rem 0.5rem; min-width: 220px;">Nama Produk Jadi</th>
                            <th style="padding: 0.65rem 0.5rem; width: 130px;">Kode Barang</th>
                            <th style="padding: 0.65rem 0.5rem; width: 110px;">Jenis</th>
                            <th style="padding: 0.65rem 0.5rem; width: 100px;">Jumlah</th>
                            <th style="padding: 0.65rem 0.5rem; width: 80px;">Satuan</th>
                            <th style="padding: 0.65rem 0.5rem; width: 130px;">@Harga Satuan (Rp)</th>
                            <th style="padding: 0.65rem 0.5rem; width: 85px;">Diskon %</th>
                            <th style="padding: 0.65rem 0.5rem; width: 110px;">Potongan (Rp)</th>
                            <th style="padding: 0.65rem 0.5rem; width: 110px;">PPN 11%</th>
                            <th style="padding: 0.65rem 0.5rem; width: 140px; text-align: right;">Total Harga (Rp)</th>
                            <th style="padding: 0.65rem 0.5rem; width: 45px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        {{-- Row template populated via JS --}}
                    </tbody>
                </table>
            </div>

            <div id="noItemsNotice" style="display: none; padding: 2rem; text-align: center; color: #64748b; background: #fafafa;">
                Klik tombol <strong>+ Tambah Baris Barang</strong> untuk menambahkan item pesanan.
            </div>
        </div>

        {{-- Section 3: Ringkasan Nilai & Komersial --}}
        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1.5rem; margin-bottom: 2rem; align-items: start;">
            {{-- Catatan Pesanan --}}
            <div class="card" style="border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem;">
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.4rem;">
                    Catatan / Syarat Khusus Pesanan
                </label>
                <textarea name="catatan_txt" rows="4" class="form-control" style="font-size: 0.85rem;">{{ old('catatan_txt', $order->catatan_txt) }}</textarea>
            </div>

            {{-- Live Calculation Summary Box --}}
            <div class="card" style="border: 2px solid #0284c7; border-radius: 10px; padding: 1.25rem; background: #f8fafc; box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.1);">
                <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem; margin-bottom: 0.75rem; display: flex; justify-content: space-between; align-items: center;">
                    <span>Kalkulasi Otomatis Total</span>
                    <span class="badge" style="background: #e0f2fe; color: #0284c7; font-size: 0.7rem; font-weight: 700;">Live Realtime</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; color: #475569;">
                        <span>Subtotal Bruto:</span>
                        <span id="lblSubtotalBruto" style="font-weight: 700; color: #0f172a;">Rp 0</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; color: #475569;">
                        <span>Total Diskon Item:</span>
                        <span id="lblTotalDiskon" style="font-weight: 700; color: #dc2626;">- Rp 0</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; color: #475569;">
                        <span>Potongan Faktur (Rp):</span>
                        <input type="number" step="any" min="0" name="potongan_nominal" id="headerPotonganInput" value="{{ old('potongan_nominal', 0) }}" class="form-control" style="width: 130px; text-align: right; padding: 0.25rem 0.5rem; font-size: 0.85rem; height: 30px;">
                    </div>

                    <div style="display: flex; justify-content: space-between; color: #475569; border-top: 1px dashed #cbd5e1; padding-top: 0.5rem;">
                        <span>Dasar Pengenaan Pajak (DPP):</span>
                        <span id="lblDppNominal" style="font-weight: 700; color: #0f172a;">Rp 0</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; color: #475569;">
                        <span>Total PPN 11%:</span>
                        <span id="lblTotalPpn" style="font-weight: 700; color: #16a34a;">+ Rp 0</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 2px solid #0f172a; padding-top: 0.75rem; margin-top: 0.25rem;">
                        <span style="font-size: 1rem; font-weight: 800; color: #0f172a;">TOTAL TAGIHAN:</span>
                        <span id="lblTotalTagihan" style="font-size: 1.25rem; font-weight: 800; color: #0284c7;">Rp 0</span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div style="display: flex; gap: 0.75rem; margin-top: 1.25rem;">
                    <a href="{{ route('penjualan.so.show', $order->so_id) }}" class="btn btn-outline-secondary" style="flex: 1; text-align: center; font-weight: 600; padding: 0.6rem; border-radius: 6px;">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary" style="flex: 2; font-weight: 700; padding: 0.6rem; border-radius: 6px; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.2);">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const MASTER_BARANG = @json($barangs);
    const EXISTING_ITEMS = @json($order->details);
    let rowCounter = 0;

    function formatRupiah(amount) {
        return 'Rp ' + Number(amount || 0).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

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

    function addRow(data = {}) {
        rowCounter++;
        const idx = rowCounter;
        const tbody = document.getElementById('itemsBody');

        const tr = document.createElement('tr');
        tr.id = `row_${idx}`;
        tr.style.borderBottom = '1px solid #e2e8f0';

        tr.innerHTML = `
            <td style="padding: 0.4rem 0.5rem; text-align: center; color: #64748b; font-weight: 700;">${idx}</td>
            <td style="padding: 0.4rem 0.5rem;">
                <select name="items[${idx}][barang_id]" class="form-control item-barang-select" required style="font-size: 0.8rem; padding: 0.35rem 0.5rem;">
                    <option value="">-- Pilih Barang (FG / WIP) --</option>
                    ${MASTER_BARANG.map(b => `
                        <option value="${b.barang_id}" 
                                data-code="${b.barang_cd || ''}" 
                                data-jenis="${b.jenis_barang ? b.jenis_barang.jenis_barang_nm : '-'}" 
                                data-satuan="${b.satuan_dasar ? b.satuan_dasar.satuan_nm : 'Pcs'}"
                                data-harga="${b.harga_jual || b.harga_standar || 0}"
                                ${data.barang_id == b.barang_id ? 'selected' : ''}>
                            [${b.jenis_barang ? b.jenis_barang.jenis_barang_cd : 'FG'}] ${b.barang_nm}
                        </option>
                    `).join('')}
                </select>
            </td>
            <td style="padding: 0.4rem 0.5rem;">
                <input type="text" class="form-control item-barang-code" readonly placeholder="-" style="background: #f8fafc; font-family: monospace; font-size: 0.775rem; cursor: not-allowed;">
            </td>
            <td style="padding: 0.4rem 0.5rem;">
                <input type="text" class="form-control item-jenis" readonly placeholder="-" style="background: #f8fafc; font-size: 0.775rem; cursor: not-allowed;">
            </td>
            <td style="padding: 0.4rem 0.5rem;">
                <input type="number" step="any" min="0.001" name="items[${idx}][pesan_qty]" value="${data.pesan_qty || 1}" required class="form-control item-qty" style="font-size: 0.8rem; text-align: right;">
            </td>
            <td style="padding: 0.4rem 0.5rem;">
                <input type="text" class="form-control item-satuan" readonly placeholder="-" style="background: #f8fafc; font-size: 0.775rem; cursor: not-allowed;">
            </td>
            <td style="padding: 0.4rem 0.5rem;">
                <input type="number" step="any" min="0" name="items[${idx}][harga_satuan]" value="${data.harga_satuan || 0}" required class="form-control item-harga" style="font-size: 0.8rem; text-align: right;">
            </td>
            <td style="padding: 0.4rem 0.5rem;">
                <input type="number" step="any" min="0" max="100" name="items[${idx}][diskon_persen]" value="${data.diskon_persen || 0}" class="form-control item-diskon" style="font-size: 0.8rem; text-align: right;">
            </td>
            <td style="padding: 0.4rem 0.5rem;">
                <input type="number" step="any" min="0" name="items[${idx}][potongan_nominal]" value="${data.potongan_nominal || 0}" class="form-control item-potongan" style="font-size: 0.8rem; text-align: right;">
            </td>
            <td style="padding: 0.4rem 0.5rem;">
                <select name="items[${idx}][ppn_tipe]" class="form-control item-ppn" style="font-size: 0.8rem; padding: 0.35rem 0.3rem;">
                    <option value="NON_PPN" ${data.ppn_tipe === 'NON_PPN' ? 'selected' : ''}>Non PPN</option>
                    <option value="PPN_11" ${data.ppn_tipe === 'PPN_11' ? 'selected' : ''}>PPN 11%</option>
                </select>
            </td>
            <td style="padding: 0.4rem 0.5rem; text-align: right;">
                <span class="item-subtotal-text" style="font-weight: 700; color: #0f172a; font-size: 0.85rem;">Rp 0</span>
            </td>
            <td style="padding: 0.4rem 0.5rem; text-align: center;">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" style="padding: 0.2rem 0.45rem; border-radius: 4px;" title="Hapus Baris">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
