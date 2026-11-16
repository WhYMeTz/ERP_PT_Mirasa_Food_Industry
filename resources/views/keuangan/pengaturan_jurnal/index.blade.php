@extends('layouts.app')

@section('title', 'Pemetaan Jurnal Otomatis')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/pengaturan-jurnal/pengaturan-jurnal.css') }}">
@endpush

@section('content')
<div class="settings-container">
    {{-- ALERT NOTIFIKASI --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-bottom: 1.25rem; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; border-radius: 8px; padding: 0.75rem 1.25rem;">
            <strong>Berhasil!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-bottom: 1.25rem; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 8px; padding: 0.75rem 1.25rem;">
            <strong>Perhatian:</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('keuangan.pengaturan_jurnal.update') }}" method="POST" id="formPengaturanJurnal">
        @csrf

        {{-- HEADER --}}
        <div class="settings-header">
            <div>
                <div class="settings-header-title">
                    <span style="font-size: 1.35rem;">⚙️</span>
                    <h1>Pemetaan Akun Jurnal Otomatis</h1>
                </div>
                <div class="settings-header-desc">
                    Tentukan rekening akun debet/kredit dan format keterangan jurnal umum yang diterbitkan otomatis oleh sistem.
                </div>
            </div>

            <div>
                <button type="submit" class="header-action-btn">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan Pengaturan</span>
                </button>
            </div>
        </div>

        {{-- TAB SWITCHER --}}
        <div class="settings-tabs">
            <button type="button" class="tab-btn active" data-tab="tab-hpp">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <span>1. Biaya Produksi &amp; HPP WIP</span>
            </button>
            <button type="button" class="tab-btn" data-tab="tab-pembelian">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>2. Pembelian &amp; Hutang Usaha (AP)</span>
            </button>
        </div>

        {{-- TAB 1: HPP PRODUKSI WIP --}}
        @php
            $hppSettings = $settingsGrouped->get('HPP_WIP', collect())->keyBy('kunci');
            $hppTemplateVal = old('settings.HPP_WIP_TEMPLATE_KET', $hppSettings['HPP_WIP_TEMPLATE_KET']->nilai_text ?? 'Biaya Produksi & HPP WIP ({tgl}) - Shift {shift} ({total_kg} kg WIP)');
            
            $hppPresetStandard = 'Biaya Produksi & HPP WIP ({tgl}) - Shift {shift} ({total_kg} kg WIP)';
            $hppPresetSimple = 'Biaya Produksi & HPP WIP ({tgl}) - Shift {shift}';
            $isHppCustom = ($hppTemplateVal !== $hppPresetStandard && $hppTemplateVal !== $hppPresetSimple);
        @endphp
        <div class="tab-content active" id="tab-hpp">
            <div class="module-card">
                <div class="module-card-header">
                    <div>
                        <h2 class="module-card-title">
                            <span style="color: #059669;">●</span>
                            <span>Pemetaan Akun Jurnal HPP WIP (Keripik Curah)</span>
                        </h2>
                        <div class="module-card-desc">Jurnal ini otomatis terbit saat Tim Keuangan memfinalisasi kalkulasi HPP harian.</div>
                    </div>
                </div>

                {{-- TABEL PEMETAAN AKUN FULL WIDTH --}}
                <div class="table-responsive">
                    <table class="mapping-table">
                        <thead>
                            <tr>
                                <th style="width: 32%;">Komponen Biaya Produksi</th>
                                <th style="width: 12%; text-align: center;">Posisi</th>
                                <th style="width: 56%;">Rekening Akun Buku Besar (COA)</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- 1. WIP DEBET --}}
                            @php $sWip = $hppSettings['HPP_WIP_AKUN_WIP'] ?? null; @endphp
                            <tr>
                                <td>
                                    <div class="pos-title">Persediaan Hasil WIP / Keripik Curah</div>
                                    <div class="pos-sub">Total akumulasi biaya produksi yang dikapitalisasi ke persediaan</div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="side-badge debet">DEBET</span>
                                </td>
                                <td>
                                    @if($sWip)
                                        <select name="settings[{{ $sWip->kunci }}]" class="coa-select">
                                            @foreach($coaList as $coa)
                                                <option value="{{ $coa->coa_id }}" {{ $sWip->nilai_coa_id == $coa->coa_id ? 'selected' : '' }}>
                                                    {{ $coa->kode_akun }} - {{ $coa->nama_akun }} ({{ $coa->kategori_akun }})
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                            </tr>

                            {{-- 2. BAHAN BAKU KREDIT --}}
                            @php $sBahan = $hppSettings['HPP_WIP_AKUN_BAHAN'] ?? null; @endphp
                            <tr>
                                <td>
                                    <div class="pos-title">Persediaan Bahan Baku &amp; Penolong</div>
                                    <div class="pos-sub">Pengeluaran fisik singkong, minyak, dan bumbu terpakai</div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="side-badge kredit">KREDIT</span>
                                </td>
                                <td>
                                    @if($sBahan)
                                        <select name="settings[{{ $sBahan->kunci }}]" class="coa-select">
                                            @foreach($coaList as $coa)
                                                <option value="{{ $coa->coa_id }}" {{ $sBahan->nilai_coa_id == $coa->coa_id ? 'selected' : '' }}>
                                                    {{ $coa->kode_akun }} - {{ $coa->nama_akun }} ({{ $coa->kategori_akun }})
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                            </tr>

                            {{-- 3. GAS CNG KREDIT --}}
                            @php $sGas = $hppSettings['HPP_WIP_AKUN_GAS'] ?? null; @endphp
                            <tr>
                                <td>
                                    <div class="pos-title">Pemakaian Gas Alam CNG Pabrik</div>
                                    <div class="pos-sub">Akrual kewajiban harian gas sebelum tagihan bulanan terbit</div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="side-badge kredit">KREDIT</span>
                                </td>
                                <td>
                                    @if($sGas)
                                        <select name="settings[{{ $sGas->kunci }}]" class="coa-select">
                                            @foreach($coaList as $coa)
                                                <option value="{{ $coa->coa_id }}" {{ $sGas->nilai_coa_id == $coa->coa_id ? 'selected' : '' }}>
                                                    {{ $coa->kode_akun }} - {{ $coa->nama_akun }} ({{ $coa->kategori_akun }})
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                            </tr>

                            {{-- 4. TENAGA KERJA KREDIT --}}
                            @php $sTk = $hppSettings['HPP_WIP_AKUN_TENAGA_KERJA'] ?? null; @endphp
                            <tr>
                                <td>
                                    <div class="pos-title">Upah Tenaga Kerja Langsung (BTKL)</div>
                                    <div class="pos-sub">Alokasi gaji dan upah borongan tim penggorengan harian</div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="side-badge kredit">KREDIT</span>
                                </td>
                                <td>
                                    @if($sTk)
                                        <select name="settings[{{ $sTk->kunci }}]" class="coa-select">
                                            @foreach($coaList as $coa)
                                                <option value="{{ $coa->coa_id }}" {{ $sTk->nilai_coa_id == $coa->coa_id ? 'selected' : '' }}>
                                                    {{ $coa->kode_akun }} - {{ $coa->nama_akun }} ({{ $coa->kategori_akun }})
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                            </tr>

                            {{-- 5. OVERHEAD KREDIT --}}
                            @php $sFoh = $hppSettings['HPP_WIP_AKUN_OVERHEAD'] ?? null; @endphp
                            <tr>
                                <td>
                                    <div class="pos-title">Biaya Overhead Pabrik (FOH)</div>
                                    <div class="pos-sub">Alokasi beban listrik, air, dan pemeliharaan jika dibebankan</div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="side-badge kredit">KREDIT</span>
                                </td>
                                <td>
                                    @if($sFoh)
                                        <select name="settings[{{ $sFoh->kunci }}]" class="coa-select">
                                            @foreach($coaList as $coa)
                                                <option value="{{ $coa->coa_id }}" {{ $sFoh->nilai_coa_id == $coa->coa_id ? 'selected' : '' }}>
                                                    {{ $coa->kode_akun }} - {{ $coa->nama_akun }} ({{ $coa->kategori_akun }})
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- FORMAT KETERANGAN SIMPEL & FITUR CEPAT --}}
                <div class="desc-section">
                    <div class="desc-title">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                        <span>Format Tulisan Keterangan di Jurnal Umum</span>
                    </div>
                    <div class="desc-subtitle">Pilih gaya ringkasan yang ingin ditampilkan di buku jurnal umum:</div>

                    <div class="preset-options">
                        <label class="preset-item">
                            <input type="radio" name="preset_hpp" value="{{ $hppPresetStandard }}" {{ (!$isHppCustom && $hppTemplateVal === $hppPresetStandard) ? 'checked' : '' }}>
                            <div>
                                <span class="preset-label">Format Lengkap (Direkomendasikan)</span>
                                <div class="preset-sample">Menampilkan tanggal produksi, shift kerja, dan total kg hasil.</div>
                            </div>
                        </label>

                        <label class="preset-item">
                            <input type="radio" name="preset_hpp" value="{{ $hppPresetSimple }}" {{ (!$isHppCustom && $hppTemplateVal === $hppPresetSimple) ? 'checked' : '' }}>
                            <div>
                                <span class="preset-label">Format Ringkas</span>
                                <div class="preset-sample">Hanya menampilkan tanggal produksi dan shift kerja.</div>
                            </div>
                        </label>

                        <label class="preset-item">
                            <input type="radio" name="preset_hpp" value="CUSTOM" {{ $isHppCustom ? 'checked' : '' }}>
                            <div>
                                <span class="preset-label">Kustom Manual</span>
                                <div class="preset-sample">Ketik susunan kalimat sendiri dan gunakan tombol cepat di bawah.</div>
                            </div>
                        </label>
                    </div>

                    {{-- KOTAK KUSTOM MANUAL & TOMBOL CEPAT --}}
                    <div class="custom-template-box" id="customBox_HPP_WIP" style="{{ $isHppCustom ? '' : 'display: none;' }}">
                        <div class="custom-box-header">
                            <span class="custom-box-title">Susunan Kalimat Keterangan:</span>
                            <div class="quick-tags-wrapper">
                                <span class="quick-tag-label">⚡ Tombol Cepat:</span>
                                <button type="button" class="quick-tag-btn" data-target="setting_HPP_WIP_TEMPLATE_KET" data-tag="{tgl}">+ Tanggal</button>
                                <button type="button" class="quick-tag-btn" data-target="setting_HPP_WIP_TEMPLATE_KET" data-tag="{shift}">+ Shift</button>
                                <button type="button" class="quick-tag-btn" data-target="setting_HPP_WIP_TEMPLATE_KET" data-tag="{total_kg}">+ Total Kg</button>
                                <button type="button" class="quick-tag-btn" data-target="setting_HPP_WIP_TEMPLATE_KET" data-tag="{lini}">+ Lini Produksi</button>
                            </div>
                        </div>
                        <input type="text" name="settings[HPP_WIP_TEMPLATE_KET]" id="setting_HPP_WIP_TEMPLATE_KET" class="template-input" value="{{ $hppTemplateVal }}" placeholder="Ketik format keterangan...">
                    </div>

                    {{-- PREVIEW NYATA (REALTIME LIVE UPDATE SAAT KETIK) --}}
                    <div class="preview-badge-box">
                        <div class="preview-badge-header">
                            <div class="preview-badge-title">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <span>Contoh Hasil di Jurnal Umum</span>
                            </div>
                            <span class="preview-live-indicator">● LIVE UPDATE LANGSUNG</span>
                        </div>
                        <div class="preview-badge-text" id="preview_HPP_WIP_TEMPLATE_KET">-</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 2: PEMBELIAN & HUTANG USAHA (AP) --}}
        @php
            $apSettings = $settingsGrouped->get('PEMBELIAN', collect())->keyBy('kunci');
            $apTemplateVal = old('settings.PEMBELIAN_TEMPLATE_KET', $apSettings['PEMBELIAN_TEMPLATE_KET']->nilai_text ?? '{supplier} - {qty} {satuan} {nama_barang} @{harga}');
            
            $apPresetStandard = '{supplier} - {qty} {satuan} {nama_barang} @{harga}';
            $apPresetSimple = '{supplier} - Faktur {no_faktur}';
            $isApCustom = ($apTemplateVal !== $apPresetStandard && $apTemplateVal !== $apPresetSimple);
        @endphp
        <div class="tab-content" id="tab-pembelian">
            <div class="module-card">
                <div class="module-card-header">
                    <div>
                        <h2 class="module-card-title">
                            <span style="color: #4f46e5;">●</span>
                            <span>Pemetaan Akun Jurnal Pembelian &amp; Hutang Usaha (AP)</span>
                        </h2>
                        <div class="module-card-desc">Jurnal ini otomatis terbit saat tagihan pembelian dari supplier dicatat ke sistem.</div>
                    </div>
                    <div>
                        <a href="{{ route('master.pajak.index') }}" target="_blank" class="quick-tag-btn" style="text-decoration: none; padding: 0.35rem 0.75rem; background: #ffffff; border: 1.5px solid #cbd5e1; font-size: 0.78rem;">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span>Kelola / Tambah Master Pajak ↗</span>
                        </a>
                    </div>
                </div>

                {{-- TABEL PEMETAAN AKUN FULL WIDTH --}}
                <div class="table-responsive">
                    <table class="mapping-table">
                        <thead>
                            <tr>
                                <th style="width: 32%;">Komponen Transaksi Pembelian</th>
                                <th style="width: 12%; text-align: center;">Posisi</th>
                                <th style="width: 56%;">Rekening Akun Buku Besar (COA)</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- 1. PERSEDIAAN DEBET --}}
                            @php $sPersediaan = $apSettings['PEMBELIAN_AKUN_PERSEDIAAN'] ?? null; @endphp
                            <tr>
                                <td>
                                    <div class="pos-title">Persediaan Bahan Baku / Penolong Masuk</div>
                                    <div class="pos-sub">Nilai pokok barang yang dibeli (DPP sebelum pajak)</div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="side-badge debet">DEBET</span>
                                </td>
                                <td>
                                    @if($sPersediaan)
                                        <select name="settings[{{ $sPersediaan->kunci }}]" class="coa-select">
                                            @foreach($coaList as $coa)
                                                <option value="{{ $coa->coa_id }}" {{ $sPersediaan->nilai_coa_id == $coa->coa_id ? 'selected' : '' }}>
                                                    {{ $coa->kode_akun }} - {{ $coa->nama_akun }} ({{ $coa->kategori_akun }})
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                            </tr>

                            {{-- 2. HUTANG KREDIT --}}
                            @php $sHutang = $apSettings['PEMBELIAN_AKUN_HUTANG'] ?? null; @endphp
                            <tr>
                                <td>
                                    <div class="pos-title">Hutang Usaha ke Supplier</div>
                                    <div class="pos-sub">Kewajiban pembayaran tagihan pembelian yang jatuh tempo</div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="side-badge kredit">KREDIT</span>
                                </td>
                                <td>
                                    @if($sHutang)
                                        <select name="settings[{{ $sHutang->kunci }}]" class="coa-select">
                                            @foreach($coaList as $coa)
                                                <option value="{{ $coa->coa_id }}" {{ $sHutang->nilai_coa_id == $coa->coa_id ? 'selected' : '' }}>
                                                    {{ $coa->kode_akun }} - {{ $coa->nama_akun }} ({{ $coa->kategori_akun }})
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- INFO AKUN PAJAK DI MASTER PAJAK --}}
                <div style="margin: 0.875rem 1.5rem 0.25rem; padding: 0.75rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-size: 1.1rem;">📌</span>
                        <div>
                            <div style="font-weight: 700; color: #1e293b; font-size: 0.8125rem;">Pengaturan Akun Pajak (PPN &amp; Potongan PPh)</div>
                            <div style="font-size: 0.75rem; color: #64748b;">Setiap jenis pajak (PPN, PPh 22, PPh 23, PPh 4(2), PPh 21, dll.) rekening COA-nya dikelola langsung di menu <strong>Master Data &gt; Master Pajak &amp; Tarif</strong>.</div>
                        </div>
                    </div>
                    <a href="{{ route('master.pajak.index') }}" target="_blank" class="quick-tag-btn" style="text-decoration: none; padding: 0.35rem 0.75rem; background: #ffffff; border: 1.5px solid #cbd5e1; font-size: 0.78rem;">
                        <span>Buka Master Pajak ↗</span>
                    </a>
                </div>

                {{-- FORMAT KETERANGAN SIMPEL & FITUR CEPAT --}}
                <div class="desc-section">
                    <div class="desc-title">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                        <span>Format Tulisan Keterangan di Jurnal Umum</span>
                    </div>
                    <div class="desc-subtitle">Pilih gaya ringkasan yang ingin ditampilkan di buku jurnal umum:</div>

                    <div class="preset-options">
                        <label class="preset-item">
                            <input type="radio" name="preset_ap" value="{{ $apPresetStandard }}" {{ (!$isApCustom && $apTemplateVal === $apPresetStandard) ? 'checked' : '' }}>
                            <div>
                                <span class="preset-label">Format Lengkap (Direkomendasikan)</span>
                                <div class="preset-sample">Menampilkan nama supplier, kuantitas barang, dan harga per satuan.</div>
                            </div>
                        </label>

                        <label class="preset-item">
                            <input type="radio" name="preset_ap" value="{{ $apPresetSimple }}" {{ (!$isApCustom && $apTemplateVal === $apPresetSimple) ? 'checked' : '' }}>
                            <div>
                                <span class="preset-label">Format Ringkas Faktur</span>
                                <div class="preset-sample">Hanya menampilkan nama supplier dan nomor faktur.</div>
                            </div>
                        </label>

                        <label class="preset-item">
                            <input type="radio" name="preset_ap" value="CUSTOM" {{ $isApCustom ? 'checked' : '' }}>
                            <div>
                                <span class="preset-label">Kustom Manual</span>
                                <div class="preset-sample">Ketik susunan kalimat sendiri dan gunakan tombol cepat di bawah.</div>
                            </div>
                        </label>
                    </div>

                    {{-- KOTAK KUSTOM MANUAL & TOMBOL CEPAT --}}
                    <div class="custom-template-box" id="customBox_PEMBELIAN" style="{{ $isApCustom ? '' : 'display: none;' }}">
                        <div class="custom-box-header">
                            <span class="custom-box-title">Susunan Kalimat Keterangan:</span>
                            <div class="quick-tags-wrapper">
                                <span class="quick-tag-label">⚡ Tombol Cepat:</span>
                                <button type="button" class="quick-tag-btn" data-target="setting_PEMBELIAN_TEMPLATE_KET" data-tag="{supplier}">+ Supplier</button>
                                <button type="button" class="quick-tag-btn" data-target="setting_PEMBELIAN_TEMPLATE_KET" data-tag="{nama_barang}">+ Nama Barang</button>
                                <button type="button" class="quick-tag-btn" data-target="setting_PEMBELIAN_TEMPLATE_KET" data-tag="{qty}">+ Qty</button>
                                <button type="button" class="quick-tag-btn" data-target="setting_PEMBELIAN_TEMPLATE_KET" data-tag="{satuan}">+ Satuan</button>
                                <button type="button" class="quick-tag-btn" data-target="setting_PEMBELIAN_TEMPLATE_KET" data-tag="{harga}">+ Harga</button>
                                <button type="button" class="quick-tag-btn" data-target="setting_PEMBELIAN_TEMPLATE_KET" data-tag="{no_faktur}">+ No Faktur</button>
                            </div>
                        </div>
                        <input type="text" name="settings[PEMBELIAN_TEMPLATE_KET]" id="setting_PEMBELIAN_TEMPLATE_KET" class="template-input" value="{{ $apTemplateVal }}" placeholder="Ketik format keterangan...">
                    </div>

                    {{-- PREVIEW NYATA (REALTIME LIVE UPDATE SAAT KETIK) --}}
                    <div class="preview-badge-box">
                        <div class="preview-badge-header">
                            <div class="preview-badge-title">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <span>Contoh Hasil di Jurnal Umum</span>
                            </div>
                            <span class="preview-live-indicator">● LIVE UPDATE LANGSUNG</span>
                        </div>
                        <div class="preview-badge-text" id="preview_PEMBELIAN_TEMPLATE_KET">-</div>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/keuangan/pengaturan-jurnal/pengaturan-jurnal.js') }}"></script>
@endpush
