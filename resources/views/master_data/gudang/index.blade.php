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
                    <th style="width: 50px;">NO</th>
                    <th>INFORMASI PERUSAHAAN</th>
                    <th>KODE</th>
                    <th>JENIS</th>
                    <th>ALAMAT & KONTAK</th>
                    <th style="width: 140px; text-align: right;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($gudangs as $index => $item)
                    <tr>
                        <td>{{ $gudangs->firstItem() + $index }}</td>
                        <td>
                            <strong style="color: #0f172a; font-size: 0.9375rem;">{{ $item->gudang_nm }}</strong>
                        </td>
                        <td>
                            <span class="badge" style="background: #f1f5f9; color: #0369a1; font-family: monospace; font-weight: 700;">
                                {{ $item->gudang_cd }}
                            </span>
                        </td>
                        <td>
                            @php
                                $badgeBg = '#f1f5f9';
                                $badgeClr = '#475569';
                                if (strcasecmp($item->tipe_gudang_cd, 'Cabang') === 0) {
                                    $badgeBg = '#fef3c7'; $badgeClr = '#b45309';
                                } elseif (strcasecmp($item->tipe_gudang_cd, 'Pusat') === 0) {
                                    $badgeBg = '#e0f2fe'; $badgeClr = '#0369a1';
                                } elseif (strcasecmp($item->tipe_gudang_cd, 'Anak Perusahaan') === 0) {
                                    $badgeBg = '#f3e8ff'; $badgeClr = '#7e22ce';
                                }
                            @endphp
                            <span class="badge" style="background: {{ $badgeBg }}; color: {{ $badgeClr }}; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 9999px;">
                                {{ $item->tipe_gudang_cd ?? 'Pusat' }}
                            </span>
                        </td>
                        <td>
                            <div style="font-size: 0.8125rem; color: #475569; display: flex; flex-direction: column; gap: 0.2rem;">
                                <div style="display: flex; align-items: flex-start; gap: 0.35rem;">
                                    <span style="color: #64748b; font-size: 0.875rem;">📍</span>
                                    <span>{{ $item->alamat_txt ?? '-' }}</span>
                                </div>
                                @if (!empty($item->telepon))
                                    <div style="display: flex; align-items: center; gap: 0.35rem; color: #16a34a; font-weight: 600;">
                                        <span style="font-size: 0.875rem;">📞</span>
                                        <span>{{ $item->telepon }}</span>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 0.35rem;">
                                <button type="button" 
                                    class="btn btn-secondary btn-sm" 
                                    onclick="editGudang({{ $item->gudang_id }}, '{{ addslashes($item->gudang_cd) }}', '{{ addslashes($item->gudang_nm) }}', '{{ addslashes($item->tipe_gudang_cd ?? '') }}', '{{ addslashes($item->alamat_txt ?? '') }}', '{{ addslashes($item->telepon ?? '') }}')"
                                    title="Edit">
                                    Edit
                                </button>
                                <form action="{{ route('master.gudang.destroy', $item->gudang_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan entitas gudang ini?')">
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
                            Belum ada data gudang/perusahaan. Klik tombol <strong>"Tambah Gudang Baru"</strong> di atas.
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
            <h2 class="modal-title">Tambah Lokasi / Entitas Perusahaan Baru</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahGudang')">&times;</button>
        </div>
        <form action="{{ route('master.gudang.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="create_gudang_nm" class="form-label">Nama Perusahaan / Lokasi <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_gudang_nm" name="gudang_nm" class="form-control" placeholder="Contoh: PT Mirasa Food Industry" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="create_gudang_cd" class="form-label">Kode Lokasi <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="create_gudang_cd" name="gudang_cd" value="{{ $nextGudangCode ?? '' }}" class="form-control" placeholder="Contoh: MFI-PST" style="text-transform: uppercase;" required>
                    </div>
                    <div class="form-group">
                        <label for="create_tipe_gudang_cd" class="form-label">Jenis Entitas</label>
                        <select id="create_tipe_gudang_cd" name="tipe_gudang_cd" class="form-control">
                            <option value="Pusat">Pusat</option>
                            <option value="Cabang">Cabang</option>
                            <option value="Anak Perusahaan">Anak Perusahaan</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="create_telepon" class="form-label">No. Telepon / Kontak</label>
                    <input type="text" id="create_telepon" name="telepon" class="form-control" placeholder="Contoh: 6287880809279">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="create_alamat_txt" class="form-label">Alamat Lengkap</label>
                    <textarea id="create_alamat_txt" name="alamat_txt" class="form-control" rows="2" placeholder="Contoh: Jalan Munggur No. 2 Ambartawang..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahGudang')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT GUDANG --}}
<div id="modalEditGudang" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Edit Data Lokasi / Entitas Perusahaan</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalEditGudang')">&times;</button>
        </div>
        <form id="formEditGudang" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_gudang_nm" class="form-label">Nama Perusahaan / Lokasi <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_gudang_nm" name="gudang_nm" class="form-control" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="edit_gudang_cd" class="form-label">Kode Lokasi <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="edit_gudang_cd" name="gudang_cd" class="form-control" style="text-transform: uppercase;" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_tipe_gudang_cd" class="form-label">Jenis Entitas</label>
                        <select id="edit_tipe_gudang_cd" name="tipe_gudang_cd" class="form-control">
                            <option value="Pusat">Pusat</option>
                            <option value="Cabang">Cabang</option>
                            <option value="Anak Perusahaan">Anak Perusahaan</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_telepon" class="form-label">No. Telepon / Kontak</label>
                    <input type="text" id="edit_telepon" name="telepon" class="form-control">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_alamat_txt" class="form-label">Alamat Lengkap</label>
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
    function editGudang(id, kode, nama, tipe, alamat, telepon) {
        document.getElementById('edit_gudang_cd').value = kode;
        document.getElementById('edit_gudang_nm').value = nama;
        document.getElementById('edit_tipe_gudang_cd').value = tipe || 'Pusat';
        document.getElementById('edit_alamat_txt').value = alamat || '';
        document.getElementById('edit_telepon').value = telepon || '';
        document.getElementById('formEditGudang').action = '{{ url("master-gudang") }}/' + id;
        openModal('modalEditGudang');
    }
</script>
@endsection
