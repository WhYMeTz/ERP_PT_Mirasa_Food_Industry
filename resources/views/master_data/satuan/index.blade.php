@extends('layouts.app')

@section('title', 'Master Data Satuan - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Master Data Satuan</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Kelola unit ukuran (Satuan Dasar & Satuan Besar) untuk transaksi gudang dan produksi.</p>
    </div>
    <div>
        <button type="button" onclick="openModal('modalTambahSatuan')" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Satuan Baru
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form action="{{ route('master.satuan.index') }}" method="GET" style="display: flex; gap: 0.5rem; max-width: 400px; width: 100%;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari kode atau nama satuan..." class="form-control" style="padding: 0.5rem 0.75rem;">
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if(!empty($search))
                <a href="{{ route('master.satuan.index') }}" class="btn btn-secondary" title="Reset Pencarian">&times;</a>
            @endif
        </form>
        <span style="color: #64748b; font-size: 0.875rem;">Total Data: <strong>{{ $satuans->total() }}</strong></span>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Kode Satuan</th>
                    <th>Nama Satuan</th>
                    <th>Status</th>
                    <th style="width: 150px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($satuans as $index => $item)
                    <tr>
                        <td>{{ $satuans->firstItem() + $index }}</td>
                        <td><strong style="color: #0284c7; font-size: 1rem;">{{ $item->satuan_cd }}</strong></td>
                        <td style="font-weight: 600;">{{ $item->satuan_nm }}</td>
                        <td>
                            <span class="badge badge-success">Aktif</span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 0.35rem;">
                                <button type="button" 
                                    class="btn btn-secondary btn-sm" 
                                    onclick="editSatuan({{ $item->satuan_id }}, '{{ addslashes($item->satuan_cd) }}', '{{ addslashes($item->satuan_nm) }}')"
                                    title="Edit">
                                    Edit
                                </button>
                                <form action="{{ route('master.satuan.destroy', $item->satuan_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan satuan ini?')">
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
                            Belum ada data master satuan. Klik tombol <strong>"Tambah Satuan Baru"</strong> di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($satuans->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $satuans->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODAL TAMBAH SATUAN --}}
<div id="modalTambahSatuan" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Tambah Satuan Baru</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahSatuan')">&times;</button>
        </div>
        <form action="{{ route('master.satuan.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="create_satuan_cd" class="form-label">Kode Satuan <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_satuan_cd" name="satuan_cd" class="form-control" placeholder="Contoh: KG, SAK, DUS" style="text-transform: uppercase;" required>
                    <span style="font-size: 0.775rem; color: #64748b; margin-top: 0.25rem; display: block;">Singkatan huruf besar tanpa spasi.</span>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="create_satuan_nm" class="form-label">Nama Satuan <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_satuan_nm" name="satuan_nm" class="form-control" placeholder="Contoh: Kilogram, Sak 50Kg" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahSatuan')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Satuan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT SATUAN --}}
<div id="modalEditSatuan" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Edit Data Satuan</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalEditSatuan')">&times;</button>
        </div>
        <form id="formEditSatuan" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_satuan_cd" class="form-label">Kode Satuan <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_satuan_cd" name="satuan_cd" class="form-control" style="text-transform: uppercase;" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_satuan_nm" class="form-label">Nama Satuan <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_satuan_nm" name="satuan_nm" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditSatuan')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editSatuan(id, kode, nama) {
        document.getElementById('edit_satuan_cd').value = kode;
        document.getElementById('edit_satuan_nm').value = nama;
        document.getElementById('formEditSatuan').action = '{{ url("master-satuan") }}/' + id;
        openModal('modalEditSatuan');
    }
</script>
@endsection
