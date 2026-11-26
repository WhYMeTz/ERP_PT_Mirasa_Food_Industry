@extends('layouts.app')

@section('title', 'Terima Pembayaran Customer (BKM AR) - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/bayar_piutang/bayar-piutang-index.css') }}?v={{ file_exists(public_path('css/keuangan/bayar_piutang/bayar-piutang-index.css')) ? filemtime(public_path('css/keuangan/bayar_piutang/bayar-piutang-index.css')) : time() }}">
@endpush

@section('content')
<div class="ar-page-wrapper">

    {{-- 1. TOP HEADER SECTION --}}
    <div class="ar-top-header">
        <div>
            <div class="ar-header-kicker">KAS &amp; PERBANKAN PENJUALAN</div>
            <h1 class="ar-header-title">Terima Pembayaran Customer</h1>
            <p class="ar-header-subtitle">
                Pelunasan faktur penjualan toko &amp; distributor dan monitoring arus kas piutang usaha (BKM AR).
            </p>
        </div>

        <div class="ar-header-actions">
            @if (Auth::user()->canCreateBayarPiutang())
                <a href="{{ route('keuangan.bayar_piutang.create') }}" class="btn-pill-dark">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Terima Pembayaran Baru</span>
                </a>
            @endif
        </div>
    </div>

    {{-- 2. METRICS CARDS (GAYA ENTERPRISE MINIMALIS 4 KOLOM) --}}
    <div class="ar-metrics-grid">
        {{-- Card 1: Total Transaksi BKM --}}
        <div class="ar-metric-card">
            <div class="ar-metric-top">
                <div class="ar-metric-header-left">
                    <span class="ar-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </span>
                    <span class="ar-metric-label">Total Transaksi BKM</span>
                </div>
                <span class="ar-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="ar-metric-value">{{ number_format($countBkm) }}</div>
            <div class="ar-metric-footer">
                {{ number_format($countLunas) }} pelunasan faktur penuh
            </div>
        </div>

        {{-- Card 2: Menunggu Alokasi / Sebagian --}}
        <div class="ar-metric-card">
            <div class="ar-metric-top">
                <div class="ar-metric-header-left">
                    <span class="ar-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <span class="ar-metric-label">Cicilan / Sebagian</span>
                </div>
                <span class="ar-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="ar-metric-value">{{ number_format($countSebagian) }}</div>
            <div class="ar-metric-footer">
                Faktur dengan saldo bertahap
            </div>
        </div>

        {{-- Card 3: Total Kas Diterima --}}
        <div class="ar-metric-card">
            <div class="ar-metric-top">
                <div class="ar-metric-header-left">
                    <span class="ar-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </span>
                    <span class="ar-metric-label">Total Kas Diterima</span>
                </div>
                <span class="ar-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="ar-metric-value">Rp {{ number_format($totalDiterima, 0, ',', '.') }}</div>
            <div class="ar-metric-footer">
                Dari {{ $kasBanks->count() }} rekening kas/bank
            </div>
        </div>

        {{-- Card 4: Customer Aktif --}}
        <div class="ar-metric-card">
            <div class="ar-metric-top">
                <div class="ar-metric-header-left">
                    <span class="ar-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </span>
                    <span class="ar-metric-label">Customer Aktif</span>
                </div>
                <span class="ar-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="ar-metric-value">{{ number_format($countCustomerAktif) }}</div>
            <div class="ar-metric-footer">
                Sisa tagihan: Rp {{ number_format($totalSisaPiutang, 0, ',', '.') }}
            </div>
        </div>
    </div>

    {{-- 3. MAIN DATA CARD (TABLE & CONTROLS) --}}
    <div class="ar-main-card">
        {{-- Controls Bar --}}
        <div class="ar-control-bar">
            {{-- Tabs Status Row --}}
            <div class="ar-tabs-row">
                <div class="ar-status-pills">
                    <a href="javascript:void(0)" onclick="switchStatusFilter('')"
                       class="ar-status-tab {{ empty($filters['status_cd']) ? 'active' : '' }}">
                        <span>Semua</span>
                        <span class="badge-count">{{ $countBkm }}</span>
                    </a>
                    <a href="javascript:void(0)" onclick="switchStatusFilter('LUNAS')"
                       class="ar-status-tab {{ ($filters['status_cd'] ?? '') === 'LUNAS' ? 'active' : '' }}">
                        <span>Lunas Penuh</span>
                        <span class="badge-count">{{ $countLunas }}</span>
                    </a>
                    <a href="javascript:void(0)" onclick="switchStatusFilter('SEBAGIAN')"
                       class="ar-status-tab {{ ($filters['status_cd'] ?? '') === 'SEBAGIAN' ? 'active' : '' }}">
                        <span>Cicilan / Sebagian</span>
                        <span class="badge-count">{{ $countSebagian }}</span>
                    </a>
                    <a href="javascript:void(0)" onclick="switchStatusFilter('VOID')"
                       class="ar-status-tab {{ ($filters['status_cd'] ?? '') === 'VOID' ? 'active' : '' }}">
                        <span>Dibatalkan</span>
                        <span class="badge-count">{{ $countVoid }}</span>
                    </a>
                </div>
            </div>

            {{-- Filter Form Row --}}
            <form id="formBayarPiutangFilter" action="{{ route('keuangan.bayar_piutang.index') }}" method="GET" class="ar-filter-form">
                <input type="hidden" id="inputFilterStatusCd" name="status_cd" value="{{ $filters['status_cd'] ?? '' }}">

                {{-- Search Box --}}
                <div class="ar-search-wrapper">
                    <span class="ar-search-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text"
                           name="search"
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Cari no BKM, referensi bank, customer..."
                           class="ar-search-input">
                </div>

                {{-- Customer Select --}}
                <select name="customer_id" class="ar-select-input" onchange="this.form.submit()">
                    <option value="">Semua Customer</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->customer_id }}" {{ ($filters['customer_id'] ?? '') == $c->customer_id ? 'selected' : '' }}>
                            {{ $c->customer_nm }}
                        </option>
                    @endforeach
                </select>

                {{-- Kas / Bank Select --}}
                <select name="kas_bank_id" class="ar-select-input" onchange="this.form.submit()">
                    <option value="">Semua Rekening Kas/Bank</option>
                    @foreach ($kasBanks as $kb)
                        <option value="{{ $kb->kas_bank_id }}" {{ ($filters['kas_bank_id'] ?? '') == $kb->kas_bank_id ? 'selected' : '' }}>
                            {{ $kb->nama_akun }}
                        </option>
                    @endforeach
                </select>

                {{-- Metode Bayar Select --}}
                <select name="metode_bayar" class="ar-select-input" onchange="this.form.submit()">
                    <option value="">Semua Metode</option>
                    <option value="TRANSFER" {{ ($filters['metode_bayar'] ?? '') === 'TRANSFER' ? 'selected' : '' }}>Transfer Bank</option>
                    <option value="CASH" {{ ($filters['metode_bayar'] ?? '') === 'CASH' ? 'selected' : '' }}>Tunai / Cash</option>
                    <option value="GIRO" {{ ($filters['metode_bayar'] ?? '') === 'GIRO' ? 'selected' : '' }}>Bilyet Giro</option>
                    <option value="LAINNYA" {{ ($filters['metode_bayar'] ?? '') === 'LAINNYA' ? 'selected' : '' }}>Lainnya</option>
                </select>

                {{-- Tanggal Mulai & Selesai --}}
                <input type="date" name="tgl_mulai" value="{{ $filters['tgl_mulai'] ?? '' }}" class="ar-date-input" title="Tanggal Mulai">
                <input type="date" name="tgl_selesai" value="{{ $filters['tgl_selesai'] ?? '' }}" class="ar-date-input" title="Tanggal Selesai">

                <button type="submit" class="btn-filter-apply">Terapkan</button>

                @if (!empty(array_filter($filters)))
                    <a href="{{ route('keuangan.bayar_piutang.index') }}" class="btn-filter-reset">Reset</a>
                @endif
            </form>
        </div>

        {{-- Table Responsive Area --}}
        <div class="ar-table-responsive">
            <table class="ar-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="min-width: 150px;">Bukti BKM &amp; Tanggal</th>
                        <th style="min-width: 240px;">Customer</th>
                        <th style="min-width: 190px;">Kas / Bank &amp; Metode</th>
                        <th style="min-width: 180px;">Alokasi Faktur</th>
                        <th style="min-width: 140px; text-align: right;">Total Kas Diterima</th>
                        <th style="min-width: 120px; text-align: center;">Status</th>
                        <th style="width: 85px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bayarList as $idx => $bayar)
                        @php
                            $menuId = 'actionMenuPiutang_' . $bayar->bayar_id;
                            $metodeClass = match (strtoupper((string) $bayar->metode_bayar)) {
                                'TRANSFER' => 'badge-metode-transfer',
                                'CASH'     => 'badge-metode-cash',
                                'GIRO'     => 'badge-metode-giro',
                                default    => 'badge-metode-lainnya',
                            };

                            // Customer Initials
                            $initials = 'CU';
                            if ($bayar->customer?->customer_nm) {
                                $words = preg_split('/\s+/', trim($bayar->customer->customer_nm));
                                $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : substr($words[0], 1, 1)));
                            }

                            // Status Checking
                            $isVoid = ($bayar->status_cd === 'VOID');
                            $hasRemaining = false;
                            if (!$isVoid) {
                                $hasRemaining = $bayar->details->contains(fn($d) => (float)$d->sisa_piutang_sesudah > 0.0001);
                            }
                        @endphp
                        <tr>
                            {{-- 1. No --}}
                            <td style="text-align: center; color: #64748b; font-size: 0.775rem;">
                                {{ $bayarList->firstItem() + $idx }}
                            </td>

                            {{-- 2. Bukti BKM & Tanggal --}}
                            <td>
                                <div class="bkm-cell-wrap">
                                    <a href="{{ route('keuangan.bayar_piutang.show', $bayar->bayar_id) }}" class="bkm-no-link">
                                        {{ $bayar->bkm_no }}
                                    </a>
                                    <span class="bkm-tgl-sub">
                                        {{ $bayar->tgl_bayar ? $bayar->tgl_bayar->format('d/m/Y') : '-' }}
                                    </span>
                                    @if ($bayar->no_referensi_bank)
                                        <span class="bkm-ref-sub">Ref: {{ $bayar->no_referensi_bank }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- 3. Customer --}}
                            <td>
                                <div class="cust-cell-wrap">
                                    <div class="cust-avatar">{{ $initials }}</div>
                                    <div class="cust-info-wrap">
                                        <div class="cust-name">
                                            {{ $bayar->customer?->customer_nm ?? '-' }}
                                            @if ($bayar->customer?->customer_cd)
                                                <span class="cust-code-pill">{{ $bayar->customer->customer_cd }}</span>
                                            @endif
                                        </div>
                                        <div class="cust-sub">
                                            <span>Penyetor: {{ $bayar->diterima_dari }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- 4. Kas / Bank & Metode --}}
                            <td>
                                <div class="kas-cell-wrap">
                                    <span class="kas-name">{{ $bayar->kasBank?->nama_akun ?? '-' }}</span>
                                    <div class="kas-meta-row">
                                        <span class="badge-metode {{ $metodeClass }}">{{ $bayar->metode_bayar }}</span>
                                        <span class="kas-coa-sub">{{ $bayar->kasBank?->kode_akun ?? '' }}</span>
                                    </div>
                                </div>
                            </td>

                            {{-- 5. Alokasi Faktur --}}
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span style="font-weight: 600; color: #334155; font-size: 0.775rem;">
                                        {{ $bayar->details->count() }} Faktur Terkait
                                    </span>
                                    <div style="font-size: 0.7rem; color: #64748b; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-family: monospace;" title="{{ $bayar->details->pluck('faktur.faktur_no')->filter()->implode(', ') }}">
                                        {{ $bayar->details->pluck('faktur.faktur_no')->filter()->implode(', ') ?: '-' }}
                                    </div>
                                </div>
                            </td>

                            {{-- 6. Total Kas Diterima --}}
                            <td class="nominal-cell {{ $isVoid ? 'nominal-void' : '' }}">
                                Rp {{ number_format($bayar->total_bayar, 0, ',', '.') }}
                            </td>

                            {{-- 7. Status Pill --}}
                            <td style="text-align: center;">
                                @if ($isVoid)
                                    <span class="pill-status pill-status-void">
                                        <span class="pill-dot"></span>
                                        <span>Dibatalkan</span>
                                    </span>
                                @elseif ($hasRemaining)
                                    <span class="pill-status pill-status-sebagian">
                                        <span class="pill-dot"></span>
                                        <span>Sebagian</span>
                                    </span>
                                @else
                                    <span class="pill-status pill-status-lunas">
                                        <span class="pill-dot"></span>
                                        <span>Lunas</span>
                                    </span>
                                @endif
                            </td>

                            {{-- 8. Smart Action Dropdown Standar AGENTS.md --}}
                            <td style="text-align: center;">
                                <button type="button"
                                        class="btn-action-trigger"
                                        onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')"
                                        aria-label="Aksi Bukti Kas Masuk {{ $bayar->bkm_no }}">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div id="{{ $menuId }}" class="action-dropdown-menu">
                                    <a href="{{ route('keuangan.bayar_piutang.show', $bayar->bayar_id) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Lihat Kuitansi</span>
                                    </a>

                                    @if ($bayar->status_cd === 'POSTED' && Auth::user()->canVoidBayarPiutang())
                                        <button type="button"
                                                class="action-dropdown-item danger-item"
                                                onclick="openModalVoid('{{ $bayar->bayar_id }}', '{{ $bayar->bkm_no }}', 'Rp {{ number_format($bayar->total_bayar, 0, ',', '.') }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            <span>Batalkan (VOID)</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="table-empty-box">
                                    <div class="table-empty-title">Belum ada riwayat penerimaan pembayaran customer</div>
                                    <div class="table-empty-sub">Pencatatan kas masuk pelunasan faktur toko &amp; distributor akan muncul di tabel ini.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Bar --}}
        @if ($bayarList->hasPages())
            <div class="ar-pagination-bar">
                {{ $bayarList->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Include Modal Konfirmasi VOID --}}
@include('keuangan.bayar_piutang.partials.modal-void')

@endsection

@push('scripts')
    <script>
        window.appConfig = {
            baseUrl: '{{ url('') }}'
        };
    </script>
    <script src="{{ asset('js/keuangan/bayar_piutang/bayar-piutang-index.js') }}?v={{ file_exists(public_path('js/keuangan/bayar_piutang/bayar-piutang-index.js')) ? filemtime(public_path('js/keuangan/bayar_piutang/bayar-piutang-index.js')) : time() }}"></script>
@endpush
