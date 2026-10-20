@extends('layouts.app')

@section('title', 'Catatan Laporan Operator Packaging (Shopfloor) - PT Mirasa Food Industry')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/packaging/catatan-operator.css') }}">
@endpush

@section('content')
<div class="catatan-operator-container">
    {{-- Header & Sub-Navigasi --}}
    <div style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <h1 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
                Catatan Laporan Operator Packaging
            </h1>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.825rem; color: #64748b;">
                Tiket catatan pengemasan lapangan dari ponsel operator (pengganti Google Form) &amp; status integrasi ke HPP FG.
            </p>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('produksi.packaging.index') }}" 
               class="btn btn-secondary" 
               style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #334155; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; display: inline-flex; align-items: center; gap: 0.35rem;"
               title="Kembali ke Dokumen HPP FG">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Dokumen HPP FG</span>
            </a>

            @if(Auth::user()->canCreateProduksi())
                <a href="{{ route('produksi.packaging.create') }}" 
                   class="btn btn-primary" 
                   style="background: #059669; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 2px 4px rgba(5, 150, 105, 0.2);">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Catat Pengemasan Baru</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Sub Nav Tabs --}}
    <div class="co-tab-nav">
        <a href="{{ route('produksi.packaging.index') }}" class="co-tab-item">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Dokumen HPP FG</span>
        </a>
        <a href="{{ route('produksi.packaging.catatan-operator') }}" class="co-tab-item active">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            <span>Catatan Operator (HP)</span>
            @if(!empty($unprocessedCount) && $unprocessedCount > 0)
                <span class="co-tab-badge">{{ $unprocessedCount }}</span>
            @endif
        </a>
        <a href="{{ route('produksi.packaging.rekap') }}" class="co-tab-item">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <span>Buku Rekap HPP Kemas</span>
        </a>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; color: #065f46; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600;">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- 4 Kartu Metrik --}}
    <div class="co-stat-grid">
        <div class="co-stat-card">
            <div>
                <span class="co-stat-label">Total Laporan</span>
                <div class="co-stat-val">{{ number_format($metrics['total_laporan'] ?? 0, 0, ',', '.') }}</div>
                <div class="co-stat-sub">Semua tiket operator</div>
            </div>
            <div class="co-stat-icon" style="background: #e0f2fe; color: #0284c7;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>

        <div class="co-stat-card">
            <div>
                <span class="co-stat-label" style="color: #b91c1c;">Menunggu Office</span>
                <div class="co-stat-val" style="color: #dc2626;">{{ number_format($metrics['menunggu_office'] ?? 0, 0, ',', '.') }}</div>
                <div class="co-stat-sub">Belum ditarik ke HPP FG</div>
            </div>
            <div class="co-stat-icon" style="background: #fee2e2; color: #dc2626;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="co-stat-card">
            <div>
                <span class="co-stat-label" style="color: #15803d;">Sudah Jadi HPP FG</span>
                <div class="co-stat-val" style="color: #059669;">{{ number_format($metrics['sudah_proses'] ?? 0, 0, ',', '.') }}</div>
                <div class="co-stat-sub">Selesai terintegrasi</div>
            </div>
            <div class="co-stat-icon" style="background: #dcfce7; color: #059669;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
        </div>

        <div class="co-stat-card">
            <div>
                <span class="co-stat-label">Total Hasil Kemas</span>
                <div class="co-stat-val">{{ number_format($metrics['total_karton'] ?? 0, 0, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">Karton</span></div>
                <div class="co-stat-sub">Output jadi terdata</div>
            </div>
            <div class="co-stat-icon" style="background: #fef3c7; color: #d97706;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="co-filter-card">
        <form method="GET" action="{{ route('produksi.packaging.catatan-operator') }}" class="co-filter-grid">
            <div>
                <label style="display: block; font-size: 0.725rem; font-weight: 700; color: #64748b; margin-bottom: 0.25rem;">Cari Laporan / Operator</label>
                <input type="text" name="search" class="co-form-control" placeholder="No. LKO, Nama Leader, Catatan..." value="{{ $filters['search'] ?? '' }}">
            </div>

            <div>
                <label style="display: block; font-size: 0.725rem; font-weight: 700; color: #64748b; margin-bottom: 0.25rem;">Tanggal Kemas</label>
                <input type="date" name="tgl_kemas" class="co-form-control" value="{{ $filters['tgl_kemas'] ?? '' }}">
            </div>

            <div>
                <label style="display: block; font-size: 0.725rem; font-weight: 700; color: #64748b; margin-bottom: 0.25rem;">Shift</label>
                <select name="shift" class="co-form-control">
                    <option value="">Semua Shift</option>
                    <option value="1" {{ ($filters['shift'] ?? '') == '1' ? 'selected' : '' }}>Shift 1</option>
                    <option value="2" {{ ($filters['shift'] ?? '') == '2' ? 'selected' : '' }}>Shift 2</option>
                    <option value="3" {{ ($filters['shift'] ?? '') == '3' ? 'selected' : '' }}>Shift 3</option>
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.725rem; font-weight: 700; color: #64748b; margin-bottom: 0.25rem;">Status Integrasi</label>
                <select name="status_cd" class="co-form-control">
                    <option value="">Semua Status</option>
                    <option value="SUBMITTED" {{ ($filters['status_cd'] ?? '') == 'SUBMITTED' ? 'selected' : '' }}>Menunggu Office</option>
                    <option value="PROCESSED" {{ ($filters['status_cd'] ?? '') == 'PROCESSED' ? 'selected' : '' }}>Sudah Jadi HPP FG</option>
                </select>
            </div>

            <div>
                <label style="display: block; font-size: 0.725rem; font-weight: 700; color: #64748b; margin-bottom: 0.25rem;">Produk Jadi</label>
                <select name="barang_jadi_id" class="co-form-control">
                    <option value="">Semua Produk</option>
                    @foreach ($barangJadiList as $b)
                        <option value="{{ $b->barang_id }}" {{ ($filters['barang_jadi_id'] ?? '') == $b->barang_id ? 'selected' : '' }}>
                            {{ $b->barang_nm }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; gap: 0.35rem;">
                <button type="submit" class="btn btn-primary" style="height: 38px; padding: 0 0.85rem; font-size: 0.8125rem; font-weight: 700;">Filter</button>
                <a href="{{ route('produksi.packaging.catatan-operator') }}" class="btn btn-secondary" style="height: 38px; padding: 0 0.75rem; font-size: 0.8125rem; display: inline-flex; align-items: center;" title="Reset Filter">Reset</a>
            </div>
        </form>
    </div>

    {{-- Tabel Catatan Operator --}}
    <div class="co-table-card">
        <table class="co-table">
            <thead>
                <tr>
                    <th style="width: 140px;">No. Tiket &amp; Tgl</th>
                    <th style="width: 150px;">Leader &amp; Shift</th>
                    <th>Produk Jadi (FG)</th>
                    <th style="text-align: right; width: 110px;">Output FG</th>
                    <th>Timbangan Lakban</th>
                    <th>Expired &amp; Tambahan</th>
                    <th style="width: 130px;">Status</th>
                    <th style="text-align: center; width: 90px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dataList as $row)
                    @php 
                        $badge = $row->status_badge;
                        $menuId = 'menu_lko_' . $row->laporan_id;
                    @endphp
                    <tr>
                        <td>
                            <strong style="color: #0f172a; font-family: ui-monospace, SFMono-Regular, monospace; font-size: 0.85rem;">
                                {{ $row->laporan_no }}
                            </strong>
                            <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                📅 {{ $row->tgl_kemas->format('d/m/Y') }}
                            </div>
                        </td>

                        <td>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span class="co-chip co-chip-amber">Shift {{ $row->shift }}</span>
                                <strong style="color: #0f172a;">{{ $row->operator_nama }}</strong>
                            </div>
                            <div style="font-size: 0.7rem; color: #64748b; margin-top: 2px;">
                                {{ $row->jumlah_personil }} Org &bull; {{ $row->jumlah_mesin }} Mesin
                            </div>
                        </td>

                        <td>
                            <div style="font-weight: 700; color: #0f172a;">
                                {{ $row->barangJadi->barang_nm ?? '-' }}
                            </div>
                            <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                @if ($row->wip_qty_kg > 0)
                                    <span>Curah: <strong style="color: #059669;">{{ number_format($row->wip_qty_kg, 1, ',', '.') }} {{ $row->wip_satuan ?: 'Kg' }}</strong></span>
                                @endif
                                @if ($row->bumbuBarang)
                                    <span>&bull; Bumbu: {{ number_format($row->bumbu_qty_kg, 1, ',', '.') }} Kg</span>
                                @endif
                            </div>
                        </td>

                        <td style="text-align: right;">
                            <strong style="font-size: 1rem; font-weight: 900; color: #059669; font-family: ui-monospace, SFMono-Regular, monospace;">
                                {{ number_format($row->hasil_karton_qty, 0, ',', '.') }}
                            </strong>
                            <div style="font-size: 0.7rem; color: #64748b;">
                                {{ $row->satuan_hasil }}
                                @if ($row->hasil_pack_qty > 0)
                                    + {{ number_format($row->hasil_pack_qty, 0, ',', '.') }} Pack
                                @endif
                            </div>
                        </td>

                        <td>
                            @if ($row->lakban_atas_gr > 0 || $row->lakban_bawah_gr > 0)
                                <div style="font-size: 0.75rem;">
                                    Atas: <strong>{{ number_format($row->lakban_atas_gr, 0, ',', '.') }}g</strong> &bull; Bwh: <strong>{{ number_format($row->lakban_bawah_gr, 0, ',', '.') }}g</strong>
                                </div>
                                <div style="font-size: 0.7rem; font-weight: 800; color: #dc2626;">
                                    Total: {{ number_format($row->lakban_qty, 0, ',', '.') }} Gram
                                </div>
                            @elseif ($row->lakban_qty > 0)
                                <span class="co-chip">{{ number_format($row->lakban_qty, 1, ',', '.') }} Gram</span>
                            @else
                                <span style="color: #94a3b8; font-size: 0.75rem;">-</span>
                            @endif
                        </td>

                        <td>
                            <div style="display: flex; flex-direction: column; gap: 3px;">
                                @if ($row->expired_qty > 0)
                                    <span class="co-chip co-chip-red">
                                        Exp: {{ number_format($row->expired_qty, 0, ',', '.') }} Lbr
                                    </span>
                                @endif

                                @if (!empty($row->kemasan_tambahan_items) && count($row->kemasan_tambahan_items) > 0)
                                    <div style="font-size: 0.7rem; color: #475569;">
                                        {{ count($row->kemasan_tambahan_items) }} Kemasan Tambahan
                                    </div>
                                @elseif ($row->kemasan_lain_qty > 0)
                                    <span class="co-chip">
                                        {{ $row->kemasanLainBarang?->barang_nm ?? 'Tambahan' }}: {{ number_format($row->kemasan_lain_qty, 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 999px; font-size: 0.7rem; font-weight: 800; background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; border: 1px solid {{ $badge['border'] }};">
                                <span>{{ $badge['icon'] }}</span>
                                <span>{{ $badge['label'] }}</span>
                            </span>
                            @if ($row->packagingHdr)
                                <div style="font-size: 0.675rem; margin-top: 3px;">
                                    <a href="{{ route('produksi.packaging.show', $row->pack_id) }}" style="color: #0284c7; text-decoration: none; font-weight: 700;">
                                        Dok: {{ $row->packagingHdr->pack_no }} &rarr;
                                    </a>
                                </div>
                            @endif
                        </td>

                        <td style="text-align: center;">
                            {{-- Smart Action Dropdown Wajib AGENTS.md --}}
                            <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                <span>Aksi</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div id="{{ $menuId }}" class="action-dropdown-menu">
                                <a href="{{ route('produksi.packaging.catatan-operator.show', $row->laporan_id) }}" class="action-dropdown-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Lihat Rincian Catatan</span>
                                </a>

                                @if ($row->status_cd === 'SUBMITTED' && Auth::user()->canCreateProduksi())
                                    <a href="{{ route('produksi.packaging.create', ['laporan_id' => $row->laporan_id]) }}" class="action-dropdown-item" style="color: #059669; font-weight: 700;">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                        <span>Proses Jadi HPP FG</span>
                                    </a>
                                @endif

                                @if ($row->pack_id)
                                    <a href="{{ route('produksi.packaging.show', $row->pack_id) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        <span>Lihat Dokumen HPP FG</span>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: #64748b;">
                            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📋</div>
                            <strong style="color: #1e293b; font-size: 0.95rem; display: block;">Belum Ada Catatan Laporan Operator</strong>
                            <p style="font-size: 0.8125rem; margin: 0.25rem 0 1rem 0;">Belum ada data tiket pengemasan yang dikirim dari HP operator pada filter ini.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if ($dataList->hasPages())
        <div style="margin-top: 1.25rem;">
            {{ $dataList->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/produksi/packaging/catatan-operator.js') }}"></script>
@endpush
