@extends('layouts.app')

@section('title', 'Master Jenis Barang - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Master Jenis Barang</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Kelola klasifikasi material: Bahan Baku (RAW), Setengah Jadi (WIP), Barang Jadi (FG), dan Kemasan.</p>
    </div>
    <div>
        <button type="button" onclick="openModal('modalTambahJenis')" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Jenis Baru
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form action="{{ route('master.jenis.index') }}" method="GET" style="display: flex; gap: 0.5rem; max-width: 400px; width: 100%;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari kode atau nama jenis..." class="form-control" style="padding: 0.5rem 0.75rem;">
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if(!empty($search))
                <a href="{{ route('master.jenis.index') }}" class="btn btn-secondary" title="Reset Pencarian">&times;</a>
            @endif
        </form>
        <span style="color: #64748b; font-size: 0.875rem;">Total Data: <strong>{{ $jenisList->total() }}</strong></span>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Kode Jenis</th>
                    <th>Nama Jenis Barang</th>
                    <th>Status</th>
                    <th style="width: 150px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jenisList as $index => $item)
                    <tr>
                        <td>{{ $jenisList->firstItem() + $index }}</td>
                        <td><strong style="color: #0284c7;">{{ $item->jenis_barang_cd }}</strong></td>
                        <td style="font-weight: 600;">{{ $item->jenis_barang_nm }}</td>
                        <td>
                            <span class="badge badge-success">Aktif</span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 0.35rem;">
                                <button type="button" 
                                    class="btn btn-secondary btn-sm" 
                                    onclick="editJenis({{ $item->jenis_barang_id }}, '{{ addslashes($item->jenis_barang_cd) }}', '{{ addslashes($item->jenis_barang_nm) }}')"
                                    title="Edit">
                                    Edit
                                </button>
                                <form action="{{ route('master.jenis.destroy', $item->jenis_barang_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan jenis barang ini?')">
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
                            Belum ada data jenis barang. Klik tombol <strong>"Tambah Jenis Baru"</strong> di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($jenisList->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $jenisList->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODAL TAMBAH JENIS --}}
<div id="modalTambahJenis" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Tambah Jenis Barang Baru</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahJenis')">&times;</button>
        </div>
        <form action="{{ route('master.jenis.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="create_jenis_cd" class="form-label">Kode Jenis Barang <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_jenis_cd" name="jenis_barang_cd" class="form-control" placeholder="Contoh: RAW, WIP, FG, PACK" style="text-transform: uppercase;" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="create_jenis_nm" class="form-label">Nama Jenis Barang <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_jenis_nm" name="jenis_barang_nm" class="form-control" placeholder="Contoh: Bahan Baku Singkong & Minyak" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahJenis')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Jenis Barang</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT JENIS --}}
<div id="modalEditJenis" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Edit Jenis Barang</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalEditJenis')">&times;</button>
        </div>
        <form id="formEditJenis" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_jenis_cd" class="form-label">Kode Jenis Barang <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_jenis_cd" name="jenis_barang_cd" class="form-control" style="text-transform: uppercase;" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_jenis_nm" class="form-label">Nama Jenis Barang <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_jenis_nm" name="jenis_barang_nm" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditJenis')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editJenis(id, kode, nama) {
        document.getElementById('edit_jenis_cd').value = kode;
        document.getElementById('edit_jenis_nm').value = nama;
        document.getElementById('formEditJenis').action = '{{ url("master-jenis") }}/' + id;
        openModal('modalEditJenis');
    }
</script>
@endsection
