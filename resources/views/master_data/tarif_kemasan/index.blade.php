@extends('layouts.app')

@section('title', 'Master Standar Harga Kemasan & Packaging - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/master/tarif_kemasan/tarif-kemasan-index.css') }}?v={{ filemtime(public_path('css/master/tarif_kemasan/tarif-kemasan-index.css')) }}">
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">Master Standar Harga Kemasan &amp; Packaging</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
            Konfigurasi standar acuan harga bahan kemasan (Karton XX/MX, Roll Plastik, Lakban per gram, Bumbu, dll.) untuk perhitungan HPP Pengemasan Ping-Ping &amp; Maksi.
        </p>
    </div>

    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <button type="button" onclick="openQuickUpdateModal()" class="btn btn-primary" style="background: #059669; border-color: #059669; color: #ffffff; font-weight: 700; font-size: 0.825rem; padding: 0.45rem 1rem; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 1px 2px rgba(5, 150, 105, 0.2);">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            <span>Perbarui Cepat (Matrix Excel)</span>
        </button>
    </div>
</div>

@if(session('toast_success'))
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; color: #065f46; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>{{ session('toast_success') }}</span>
    </div>
@endif

{{-- BANNER STANDAR ACUAN EXCEL PABRIK --}}
<div class="excel-benchmark-banner">
    <div class="benchmark-left">
        <div class="benchmark-icon">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div>
            <h4 class="benchmark-title">Sinkron dengan Lembar Kerja Excel PT Mirasa Food Industry</h4>
            <p class="benchmark-desc">
                Daftar harga ini digunakan langsung sebagai standar perhitungan biaya bahan pada modul <strong>Pengemasan Barang Jadi &amp; Buku Rekap HPP Packaging</strong>.
            </p>
        </div>
    </div>
    <div>
        <a href="{{ route('produksi.packaging.rekap') }}" class="btn btn-secondary" style="font-size: 0.775rem; font-weight: 700; background: #ffffff; color: #15803d; border-color: #86efac; padding: 0.35rem 0.75rem;">
            Lihat Rekap HPP Packaging &rarr;
        </a>
    </div>
</div>

