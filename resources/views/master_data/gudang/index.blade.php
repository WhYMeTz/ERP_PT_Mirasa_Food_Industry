@extends('layouts.app')

@section('title', 'Master Data Gudang - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Master Data Gudang</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Kelola lokasi fisik gudang bahan baku mentah, produksi, barang jadi, dan karantina.</p>
    </div>
    <div>
        <button type="button" onclick="openModal('modalTambahGudang')" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Gudang Baru
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form action="{{ route('master.gudang.index') }}" method="GET" style="display: flex; gap: 0.5rem; max-width: 400px; width: 100%;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari kode atau nama gudang..." class="form-control" style="padding: 0.5rem 0.75rem;">
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if(!empty($search))
                <a href="{{ route('master.gudang.index') }}" class="btn btn-secondary" title="Reset Pencarian">&times;</a>
            @endif
        </form>
        <span style="color: #64748b; font-size: 0.875rem;">Total Data: <strong>{{ $gudangs->total() }}</strong></span>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Kode Gudang</th>
                    <th>Nama Gudang</th>
                    <th>Tipe Gudang</th>
                    <th>Alamat / Lokasi</th>
                    <th style="width: 150px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($gudangs as $index => $item)
                    <tr>
                        <td>{{ $gudangs->firstItem() + $index }}</td>
                        <td><strong style="color: #0284c7;">{{ $item->gudang_cd }}</strong></td>
                        <td style="font-weight: 600;">{{ $item->gudang_nm }}</td>
                        <td>
                            <span class="badge badge-info">{{ $item->tipe_gudang_cd ?? 'GENERAL' }}</span>
                        </td>
                        <td>{{ $item->alamat_txt ?? '-' }}</td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 0.35rem;">
                                <button type="button" 
                                    class="btn btn-secondary btn-sm" 
                                    onclick="editGudang({{ $item->gudang_id }}, '{{ addslashes($item->gudang_cd) }}', '{{ addslashes($item->gudang_nm) }}', '{{ addslashes($item->tipe_gudang_cd ?? '') }}', '{{ addslashes($item->alamat_txt ?? '') }}')"
                                    title="Edit">
                                    Edit
                                </button>
                                <form action="{{ route('master.gudang.destroy', $item->gudang_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan gudang ini?')">
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
                        <td colspan="6" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            Belum ada data gudang. Klik tombol <strong>"Tambah Gudang Baru"</strong> di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($gudangs->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $gudangs->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODAL TAMBAH GUDANG --}}
<div id="modalTambahGudang" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Tambah Gudang Baru</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahGudang')">&times;</button>
        </div>
        <form action="{{ route('master.gudang.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.375rem;">
                        <label for="create_gudang_cd" class="form-label" style="margin-bottom: 0;">Kode Gudang <span style="color:#ef4444;">*</span></label>
                        <button type="button" class="btn btn-secondary btn-sm" data-target="create_gudang_cd" onclick="fetchNextCode('gudang', 'create_gudang_cd', {tipe: document.getElementById('create_tipe_gudang_cd').value})" style="padding: 0.2rem 0.6rem; font-size: 0.75rem;" title="Generate Ulang Nomor Urut Otomatis">
                            ↺ Auto Generate
                        </button>
                    </div>
                    <input type="text" id="create_gudang_cd" name="gudang_cd" value="{{ $nextGudangCode ?? '' }}" class="form-control" placeholder="Contoh: GDG-001" style="text-transform: uppercase;" required>
                    <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">Kode otomatis terisi nomor urut berikutnya, namun tetap bisa Anda ubah manual.</small>
                </div>
                <div class="form-group">
                    <label for="create_gudang_nm" class="form-label">Nama Gudang <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_gudang_nm" name="gudang_nm" class="form-control" placeholder="Contoh: Gudang Bahan Baku Singkong" required>
                </div>
                <div class="form-group">
                    <label for="create_tipe_gudang_cd" class="form-label">Tipe Gudang</label>
                    <select id="create_tipe_gudang_cd" name="tipe_gudang_cd" class="form-control" onchange="fetchNextCode('gudang', 'create_gudang_cd', {tipe: this.value})">
                        <option value="GENERAL" selected>Umum (General)</option>
                        <option value="RAW">Bahan Baku (RAW)</option>
                        <option value="WIP">Produksi / Setengah Jadi (WIP)</option>
                        <option value="FG">Barang Jadi (FG)</option>
                        <option value="TRANSIT">Transit / Sortir</option>
                        <option value="REJECT">Afkir / Rusak</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="create_alamat_txt" class="form-label">Alamat / Lokasi Fisik</label>
                    <textarea id="create_alamat_txt" name="alamat_txt" class="form-control" rows="2" placeholder="Contoh: Gedung A Sisi Utara Pabrik"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahGudang')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Gudang</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT GUDANG --}}
<div id="modalEditGudang" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Edit Data Gudang</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalEditGudang')">&times;</button>
        </div>
        <form id="formEditGudang" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_gudang_cd" class="form-label">Kode Gudang <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_gudang_cd" name="gudang_cd" class="form-control" style="text-transform: uppercase;" required>
                </div>
                <div class="form-group">
                    <label for="edit_gudang_nm" class="form-label">Nama Gudang <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_gudang_nm" name="gudang_nm" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_tipe_gudang_cd" class="form-label">Tipe Gudang</label>
                    <select id="edit_tipe_gudang_cd" name="tipe_gudang_cd" class="form-control">
                        <option value="RAW">Bahan Baku (RAW)</option>
                        <option value="WIP">Produksi / Setengah Jadi (WIP)</option>
                        <option value="FG">Barang Jadi (FG)</option>
                        <option value="TRANSIT">Transit / Sortir</option>
                        <option value="REJECT">Afkir / Rusak</option>
                        <option value="GENERAL">Umum (General)</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_alamat_txt" class="form-label">Alamat / Lokasi Fisik</label>
                    <textarea id="edit_alamat_txt" name="alamat_txt" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditGudang')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editGudang(id, kode, nama, tipe, alamat) {
        document.getElementById('edit_gudang_cd').value = kode;
        document.getElementById('edit_gudang_nm').value = nama;
        document.getElementById('edit_tipe_gudang_cd').value = tipe || 'GENERAL';
        document.getElementById('edit_alamat_txt').value = alamat || '';
        document.getElementById('formEditGudang').action = '{{ url("master-gudang") }}/' + id;
        openModal('modalEditGudang');
    }
</script>
@endsection
