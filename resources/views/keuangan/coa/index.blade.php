@extends('layouts.app')

@section('title', 'Bagan Akun (Chart of Accounts / COA)')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/coa/coa-index.css') }}?v={{ filemtime(public_path('css/keuangan/coa/coa-index.css')) }}">
@endpush

@section('content')
<div class="coa-container">
    {{-- CONTENT HEADER --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.25rem;">📑</span>
                <h1 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">
                    Bagan Akun (Chart of Accounts / COA)
                </h1>
            </div>
            <p style="font-size: 0.8125rem; color: #64748b; margin: 0.25rem 0 0 0;">
                Master daftar 42 pos rekening akuntansi PT Mirasa Food Industry (Aktiva, Pasiva, Modal, Pendapatan, HPP &amp; Beban)
            </p>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <a href="{{ route('keuangan.kas_bank.index') }}" class="btn btn-secondary" style="font-weight: 700; font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <span>Buka Mutasi Kas &amp; Bank &rarr;</span>
            </a>
            <button type="button" class="btn btn-primary" onclick="openCreateModal()" style="font-weight: 700; font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 6px; background: #0284c7; border-color: #0284c7; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.25);">
                <span>+ Tambah Akun Baru</span>
            </button>
        </div>
    </div>

    {{-- METRIC CARDS RINGKASAN --}}
    <div class="coa-metrics-grid">
        <div class="coa-metric-card">
            <div class="coa-metric-icon" style="background: #eff6ff; color: #1e40af;">📑</div>
            <div>
                <div class="coa-metric-val">{{ $summaryCounts['total'] ?? 0 }}</div>
                <div class="coa-metric-lbl">Total Akun Terdaftar</div>
            </div>
        </div>

        <div class="coa-metric-card">
            <div class="coa-metric-icon" style="background: #f0fdf4; color: #166534;">🏦</div>
            <div>
                <div class="coa-metric-val">{{ $summaryCounts['kas_bank'] ?? 0 }}</div>
                <div class="coa-metric-lbl">Kas &amp; Rekening Bank</div>
            </div>
        </div>

        <div class="coa-metric-card">
            <div class="coa-metric-icon" style="background: #e0f2fe; color: #0369a1;">📦</div>
            <div>
                <div class="coa-metric-val">{{ $summaryCounts['aktiva'] ?? 0 }}</div>
                <div class="coa-metric-lbl">Aktiva / Harta Pabrik</div>
            </div>
        </div>

        <div class="coa-metric-card">
            <div class="coa-metric-icon" style="background: #fef2f2; color: #991b1b;">🤝</div>
            <div>
                <div class="coa-metric-val">{{ $summaryCounts['pasiva'] ?? 0 }}</div>
                <div class="coa-metric-lbl">Kewajiban &amp; Hutang</div>
            </div>
        </div>

        <div class="coa-metric-card">
            <div class="coa-metric-icon" style="background: #fefce8; color: #854d0e;">💰</div>
            <div>
                <div class="coa-metric-val">{{ ($summaryCounts['pendapatan'] ?? 0) + ($summaryCounts['beban'] ?? 0) }}</div>
                <div class="coa-metric-lbl">Pendapatan &amp; Biaya</div>
            </div>
        </div>
    </div>

    {{-- KATEGORI FILTER TABS --}}
    <div class="coa-tabs-bar">
        <button type="button" class="coa-tab-btn active" data-category="ALL" onclick="switchCoaCategory('ALL')">
            <span>Semua Akun</span>
            <span class="coa-tab-badge">{{ $summaryCounts['total'] ?? 0 }}</span>
        </button>

        <button type="button" class="coa-tab-btn" data-category="KAS_BANK" onclick="switchCoaCategory('KAS_BANK')">
            <span>🏦 Kas &amp; Bank</span>
            <span class="coa-tab-badge">{{ $summaryCounts['kas_bank'] ?? 0 }}</span>
        </button>

        <button type="button" class="coa-tab-btn" data-category="AKTIVA" onclick="switchCoaCategory('AKTIVA')">
            <span>📦 Aktiva (Harta)</span>
            <span class="coa-tab-badge">{{ $summaryCounts['aktiva'] ?? 0 }}</span>
        </button>

        <button type="button" class="coa-tab-btn" data-category="PASIVA" onclick="switchCoaCategory('PASIVA')">
            <span>🤝 Pasiva (Hutang)</span>
            <span class="coa-tab-badge">{{ $summaryCounts['pasiva'] ?? 0 }}</span>
        </button>

        <button type="button" class="coa-tab-btn" data-category="MODAL" onclick="switchCoaCategory('MODAL')">
            <span>🏛️ Modal / Ekuitas</span>
            <span class="coa-tab-badge">{{ $summaryCounts['modal'] ?? 0 }}</span>
        </button>

        <button type="button" class="coa-tab-btn" data-category="PENDAPATAN" onclick="switchCoaCategory('PENDAPATAN')">
            <span>📈 Pendapatan</span>
            <span class="coa-tab-badge">{{ $summaryCounts['pendapatan'] ?? 0 }}</span>
        </button>

        <button type="button" class="coa-tab-btn" data-category="HPP" onclick="switchCoaCategory('HPP')">
            <span>🏭 HPP Produksi</span>
            <span class="coa-tab-badge">{{ $summaryCounts['hpp'] ?? 0 }}</span>
        </button>

        <button type="button" class="coa-tab-btn" data-category="BEBAN" onclick="switchCoaCategory('BEBAN')">
            <span>📉 Biaya / Beban</span>
            <span class="coa-tab-badge">{{ $summaryCounts['beban'] ?? 0 }}</span>
        </button>
    </div>

    {{-- TABEL DATA AKUN COA --}}
    <div class="coa-table-card">
        <div class="coa-toolbar">
            <div class="coa-search-wrap">
                <span class="coa-search-icon">🔍</span>
                <input type="text" 
                       id="coaSearchInput" 
                       class="coa-search-input" 
                       placeholder="Cari kode akun, nama rekening, atau sub-kelompok...">
            </div>

            <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">
                💡 <em>Tip: Klik tombol <strong>Aksi</strong> di kanan untuk mengedit nama atau mengatur saldo awal.</em>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="coa-table">
                <thead>
                    <tr>
                        <th style="width: 110px;">Kode</th>
                        <th>Nama Akun Keuangan</th>
                        <th style="width: 140px;">Klasifikasi</th>
                        <th>Sub-Kelompok</th>
                        <th style="width: 110px; text-align: center;">Posisi</th>
                        <th style="width: 90px; text-align: center;">Saldo Normal</th>
                        <th style="width: 150px; text-align: right;">Saldo Berjalan</th>
                        <th style="width: 80px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="coaTbodyContainer">
                    @forelse ($accounts as $acc)
                        @php
                            $catClass = match($acc->kategori_akun) {
                                'AKTIVA'     => 'badge-aktiva',
                                'PASIVA'     => 'badge-pasiva',
                                'MODAL'      => 'badge-modal',
                                'PENDAPATAN' => 'badge-pendapatan',
                                'HPP'        => 'badge-hpp',
                                'BEBAN'      => 'badge-beban',
                                default      => 'badge-aktiva'
                            };
                            $menuId = 'action-coa-' . $acc->coa_id;
                        @endphp
                        <tr class="coa-table-row" 
                            data-code="{{ $acc->kode_akun }}" 
                            data-name="{{ $acc->nama_akun }}" 
                            data-category="{{ $acc->kategori_akun }}" 
                            data-kas-bank="{{ $acc->is_kas_bank ? '1' : '0' }}">
                            
                            {{-- KODE AKUN --}}
                            <td>
                                <span class="badge-code">{{ $acc->kode_akun }}</span>
                            </td>

                            {{-- NAMA AKUN & LENCANA KAS/BANK --}}
                            <td>
                                <strong style="color: #0f172a; font-size: 0.85rem;">{{ $acc->nama_akun }}</strong>
                                @if ($acc->is_kas_bank)
                                    <span class="badge-kas-bank-tag">
                                        <span>🏦</span> <span>Kas/Bank</span>
                                    </span>
                                @endif
                                @if ($acc->keterangan)
                                    <div style="font-size: 0.7rem; color: #64748b; margin-top: 2px;">
                                        {{ $acc->keterangan }}
                                    </div>
                                @endif
                            </td>

                            {{-- KLASIFIKASI KATEGORI --}}
                            <td>
                                <span class="badge-kategori {{ $catClass }}">
                                    {{ $acc->kategori_akun }}
                                </span>
                            </td>

                            {{-- SUB KELOMPOK --}}
                            <td style="color: #475569; font-size: 0.78rem;">
                                {{ $acc->sub_kategori ?: '-' }}
                            </td>

                            {{-- POSISI LAPORAN --}}
                            <td style="text-align: center; font-size: 0.75rem; font-weight: 700;">
                                <span style="color: {{ $acc->posisi_keuangan === 'NERACA' ? '#0369a1' : '#b45309' }}; background: {{ $acc->posisi_keuangan === 'NERACA' ? '#f0f9ff' : '#fffbeb' }}; padding: 2px 7px; border-radius: 4px; border: 1px solid {{ $acc->posisi_keuangan === 'NERACA' ? '#bae6fd' : '#fef3c7' }};">
                                    {{ $acc->posisi_keuangan }}
                                </span>
                            </td>

                            {{-- SALDO NORMAL --}}
                            <td style="text-align: center; font-size: 0.75rem; font-weight: 800;">
                                <span style="color: {{ $acc->saldo_normal === 'DEBET' ? '#059669' : '#d97706' }};">
                                    {{ $acc->saldo_normal }}
                                </span>
                            </td>

                            {{-- SALDO BERJALAN --}}
                            <td style="text-align: right; font-weight: 800; font-family: ui-monospace, monospace; font-size: 0.85rem; color: {{ $acc->saldo_berjalan < 0 ? '#dc2626' : '#0f172a' }};">
                                Rp {{ number_format($acc->saldo_berjalan, 0, ',', '.') }}
                            </td>

                            {{-- TOMBOL AKSI TERPADU (SMART ACTION DROPDOWN) --}}
                            <td style="text-align: center;">
                                <button type="button" 
                                        class="btn-action-trigger" 
                                        onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div id="{{ $menuId }}" class="action-dropdown-menu">
                                    <button type="button" 
                                            class="action-dropdown-item" 
                                            onclick="openEditModal({{ $acc->coa_id }}, '{{ addslashes($acc->kode_akun) }}', '{{ addslashes($acc->nama_akun) }}', '{{ $acc->kategori_akun }}', '{{ addslashes($acc->sub_kategori ?? '') }}', '{{ $acc->posisi_keuangan }}', '{{ $acc->saldo_normal }}', {{ (float)$acc->saldo_awal }}, {{ $acc->is_kas_bank ? 'true' : 'false' }}, '{{ addslashes($acc->keterangan ?? '') }}')">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #d97706;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        <span>Edit Akun</span>
                                    </button>

                                    <button type="button" 
                                            class="action-dropdown-item item-danger" 
                                            onclick="openDeleteModal({{ $acc->coa_id }}, '{{ addslashes($acc->kode_akun) }}', '{{ addslashes($acc->nama_akun) }}')">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #dc2626;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Hapus Akun</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2.5rem; color: #94a3b8;">
                                Belum ada akun yang terdaftar di Bagan Akun.
                            </td>
                        </tr>
                    @endforelse

                    <tr id="coaEmptyRow" style="display: none;">
                        <td colspan="8" style="text-align: center; padding: 2.5rem; color: #94a3b8;">
                            Tidak ditemukan akun yang sesuai dengan pencarian atau filter kategori.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODALS INCLUDE --}}
@include('keuangan.coa.partials.modal-create')
@include('keuangan.coa.partials.modal-edit')
@include('keuangan.coa.partials.modal-delete')

@endsection

@push('scripts')
<script>
    window.appConfig = {
        baseUrl: '{{ url('') }}'
    };
</script>
<script src="{{ asset('js/keuangan/coa/coa-index.js') }}?v={{ filemtime(public_path('js/keuangan/coa/coa-index.js')) }}"></script>
@endpush
