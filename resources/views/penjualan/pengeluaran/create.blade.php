@extends('layouts.app')

@section('title', 'Buat Dokumen Pengeluaran Barang - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/pengeluaran/pengeluaran-form.css') }}">
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
            <h1 class="sj-page-title">Buat Dokumen Pengeluaran Barang</h1>
            <span class="badge" style="background: #ecfdf5; color: #059669; font-weight: 700; font-size: 0.75rem; padding: 4px 10px; border-radius: 6px;">
                Gudang FG Outbound
            </span>
        </div>
        <p class="sj-page-subtitle">
            Alokasi fisik barang jadi dari Gudang FG via metode FEFO berdasarkan PO Penjualan yang telah disetujui.
        </p>
    </div>

    @if (isset($errors) && $errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; color: #991b1b;">
            <div style="font-weight: 700; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Terdapat kesalahan pengisian data pengeluaran:
            </div>
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.825rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="sjCreateForm" method="POST" action="{{ route('penjualan.pengeluaran.store') }}">
        @csrf

        {{-- 2 KOLOM KARTU HEADER --}}
        <div class="sj-grid-2-col">
            
            {{-- KARTU 1: REFERENSI PO PENJUALAN & DATA CUSTOMER --}}
            <div class="sj-card">
                <div class="sj-card-header">
                    <div class="sj-card-header-left">
                        <span class="sj-card-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                        <div>
                            <h3 class="sj-card-title">Referensi PO Penjualan</h3>
                            <div class="sj-card-subtitle">Pilih pesanan pelanggan yang siap dikirim</div>
                        </div>
                    </div>
                </div>

                <div class="sj-card-body">
                    <div class="sj-form-group">
                        <label for="soSelect" class="sj-form-label">
                            <span>Pilih PO Penjualan <span class="req">*</span></span>
                            <span style="font-size: 0.725rem; color: #0284c7; font-weight: 600;">Status: Approved / Partial</span>
                        </label>
                        <select name="so_id" id="soSelect" required class="sj-form-select" style="font-weight: 600;">
                            <option value="">-- Pilih Dokumen PO Penjualan --</option>
                            @foreach ($readyOrders as $ro)
                                <option value="{{ $ro->so_id }}" {{ (old('so_id', $selectedSoId) == $ro->so_id) ? 'selected' : '' }}>
                                    {{ $ro->so_no }} — {{ $ro->customer?->customer_nm }} (Tgl: {{ \Carbon\Carbon::parse($ro->so_tgl)->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Customer Info Preview Box --}}
                    @php
                        $hasPrefill = !empty($prefilledData);
                        $pCust = $hasPrefill ? $prefilledData['customer'] : null;
                        $pOrder = $hasPrefill ? $prefilledData['order'] : null;
                        $pKlasifikasi = $pCust?->klasifikasi ?? 'MANUAL';
                    @endphp
                    <div id="customerInfoBox" class="sj-info-box" style="{{ $hasPrefill ? 'display: grid;' : 'display: none;' }}">
                        <div class="sj-info-item" style="grid-column: 1 / -1;">
                            <div class="sj-info-label">Nama Pelanggan / Mitra:</div>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span id="custNmText" class="sj-info-value" style="font-size: 0.925rem;">
                                    {{ $pCust?->customer_nm ?? '-' }}
                                </span>
                                <span id="custKlasifikasiBadge" class="badge-klasifikasi {{ $pKlasifikasi === 'IFM' ? 'ifm' : (in_array($pKlasifikasi, ['IDM', 'MIDI_ALFA', 'IDM_ALFA']) ? 'modern' : 'manual') }}">
                                    {{ $pKlasifikasi === 'MIDI_ALFA' ? 'Midi/Alfa' : $pKlasifikasi }}
                                </span>
                            </div>
                        </div>

                        <div class="sj-info-item">
                            <div class="sj-info-label">Alamat Pengiriman:</div>
                            <div id="custAlamatText" class="sj-info-value" style="font-weight: 500; font-size: 0.775rem;">
                                {{ $pCust?->alamat_txt ?? '-' }}
                            </div>
                        </div>

                        <div class="sj-info-item">
                            <div class="sj-info-label">Kota / Wilayah Tujuan:</div>
                            <div id="custWilayahText" class="sj-info-value">
                                {{ $pCust?->wilayah ?? '-' }}
                            </div>
                        </div>

                        <div class="sj-info-item" style="grid-column: 1 / -1; border-top: 1px dashed #e2e8f0; padding-top: 0.45rem;">
                            <div class="sj-info-label">Nomor PO Eksternal Customer:</div>
                            <div id="custPoText" class="sj-info-value" style="font-family: monospace;">
                                {{ $pOrder?->customer_po_no ?: '(Tidak ada PO Ext)' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KARTU 2: PARAMETER DOKUMEN & EKSPEDISI ARMADA --}}
            <div class="sj-card">
                <div class="sj-card-header">
                    <div class="sj-card-header-left">
                        <span class="sj-card-icon emerald">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        </span>
                        <div>
                            <h3 class="sj-card-title">Parameter Surat Jalan &amp; Ekspedisi</h3>
                            <div class="sj-card-subtitle">Nomor dokumen, tanggal kirim, supir, dan armada</div>
                        </div>
                    </div>
                </div>

                <div class="sj-card-body">
                    <div class="sj-form-grid-2">
                        <div class="sj-form-group">
                            <label for="pengeluaran_no" class="sj-form-label">
                                <span>No. Surat Jalan <span class="req">*</span></span>
                                <span style="font-size: 0.7rem; color: #059669; font-weight: 700;">Auto Format</span>
                            </label>
                            <input type="text" name="pengeluaran_no" id="pengeluaran_no"
                                   value="{{ old('pengeluaran_no', $defaultSjNo) }}"
                                   class="sj-form-input" style="font-family: monospace; font-weight: 700;" required>
                        </div>

                        <div class="sj-form-group">
                            <label for="pengeluaran_tgl" class="sj-form-label">
                                <span>Tanggal Berangkat <span class="req">*</span></span>
                            </label>
                            <input type="date" name="pengeluaran_tgl" id="pengeluaran_tgl"
                                   value="{{ old('pengeluaran_tgl', date('Y-m-d')) }}"
                                   class="sj-form-input" required>
                        </div>
                    </div>

                    <div class="sj-form-grid-2">
                        <div class="sj-form-group">
                            <label for="supir_nm" class="sj-form-label">Nama Supir / Pengemudi</label>
                            <input type="text" name="supir_nm" id="supir_nm"
                                   value="{{ old('supir_nm') }}"
                                   placeholder="Contoh: Pak Budi / Slamet..." class="sj-form-input">
                        </div>

                        <div class="sj-form-group">
                            <label for="nopol_kendaraan" class="sj-form-label">No. Polisi Armada</label>
                            <input type="text" name="nopol_kendaraan" id="nopol_kendaraan"
                                   value="{{ old('nopol_kendaraan') }}"
                                   placeholder="Contoh: AD 8234 KL / B 9123 XY"
                                   class="sj-form-input" style="text-transform: uppercase; font-family: monospace;">
                        </div>
                    </div>

                    <div class="sj-form-grid-2">
                        <div class="sj-form-group">
                            <label for="ekspedisi_nm" class="sj-form-label">Ekspedisi / Jenis Armada</label>
                            <input type="text" name="ekspedisi_nm" id="ekspedisi_nm"
                                   value="{{ old('ekspedisi_nm', 'Truk Box MFI') }}"
                                   placeholder="Truk Box MFI / Ekspedisi Luar..." class="sj-form-input">
                        </div>

                        <div class="sj-form-group">
                            <label for="catatan_txt" class="sj-form-label">Instruksi / Catatan Pengiriman</label>
                            <input type="text" name="catatan_txt" id="catatan_txt"
                                   value="{{ old('catatan_txt') }}"
                                   placeholder="Catatan untuk bagian ekspedisi..." class="sj-form-input">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- KARTU 3: TABEL RINCIAN PRODUK YANG DIKELUARKAN (FEFO) --}}
        <div class="sj-card">
            <div class="sj-card-header">
                <div class="sj-card-header-left">
                    <span class="sj-card-icon amber">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </span>
                    <div>
                        <h3 class="sj-card-title">Rincian Fisik Barang Jadi yang Dikeluarkan</h3>
                        <div class="sj-card-subtitle">Kuantitas kirim dialokasikan dari stok fisik Gudang FG via metode FEFO</div>
                    </div>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="sj-table">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No.</th>
                            <th>Nama Produk Hasil Produksi</th>
                            <th style="text-align: right; width: 120px;">Qty Pesan</th>
                            <th style="text-align: right; width: 120px;">Sudah Kirim</th>
                            <th style="text-align: right; width: 120px;">Sisa Pesan</th>
                            <th style="text-align: center; width: 170px;">Stok Gudang FG</th>
                            <th style="text-align: right; width: 140px;">Qty Kirim Hari Ini <span class="req">*</span></th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody">
                        @if ($hasPrefill && !empty($prefilledData['items']))
                            @foreach ($prefilledData['items'] as $index => $item)
                                @php
                                    $sisa = $item['sisa_qty'];
                                    $stokFg = $item['stok_fg'];
                                    $isStockOk = $stokFg >= $sisa;
                                @endphp
                                <tr>
                                    <td style="text-align: center; color: #94a3b8; font-weight: 600;">
                                        {{ $index + 1 }}
                                        <input type="hidden" name="items[{{ $index }}][sodtl_id]" value="{{ $item['sodtl_id'] }}">
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: #0f172a;">{{ $item['barang_nm'] }}</div>
                                        <div style="font-size: 0.725rem; color: #64748b; font-family: monospace;">Kode: {{ $item['barang_cd'] }}</div>
                                    </td>
                                    <td style="text-align: right; color: #475569; font-weight: 600;">
                                        {{ number_format($item['pesan_qty'], 0, ',', '.') }} {{ $item['satuan_nm'] }}
                                    </td>
                                    <td style="text-align: right; color: #0284c7; font-weight: 600;">
                                        {{ number_format($item['kirim_qty_old'], 0, ',', '.') }}
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: #0f172a;">
                                        {{ number_format($sisa, 0, ',', '.') }}
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge-stok {{ $isStockOk ? 'cukup' : 'kurang' }}">
                                            {{ $isStockOk ? '✓ Tersedia' : '⚠ Stok Kurang' }} ({{ number_format($stokFg, 0, ',', '.') }})
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <input type="number" step="any" min="0" max="{{ $sisa }}"
                                               name="items[{{ $index }}][kirim_qty]"
                                               value="{{ old("items.{$index}.kirim_qty", $sisa) }}"
                                               class="input-qty-kirim"
                                               data-sisa="{{ $sisa }}"
                                               data-stok="{{ $stokFg }}"
                                               required>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2.5rem; color: #94a3b8;">
                                    Silakan pilih dokumen PO Penjualan di atas untuk memuat rincian barang.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- FOOTER SUMMARY BAR --}}
            <div class="sj-table-footer-bar">
                <div class="sj-footer-left">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Sistem otomatis memilih batch/lot produksi terlama yang belum kadaluarsa (FEFO) saat dokumen disimpan.</span>
                </div>

                <div class="sj-footer-right">
                    <div class="sj-total-display">
                        <div class="sj-total-label">Total Karton Dikirim:</div>
                        <div id="totalKirimQtyDisplay" class="sj-total-val">0 Karton</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BOTTOM ACTION BAR --}}
        <div class="sj-action-bar">
            <a href="{{ route('penjualan.pengeluaran.index') }}" class="btn-sj-cancel">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>Batal</span>
            </a>

            <button type="submit" class="btn-sj-submit">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Proses Pengeluaran &amp; Terbitkan Surat Jalan</span>
            </button>
        </div>

    </form>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/penjualan/pengeluaran/pengeluaran-create.js') }}"></script>
@endpush
