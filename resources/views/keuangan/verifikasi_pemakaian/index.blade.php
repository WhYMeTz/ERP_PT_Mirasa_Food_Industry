@extends('layouts.app')

@section('title', 'Verifikasi Pemakaian Bahan - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/verifikasi_pemakaian/verifikasi-pemakaian.css') }}">
@endpush

@section('content')
<div style="margin-bottom: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.6rem;">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #ea580c;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Verifikasi Pemakaian Bahan (HPP)
            </h1>
            <p style="font-size: 0.85rem; color: #64748b; margin-top: 0.25rem;">
                Meja kerja pemeriksaan fisik pengeluaran bahan baku, validasi batch, pemotongan stok &amp; penerbitan jurnal umum.
            </p>
        </div>
        <div>
            <a href="{{ route('gudang.pemakaian.create') }}" class="btn btn-primary" style="background: #0284c7;">
                + Input Pemakaian Baru
            </a>
        </div>
    </div>
</div>

{{-- 1. KPI SUMMARY CARDS --}}
<div class="kpi-container">
    <div class="kpi-card" style="border-left: 4px solid #f59e0b;">
        <div class="kpi-icon-wrapper" style="background: #fef3c7; color: #d97706;">
            ⏳
        </div>
        <div>
            <div class="kpi-val" style="color: #b45309;">{{ $kpis['count_draft'] }}</div>
            <div class="kpi-lbl">Menunggu Pemeriksaan (Draft)</div>
        </div>
    </div>

    <div class="kpi-card" style="border-left: 4px solid #ea580c;">
        <div class="kpi-icon-wrapper" style="background: #ffedd5; color: #c2410c;">
            📋
        </div>
        <div>
            <div class="kpi-val" style="color: #9a3412;">Rp {{ number_format($kpis['total_nominal_draft'], 0, ',', '.') }}</div>
            <div class="kpi-lbl">Estimasi HPP Menunggu Posting</div>
        </div>
    </div>

    <div class="kpi-card" style="border-left: 4px solid #059669;">
        <div class="kpi-icon-wrapper" style="background: #d1fae5; color: #047857;">
            ✓
        </div>
        <div>
            <div class="kpi-val" style="color: #065f46;">{{ $kpis['count_posted_month'] }}</div>
            <div class="kpi-lbl">Terposting Bulan Ini</div>
        </div>
    </div>

    <div class="kpi-card" style="border-left: 4px solid #0284c7;">
        <div class="kpi-icon-wrapper" style="background: #e0f2fe; color: #0369a1;">
            💼
        </div>
        <div>
            <div class="kpi-val" style="color: #0c4a6e;">Rp {{ number_format($kpis['total_nominal_posted_month'], 0, ',', '.') }}</div>
            <div class="kpi-lbl">Total HPP Masuk Buku Besar (Bulan Ini)</div>
        </div>
    </div>
</div>

