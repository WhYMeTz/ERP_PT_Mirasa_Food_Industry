@extends('layouts.qc-mobile')

@section('title', 'Riwayat & Tiket QC Bahan Masuk - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/mobile/qc-index.css') }}?v={{ filemtime(public_path('css/gudang/qc/mobile/qc-index.css')) }}">
@endpush

@section('content')
<div id="qcIndexWrapper">
    {{-- HEADER HALAMAN (MODERN ENTERPRISE MOBILE HEADER) --}}
    <div class="qc-index-header">
        <div class="qc-index-title-row">
            <h1 class="qc-index-title">
                <span>🔬</span>
                <span>Riwayat Tiket QC</span>
            </h1>

            <div class="qc-header-actions">
                @if (Auth::user()?->isSuperAdmin())
                    <a href="{{ route('qc.inbound.index', ['view' => 'desktop']) }}" class="btn-qc-switch-desktop" title="Buka Mode Tampilan Web ERP Desktop">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Mode Web</span>
                    </a>
                @endif

                @if (Auth::user()?->canCreateQcMobile())
                    <a href="{{ route('qc.inbound.create_pengujian_2') }}" class="btn-qc-p2-inline" title="Input Pengujian II Produksi (Uji Wajan/Fryer)">
                        <span>🍟 + QC 2</span>
                    </a>
                    <a href="{{ route('qc.inbound.create') }}" class="qc-btn-create-inline" title="Buat Tiket QC 1 Kedatangan Baru">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>+ QC 1</span>
                    </a>
                @endif
            </div>
        </div>
        <p class="qc-index-subtitle">
            Sampling mutu kedatangan bahan &bull; Khusus Tim QC Lapangan
        </p>
    </div>

    {{-- HORIZONTAL SCROLLABLE FILTER CHIPS --}}
    <div class="qc-chips-container">
        @php
            $currKat = strtoupper((string) ($filters['kategori_barang'] ?? ''));
            $currSts = strtoupper((string) ($filters['status_qc'] ?? ''));
            $currTahap = strtoupper((string) ($filters['tahap_uji'] ?? ''));
            $isAll = empty($currKat) && empty($currSts) && empty($currTahap) && empty($filters['search']);
        @endphp

        <a href="{{ route('qc.inbound.index', ['view' => 'mobile']) }}" class="qc-chip {{ $isAll ? 'active' : '' }}">
            <span>Semua</span>
        </a>

        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'status_qc' => 'SIAP_GUDANG']) }}" class="qc-chip chip-siap {{ $currSts === 'SIAP_GUDANG' ? 'active' : '' }}">
            <span>⏳ Siap Gudang</span>
        </a>

        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'tahap_uji' => 'PENGUJIAN_2']) }}" class="qc-chip chip-bp {{ $currTahap === 'PENGUJIAN_2' ? 'active' : '' }}">
            <span>🍟 Pengujian II</span>
        </a>

        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'SINGKONG']) }}" class="qc-chip chip-singkong {{ $currKat === 'SINGKONG' ? 'active' : '' }}">
            <span>🥔 Singkong</span>
        </a>

        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'MINYAK']) }}" class="qc-chip chip-minyak {{ $currKat === 'MINYAK' ? 'active' : '' }}">
            <span>🛢️ Minyak</span>
        </a>

        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'PLASTIK']) }}" class="qc-chip chip-plastik {{ $currKat === 'PLASTIK' ? 'active' : '' }}">
            <span>🛍️ Plastik</span>
        </a>

        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'KARTON']) }}" class="qc-chip chip-karton {{ $currKat === 'KARTON' ? 'active' : '' }}">
            <span>📦 Karton</span>
        </a>

        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'MSG']) }}" class="qc-chip chip-bp {{ $currKat === 'MSG' ? 'active' : '' }}">
            <span>🧂 MSG</span>
        </a>

        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'GARAM']) }}" class="qc-chip chip-bp {{ $currKat === 'GARAM' ? 'active' : '' }}">
            <span>🧂 Garam</span>
        </a>

        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'PERENYAH']) }}" class="qc-chip chip-bp {{ $currKat === 'PERENYAH' ? 'active' : '' }}">
            <span>🥨 Perenyah</span>
        </a>

        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'kategori_barang' => 'BUMBU']) }}" class="qc-chip chip-bp {{ $currKat === 'BUMBU' ? 'active' : '' }}">
            <span>🌶️ Bumbu</span>
        </a>

        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'status_qc' => 'DITERIMA_GUDANG']) }}" class="qc-chip chip-masuk {{ $currSts === 'DITERIMA_GUDANG' ? 'active' : '' }}">
            <span>✅ Masuk Penuh</span>
        </a>

        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'status_qc' => 'DITERIMA_PARSIAL']) }}" class="qc-chip chip-karton {{ in_array($currSts, ['DITERIMA_PARSIAL', 'DITOLAK_PARSIAL', 'PARSIAL']) ? 'active' : '' }}">
            <span>⚠️ Masuk Parsial</span>
        </a>

        <a href="{{ route('qc.inbound.index', ['view' => 'mobile', 'status_qc' => 'DITOLAK_TOTAL']) }}" class="qc-chip chip-tolak {{ $currSts === 'DITOLAK_TOTAL' ? 'active' : '' }}">
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
            @if (!empty($filters['tahap_uji']))
                <input type="hidden" name="tahap_uji" value="{{ $filters['tahap_uji'] }}">
            @endif

            <div class="qc-search-input-wrap">
                <svg class="qc-search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" class="qc-search-input" placeholder="Cari No Tiket, Plat Truk, Sopir, Supplier..." value="{{ $filters['search'] ?? '' }}">
            </div>

            <button type="submit" class="qc-search-btn">Cari</button>

            @if (!empty($filters['search']) || !empty($filters['kategori_barang']) || !empty($filters['status_qc']) || !empty($filters['tahap_uji']))
                <a href="{{ route('qc.inbound.index', ['view' => 'mobile']) }}" class="qc-search-btn-reset">Reset</a>
            @endif
        </form>
    </div>

    {{-- CARD FEED KHUSUS SMARTPHONE / TIM QC LAPANGAN --}}
    <div id="qcCardContainer" class="qc-card-feed">
        @forelse ($inspeksiList as $qc)
            @php
                $totalGross = (float) $qc->details->sum('qty_timbang_gross');
                $totalRefraksi = (float) $qc->details->sum('qty_refraksi');
                $totalReject = (float) $qc->details->sum('qty_reject');
                $totalNetto = (float) $qc->details->sum('qty_netto_lolos');
                $kat = strtoupper((string) ($qc->kategori_barang ?: 'SINGKONG'));
                $isLocked = !empty($qc->terima) && !Auth::user()?->isSuperAdmin();
                $cardItemDtl = $qc->details->first();

                // Klasifikasi kelas warna kartu per komoditas
                $katClass = match($kat) {
                    'SINGKONG' => 'kat-singkong',
                    'MINYAK'   => 'kat-minyak',
                    'PLASTIK'  => 'kat-plastik',
                    'KARTON'   => 'kat-karton',
                    default    => 'kat-bp',
                };

                // Badge komoditas
                $commBadge = match($kat) {
                    'SINGKONG' => ['icon' => '🥔', 'label' => 'SINGKONG', 'class' => 'comm-singkong'],
                    'MINYAK'   => ['icon' => '🛢️', 'label' => 'MINYAK',   'class' => 'comm-minyak'],
                    'PLASTIK'  => ['icon' => '🛍️', 'label' => 'PLASTIK',  'class' => 'comm-plastik'],
                    'KARTON'   => ['icon' => '📦', 'label' => 'KARTON',   'class' => 'comm-karton'],
                    'MSG'      => ['icon' => '🧂', 'label' => 'MSG',      'class' => 'comm-bp'],
                    'GARAM'    => ['icon' => '🧂', 'label' => 'GARAM',    'class' => 'comm-bp'],
                    'PERENYAH' => ['icon' => '🥨', 'label' => 'PERENYAH', 'class' => 'comm-bp'],
                    'BUMBU'    => ['icon' => '🌶️', 'label' => 'BUMBU',    'class' => 'comm-bp'],
                    default    => ['icon' => '🧂', 'label' => $kat,       'class' => 'comm-bp'],
                };

                // Deteksi Satuan
                $satRaw = $cardItemDtl?->barang?->satuanDasar?->satuan_nm ?: ($cardItemDtl?->barang?->satuanDasar?->satuan_cd ?: '');
                $satuan = 'kg';
                if ($kat === 'KARTON') {
                    $satuan = (strtoupper(trim($satRaw)) === 'BOX') ? 'box' : 'pcs';
                } elseif ($kat === 'MINYAK') {
                    $isLiter = (stripos($satRaw, 'liter') !== false || stripos($satRaw, 'ltr') !== false || strtoupper(trim($satRaw)) === 'L' || (str_contains(strtoupper($cardItemDtl?->barang?->barang_nm ?? ''), 'KELAPA') && !str_contains(strtoupper($cardItemDtl?->barang?->barang_nm ?? ''), 'SAWIT')));
                    $satuan = $isLiter ? 'liter' : 'kg';
                }
            @endphp

            <div class="qc-card-item {{ $katClass }}">
                {{-- HEADER KARTU --}}
                <div class="qc-card-header">
                    <div class="qc-card-top-row">
                        <a href="{{ route('qc.inbound.show', [$qc->qc_id, 'view' => 'mobile']) }}" class="qc-card-id">
                            <span>#{{ $qc->qc_no }}</span>
                        </a>

                        <div class="qc-card-badges">
                            {{-- Badge Komoditas --}}
                            <span class="badge-commodity {{ $commBadge['class'] }}">
                                <span>{{ $commBadge['icon'] }}</span>
                                <span>{{ $commBadge['label'] }}</span>
                            </span>

                            {{-- Badge Status QC --}}
                            @if ($qc->status_qc === 'SIAP_GUDANG')
                                <span class="badge-status-siap">⏳ Siap Gudang</span>
                            @elseif ($qc->status_qc === 'DITERIMA_PARSIAL' || ($qc->status_qc === 'DITERIMA_GUDANG' && $totalReject > 0))
                                <span class="badge-status-diterima" style="background: #fff7ed; color: #c2410c; border: 1px solid #fdba74;">⚠️ Masuk Parsial</span>
                            @elseif ($qc->status_qc === 'DITERIMA_GUDANG')
                                <span class="badge-status-diterima">✅ Masuk Penuh</span>
                            @elseif ($qc->status_qc === 'DITOLAK_TOTAL')
                                <span class="badge-status-reject">❌ Ditolak</span>
                            @else
                                <span class="badge-status-siap">{{ $qc->status_qc }}</span>
                            @endif

                            {{-- Badge Ekstra Singkong --}}
                            @if ($kat === 'SINGKONG')
                                @php
                                    $grade = $cardItemDtl?->grade_cd ?? 'A';
                                @endphp
                                @if (($qc->tahap_uji ?? 'PENGUJIAN_1') === 'PENGUJIAN_2')
                                    <span class="badge-status-lab" style="background: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe; font-weight: 800;">
                                        🍟 Uji 2
                                    </span>
                                @else
                                    <span class="badge-status-lab" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-weight: 800;">
                                        🚛 Uji 1
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
                            @elseif ($kat === 'MINYAK')
                                @if ($cardItemDtl && $cardItemDtl->ffa_qc !== null)
                                    @if ((float)$cardItemDtl->ffa_qc <= 0.20)
                                        <span class="badge-status-lab" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; font-weight: 800;">🧪 FFA {{ number_format((float)$cardItemDtl->ffa_qc, 2) }}%</span>
                                    @else
                                        <span class="badge-status-reject" style="font-weight: 800;">⚠️ FFA {{ number_format((float)$cardItemDtl->ffa_qc, 2) }}%</span>
                                    @endif
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Metadata Baris Bawah: Waktu & Petugas --}}
                    <div class="qc-card-meta-sub">
                        <span class="qc-card-time">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>{{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : '-' }} WIB</span>
                        </span>
                        <span style="font-weight: 600; color: #475569;">
                            👤 {{ $qc->petugas_qc_nama ?: '-' }}
                        </span>
                    </div>
                </div>

                {{-- BADAN KARTU --}}
                <div class="qc-card-body">
                    {{-- NAMA BARANG / KOMODITAS STRIP --}}
                    @if ($cardItemDtl && ($cardItemDtl->barang || $qc->nama_jenis))
                        <div class="qc-item-strip">
                            <div class="qc-item-name">
                                {{ $cardItemDtl->barang?->barang_nm ?: ($qc->nama_jenis ?: $commBadge['label']) }}
                            </div>
                            <span class="qc-item-tag">
                                {{ $commBadge['label'] }}
                            </span>
                        </div>
                    @endif

                    {{-- META GRID INFO LOGISTIK & VENDOR --}}
                    <div class="qc-card-meta-grid">
                        <div class="qc-meta-item">
                            <span class="qc-meta-label">Mitra Supplier</span>
                            <span class="qc-meta-value strong">
                                {{ $qc->supplier?->supplier_nm ?? 'Supplier Langsung' }}
                            </span>
                            @if ($qc->po)
                                <span class="qc-po-pill">
                                    PO: {{ $qc->po->po_no }}
                                </span>
                            @endif
                        </div>

                        <div class="qc-meta-item">
                            <span class="qc-meta-label">Armada Truk &amp; Sopir</span>
                            <div>
                                <span class="qc-truck-pill">
                                    🚛 {{ $qc->plat_nomor_truk ?: '-' }}
                                </span>
                            </div>
                            <span style="font-size: 0.725rem; color: #64748b; font-weight: 600; margin-top: 3px;">
                                Sopir: {{ $qc->sopir_nama ?: '-' }}
                            </span>
                        </div>
                    </div>

                    {{-- RINGKASAN METRIK TIMBANGAN / KUANTITAS SAMPLING --}}
                    <div class="qc-metric-box" style="{{ $kat === 'MINYAK' ? 'grid-template-columns: repeat(3, 1fr);' : '' }}">
                        <div class="qc-metric-col">
                            <span class="qc-metric-label">Gross</span>
                            <span class="qc-metric-val">{{ number_format($totalGross, 0, ',', '.') }}</span>
                        </div>

                        @if ($kat !== 'MINYAK')
                            <div class="qc-metric-col">
                                <span class="qc-metric-label">Refraksi</span>
                                <span class="qc-metric-val {{ $totalRefraksi > 0 ? 'refraksi' : '' }}">
                                    {{ number_format($totalRefraksi, 0, ',', '.') }}
                                </span>
                            </div>
                        @endif

                        <div class="qc-metric-col">
                            <span class="qc-metric-label">Reject</span>
                            <span class="qc-metric-val {{ $totalReject > 0 ? 'reject' : '' }}">
                                {{ number_format($totalReject, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="qc-metric-col highlight-netto">
                            <span class="qc-metric-label" style="color: #15803d;">Netto Lolos</span>
                            <span class="qc-metric-val netto">
                                {{ number_format($totalNetto, 0, ',', '.') }} <small style="font-size: 0.68rem; font-weight: 800;">{{ $satuan }}</small>
                            </span>
                        </div>
                    </div>

                    {{-- STATUS INTEGRASI GUDANG PERSIMPANGAN --}}
                    @if ($kat === 'SINGKONG' && $qc->status_uji_goreng === 'MENUNGGU_LAB')
                        <div class="qc-waiting-banner" style="background: #fffbeb; border-color: #fde68a; color: #92400e;">
                            <span>🍟</span>
                            <span>Pengujian II (Uji Goreng Lab) belum diisi &bull; Klik <strong>Uji Fryer</strong></span>
                        </div>
                    @elseif ($qc->terima)
                        @if ($qc->status_qc === 'DITERIMA_PARSIAL' || $totalReject > 0)
                            <div class="qc-waiting-banner" style="background: #fff7ed; border-color: #fdba74; color: #c2410c;">
                                <span>⚠️</span>
                                <span>Diterima Sebagian &bull; GRN: <strong>#{{ $qc->terima->terima_no }}</strong> (Reject: {{ number_format($totalReject, 0, ',', '.') }} {{ $satuan }})</span>
                            </div>
                        @else
                            <div class="qc-grn-banner">
                                <span>📦</span>
                                <span>Masuk Gudang Penuh &bull; GRN: <strong>#{{ $qc->terima->terima_no }}</strong></span>
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
                    {{-- TOMBOL CEPAT CATAT PENGUJIAN II UNTUK SINGKONG PENGUJIAN I --}}
                    @if ($kat === 'SINGKONG' && ($qc->tahap_uji ?? 'PENGUJIAN_1') === 'PENGUJIAN_1' && Auth::user()?->canCreateQc() && !$qc->isPengujian2Done())
                        <a href="{{ route('qc.inbound.create', ['parent_qc_id' => $qc->qc_id, 'tahap' => 2, 'po_id' => $qc->po_id, 'supplier_id' => $qc->supplier_id, 'view' => 'mobile']) }}" class="qc-btn-action" style="background: #7c3aed; color: #ffffff; border-color: #6d28d9; font-weight: 800; text-decoration: none;" title="Lanjutkan Pengujian II untuk kedatangan singkong ini">
                            <span>🍟 + Uji 2</span>
                        </a>
                    @elseif ($kat === 'SINGKONG' && $qc->status_uji_goreng === 'MENUNGGU_LAB')
                        <button type="button" class="qc-btn-action" style="background: #d97706; color: #ffffff; border-color: #b45309; font-weight: 800;" onclick="openModalUjiFryer('{{ $qc->qc_id }}', '{{ $qc->qc_no }}', '{{ $cardItemDtl?->qcdtl_id }}')">
                            <span>🍟 Uji Fryer</span>
                        </button>
                    @endif

                    <a href="{{ route('qc.inbound.show', [$qc->qc_id, 'view' => 'mobile']) }}" class="qc-btn-action qc-btn-detail">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Detail Uji</span>
                    </a>

                    {{-- TOMBOL EDIT KHUSUS SAMPLING QC --}}
                    @if (Auth::user()?->canEditQcMobile())
                        @if (!$isLocked)
                            <a href="{{ route('qc.inbound.edit', [$qc->qc_id, 'view' => 'mobile', 'ref' => 'index']) }}" class="qc-btn-action qc-btn-edit" title="Edit / Koreksi Parameter Mutu Sampling">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Edit</span>
                            </a>
                        @else
                            <button type="button" class="qc-btn-action qc-btn-locked" title="Tiket terkunci karena sudah diproses Gudang menjadi GRN #{{ $qc->terima->terima_no }}">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>Terkunci</span>
                            </button>
                        @endif
                    @endif

                    {{-- TOMBOL HAPUS DENGAN MODAL BAHAYA --}}
                    @if (Auth::user()?->canDeleteQcMobile() && !$isLocked)
                        <button type="button" class="qc-btn-action qc-btn-delete" onclick="openDeleteQcModal('{{ $qc->qc_id }}', '{{ $qc->qc_no }}')" title="Batalkan Tiket QC">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="qc-empty-state">
                <div class="qc-empty-icon">🔬</div>
                <div class="qc-empty-title">Belum Ada Tiket QC Inbound</div>
                <div class="qc-empty-desc">
                    Silakan klik tombol <strong>"+ QC 1"</strong> di atas untuk memulai pencatatan inspeksi kedatangan bahan baku.
                </div>
            </div>
        @endforelse
    </div>

    {{-- MODERN TOUCH-FRIENDLY MOBILE PAGINATION --}}
    @if ($inspeksiList->hasPages())
        @php
            $paginator = $inspeksiList->withQueryString();
        @endphp
        <div class="qc-pagination-wrap">
            <div class="qc-pagination-info">
                Menampilkan <strong>{{ $paginator->firstItem() }}</strong> - <strong>{{ $paginator->lastItem() }}</strong> dari <strong>{{ $paginator->total() }}</strong> tiket
            </div>

            <div class="qc-pagination-controls">
                {{-- Tombol Sebelumnya --}}
                @if ($paginator->onFirstPage())
                    <span class="qc-page-btn disabled">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        <span>Sebelumnya</span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="qc-page-btn">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        <span>Sebelumnya</span>
                    </a>
                @endif

                {{-- Page Number Pills --}}
                <div class="qc-page-numbers">
                    @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 1), min($paginator->lastPage(), $paginator->currentPage() + 1)) as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="qc-page-num active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="qc-page-num">{{ $page }}</a>
                        @endif
                    @endforeach
                </div>

                {{-- Tombol Selanjutnya --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="qc-page-btn">
                        <span>Selanjutnya</span>
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <span class="qc-page-btn disabled">
                        <span>Selanjutnya</span>
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </span>
                @endif
            </div>
        </div>
    @endif
</div>

{{-- MODAL KONFIRMASI HAPUS --}}
@include('gudang.qc.partials.common.modal-delete')

{{-- MODAL UJI GORENG FRYER --}}
@include('gudang.qc.partials.singkong.modal-uji-fryer')

@endsection

@push('scripts')
    <script src="{{ asset('js/gudang/qc/mobile/qc-index.js') }}"></script>
@endpush
