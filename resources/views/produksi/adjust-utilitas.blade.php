@extends('layouts.app')

@section('title', 'Penyesuaian Biaya Utilitas Bulanan - PT Mirasa Food Industry')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-adjust-utilitas.css') }}">
@endpush

@section('content')
@php
    $modalCount = (int) (($report ?? [])['count'] ?? 0);
    $modalTot = (($report ?? [])['totals'] ?? [
        'total_wip_qty' => 0,
        'cng_mmbtu' => 0,
        'listrik_air_telp_nilai' => 0,
        'cng_nilai' => 0,
    ]);

    $prodDays = collect($report['days'] ?? [])->filter(fn($d) => !empty($d['has_data']));
    $tarifListrikDefault = (float) ($fohRates['listrik'] ?? 223.80);
    $tarifCngDefault = 232500.00;
@endphp

{{-- ═════════════════════════════════════════════════════════════
     HEADER MODUL PRODUKSI & MANUFAKTUR (STANDAR ERP MIRASA)
     ═════════════════════════════════════════════════════════════ --}}
<div style="margin-bottom: 1.25rem;">
    {{-- TOMBOL KEMBALI DI ATAS SEBELAH KIRI (SESUAI STANDAR HALAMAN LAIN) --}}
    <a href="{{ route('produksi.rekap', ['tahun' => $year, 'bulan' => $month]) }}" 
       style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.45rem; font-weight: 600; transition: color 0.15s ease-in-out;"
       onmouseover="this.style.color='#0f172a'" 
       onmouseout="this.style.color='#64748b'"
       title="Kembali ke Buku Rekapitulasi HPP">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Kembali ke Buku Rekapitulasi HPP</span>
    </a>

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <h1 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
                Penyesuaian Biaya Utilitas Bulanan
            </h1>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.825rem; color: #64748b;">
                Alokasi tagihan faktur aktual bulanan (PLN &amp; Gas CNG) ke seluruh lembar produksi harian secara proporsional sesuai kaidah akuntansi manufaktur.
            </p>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            {{-- Filter Periode Kalender Terpadu (Format Standar Rekap HPP) --}}
            <form action="{{ route('produksi.adjust-utilitas') }}" method="GET" style="display: flex; gap: 0.35rem; align-items: center; background: #ffffff; padding: 0.2rem 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                <select name="bulan" class="form-control" style="font-size: 0.8rem; padding: 0.25rem 0.5rem; height: 30px; border: none; font-weight: 700; color: #1e293b;" onchange="this.form.submit()">
                    @foreach ($monthsList as $num => $nm)
                        <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }}>{{ $nm }}</option>
                    @endforeach
                </select>
                <select name="tahun" class="form-control" style="font-size: 0.8rem; padding: 0.25rem 0.5rem; height: 30px; border: none; font-weight: 700; color: #1e293b;" onchange="this.form.submit()">
                    @for ($y = date('Y'); $y >= 2024; $y--)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>
    </div>
</div>

{{-- NOTIFIKASI SUKSES & ERROR --}}
@if(session('success'))
    <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; color: #065f46; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
@endif

{{-- ═════════════════════════════════════════════════════════════
     4 KARTU METRIK OPERASIONAL & HPP (STANDAR EXECUTIVE-STAT-GRID)
     ═════════════════════════════════════════════════════════════ --}}
