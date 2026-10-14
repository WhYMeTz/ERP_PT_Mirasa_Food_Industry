@extends('layouts.app')

@section('title', 'Riwayat Kas Keluar & Pembayaran Hutang (BKK)')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/bayar/bayar-index.css') }}">
@endpush

@section('content')
<div class="content-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <h1 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
                💳 Riwayat Kas/Bank Keluar (BKK)
            </h1>
            <span style="background: #eff6ff; color: #1e40af; font-size: 0.725rem; font-weight: 800; padding: 2px 8px; border-radius: 6px; border: 1px solid #bfdbfe;">
                Bukti Kas Keluar
            </span>
        </div>
        <p style="font-size: 0.825rem; color: #64748b; margin: 0.25rem 0 0 0;">
            Kelola transaksi pelunasan hutang supplier, pengeluaran kas/bank, cetak slip BKK, dan pembatalan voucher.
        </p>
    </div>

    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <a href="{{ route('keuangan.hutang.index') }}" class="btn btn-secondary" style="font-weight: 700; font-size: 0.8125rem; padding: 0.5rem 0.95rem; border-radius: 8px;">
            &larr; Riwayat Hutang (AP)
        </a>

        @if (Auth::user()->canCreateBayarHutang())
            <a href="{{ route('keuangan.bayar.create') }}" class="btn btn-primary" style="font-weight: 700; font-size: 0.8125rem; padding: 0.5rem 1.15rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.25);">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Bayar Tagihan Baru</span>
            </a>
        @endif
    </div>
</div>

{{-- METRIC CARDS --}}
@php
    $totalBayarBulanIni = $bayarList->where('status_cd', 'POSTED')->sum('total_bayar');
    $totalSaldoKasBank = $kasBanks->sum('saldo_berjalan');
@endphp
<div class="bayar-metrics-grid">
    <div class="bayar-metric-card">
        <div class="bayar-metric-icon" style="background: #eff6ff; color: #2563eb;">
            💳
        </div>
        <div class="bayar-metric-info">
            <span class="bayar-metric-label">Total Transaksi BKK</span>
            <span class="bayar-metric-val" style="color: #1e3a8a;">
                {{ $bayarList->total() }} Voucher
            </span>
            <span class="bayar-metric-sub">
                Tercatat di sistem
            </span>
        </div>
    </div>

    <div class="bayar-metric-card">
        <div class="bayar-metric-icon" style="background: #f0fdf4; color: #16a34a;">
            💵
        </div>
        <div class="bayar-metric-info">
            <span class="bayar-metric-label">Total Pengeluaran (Tampil)</span>
            <span class="bayar-metric-val" style="color: #15803d;">
                Rp {{ number_format($totalBayarBulanIni, 0, ',', '.') }}
            </span>
            <span class="bayar-metric-sub">
                Dana teralokasi ke hutang
            </span>
        </div>
    </div>

    <div class="bayar-metric-card">
        <div class="bayar-metric-icon" style="background: #f8fafc; color: #0284c7; border: 1px solid #e2e8f0;">
            🏦
        </div>
        <div class="bayar-metric-info">
            <span class="bayar-metric-label">Total Saldo Kas &amp; Bank Aktif</span>
            <span class="bayar-metric-val" style="color: #0369a1;">
                Rp {{ number_format($totalSaldoKasBank, 0, ',', '.') }}
            </span>
            <span class="bayar-metric-sub">
                {{ $kasBanks->count() }} Rekening pabrik &amp; bank
            </span>
        </div>
    </div>
</div>

