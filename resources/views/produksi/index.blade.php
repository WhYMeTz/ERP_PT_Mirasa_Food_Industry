@extends('layouts.app')

@section('title', 'HPP/KG ' . strtoupper($monthName) . ' ' . $year . ' - Rekap Produksi Harian')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-index.css') }}">
@endpush

@section('content')
<div style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <div style="font-size: 0.8rem; font-weight: 700; color: #0284c7; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.35rem;">
            <span>🏭 Produksi &amp; Manufaktur</span>
            <span>&bull;</span>
            <span>PT Mirasa Food Industry</span>
        </div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0.2rem 0 0 0; letter-spacing: -0.02em;">
            HPP/KG BULAN {{ strtoupper($monthName) }} {{ $year }}
        </h1>
        <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #64748b;">
            Buku Rekap Biaya Produksi Harian, Rendemen Singkong, dan Output WIP (Standar Spreadsheet Mirasa).
        </p>
    </div>

    <div style="display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap;">
        {{-- Filter Bulan & Tahun --}}
        <form action="{{ route('produksi.index') }}" method="GET" style="display: flex; gap: 0.4rem; align-items: center; background: #ffffff; padding: 0.25rem 0.5rem; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
            <select name="bulan" class="form-control" style="font-size: 0.85rem; padding: 0.35rem 0.65rem; height: 34px; border: none; font-weight: 600;" onchange="this.form.submit()">
                @foreach ($monthsList as $num => $nm)
                    <option value="{{ $num }}" {{ $num == $month ? 'selected' : '' }}>{{ $nm }}</option>
                @endforeach
            </select>
            <select name="tahun" class="form-control" style="font-size: 0.85rem; padding: 0.35rem 0.65rem; height: 34px; border: none; font-weight: 600;" onchange="this.form.submit()">
                @foreach ($yearsList as $yr)
                    <option value="{{ $yr }}" {{ $yr == $year ? 'selected' : '' }}>{{ $yr }}</option>
                @endforeach
            </select>
        </form>

        <a href="{{ route('produksi.create') }}" class="btn btn-primary" style="padding: 0.55rem 1rem; font-size: 0.85rem; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.25);">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>+ Catat Hasil Produksi</span>
        </a>
    </div>
</div>

@if(session('success'))
    <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; color: #065f46; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
        <span>⚠️ {{ session('error') }}</span>
    </div>
@endif

{{-- 4 KARTU KPI RINGKASAN PRODUKSI BULANAN --}}
@php
    $tot = $report['totals'];
    $rendemenColor = $tot['rendemen_persen'] >= 33.0 ? '#059669' : ($tot['rendemen_persen'] >= 30.0 ? '#d97706' : '#dc2626');
    $rendemenBg = $tot['rendemen_persen'] >= 33.0 ? '#ecfdf5' : ($tot['rendemen_persen'] >= 30.0 ? '#fffbeb' : '#fef2f2');
