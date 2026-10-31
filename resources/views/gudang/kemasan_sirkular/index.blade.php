@extends('layouts.app')

@section('title', 'Penerimaan Kemasan Sirkular - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/kemasan_sirkular/sirkular-index.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="sirkular-container">
    {{-- HEADER --}}
    <div class="sirkular-header">
        <div class="sirkular-title-group">
            <h1>
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                Penerimaan Kemasan Sirkular
            </h1>
            <p>Pencatatan &amp; Sortir Fisik Kardus Balikan, Palet, serta Rekonsiliasi Surat Jalan Pengiriman (DO)</p>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <a href="{{ route('gudang.kemasan-sirkular.mobile') }}" class="btn btn-outline-primary" style="padding: 0.6rem 1rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.825rem;" title="Buka mode input HP / Tablet">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Mode HP (Checker)</span>
            </a>
            <a href="{{ route('gudang.kemasan-sirkular.create') }}" class="btn btn-primary" style="padding: 0.6rem 1.25rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Catat Kemasan Masuk</span>
            </a>
        </div>
    </div>

    {{-- BARIS KPI METRIK CEPAT --}}
    @php
        $totalDokumen = $list->total();
        $sumKembali = $list->sum('total_kembali_qty');
        $sumMulus = $list->sum('total_mulus_qty');
        $sumRusak = $list->sum('total_rusak_qty');
    @endphp
    <div class="sirkular-kpi-bar">
        <div class="sirkular-kpi-card">
            <div>
                <div class="sirkular-kpi-label">Total Dokumen</div>
                <div class="sirkular-kpi-val">{{ number_format($totalDokumen, 0, ',', '.') }}</div>
            </div>
            <div style="background: #f1f5f9; padding: 0.65rem; border-radius: 8px; color: #475569;">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <div class="sirkular-kpi-card">
            <div>
                <div class="sirkular-kpi-label">Fisik Kardus Kembali</div>
                <div class="sirkular-kpi-val">{{ number_format($sumKembali, 0, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Box</span></div>
            </div>
            <div style="background: #e0f2fe; padding: 0.65rem; border-radius: 8px; color: #0284c7;">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
        <div class="sirkular-kpi-card">
            <div>
                <div class="sirkular-kpi-label">Layak Puter Ulang (Mulus)</div>
                <div class="sirkular-kpi-val" style="color: #059669;">{{ number_format($sumMulus, 0, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #059669;">Box</span></div>
            </div>
            <div style="background: #ecfdf5; padding: 0.65rem; border-radius: 8px; color: #059669;">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="sirkular-kpi-card">
            <div>
                <div class="sirkular-kpi-label">Reject / Rusak (Manual)</div>
                <div class="sirkular-kpi-val" style="color: #d97706;">{{ number_format($sumRusak, 0, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #d97706;">Box</span></div>
            </div>
            <div style="background: #fffbeb; padding: 0.65rem; border-radius: 8px; color: #d97706;">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
    </div>

    {{-- TABEL DATA UTAMA --}}
    <div class="sirkular-table-card">
        <form method="GET" action="{{ route('gudang.kemasan-sirkular.index') }}" class="sirkular-filter-bar">
            <div style="display: flex; gap: 0.65rem; align-items: center; flex: 1; min-width: 280px;">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari No. Dokumen, Surat Jalan, Nopol, Sopir..." style="font-size: 0.825rem;">
                <select name="customer_id" class="form-control" style="font-size: 0.825rem; max-width: 250px;">
                    <option value="">-- Semua Customer / Mitra --</option>
                    @foreach($customerList as $c)
                        <option value="{{ $c->customer_id }}" {{ request('customer_id') == $c->customer_id ? 'selected' : '' }}>
                            {{ $c->customer_nm }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-secondary" style="font-size: 0.825rem; padding: 0.5rem 0.85rem;">Filter</button>
                @if(request()->anyFilled(['search', 'customer_id']))
                    <a href="{{ route('gudang.kemasan-sirkular.index') }}" class="btn btn-outline-secondary" style="font-size: 0.825rem;">Reset</a>
                @endif
            </div>
        </form>

        <div style="overflow-x: auto;">
            <table class="sirkular-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No.</th>
                        <th style="min-width: 180px;">Dokumen Penerimaan</th>
                        <th style="min-width: 220px;">Customer &amp; Ref Pengiriman</th>
                        <th style="min-width: 180px;">Ekspedisi &amp; Armada</th>
                        <th style="width: 95px; text-align: right;">Terkirim</th>
                        <th style="width: 95px; text-align: right;">Kembali</th>
                        <th style="width: 125px; text-align: right;">Mulus (Puteran)</th>
                        <th style="width: 80px; text-align: right;">BMG</th>
                        <th style="width: 115px; text-align: right;">Rusak (Reject)</th>
                        <th style="width: 110px; text-align: center;">Status</th>
                        <th style="width: 90px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($list as $idx => $row)
                        @php
                            $menuId = 'menu_sirkular_' . $row->kemasan_sirkular_id;
                        @endphp
                        <tr>
                            <td style="text-align: center; color: #64748b; font-weight: 600;">
                                {{ $list->firstItem() + $idx }}
                            </td>
                            <td>
                                <strong style="color: #0f172a; font-family: monospace;">{{ $row->sirkular_no }}</strong>
                                <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                    {{ \Carbon\Carbon::parse($row->sirkular_tgl)->format('d/m/Y') }} &bull; {{ $row->gudang?->gudang_nm }}
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #334155;">{{ $row->customer?->customer_nm ?? 'Mitra Umum' }}</div>
                                @if($row->pengeluaran)
                                    <div style="font-size: 0.725rem; color: #0284c7; margin-top: 2px;">
                                        DO: <strong>{{ $row->pengeluaran->pengeluaran_no }}</strong>
                                    </div>
                                @endif
                                @if($row->surat_jalan_customer_no)
                                    <div style="font-size: 0.7rem; color: #64748b;">
                                        SJ Balik: {{ $row->surat_jalan_customer_no }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #0f172a;">{{ $row->supir_nm ?: '-' }}</div>
                                <div style="font-size: 0.725rem; color: #64748b;">
                                    {{ $row->nopol_kendaraan ?: 'Tanpa Nopol' }} {{ $row->ekspedisi_nm ? "({$row->ekspedisi_nm})" : '' }}
                                </div>
                            </td>
                            <td class="td-num">
                                {{ number_format($row->total_kirim_qty, 0, ',', '.') }}
                            </td>
                            <td class="td-num td-bold">
                                {{ number_format($row->total_kembali_qty, 0, ',', '.') }}
                            </td>
                            <td class="td-num td-mulus">
                                {{ number_format($row->total_mulus_qty, 0, ',', '.') }}
                            </td>
                            <td class="td-num td-bmg">
                                {{ number_format($row->total_bmg_qty, 0, ',', '.') }}
                            </td>
                            <td class="td-num td-rusak">
                                {{ number_format($row->total_rusak_qty, 0, ',', '.') }}
                            </td>
                            <td style="text-align: center;">
                                @if($row->status_cd === 'VOID')
                                    <span class="badge-status-void">DIBATALKAN</span>
                                @else
                                    <span class="badge-status-completed">SELESAI</span>
                                @endif
                            </td>
                            <td style="text-align: center; position: relative;">
                                {{-- SMART ACTION DROPDOWN STANDAR PO --}}
                                <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div id="{{ $menuId }}" class="action-dropdown-menu">
                                    <a href="{{ route('gudang.kemasan-sirkular.show', $row->kemasan_sirkular_id) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Lihat Detail</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="text-align: center; padding: 2.5rem; color: #94a3b8;">
                                Belum ada riwayat penerimaan kemasan sirkular.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($list->hasPages())
            <div style="padding: 1rem 1.25rem; border-top: 1px solid #e2e8f0;">
                {{ $list->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/gudang/kemasan_sirkular/sirkular-index.js') }}?v={{ time() }}"></script>
@endpush
@endsection

