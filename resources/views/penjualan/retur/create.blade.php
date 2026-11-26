@extends('layouts.app')

@section('title', 'Pencatatan Retur Penjualan - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/po/po-workstation.css') }}?v={{ filemtime(public_path('css/gudang/po/po-workstation.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/penjualan/so/so-form.css') }}?v={{ filemtime(public_path('css/penjualan/so/so-form.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/penjualan/retur/retur-create.css') }}?v={{ filemtime(public_path('css/penjualan/retur/retur-create.css')) }}">
@endpush

@section('content')
<div class="so-form-container">
    {{-- PAGE HEADER & TOMBOL KEMBALI --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <a href="{{ $selectedSo ? route('penjualan.so.show', $selectedSo->so_id) : route('penjualan.retur.index') }}" class="so-back-link">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Daftar Retur Penjualan
            </a>
            <div class="so-title-row">
                <h1 class="so-page-title">Pencatatan Retur Penjualan</h1>
            </div>
            <p class="so-page-subtitle">
                Catat pengembalian barang jadi dari customer, tentukan perlakuan fisik (stok kembali atau pemusnahan), dan penyesuaian komersial.
            </p>
        </div>

        {{-- Live KPI Widget Ramping di Siku Kanan Atas --}}
        <div class="po-live-kpi-widget">
            <div>
                <div class="po-live-kpi-title">TOTAL NILAI RETUR:</div>
                <div class="po-live-kpi-val" id="topLiveReturTotal" style="color: #dc2626;">Rp 0</div>
            </div>
            <div style="height: 28px; width: 1.5px; background: #e2e8f0;"></div>
            <div style="font-size: 0.775rem; line-height: 1.35; color: #475569;">
                <div>Total Qty: <strong id="topLiveQtyRetur" style="color: #0f172a; font-family: monospace;">0 Karton</strong></div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 0.7rem; color: #dc2626; font-weight: 700;">Pengembalian</span>
                    <span class="badge" style="background: #fef2f2; color: #991b1b; font-size: 0.65rem;">Retur Resmi</span>
                </div>
            </div>
        </div>
    </div>

    @if (session('error'))
        <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.15rem; margin-bottom: 0.85rem; color: #991b1b; font-size: 0.85rem;">
            <strong>Peringatan:</strong> {{ session('error') }}
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.15rem; margin-bottom: 0.85rem; color: #991b1b;">
            <div style="font-weight: 700; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Terdapat kesalahan pada isian form:
            </div>
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.8rem;">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('penjualan.retur.store') }}" method="POST" id="returCreateForm">
        @csrf

        {{-- 1. TOOLBAR PARAMETER RETUR (UNIFIED COMPACT CARD) --}}
        <div class="po-toolbar-card" style="margin-bottom: 1.25rem;">
            {{-- Baris Atas Header Dokumen --}}
            <div class="po-mode-toolbar-wrap">
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <span style="font-size: 0.725rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.04em;">
                        Sumber Dokumen:
                    </span>
                    <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 0.725rem;">
                        Sales Order (SO Terkirim)
                    </span>
                </div>

                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 0.725rem; color: #64748b;">
                        Tipe Form: <strong style="color: #0f172a;">Retur Penjualan Komersial</strong>
                    </span>
                </div>
            </div>

            {{-- Grid Parameter Terpadu --}}
            <div style="padding: 1.15rem 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                {{-- Baris 1: Dokumen Sumber --}}
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem;">
                    <div>
                        <label class="po-field-label" for="so_id">
                            Pilih Dokumen Pesanan (SO Terkirim) <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="so_id" id="so_id" class="po-field-input" required style="font-size: 0.8125rem;">
                            <option value="">-- Pilih Dokumen SO Terkirim --</option>
                            @foreach ($eligibleSos as $so)
                                <option value="{{ $so->so_id }}" {{ ($selectedSo && $selectedSo->so_id == $so->so_id) || old('so_id') == $so->so_id ? 'selected' : '' }}>
                                    {{ $so->so_no }} &mdash; {{ $so->customer?->customer_nm ?: 'Pelanggan Umum' }} ({{ \Carbon\Carbon::parse($so->so_tgl)->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                        <span style="font-size: 0.7rem; color: #64748b; margin-top: 3px; display: block;">
                            Menampilkan seluruh SO berstatus Terkirim (Partial/Completed) yang memenuhi syarat retur.
                        </span>
                    </div>

                    <div>
                        <label class="po-field-label">Surat Jalan Terkait</label>
                        <input type="text" class="po-field-input" readonly id="infoSjNo" value="{{ $selectedSo?->surat_jalan_no ?: ($selectedSo?->pengeluaran?->first()?->pengeluaran_no ?: '-') }}" style="background: #f8fafc; font-family: monospace; color: #475569; font-weight: 600;">
                    </div>

                    <div>
                        <label class="po-field-label">Faktur Penjualan Terkait</label>
                        <input type="text" class="po-field-input" readonly id="infoFakturNo" value="{{ $selectedSo?->faktur_no ?: ($selectedSo?->faktur?->first()?->faktur_no ?: '-') }}" style="background: #f8fafc; font-family: monospace; color: #475569; font-weight: 600;">
                    </div>
                </div>

                {{-- Detail Pelanggan Strip --}}
                <div id="soInfoBox" style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; padding: 0.65rem 1rem; display: {{ $selectedSo ? 'flex' : 'none' }}; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; font-size: 0.8rem;">
                    <div>
                        <span style="color: #0369a1; font-weight: 700;">Nomor SO:</span>
                        <strong id="infoSoNo" style="font-family: monospace; color: #0c4a6e; margin-left: 4px;">{{ $selectedSo?->so_no ?: '-' }}</strong>
                    </div>
                    <div>
                        <span style="color: #0369a1; font-weight: 700;">Mitra Customer:</span>
                        <strong id="infoCustNm" style="color: #0c4a6e; margin-left: 4px;">{{ $selectedSo?->customer?->customer_nm ?: '-' }}</strong>
                    </div>
                    <div>
                        <span style="color: #0369a1; font-weight: 700;">Tanggal Terbit SO:</span>
                        <span id="infoSoTgl" style="font-family: monospace; color: #0c4a6e; margin-left: 4px;">{{ $selectedSo?->so_tgl ? \Carbon\Carbon::parse($selectedSo->so_tgl)->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div>
                        <span style="color: #0369a1; font-weight: 700;">Status Order:</span>
                        <span class="badge" style="background: #dcfce7; color: #15803d; font-weight: 700; margin-left: 4px;">{{ $selectedSo?->status_cd ?? '-' }}</span>
                    </div>
                </div>

                <div style="height: 1px; background: #f1f5f9; margin: 0.15rem 0;"></div>

                {{-- Baris 2: Parameter Retur & Kebijakan Finansial --}}
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
                    <div>
                        <label class="po-field-label" for="retur_tgl">
                            Tanggal Retur <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" name="retur_tgl" id="retur_tgl" class="po-field-input" value="{{ old('retur_tgl', date('Y-m-d')) }}" required>
                    </div>

                    <div>
                        <label class="po-field-label" for="gudang_id">
                            Gudang Penerima Masuk <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="gudang_id" id="gudang_id" class="po-field-input" required>
                            @foreach ($gudangs as $gdg)
                                <option value="{{ $gdg->gudang_id }}" {{ old('gudang_id', $defaultGudangId) == $gdg->gudang_id ? 'selected' : '' }}>
                                    {{ $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                                </option>
                            @endforeach
                        </select>
                        <span style="font-size: 0.7rem; color: #64748b; margin-top: 3px; display: block;">Lokasi barang retur yang layak simpan.</span>
                    </div>

                    <div>
                        <label class="po-field-label" for="tindakan_cd">
                            Tindakan Komersial Tagihan <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="tindakan_cd" id="tindakan_cd" class="po-field-input" required>
                            <option value="POTONG_TAGIHAN" {{ old('tindakan_cd', 'POTONG_TAGIHAN') == 'POTONG_TAGIHAN' ? 'selected' : '' }}>
                                Potong Tagihan Faktur (Pengurangan Piutang)
                            </option>
                            <option value="TUKAR_GULING" {{ old('tindakan_cd') == 'TUKAR_GULING' ? 'selected' : '' }}>
                                Tukar Guling / Ganti Barang Fisik Baru
                            </option>
                        </select>
                        <span style="font-size: 0.7rem; color: #64748b; margin-top: 3px; display: block;">Kebijakan finansial terhadap nilai retur.</span>
                    </div>

                    <div>
                        <label class="po-field-label" for="alasan_txt">
                            Alasan / Catatan Pengembalian
                        </label>
                        <input type="text" name="alasan_txt" id="alasan_txt" class="po-field-input" placeholder="Contoh: Kemasan penyok saat ekspedisi" value="{{ old('alasan_txt') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. TABEL RINCIAN BARANG (EXCEL GRID ENTERPRISE TABLE) --}}
        <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow: hidden; margin-bottom: 1.25rem;">
            <div style="padding: 0.75rem 1.15rem; background: #f8fafc; border-bottom: 1.5px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="18" height="18" fill="none" stroke="#d97706" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span style="font-weight: 700; color: #0f172a; font-size: 0.9rem;">Rincian Barang &amp; Disposisi Fisik</span>
                </div>
                <span style="font-size: 0.75rem; color: #64748b;">
                    Tentukan apakah barang <strong>Disimpan Kembali</strong> ke stok atau <strong>Dimusnahkan Resmi</strong>.
                </span>
            </div>

            <div style="overflow-x: auto;">
                <table class="excel-grid-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 35px; text-align: center;">No</th>
                            <th style="min-width: 200px; text-align: left;">Produk Jadi (FG)</th>
                            <th style="width: 85px; text-align: right;">Dikirim</th>
                            <th style="width: 95px; text-align: right;">Bisa Diretur</th>
                            <th style="width: 110px; text-align: right;">Qty Retur <span style="color:#ef4444;">*</span></th>
                            <th style="width: 75px; text-align: center;">Satuan</th>
                            <th style="width: 115px; text-align: right;">Harga Satuan</th>
                            <th style="width: 240px; text-align: left;">Perlakuan Fisik (Disposisi)</th>
                            <th style="width: 160px; text-align: left;">Kondisi Barang</th>
                            <th style="width: 130px; text-align: right;">Subtotal Nilai</th>
                        </tr>
                    </thead>
                    <tbody id="returItemsTbody">
                        @if ($selectedSo && count($itemsData) > 0)
                            @foreach ($itemsData as $idx => $item)
                                <tr class="retur-item-row" data-sodtl-id="{{ $item['sodtl_id'] }}" data-price="{{ $item['effective_price'] }}" data-max="{{ $item['max_returnable'] }}">
                                    <td style="text-align: center; color: #64748b; font-weight: 700;">{{ $idx + 1 }}</td>
                                    <td>
                                        <strong style="color: #0f172a; display: block;">{{ $item['barang_nm'] }}</strong>
                                        <span style="font-size: 0.725rem; color: #0284c7; font-family: monospace;">{{ $item['barang_cd'] }}</span>
                                        <input type="hidden" name="items[{{ $idx }}][sodtl_id]" value="{{ $item['sodtl_id'] }}">
                                        <div style="margin-top: 4px;">
                                            <input type="text" name="items[{{ $idx }}][batch_no]"
                                                   class="form-control"
                                                   value="{{ old("items.{$idx}.batch_no", $item['batch_no'] ?? '') }}"
                                                   placeholder="No. Batch"
                                                   title="Nomor Batch / Kode Produksi pada kemasan"
                                                   style="font-size: 0.7rem; font-family: monospace; height: 26px; padding: 2px 6px; border: 1.5px solid #cbd5e1; border-radius: 4px; max-width: 145px;">
                                        </div>
                                    </td>
                                    <td style="text-align: right; font-family: monospace; font-weight: 700; color: #475569;">
                                        {{ number_format($item['kirim_qty'], 0) }}
                                    </td>
                                    <td style="text-align: right; font-family: monospace; font-weight: 700; color: #059669;">
                                        {{ number_format($item['max_returnable'], 0) }} {{ $item['satuan_nm'] }}
                                    </td>
                                    <td style="width: 110px;">
                                        <input type="number" step="any" min="0" max="{{ $item['max_returnable'] }}"
                                            name="items[{{ $idx }}][qty_retur]"
                                            class="form-control qty-retur-input"
                                            value="{{ old("items.{$idx}.qty_retur", 0) }}"
                                            style="text-align: right; font-weight: 800; font-family: monospace; border: 1.5px solid #cbd5e1; border-radius: 5px; height: 32px;"
                                            data-idx="{{ $idx }}">
                                    </td>
                                    <td style="text-align: center; color: #475569; font-weight: 600;">
                                        {{ $item['satuan_nm'] }}
                                    </td>
                                    <td style="text-align: right; font-family: monospace; color: #475569;">
                                        {{ $item['formatted_price'] }}
                                    </td>
                                    <td style="width: 240px;">
                                        <div class="disposisi-card-group">
                                            <label class="disposisi-option opt-simpan">
                                                <input type="radio" name="items[{{ $idx }}][perlakuan_cd]" value="MASUK_PERSEDIAAN" {{ old("items.{$idx}.perlakuan_cd", 'MASUK_PERSEDIAAN') === 'MASUK_PERSEDIAAN' ? 'checked' : '' }} class="disposisi-radio" data-idx="{{ $idx }}">
                                                <span>Gunakan Kembali (Masuk Stok)</span>
                                            </label>
                                            <label class="disposisi-option opt-musnah">
                                                <input type="radio" name="items[{{ $idx }}][perlakuan_cd]" value="DIMUSNAHKAN" {{ old("items.{$idx}.perlakuan_cd") === 'DIMUSNAHKAN' ? 'checked' : '' }} class="disposisi-radio" data-idx="{{ $idx }}">
                                                <span>Dimusnahkan (Afkir / Rusak)</span>
                                            </label>
                                        </div>
                                    </td>
                                    <td style="width: 160px;">
                                        <select name="items[{{ $idx }}][kondisi_cd]" class="form-control" style="font-size: 0.775rem; border: 1.5px solid #cbd5e1; border-radius: 5px; height: 32px;">
                                            <option value="RUSAK_KEMASAN" {{ old("items.{$idx}.kondisi_cd") == 'RUSAK_KEMASAN' ? 'selected' : '' }}>Kemasan Rusak / Cacat</option>
                                            <option value="REMUK_MELEMPEM" {{ old("items.{$idx}.kondisi_cd") == 'REMUK_MELEMPEM' ? 'selected' : '' }}>Remuk / Melempem</option>
                                            <option value="KADALUARSA" {{ old("items.{$idx}.kondisi_cd") == 'KADALUARSA' ? 'selected' : '' }}>Kadaluarsa (Expired)</option>
                                            <option value="SALAH_KIRIM" {{ old("items.{$idx}.kondisi_cd") == 'SALAH_KIRIM' ? 'selected' : '' }}>Salah Varian / Lebih</option>
                                            <option value="REJECT_CUSTOMER" {{ old("items.{$idx}.kondisi_cd") == 'REJECT_CUSTOMER' ? 'selected' : '' }}>Reject Mitra / Customer</option>
                                        </select>
                                    </td>
                                    <td style="text-align: right; font-family: monospace; font-weight: 800; color: #0f172a;" class="row-subtotal">
                                        Rp 0
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 auto 0.5rem auto; display: block; opacity: 0.5;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Silakan pilih Dokumen Pesanan (SO) di atas untuk memuat rincian barang yang dapat diretur.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- FOOTER KALKULASI & TOTALS --}}
            <div style="background: #ffffff; border-top: 1.5px solid #cbd5e1; padding: 1.15rem 1.25rem; display: grid; grid-template-columns: 1fr 360px; gap: 1.5rem; align-items: start;">
                <div>
                    {{-- WARNING BANNER PEMUSNAHAN --}}
                    <div id="musnahWarningBanner" style="display: none; background: #fff5f5; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1rem; color: #991b1b;">
                        <div style="display: flex; gap: 0.65rem; align-items: flex-start;">
                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0; color: #dc2626;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <div>
                                <strong style="display: block; font-size: 0.85rem; color: #991b1b; margin-bottom: 2px;">
                                    Peringatan Pemusnahan Barang (<span id="countMusnahText">0</span> unit):
                                </strong>
                                <span style="font-size: 0.775rem; line-height: 1.4; color: #7f1d1d; display: block;">
                                    Barang yang ditandai <strong>Dimusnahkan</strong> tidak akan dimasukkan kembali ke persediaan gudang. Sistem akan otomatis menerbitkan <strong>Berita Acara Pemusnahan Barang Retur</strong> resmi untuk ditandatangani QC &amp; Pimpinan Pabrik.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD SUMMARY SEBELAH KANAN --}}
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1.15rem; display: flex; flex-direction: column; gap: 0.45rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: #475569;">
                        <span>Total Kuantitas Retur:</span>
                        <strong id="dispTotQtyRetur" style="color: #0f172a; font-family: monospace;">0 Karton / Satuan</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: #059669;">
                        <span>&bull; Disimpan ke Gudang:</span>
                        <strong id="dispTotQtyMasuk" style="font-family: monospace;">0 Karton</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: #dc2626;">
                        <span>&bull; Dimusnahkan:</span>
                        <strong id="dispTotQtyMusnah" style="font-family: monospace;">0 Karton</strong>
                    </div>
                    <div style="border-top: 1.5px dashed #cbd5e1; margin-top: 0.35rem; padding-top: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.825rem; font-weight: 700; color: #0f172a;">Total Nilai Retur:</span>
                        <span id="dispTotNilai" style="font-size: 1.15rem; font-weight: 800; color: #dc2626; font-family: monospace;">Rp 0</span>
                    </div>
                </div>
            </div>

            {{-- ACTION BUTTONS STICKY/FOOTER --}}
            <div style="background: #f8fafc; border-top: 1.5px solid #cbd5e1; padding: 0.85rem 1.25rem; display: flex; justify-content: flex-end; gap: 0.75rem; align-items: center;">
                <a href="{{ $selectedSo ? route('penjualan.so.show', $selectedSo->so_id) : route('penjualan.retur.index') }}" class="btn btn-secondary" style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #475569; font-weight: 700; font-size: 0.825rem; padding: 0.5rem 1.15rem; border-radius: 6px; text-decoration: none;">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary" style="background: #059669; border: 1px solid #047857; color: #ffffff; font-weight: 700; font-size: 0.85rem; padding: 0.55rem 1.35rem; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.45rem; box-shadow: 0 1px 3px rgba(5, 150, 105, 0.3);">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan &amp; Proses Retur</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/penjualan/retur/retur-create.js') }}?v={{ filemtime(public_path('js/penjualan/retur/retur-create.js')) }}"></script>
@endpush