@endphp

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
    {{-- KPI 1: TOTAL BIAYA PRODUKSI --}}
    <div class="card" style="padding: 1.1rem; border-left: 4px solid #eab308;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #854d0e;">
                    Total Biaya Produksi
                </span>
                <div style="font-size: 1.4rem; font-weight: 800; color: #0f172a; margin-top: 0.25rem;">
                    Rp {{ number_format($tot['total_biaya_produksi'], 0, ',', '.') }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #fef9c3; display: flex; align-items: center; justify-content: center; color: #a16207; font-weight: 800;">
                Rp
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Bahan, CNG, Tenaga Kerja &amp; FOH
        </div>
    </div>

    {{-- KPI 2: TOTAL WIP OUTPUT JADI --}}
    <div class="card" style="padding: 1.1rem; border-left: 4px solid #0284c7;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #0369a1;">
                    Total Hasil WIP Jadi
                </span>
                <div style="font-size: 1.4rem; font-weight: 800; color: #0f172a; margin-top: 0.25rem;">
                    {{ number_format($tot['total_wip_qty'], 2, ',', '.') }} <span style="font-size: 0.9rem; font-weight: 600; color: #64748b;">kg</span>
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: #0284c7;">
                📦
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Dari {{ number_format($tot['singkong_qty'], 0, ',', '.') }} kg singkong mentah
            @if(!empty($tot['total_karton']) && $tot['total_karton'] > 0)
                &bull; <strong style="color: #0369a1;">{{ number_format($tot['total_karton'], 0, ',', '.') }} Karton/Box</strong>
            @endif
        </div>
    </div>

    {{-- KPI 3: RENDEMEN RATA-RATA --}}
    <div class="card" style="padding: 1.1rem; border-left: 4px solid {{ $rendemenColor }};">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: {{ $rendemenColor }};">
                    Rendemen Rata-Rata
                </span>
                <div style="font-size: 1.4rem; font-weight: 800; color: {{ $rendemenColor }}; margin-top: 0.25rem;">
                    {{ number_format($tot['rendemen_persen'], 2, ',', '.') }}%
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: {{ $rendemenBg }}; display: flex; align-items: center; justify-content: center; color: {{ $rendemenColor }}; font-weight: 800;">
                %
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Target standar pabrik: &ge; 33.00%
        </div>
    </div>

    {{-- KPI 4: HPP RATA-RATA PER KG --}}
    <div class="card" style="padding: 1.1rem; border-left: 4px solid #10b981;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #047857;">
                    HPP Rata-Rata per Kg
                </span>
                <div style="font-size: 1.4rem; font-weight: 800; color: #0f172a; margin-top: 0.25rem;">
                    Rp {{ number_format($tot['hpp_per_kg'], 2, ',', '.') }}
                </div>
            </div>
            <div style="width: 32px; height: 32px; border-radius: 6px; background: #d1fae5; display: flex; align-items: center; justify-content: center; color: #059669;">
                ⚖️
            </div>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem;">
            Biaya riil per kg produk jadi
        </div>
    </div>
</div>

{{-- GRID TABEL UTAMA (PERSIS TAMPILAN SPREADSHEET EXCEL MIRASA) --}}
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-size: 0.85rem; font-weight: 700; color: #1e293b;">
                Tabel Buku Rekap HPP Harian (Grid Excel)
            </span>
            <span style="font-size: 0.75rem; background: #f1f5f9; color: #475569; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 600;">
                {{ $report['count'] }} Hari Produksi
            </span>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <button type="button" onclick="window.print()" class="btn btn-secondary btn-sm" style="font-size: 0.8rem; padding: 0.35rem 0.65rem;">
                🖨️ Cetak Lembar HPP
            </button>
        </div>
    </div>

    {{-- CONTAINER SCROLLABLE HORIZONTAL (DENGAN HEADER DAN KOLOM KIRI STICKY) --}}
    <div style="overflow-x: auto; max-height: calc(100vh - 300px); position: relative; border-radius: 0 0 12px 12px;">
        <table style="width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.75rem; white-space: nowrap;">
            {{-- HEADER LEVEL 1: KELOMPOK UTAMA --}}
            <thead style="position: sticky; top: 0; z-index: 20;">
                <tr style="background: #e2e8f0; color: #0f172a; text-align: center; font-weight: 800; font-size: 0.75rem;">
                    <th rowspan="2" style="position: sticky; left: 0; z-index: 25; background: #cbd5e1; border: 1px solid #94a3b8; padding: 0.5rem 0.75rem; min-width: 90px;">HARI</th>
                    <th rowspan="2" style="position: sticky; left: 90px; z-index: 25; background: #cbd5e1; border: 1px solid #94a3b8; padding: 0.5rem 0.75rem; min-width: 85px;">TANGGAL</th>
                    <th rowspan="2" style="background: #cbd5e1; border: 1px solid #94a3b8; padding: 0.5rem 0.65rem; min-width: 140px; text-align: center;">SHIFT &amp; BATCH</th>
                    
                    {{-- TOTAL BIAYA PRODUKSI / KG (WARNA HIJAU MUDA SEPERTI EXCEL) --}}
                    <th colspan="2" style="background: #a3e635; color: #1a2e05; border: 1px solid #65a30d; padding: 0.4rem;">SINGKONG</th>
                    <th colspan="4" style="background: #a3e635; color: #1a2e05; border: 1px solid #65a30d; padding: 0.4rem;">MINYAK GORENG</th>
                    <th colspan="2" style="background: #a3e635; color: #1a2e05; border: 1px solid #65a30d; padding: 0.4rem;">CNG</th>
                    <th colspan="4" style="background: #a3e635; color: #1a2e05; border: 1px solid #65a30d; padding: 0.4rem;">TENAGA KERJA (Rp 91.300)</th>
                    <th rowspan="2" style="background: #bef264; color: #1a2e05; border: 1px solid #65a30d; padding: 0.4rem; min-width: 90px;">BUMBU<br>PERENYAH</th>
                    <th colspan="2" style="background: #a3e635; color: #1a2e05; border: 1px solid #65a30d; padding: 0.4rem;">KARTON IFL</th>
                    <th rowspan="2" style="background: #bef264; color: #1a2e05; border: 1px solid #65a30d; padding: 0.4rem; min-width: 85px;">PLASTIK HD<br>90x100</th>
                    <th colspan="2" style="background: #a3e635; color: #1a2e05; border: 1px solid #65a30d; padding: 0.4rem;">LAKBAN</th>
                    <th rowspan="2" style="background: #bef264; color: #1a2e05; border: 1px solid #65a30d; padding: 0.4rem; min-width: 75px;">TALI<br>RAFIA</th>
                    <th rowspan="2" style="background: #bef264; color: #1a2e05; border: 1px solid #65a30d; padding: 0.4rem; min-width: 75px;">FOTO<br>COPY</th>
                    <th colspan="2" style="background: #a3e635; color: #1a2e05; border: 1px solid #65a30d; padding: 0.4rem;">SARUNG TANGAN</th>
                    <th rowspan="2" style="background: #bef264; color: #1a2e05; border: 1px solid #65a30d; padding: 0.4rem; min-width: 85px;">PENGAWASAN<br>MUTU (QC)</th>
                    <th rowspan="2" style="background: #bef264; color: #1a2e05; border: 1px solid #65a30d; padding: 0.4rem; min-width: 90px;">LISTRIK &amp;<br>AIR + TELP</th>
                    <th rowspan="2" style="background: #bef264; color: #1a2e05; border: 1px solid #65a30d; padding: 0.4rem; min-width: 85px;">PEMLHR<br>MESIN</th>
                    <th rowspan="2" style="background: #bef264; color: #1a2e05; border: 1px solid #65a30d; padding: 0.4rem; min-width: 85px;">PENYS<br>MESIN</th>
                    <th colspan="2" style="background: #a3e635; color: #1a2e05; border: 1px solid #65a30d; padding: 0.4rem;">B. PNGOLHN LIMBAH</th>

                    {{-- TOTAL BIAYA (KOLOM KUNING EMAS SEPERTI EXCEL) --}}
                    <th rowspan="2" style="background: #facc15; color: #713f12; border: 1px solid #ca8a04; padding: 0.5rem 0.85rem; font-size: 0.825rem; font-weight: 800; min-width: 120px;">
                        TOTAL BIAYA<br>(Rp)
                    </th>

                    {{-- TOTAL WIP (OUTPUT HASIL JADI) --}}
                    <th colspan="4" style="background: #86efac; color: #064e3b; border: 1px solid #16a34a; padding: 0.4rem;">MANUAL</th>
                    <th colspan="4" style="background: #86efac; color: #064e3b; border: 1px solid #16a34a; padding: 0.4rem;">BERKO + BERKO ME</th>
                    <th rowspan="2" style="background: #4ade80; color: #064e3b; border: 1px solid #16a34a; padding: 0.5rem 0.75rem; font-size: 0.825rem; font-weight: 800; min-width: 95px;">
                        TOTAL WIP<br>(KG)
                    </th>
                    <th rowspan="2" style="background: #22c55e; color: #ffffff; border: 1px solid #15803d; padding: 0.5rem 0.75rem; font-size: 0.825rem; font-weight: 800; min-width: 85px;">
                        RENDEMEN<br>%
                    </th>
                    <th rowspan="2" style="background: #0284c7; color: #ffffff; border: 1px solid #0369a1; padding: 0.5rem 0.85rem; font-size: 0.825rem; font-weight: 800; min-width: 95px;">
                        HPP/KG<br>(Rp)
                    </th>
                    <th rowspan="2" style="position: sticky; right: 0; z-index: 25; background: #0284c7; color: #ffffff; border: 1px solid #0369a1; padding: 0.5rem 0.65rem; font-size: 0.825rem; font-weight: 800; min-width: 80px; text-align: center;">
                        AKSI
                    </th>
                </tr>

                {{-- HEADER LEVEL 2: RINCIAN SUB-KOLOM --}}
                <tr style="background: #bef264; color: #1a2e05; text-align: center; font-weight: 700; font-size: 0.7rem;">
                    {{-- Singkong --}}
                    <th style="border: 1px solid #65a30d; padding: 0.35rem 0.5rem; min-width: 75px;">KG</th>
                    <th style="border: 1px solid #65a30d; padding: 0.35rem 0.5rem; min-width: 100px;">Rp</th>

                    {{-- Minyak --}}
                    <th style="border: 1px solid #65a30d; padding: 0.35rem 0.5rem; min-width: 65px;">SAWIT</th>
                    <th style="border: 1px solid #65a30d; padding: 0.35rem 0.5rem; min-width: 65px;">KELAPA</th>
                    <th style="border: 1px solid #65a30d; padding: 0.35rem 0.5rem; min-width: 95px;">Rp</th>
                    <th style="border: 1px solid #65a30d; padding: 0.35rem 0.5rem; min-width: 60px;">%</th>

                    {{-- CNG --}}
                    <th style="border: 1px solid #65a30d; padding: 0.35rem 0.5rem; min-width: 75px;">MMBTU</th>
                    <th style="border: 1px solid #65a30d; padding: 0.35rem 0.5rem; min-width: 95px;">Rp</th>

                    {{-- Tenaga Kerja --}}
                    <th style="border: 1px solid #65a30d; padding: 0.35rem 0.4rem; min-width: 55px;">LANGSUNG</th>
                    <th style="border: 1px solid #65a30d; padding: 0.35rem 0.4rem; min-width: 55px;">TDK LANGSUNG</th>
                    <th style="border: 1px solid #65a30d; padding: 0.35rem 0.4rem; min-width: 50px;">TRAINING</th>
                    <th style="border: 1px solid #65a30d; padding: 0.35rem 0.5rem; min-width: 90px;">Rp</th>

                    {{-- Karton --}}
                    <th style="border: 1px solid #65a30d; padding: 0.35rem 0.5rem; min-width: 80px;">BARU</th>
                    <th style="border: 1px solid #65a30d; padding: 0.35rem 0.5rem; min-width: 80px;">BEKAS</th>

                    {{-- Lakban --}}
                    <th style="border: 1px solid #65a30d; padding: 0.35rem 0.5rem; min-width: 70px;">BESAR</th>
                    <th style="border: 1px solid #65a30d; padding: 0.35rem 0.5rem; min-width: 70px;">KECIL</th>

                    {{-- Sarung Tangan --}}
                    <th style="border: 1px solid #65a30d; padding: 0.35rem 0.5rem; min-width: 65px;">PLASTIK</th>
                    <th style="border: 1px solid #65a30d; padding: 0.35rem 0.5rem; min-width: 65px;">KAIN</th>

                    {{-- Limbah --}}
                    <th style="border: 1px solid #65a30d; padding: 0.35rem 0.5rem; min-width: 75px;">LIMBAH PADAT</th>
                    <th style="border: 1px solid #65a30d; padding: 0.35rem 0.5rem; min-width: 75px;">BAHAN KIMIA</th>

                    {{-- Manual Output --}}
                    <th style="border: 1px solid #16a34a; background: #bbf7d0; padding: 0.35rem 0.5rem; min-width: 75px;">ASIN BARCO</th>
                    <th style="border: 1px solid #16a34a; background: #bbf7d0; padding: 0.35rem 0.5rem; min-width: 70px;">ASIN SAWIT</th>
                    <th style="border: 1px solid #16a34a; background: #bbf7d0; padding: 0.35rem 0.5rem; min-width: 60px;">NO SALT</th>
                    <th style="border: 1px solid #16a34a; background: #bbf7d0; padding: 0.35rem 0.5rem; min-width: 60px;">BALO GLMB</th>

                    {{-- Berko --}}
                    <th style="border: 1px solid #16a34a; background: #bbf7d0; padding: 0.35rem 0.5rem; min-width: 65px;">BERKO</th>
                    <th style="border: 1px solid #16a34a; background: #bbf7d0; padding: 0.35rem 0.5rem; min-width: 65px;">BERKO ME</th>
                    <th style="border: 1px solid #16a34a; background: #bbf7d0; padding: 0.35rem 0.5rem; min-width: 70px;">TOTAL BERKO</th>
                    <th style="border: 1px solid #16a34a; background: #bbf7d0; padding: 0.35rem 0.5rem; min-width: 55px;">% BERKO</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($report['records'] as $row)
                    @php
                        $isWeekend = in_array(strtolower($row->hari_nm), ['sunday', 'minggu']);
                        $rendClass = $row->rendemen_persen >= 33.0 ? '#059669' : ($row->rendemen_persen >= 30.0 ? '#d97706' : '#dc2626');
                    @endphp
                    <tr style="background: {{ $isWeekend ? '#fef2f2' : '#ffffff' }}; text-align: right; border-bottom: 1px solid #e2e8f0; font-variant-numeric: tabular-nums;">
                        {{-- Sticky Col 1: Hari --}}
                        <td style="position: sticky; left: 0; z-index: 10; background: {{ $isWeekend ? '#fee2e2' : '#f8fafc' }}; text-align: left; font-weight: 700; color: #1e293b; border-right: 1px solid #cbd5e1; padding: 0.45rem 0.65rem;">
                            {{ $row->hari_nm }}
                        </td>
                        {{-- Sticky Col 2: Tanggal --}}
                        <td style="position: sticky; left: 90px; z-index: 10; background: {{ $isWeekend ? '#fee2e2' : '#f8fafc' }}; text-align: center; font-weight: 600; color: #475569; border-right: 2px solid #94a3b8; padding: 0.45rem 0.5rem;">
                            {{ Carbon\Carbon::parse($row->produksi_tgl)->format('d/m/y') }}
                        </td>

                        {{-- Shift & Batch WIP --}}
                        <td style="border-right: 1px solid #cbd5e1; padding: 0.45rem 0.65rem; text-align: left; vertical-align: middle;">
                            <div style="display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.2rem;">
                                @if ($row->shift_cd === 'A')
                                    <span class="badge-shift-a">Shift A</span>
                                @elseif ($row->shift_cd === 'B')
                                    <span class="badge-shift-b">Shift B</span>
                                @else
                                    <span style="font-size: 0.7rem; color: #94a3b8; font-style: italic;">Reguler</span>
                                @endif

                                @if ($row->jam_produksi)
                                    <span style="font-size: 0.7rem; color: #64748b;">🕒 {{ $row->jam_produksi }}</span>
                                @endif
                            </div>

                            @if ($row->batch_wip_no)
                                <div style="margin-bottom: 0.2rem;">
                                    <span class="badge-batch-wip">{{ $row->batch_wip_no }}</span>
                                </div>
                            @endif

                            <div style="font-size: 0.7rem; color: #475569; display: flex; align-items: center; gap: 0.35rem;">
                                @if ($row->qty_karton)
                                    <strong>{{ number_format($row->qty_karton, 0, ',', '.') }} box</strong>
                                @endif
                                @if ($row->varietas_singkong)
                                    <span style="background: #f1f5f9; padding: 0.05rem 0.3rem; border-radius: 3px; font-weight: 600; color: #0369a1;">
                                        {{ $row->varietas_singkong }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Singkong --}}
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->singkong_qty > 0 ? number_format($row->singkong_qty, 0, ',', '.') : '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->singkong_nilai > 0 ? number_format($row->singkong_nilai, 0, ',', '.') : '-' }}</td>

                        {{-- Minyak --}}
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->minyak_sawit_qty > 0 ? number_format($row->minyak_sawit_qty, 0, ',', '.') : '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->minyak_kelapa_qty > 0 ? number_format($row->minyak_kelapa_qty, 0, ',', '.') : '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->minyak_nilai > 0 ? number_format($row->minyak_nilai, 0, ',', '.') : '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem; color: #0284c7; font-weight: 600;">{{ $row->minyak_rasio_persen > 0 ? number_format($row->minyak_rasio_persen, 2, ',', '.') . '%' : '-' }}</td>

                        {{-- CNG --}}
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->cng_mmbtu > 0 ? number_format($row->cng_mmbtu, 3, ',', '.') : '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->cng_nilai > 0 ? number_format($row->cng_nilai, 0, ',', '.') : '-' }}</td>

                        {{-- Tenaga Kerja --}}
                        <td style="border-right: 1px solid #f1f5f9; text-align: center; padding: 0.45rem 0.4rem;">{{ $row->tk_langsung_org ?: '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; text-align: center; padding: 0.45rem 0.4rem;">{{ $row->tk_tidak_langsung_org ?: '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; text-align: center; padding: 0.45rem 0.4rem;">{{ $row->tk_training_org ?: '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->tk_total_nilai > 0 ? number_format($row->tk_total_nilai, 0, ',', '.') : '-' }}</td>

                        {{-- Bumbu --}}
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->bumbu_nilai > 0 ? number_format($row->bumbu_nilai, 0, ',', '.') : '-' }}</td>

                        {{-- Karton --}}
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->karton_baru_nilai > 0 ? number_format($row->karton_baru_nilai, 0, ',', '.') : '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->karton_bekas_nilai > 0 ? number_format($row->karton_bekas_nilai, 0, ',', '.') : '-' }}</td>

                        {{-- Plastik HD --}}
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->plastik_hd_nilai > 0 ? number_format($row->plastik_hd_nilai, 0, ',', '.') : '-' }}</td>

                        {{-- Lakban --}}
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->lakban_besar_nilai > 0 ? number_format($row->lakban_besar_nilai, 0, ',', '.') : '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->lakban_kecil_nilai > 0 ? number_format($row->lakban_kecil_nilai, 0, ',', '.') : '-' }}</td>

                        {{-- Tali & ATK --}}
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->tali_rafia_nilai > 0 ? number_format($row->tali_rafia_nilai, 0, ',', '.') : '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->fotocopy_nilai > 0 ? number_format($row->fotocopy_nilai, 0, ',', '.') : '-' }}</td>

                        {{-- Sarung Tangan --}}
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->sarung_tangan_plastik_nilai > 0 ? number_format($row->sarung_tangan_plastik_nilai, 0, ',', '.') : '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->sarung_tangan_kain_nilai > 0 ? number_format($row->sarung_tangan_kain_nilai, 0, ',', '.') : '-' }}</td>

                        {{-- Overhead Pabrik --}}
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->qc_pengawasan_nilai > 0 ? number_format($row->qc_pengawasan_nilai, 0, ',', '.') : '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->listrik_air_telp_nilai > 0 ? number_format($row->listrik_air_telp_nilai, 0, ',', '.') : '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->pemeliharaan_mesin_nilai > 0 ? number_format($row->pemeliharaan_mesin_nilai, 0, ',', '.') : '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->penyusutan_mesin_nilai > 0 ? number_format($row->penyusutan_mesin_nilai, 0, ',', '.') : '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->limbah_padat_nilai > 0 ? number_format($row->limbah_padat_nilai, 0, ',', '.') : '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->limbah_kimia_nilai > 0 ? number_format($row->limbah_kimia_nilai, 0, ',', '.') : '-' }}</td>

                        {{-- TOTAL BIAYA (KOLOM KUNING EMAS EXCEL) --}}
                        <td style="background: #fef9c3; font-weight: 800; color: #854d0e; border-left: 2px solid #eab308; border-right: 2px solid #eab308; padding: 0.45rem 0.75rem;">
                            {{ $row->total_biaya_produksi > 0 ? number_format($row->total_biaya_produksi, 0, ',', '.') : '-' }}
                        </td>

                        {{-- Manual Output WIP --}}
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->asin_barco_qty > 0 ? number_format($row->asin_barco_qty, 0, ',', '.') : '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->asin_sawit_qty > 0 ? number_format($row->asin_sawit_qty, 0, ',', '.') : '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->no_salt_qty > 0 ? number_format($row->no_salt_qty, 0, ',', '.') : '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->balo_gelombang_qty > 0 ? number_format($row->balo_gelombang_qty, 0, ',', '.') : '-' }}</td>

                        {{-- Berko --}}
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->berko_qty > 0 ? number_format($row->berko_qty, 2, ',', '.') : '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; padding: 0.45rem 0.5rem;">{{ $row->berko_me_qty > 0 ? number_format($row->berko_me_qty, 2, ',', '.') : '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; font-weight: 600; padding: 0.45rem 0.5rem;">{{ $row->total_berko_qty > 0 ? number_format($row->total_berko_qty, 2, ',', '.') : '-' }}</td>
                        <td style="border-right: 1px solid #f1f5f9; color: #64748b; padding: 0.45rem 0.5rem;">{{ $row->berko_persen > 0 ? number_format($row->berko_persen, 2, ',', '.') . '%' : '-' }}</td>

                        {{-- Total WIP --}}
                        <td style="background: #f0fdf4; font-weight: 800; color: #166534; border-right: 1px solid #bbf7d0; padding: 0.45rem 0.65rem;">
                            {{ $row->total_wip_qty > 0 ? number_format($row->total_wip_qty, 2, ',', '.') : '-' }}
                        </td>

                        {{-- Rendemen % --}}
                        <td style="background: {{ $row->rendemen_persen >= 33.0 ? '#f0fdf4' : '#fffbeb' }}; font-weight: 800; color: {{ $rendClass }}; border-right: 1px solid #e2e8f0; padding: 0.45rem 0.65rem;">
                            {{ $row->rendemen_persen > 0 ? number_format($row->rendemen_persen, 2, ',', '.') . '%' : '-' }}
                        </td>

                        {{-- HPP per Kg --}}
                        <td style="background: #f0f9ff; font-weight: 800; color: #0369a1; padding: 0.45rem 0.65rem;">
                            {{ $row->hpp_per_kg > 0 ? number_format($row->hpp_per_kg, 2, ',', '.') : '-' }}
                        </td>

                        {{-- Tombol Aksi Smart Dropdown --}}
                        <td style="position: sticky; right: 0; z-index: 10; background: #ffffff; text-align: center; padding: 0.35rem 0.5rem; vertical-align: middle; border-left: 2px solid #cbd5e1;">
                            <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, 'dropdown-prd-{{ $row->produksi_id }}')">
                                <span>Aksi</span>
                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div id="dropdown-prd-{{ $row->produksi_id }}" class="action-dropdown-menu">
                                <a href="{{ route('produksi.show', $row->produksi_id) }}" class="action-dropdown-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Detail &amp; Stiker Karton</span>
                                </a>
                                <a href="{{ route('produksi.cetak-stiker', $row->produksi_id) }}" target="_blank" class="action-dropdown-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    <span>Cetak Stiker Box</span>
                                </a>
                                <div class="action-dropdown-divider"></div>
                                <button type="button" class="action-dropdown-item danger-item" onclick="openDeleteProduksiModal({{ $row->produksi_id }}, '{{ $row->produksi_no }}', '{{ Carbon\Carbon::parse($row->produksi_tgl)->format('d/m/Y') }}', '{{ $row->shift_cd }}', '{{ $row->batch_wip_no }}')">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Hapus Catatan</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="42" style="text-align: center; padding: 3rem 1rem; color: #94a3b8; font-size: 0.9rem;">
                            Belum ada catatan lembar produksi harian untuk periode {{ $monthName }} {{ $year }}.<br>
                            <a href="{{ route('produksi.create') }}" style="color: #0284c7; font-weight: 700; text-decoration: underline; margin-top: 0.5rem; display: inline-block;">
                                Klik di sini untuk mencatat produksi hari ini &rarr;
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>

            {{-- FOOTER / BARIS TOTAL BULANAN (PERSIS FOOTER EXCEL) --}}
            @if ($report['count'] > 0)
                <tfoot style="position: sticky; bottom: 0; z-index: 20; background: #e2e8f0; font-weight: 800; text-align: right; box-shadow: 0 -2px 5px rgba(0,0,0,0.05);">
                    <tr style="border-top: 2px solid #0f172a; border-bottom: 2px solid #0f172a; color: #0f172a;">
                        <td colspan="3" style="position: sticky; left: 0; z-index: 25; background: #cbd5e1; text-align: center; font-size: 0.8rem; border-right: 2px solid #94a3b8; padding: 0.65rem;">
                            TOTAL {{ strtoupper($monthName) }}
                        </td>

                        {{-- Singkong --}}
                        <td style="padding: 0.5rem;">{{ number_format($tot['singkong_qty'], 0, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['singkong_nilai'], 0, ',', '.') }}</td>

                        {{-- Minyak --}}
                        <td style="padding: 0.5rem;">{{ number_format($tot['minyak_sawit_qty'], 0, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['minyak_kelapa_qty'], 0, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['minyak_nilai'], 0, ',', '.') }}</td>
                        <td style="padding: 0.5rem; color: #0284c7;">{{ number_format($tot['minyak_rasio_persen'], 2, ',', '.') }}%</td>

                        {{-- CNG --}}
                        <td style="padding: 0.5rem;">{{ number_format($tot['cng_mmbtu'], 3, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['cng_nilai'], 0, ',', '.') }}</td>

                        {{-- Tenaga Kerja --}}
                        <td style="text-align: center; padding: 0.5rem;">{{ number_format($tot['tk_langsung_org']) }}</td>
                        <td style="text-align: center; padding: 0.5rem;">{{ number_format($tot['tk_tidak_langsung_org']) }}</td>
                        <td style="text-align: center; padding: 0.5rem;">{{ number_format($tot['tk_training_org']) }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['tk_total_nilai'], 0, ',', '.') }}</td>

                        {{-- Bumbu & Kemasan --}}
                        <td style="padding: 0.5rem;">{{ number_format($tot['bumbu_nilai'], 0, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['karton_baru_nilai'], 0, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['karton_bekas_nilai'], 0, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['plastik_hd_nilai'], 0, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['lakban_besar_nilai'], 0, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['lakban_kecil_nilai'], 0, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['tali_rafia_nilai'], 0, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['fotocopy_nilai'], 0, ',', '.') }}</td>

                        {{-- Overhead --}}
                        <td style="padding: 0.5rem;">{{ number_format($tot['sarung_tangan_plastik_nilai'], 0, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['sarung_tangan_kain_nilai'], 0, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['qc_pengawasan_nilai'], 0, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['listrik_air_telp_nilai'], 0, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['pemeliharaan_mesin_nilai'], 0, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['penyusutan_mesin_nilai'], 0, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['limbah_padat_nilai'], 0, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['limbah_kimia_nilai'], 0, ',', '.') }}</td>

                        {{-- TOTAL BIAYA (KUNING EMAS) --}}
                        <td style="background: #facc15; color: #713f12; border-left: 2px solid #ca8a04; border-right: 2px solid #ca8a04; font-size: 0.85rem; padding: 0.65rem 0.75rem;">
                            Rp {{ number_format($tot['total_biaya_produksi'], 0, ',', '.') }}
                        </td>

                        {{-- Manual Output --}}
                        <td style="padding: 0.5rem;">{{ number_format($tot['asin_barco_qty'], 0, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['asin_sawit_qty'], 0, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['no_salt_qty'], 0, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['balo_gelombang_qty'], 0, ',', '.') }}</td>

                        {{-- Berko --}}
                        <td style="padding: 0.5rem;">{{ number_format($tot['berko_qty'], 2, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['berko_me_qty'], 2, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['total_berko_qty'], 2, ',', '.') }}</td>
                        <td style="padding: 0.5rem;">{{ number_format($tot['berko_persen'], 2, ',', '.') }}%</td>

                        {{-- Grand Total WIP --}}
                        <td style="background: #86efac; color: #064e3b; font-size: 0.85rem; padding: 0.65rem 0.75rem;">
                            {{ number_format($tot['total_wip_qty'], 2, ',', '.') }}
                        </td>

                        {{-- Rata-rata Rendemen --}}
                        <td style="background: #22c55e; color: #ffffff; font-size: 0.85rem; padding: 0.65rem 0.75rem;">
                            {{ number_format($tot['rendemen_persen'], 2, ',', '.') }}%
                        </td>

                        {{-- Rata-rata HPP per Kg --}}
                        <td style="background: #0284c7; color: #ffffff; font-size: 0.85rem; padding: 0.65rem 0.75rem;">
                            Rp {{ number_format($tot['hpp_per_kg'], 2, ',', '.') }}
                        </td>

                        {{-- Sticky Col Right Aksi --}}
                        <td style="position: sticky; right: 0; z-index: 25; background: #0284c7;"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

@include('produksi.partials.modal-delete-confirm')

@push('scripts')
    <script src="{{ asset('js/produksi/produksi-index.js') }}"></script>
@endpush
@endsection
