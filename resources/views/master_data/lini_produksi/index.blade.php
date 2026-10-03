@extends('layouts.app')

@section('title', 'Master Data Lini Produksi - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/master/lini_produksi/lini-index.css') }}">
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Master Data Lini Produksi</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">
            Kelola daftar lini kerja operasional, kategori produk jadi (FG), dan barang dalam proses (WIP).
        </p>
    </div>
    <div>
        @if (Auth::user()?->canCreateMasterLiniProduksi())
            <button type="button" onclick="openModal('modalTambahLini')" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; padding: 0.55rem 1rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Lini Produksi Baru
            </button>
        @endif
    </div>
</div>

@if(session('success'))
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; color: #065f46; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
        <span>{{ session('error') }}</span>
    </div>
@endif

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <form action="{{ route('master.lini_produksi.index') }}" method="GET" style="display: flex; gap: 0.5rem; max-width: 400px; width: 100%;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari kode atau nama lini..." class="form-control" style="padding: 0.5rem 0.75rem;">
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if(!empty($search))
                <a href="{{ route('master.lini_produksi.index') }}" class="btn btn-secondary" title="Reset Pencarian">&times;</a>
            @endif
        </form>
        <span style="color: #64748b; font-size: 0.875rem;">Total Data: <strong>{{ $liniList->total() }}</strong></span>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th style="width: 120px;">Kode Lini</th>
                    <th>Nama Lini / Tujuan</th>
                    <th style="width: 180px;">Kategori</th>
                    <th style="width: 170px;">Format Batch</th>
                    <th>Keterangan</th>
                    <th style="width: 90px; text-align: center;">Status</th>
                    <th style="width: 110px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($liniList as $index => $item)
                    <tr>
                        <td>{{ $liniList->firstItem() + $index }}</td>
                        <td>
                            <strong style="color: #0284c7;">{{ $item->lini_cd }}</strong>
                        </td>
                        <td style="font-weight: 600; color: #0f172a;">
                            {{ $item->lini_nm }}
                        </td>
                        <td>
                            @if(str_contains((string) $item->kategori_lini, 'WIP'))
                                <span class="badge-category-wip">
                                    Work In Progress (WIP)
                                </span>
                            @elseif(str_contains((string) $item->kategori_lini, 'FG') || str_contains((string) $item->kategori_lini, 'FINISH'))
                                <span class="badge-category-fg">
                                    Finish Good (FG)
                                </span>
                            @else
                                <span class="badge-category-default">
                                    {{ $item->kategori_lini }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if ($item->tipe_batch === 'IFM')
                                <span style="color: #0369a1; font-weight: 600; font-size: 0.85rem;">
                                    Shift &amp; Karton (IFM)
                                </span>
                            @else
                                <span style="color: #64748b; font-size: 0.85rem;">
                                    Standar Tanggal
                                </span>
                            @endif
                        </td>
                        <td style="color: #64748b; font-size: 0.85rem;">
                            {{ $item->keterangan ?: '-' }}
                        </td>
                        <td style="text-align: center;">
                            @if ($item->active_st)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            @if (Auth::user()?->canEditMasterLiniProduksi() || Auth::user()?->canDeleteMasterLiniProduksi())
                                <button type="button" 
                                    class="btn-action-trigger" 
                                    onclick="toggleSmartActionDropdown(this, event, 'action-menu-{{ $item->lini_id }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div id="action-menu-{{ $item->lini_id }}" class="action-dropdown-menu">
                                    @if (Auth::user()?->canEditMasterLiniProduksi())
                                        <a href="{{ route('master.lini_produksi.edit', $item->lini_id) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Edit Data Lini
                                        </a>
                                    @endif

                                    @if (Auth::user()?->canDeleteMasterLiniProduksi())
                                        <div class="action-dropdown-divider"></div>
                                        <button type="button" 
                                             class="action-dropdown-item danger-item"
                                            onclick="confirmDeleteLini('{{ route('master.lini_produksi.destroy', $item->lini_id) }}', '{{ $item->lini_cd }}', '{{ $item->lini_nm }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Nonaktifkan
                                        </button>
                                    @endif
                                </div>
                            @else
                                <span style="color: #94a3b8; font-size: 0.8rem;">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            Belum ada data lini produksi yang terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($liniList->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $liniList->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODALS --}}
@include('master_data.lini_produksi.partials.modal-create')
@include('master_data.lini_produksi.partials.modal-delete')

@endsection

@push('scripts')
    <script src="{{ asset('js/master/lini_produksi/lini-index.js') }}"></script>
@endpush
