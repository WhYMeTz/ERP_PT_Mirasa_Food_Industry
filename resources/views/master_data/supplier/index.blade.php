@extends('layouts.app')

@section('title', 'Master Data Supplier - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/master/supplier/supplier-index.css') }}">
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Master Data Supplier</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Kelola data pemasok singkong, minyak, bumbu, kemasan, sparepart, dan ekspedisi.</p>
    </div>
    <div>
        @if (Auth::user()?->canCreateMasterSupplier())
            <button type="button" onclick="openModal('modalTambahSupplier')" class="btn btn-primary">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Supplier Baru
            </button>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form action="{{ route('master.supplier.index') }}" method="GET" style="display: flex; gap: 0.5rem; max-width: 400px; width: 100%;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari kode, nama, atau kontak..." class="form-control" style="padding: 0.5rem 0.75rem;">
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if(!empty($search))
                <a href="{{ route('master.supplier.index') }}" class="btn btn-secondary" title="Reset Pencarian">&times;</a>
            @endif
        </form>
        <span style="color: #64748b; font-size: 0.875rem;">Total Data: <strong>{{ $suppliers->total() }}</strong></span>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Kode Supplier</th>
                    <th>Nama Supplier</th>
                    <th>Jenis Supplier</th>
                    <th>Kontak / Telp</th>
                    <th>Alamat</th>
                    <th style="width: 130px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($suppliers as $index => $item)
                    <tr>
                        <td>{{ $suppliers->firstItem() + $index }}</td>
                        <td><strong style="color: #0284c7; font-size: 1rem;">{{ $item->supplier_cd }}</strong></td>
                        <td style="font-weight: 600;">{{ $item->supplier_nm }}</td>
                        <td>
                            @if ($item->jenisSupplier)
                                <span class="badge badge-info">{{ $item->jenisSupplier->jenis_supplier_nm }}</span>
                            @else
                                <span class="badge" style="background:#f1f5f9; color:#64748b;">Umum / Belum Diatur</span>
                            @endif
                        </td>
                        <td>{{ $item->kontak_no ?? '-' }}</td>
                        <td>{{ $item->alamat_txt ?? '-' }}</td>
                        <td style="text-align: center; vertical-align: middle;">
                            @if (Auth::user()?->canEditMasterSupplier() || Auth::user()?->canDeleteMasterSupplier())
                                <button type="button" 
                                    class="btn-action-trigger" 
                                    onclick="toggleSmartActionDropdown(this, event, 'action-menu-{{ $item->supplier_id }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div id="action-menu-{{ $item->supplier_id }}" class="action-dropdown-menu">
                                    @if (Auth::user()?->canEditMasterSupplier())
                                        <button type="button" 
                                            class="action-dropdown-item" 
                                            onclick="closeAllActionDropdowns(); editSupplier({{ $item->supplier_id }}, '{{ addslashes($item->supplier_cd) }}', '{{ addslashes($item->supplier_nm) }}', '{{ $item->jenis_supplier_id ?? '' }}', '{{ addslashes($item->kontak_no ?? '') }}', '{{ addslashes($item->alamat_txt ?? '') }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <span>Edit Data Supplier</span>
                                        </button>
                                    @endif

                                    @if (Auth::user()?->canEditMasterSupplier() && Auth::user()?->canDeleteMasterSupplier())
                                        <div class="action-dropdown-divider"></div>
                                    @endif

                                    @if (Auth::user()?->canDeleteMasterSupplier())
                                        <button type="button" 
                                            class="action-dropdown-item danger-item" 
                                            onclick="closeAllActionDropdowns(); openDeleteSupplierModal({{ $item->supplier_id }}, '{{ addslashes($item->supplier_cd) }}', '{{ addslashes($item->supplier_nm) }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Hapus / Nonaktifkan</span>
                                        </button>
                                    @endif
                                </div>
                            @else
                                <span style="font-size: 0.75rem; color: #94a3b8;">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            Belum ada data supplier. Klik tombol <strong>"Tambah Supplier Baru"</strong> di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($suppliers->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $suppliers->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODALS --}}
@include('master_data.supplier.partials.modal-create')
@include('master_data.supplier.partials.modal-edit')
@include('master_data.supplier.partials.modal-delete')

@push('scripts')
<script>
    window.masterSupplierConfig = {
        baseUrl: "{{ url('master-supplier') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('js/master/supplier/supplier-index.js') }}"></script>
@endpush
@endsection