{{-- 6 KARTU STATISTIK KEMASAN --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <span class="stat-label">Total Komponen</span>
            <div class="stat-value">{{ $stats['total_komponen'] }} Item</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
        <div>
            <span class="stat-label">Karton Box</span>
            <div class="stat-value">{{ $stats['total_karton'] }} Item</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon emerald">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7c0-2-1.5-3-3.5-3h-9C5.5 4 4 5 4 7z"/></svg>
        </div>
        <div>
            <span class="stat-label">Roll Plastik</span>
            <div class="stat-value">{{ $stats['total_roll'] }} Item</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon cyan">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
        </div>
        <div>
            <span class="stat-label">Lakban Dus</span>
            <div class="stat-value">{{ $stats['total_lakban'] }} Item</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon rose">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
        </div>
        <div>
            <span class="stat-label">Bumbu Balado</span>
            <div class="stat-value">{{ $stats['total_bumbu'] }} Item</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
        </div>
        <div>
            <span class="stat-label">Curah WIP</span>
            <div class="stat-value">{{ $stats['total_wip'] }} Item</div>
        </div>
    </div>
</div>

{{-- TAB KATEGORI & PENCARIAN --}}
<div class="category-tabs">
    <a href="{{ route('master.tarif_kemasan.index', ['search' => $search]) }}" class="category-tab {{ empty($kategori) ? 'active' : '' }}">
        Semua Kategori ({{ $stats['total_komponen'] }})
    </a>
    <a href="{{ route('master.tarif_kemasan.index', ['kategori' => 'KARTON', 'search' => $search]) }}" class="category-tab {{ $kategori === 'KARTON' ? 'active' : '' }}">
        Karton Box ({{ $stats['total_karton'] }})
    </a>
    <a href="{{ route('master.tarif_kemasan.index', ['kategori' => 'ROLL', 'search' => $search]) }}" class="category-tab {{ $kategori === 'ROLL' ? 'active' : '' }}">
        Roll Plastik ({{ $stats['total_roll'] }})
    </a>
    <a href="{{ route('master.tarif_kemasan.index', ['kategori' => 'LAKBAN', 'search' => $search]) }}" class="category-tab {{ $kategori === 'LAKBAN' ? 'active' : '' }}">
        Lakban Dus ({{ $stats['total_lakban'] }})
    </a>
    <a href="{{ route('master.tarif_kemasan.index', ['kategori' => 'BUMBU', 'search' => $search]) }}" class="category-tab {{ $kategori === 'BUMBU' ? 'active' : '' }}">
        Bumbu Balado ({{ $stats['total_bumbu'] }})
    </a>
    <a href="{{ route('master.tarif_kemasan.index', ['kategori' => 'WIP_CURAH', 'search' => $search]) }}" class="category-tab {{ $kategori === 'WIP_CURAH' ? 'active' : '' }}">
        Bahan Curah WIP ({{ $stats['total_wip'] }})
    </a>
</div>

{{-- TABEL UTAMA STANDAR HARGA KEMASAN --}}
<div class="card" style="border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); overflow: hidden;">
    <div class="card-header" style="background: #ffffff; padding: 0.85rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <form action="{{ route('master.tarif_kemasan.index') }}" method="GET" style="display: flex; gap: 0.5rem; max-width: 400px; width: 100%;">
            @if(!empty($kategori))
                <input type="hidden" name="kategori" value="{{ $kategori }}">
            @endif
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama komponen atau kode acuan..." class="form-control" style="padding: 0.45rem 0.75rem; font-size: 0.825rem;">
            <button type="submit" class="btn btn-secondary" style="font-size: 0.825rem; padding: 0.45rem 0.85rem;">Cari</button>
            @if(!empty($search))
                <a href="{{ route('master.tarif_kemasan.index', ['kategori' => $kategori]) }}" class="btn btn-secondary" style="font-size: 0.825rem; padding: 0.45rem 0.65rem;" title="Reset">&times;</a>
            @endif
        </form>
        <span style="color: #64748b; font-size: 0.825rem;">Total Data Tampil: <strong>{{ $tarifs->count() }} Komponen</strong></span>
    </div>

    <div style="overflow-x: auto;">
        <table class="table" style="width: 100%; margin-bottom: 0;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.03em; color: #475569;">
                    <th style="width: 45px; text-align: center; padding: 0.65rem 0.75rem;">No</th>
                    <th style="width: 120px; padding: 0.65rem 0.75rem;">Kategori</th>
                    <th style="width: 150px; padding: 0.65rem 0.75rem;">Kode Acuan</th>
                    <th style="padding: 0.65rem 0.75rem;">Nama Komponen Bahan Kemasan</th>
                    <th style="width: 180px; text-align: right; padding: 0.65rem 0.75rem;">Standar Nilai Acuan</th>
                    <th style="width: 140px; padding: 0.65rem 0.75rem;">Satuan Acuan</th>
                    <th style="width: 120px; text-align: center; padding: 0.65rem 0.75rem;">Berlaku Sejak</th>
                    <th style="padding: 0.65rem 0.75rem;">Penjelasan / Catatan Excel</th>
                    <th style="width: 100px; text-align: center; padding: 0.65rem 0.75rem;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tarifs as $index => $item)
                    @php
                        $katUpper = strtoupper($item->kategori_bahan ?? '');
                        $badgeClass = match($katUpper) {
                            'KARTON'    => 'badge-karton',
                            'ROLL'      => 'badge-roll',
                            'LAKBAN'    => 'badge-lakban',
                            'BUMBU'     => 'badge-bumbu',
                            'WIP_CURAH' => 'badge-wip',
                            default     => 'badge-lain'
                        };
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9; font-size: 0.825rem;">
                        <td style="text-align: center; color: #64748b; font-weight: 600; padding: 0.65rem 0.75rem;">
                            {{ $index + 1 }}
                        </td>
                        <td style="padding: 0.65rem 0.75rem;">
                            <span class="badge-category {{ $badgeClass }}">
                                {{ $item->kategori_bahan }}
                            </span>
                        </td>
                        <td style="padding: 0.65rem 0.75rem;">
                            <code style="font-size: 0.75rem; color: #0284c7; background: #f0f9ff; border: 1px solid #bae6fd; padding: 0.15rem 0.35rem; border-radius: 4px; font-weight: 700;">
                                {{ $item->kode_tarif }}
                            </code>
                        </td>
                        <td style="padding: 0.65rem 0.75rem;">
                            <strong style="color: #0f172a; font-size: 0.85rem;">{{ $item->nama_tarif }}</strong>
                        </td>
                        <td style="text-align: right; padding: 0.65rem 0.75rem;">
                            <div class="rate-value-box">
                                Rp {{ number_format($item->nilai_tarif, in_array($item->satuan_basis, ['PER_LEMBAR', 'PER_GRAM']) ? 2 : 0, ',', '.') }}
                            </div>
                            @if($item->nilai_tarif_sekunder !== null)
                                <div class="rate-sub-box" title="Alternatif / Konversi Satuan">
                                    Rp {{ number_format($item->nilai_tarif_sekunder, in_array($item->satuan_basis, ['PER_LEMBAR', 'PER_GRAM']) ? 2 : 0, ',', '.') }}
                                    <span style="font-weight: 400; color: #64748b;">{{ $item->satuan_sekunder_label }}</span>
                                </div>
                            @endif
                        </td>
                        <td style="padding: 0.65rem 0.75rem;">
                            <span style="color: #334155; font-size: 0.8rem; font-weight: 600;">
                                {{ $item->satuan_label }}
                            </span>
                        </td>
                        <td style="text-align: center; padding: 0.65rem 0.75rem; color: #64748b; font-size: 0.775rem;">
                            @if($item->berlaku_sejak)
                                <span style="font-family: monospace; font-weight: 600; color: #0369a1;">
                                    {{ \Carbon\Carbon::parse($item->berlaku_sejak)->format('d/m/Y') }}
                                </span>
                            @else
                                <span style="color: #94a3b8;">-</span>
                            @endif
                        </td>
                        <td style="padding: 0.65rem 0.75rem; color: #64748b; font-size: 0.8rem;">
                            {{ $item->keterangan ?? '-' }}
                        </td>
                        <td style="text-align: center; padding: 0.65rem 0.75rem;">
                            @php
                                $menuId = 'menuTarifKemasan' . $item->tarif_kemasan_id;
                            @endphp
                            <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                <span>Aksi</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div id="{{ $menuId }}" class="action-dropdown-menu">
                                <button type="button" class="action-dropdown-item" onclick="openEditTarifModal({{ json_encode($item) }})">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Ubah Standar Harga</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 2.5rem; color: #94a3b8;">
                            Tidak ada data standar harga kemasan yang sesuai pencarian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL UBAH TARIF TUNGGAL & QUICK MATRIX UPDATE --}}
@include('master_data.tarif_kemasan.partials.modal-edit-tarif-kemasan')
@include('master_data.tarif_kemasan.partials.modal-quick-update')
@endsection

@push('scripts')
    <script src="{{ asset('js/master/tarif_kemasan/tarif-kemasan-index.js') }}?v={{ filemtime(public_path('js/master/tarif_kemasan/tarif-kemasan-index.js')) }}"></script>
@endpush
