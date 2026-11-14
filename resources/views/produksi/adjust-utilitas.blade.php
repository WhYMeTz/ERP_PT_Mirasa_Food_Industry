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
    $tarifCngDefault = (float) ($tarifCngDefault ?? 263001.00);
@endphp

<div style="margin-bottom: 1.25rem;">
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
                Alokasi penyesuaian tagihan faktur Gas Alam CNG aktual bulanan ke seluruh lembar produksi harian secara proporsional sesuai kaidah akuntansi manufaktur.
            </p>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            {{-- Filter Periode Kalender Terpadu --}}
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

    {{-- METRIK 2: TOTAL PEMAKAIAN GAS CNG (NOMOR 1 UTAMA) --}}
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

    {{-- METRIK 3: TOTAL OUTPUT WIP --}}
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
            <span style="color: #059669; font-weight: 600;">Total hasil olahan curah</span>
        </div>
    </div>

    {{-- METRIK 4: AKUMULASI BIAYA GAS SAAT INI --}}
    <div class="executive-stat-card">
        <div class="executive-stat-card-header">
            <div>
                <span class="executive-stat-label" style="color: #d97706;">
                    Total Beban Gas Terkini
                </span>
                <div class="executive-stat-value" style="font-family: monospace; font-size: 1.35rem; color: #d97706;">
                    Rp {{ number_format($modalTot['cng_nilai'], 0, ',', '.') }}
                </div>
            </div>
            <div class="executive-stat-icon-box" style="background: #fef3c7; color: #d97706;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
            </div>
        </div>
        <div class="executive-stat-subtext">
            <span>Beban tercatat bulan {{ $monthsList[$month] }}</span>
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
    <form id="formMainAdjustUtilitas" action="{{ route('produksi.adjust-utilitas.store') }}" method="POST">
        @csrf
        <input type="hidden" name="tahun" value="{{ $year }}">
        <input type="hidden" name="bulan" value="{{ $month }}">
        <input type="hidden" name="redirect_to" value="adjust">
        <input type="hidden" name="adjust_cng" value="1">
        <input type="hidden" name="mode_cng" value="total_tagihan">
        <input type="hidden" name="adjust_listrik" value="0">

        {{-- KARTU TUNGGAL TERPADU: PENYESUAIAN TOTAL FAKTUR GAS BULANAN --}}
        <div class="card" style="margin-bottom: 1.25rem; border: 1.5px solid #cbd5e1; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); background: #ffffff; overflow: hidden;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.95rem 1.35rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <div style="width: 32px; height: 32px; border-radius: 6px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
                    </div>
                    <div>
                        <h2 style="font-size: 0.975rem; font-weight: 800; color: #0f172a; margin: 0;">
                            Penyesuaian Total Tagihan Faktur Gas Alam CNG (Boiler &amp; Fryer)
                        </h2>
                        <span style="font-size: 0.725rem; color: #64748b;">
                            Masukkan total tagihan faktur aktual bulanan dari vendor. Biaya didistribusikan proporsional sesuai pemakaian fisik MMBTU boiler tiap hari.
                        </span>
                    </div>
                </div>
                <span class="badge" style="font-size: 0.725rem; font-weight: 700; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 0.25rem 0.65rem; border-radius: 4px;">
                    Alokasi Faktur Bulanan
                </span>
            </div>

            <div class="card-body" style="padding: 1.35rem;">
                
                {{-- BAGIAN 1: PARAMETER ACUAN KONTRAK VENDOR & KURS JISDOR (MODEL INPUTAN SEPERTI HARIAN BISA DIEDIT) --}}
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.15rem; margin-bottom: 1.25rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.45rem;">
                            <svg width="16" height="16" fill="none" stroke="#d97706" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            <span style="font-size: 0.825rem; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.04em;">
                                1. Acuan Kontrak Vendor &amp; Kurs JISDOR BI (Dapat Diedit)
                            </span>
                        </div>
                        <span style="font-size: 0.725rem; color: #64748b;">
                            Model inputan acuan harian &bull; Nilai USD dan Kurs dapat disesuaikan
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; align-items: start;">
                        {{-- INPUT TARIF KONTRAK USD --}}
                        <div>
                            <label for="input_cng_tarif_usd" style="font-size: 0.775rem; font-weight: 700; color: #334155; display: block; margin-bottom: 0.35rem;">
                                Tarif Kontrak Vendor (USD / MMBTU):
                            </label>
                            <div class="input-addon-group">
                                <span class="addon-prefix" style="font-weight: 800; color: #475569; padding: 0 0.75rem;">$</span>
                                <input type="number" step="0.01" min="0" 
                                       name="cng_tarif_usd" 
                                       id="input_cng_tarif_usd" 
                                       class="form-control addon-input-field" 
                                       value="{{ old('cng_tarif_usd', $energiRates['cng_tarif_usd'] ?? 14.50) }}" 
                                       oninput="calcContractRateFromInputs()">
                            </div>
                            <small style="font-size: 0.7rem; color: #64748b; margin-top: 0.25rem; display: block;">
                                Standar kontrak vendor: $14.50 / MMBTU
                            </small>
                        </div>

                        {{-- INPUT KURS JISDOR BI --}}
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                <label for="input_cng_kurs_jisdor" style="font-size: 0.775rem; font-weight: 700; color: #334155; margin: 0;">
                                    Kurs Rata-Rata JISDOR BI (Rp):
                                </label>
                                <a href="https://www.bi.go.id/id/statistik/informasi-kurs/jisdor/default.aspx" 
                                   target="_blank" 
                                   rel="noopener noreferrer" 
                                   style="font-size: 0.7rem; font-weight: 600; color: #0284c7; text-decoration: none; display: inline-flex; align-items: center; gap: 2px;"
                                   title="Cek Kurs di Bank Indonesia">
                                    <span>Web BI</span>
                                    <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </div>
                            <div class="input-addon-group">
                                <span class="addon-prefix" style="font-weight: 800; color: #475569; padding: 0 0.75rem;">Rp</span>
                                <input type="number" step="any" min="0" 
                                       name="cng_kurs_jisdor" 
                                       id="input_cng_kurs_jisdor" 
                                       class="form-control addon-input-field" 
                                       value="{{ old('cng_kurs_jisdor', $energiRates['cng_kurs_jisdor'] ?? 18138) }}" 
                                       oninput="calcContractRateFromInputs()">
                            </div>
                            <small style="font-size: 0.7rem; color: #64748b; margin-top: 0.25rem; display: block;">
                                Referensi kurs rata-rata bulanan
                            </small>
                        </div>

                        {{-- HASIL TARIF KONVERSI RESMI (RP / MMBTU) --}}
                        <div>
                            <label for="input_cng_tarif_resmi" style="font-size: 0.775rem; font-weight: 700; color: #166534; display: block; margin-bottom: 0.35rem;">
                                Tarif Acuan Terkonversi (Rp / MMBTU):
                            </label>
                            <div class="input-addon-group" style="border-color: #86efac; background: #f0fdf4;">
                                <span class="addon-prefix" style="font-weight: 800; color: #166534; padding: 0 0.75rem; background: #dcfce7;">Rp</span>
                                <input type="number" step="any" min="0" 
                                       name="cng_tarif_baru" 
                                       id="input_cng_tarif_resmi" 
                                       class="form-control addon-input-field" 
                                       style="color: #15803d; font-weight: 800;"
                                       value="{{ old('cng_tarif_baru', $tarifCngDefault) }}" 
                                       oninput="onManualTarifResmiChange()">
                            </div>
                            <small style="font-size: 0.7rem; color: #15803d; margin-top: 0.25rem; display: block;">
                                Otomatis: USD &times; Kurs JISDOR (bisa diedit)
                            </small>
                        </div>
                    </div>
                </div>

                {{-- BAGIAN 2: INPUT UTAMA TOTAL FAKTUR GAS & RINGKASAN METRIK BULANAN --}}
                <div>
                    <div style="font-size: 0.825rem; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.65rem;">
                        2. Total Tagihan Faktur Aktual &amp; Alokasi Biaya
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; align-items: stretch;">
                        
                        {{-- INPUT BESAR TOTAL FAKTUR GAS --}}
                        <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 1.15rem; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.45rem;">
                                    <label for="input_total_cng_tagihan" style="font-size: 0.8rem; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.02em; margin: 0;">
                                        Total Faktur Tagihan Gas Bulan Ini:
                                    </label>
                                    <span class="badge" style="font-size: 0.6875rem; font-weight: 700; background: #fef3c7; color: #b45309; padding: 0.2rem 0.5rem; border-radius: 4px;">
                                        Input Aktual
                                    </span>
                                </div>
                                <div class="input-addon-group" style="border-color: #d97706;">
                                    <span class="addon-prefix" style="font-size: 1.15rem; font-weight: 800; color: #d97706; padding: 0 0.85rem; background: #fffbeb;">Rp</span>
                                    <input type="number" step="1" min="0" 
                                           name="total_cng_tagihan" 
                                           id="input_total_cng_tagihan" 
                                           class="form-control addon-input-field" 
                                           style="font-size: 1.25rem; font-weight: 800; color: #0f172a; height: 44px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;"
                                           value="{{ old('total_cng_tagihan', round($modalTot['cng_nilai'] > 0 ? $modalTot['cng_nilai'] : ($modalTot['cng_mmbtu'] * $tarifCngDefault))) }}"
                                           oninput="recalculateLiveSimulation()">
                                </div>
                                <p style="font-size: 0.725rem; color: #64748b; margin: 0.45rem 0 0 0; line-height: 1.4;">
                                    Ketik nominal tagihan pada faktur/invoice vendor gas (PGN/Gagas/Vendor CNG).
                                </p>
                            </div>

                            {{-- Tombol Helper Gunakan Acuan Kontrak --}}
                            <div style="margin-top: 0.85rem; padding-top: 0.75rem; border-top: 1px dashed #e2e8f0;">
                                <button type="button" onclick="applyContractRateToInvoice()" class="btn btn-sm" style="width: 100%; font-size: 0.75rem; font-weight: 700; background: #f8fafc; color: #1e293b; border: 1.5px solid #cbd5e1; padding: 0.45rem 0.75rem; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.15s ease;">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Hitung dari Acuan Kontrak ($ &times; Kurs &times; Total MMBTU)</span>
                                </button>
                            </div>
                        </div>

                        {{-- STAT BOX 1: TOTAL PEMAKAIAN FISIK MMBTU --}}
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.15rem; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <span style="font-size: 0.725rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">
                                    Total Pemakaian Fisik Gas
                                </span>
                                <div style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin-top: 0.4rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                                    {{ number_format($modalTot['cng_mmbtu'], 2, ',', '.') }}
                                    <span style="font-size: 0.85rem; color: #64748b; font-weight: 700;">MMBTU</span>
                                </div>
                            </div>
                            <div style="font-size: 0.725rem; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 0.5rem; margin-top: 0.5rem;">
                                Akumulasi {{ $prodDays->count() }} hari kerja di bulan {{ $monthsList[$month] }} {{ $year }}
                            </div>
                        </div>

                        {{-- STAT BOX 2: TARIF EFEKTIF PER MMBTU --}}
                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 1.15rem; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 0.725rem; font-weight: 700; color: #166534; text-transform: uppercase; letter-spacing: 0.04em;">
                                        Tarif Efektif Faktur Aktual
                                    </span>
                                    <span style="font-size: 0.6875rem; font-weight: 700; background: #dcfce7; color: #15803d; padding: 0.15rem 0.45rem; border-radius: 4px;">
                                        Actual Cost
                                    </span>
                                </div>
                                <div style="font-size: 1.45rem; font-weight: 800; color: #15803d; margin-top: 0.4rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;" id="effective_cng_rate_display">
                                    Rp {{ number_format($modalTot['cng_mmbtu'] > 0 ? ($modalTot['cng_nilai'] / $modalTot['cng_mmbtu']) : $tarifCngDefault, 0, ',', '.') }}
                                </div>
                            </div>
                            <div style="font-size: 0.725rem; color: #166534; border-top: 1px solid #bbf7d0; padding-top: 0.5rem; margin-top: 0.5rem;">
                                Rumus: Total Faktur &divide; Total MMBTU Fisik
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

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
                            <th style="text-align: right; color: #64748b; width: 160px;">Gas Eksisting</th>
                            <th style="text-align: right; color: #d97706; width: 170px;">Gas Baru (Simulasi)</th>
                            <th style="text-align: right; color: #059669; width: 170px;">Estimasi HPP/Kg Baru</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prodDays as $d)
                            <tr class="sim-data-row"
                                data-wip="{{ (float) $d['total_wip_qty'] }}"
                                data-mmbtu="{{ (float) $d['cng_mmbtu'] }}"
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
                                    Rp {{ number_format($d['cng_nilai'], 0, ',', '.') }}
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
                                Rp {{ number_format($modalTot['cng_nilai'], 0, ',', '.') }}
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
