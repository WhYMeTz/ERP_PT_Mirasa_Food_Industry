@extends('layouts.app')

@section('title', 'Pemeriksaan Mutu Bahan Masuk (QC Inbound) - PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/qc/qc-admin-index.css') }}">
@endpush

@section('content')
<div class="qc-admin-container">
    {{-- HEADER HALAMAN & ACTIONS --}}
    <div class="qc-admin-header">
        <div>
            <div class="qc-admin-badge-sub">
                <span>🔬 Quality Control &amp; Penerimaan Bahan</span>
            </div>
            <h1 class="qc-admin-title">
                <span>Pemeriksaan Mutu Kedatangan (QC Inbound)</span>
            </h1>
            <p class="qc-admin-subtitle">
                Monitoring hasil sampling kedatangan bahan baku, uji lab fisik &amp; fryer, serta serah terima ke Gudang Pabrik.
            </p>
        </div>

        @if (Auth::user()->isSuperAdmin())
            <div class="qc-admin-header-actions">
                {{-- SHORTCUT BERALIH KE MODE HP MOBILE FEED (KHUSUS SUPERADMIN) --}}
                <a href="{{ route('qc.inbound.index', ['view' => 'mobile']) }}" class="btn-qc-switch-mobile" title="Buka Tampilan Khusus Smartphone Lapangan">
                    <span>📱 Mode Mobile QC Lapangan</span>
                </a>
            </div>
        @endif
    </div>

    {{-- KARTU METRIK STATISTIK OPERASIONAL --}}
    <div class="qc-stat-grid">
        <div class="qc-stat-card siap">
            <div>
                <div class="qc-stat-title">Siap Ditarik ke GRN</div>
                <div class="qc-stat-val">{{ number_format($countSiap ?? 0, 0, ',', '.') }}</div>
                <div class="qc-stat-desc">Lolos uji sampling &amp; siap bongkar gudang</div>
            </div>
            <div class="qc-stat-icon">⏳</div>
        </div>

        <div class="qc-stat-card fryer">
            <div>
                <div class="qc-stat-title">Menunggu Uji Fryer</div>
                <div class="qc-stat-val">{{ number_format($countFryer ?? 0, 0, ',', '.') }}</div>
                <div class="qc-stat-desc">Singkong perlu hasil tes lab penggorengan</div>
            </div>
            <div class="qc-stat-icon">🍟</div>
        </div>

        <div class="qc-stat-card selesai">
            <div>
                <div class="qc-stat-title">Sudah Masuk Gudang</div>
                <div class="qc-stat-val">{{ number_format($countSelesai ?? 0, 0, ',', '.') }}</div>
                <div class="qc-stat-desc">Telah terbit Bukti Penerimaan Barang (GRN)</div>
            </div>
            <div class="qc-stat-icon">✅</div>
        </div>

        <div class="qc-stat-card reject">
            <div>
                <div class="qc-stat-title">Ditolak QC / Reject</div>
                <div class="qc-stat-val">{{ number_format($countReject ?? 0, 0, ',', '.') }}</div>
                <div class="qc-stat-desc">Tidak memenuhi parameter standar mutu</div>
            </div>
            <div class="qc-stat-icon">❌</div>
        </div>
    </div>

    {{-- FILTER & PENCARIAN WEB ADMIN --}}
    <div class="qc-filter-card">
        <form action="{{ route('qc.inbound.index') }}" method="GET" class="qc-filter-grid">
            <input type="hidden" name="view" value="desktop">

            <div class="qc-form-group">
                <label class="qc-form-label">Pencarian Cepat</label>
                <input type="text" name="search" class="qc-form-input" placeholder="No. QC, Surat Jalan, Plat Truk, Sopir..." value="{{ $filters['search'] ?? '' }}">
            </div>

            <div class="qc-form-group">
                <label class="qc-form-label">Komoditas</label>
                <select name="kategori_barang" class="qc-form-select">
                    <option value="">-- Semua Komoditas --</option>
                    <option value="SINGKONG" {{ ($filters['kategori_barang'] ?? '') === 'SINGKONG' ? 'selected' : '' }}>🥔 Singkong</option>
                    <option value="MINYAK" {{ ($filters['kategori_barang'] ?? '') === 'MINYAK' ? 'selected' : '' }}>🛢️ Minyak Goreng</option>
                    <option value="PLASTIK" {{ ($filters['kategori_barang'] ?? '') === 'PLASTIK' ? 'selected' : '' }}>🛍️ Plastik</option>
                    <option value="KARTON" {{ ($filters['kategori_barang'] ?? '') === 'KARTON' ? 'selected' : '' }}>📦 Karton</option>
                    <option value="MSG" {{ ($filters['kategori_barang'] ?? '') === 'MSG' ? 'selected' : '' }}>🧂 MSG</option>
                    <option value="GARAM" {{ ($filters['kategori_barang'] ?? '') === 'GARAM' ? 'selected' : '' }}>🧂 Garam</option>
                    <option value="PERENYAH" {{ ($filters['kategori_barang'] ?? '') === 'PERENYAH' ? 'selected' : '' }}>✨ Perenyah</option>
                </select>
            </div>

            <div class="qc-form-group">
                <label class="qc-form-label">Status Antrean</label>
                <select name="status_qc" class="qc-form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="SIAP_GUDANG" {{ ($filters['status_qc'] ?? '') === 'SIAP_GUDANG' ? 'selected' : '' }}>⏳ Siap Gudang</option>
                    <option value="DITERIMA_GUDANG" {{ ($filters['status_qc'] ?? '') === 'DITERIMA_GUDANG' ? 'selected' : '' }}>✅ Selesai Diterima (GRN)</option>
                    <option value="DITOLAK_TOTAL" {{ ($filters['status_qc'] ?? '') === 'DITOLAK_TOTAL' ? 'selected' : '' }}>❌ Ditolak QC (Reject)</option>
                </select>
            </div>

            <div class="qc-form-group">
                <label class="qc-form-label">Mitra Supplier</label>
                <select name="supplier_id" class="qc-form-select">
                    <option value="">-- Semua Supplier --</option>
                    @foreach ($suppliers as $s)
                        <option value="{{ $s->supplier_id }}" {{ ($filters['supplier_id'] ?? '') == $s->supplier_id ? 'selected' : '' }}>
                            {{ $s->supplier_nm }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="qc-filter-actions">
                <button type="submit" class="btn-filter-submit">Filter</button>
                <a href="{{ route('qc.inbound.index', ['view' => 'desktop']) }}" class="btn-filter-reset">Reset</a>
            </div>
        </form>
    </div>

    {{-- TABEL DATA OPERASIONAL INBOUND QC --}}
    <div class="qc-table-card">
        <div class="qc-table-responsive">
            <table class="qc-admin-table">
                <thead>
                    <tr>
                        <th style="width: 170px;">No. Tiket QC &amp; Waktu</th>
                        <th style="width: 110px;">Komoditas</th>
                        <th>Supplier &amp; PO</th>
                        <th>Armada / Sopir</th>
                        <th style="text-align: right;">Gross (kg)</th>
                        <th style="text-align: right;">Refraksi</th>
                        <th style="text-align: right;">Reject</th>
                        <th style="text-align: right; color: #047857;">Netto Lolos (kg)</th>
                        <th style="text-align: center;">Status Mutu &amp; Lab</th>
                        <th style="text-align: center;">Status Gudang</th>
                        <th style="text-align: center; width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inspeksiList as $qc)
                        @php
                            $totalGross = $qc->details->sum('qty_timbang_gross');
                            $totalRefraksi = $qc->details->sum('qty_refraksi');
                            $totalReject = $qc->details->sum('qty_reject');
                            $totalNetto = $qc->details->sum('qty_netto_lolos');
                            $kat = strtoupper((string) ($qc->kategori_barang ?: 'SINGKONG'));
                            $isLocked = !empty($qc->terima) && !Auth::user()?->isSuperAdmin() && !Auth::user()?->isGudang();
                            $menuId = 'actionMenuQc_' . $qc->qc_id;
                        @endphp
                        <tr>
                            {{-- 1. NO QC & WAKTU --}}
                            <td>
                                <a href="{{ route('qc.inbound.show', $qc->qc_id) }}" style="font-weight: 800; color: #0284c7; text-decoration: none;">
                                    {{ $qc->qc_no }}
                                </a>
                                <div style="font-size: 0.72rem; color: #64748b; margin-top: 2px;">
                                    📅 {{ $qc->tgl_periksa ? $qc->tgl_periksa->format('d/m/Y H:i') : '-' }}
                                </div>
                                <div style="font-size: 0.7rem; color: #94a3b8;">
                                    QC: {{ $qc->petugas_qc_nama ?: 'Petugas' }}
                                </div>
                            </td>

                            {{-- 2. KOMODITAS --}}
                            <td>
                                <span class="badge-qc-commodity">
                                    @if ($kat === 'SINGKONG') 🥔 @elseif ($kat === 'MINYAK') 🛢️ @elseif ($kat === 'PLASTIK') 🛍️ @elseif ($kat === 'KARTON') 📦 @else ✨ @endif
                                    {{ $kat }}
                                </span>
                            </td>

                            {{-- 3. SUPPLIER & PO --}}
                            <td>
                                <div style="font-weight: 700; color: #0f172a;">
                                    {{ $qc->supplier?->supplier_nm ?? 'Supplier Langsung' }}
                                </div>
                                @if ($qc->po)
                                    <div style="font-size: 0.72rem; color: #0284c7; font-weight: 700;">
                                        PO: {{ $qc->po->po_no }}
                                    </div>
                                @else
                                    <div style="font-size: 0.7rem; color: #94a3b8;">(Non-PO)</div>
                                @endif
                            </td>

                            {{-- 4. ARMADA / SOPIR --}}
                            <td>
                                <div style="font-weight: 700; color: #334155;">
                                    🚛 {{ $qc->plat_nomor_truk ?: '-' }}
                                </div>
                                <div style="font-size: 0.72rem; color: #64748b;">
                                    Sopir: {{ $qc->sopir_nama ?: '-' }}
                                </div>
                                @if ($qc->nomor_do)
                                    <div style="font-size: 0.7rem; color: #64748b;">
                                        DO: {{ $qc->nomor_do }}
                                    </div>
                                @endif
                            </td>

                            {{-- 5. GROSS --}}
                            <td style="text-align: right; font-weight: 700;">
                                {{ number_format($totalGross, 0, ',', '.') }}
                            </td>

                            {{-- 6. REFRAKSI --}}
                            <td style="text-align: right; color: #d97706; font-weight: 600;">
                                {{ number_format($totalRefraksi, 0, ',', '.') }}
                            </td>

                            {{-- 7. REJECT --}}
                            <td style="text-align: right; color: #dc2626; font-weight: 600;">
                                {{ number_format($totalReject, 0, ',', '.') }}
                            </td>

                            {{-- 8. NETTO LOLOS --}}
                            <td style="text-align: right; font-weight: 900; color: #059669; font-size: 0.925rem;">
                                {{ number_format($totalNetto, 0, ',', '.') }}
                            </td>

                            {{-- 9. STATUS MUTU & LAB --}}
                            <td style="text-align: center;">
                                @if ($qc->status_qc === 'SIAP_GUDANG')
                                    <span class="badge-qc-status siap">⏳ Lolos / Siap</span>
                                @elseif ($qc->status_qc === 'DITERIMA_GUDANG')
                                    <span class="badge-qc-status masuk">✅ Diterima</span>
                                @elseif ($qc->status_qc === 'DITOLAK_TOTAL')
                                    <span class="badge-qc-status reject">❌ Ditolak</span>
                                @else
                                    <span class="badge-qc-status siap">{{ $qc->status_qc }}</span>
                                @endif

                                @if ($kat === 'SINGKONG')
                                    @if ($qc->status_uji_goreng === 'MENUNGGU_LAB')
                                        <div>
                                            <span class="badge-lab-warning">🍟 Uji Fryer Pending</span>
                                        </div>
                                    @elseif ($qc->status_uji_goreng === 'SELESAI')
                                        <div>
                                            <span style="font-size: 0.68rem; color: #166534; font-weight: 700;">🍟 Fryer Selesai</span>
                                        </div>
                                    @endif
                                @endif
                            </td>

                            {{-- 10. STATUS GUDANG --}}
                            <td style="text-align: center;">
                                @if ($qc->terima)
                                    <a href="{{ route('gudang.terima.show', $qc->terima->terima_id) }}" class="badge-grn-linked" title="Lihat Penerimaan Barang (GRN)">
                                        <span>📦 GRN #{{ $qc->terima->terima_no }}</span>
                                    </a>
                                @elseif ($qc->status_qc === 'SIAP_GUDANG')
                                    <span style="font-size: 0.72rem; color: #d97706; font-weight: 700;">
                                        ⏳ Belum Ditarik
                                    </span>
                                @else
                                    <span style="font-size: 0.72rem; color: #94a3b8;">-</span>
                                @endif
                            </td>

                            {{-- 11. SMART ACTION DROPDOWN --}}
                            <td style="text-align: center; vertical-align: middle;">
                                <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                {{-- FIXED DROPDOWN CONTAINER --}}
                                <div id="{{ $menuId }}" class="action-dropdown-menu">
                                    <a href="{{ route('qc.inbound.show', $qc->qc_id) }}" class="action-dropdown-item primary">
                                        <span>📄</span> <span>Dokumen HACCP &amp; Cetak A4</span>
                                    </a>

                                    {{-- OPSI TARIK KE GRN JIKA BELUM DITERIMA --}}
                                    @if ($qc->status_qc === 'SIAP_GUDANG' && Auth::user()?->canAccessTerima())
                                        <a href="{{ route('gudang.terima.create', ['qc_id' => $qc->qc_id]) }}" class="action-dropdown-item success">
                                            <span>📦</span> <span>Tarik ke GRN Gudang</span>
                                        </a>
                                    @endif

                                    {{-- OPSI CEPAT UJI FRYER LAB SINGKONG --}}
                                    @if ($kat === 'SINGKONG' && $qc->status_uji_goreng === 'MENUNGGU_LAB')
                                        <button type="button" class="action-dropdown-item warning" onclick="openModalUjiFryer('{{ $qc->qc_id }}', '{{ $qc->qc_no }}', '{{ $qc->details->first()?->qcdtl_id }}')">
                                            <span>🍟</span> <span>Lengkapi Uji Fryer</span>
                                        </button>
                                    @endif

                                    {{-- CETAK LANGSUNG FORMAT A4 DOKUMEN FISIK --}}
                                    <a href="{{ route('gudang.qc.haccp_cetak', $qc->qc_id) }}" target="_blank" class="action-dropdown-item">
                                        <span>🖨️</span> <span>Cetak Langsung (A4)</span>
                                    </a>

                                    <div class="action-dropdown-divider"></div>

                                    {{-- EDIT SELURUH DOKUMEN HACCP --}}
                                    @if (Auth::user()?->canEditQc())
                                        @if (!$isLocked)
                                            <a href="{{ route('qc.inbound.edit', $qc->qc_id) }}" class="action-dropdown-item">
                                                <span>✏️</span> <span>Edit Seluruh Dokumen HACCP</span>
                                            </a>
                                        @else
                                            <span class="action-dropdown-item" style="color: #94a3b8; cursor: not-allowed;" title="Terkunci karena sudah dibuatkan GRN">
                                                <span>🔒</span> <span>Terkunci (GRN Ada)</span>
                                            </span>
                                        @endif
                                    @endif

                                    {{-- HAPUS / BATALKAN TIKET QC --}}
                                    @if (Auth::user()?->canDeleteQc() && !$isLocked)
                                        <button type="button" class="action-dropdown-item danger" onclick="openDeleteQcModal('{{ $qc->qc_id }}', '{{ $qc->qc_no }}')">
                                            <span>🗑️</span> <span>Batalkan Tiket QC</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="padding: 3.5rem 1.5rem; text-align: center; color: #64748b;">
                                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🔬</div>
                                <div style="font-weight: 700; font-size: 1rem; color: #0f172a;">Belum Ada Tiket QC Inbound</div>
                                <div style="font-size: 0.85rem; margin-top: 0.25rem;">
                                    Belum ada data kedatangan bahan baku yang dicatat atau sesuai kriteria filter di atas.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($inspeksiList->hasPages())
            <div style="padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9; background: #ffffff;">
                {{ $inspeksiList->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

{{-- MODAL KONFIRMASI HAPUS --}}
@include('gudang.qc.partials.modal-delete')

{{-- MODAL UJI GORENG FRYER --}}
@include('gudang.qc.partials.modal-uji-fryer')

@endsection

@push('scripts')
    <script src="{{ asset('js/gudang/qc/qc-admin-index.js') }}"></script>
@endpush
