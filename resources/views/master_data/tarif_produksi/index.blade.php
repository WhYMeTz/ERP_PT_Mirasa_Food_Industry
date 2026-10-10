@extends('layouts.app')

@section('title', 'Master Standar Tarif Produksi & FOH - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/master/tarif_produksi/tarif-index.css') }}">
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Master Standar Tarif Produksi &amp; FOH Pabrik</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">
            Konfigurasi standar pengali Overhead Pabrik (FOH), acuan tarif flow gas alam (CNG), dan upah harian tenaga kerja tanpa perlu modifikasi kode program.
        </p>
    </div>
</div>

@if(session('toast_success'))
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; color: #065f46; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>{{ session('toast_success') }}</span>
    </div>
@endif

{{-- KARTU STATISTIK --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <span class="stat-label">Total Komponen</span>
            <div class="stat-value">{{ $stats['total_komponen'] }} Item</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon emerald">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        </div>
        <div>
            <span class="stat-label">Overhead Pabrik (FOH)</span>
            <div class="stat-value">{{ $stats['total_foh'] }} Tarif</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <div>
            <span class="stat-label">Energi &amp; Gas (CNG)</span>
            <div class="stat-value">{{ $stats['total_energi'] }} Tarif</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div>
            <span class="stat-label">Tenaga Kerja Harian</span>
            <div class="stat-value">{{ $stats['total_tk'] }} Tarif</div>
        </div>
    </div>
</div>

{{-- TAB KATEGORI & PENCARIAN --}}
<div class="category-tabs">
    <a href="{{ route('master.tarif_produksi.index', ['search' => $search]) }}" class="category-tab {{ empty($kategori) ? 'active' : '' }}">
        Semua Kategori ({{ $stats['total_komponen'] }})
    </a>
    <a href="{{ route('master.tarif_produksi.index', ['kategori' => 'FOH', 'search' => $search]) }}" class="category-tab {{ $kategori === 'FOH' ? 'active' : '' }}">
        Overhead Pabrik / FOH ({{ $stats['total_foh'] }})
    </a>
    <a href="{{ route('master.tarif_produksi.index', ['kategori' => 'ENERGI', 'search' => $search]) }}" class="category-tab {{ $kategori === 'ENERGI' ? 'active' : '' }}">
        Energi Gas CNG ({{ $stats['total_energi'] }})
    </a>
    <a href="{{ route('master.tarif_produksi.index', ['kategori' => 'TENAGA_KERJA', 'search' => $search]) }}" class="category-tab {{ $kategori === 'TENAGA_KERJA' ? 'active' : '' }}">
        Tenaga Kerja ({{ $stats['total_tk'] }})
    </a>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <form action="{{ route('master.tarif_produksi.index') }}" method="GET" style="display: flex; gap: 0.5rem; max-width: 400px; width: 100%;">
            @if(!empty($kategori))
                <input type="hidden" name="kategori" value="{{ $kategori }}">
            @endif
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama atau kode tarif..." class="form-control" style="padding: 0.5rem 0.75rem;">
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if(!empty($search))
                <a href="{{ route('master.tarif_produksi.index', ['kategori' => $kategori]) }}" class="btn btn-secondary" title="Reset">&times;</a>
            @endif
        </form>
        <span style="color: #64748b; font-size: 0.875rem;">Total Tampil: <strong>{{ $tarifs->count() }} Data</strong></span>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th style="width: 140px;">Kategori</th>
                    <th style="width: 160px;">Kode Tarif</th>
                    <th>Nama Komponen Biaya</th>
                    <th style="width: 180px; text-align: right;">Nilai Standar Acuan</th>
                    <th style="width: 160px;">Satuan Basis</th>
                    <th>Penjelasan / Keterangan</th>
                    <th style="width: 110px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tarifs as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            @if($item->kategori === 'FOH')
                                <span class="badge-category badge-foh">Overhead Pabrik</span>
                            @elseif($item->kategori === 'ENERGI')
                                <span class="badge-category badge-energi">Gas Alam CNG</span>
                            @else
                                <span class="badge-category badge-tk">Tenaga Kerja</span>
                            @endif
                        </td>
                        <td>
                            <code style="font-size: 0.75rem; color: #0284c7; background: #e0f2fe; padding: 0.15rem 0.35rem; border-radius: 4px; font-weight: 700;">
                                {{ $item->kode_tarif }}
                            </code>
                        </td>
                        <td>
                            <strong style="color: #0f172a; font-size: 0.875rem;">{{ $item->nama_tarif }}</strong>
                        </td>
                        <td style="text-align: right;">
                            <div class="rate-value-box">
                                @if(in_array($item->satuan_basis, ['PER_SHIFT', 'PER_ORANG', 'PER_MMBTU']))
                                    Rp {{ number_format($item->nilai_tarif, 2, ',', '.') }}
                                @else
                                    x {{ number_format($item->nilai_tarif, 2, ',', '.') }}
                                @endif
                            </div>
                        </td>
                        <td>
                            <span style="color: #475569; font-size: 0.8rem; font-weight: 600;">
                                {{ $item->satuan_label }}
                            </span>
                        </td>
                        <td>
                            <span style="color: #64748b; font-size: 0.8rem;">
                                {{ $item->keterangan ?? '-' }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            @php
                                $menuId = 'menuTarif' . $item->tarif_id;
                            @endphp
                            <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')">
                                <span>Aksi</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div id="{{ $menuId }}" class="action-dropdown-menu">
                                <button type="button" class="action-dropdown-item" onclick="openEditTarifModal({{ json_encode($item) }})">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Ubah Standar Tarif</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem; color: #94a3b8;">
                            Tidak ada data standar tarif yang sesuai dengan pencarian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL EDIT TARIF --}}
@include('master_data.tarif_produksi.partials.modal-edit-tarif')

@endsection

@push('scripts')
    <script src="{{ asset('js/master/tarif_produksi/tarif-index.js') }}"></script>
@endpush
