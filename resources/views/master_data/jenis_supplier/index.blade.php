@extends('layouts.app')

@section('title', 'Master Jenis Supplier - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Master Jenis Supplier</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Kelola klasifikasi mitra supplier: Bahan Baku, Bumbu/Perasa, Kemasan, Sparepart Mesin, dan Jasa/Umum.</p>
    </div>
    <div>
        <button type="button" onclick="openModal('modalTambahJenisSupplier')" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Jenis Supplier Baru
        </button>
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
                    <th style="width: 60px;">No</th>
                    <th>Kode Jenis</th>
                    <th>Nama Jenis Supplier</th>
                    <th>Status</th>
                    <th style="width: 150px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jenisSupplierList as $index => $item)
                    <tr>
                        <td>{{ $jenisSupplierList->firstItem() + $index }}</td>
                        <td><strong style="color: #0284c7;">{{ $item->jenis_supplier_cd }}</strong></td>
                        <td style="font-weight: 600;">{{ $item->jenis_supplier_nm }}</td>
                        <td>
                            <span class="badge badge-success">Aktif</span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 0.35rem;">
                                <button type="button" 
                                    class="btn btn-secondary btn-sm" 
                                    onclick="editJenisSupplier({{ $item->jenis_supplier_id }}, '{{ addslashes($item->jenis_supplier_cd) }}', '{{ addslashes($item->jenis_supplier_nm) }}')"
                                    title="Edit">
                                    Edit
                                </button>
                                <form action="{{ route('master.jenis_supplier.destroy', $item->jenis_supplier_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan jenis supplier ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                        Hapus
                                    </button>
                                </form>
                            </div>
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

{{-- MODAL TAMBAH JENIS SUPPLIER --}}
<div id="modalTambahJenisSupplier" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Tambah Jenis Supplier Baru</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahJenisSupplier')">&times;</button>
        </div>
        <form action="{{ route('master.jenis_supplier.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="create_jenis_supplier_cd" class="form-label">Kode Jenis Supplier <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_jenis_supplier_cd" name="jenis_supplier_cd" class="form-control" placeholder="Contoh: RAW, BUMBU, KEMASAN, SPAREPART" style="text-transform: uppercase;" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="create_jenis_supplier_nm" class="form-label">Nama Jenis Supplier <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_jenis_supplier_nm" name="jenis_supplier_nm" class="form-control" placeholder="Contoh: Bahan Baku Singkong & Minyak" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahJenisSupplier')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Jenis Supplier</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT JENIS SUPPLIER --}}
<div id="modalEditJenisSupplier" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Edit Jenis Supplier</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalEditJenisSupplier')">&times;</button>
        </div>
        <form id="formEditJenisSupplier" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_jenis_supplier_cd" class="form-label">Kode Jenis Supplier <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_jenis_supplier_cd" name="jenis_supplier_cd" class="form-control" style="text-transform: uppercase;" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_jenis_supplier_nm" class="form-label">Nama Jenis Supplier <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_jenis_supplier_nm" name="jenis_supplier_nm" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditJenisSupplier')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editJenisSupplier(id, kode, nama) {
        document.getElementById('edit_jenis_supplier_cd').value = kode;
        document.getElementById('edit_jenis_supplier_nm').value = nama;
        document.getElementById('formEditJenisSupplier').action = '{{ url("master-jenis-supplier") }}/' + id;
        openModal('modalEditJenisSupplier');
    }
</script>
@endsection
