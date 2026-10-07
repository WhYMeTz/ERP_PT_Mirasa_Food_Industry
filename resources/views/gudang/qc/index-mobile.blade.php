@extends('layouts.qc-mobile')

@section('title', 'Riwayat & Tiket QC Bahan Masuk - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/qc-index.css') }}">
@endpush

@section('content')
<div id="qcIndexWrapper" style="max-width: 800px; margin: 0 auto; transition: max-width 0.2s ease;">
    {{-- HEADER HALAMAN (MOBILE VIEW) --}}
    <div class="qc-index-header">
        <div class="qc-index-title-row">
            <h1 class="qc-index-title">
                <span>🔬</span>
                <span>Riwayat Tiket QC Masuk</span>
            </h1>

            <div class="qc-header-actions">
                @if (Auth::user()?->isSuperAdmin())
                    <a href="{{ route('qc.inbound.index', ['view' => 'desktop']) }}" class="btn-qc-switch-desktop" style="font-size: 0.75rem; font-weight: 700; color: #0284c7; background: #e0f2fe; border: 1px solid #bae6fd; padding: 0.35rem 0.65rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;" title="Kembali ke Tampilan Web Desktop">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Mode Web</span>
                    </a>
                @endif
                @if (Auth::user()?->canCreateQc())
                    <a href="{{ route('qc.inbound.create_pengujian_2') }}" class="btn-qc-p2-inline" style="font-size: 0.75rem; font-weight: 800; color: #7e22ce; background: #f3e8ff; border: 1px solid #d8b4fe; padding: 0.35rem 0.65rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;" title="Input Pengujian II Produksi">
                        <span>🍟 + QC 2</span>
                    </a>
                    <a href="{{ route('qc.inbound.create') }}" class="qc-btn-create-inline">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>+ QC 1</span>
                    </a>
                @endif
            </div>
        </div>
        <div style="margin-top: 0.25rem;">
            <p class="qc-index-subtitle" style="margin: 0;">
                Riwayat sampling mutu kedatangan &bull; Khusus Petugas Lapangan
            </p>
        </div>
    </div>

    {{-- HORIZONTAL SCROLLABLE FILTER CHIPS --}}
    <div class="qc-chips-container">
        @php
            $currKat = $filters['kategori_barang'] ?? '';
            $currSts = $filters['status_qc'] ?? '';
            $currTahap = $filters['tahap_uji'] ?? '';
            $isAll = empty($currKat) && empty($currSts) && empty($currTahap) && empty($filters['search']);
        @endphp
        <a href="{{ route('qc.inbound.index', ['view' => 'mobile']) }}" class="qc-chip {{ $isAll ? 'active' : '' }}">
            <span>Semua</span>
        </a>
        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'tahap_uji' => 'PENGUJIAN_2']) }}" class="qc-chip {{ $currTahap === 'PENGUJIAN_2' ? 'active' : '' }}" style="{{ $currTahap === 'PENGUJIAN_2' ? 'background: #f3e8ff; color: #7e22ce; border-color: #d8b4fe;' : '' }}">
            <span>🍟 Pengujian II</span>
        </a>
        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'status_qc' => 'SIAP_GUDANG']) }}" class="qc-chip {{ $currSts === 'SIAP_GUDANG' ? 'active' : '' }}">
            <span>⏳ Siap Gudang</span>
        </a>
        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'SINGKONG']) }}" class="qc-chip {{ $currKat === 'SINGKONG' ? 'active' : '' }}">
            <span>🥔 Singkong</span>
        </a>
        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'MINYAK']) }}" class="qc-chip {{ $currKat === 'MINYAK' ? 'active' : '' }}">
            <span>🛢️ Minyak</span>
        </a>
        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'PLASTIK']) }}" class="qc-chip {{ $currKat === 'PLASTIK' ? 'active' : '' }}">
            <span>🛍️ Plastik</span>
        </a>
        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'KARTON']) }}" class="qc-chip {{ $currKat === 'KARTON' ? 'active' : '' }}">
            <span>📦 Karton</span>
        </a>
        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'status_qc' => 'DITERIMA_GUDANG']) }}" class="qc-chip {{ $currSts === 'DITERIMA_GUDANG' ? 'active' : '' }}">
            <span>✅ Masuk Penuh</span>
        </a>
        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'status_qc' => 'DITERIMA_PARSIAL']) }}" class="qc-chip {{ in_array($currSts, ['DITERIMA_PARSIAL', 'DITOLAK_PARSIAL', 'PARSIAL']) ? 'active' : '' }}" style="{{ in_array($currSts, ['DITERIMA_PARSIAL', 'DITOLAK_PARSIAL', 'PARSIAL']) ? 'background: #fff7ed; color: #c2410c; border-color: #fdba74;' : '' }}">
            <span>⚠️ Ditolak / Masuk Parsial</span>
        </a>
        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'status_qc' => 'DITOLAK_TOTAL']) }}" class="qc-chip {{ $currSts === 'DITOLAK_TOTAL' ? 'active' : '' }}">
            <span>❌ Ditolak</span>
        </a>
    </div>

    {{-- PENCARIAN TIKET / TRUK / SOPIR / SUPPLIER --}}
    <div class="qc-search-card">
        <form action="{{ route('qc.inbound.index') }}" method="GET" class="qc-search-form">
            <input type="hidden" name="view" value="mobile">
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
                <a href="{{ route('qc.inbound.index', ['view' => 'mobile']) }}" class="qc-search-btn-reset">Reset</a>
            @endif
        </form>
    </div>

    {{-- CARD FEED KHUSUS SMARTPHONE / TIM QC LAPANGAN --}}
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
                        <a href="{{ route('qc.inbound.show', [$qc->qc_id, 'view' => 'mobile']) }}" class="qc-card-id">
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
                        @elseif ($qc->status_qc === 'DITERIMA_PARSIAL' || ($qc->status_qc === 'DITERIMA_GUDANG' && $totalReject > 0))
                            <span class="badge-status-diterima" style="background: #fff7ed; color: #c2410c; border: 1px solid #fdba74;">⚠️ Masuk Parsial</span>
                        @elseif ($qc->status_qc === 'DITERIMA_GUDANG')
                            <span class="badge-status-diterima">✅ Masuk Penuh</span>
                        @elseif ($qc->status_qc === 'DITOLAK_TOTAL')
                            <span class="badge-status-reject">❌ Reject Total</span>
                        @else
                            <span class="badge-status-siap">{{ $qc->status_qc }}</span>
                        @endif

                        @if ($kat === 'SINGKONG')
                            @php
                                $firstDtl = $qc->details->first();
                                $grade = $firstDtl?->grade_cd ?? 'A';
                            @endphp
                            @if (($qc->tahap_uji ?? 'PENGUJIAN_1') === 'PENGUJIAN_2')
                                <span class="badge-status-lab" style="background: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe; font-weight: 800;">
                                    🍟 Pengujian II
                                </span>
                            @else
                                <span class="badge-status-lab" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-weight: 800;">
                                    🚛 Pengujian I
                                </span>
                                @if ($qc->isPengujian2Done())
                                    <span class="badge-status-lab" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;" title="Batch kedatangan ini sudah selesai Pengujian II">✔ Selesai Uji 2</span>
                                @endif
                            @endif

                            @if ($grade === 'B')
                                <span class="badge-status-lab" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-weight: 800;">🟡 Grade B</span>
                            @elseif ($grade === 'REJECT')
                                <span class="badge-status-reject" style="font-weight: 800;">❌ Afkir</span>
                            @else
                                <span class="badge-status-lab" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-weight: 800;">🟢 Grade A</span>
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
                    @if ($kat === 'SINGKONG' && $qc->status_uji_goreng === 'MENUNGGU_LAB')
                        <div class="qc-waiting-banner" style="background: #fffbeb; border-color: #fde68a; color: #92400e;">
                            <span>🍟</span>
                            <span>Pengujian II (Uji Goreng Lab) belum diisi &bull; Klik <strong>Uji Fryer</strong> untuk melengkapi</span>
                        </div>
                    @elseif ($qc->terima)
                        @if ($qc->status_qc === 'DITERIMA_PARSIAL' || $totalReject > 0)
                            <div class="qc-waiting-banner" style="background: #fff7ed; border-color: #fdba74; color: #c2410c;">
                                <span>⚠️</span>
                                <span>Diterima Sebagian &bull; GRN: <strong>{{ $qc->terima->terima_no }}</strong> (Reject: {{ number_format($totalReject, 0, ',', '.') }} kg)</span>
                            </div>
                        @else
                            <div class="qc-grn-banner">
                                <span>📦</span>
                                <span>Sudah diterima Gudang (Penuh) &bull; GRN: <strong>{{ $qc->terima->terima_no }}</strong></span>
                            </div>
                        @endif
                    @elseif ($qc->status_qc === 'SIAP_GUDANG')
                        <div class="qc-waiting-banner">
                            <span>⏳</span>
                            <span>Sampling lolos uji &bull; Menunggu admin gudang membuat Bukti Terima Barang (GRN)</span>
                        </div>
                    @endif
                </div>

                {{-- FOOTER AKSI TOMBOL TOUCH-FRIENDLY --}}
                <div class="qc-card-actions">
                    {{-- TOMBOL CEPAT CATAT PENGUJIAN II UNTUK SINGKONG PENGUJIAN I (Otomatis hilang jika sudah diuji 2) --}}
                    @if ($kat === 'SINGKONG' && ($qc->tahap_uji ?? 'PENGUJIAN_1') === 'PENGUJIAN_1' && Auth::user()?->canCreateQc() && !$qc->isPengujian2Done())
                        <a href="{{ route('qc.inbound.create', ['parent_qc_id' => $qc->qc_id, 'tahap' => 2, 'po_id' => $qc->po_id, 'supplier_id' => $qc->supplier_id, 'view' => 'mobile']) }}" class="qc-btn-action" style="background: #9333ea; color: #ffffff; border-color: #7e22ce; font-weight: 800; text-decoration: none;" title="Lanjutkan Pengujian II untuk kedatangan singkong ini">
                            <span>🍟 + Uji 2</span>
                        </a>
                    @elseif ($kat === 'SINGKONG' && $qc->status_uji_goreng === 'MENUNGGU_LAB')
                        <button type="button" class="qc-btn-action" style="background: #d97706; color: #ffffff; border-color: #b45309; font-weight: 800;" onclick="openModalUjiFryer('{{ $qc->qc_id }}', '{{ $qc->qc_no }}', '{{ $qc->details->first()?->qcdtl_id }}')">
                            <span>🍟 Uji Fryer</span>
                        </button>
                    @endif

                    <a href="{{ route('qc.inbound.show', [$qc->qc_id, 'view' => 'mobile']) }}" class="qc-btn-action qc-btn-detail">
                        <span>👁️ Detail Uji</span>
                    </a>

                    @if ($qc->status_qc === 'DITOLAK_TOTAL' || $qc->status_qc === 'DITERIMA_PARSIAL' || $totalReject > 0)
                        <a href="{{ route('qc.inbound.berita_acara', $qc->qc_id) }}" class="qc-btn-action" style="background: #fee2e2; color: #dc2626; border-color: #fca5a5; font-weight: 700; text-decoration: none;" title="Cetak Berita Acara Penolakan Barang">
                            <span>📄 Berita Acara</span>
                        </a>
                    @endif

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

    {{-- PAGINASI --}}
    @if ($inspeksiList->hasPages())
        <div style="margin-top: 1.25rem; background: #ffffff; padding: 0.85rem; border-radius: 12px; border: 1px solid #e2e8f0; text-align: center;">
            {{ $inspeksiList->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODAL KONFIRMASI HAPUS --}}
@include('gudang.qc.partials.modal-delete')

{{-- MODAL UJI GORENG FRYER --}}
@include('gudang.qc.partials.modal-uji-fryer')

@endsection

@push('scripts')
    <script src="{{ asset('js/gudang/qc/qc-index.js') }}"></script>
@endpush
