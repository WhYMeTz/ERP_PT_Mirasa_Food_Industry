@extends('layouts.app')

@section('title', 'Penyesuaian Gas CNG Harian & Balancing - PT Mirasa Food Industry')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-index.css') }}?v={{ filemtime(public_path('css/produksi/produksi-index.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-adjust-cng-harian.css') }}?v={{ filemtime(public_path('css/produksi/produksi-adjust-cng-harian.css')) }}">
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
    $tarifCngDefault = (float) ($tarifCngDefault ?? 263001.00);
    $defaultCngTarifUsd = (float) ($defaultCngTarifUsd ?? 14.50);
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
                Penyesuaian Gas CNG Harian &amp; Balancing
            </h1>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.825rem; color: #64748b;">
                Worksheet koreksi pemakaian gas per tanggal kerja dan rekonsiliasi (balancing) dengan target tagihan faktur vendor.
            </p>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            {{-- Filter Periode Kalender Terpadu --}}
            <form action="{{ route('produksi.adjust-cng-harian') }}" method="GET" style="display: flex; gap: 0.35rem; align-items: center; background: #ffffff; padding: 0.2rem 0.5rem; border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
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

{{-- SEGMENTED TAB SWITCHER: PILIHAN MODE REKONSILIASI BULANAN VS PENYESUAIAN HARIAN --}}
<div style="display: flex; gap: 0.5rem; background: #e2e8f0; padding: 0.25rem; border-radius: 8px; width: fit-content; margin-bottom: 1.25rem;">
    <a href="{{ route('produksi.adjust-utilitas', ['tahun' => $year, 'bulan' => $month]) }}" 
       style="padding: 0.45rem 1rem; border-radius: 6px; font-size: 0.825rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem; background: transparent; color: #64748b; transition: all 0.15s ease;"
       onmouseover="this.style.background='#ffffff'; this.style.color='#0f172a'"
       onmouseout="this.style.background='transparent'; this.style.color='#64748b'">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <span>1. Rekonsiliasi Tagihan Bulanan (Faktur Total)</span>
    </a>
    <a href="{{ route('produksi.adjust-cng-harian', ['tahun' => $year, 'bulan' => $month]) }}" 
       style="padding: 0.45rem 1rem; border-radius: 6px; font-size: 0.825rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem; background: #ffffff; color: #d97706; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
        <span>2. Penyesuaian Gas Harian &amp; Balancing</span>
    </a>
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

    {{-- KARTU REKONSILIASI & STATUS BALANCING BULANAN --}}
    <div class="balancing-dashboard-grid">
        
        {{-- KARTU 1: TARGET TAGIHAN FAKTUR VENDOR (ACUAN BALANCING) --}}
        <div class="balancing-card card-target">
            <div>
                <div class="balancing-card-header">
                    <div>
                        <span class="balancing-card-label" style="color: #b45309;">1. Target Faktur Vendor</span>
                        <span class="balancing-card-sublabel">Input kuantitas invoice resmi</span>
                    </div>
                    <div class="balancing-icon-box" style="background: #fef3c7; color: #d97706;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                </div>

                <div style="margin-top: 0.35rem;">
                    <label for="target_vendor_mmbtu" style="font-size: 0.725rem; font-weight: 700; color: #78350f; display: block; margin-bottom: 0.25rem;">
                        Target Total MMBTU:
                    </label>
                    <div style="display: flex; align-items: center; gap: 0.35rem;">
                        <input type="number" step="any" min="0" 
                               id="target_vendor_mmbtu" 
                               class="form-control" 
                               style="font-size: 1.35rem; font-weight: 800; font-family: ui-monospace, monospace; height: 38px; color: #92400e; border: 1.5px solid #f59e0b; background: #ffffff;"
                               value="{{ round((float) $modalTot['cng_mmbtu'], 4) }}" 
                               oninput="recalculateCngBalancing()"
                               placeholder="0.0000">
                        <span style="font-weight: 800; font-size: 0.85rem; color: #92400e;">MMBTU</span>
                    </div>
                </div>
            </div>

            <div class="balancing-card-footer">
                <span>Acuan Rekonsiliasi Tagihan</span>
                <button type="button" onclick="autoDistributeVariance()" style="background: none; border: none; font-size: 0.725rem; font-weight: 700; color: #d97706; cursor: pointer; text-decoration: underline;" title="Bagi rata selisih ke seluruh hari kerja">
                    Bagi Rata Selisih Otomatis &raquo;
                </button>
            </div>
        </div>

        {{-- KARTU 2: TOTAL AKUMULASI HARIAN SAAT INI --}}
        <div class="balancing-card card-actual">
            <div>
                <div class="balancing-card-header">
                    <div>
                        <span class="balancing-card-label" style="color: #0369a1;">2. Akumulasi Harian Terkini</span>
                        <span class="balancing-card-sublabel">{{ $prodDays->count() }} Hari Kerja Produksi</span>
                    </div>
                    <div class="balancing-icon-box" style="background: #e0f2fe; color: #0284c7;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
                    </div>
                </div>

                <div class="balancing-card-value" id="stat_actual_mmbtu" style="color: #0284c7;">
                    {{ number_format((float) $modalTot['cng_mmbtu'], 4, ',', '.') }} MMBTU
                </div>

                <div class="balancing-card-subvalue" id="stat_actual_rp" style="color: #475569;">
                    Rp {{ number_format((float) $modalTot['cng_nilai'], 0, ',', '.') }}
                </div>
            </div>

            <div class="balancing-card-footer">
                <span>Total Output WIP: <strong>{{ number_format((float) $modalTot['total_wip_qty'], 0, ',', '.') }} kg</strong></span>
                <span style="color: #0284c7; font-weight: 600;">Data Real-Time</span>
            </div>
        </div>

        {{-- KARTU 3: STATUS BALANCING REKONSILIASI --}}
        <div class="balancing-card card-status status-balanced" id="cardBalancingStatus">
            <div>
                <div class="balancing-card-header">
                    <div>
                        <span class="balancing-card-label" id="labelBalancingStatus">3. Status Balancing</span>
                        <span class="badge" id="badgeBalancingTitle" style="font-size: 0.7rem; font-weight: 800; padding: 0.2rem 0.5rem; border-radius: 4px; background: #dcfce7; color: #15803d; margin-top: 0.25rem; display: inline-block;">
                            100% BALANCE / SEIMBANG
                        </span>
                    </div>
                    <div class="balancing-icon-box" id="iconBalancingBox" style="background: #f0fdf4; color: #16a34a;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 18h12l3-18H3zm16 4H5m7-4v4"/></svg>
                    </div>
                </div>

                <div class="balancing-card-value" id="textBalancingVal" style="color: #16a34a; font-size: 1.45rem;">
                    Selisih: 0.0000 MMBTU
                </div>

                <p id="textBalancingDesc" style="font-size: 0.75rem; color: #64748b; margin: 0.35rem 0 0 0; line-height: 1.4;">
                    Total pencatatan fisik harian seimbang persis dengan target faktur vendor.
                </p>
            </div>

            <div class="balancing-card-footer">
                <span>Indikator Rekonsiliasi</span>
                <span style="font-weight: 700;">Audit Standard</span>
            </div>
        </div>

    </div>

    {{-- TOOLBAR CEPAT (BULK OFFSET HELPER) --}}
    <div class="quick-bulk-toolbar">
        <div class="bulk-toolbar-title">
            <svg width="18" height="18" fill="none" stroke="#d97706" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
            <span>Alat Bantu Penyesuaian Serentak (Bulk Offset):</span>
        </div>

        <div class="bulk-action-controls">
            <label for="input_bulk_offset" style="font-size: 0.775rem; color: #475569; font-weight: 600; margin: 0;">
                Tambah / Kurang:
            </label>
            <input type="number" step="any" 
                   id="input_bulk_offset" 
                   class="input-bulk-step" 
                   placeholder="0.0000" 
                   title="Ketik angka selisih penyesuaian MMBTU">
            <span style="font-size: 0.75rem; font-weight: 700; color: #475569;">MMBTU / hari</span>

            <button type="button" onclick="applyBulkOffset()" class="btn-bulk-apply" title="Tambahkan angka di atas ke seluruh hari kerja secara serentak">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                <span>Terapkan ke Seluruh Hari</span>
            </button>

            <button type="button" onclick="resetAllDailyInputs()" class="btn-bulk-reset" title="Kembalikan semua angka ke catatan awal lapangan">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Reset Awal</span>
            </button>
        </div>
    </div>

    {{-- FORM UTAMA TABEL PENYESUAIAN HARIAN --}}
    <form id="formDailyCngBalancing" action="{{ route('produksi.adjust-cng-harian.store') }}" method="POST">
        @csrf
        <input type="hidden" name="tahun" value="{{ $year }}">
        <input type="hidden" name="bulan" value="{{ $month }}">
        <input type="hidden" name="alasan_txt" id="input_hidden_alasan" value="Rekonsiliasi &amp; balancing pemakaian gas CNG periode {{ $monthsList[$month] }} {{ $year }}">

        <div class="card" style="border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); overflow: hidden; margin-bottom: 1.25rem;">
            <div class="card-header" style="background: #ffffff; padding: 0.85rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                <div>
                    <span style="font-size: 0.925rem; font-weight: 800; color: #0f172a;">
                        Daftar Pemakaian Gas CNG Harian ({{ $prodDays->count() }} Hari Kerja &bull; {{ $monthsList[$month] }} {{ $year }})
                    </span>
                    <span style="font-size: 0.725rem; color: #64748b; margin-left: 0.4rem;">
                        Ketik angka baru pada kolom <strong>MMBTU Baru</strong> untuk mengoreksi hari tertentu.
                    </span>
                </div>
                <div style="font-size: 0.725rem; font-weight: 700; color: #059669; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 0.2rem 0.55rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #059669;"></span>
                    <span>Kurs Terkunci Per Tanggal Produksi</span>
                </div>
            </div>

            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table-cng-balancing" id="tableCngBalancing">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">No</th>
                            <th style="width: 130px;">Tanggal &amp; Hari</th>
                            <th style="width: 140px;">Dokumen SPK</th>
                            <th style="text-align: right; width: 110px;">Output WIP</th>
                            <th style="text-align: right; width: 130px; color: #0284c7;">Kurs JISDOR BI</th>
                            <th style="text-align: right; width: 110px;">MMBTU Awal</th>
                            <th style="text-align: right; width: 140px; background: #fffdf5; color: #b45309;">MMBTU Baru (Edit)</th>
                            <th style="text-align: center; width: 100px;">&Delta; MMBTU</th>
                            <th style="text-align: right; width: 130px; color: #d97706;">Biaya Gas Baru</th>
                            <th style="text-align: center; width: 110px;">&Delta; Biaya Rp</th>
                            <th style="text-align: right; width: 130px; color: #059669;">Estimasi HPP</th>
                            <th style="width: 160px;">Catatan Koreksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prodDays as $d)
                            @php
                                $dDate = $d['date'];
                                $dOldMmbtu = (float) $d['cng_mmbtu'];
                                $dOldNilai = (float) $d['cng_nilai'];
                                $dOldTotalBiaya = (float) $d['total_biaya_produksi'];
                                $dWip = (float) $d['total_wip_qty'];
                                $dJisdor = (float) ($d['cng_kurs_jisdor'] ?? 18138);
                                $dUsd = (float) ($d['cng_tarif_usd'] ?? $defaultCngTarifUsd);
                                $dTarifRp = (float) ($d['cng_tarif'] ?? round($dUsd * $dJisdor, 2));
                                $dHpp = (float) $d['hpp_per_kg'];
                            @endphp
                            <tr class="cng-daily-row" 
                                id="row_cng_{{ $dDate }}"
                                data-date="{{ $dDate }}"
                                data-old-mmbtu="{{ $dOldMmbtu }}"
                                data-old-cng-nilai="{{ $dOldNilai }}"
                                data-old-total-biaya="{{ $dOldTotalBiaya }}"
                                data-cng-tarif="{{ $dTarifRp }}"
                                data-cng-jisdor="{{ $dJisdor }}"
                                data-cng-usd="{{ $dUsd }}"
                                data-wip-qty="{{ $dWip }}"
                                data-old-hpp="{{ $dHpp }}">
                                
                                <td style="text-align: center; color: #94a3b8; font-size: 0.75rem;">
                                    {{ $loop->iteration }}
                                </td>

                                <td>
                                    <strong style="color: #0f172a; font-size: 0.8rem;">{{ date('d/m/Y', strtotime($dDate)) }}</strong>
                                    <div style="font-size: 0.7rem; color: #64748b;">{{ $d['hari_nm'] }}</div>
                                </td>

                                <td>
                                    <span style="font-size: 0.75rem; font-weight: 700; color: #334155;">{{ $d['primary_produksi_no'] }}</span>
                                    <div style="font-size: 0.675rem; color: #64748b;">Shift {{ $d['shift_cd'] }}</div>
                                </td>

                                <td style="text-align: right; font-weight: 700; color: #0f172a; font-family: ui-monospace, monospace;">
                                    {{ number_format($dWip, 0, ',', '.') }} kg
                                </td>

                                <td style="text-align: right; color: #0284c7; font-weight: 700; font-family: ui-monospace, monospace;">
                                    Rp {{ number_format($dJisdor, 0, ',', '.') }}
                                    <div style="font-size: 0.65rem; color: #64748b; font-weight: normal;">${{ number_format($dUsd, 2) }} / MMBTU</div>
                                </td>

                                <td style="text-align: right; color: #475569; font-weight: 600; font-family: ui-monospace, monospace;">
                                    {{ number_format($dOldMmbtu, 4, ',', '.') }}
                                </td>

                                <td style="text-align: right; background: #fffdf5;">
                                    <input type="hidden" name="days[{{ $dDate }}][old_mmbtu]" value="{{ $dOldMmbtu }}">
                                    <input type="hidden" name="days[{{ $dDate }}][cng_kurs_jisdor]" value="{{ $dJisdor }}">
                                    <input type="hidden" name="days[{{ $dDate }}][cng_tarif_usd]" value="{{ $dUsd }}">
                                    <input type="number" step="any" min="0" 
                                           name="days[{{ $dDate }}][new_mmbtu]" 
                                           class="input-cng-edit" 
                                           value="{{ round($dOldMmbtu, 4) }}" 
                                           oninput="recalculateCngBalancing()"
                                           title="Ketik kuantitas fisik MMBTU baru untuk tanggal ini">
                                </td>

                                <td style="text-align: center;">
                                    <span class="badge-cng-variance badge-cng-zero badge-delta-mmbtu">0</span>
                                </td>

                                <td style="text-align: right; font-weight: 800; color: #d97706; font-family: ui-monospace, monospace;" class="cell-cng-nilai-baru">
                                    Rp {{ number_format($dOldNilai, 0, ',', '.') }}
                                </td>

                                <td style="text-align: center;">
                                    <span class="badge-cng-variance badge-cng-zero badge-delta-biaya">Rp 0</span>
                                </td>

                                <td style="text-align: right; font-weight: 800; color: #059669; font-family: ui-monospace, monospace;" class="cell-hpp-baru">
                                    Rp {{ number_format($dHpp, 0, ',', '.') }}/kg
                                </td>

                                <td>
                                    <input type="text" 
                                           name="days[{{ $dDate }}][catatan]" 
                                           class="input-catatan-inline" 
                                           placeholder="Koreksi..." 
                                           title="Catatan perubahan untuk tanggal ini">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: center; color: #475569; text-transform: uppercase; font-size: 0.725rem;">
                                TOTAL &amp; RATA-RATA:
                            </td>
                            <td style="text-align: right; color: #0f172a; font-family: ui-monospace, monospace;">
                                {{ number_format((float) $modalTot['total_wip_qty'], 0, ',', '.') }} kg
                            </td>
                            <td style="text-align: right; color: #0284c7; font-size: 0.75rem;">
                                Rata-rata JISDOR
                            </td>
                            <td style="text-align: right; color: #475569; font-family: ui-monospace, monospace;">
                                {{ number_format((float) $modalTot['cng_mmbtu'], 4, ',', '.') }}
                            </td>
                            <td style="text-align: right; color: #b45309; font-family: ui-monospace, monospace;" id="foot_total_mmbtu_baru">
                                {{ number_format((float) $modalTot['cng_mmbtu'], 4, ',', '.') }} MMBTU
                            </td>
                            <td style="text-align: center; color: #64748b; font-size: 0.75rem;">
                                &Delta; Total
                            </td>
                            <td style="text-align: right; color: #d97706; font-family: ui-monospace, monospace;" id="foot_total_cng_baru">
                                Rp {{ number_format((float) $modalTot['cng_nilai'], 0, ',', '.') }}
                            </td>
                            <td style="text-align: center; color: #64748b; font-size: 0.75rem;">
                                &Delta; Biaya
                            </td>
                            <td style="text-align: right; color: #059669; font-family: ui-monospace, monospace;" id="foot_avg_hpp_baru">
                                Rp {{ number_format((float) (($modalTot['total_wip_qty'] > 0) ? (array_sum(array_column($prodDays->toArray(), 'total_biaya_produksi')) / $modalTot['total_wip_qty']) : 0), 0, ',', '.') }}/kg
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- STICKY BOTTOM ACTION BAR --}}
        <div class="bottom-submit-bar">
            <div class="bottom-summary-text" id="bottomSummaryDesc">
                Seluruh lembar kerja gas harian sesuai data fisik saat ini (belum ada perubahan).
            </div>

            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <a href="{{ route('produksi.rekap', ['tahun' => $year, 'bulan' => $month]) }}" 
                   class="btn btn-secondary" 
                   style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #475569; font-size: 0.85rem; font-weight: 600; padding: 0.6rem 1.25rem; border-radius: 6px; text-decoration: none;">
                    Batal
                </a>

                <button type="button" 
                        id="btnSubmitDailyCng" 
                        onclick="openModalConfirmAdjustCng()" 
                        class="btn btn-primary" 
                        style="background: #059669; border: 1.5px solid #059669; font-size: 0.85rem; font-weight: 700; padding: 0.6rem 1.5rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); cursor: pointer;" 
                        disabled>
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan &amp; Terapkan Penyesuaian Gas Harian</span>
                </button>
            </div>
        </div>
    </form>

    {{-- MODAL KONFIRMASI AKUNTANSI --}}
    @include('produksi.partials.modal-confirm-adjust-cng')

@endif

@endsection

@push('scripts')
    <script src="{{ asset('js/produksi/produksi-adjust-cng-harian.js') }}?v={{ filemtime(public_path('js/produksi/produksi-adjust-cng-harian.js')) }}"></script>
@endpush