<div class="executive-stat-grid">
    {{-- METRIK 1: HARI KERJA & DOKUMEN PRODUKSI --}}
    <div class="executive-stat-card">
        <div class="executive-stat-card-header">
            <div>
                <span class="executive-stat-label">
                    Hari Kerja Produksi
                </span>
                <div class="executive-stat-value">
                    {{ $prodDays->count() }} Hari
                </div>
            </div>
            <div class="executive-stat-icon-box" style="background: #e0f2fe; color: #0284c7;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <div class="executive-stat-subtext">
            <span>{{ $modalCount }} Dokumen Produksi ({{ $monthsList[$month] }} {{ $year }})</span>
        </div>
    </div>

    {{-- METRIK 2: TOTAL OUTPUT WIP (BASIS ALOKASI LISTRIK) --}}
    <div class="executive-stat-card">
        <div class="executive-stat-card-header">
            <div>
                <span class="executive-stat-label" style="color: #059669;">
                    Total Output WIP
                </span>
                <div class="executive-stat-value" style="color: #059669;">
                    {{ number_format($modalTot['total_wip_qty'], 2, ',', '.') }} kg
                </div>
            </div>
            <div class="executive-stat-icon-box" style="background: #d1fae5; color: #059669;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
        <div class="executive-stat-subtext">
            <span style="color: #059669; font-weight: 600;">Basis Pembagi Proporsional Listrik PLN</span>
        </div>
    </div>

    {{-- METRIK 3: TOTAL PEMAKAIAN GAS CNG --}}
    <div class="executive-stat-card">
        <div class="executive-stat-card-header">
            <div>
                <span class="executive-stat-label" style="color: #d97706;">
                    Total Pemakaian Gas CNG
                </span>
                <div class="executive-stat-value" style="color: #d97706;">
                    {{ number_format($modalTot['cng_mmbtu'], 2, ',', '.') }} MMBTU
                </div>
            </div>
            <div class="executive-stat-icon-box" style="background: #fef3c7; color: #d97706;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
            </div>
        </div>
        <div class="executive-stat-subtext">
            <span style="color: #d97706; font-weight: 600;">Energi Boiler &amp; Penggorengan Keripik</span>
        </div>
    </div>

    {{-- METRIK 4: AKUMULASI BIAYA UTILITAS SAAT INI --}}
    <div class="executive-stat-card">
        <div class="executive-stat-card-header">
            <div>
                <span class="executive-stat-label" style="color: #7c3aed;">
                    Total Biaya Utilitas Terkini
                </span>
                <div class="executive-stat-value" style="font-family: monospace; font-size: 1.35rem;">
                    Rp {{ number_format($modalTot['listrik_air_telp_nilai'] + $modalTot['cng_nilai'], 0, ',', '.') }}
                </div>
            </div>
            <div class="executive-stat-icon-box" style="background: #f3e8ff; color: #7c3aed;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
        </div>
        <div class="executive-stat-subtext">
            <span>PLN: Rp {{ number_format($modalTot['listrik_air_telp_nilai'], 0, ',', '.') }} | Gas: Rp {{ number_format($modalTot['cng_nilai'], 0, ',', '.') }}</span>
        </div>
    </div>
</div>

{{-- KONTEN UTAMA PENYESUAIAN UTILITAS --}}
@if($modalCount == 0)
    <div class="card" style="padding: 3rem; text-align: center; color: #64748b; margin-bottom: 2rem;">
        <svg width="44" height="44" fill="none" stroke="#cbd5e1" viewBox="0 0 24 24" style="margin: 0 auto 0.75rem auto;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <div style="font-weight: 700; font-size: 1.05rem; color: #0f172a; margin-bottom: 0.25rem;">
            Tidak Ada Catatan Produksi
        </div>
        <p style="font-size: 0.825rem; margin: 0;">
            Belum terdapat lembar catatan produksi pada periode <strong>{{ $monthsList[$month] }} {{ $year }}</strong>. Silakan pilih periode bulan lain pada filter di atas.
        </p>
    </div>
