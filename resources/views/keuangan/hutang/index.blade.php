@extends('layouts.app')

@section('title', 'Hutang Usaha & Tagihan Pembelian (AP)')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/hutang/hutang-index.css') }}">
@endpush

@section('content')
<div class="content-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <h1 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
                💰 Hutang Usaha &amp; Tagihan Supplier (AP)
            </h1>
            <span style="background: #f0fdf4; color: #166534; font-size: 0.725rem; font-weight: 800; padding: 2px 8px; border-radius: 6px; border: 1px solid #bbf7d0;">
                Accounts Payable
            </span>
        </div>
        <p style="font-size: 0.825rem; color: #64748b; margin: 0.25rem 0 0 0;">
            Kelola tagihan pembelian bahan dari GRN, tagihan konsinyasi gas bulanan, jatuh tempo, dan riwayat pembayaran.
        </p>
    </div>

    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        @if (Auth::user()->canCreateBayarHutang())
            <a href="{{ route('keuangan.bayar.create') }}" class="btn btn-secondary" style="font-weight: 700; font-size: 0.8125rem; padding: 0.5rem 0.95rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>💳 Bayar Tagihan (BKK)</span>
            </a>
        @endif

        @if (Auth::user()->canCreateHutang())
            <a href="{{ route('keuangan.hutang.create_gas') }}" class="btn" style="background: #fff7ed; color: #c2410c; border: 1.5px solid #fed7aa; font-weight: 700; font-size: 0.8125rem; padding: 0.5rem 0.95rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;">
                <span>🔥 + Tagihan Gas Bulanan</span>
            </a>

            <a href="{{ route('keuangan.hutang.create') }}" class="btn btn-primary" style="font-weight: 700; font-size: 0.8125rem; padding: 0.5rem 1.15rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.25);">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Catat Tagihan dari GRN</span>
            </a>
        @endif
    </div>
</div>

{{-- 4 KARTU METRIK OPERASIONAL AP --}}
<div class="keuangan-metrics-grid">
    <div class="keuangan-metric-card">
        <div class="keuangan-metric-icon" style="background: #eff6ff; color: #2563eb;">
            📋
        </div>
        <div class="keuangan-metric-info">
            <span class="keuangan-metric-label">Total Hutang Aktif</span>
            <span class="keuangan-metric-val" style="color: #1e3a8a;">
                Rp {{ number_format($metrics['total_hutang_aktif'] ?? 0, 0, ',', '.') }}
            </span>
            <span class="keuangan-metric-sub">
                {{ $metrics['count_belum_lunas'] ?? 0 }} tagihan belum lunas
            </span>
        </div>
    </div>

    <div class="keuangan-metric-card" style="{{ ($metrics['count_jatuh_tempo'] ?? 0) > 0 ? 'border-color: #fca5a5; background: #fff5f5;' : '' }}">
        <div class="keuangan-metric-icon" style="background: #fee2e2; color: #dc2626;">
            ⚠️
        </div>
        <div class="keuangan-metric-info">
            <span class="keuangan-metric-label" style="{{ ($metrics['count_jatuh_tempo'] ?? 0) > 0 ? 'color: #b91c1c;' : '' }}">
                Sudah Jatuh Tempo
            </span>
            <span class="keuangan-metric-val" style="color: #dc2626;">
                Rp {{ number_format($metrics['total_jatuh_tempo'] ?? 0, 0, ',', '.') }}
            </span>
            <span class="keuangan-metric-sub" style="{{ ($metrics['count_jatuh_tempo'] ?? 0) > 0 ? 'color: #ef4444; font-weight: 700;' : '' }}">
                {{ $metrics['count_jatuh_tempo'] ?? 0 }} tagihan lewat batas tempo!
            </span>
        </div>
    </div>

    <div class="keuangan-metric-card">
        <div class="keuangan-metric-icon" style="background: #f0fdf4; color: #16a34a;">
            💸
        </div>
        <div class="keuangan-metric-info">
            <span class="keuangan-metric-label">Terbayar Bulan Ini</span>
            <span class="keuangan-metric-val" style="color: #15803d;">
                Rp {{ number_format($metrics['total_bayar_bulan_ini'] ?? 0, 0, ',', '.') }}
            </span>
            <span class="keuangan-metric-sub">
                Realisasi kas/bank keluar periode {{ date('F Y') }}
            </span>
        </div>
    </div>

    <div class="keuangan-metric-card">
        <div class="keuangan-metric-icon" style="background: #f8fafc; color: #475569;">
            🏦
        </div>
        <div class="keuangan-metric-info">
            <span class="keuangan-metric-label">Akun Kas &amp; Bank</span>
            <span class="keuangan-metric-val" style="font-size: 1.05rem;">
                {{ count($kasBanks) }} Rekening
            </span>
            <span class="keuangan-metric-sub">
                <a href="{{ route('keuangan.kas_bank.index') }}" style="color: #0284c7; text-decoration: underline; font-weight: 600;">Lihat Saldo Rekening &rarr;</a>
            </span>
        </div>
    </div>
