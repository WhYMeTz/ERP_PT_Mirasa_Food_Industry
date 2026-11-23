@extends('layouts.app')

@section('title', 'Kas Keluar (Biaya Harian) - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/kas_keluar/kas-keluar-index.css') }}?v={{ file_exists(public_path('css/keuangan/kas_keluar/kas-keluar-index.css')) ? filemtime(public_path('css/keuangan/kas_keluar/kas-keluar-index.css')) : time() }}">
@endpush

@section('content')
<div class="bkk-page-wrapper">

    {{-- 1. TOP HEADER SECTION --}}
    <div class="bkk-top-header">
        <div>
            <div class="bkk-header-kicker">Kas &amp; Perbankan Operasional</div>
            <h1 class="bkk-header-title">Kas Keluar (Biaya Harian)</h1>
            <p class="bkk-header-subtitle">
                Pencatatan pengeluaran operasional pabrik harian (BKK) dan antrean verifikasi akuntansi.
            </p>
        </div>

        <div class="bkk-header-actions">
            <a href="{{ route('keuangan.bayar.index') }}" class="btn-pill-outline">
                <span>Bayar Hutang Supplier</span>
            </a>

            @if (Auth::user()->canCreateBayarHutang())
                <a href="{{ route('keuangan.kas_keluar.create') }}" class="btn-pill-dark">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Catat Kas Keluar</span>
                </a>
            @endif
        </div>
    </div>

    {{-- 2. METRICS CARDS (GAYA ENTERPRISE MINIMALIS 4 KOLOM) --}}
    <div class="bkk-metrics-grid">
        {{-- Card 1: Total Transaksi --}}
        <div class="bkk-metric-card">
            <div class="bkk-metric-top">
                <div class="bkk-metric-header-left">
                    <span class="bkk-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <span class="bkk-metric-label">Total Transaksi</span>
                </div>
                <span class="bkk-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="bkk-metric-value">{{ number_format($countOperasional) }}</div>
            <div class="bkk-metric-footer">
                {{ number_format($countDraft) }} menunggu verifikasi
            </div>
        </div>

        {{-- Card 2: Menunggu Verifikasi --}}
        <div class="bkk-metric-card">
            <div class="bkk-metric-top">
                <div class="bkk-metric-header-left">
                    <span class="bkk-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <span class="bkk-metric-label">Menunggu Jurnal</span>
                </div>
                <span class="bkk-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="bkk-metric-value">{{ number_format($countDraft) }}</div>
            <div class="bkk-metric-footer">
                Perlu verifikasi akuntansi
            </div>
        </div>

        {{-- Card 3: Sudah Jurnal --}}
        <div class="bkk-metric-card">
            <div class="bkk-metric-top">
                <div class="bkk-metric-header-left">
                    <span class="bkk-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <span class="bkk-metric-label">Sudah Jurnal</span>
                </div>
                <span class="bkk-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="bkk-metric-value">{{ number_format($countPosted) }}</div>
            <div class="bkk-metric-footer">
                Tercatat di buku besar
            </div>
        </div>

        {{-- Card 4: Total Pengeluaran --}}
        <div class="bkk-metric-card">
            <div class="bkk-metric-top">
                <div class="bkk-metric-header-left">
                    <span class="bkk-metric-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </span>
                    <span class="bkk-metric-label">Total Pengeluaran</span>
                </div>
                <span class="bkk-metric-dots">&bull;&bull;&bull;</span>
            </div>
            <div class="bkk-metric-value">Rp {{ number_format($totalOperasional, 0, ',', '.') }}</div>
            <div class="bkk-metric-footer">
                Dari {{ $kasBanks->count() }} rekening aktif
            </div>
        </div>
    </div>

    {{-- 3. MAIN DATA CARD (TABLE & CONTROLS) --}}
    <div class="bkk-main-card">
        {{-- Controls Bar --}}
        <div class="bkk-control-bar">
            {{-- Tabs Status Row --}}
            <div class="bkk-tabs-row">
                <div class="bkk-status-pills">
                    <a href="javascript:void(0)" onclick="switchStatusFilter('')"
                       class="bkk-status-tab {{ empty($filters['status_cd']) ? 'active' : '' }}">
                        <span>Semua</span>
                        <span class="badge-count">{{ $countOperasional }}</span>
                    </a>
                    <a href="javascript:void(0)" onclick="switchStatusFilter('DRAFT')"
                       class="bkk-status-tab {{ ($filters['status_cd'] ?? '') === 'DRAFT' ? 'active' : '' }}">
                        <span>Menunggu Jurnal</span>
                        <span class="badge-count">{{ $countDraft }}</span>
                    </a>
                    <a href="javascript:void(0)" onclick="switchStatusFilter('POSTED')"
                       class="bkk-status-tab {{ ($filters['status_cd'] ?? '') === 'POSTED' ? 'active' : '' }}">
                        <span>Sudah Jurnal</span>
                        <span class="badge-count">{{ $countPosted }}</span>
                    </a>
                    <a href="javascript:void(0)" onclick="switchStatusFilter('VOID')"
                       class="bkk-status-tab {{ ($filters['status_cd'] ?? '') === 'VOID' ? 'active' : '' }}">
                        <span>Dibatalkan</span>
                        <span class="badge-count">{{ $countVoid ?? 0 }}</span>
                    </a>
                </div>
            </div>

            {{-- Filter Form Row --}}
            <form id="formBkkFilter" action="{{ route('keuangan.kas_keluar.index') }}" method="GET" class="bkk-filter-form">
                <input type="hidden" id="inputFilterStatusCd" name="status_cd" value="{{ $filters['status_cd'] ?? '' }}">

                {{-- Search Box --}}
                <div class="bkk-search-wrapper">
                    <span class="bkk-search-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text"
                           name="search"
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Cari nomor BKK, nama penerima, PIC..."
                           class="bkk-search-input">
                </div>

                {{-- Kategori Select --}}
                <select name="kategori" class="bkk-select-input" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @if (isset($kategoriMasterList) && $kategoriMasterList->count() > 0)
                        @foreach ($kategoriMasterList as $km)
                            <option value="{{ $km->kode_kategori }}" {{ ($filters['kategori'] ?? '') === $km->kode_kategori ? 'selected' : '' }}>
                                {{ $km->nama_kategori }}
                            </option>
                        @endforeach
                    @else
                        @foreach ($kategoriList as $katKey => $katLabel)
                            <option value="{{ $katKey }}" {{ ($filters['kategori'] ?? '') === $katKey ? 'selected' : '' }}>
                                {{ $katLabel }}
                            </option>
                        @endforeach
                    @endif
                </select>

                {{-- Rekening Kas/Bank Select --}}
                <select name="kas_bank_id" class="bkk-select-input" onchange="this.form.submit()">
                    <option value="">Semua Rekening Kas</option>
                    @foreach ($kasBanks as $kb)
                        <option value="{{ $kb->kas_bank_id }}" {{ ($filters['kas_bank_id'] ?? '') == $kb->kas_bank_id ? 'selected' : '' }}>
                            {{ $kb->nama_akun }}
                        </option>
                    @endforeach
                </select>

                {{-- Tanggal Mulai & Selesai --}}
                <input type="date" name="tgl_mulai" value="{{ $filters['tgl_mulai'] ?? '' }}" class="bkk-date-input" title="Tanggal Mulai">
                <input type="date" name="tgl_selesai" value="{{ $filters['tgl_selesai'] ?? '' }}" class="bkk-date-input" title="Tanggal Selesai">

                <button type="submit" class="btn-filter-apply">Terapkan</button>

                @if (!empty(array_filter($filters)))
                    <a href="{{ route('keuangan.kas_keluar.index') }}" class="btn-filter-reset">Reset</a>
                @endif
            </form>
        </div>

        {{-- Table Responsive Area --}}
        <div class="bkk-table-responsive">
            <table class="bkk-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="min-width: 140px;">No. Voucher</th>
                        <th style="min-width: 220px;">Penerima &amp; PIC</th>
                        <th style="min-width: 190px;">Kategori &amp; Beban</th>
                        <th style="min-width: 170px;">Rekening Kas</th>
                        <th style="min-width: 130px; text-align: right;">Total Keluar</th>
                        <th style="min-width: 150px; text-align: center;">Status</th>
                        <th style="width: 85px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($operasionalList as $index => $bkk)
                        @php
                            $menuId = 'action_bkk_ops_' . $bkk->bkk_id;
                            $words = explode(' ', trim($bkk->penerima_nama));
                            $initials = strtoupper(substr($words[0] ?? 'P', 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : (strlen($words[0] ?? '') > 1 ? substr($words[0], 1, 1) : '')));
                            $nominalFormatted = 'Rp ' . number_format($bkk->total_keluar, 0, ',', '.');
                            $catLabel = $bkk->kategoriPengeluaran?->nama_kategori ?? str_replace('_', ' ', $bkk->kategori_pengeluaran);
                        @endphp
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-size: 0.775rem;">
                                {{ $operasionalList->firstItem() + $index }}
                            </td>

                            {{-- No. Voucher & Tanggal --}}
                            <td>
                                <a href="{{ route('keuangan.kas_keluar.show', $bkk->bkk_id) }}" class="bkk-code-link">
                                    {{ $bkk->bkk_no }}
                                </a>
                                <div class="bkk-cell-date">
                                    {{ date('d M Y', strtotime($bkk->tgl_keluar)) }} &bull; {{ $bkk->details->count() }} item
                                </div>
                            </td>

                            {{-- Penerima Dana & PIC --}}
                            <td>
                                <div class="bkk-payee-group">
                                    <div class="bkk-payee-avatar">{{ $initials }}</div>
                                    <div>
                                        <div class="bkk-payee-name">
                                            {{ $bkk->penerima_nama }}
                                        </div>
                                        <div class="bkk-payee-sub">
                                            @if ($bkk->pemohon)
                                                PIC: {{ $bkk->pemohon->karyawan_nm }}
                                            @elseif ($bkk->tipe_penerima === 'KARYAWAN')
                                                Karyawan Internal
                                            @else
                                                Pihak Ketiga / Vendor
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Kategori & Pos Beban --}}
                            <td>
                                <span class="bkk-category-tag">
                                    {{ $catLabel }}
                                </span>
                                <div class="bkk-coa-sub" title="{{ $bkk->coa ? $bkk->coa->nama_akun : ($bkk->status_cd === 'DRAFT' ? 'Menunggu penentuan akun oleh Akuntansi' : '-') }}">
                                    @if ($bkk->coa)
                                        {{ $bkk->coa->nama_akun }} ({{ $bkk->coa->kode_akun }})
                                    @elseif ($bkk->status_cd === 'DRAFT')
                                        <span style="color: #64748b;">Menunggu Verifikasi</span>
                                    @else
                                        <span style="color: #94a3b8;">-</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Rekening Kas / Bank --}}
                            <td>
                                <div class="bkk-bank-name">
                                    {{ $bkk->kasBank->nama_akun ?? '-' }}
                                </div>
                                <div class="bkk-bank-method">
                                    {{ strtolower($bkk->metode_bayar) }}
                                    @if ($bkk->no_referensi_bank)
                                        &bull; {{ $bkk->no_referensi_bank }}
                                    @endif
                                </div>
                            </td>

                            {{-- Total Pengeluaran (Rp) --}}
                            <td>
                                <span class="bkk-amount-text">
                                    {{ $nominalFormatted }}
                                </span>
                            </td>

                            {{-- Status Jurnal dengan Dot Indikator (Standar Referensi) --}}
                            <td style="text-align: center;">
                                @if ($bkk->status_cd === 'POSTED')
                                    <span class="bkk-status-indicator bkk-status-posted">
                                        <span class="bkk-status-dot"></span>
                                        <span>Sudah Jurnal</span>
                                    </span>
                                @elseif ($bkk->status_cd === 'VOID')
                                    <span class="bkk-status-indicator bkk-status-void">
                                        <span class="bkk-status-dot"></span>
                                        <span>Dibatalkan</span>
                                    </span>
                                @else
                                    <span class="bkk-status-indicator bkk-status-draft">
                                        <span class="bkk-status-dot"></span>
                                        <span>Menunggu Jurnal</span>
                                    </span>
                                @endif
                            </td>

                            {{-- Smart Action Dropdown (AGENTS.md Section 2) --}}
                            <td style="text-align: center;">
                                <button type="button"
                                        class="btn-action-trigger"
                                        aria-haspopup="true"
                                        aria-label="Menu aksi untuk {{ $bkk->bkk_no }}"
                                        onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div id="{{ $menuId }}" class="action-dropdown-menu" role="menu">
                                    <a href="{{ route('keuangan.kas_keluar.show', $bkk->bkk_id) }}" class="action-dropdown-item" role="menuitem">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>Lihat Slip BKK</span>
                                    </a>

                                    @if ($bkk->status_cd === 'DRAFT' && Auth::user()->canCreateJurnal())
                                        <a href="{{ route('keuangan.verifikasi_jurnal.index') }}" class="action-dropdown-item" role="menuitem">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>Verifikasi di Jurnal</span>
                                        </a>
                                    @endif

                                    @if (in_array($bkk->status_cd, ['DRAFT', 'POSTED'], true) && Auth::user()->canVoidBayarHutang())
                                        <button type="button"
                                                class="action-dropdown-item danger-item"
                                                role="menuitem"
                                                onclick='openModalVoidKasKeluar({{ $bkk->bkk_id }}, @json($bkk->bkk_no), @json($nominalFormatted), @json($bkk->kasBank->nama_akun ?? "Kas/Bank"), this)'>
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <span>Batalkan (VOID)</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="bkk-empty-state">
                                    <div class="bkk-empty-icon">
                                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <h3 class="bkk-empty-title">Tidak ada transaksi ditemukan</h3>
                                    <p class="bkk-empty-text">
                                        Belum ada data pengeluaran kas operasional yang sesuai dengan kriteria filter saat ini.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Area --}}
        @if ($operasionalList->hasPages())
            <div class="bkk-pagination-wrapper">
                <div>
                    Menampilkan {{ $operasionalList->firstItem() }} &ndash; {{ $operasionalList->lastItem() }} dari {{ $operasionalList->total() }} voucher
                </div>
                <div>
                    {{ $operasionalList->links() }}
                </div>
            </div>
        @endif
    </div>

</div>

{{-- MODAL VOID KAS KELUAR --}}
@include('keuangan.kas_keluar.partials.modal-void-kas-keluar')

@endsection

@push('scripts')
<script>
    window.appConfig = {
        baseUrl: '{{ url('') }}'
    };
</script>
<script src="{{ asset('js/keuangan/kas_keluar/kas-keluar-index.js') }}?v={{ file_exists(public_path('js/keuangan/kas_keluar/kas-keluar-index.js')) ? filemtime(public_path('js/keuangan/kas_keluar/kas-keluar-index.js')) : time() }}"></script>
@endpush