@else
    {{-- ALERT VALIDASI (JIKA TIDAK ADA OPSI AKTIF DIPILIH) --}}
    <div id="alertValidateAdjust" style="display: none; background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 8px; padding: 0.85rem 1.25rem; color: #92400e; margin-bottom: 1.25rem; font-size: 0.85rem; font-weight: 600; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>Mohon aktifkan setidaknya salah satu opsi penyesuaian (Beban Listrik atau Beban Gas) sebelum menyimpan.</span>
        </div>
        <button type="button" onclick="document.getElementById('alertValidateAdjust').style.display='none'" style="background: none; border: none; color: #92400e; font-size: 1.2rem; cursor: pointer; line-height: 1;">&times;</button>
    </div>

    <form id="formMainAdjustUtilitas" action="{{ route('produksi.adjust-utilitas.store') }}" method="POST">
        @csrf
        <input type="hidden" name="tahun" value="{{ $year }}">
        <input type="hidden" name="bulan" value="{{ $month }}">
        <input type="hidden" name="redirect_to" value="adjust">

        {{-- ═════════════════════════════════════════════════════════════
             GRID FORM INPUT 2 SISI (LISTRIK PLN & GAS CNG)
             ═════════════════════════════════════════════════════════════ --}}
        <div class="utilitas-forms-grid">

            {{-- KARTU 1: LISTRIK & AIR --}}
            <div class="card utilitas-card-box">
                <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.85rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                    <label class="toggle-card-label" for="chk_adjust_listrik" style="margin: 0; display: inline-flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                        <div class="custom-switch">
                            <input type="checkbox" name="adjust_listrik" id="chk_adjust_listrik" value="1" checked>
                            <span class="switch-slider switch-slider-blue"></span>
                        </div>
                        <span style="font-size: 0.925rem; font-weight: 700; color: #0f172a;">1. Beban Listrik &amp; Air (PLN)</span>
                    </label>
                    <span class="badge" style="font-size: 0.725rem; font-weight: 700; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; padding: 0.2rem 0.55rem; border-radius: 4px;">
                        Alokasi Proporsional WIP
                    </span>
                </div>

                <div class="card-body" id="section_body_listrik" style="padding: 1.25rem;">
                    {{-- Mode Selector (Segmented Pills Standar) --}}
                    <div class="mode-pill-container" style="margin-bottom: 1.15rem;">
                        <label class="mode-pill-item active">
                            <input type="radio" name="mode_alokasi_listrik" value="total_tagihan" checked style="display: none;">
                            <span>Tagihan Sebulan (Proporsional WIP)</span>
                        </label>
                        <label class="mode-pill-item">
                            <input type="radio" name="mode_alokasi_listrik" value="tarif_per_kg" style="display: none;">
                            <span>Tarif Standar per Kg</span>
                        </label>
                    </div>

                    {{-- Input 1: Total Rekening PLN Sebulan (Default Standar) --}}
                    <div id="box_listrik_total_tagihan">
                        <label style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: 0.4rem;">
                            Total Tagihan Rekening Listrik PLN Sebulan:
                        </label>
                        <div class="input-addon-group">
                            <span class="addon-prefix">Rp</span>
                            <input type="number" step="1" min="0" 
                                   name="total_listrik_air" 
                                   id="input_total_listrik_tagihan" 
                                   class="form-control addon-input-field" 
                                   value="{{ old('total_listrik_air', $modalTot['listrik_air_telp_nilai'] > 0 ? round($modalTot['listrik_air_telp_nilai']) : round($modalTot['total_wip_qty'] * $tarifListrikDefault)) }}"
                                   oninput="recalculateLiveSimulation()">
                        </div>
                        <p style="font-size: 0.75rem; color: #64748b; margin: 0.45rem 0 0 0; line-height: 1.4;">
                            Total tagihan akan didistribusikan secara proporsional ke tiap hari kerja berdasarkan porsi kg output keripik WIP yang dihasilkan.
                        </p>
                    </div>

                    {{-- Input 2: Tarif Standar per Kg (Alternatif) --}}
                    <div id="box_listrik_tarif_kg" style="display: none;">
                        <label style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: 0.4rem;">
                            Tarif Listrik per Kg Keripik WIP:
                        </label>
                        <div class="input-addon-group">
                            <span class="addon-prefix">Rp</span>
                            <input type="number" step="0.01" min="0" 
                                   name="listrik_tarif_per_kg" 
                                   id="input_listrik_tarif_kg" 
                                   class="form-control addon-input-field" 
                                   value="{{ old('listrik_tarif_per_kg', $tarifListrikDefault) }}"
                                   oninput="recalculateLiveSimulation()">
                            <span class="addon-suffix">/ Kg</span>
                        </div>
                        <p style="font-size: 0.75rem; color: #64748b; margin: 0.45rem 0 0 0; line-height: 1.4;">
                            Mengalikan seluruh hari kerja di bulan ini dengan tarif flat: <code>Kg WIP &times; Rp/Kg</code>.
                        </p>
                    </div>
                </div>
            </div>

            {{-- KARTU 2: GAS ALAM (CNG) --}}
            <div class="card utilitas-card-box">
                <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.85rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                    <label class="toggle-card-label" for="chk_adjust_cng" style="margin: 0; display: inline-flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                        <div class="custom-switch">
                            <input type="checkbox" name="adjust_cng" id="chk_adjust_cng" value="1">
                            <span class="switch-slider switch-slider-amber"></span>
                        </div>
                        <span style="font-size: 0.925rem; font-weight: 700; color: #0f172a;">2. Beban Gas Alam CNG (Boiler &amp; Fryer)</span>
                    </label>
                    <span class="badge" style="font-size: 0.725rem; font-weight: 700; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 0.2rem 0.55rem; border-radius: 4px;">
                        Alokasi Pemakaian MMBTU
                    </span>
                </div>

                <div class="card-body" id="section_body_cng" style="padding: 1.25rem; opacity: 0.45;">
                    {{-- Mode Selector --}}
                    <div class="mode-pill-container" style="margin-bottom: 1.15rem;">
                        <label class="mode-pill-item active">
                            <input type="radio" name="mode_cng" value="update_tarif" checked style="display: none;">
                            <span>Tarif Resmi per MMBTU</span>
                        </label>
                        <label class="mode-pill-item">
                            <input type="radio" name="mode_cng" value="total_tagihan" style="display: none;">
                            <span>Total Tagihan Faktur Gas</span>
                        </label>
                    </div>

                    {{-- Input 1: Tarif Gas Resmi --}}
                    <div id="box_cng_tarif_resmi">
                        <label style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: 0.4rem;">
                            Tarif Gas Resmi per MMBTU:
                        </label>
                        <div class="input-addon-group">
                            <span class="addon-prefix">Rp</span>
                            <input type="number" step="0.01" min="0" 
                                   name="cng_tarif_baru" 
                                   id="input_cng_tarif_resmi" 
                                   class="form-control addon-input-field" 
                                   value="{{ old('cng_tarif_baru', $tarifCngDefault) }}"
                                   oninput="recalculateLiveSimulation()">
                            <span class="addon-suffix">/ MMBTU</span>
                        </div>
                        <p style="font-size: 0.75rem; color: #64748b; margin: 0.45rem 0 0 0; line-height: 1.4;">
                            Tiap hari produksi dihitung otomatis: <code>MMBTU Gas Aktual &times; Tarif Resmi Gas</code>.
                        </p>
                    </div>

                    {{-- Input 2: Total Faktur Gas Sebulan --}}
                    <div id="box_cng_total_tagihan" style="display: none;">
                        <label style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: 0.4rem;">
                            Total Faktur Gas Vendor Sebulan:
                        </label>
                        <div class="input-addon-group">
                            <span class="addon-prefix">Rp</span>
                            <input type="number" step="1" min="0" 
                                   name="total_cng_tagihan" 
                                   id="input_total_cng_tagihan" 
                                   class="form-control addon-input-field" 
                                   value="{{ old('total_cng_tagihan', round($modalTot['cng_nilai'])) }}"
                                   oninput="recalculateLiveSimulation()">
                        </div>
                        <p style="font-size: 0.75rem; color: #64748b; margin: 0.45rem 0 0 0; line-height: 1.4;">
                            Total tagihan faktur akan dibagi proporsional ke tiap hari kerja berdasarkan porsi MMBTU masing-masing.
                        </p>
                    </div>
                </div>
            </div>

        </div>

        {{-- ═════════════════════════════════════════════════════════════
             TABEL SIMULASI PRATINJAU INTERAKTIF (STANDAR ERP ENTERPRISE)
             ═════════════════════════════════════════════════════════════ --}}
        <div class="card" style="margin-bottom: 1.25rem; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); overflow: hidden;">
            <div class="card-header" style="background: #ffffff; padding: 0.85rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                <div>
                    <span style="font-size: 0.925rem; font-weight: 700; color: #0f172a; letter-spacing: -0.01em;">
                        Simulasi Alokasi Biaya Utilitas &amp; Dampak HPP Harian
                    </span>
                    <span style="font-size: 0.725rem; color: #64748b; margin-left: 0.4rem;">
                        Pratinjau kalkulasi real-time pada {{ $prodDays->count() }} hari kerja ({{ $modalCount }} dokumen produksi)
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.4rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; padding: 0.25rem 0.65rem; font-size: 0.75rem; font-weight: 700; color: #166534;">
                    <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #16a34a;"></span>
                    <span>Kalkulasi Real-Time Aktif</span>
                </div>
            </div>

            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table-compact-hpp" id="simGridTable"
                       data-month-wip="{{ (float) $modalTot['total_wip_qty'] }}"
                       data-month-mmbtu="{{ (float) $modalTot['cng_mmbtu'] }}"
                       data-month-days="{{ $prodDays->count() }}"
                       style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No</th>
                            <th style="width: 140px;">Tanggal &amp; Hari</th>
                            <th style="text-align: right; width: 130px;">Output WIP (kg)</th>
                            <th style="text-align: right; width: 120px;">Gas (MMBTU)</th>
                            <th style="text-align: right; color: #64748b; width: 150px;">Listrik Eksisting</th>
                            <th style="text-align: right; color: #0284c7; width: 160px;">Listrik Baru (Simulasi)</th>
                            <th style="text-align: right; color: #d97706; width: 160px;">Gas Baru (Simulasi)</th>
                            <th style="text-align: right; color: #059669; width: 160px;">Estimasi HPP/Kg Baru</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prodDays as $d)
                            <tr class="sim-data-row"
                                data-wip="{{ (float) $d['total_wip_qty'] }}"
                                data-mmbtu="{{ (float) $d['cng_mmbtu'] }}"
                                data-old-listrik="{{ (float) $d['listrik_air_telp_nilai'] }}"
                                data-old-cng="{{ (float) $d['cng_nilai'] }}"
                                data-old-total-biaya="{{ (float) $d['total_biaya_produksi'] }}">
                                <td style="text-align: center; color: #94a3b8; font-size: 0.775rem;">{{ $loop->iteration }}</td>
                                <td>
                                    <strong style="color: #0f172a;">{{ date('d/m/Y', strtotime($d['date'])) }}</strong>
                                    <span style="font-size: 0.725rem; color: #64748b; margin-left: 0.25rem;">({{ $d['hari_nm'] }})</span>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #0f172a; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                                    {{ number_format($d['total_wip_qty'], 2, ',', '.') }} kg
                                </td>
                                <td style="text-align: right; color: #475569; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                                    {{ number_format($d['cng_mmbtu'], 2, ',', '.') }}
                                </td>
                                <td style="text-align: right; color: #64748b; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                                    Rp {{ number_format($d['listrik_air_telp_nilai'], 0, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #0284c7; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;" class="cell-sim-listrik">
                                    Rp -
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #d97706; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;" class="cell-sim-cng">
                                    Rp -
                                </td>
                                <td style="text-align: right; font-weight: 800; color: #059669; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;" class="cell-sim-hpp">
                                    Rp -
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: #f8fafc; border-top: 2px solid #cbd5e1; font-weight: 700;">
                            <td colspan="2" style="text-align: center; color: #475569; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em;">TOTAL &amp; RATA-RATA:</td>
                            <td style="text-align: right; color: #0f172a; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                                {{ number_format($modalTot['total_wip_qty'], 2, ',', '.') }} kg
                            </td>
                            <td style="text-align: right; color: #0f172a; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                                {{ number_format($modalTot['cng_mmbtu'], 2, ',', '.') }}
                            </td>
                            <td style="text-align: right; color: #64748b; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                                Rp {{ number_format($modalTot['listrik_air_telp_nilai'], 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; color: #0284c7; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.85rem;" id="foot_total_listrik">
                                Rp -
                            </td>
                            <td style="text-align: right; color: #d97706; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.85rem;" id="foot_total_cng">
                                Rp -
                            </td>
                            <td style="text-align: right; color: #059669; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.875rem;" id="foot_avg_hpp">
                                Rp -
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- ACTION BUTTONS (STANDAR FORM PRODUKSI) --}}
        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; align-items: center; margin-top: 1.25rem;">
            <a href="{{ route('produksi.rekap', ['tahun' => $year, 'bulan' => $month]) }}" 
               class="btn btn-secondary" 
               style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #475569; font-size: 0.85rem; font-weight: 600; padding: 0.6rem 1.25rem; text-decoration: none;">
                Batal
            </a>

            <button type="button" 
                    onclick="openModalConfirmAdjust()" 
                    class="btn btn-primary" 
                    style="background: #059669; border-color: #059669; font-size: 0.85rem; font-weight: 700; padding: 0.6rem 1.5rem; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); cursor: pointer;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Simpan &amp; Terapkan Penyesuaian ke HPP</span>
            </button>
        </div>
    </form>
@endif

{{-- MODAL KONFIRMASI (SESUAI ATURAN AGENTS.md RULE 2: DILARANG CONFIRM BROWSER) --}}
@include('produksi.partials.modal-confirm-adjust')

@endsection

@push('scripts')
    <script src="{{ asset('js/produksi/produksi-adjust-utilitas.js') }}"></script>
@endpush
