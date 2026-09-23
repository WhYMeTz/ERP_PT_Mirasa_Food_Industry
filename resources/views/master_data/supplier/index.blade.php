@extends('layouts.app')

@section('title', 'Master Data Supplier - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Master Data Supplier</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Kelola data pemasok singkong, minyak, bumbu, kemasan, sparepart, dan ekspedisi.</p>
    </div>
    <div>
        <button type="button" onclick="openModal('modalTambahSupplier')" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Supplier Baru
        </button>
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
                    <th style="width: 150px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($suppliers as $index => $item)
                    <tr>
                        <td>{{ $suppliers->firstItem() + $index }}</td>
                        <td><strong style="color: #0284c7;">{{ $item->supplier_cd }}</strong></td>
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
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 0.35rem;">
                                <button type="button" 
                                    class="btn btn-secondary btn-sm" 
                                    onclick="editSupplier({{ $item->supplier_id }}, '{{ addslashes($item->supplier_cd) }}', '{{ addslashes($item->supplier_nm) }}', '{{ $item->jenis_supplier_id ?? '' }}', '{{ addslashes($item->kontak_no ?? '') }}', '{{ addslashes($item->alamat_txt ?? '') }}')"
                                    title="Edit">
                                    Edit
                                </button>
                                <form action="{{ route('master.supplier.destroy', $item->supplier_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan supplier ini?')">
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

{{-- MODAL TAMBAH SUPPLIER --}}
<div id="modalTambahSupplier" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Tambah Supplier Baru</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahSupplier')">&times;</button>
        </div>
        <form action="{{ route('master.supplier.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.375rem;">
                        <label for="create_supplier_cd" class="form-label" style="margin-bottom: 0;">Kode Supplier <span style="color:#ef4444;">*</span></label>
                        <div style="display: flex; gap: 0.35rem;">
                            <button type="button" class="btn btn-secondary btn-sm" data-target="create_supplier_cd" onclick="fetchNextCode('supplier', 'create_supplier_cd', {name: document.getElementById('create_supplier_nm').value})" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" title="Buat kode dari singkatan nama">
                                ✨ Dari Singkatan
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" data-target="create_supplier_cd" onclick="fetchNextCode('supplier', 'create_supplier_cd')" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" title="Reset ke nomor urut standar">
                                ↺ Reset
                            </button>
                        </div>
                    </div>
                    <input type="text" id="create_supplier_cd" name="supplier_cd" value="{{ $nextSupplierCode ?? '' }}" class="form-control" placeholder="Contoh: SUP-SMN-01" style="text-transform: uppercase;" required>
                    <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">Otomatis mengikuti singkatan nama (misal: PT Sawit Murni → SUP-SMN-01) atau nomor urut.</small>
                </div>
                <div class="form-group">
                    <label for="create_supplier_nm" class="form-label">Nama Supplier / Mitra <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_supplier_nm" name="supplier_nm" class="form-control" placeholder="Contoh: PT Sawit Murni Nusantara" oninput="debounceCodeFromName('supplier', 'create_supplier_nm', 'create_supplier_cd')" required>
                </div>
                <div class="form-group">
                    <label for="create_jenis_supplier_id" class="form-label">Jenis Supplier</label>
                    <select id="create_jenis_supplier_id" name="jenis_supplier_id" class="form-control">
                        <option value="">-- Pilih Jenis Supplier (Opsional) --</option>
                        @foreach ($jenisSupplierList as $js)
                            <option value="{{ $js->jenis_supplier_id }}">{{ $js->jenis_supplier_nm }} ({{ $js->jenis_supplier_cd }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="create_kontak_no" class="form-label">Kontak / No Telepon (WhatsApp)</label>
                    <input type="text" id="create_kontak_no" name="kontak_no" class="form-control" placeholder="Contoh: 081234567890">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="create_alamat_txt" class="form-label">Alamat Lengkap</label>
                    <textarea id="create_alamat_txt" name="alamat_txt" class="form-control" rows="2" placeholder="Contoh: Desa Sukamaju, RT 02/05, Kec. Wonosobo"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahSupplier')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Supplier</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT SUPPLIER --}}
<div id="modalEditSupplier" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Edit Data Supplier</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalEditSupplier')">&times;</button>
        </div>
        <form id="formEditSupplier" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_supplier_cd" class="form-label">Kode Supplier <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_supplier_cd" name="supplier_cd" class="form-control" style="text-transform: uppercase;" required>
                </div>
                <div class="form-group">
                    <label for="edit_supplier_nm" class="form-label">Nama Supplier / Mitra <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_supplier_nm" name="supplier_nm" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_jenis_supplier_id" class="form-label">Jenis Supplier</label>
                    <select id="edit_jenis_supplier_id" name="jenis_supplier_id" class="form-control">
                        <option value="">-- Pilih Jenis Supplier (Opsional) --</option>
                        @foreach ($jenisSupplierList as $js)
                            <option value="{{ $js->jenis_supplier_id }}">{{ $js->jenis_supplier_nm }} ({{ $js->jenis_supplier_cd }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_kontak_no" class="form-label">Kontak / No Telepon</label>
                    <input type="text" id="edit_kontak_no" name="kontak_no" class="form-control">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_alamat_txt" class="form-label">Alamat Lengkap</label>
                    <textarea id="edit_alamat_txt" name="alamat_txt" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditSupplier')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editSupplier(id, kode, nama, jenisSupplierId, kontak, alamat) {
        document.getElementById('edit_supplier_cd').value = kode;
        document.getElementById('edit_supplier_nm').value = nama;
        document.getElementById('edit_jenis_supplier_id').value = jenisSupplierId || '';
        document.getElementById('edit_kontak_no').value = kontak || '';
        document.getElementById('edit_alamat_txt').value = alamat || '';
        document.getElementById('formEditSupplier').action = '{{ url("master-supplier") }}/' + id;
        openModal('modalEditSupplier');
    }
</script>
@endsection