</div>

{{-- FILTER CARD --}}
<div class="card" style="margin-bottom: 1.25rem; padding: 1rem 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px;">
    <form method="GET" action="{{ route('keuangan.hutang.index') }}" id="filterForm">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; align-items: flex-end;">
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 0.25rem;">
                    Status Pembayaran
                </label>
                <select name="status_pembayaran" class="form-control" style="font-size: 0.8125rem; height: 36px;" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="BELUM_LUNAS" {{ ($filters['status_pembayaran'] ?? '') === 'BELUM_LUNAS' ? 'selected' : '' }}>🔴 Belum Lunas (Unpaid)</option>
                    <option value="SEBAGIAN" {{ ($filters['status_pembayaran'] ?? '') === 'SEBAGIAN' ? 'selected' : '' }}>🟡 Cicilan / Parsial</option>
                    <option value="LUNAS" {{ ($filters['status_pembayaran'] ?? '') === 'LUNAS' ? 'selected' : '' }}>🟢 Lunas</option>
                    <option value="JATUH_TEMPO" {{ ($filters['status_pembayaran'] ?? '') === 'JATUH_TEMPO' ? 'selected' : '' }}>⚠️ Lewat Jatuh Tempo (Overdue)</option>
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 0.25rem;">
                    Pemasok / Rekanan
                </label>
                <select name="supplier_id" class="form-control" style="font-size: 0.8125rem; height: 36px;" onchange="this.form.submit()">
                    <option value="">Semua Supplier</option>
                    @foreach ($suppliers as $supp)
                        <option value="{{ $supp->supplier_id }}" {{ ($filters['supplier_id'] ?? '') == $supp->supplier_id ? 'selected' : '' }}>
                            {{ $supp->supplier_nm }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 0.25rem;">
                    Tipe Tagihan
                </label>
                <select name="tipe_hutang" class="form-control" style="font-size: 0.8125rem; height: 36px;" onchange="this.form.submit()">
                    <option value="">Semua Tipe</option>
                    <option value="PEMBELIAN_GRN" {{ ($filters['tipe_hutang'] ?? '') === 'PEMBELIAN_GRN' ? 'selected' : '' }}>🌾 Pembelian Bahan (GRN)</option>
                    <option value="GAS_UTILITAS" {{ ($filters['tipe_hutang'] ?? '') === 'GAS_UTILITAS' ? 'selected' : '' }}>🔥 Gas Utilitas Bulanan</option>
                    <option value="OPERASIONAL_UMUM" {{ ($filters['tipe_hutang'] ?? '') === 'OPERASIONAL_UMUM' ? 'selected' : '' }}>📦 Operasional Umum</option>
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 0.25rem;">
                    Cari No. Tagihan / Faktur
                </label>
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="AP-xxxx / No Invoice..." 
                       value="{{ $filters['search'] ?? '' }}" 
                       style="font-size: 0.8125rem; height: 36px;">
            </div>

            <div style="display: flex; gap: 0.4rem;">
                <button type="submit" class="btn btn-primary" style="font-size: 0.8125rem; font-weight: 700; height: 36px; padding: 0 1rem;">
                    Filter
                </button>
                @if (!empty($filters['status_pembayaran']) || !empty($filters['supplier_id']) || !empty($filters['tipe_hutang']) || !empty($filters['search']))
                    <a href="{{ route('keuangan.hutang.index') }}" class="btn btn-secondary" style="font-size: 0.8125rem; font-weight: 700; height: 36px; padding: 0 0.85rem;" title="Reset Filter">
                        ✕
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

{{-- TABEL DAFTAR TAGIHAN HUTANG --}}
<div class="card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
    <div style="overflow-x: auto;">
        <table class="table-compact" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-size: 0.75rem; color: #475569; text-transform: uppercase; letter-spacing: 0.03em;">
                    <th style="padding: 0.85rem 1rem;">No. Tagihan &amp; Tgl</th>
                    <th style="padding: 0.85rem 0.75rem;">No. Faktur Supplier</th>
                    <th style="padding: 0.85rem 0.75rem;">Pemasok / Rekanan</th>
                    <th style="padding: 0.85rem 0.75rem;">Tipe</th>
                    <th style="padding: 0.85rem 0.75rem;">Jatuh Tempo</th>
                    <th style="padding: 0.85rem 0.75rem; text-align: right;">Total Tagihan</th>
                    <th style="padding: 0.85rem 0.75rem; text-align: right;">Terbayar</th>
                    <th style="padding: 0.85rem 0.75rem; text-align: right;">Sisa Hutang</th>
                    <th style="padding: 0.85rem 0.75rem; text-align: center;">Status</th>
                    <th style="padding: 0.85rem 1rem; text-align: center; width: 90px;">Aksi</th>
                </tr>
            </thead>
            <tbody style="font-size: 0.8125rem; color: #1e293b; divide-y: 1px solid #f1f5f9;">
                @forelse ($hutangList as $h)
                    @php
                        $menuId = 'menuHutang_' . $h->hutang_id;
                        $isOverdue = $h->isOverdue();
                        $diffDays = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($h->tgl_jatuh_tempo)->startOfDay(), false);
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.12s; {{ $isOverdue ? 'background: #fff8f8;' : '' }}" onmouseover="this.style.background='{{ $isOverdue ? '#fee2e2' : '#f8fafc' }}'" onmouseout="this.style.background='{{ $isOverdue ? '#fff8f8' : '#ffffff' }}'">
                        {{-- 1. NO TAGIHAN & TGL --}}
                        <td style="padding: 0.75rem 1rem;">
                            <a href="{{ route('keuangan.hutang.show', $h->hutang_id) }}" style="font-weight: 800; color: #0284c7; text-decoration: none; font-family: monospace;">
                                {{ $h->hutang_no }}
                            </a>
                            <div style="font-size: 0.72rem; color: #64748b; margin-top: 2px;">
                                {{ \Carbon\Carbon::parse($h->tgl_tagihan)->format('d/m/Y') }}
                            </div>
                        </td>

                        {{-- 2. NO FAKTUR SUPPLIER --}}
                        <td style="padding: 0.75rem 0.75rem;">
                            @if ($h->no_faktur_supplier)
                                <span style="font-weight: 700; font-family: monospace; color: #334155;">
                                    {{ $h->no_faktur_supplier }}
                                </span>
                            @else
                                <span style="color: #94a3b8; font-style: italic;">— (Non-Faktur)</span>
                            @endif
                        </td>

                        {{-- 3. PEMASOK / REKANAN --}}
                        <td style="padding: 0.75rem 0.75rem;">
                            <div style="font-weight: 700; color: #0f172a;">
                                {{ $h->supplier?->supplier_nm ?: 'Operasional Pabrik' }}
                            </div>
                            <div style="font-size: 0.7rem; color: #64748b;">
                                {{ $h->supplier?->jenisSupplier?->jenis_supplier_nm ?: ($h->gudang?->gudang_nm ?: 'Mirasa Magelang') }}
                            </div>
                        </td>

                        {{-- 4. TIPE TAGIHAN --}}
                        <td style="padding: 0.75rem 0.75rem;">
                            @if ($h->tipe_hutang === 'GAS_UTILITAS')
                                <span class="badge-tipe-hutang badge-tipe-gas">
                                    🔥 Gas ({{ $h->periode_bulan }})
                                </span>
                            @elseif ($h->tipe_hutang === 'PEMBELIAN_GRN')
                                <span class="badge-tipe-hutang badge-tipe-grn">
                                    🌾 Beli Bahan
                                </span>
                            @else
                                <span class="badge-tipe-hutang badge-tipe-ops">
                                    📦 Beban Ops
                                </span>
                            @endif
                        </td>

                        {{-- 5. JATUH TEMPO & HITUNG MUNDUR --}}
                        <td style="padding: 0.75rem 0.75rem;">
                            <div class="tempo-indicator">
                                <span class="tempo-due-date {{ $isOverdue ? 'tempo-danger' : '' }}">
                                    {{ \Carbon\Carbon::parse($h->tgl_jatuh_tempo)->format('d/m/Y') }}
                                </span>
                                @if ($h->status_pembayaran === 'LUNAS')
                                    <span class="tempo-days-left tempo-safe">✓ Selesai</span>
                                @elseif ($isOverdue)
                                    <span class="tempo-days-left tempo-danger">
                                        Lewat {{ abs($diffDays) }} hari!
                                    </span>
                                @elseif ($diffDays === 0)
                                    <span class="tempo-days-left tempo-warning" style="font-weight: 800;">
                                        Hari Ini!
                                    </span>
                                @else
                                    <span class="tempo-days-left {{ $diffDays <= 3 ? 'tempo-warning' : 'tempo-safe' }}">
                                        {{ $diffDays }} hari lagi
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- 6. TOTAL TAGIHAN --}}
                        <td style="padding: 0.75rem 0.75rem; text-align: right; font-weight: 700; color: #334155;">
                            Rp {{ number_format($h->total_tagihan, 0, ',', '.') }}
                        </td>

                        {{-- 7. TERBAYAR --}}
                        <td style="padding: 0.75rem 0.75rem; text-align: right; font-weight: 600; color: #16a34a;">
                            Rp {{ number_format($h->total_dibayar, 0, ',', '.') }}
                        </td>

                        {{-- 8. SISA HUTANG --}}
                        <td style="padding: 0.75rem 0.75rem; text-align: right; font-weight: 800; color: {{ $h->sisa_hutang > 0 ? '#b91c1c' : '#64748b' }};">
                            Rp {{ number_format($h->sisa_hutang, 0, ',', '.') }}
                        </td>

                        {{-- 9. STATUS BADGE --}}
                        <td style="padding: 0.75rem 0.75rem; text-align: center;">
                            @if ($h->status_pembayaran === 'LUNAS')
                                <span class="badge-status-hutang badge-paid">
                                    <span>✓</span> <span>LUNAS</span>
                                </span>
                            @elseif ($isOverdue)
                                <span class="badge-status-hutang badge-overdue">
                                    <span>⚠️</span> <span>OVERDUE</span>
                                </span>
                            @elseif ($h->status_pembayaran === 'SEBAGIAN')
                                <span class="badge-status-hutang badge-partial">
                                    <span>⏳</span> <span>PARSIAL</span>
                                </span>
                            @else
                                <span class="badge-status-hutang badge-unpaid">
                                    <span>🔴</span> <span>BELUM BAYAR</span>
                                </span>
                            @endif
                        </td>

                        {{-- 10. SMART ACTION DROPDOWN --}}
                        <td style="padding: 0.75rem 1rem; text-align: center;">
                            <button type="button" 
                                    class="btn-action-trigger" 
                                    onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                <span>Aksi</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div id="{{ $menuId }}" class="action-dropdown-menu">
                                <a href="{{ route('keuangan.hutang.show', $h->hutang_id) }}" class="action-dropdown-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Lihat Detail Tagihan</span>
                                </a>

                                @if ($h->sisa_hutang > 0 && Auth::user()->canCreateBayarHutang())
                                    <div class="action-dropdown-divider"></div>
                                    <a href="{{ route('keuangan.bayar.create', ['hutang_id' => $h->hutang_id, 'supplier_id' => $h->supplier_id]) }}" class="action-dropdown-item" style="color: #059669; font-weight: 700;">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        <span>💳 Bayar Tagihan Ini</span>
                                    </a>
                                @endif

                                @if ($h->total_dibayar <= 0 && Auth::user()->canDeleteHutang())
                                    <div class="action-dropdown-divider"></div>
                                    <button type="button" 
                                            class="action-dropdown-item danger-item" 
                                            onclick="openDeleteHutangModal('{{ $h->hutang_id }}', '{{ $h->hutang_no }}', 'Rp {{ number_format($h->total_tagihan, 0, ',', '.') }}')">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Batalkan Tagihan</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="padding: 3.5rem 1.5rem; text-align: center; color: #64748b;">
                            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">💳</div>
                            <div style="font-weight: 700; font-size: 1rem; color: #0f172a;">Belum Ada Tagihan Hutang</div>
                            <div style="font-size: 0.85rem; margin-top: 0.25rem;">
                                Tidak ada data tagihan supplier yang sesuai kriteria filter di atas.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($hutangList->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 0.8rem; color: #64748b;">
                Menampilkan {{ $hutangList->firstItem() }} - {{ $hutangList->lastItem() }} dari {{ $hutangList->total() }} tagihan
            </div>
            <div>
                {{ $hutangList->links() }}
            </div>
        </div>
    @endif
</div>

{{-- MODAL PEMBATALAN TAGIHAN --}}
@include('keuangan.hutang.partials.modal-delete-hutang')

@endsection

@push('scripts')
<script>
    window.appConfig = {
        baseUrl: '{{ url('') }}'
    };
</script>
<script src="{{ asset('js/keuangan/hutang/hutang-index.js') }}"></script>
@endpush
