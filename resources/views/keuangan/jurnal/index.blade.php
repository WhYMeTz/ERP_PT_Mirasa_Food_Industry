@extends('layouts.app')

@section('title', 'Jurnal Umum (General Journal) - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/jurnal/jurnal-index.css') }}?v={{ filemtime(public_path('css/keuangan/jurnal/jurnal-index.css')) }}">
@endpush

@section('content')
<div class="jurnal-page-wrapper">
    {{-- HEADER HALAMAN & ACTIONS --}}
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span style="font-size: 1.35rem;">📑</span>
                <h1 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0;">
                    Jurnal Umum (General Journal Ledger)
                </h1>
            </div>
            <p style="font-size: 0.8rem; color: #64748b; margin: 0;">
                Buku harian transaksi keuangan berpasangan (Double Entry) otomatis dari Hutang, Pembayaran Kas &amp; Bank.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <button type="button" 
                    class="btn btn-secondary" 
                    onclick="toggleExpandAll()" 
                    style="font-size: 0.8rem; font-weight: 700; padding: 0.5rem 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                <span id="textToggleExpandAll">Buka Semua Rincian</span>
            </button>

            <a href="{{ route('keuangan.coa.index') }}" 
               class="btn btn-secondary" 
               style="font-size: 0.8rem; font-weight: 700; padding: 0.5rem 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Bagan Akun (COA)</span>
            </a>
        </div>
    </div>

    {{-- KARTU METRIK RINGKASAN --}}
    <div class="metric-grid-4">
        <div class="metric-card">
            <div class="metric-icon-box" style="background: #eff6ff; color: #1d4ed8;">
                📑
            </div>
            <div class="metric-info">
                <span class="metric-label">Total Entri Jurnal</span>
                <span class="metric-val">{{ number_format($metrics['total_entries'], 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon-box" style="background: #f0fdf4; color: #15803d;">
                📈
            </div>
            <div class="metric-info">
                <span class="metric-label">Akumulasi Debet</span>
                <span class="metric-val" style="color: #15803d;">Rp {{ number_format($metrics['total_debet'], 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon-box" style="background: #faf5ff; color: #7e22ce;">
                📉
            </div>
            <div class="metric-info">
                <span class="metric-label">Akumulasi Kredit</span>
                <span class="metric-val" style="color: #7e22ce;">Rp {{ number_format($metrics['total_kredit'], 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon-box" style="background: {{ $metrics['is_all_balanced'] ? '#dcfce7' : '#fee2e2' }}; color: {{ $metrics['is_all_balanced'] ? '#166534' : '#b91c1c' }};">
                {{ $metrics['is_all_balanced'] ? '⚖️' : '⚠️' }}
            </div>
            <div class="metric-info">
                <span class="metric-label">Keseimbangan Buku</span>
                <span class="metric-val" style="color: {{ $metrics['is_all_balanced'] ? '#166534' : '#b91c1c' }}; font-size: 1rem;">
                    {{ $metrics['is_all_balanced'] ? '100% Balanced' : $metrics['count_unbalanced'] . ' Unbalanced' }}
                </span>
            </div>
        </div>
    </div>

    {{-- KARTU FILTER TOOLBAR --}}
    <div class="jurnal-filter-card">
        <form method="GET" action="{{ route('keuangan.jurnal.index') }}" id="jurnalFilterForm">
            <div class="filter-grid">
                <div class="filter-field" style="flex: 1; min-width: 220px;">
                    <label class="filter-label">Cari No. Jurnal / Ref / Keterangan</label>
                    <input type="text" 
                           name="search" 
                           value="{{ $filters['search'] }}" 
                           placeholder="Cari No. JRN, No. AP, BKK..." 
                           class="form-control" 
                           style="height: 36px; font-size: 0.8125rem;">
                </div>

                <div class="filter-field" style="width: 180px;">
                    <label class="filter-label">Tipe Transaksi</label>
                    <select name="tipe_transaksi" class="form-control" style="height: 36px; font-size: 0.8125rem;">
                        <option value="">Semua Tipe Transaksi</option>
                        @foreach ($tipeTransaksiList as $key => $lbl)
                            <option value="{{ $key }}" {{ $filters['tipe_transaksi'] === $key ? 'selected' : '' }}>
                                {{ $lbl }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-field" style="width: 220px;">
                    <label class="filter-label">Filter Akun COA Terkait</label>
                    <select name="coa_id" class="form-control" style="height: 36px; font-size: 0.8125rem;">
                        <option value="">Semua Akun COA</option>
                        @foreach ($coaList as $c)
                            <option value="{{ $c->coa_id }}" {{ ($filters['coa_id'] == $c->coa_id) ? 'selected' : '' }}>
                                {{ $c->kode_akun }} - {{ $c->nama_akun }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-field" style="width: 140px;">
                    <label class="filter-label">Dari Tanggal</label>
                    <input type="date" 
                           name="start_date" 
                           value="{{ $filters['start_date'] }}" 
                           class="form-control" 
                           style="height: 36px; font-size: 0.8125rem;">
                </div>

                <div class="filter-field" style="width: 140px;">
                    <label class="filter-label">Sampai Tanggal</label>
                    <input type="date" 
                           name="end_date" 
                           value="{{ $filters['end_date'] }}" 
                           class="form-control" 
                           style="height: 36px; font-size: 0.8125rem;">
                </div>

                <div style="display: flex; gap: 0.35rem; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary" style="height: 36px; padding: 0 0.85rem; font-size: 0.8125rem; font-weight: 700;">
                        🔍 Filter
                    </button>
                    @if (!empty($filters['search']) || !empty($filters['tipe_transaksi']) || !empty($filters['start_date']) || !empty($filters['end_date']) || !empty($filters['coa_id']))
                        <a href="{{ route('keuangan.jurnal.index') }}" class="btn btn-secondary" style="height: 36px; padding: 0 0.75rem; font-size: 0.8125rem; display: inline-flex; align-items: center;">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- TABEL JURNAL UMUM DENGAN EXPANDABLE SUB-ROWS --}}
    <div class="jurnal-table-wrapper">
        <div style="overflow-x: auto;">
            <table class="jurnal-table">
                <thead>
                    <tr>
                        <th style="width: 35px; text-align: center;"></th>
                        <th style="width: 110px;">Tanggal</th>
                        <th style="width: 155px;">No. Jurnal</th>
                        <th style="width: 140px;">Tipe &amp; Ref</th>
                        <th>Keterangan / Memo Jurnal</th>
                        <th style="text-align: right; width: 140px;">Total Debet</th>
                        <th style="text-align: right; width: 140px;">Total Kredit</th>
                        <th style="width: 100px; text-align: center;">Status</th>
                        <th style="width: 90px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jurnals as $j)
                        @php
                            $tipeClass = match($j->tipe_transaksi) {
                                'HUTANG_PEMBELIAN' => 'tipe-hutang',
                                'BAYAR_HUTANG'     => 'tipe-bayar',
                                'KAS_MASUK'        => 'tipe-masuk',
                                default            => 'tipe-umum',
                            };
                            $menuId = 'menu-action-jurnal-' . $j->jurnal_id;
                        @endphp

                        {{-- BARIS UTAMA HEADER JURNAL --}}
                        <tr class="jurnal-main-row" onclick="toggleJurnalDetails({{ $j->jurnal_id }}, event)">
                            <td style="text-align: center; color: #94a3b8;">
                                <svg id="icon-toggle-{{ $j->jurnal_id }}" 
                                     class="toggle-arrow-icon" 
                                     width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" 
                                     style="transition: transform 0.2s ease;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </td>
                            <td style="font-weight: 700; color: #0f172a;">
                                {{ \Carbon\Carbon::parse($j->tgl_jurnal)->format('d/m/Y') }}
                            </td>
                            <td>
                                <strong style="font-family: monospace; color: #0284c7; font-size: 0.85rem;">
                                    {{ $j->jurnal_no }}
                                </strong>
                            </td>
                            <td>
                                <div class="badge-transaksi {{ $tipeClass }}">
                                    {{ $tipeTransaksiList[$j->tipe_transaksi] ?? $j->tipe_transaksi }}
                                </div>
                                @if ($j->ref_no)
                                    <div style="font-size: 0.7rem; font-family: monospace; color: #64748b; margin-top: 2px;">
                                        Ref: {{ $j->ref_no }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #1e293b; line-height: 1.35;">
                                    {{ $j->keterangan }}
                                </div>
                                <div style="font-size: 0.6875rem; color: #94a3b8; margin-top: 2px;">
                                    User: {{ $j->created_by ?: 'SYSTEM' }} &bull; {{ $j->details->count() }} Baris Akun
                                </div>
                            </td>
                            <td style="text-align: right; font-weight: 800; color: #0f172a; font-family: monospace;">
                                Rp {{ number_format($j->total_debet, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-weight: 800; color: #0f172a; font-family: monospace;">
                                Rp {{ number_format($j->total_kredit, 0, ',', '.') }}
                            </td>
                            <td style="text-align: center;">
                                @if ($j->is_balanced)
                                    <span style="font-size: 0.7rem; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-weight: 800; padding: 2px 6px; border-radius: 4px;">
                                        ✓ BALANCED
                                    </span>
                                @else
                                    <span style="font-size: 0.7rem; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; font-weight: 800; padding: 2px 6px; border-radius: 4px;">
                                        ⚠️ UNBALANCED
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: center;" onclick="event.stopPropagation()">
                                <button type="button" 
                                        class="btn-action-trigger" 
                                        onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div id="{{ $menuId }}" class="action-dropdown-menu">
                                    <button type="button" 
                                            class="action-dropdown-item" 
                                            onclick="openModalDetailJurnal({{ $j->jurnal_id }})">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Lihat Detail Modal</span>
                                    </button>

                                    @if ($j->tipe_transaksi === 'HUTANG_PEMBELIAN' && $j->ref_id)
                                        <a href="{{ route('keuangan.hutang.show', $j->ref_id) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>Buka Dokumen Tagihan Hutang</span>
                                        </a>
                                    @elseif ($j->tipe_transaksi === 'BAYAR_HUTANG' && $j->ref_id)
                                        <a href="{{ route('keuangan.bayar.show', $j->ref_id) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            <span>Buka Dokumen BKK Pelunasan</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- BARIS ACCORDION RINCIAN DOUBLE ENTRY --}}
                        <tr id="subrow-jurnal-{{ $j->jurnal_id }}" class="jurnal-sub-row" style="display: none;">
                            <td colspan="9" style="padding: 0.75rem 1.25rem 1rem 3rem;">
                                <div style="border-left: 3px solid #0284c7; padding-left: 0.85rem;">
                                    <div style="font-size: 0.725rem; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 0.4rem; display: flex; align-items: center; justify-content: space-between;">
                                        <span>Rincian Jurnal Berpasangan (Debet / Kredit)</span>
                                        <span style="font-weight: 600; color: #64748b; text-transform: none;">Klik baris untuk menutup</span>
                                    </div>

                                    <table class="journal-entry-subtable">
                                        <thead>
                                            <tr>
                                                <th style="width: 250px;">Kode &amp; Nama Akun (COA)</th>
                                                <th>Keterangan Baris Akun</th>
                                                <th style="text-align: right; width: 140px;">Debet (Rp)</th>
                                                <th style="text-align: right; width: 140px;">Kredit (Rp)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($j->details as $d)
                                                <tr>
                                                    <td>
                                                        <span class="coa-code-pill">{{ $d->coa?->kode_akun ?? '-' }}</span>
                                                        <strong style="color: #0f172a; margin-left: 6px;">{{ $d->coa?->nama_akun ?? '-' }}</strong>
                                                    </td>
                                                    <td style="color: #64748b;">
                                                        {{ $d->keterangan }}
                                                    </td>
                                                    <td style="text-align: right; font-weight: 700; color: {{ $d->debet > 0 ? '#0f172a' : '#cbd5e1' }}; font-family: monospace;">
                                                        {{ $d->debet > 0 ? 'Rp ' . number_format($d->debet, 0, ',', '.') : '-' }}
                                                    </td>
                                                    <td style="text-align: right; font-weight: 700; color: {{ $d->kredit > 0 ? '#0f172a' : '#cbd5e1' }}; font-family: monospace;">
                                                        {{ $d->kredit > 0 ? 'Rp ' . number_format($d->kredit, 0, ',', '.') : '-' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr style="background: #f8fafc; font-weight: 800; border-top: 1.5px solid #e2e8f0;">
                                                <td colspan="2" style="text-align: right; color: #475569; font-size: 0.725rem;">
                                                    Keseimbangan Jurnal:
                                                </td>
                                                <td style="text-align: right; color: #0284c7; font-family: monospace;">
                                                    Rp {{ number_format($j->total_debet, 0, ',', '.') }}
                                                </td>
                                                <td style="text-align: right; color: #0284c7; font-family: monospace;">
                                                    Rp {{ number_format($j->total_kredit, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: #94a3b8; font-size: 0.85rem;">
                                Belum ada catatan jurnal umum yang sesuai dengan filter pencarian Anda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if ($jurnals->hasPages())
            <div style="padding: 1rem 1.25rem; border-top: 1px solid #e2e8f0; background: #ffffff;">
                {{ $jurnals->links() }}
            </div>
        @endif
    </div>
</div>

{{-- MODAL DETAIL JURNAL POPUP --}}
@include('keuangan.jurnal.partials.modal-detail-jurnal')

@endsection

@push('scripts')
<script>
    window.appConfig = {
        baseUrl: '{{ url('') }}'
    };
</script>
<script src="{{ asset('js/keuangan/jurnal/jurnal-index.js') }}?v={{ filemtime(public_path('js/keuangan/jurnal/jurnal-index.js')) }}"></script>
@endpush