{{-- FILTER TOOLBAR --}}
<div class="card" style="padding: 1rem 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 1.25rem;">
    <form action="{{ route('keuangan.bayar.index') }}" method="GET" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 200px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 0.25rem;">
                Pencarian No. BKK / Supplier
            </label>
            <input type="text" 
                   name="search" 
                   value="{{ $filters['search'] ?? '' }}" 
                   class="form-control" 
                   placeholder="Cari BKK-2026..., nama supplier, no. ref..." 
                   style="height: 36px; font-size: 0.8125rem;">
        </div>

        <div style="width: 200px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 0.25rem;">
                Rekening Kas / Bank
            </label>
            <select name="kas_bank_id" class="form-control" style="height: 36px; font-size: 0.8125rem;">
                <option value="">Semua Rekening</option>
                @foreach ($kasBanks as $kb)
                    <option value="{{ $kb->kas_bank_id }}" {{ ($filters['kas_bank_id'] ?? '') == $kb->kas_bank_id ? 'selected' : '' }}>
                        {{ $kb->nama_akun }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="width: 150px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 0.25rem;">
                Metode Bayar
            </label>
            <select name="metode_bayar" class="form-control" style="height: 36px; font-size: 0.8125rem;">
                <option value="">Semua Metode</option>
                <option value="TRANSFER" {{ ($filters['metode_bayar'] ?? '') == 'TRANSFER' ? 'selected' : '' }}>Transfer Bank</option>
                <option value="CASH" {{ ($filters['metode_bayar'] ?? '') == 'CASH' ? 'selected' : '' }}>Tunai / Kas</option>
                <option value="GIRO" {{ ($filters['metode_bayar'] ?? '') == 'GIRO' ? 'selected' : '' }}>Giro</option>
                <option value="CEK" {{ ($filters['metode_bayar'] ?? '') == 'CEK' ? 'selected' : '' }}>Cek Bank</option>
            </select>
        </div>

        <div style="width: 140px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 0.25rem;">
                Tgl Mulai
            </label>
            <input type="date" 
                   name="tgl_mulai" 
                   value="{{ $filters['tgl_mulai'] ?? '' }}" 
                   class="form-control" 
                   style="height: 36px; font-size: 0.8125rem;">
        </div>

        <div style="width: 140px;">
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 0.25rem;">
                Tgl Selesai
            </label>
            <input type="date" 
                   name="tgl_selesai" 
                   value="{{ $filters['tgl_selesai'] ?? '' }}" 
                   class="form-control" 
                   style="height: 36px; font-size: 0.8125rem;">
        </div>

        <div>
            <button type="submit" class="btn btn-secondary" style="height: 36px; font-weight: 700; font-size: 0.8125rem; padding: 0 1rem;">
                Filter
            </button>
            @if (!empty(array_filter($filters)))
                <a href="{{ route('keuangan.bayar.index') }}" class="btn btn-link" style="font-size: 0.8rem; color: #dc2626; text-decoration: none;">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

{{-- TABEL DATA BKK --}}
<div class="card" style="padding: 0; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
    <div style="overflow-x: auto;">
        <table class="table-compact" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.8125rem;">
            <thead style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0;">
                <tr style="color: #475569; font-size: 0.725rem; text-transform: uppercase;">
                    <th style="padding: 0.75rem 1rem;">No. Voucher (BKK)</th>
                    <th style="padding: 0.75rem 0.75rem;">Tgl Bayar</th>
                    <th style="padding: 0.75rem 0.75rem;">Supplier Penerima</th>
                    <th style="padding: 0.75rem 0.75rem;">Rekening Sumber</th>
                    <th style="padding: 0.75rem 0.75rem;">Metode &amp; Ref</th>
                    <th style="padding: 0.75rem 1rem; text-align: right;">Total Bayar (Rp)</th>
                    <th style="padding: 0.75rem 0.75rem; text-align: center;">Status</th>
                    <th style="padding: 0.75rem 1rem; text-align: center; width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bayarList as $bkk)
                    @php
                        $menuId = 'action_bkk_' . $bkk->bayar_id;
                        $metodeClass = strtolower($bkk->metode_bayar);
                        $statusClass = strtolower($bkk->status_cd);
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 0.75rem 1rem;">
                            <a href="{{ route('keuangan.bayar.show', $bkk->bayar_id) }}" style="font-weight: 800; font-family: monospace; color: #0284c7; text-decoration: none; font-size: 0.85rem;">
                                {{ $bkk->bayar_no }}
                            </a>
                            <div style="font-size: 0.7rem; color: #64748b; margin-top: 2px;">
                                {{ $bkk->details->count() }} Faktur dialokasikan
                            </div>
                        </td>

                        <td style="padding: 0.75rem 0.75rem; color: #334155; font-weight: 600;">
                            {{ date('d/m/Y', strtotime($bkk->tgl_bayar)) }}
                        </td>

                        <td style="padding: 0.75rem 0.75rem;">
                            <div style="font-weight: 700; color: #0f172a;">
                                {{ $bkk->supplier->supplier_nm ?? '-' }}
                            </div>
                            @if ($bkk->penerima_nama)
                                <div style="font-size: 0.7rem; color: #64748b;">
                                    U/p: {{ $bkk->penerima_nama }}
                                </div>
                            @endif
                        </td>

                        <td style="padding: 0.75rem 0.75rem;">
                            <div style="font-weight: 700; color: #0284c7;">
                                {{ $bkk->kasBank->nama_akun ?? '-' }}
                            </div>
                            <div style="font-size: 0.7rem; color: #64748b;">
                                {{ $bkk->kasBank->kode_akun ?? '' }}
                            </div>
                        </td>

                        <td style="padding: 0.75rem 0.75rem;">
                            <span class="badge-metode {{ $metodeClass }}">
                                {{ $bkk->metode_bayar }}
                            </span>
                            @if ($bkk->no_referensi_bank)
                                <div style="font-size: 0.7rem; color: #64748b; margin-top: 2px; font-family: monospace;">
                                    Ref: {{ $bkk->no_referensi_bank }}
                                </div>
                            @endif
                        </td>

                        <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 800; color: #16a34a; font-size: 0.9rem;">
                            Rp {{ number_format($bkk->total_bayar, 0, ',', '.') }}
                        </td>

                        <td style="padding: 0.75rem 0.75rem; text-align: center;">
                            <span class="badge-status-bkk {{ $statusClass }}">
                                {{ $bkk->status_cd }}
                            </span>
                        </td>

                        {{-- SMART ACTION DROPDOWN STANDAR ERP MIRASA --}}
                        <td style="padding: 0.75rem 1rem; text-align: center;">
                            <button type="button" 
                                    class="btn-action-trigger" 
                                    onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                <span>Aksi</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div id="{{ $menuId }}" class="action-dropdown-menu">
                                <a href="{{ route('keuangan.bayar.show', $bkk->bayar_id) }}" class="action-dropdown-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Lihat Slip BKK</span>
                                </a>

                                @if ($bkk->status_cd === 'POSTED')
                                    <button type="button" 
                                            class="action-dropdown-item danger-item"
                                            onclick="openModalVoid({{ $bkk->bayar_id }}, '{{ $bkk->bayar_no }}', 'Rp {{ number_format($bkk->total_bayar, 0, ',', '.') }}')">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Batalkan (VOID)</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="padding: 3rem 1rem; text-align: center; color: #94a3b8; font-size: 0.85rem;">
                            Belum ada transaksi pembayaran hutang (BKK) yang tercatat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($bayarList->hasPages())
        <div style="padding: 1rem 1.25rem; border-top: 1px solid #e2e8f0; background: #f8fafc;">
            {{ $bayarList->links() }}
        </div>
    @endif
</div>

{{-- MODAL VOID BKK --}}
@include('keuangan.bayar.partials.modal-void-bayar')

@endsection

@push('scripts')
<script>
    window.appConfig = {
        baseUrl: '{{ url('') }}'
    };
</script>
<script src="{{ asset('js/keuangan/bayar/bayar-index.js') }}"></script>
@endpush