{{-- 2. TAB NAVIGASI & KARTU DATA --}}
<div class="verifikasi-card">
    <div class="tabs-wrapper">
        <a href="{{ route('keuangan.verifikasi_pemakaian.index', array_merge(request()->query(), ['tab' => 'DRAFT'])) }}" 
           class="tab-btn {{ $statusTab === 'DRAFT' ? 'active' : '' }}">
            <span>Menunggu Pemeriksaan</span>
            <span class="tab-badge" style="{{ $statusTab === 'DRAFT' ? 'background: #fef3c7; color: #92400e;' : 'background: #e2e8f0; color: #475569;' }}">
                {{ $kpis['count_draft'] }}
            </span>
        </a>
        <a href="{{ route('keuangan.verifikasi_pemakaian.index', array_merge(request()->query(), ['tab' => 'POSTED'])) }}" 
           class="tab-btn {{ $statusTab === 'POSTED' ? 'active' : '' }}">
            <span>Riwayat Terposting</span>
            <span class="tab-badge" style="{{ $statusTab === 'POSTED' ? 'background: #d1fae5; color: #065f46;' : 'background: #e2e8f0; color: #475569;' }}">
                ✓ POSTED
            </span>
        </a>
    </div>

    {{-- TOOLBAR FILTER --}}
    <div style="background: #f8fafc; padding: 0.85rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
        <form action="{{ route('keuangan.verifikasi_pemakaian.index') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
            <input type="hidden" name="tab" value="{{ $statusTab }}">
            
            <div style="flex: 1 1 240px; min-width: 180px;">
                <input type="text" name="search" value="{{ $search ?? '' }}" 
                       placeholder="Cari No Dokumen, tujuan, bahan..." 
                       class="form-control" 
                       style="padding: 0.45rem 0.75rem; font-size: 0.85rem; width: 100%;">
            </div>

            <div style="flex: 0 1 180px; min-width: 140px;">
                <select name="gudang_id" class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.85rem; width: 100%;" onchange="this.form.submit()">
                    <option value="">-- Semua Gudang --</option>
                    @foreach ($gudangList as $gdg)
                        <option value="{{ $gdg->gudang_id }}" {{ ($gudangId == $gdg->gudang_id) ? 'selected' : '' }}>
                            {{ $gdg->gudang_nm }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: inline-flex; align-items: center; gap: 0.35rem; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.2rem 0.6rem; height: 35px;">
                <input type="date" name="start_date" value="{{ $startDate ?? '' }}" title="Dari Tanggal" 
                       style="border: none; background: transparent; font-size: 0.825rem; padding: 0; outline: none; width: 110px;">
                <span style="font-size: 0.75rem; color: #94a3b8;">s/d</span>
                <input type="date" name="end_date" value="{{ $endDate ?? '' }}" title="Sampai Tanggal" 
                       style="border: none; background: transparent; font-size: 0.825rem; padding: 0; outline: none; width: 110px;">
            </div>

            <button type="submit" class="btn btn-primary btn-sm" style="padding: 0.45rem 0.85rem; font-size: 0.825rem;">
                Filter
            </button>
            <a href="{{ route('keuangan.verifikasi_pemakaian.index', ['tab' => $statusTab]) }}" class="btn btn-secondary btn-sm" style="padding: 0.45rem 0.85rem; font-size: 0.825rem;">
                Reset
            </a>
        </form>
    </div>

    {{-- TABEL DATA --}}
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th style="min-width: 150px;">No. Pengeluaran</th>
                    <th style="min-width: 110px;">Tanggal Keluar</th>
                    <th style="min-width: 140px;">Gudang Asal</th>
                    <th style="min-width: 170px;">Tujuan / SPK</th>
                    <th style="min-width: 180px;">Rincian Bahan &amp; Batch</th>
                    <th style="min-width: 130px; text-align: right;">Total Nilai (HPP)</th>
                    <th style="min-width: 110px; text-align: center;">Status</th>
                    <th style="width: 120px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dataList as $index => $hdr)
                    @php
                        $docTotal = $hdr->details->sum('total_harga');
                        $menuId = 'menuActionVerif_' . $hdr->pakai_id;
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="text-align: center; color: #64748b; font-size: 0.85rem;">
                            {{ $dataList->firstItem() + $index }}
                        </td>
                        <td>
                            <a href="{{ route('keuangan.verifikasi_pemakaian.show', $hdr->pakai_id) }}" style="font-weight: 700; color: #ea580c; text-decoration: none; font-size: 0.875rem;">
                                {{ $hdr->pakai_no }}
                            </a>
                            <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                Oleh: {{ $hdr->created_by ?? '-' }}
                            </div>
                        </td>
                        <td>
                            <span style="font-size: 0.85rem; color: #334155; font-weight: 600;">
                                {{ \Carbon\Carbon::parse($hdr->pakai_tgl)->format('d/m/Y') }}
                            </span>
                        </td>
                        <td>
                            <span style="font-size: 0.85rem; color: #0f172a;">{{ $hdr->gudang?->gudang_nm ?? '-' }}</span>
                        </td>
                        <td>
                            <span class="badge" style="background: #ffedd5; color: #c2410c; font-weight: 700; font-size: 0.75rem;">
                                {{ $hdr->tujuan_pemakaian }}
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 0.2rem; max-width: 260px;">
                                @foreach ($hdr->details->take(2) as $dtl)
                                    <div style="font-size: 0.8rem; color: #1e293b; display: flex; justify-content: space-between;">
                                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            {{ $dtl->barang?->barang_nm }}
                                        </span>
                                        <span style="font-family: monospace; font-size: 0.725rem; background: #f1f5f9; padding: 0.1rem 0.3rem; border-radius: 3px; margin-left: 0.3rem;">
                                            {{ $dtl->batch_no }}
                                        </span>
                                    </div>
                                @endforeach
                                @if($hdr->details->count() > 2)
                                    <span style="font-size: 0.725rem; color: #64748b;">
                                        + {{ $hdr->details->count() - 2 }} bahan lainnya...
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <strong style="color: #0f172a; font-family: monospace; font-size: 0.925rem;">
                                Rp {{ number_format((float) $docTotal, 0, ',', '.') }}
                            </strong>
                        </td>
                        <td style="text-align: center;">
                            @if($hdr->status_st === 'DRAFT')
                                <span class="badge-status badge-draft">
                                    ⏳ DRAFT
                                </span>
                            @else
                                <span class="badge-status badge-posted">
                                    ✓ POSTED
                                </span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            {{-- STANDAR SMART ACTION DROPDOWN ERP MIRASA --}}
                            <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                <span>Aksi</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div id="{{ $menuId }}" class="action-dropdown-menu">
                                <a href="{{ route('keuangan.verifikasi_pemakaian.show', $hdr->pakai_id) }}" class="action-dropdown-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Periksa Rincian</span>
                                </a>

                                @if($hdr->status_st === 'DRAFT')
                                    <button type="button" class="action-dropdown-item" onclick="openModalApprove({{ $hdr->pakai_id }}, '{{ $hdr->pakai_no }}', {{ $docTotal }})" style="color: #059669;">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Setujui &amp; Terbitkan Jurnal</span>
                                    </button>
                                    <button type="button" class="action-dropdown-item danger-item" onclick="openModalReject({{ $hdr->pakai_id }}, '{{ $hdr->pakai_no }}')">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <span>Tolak / Batalkan</span>
                                    </button>
                                @else
                                    @if($hdr->jurnal_id)
                                        <button type="button" class="action-dropdown-item" onclick="openModalDetailJurnal({{ $hdr->jurnal_id }})" style="color: #0284c7;">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>Lihat Jurnal Umum</span>
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            @if($statusTab === 'DRAFT')
                                Tidak ada antrean pemakaian bahan yang menunggu pemeriksaan. Semua dokumen aman terkendali!
                            @else
                                Belum ada riwayat dokumen pemakaian bahan yang terposting.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($dataList->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9;">
            {{ $dataList->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODALS PARTIALS --}}
@include('keuangan.verifikasi_pemakaian.partials.modal-approve')
@include('keuangan.verifikasi_pemakaian.partials.modal-reject')
@include('keuangan.jurnal.partials.modal-detail-jurnal')

@endsection

@push('scripts')
    <script src="{{ asset('js/keuangan/verifikasi_pemakaian/verifikasi-pemakaian.js') }}"></script>
@endpush
