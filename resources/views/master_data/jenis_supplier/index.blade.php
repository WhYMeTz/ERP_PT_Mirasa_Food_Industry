@extends('layouts.app')

@section('title', 'Master Jenis Supplier - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/master/jenis_supplier/jenis-supplier-index.css') }}">
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Master Jenis Supplier</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Kelola klasifikasi mitra supplier: Bahan Baku, Bumbu/Perasa, Kemasan, Sparepart Mesin, dan Jasa/Umum.</p>
    </div>
    <div>
        @if (Auth::user()?->canCreateMasterJenisSupplier())
            <button type="button" onclick="openModal('modalTambahJenisSupplier')" class="btn btn-primary">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Jenis Supplier Baru
            </button>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form action="{{ route('master.jenis_supplier.index') }}" method="GET" style="display: flex; gap: 0.5rem; max-width: 400px; width: 100%;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari kode atau nama jenis supplier..." class="form-control" style="padding: 0.5rem 0.75rem;">
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if(!empty($search))
                <a href="{{ route('master.jenis_supplier.index') }}" class="btn btn-secondary" title="Reset Pencarian">&times;</a>
            @endif
        </form>
        <span style="color: #64748b; font-size: 0.875rem;">Total Data: <strong>{{ $jenisSupplierList->total() }}</strong></span>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Kode Jenis</th>
                    <th>Nama Jenis Supplier</th>
                    <th style="width: 120px; text-align: center;">Status</th>
                    <th style="width: 130px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jenisSupplierList as $index => $item)
                    <tr>
                        <td>{{ $jenisSupplierList->firstItem() + $index }}</td>
                        <td><strong style="color: #0284c7; font-size: 1rem;">{{ $item->jenis_supplier_cd }}</strong></td>
                        <td style="font-weight: 600;">{{ $item->jenis_supplier_nm }}</td>
                        <td style="text-align: center;">
                            <span class="badge badge-success">Aktif</span>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            @if (Auth::user()?->canEditMasterJenisSupplier() || Auth::user()?->canDeleteMasterJenisSupplier())
                                <button type="button" 
                                    class="btn-action-trigger" 
                                    onclick="toggleSmartActionDropdown(this, event, 'action-menu-{{ $item->jenis_supplier_id }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div id="action-menu-{{ $item->jenis_supplier_id }}" class="action-dropdown-menu">
                                    @if (Auth::user()?->canEditMasterJenisSupplier())
                                        <button type="button" 
                                            class="action-dropdown-item" 
                                            onclick="closeAllActionDropdowns(); editJenisSupplier({{ $item->jenis_supplier_id }}, '{{ addslashes($item->jenis_supplier_cd) }}', '{{ addslashes($item->jenis_supplier_nm) }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            <span>Edit Jenis Supplier</span>
                                        </button>
                                    @endif

                                    @if (Auth::user()?->canEditMasterJenisSupplier() && Auth::user()?->canDeleteMasterJenisSupplier())
                                        <div class="action-dropdown-divider"></div>
                                    @endif

                                    @if (Auth::user()?->canDeleteMasterJenisSupplier())
                                        <button type="button" 
                                            class="action-dropdown-item danger-item" 
                                            onclick="closeAllActionDropdowns(); openDeleteJenisSupplierModal({{ $item->jenis_supplier_id }}, '{{ addslashes($item->jenis_supplier_cd) }}', '{{ addslashes($item->jenis_supplier_nm) }}')">
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
                        <td colspan="5" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            Belum ada data jenis supplier. Klik tombol <strong>"Tambah Jenis Supplier Baru"</strong> di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($jenisSupplierList->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $jenisSupplierList->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODALS --}}
@include('master_data.jenis_supplier.partials.modal-create')
@include('master_data.jenis_supplier.partials.modal-edit')
@include('master_data.jenis_supplier.partials.modal-delete')

@push('scripts')
<script>
    window.masterJenisSupplierConfig = {
        baseUrl: "{{ url('master-jenis-supplier') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('js/master/jenis_supplier/jenis-supplier-index.js') }}"></script>
@endpush
@endsection
