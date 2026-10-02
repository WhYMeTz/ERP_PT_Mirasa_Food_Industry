@extends('layouts.qc-mobile')

@section('title', 'Riwayat & Tiket QC Bahan Masuk - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/qc-index.css') }}">
@endpush

@section('content')
<div id="qcIndexWrapper" style="max-width: 800px; margin: 0 auto; transition: max-width 0.2s ease;">
    {{-- HEADER HALAMAN & VIEW SWITCHER (DUAL MODE: HP VS PC GUDANG) --}}
    <div class="qc-index-header">
        <div class="qc-index-title-row">
            <h1 class="qc-index-title">
                <span>🔬</span>
                <span>Riwayat Tiket QC Masuk</span>
            </h1>

            <div class="qc-header-actions">
                @if (Auth::user()?->canCreateQc())
                    <a href="{{ route('qc.inbound.create') }}" class="qc-btn-create-inline">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Input QC</span>
                    </a>
                @endif
            </div>
        </div>
        <p class="qc-index-subtitle">
            Daftar riwayat tiket inspeksi mutu bahan baku kedatangan PT Mirasa Food Industry.
        </p>
    </div>

    {{-- HORIZONTAL SCROLLABLE FILTER CHIPS --}}
    <div class="qc-chips-container">
        @php
            $currKat = $filters['kategori_barang'] ?? '';
            $currSts = $filters['status_qc'] ?? '';
            $isAll = empty($currKat) && empty($currSts) && empty($filters['search']);
        @endphp
        <a href="{{ route('qc.inbound.index') }}" class="qc-chip {{ $isAll ? 'active' : '' }}">
            <span>Semua</span>
        </a>
        <a href="{{ route('qc.inbound.index', ['status_qc' => 'SIAP_GUDANG']) }}" class="qc-chip {{ $currSts === 'SIAP_GUDANG' ? 'active' : '' }}">
            <span>⏳ Siap Gudang</span>
        </a>
        <a href="{{ route('qc.inbound.index', ['kategori_barang' => 'SINGKONG']) }}" class="qc-chip {{ $currKat === 'SINGKONG' ? 'active' : '' }}">
            <span>🥔 Singkong</span>
        </a>
        <a href="{{ route('qc.inbound.index', ['kategori_barang' => 'MINYAK']) }}" class="qc-chip {{ $currKat === 'MINYAK' ? 'active' : '' }}">
            <span>🛢️ Minyak</span>
        </a>
        <a href="{{ route('qc.inbound.index', ['kategori_barang' => 'PLASTIK']) }}" class="qc-chip {{ $currKat === 'PLASTIK' ? 'active' : '' }}">
            <span>🛍️ Plastik</span>
        </a>
        <a href="{{ route('qc.inbound.index', ['kategori_barang' => 'KARTON']) }}" class="qc-chip {{ $currKat === 'KARTON' ? 'active' : '' }}">
            <span>📦 Karton</span>
        </a>
        <a href="{{ route('qc.inbound.index', ['status_qc' => 'DITERIMA_GUDANG']) }}" class="qc-chip {{ $currSts === 'DITERIMA_GUDANG' ? 'active' : '' }}">
            <span>✅ Masuk Gudang</span>
        </a>
        <a href="{{ route('qc.inbound.index', ['status_qc' => 'DITOLAK_TOTAL']) }}" class="qc-chip {{ $currSts === 'DITOLAK_TOTAL' ? 'active' : '' }}">
            <span>❌ Ditolak</span>
        </a>
    </div>

    {{-- PENCARIAN TIKET / TRUK / SOPIR / SUPPLIER --}}
    <div class="qc-search-card">
        <form action="{{ route('qc.inbound.index') }}" method="GET" class="qc-search-form">
            @if (!empty($filters['kategori_barang']))
                <input type="hidden" name="kategori_barang" value="{{ $filters['kategori_barang'] }}">
            @endif
            @if (!empty($filters['status_qc']))
                <input type="hidden" name="status_qc" value="{{ $filters['status_qc'] }}">
            @endif

            <div class="qc-search-input-wrap">
                <svg class="qc-search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" class="qc-search-input" placeholder="Cari No Tiket, Plat Truk, Sopir, Supplier..." value="{{ $filters['search'] ?? '' }}">
            </div>
            <button type="submit" class="qc-search-btn">Cari</button>
            @if (!empty($filters['search']) || !empty($filters['kategori_barang']) || !empty($filters['status_qc']))
                <a href="{{ route('qc.inbound.index') }}" class="qc-search-btn-reset">Reset</a>
            @endif
        </form>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAMPILAN 1: CARD FEED KHUSUS SMARTPHONE / TIM QC LAPANGAN               --}}
    {{-- ========================================================================= --}}
    <div id="qcCardContainer" class="qc-card-feed">
        @forelse ($inspeksiList as $qc)
            @php
                $totalGross = $qc->details->sum('qty_timbang_gross');
                $totalRefraksi = $qc->details->sum('qty_refraksi');
                $totalReject = $qc->details->sum('qty_reject');
                $totalNetto = $qc->details->sum('qty_netto_lolos');
                $kat = strtoupper((string) ($qc->kategori_barang ?: 'SINGKONG'));
                $isLocked = !empty($qc->terima) && !Auth::user()?->isSuperAdmin();
            @endphp
            <div class="qc-card-item">
                {{-- HEADER KARTU --}}
                <div class="qc-card-header">
                    <div class="qc-card-id-wrap">
                        <a href="{{ route('qc.inbound.show', $qc->qc_id) }}" class="qc-card-id">
                            {{ $qc->qc_no }}
                        </a>
                        <span class="qc-card-time">
                            📅 {{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : '-' }} &bull; {{ $qc->petugas_qc_nama }}
                        </span>
                    </div>
                    <div class="qc-card-badges">
                        <span class="badge-commodity">
                            @if ($kat === 'SINGKONG') 🥔 @elseif ($kat === 'MINYAK') 🛢️ @elseif ($kat === 'PLASTIK') 🛍️ @elseif ($kat === 'KARTON') 📦 @else ✨ @endif
                            {{ $kat }}
                        </span>

                        @if ($qc->status_qc === 'SIAP_GUDANG')
                            <span class="badge-status-siap">⏳ Siap Gudang</span>
                        @elseif ($qc->status_qc === 'DITERIMA_GUDANG')
                            <span class="badge-status-diterima">✅ Masuk Gudang</span>
                        @elseif ($qc->status_qc === 'DITOLAK_TOTAL')
                            <span class="badge-status-reject">❌ Reject Total</span>
                        @else
                            <span class="badge-status-siap">{{ $qc->status_qc }}</span>
                        @endif

                        @if ($kat === 'SINGKONG')
                            @if ($qc->status_uji_goreng === 'MENUNGGU_LAB')
                                <span class="badge-status-lab">⏳ Lab Fryer</span>
                            @elseif ($qc->status_uji_goreng === 'SELESAI')
                                <span class="badge-status-lab" style="background: #dcfce7; color: #166534;">🍟 Fryer Selesai</span>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- BADAN KARTU --}}
                <div class="qc-card-body">
                    {{-- META GRID INFO LOGISTIK --}}
                    <div class="qc-card-meta-grid">
                        <div class="qc-meta-item">
                            <span class="qc-meta-label">Mitra Supplier</span>
                            <span class="qc-meta-value strong" style="color: #0f172a;">
                                {{ $qc->supplier?->supplier_nm ?? 'Supplier Langsung' }}
                            </span>
                            @if ($qc->po)
                                <span style="font-size: 0.72rem; color: #0284c7; font-weight: 700; margin-top: 1px;">
                                    PO: {{ $qc->po->po_no }}
                                </span>
                            @endif
                        </div>

                        <div class="qc-meta-item">
                            <span class="qc-meta-label">Armada Truk &amp; Sopir</span>
                            <span class="qc-meta-value strong">
                                🚛 {{ $qc->plat_nomor_truk ?: 'No Plat -' }}
                            </span>
                            <span style="font-size: 0.72rem; color: #64748b; margin-top: 1px;">
                                Sopir: {{ $qc->sopir_nama ?: '-' }}
                            </span>
                        </div>
                    </div>

                    {{-- RINGKASAN METRIK TIMBANGAN SAMPLING --}}
                    <div class="qc-metric-box">
                        <div class="qc-metric-col">
                            <span class="qc-metric-label">Gross</span>
                            <span class="qc-metric-val">{{ number_format($totalGross, 0, ',', '.') }}</span>
                        </div>
                        <div class="qc-metric-col">
                            <span class="qc-metric-label">Refraksi</span>
                            <span class="qc-metric-val refraksi">{{ number_format($totalRefraksi, 0, ',', '.') }}</span>
                        </div>
                        <div class="qc-metric-col">
                            <span class="qc-metric-label">Reject</span>
                            <span class="qc-metric-val reject">{{ number_format($totalReject, 0, ',', '.') }}</span>
                        </div>
                        <div class="qc-metric-col">
                            <span class="qc-metric-label">Netto Lolos</span>
                            <span class="qc-metric-val netto">{{ number_format($totalNetto, 0, ',', '.') }} <small style="font-size: 0.65rem;">kg</small></span>
                        </div>
                    </div>

                    {{-- STATUS INTEGRASI GUDANG PERSIMPANGAN --}}
                    @if ($qc->terima)
                        <div class="qc-grn-banner">
                            <span>📦</span>
                            <span>Sudah diterima Gudang &bull; GRN: <strong>{{ $qc->terima->terima_no }}</strong></span>
                        </div>
                    @elseif ($qc->status_qc === 'SIAP_GUDANG')
                        <div class="qc-waiting-banner">
                            <span>⏳</span>
                            <span>Sampling lolos uji &bull; Menunggu admin gudang membuat Bukti Terima Barang (GRN)</span>
                        </div>
                    @endif
                </div>

                {{-- FOOTER AKSI TOMBOL TOUCH-FRIENDLY --}}
                <div class="qc-card-actions">
                    <a href="{{ route('qc.inbound.show', $qc->qc_id) }}" class="qc-btn-action qc-btn-detail">
                        <span>👁️ Detail Uji</span>
                    </a>

                    {{-- TOMBOL EDIT KHUSUS SAMPLING QC --}}
                    @if (Auth::user()?->canEditQc())
                        @if (!$isLocked)
                            <a href="{{ route('qc.inbound.edit', [$qc->qc_id, 'view' => 'mobile']) }}" class="qc-btn-action qc-btn-edit" title="Edit / Koreksi Parameter Mutu Sampling">
                                <span>✏️ Edit</span>
                            </a>
                        @else
                            <button type="button" class="qc-btn-action qc-btn-locked" title="Tiket terkunci karena sudah diproses Gudang menjadi GRN #{{ $qc->terima->terima_no }}">
                                <span>🔒 Terkunci</span>
                            </button>
                        @endif
                    @endif

                    {{-- SHORTCUT TARIK KE GRN JIKA DI HP DIBUKA OLEH GUDANG --}}
                    @if ($qc->status_qc === 'SIAP_GUDANG' && Auth::user()?->canAccessTerima())
                        <a href="{{ route('gudang.terima.create', ['qc_id' => $qc->qc_id]) }}" class="qc-btn-action" style="background: #10b981; color: #ffffff; border-color: #059669;">
                            <span>📦 Tarik GRN</span>
                        </a>
                    @endif

                    {{-- TOMBOL HAPUS DENGAN MODAL BAHAYA --}}
                    @if (Auth::user()?->canDeleteQc() && !$isLocked)
                        <button type="button" class="qc-btn-action qc-btn-delete" onclick="openDeleteQcModal('{{ $qc->qc_id }}', '{{ $qc->qc_no }}')" title="Batalkan Tiket QC">
                            <span>🗑️</span>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="qc-empty-state">
                <div class="qc-empty-icon">🔬</div>
                <div class="qc-empty-title">Belum Ada Tiket QC Inbound</div>
                <div class="qc-empty-desc">
                    Silakan klik tombol "Input QC Baru" di atas untuk memulai pencatatan inspeksi kedatangan bahan baku.
                </div>
            </div>
        @endforelse
    </div>

    {{-- ========================================================================= --}}
    {{-- TAMPILAN 2: TABEL OPERASIONAL GUDANG (KHUSUS ADMIN GUDANG DI PC/DESKTOP) --}}
    {{-- ========================================================================= --}}

    {{-- PAGINASI --}}
    @if ($inspeksiList->hasPages())
        <div style="margin-top: 1.25rem; background: #ffffff; padding: 0.85rem; border-radius: 12px; border: 1px solid #e2e8f0; text-align: center;">
            {{ $inspeksiList->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODAL KONFIRMASI HAPUS --}}
@include('gudang.qc.partials.modal-delete')

@endsection

@push('scripts')
    <script src="{{ asset('js/gudang/qc/qc-index.js') }}"></script>
@endpush
