@extends('layouts.app')

@section('title', 'Master Data Karyawan - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Master Data Karyawan</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Kelola data profil staf operasional, produksi, gudang, purchasing, dan tim manajemen.</p>
    </div>
    <div>
        <button type="button" onclick="openModal('modalTambahKaryawan')" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Karyawan Baru
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form action="{{ route('master.karyawan.index') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; max-width: 600px; width: 100%;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari NIK, nama, jabatan, email..." class="form-control" style="padding: 0.5rem 0.75rem; max-width: 260px;">
            <select name="departemen" class="form-control" style="padding: 0.5rem 0.75rem; max-width: 200px;" onchange="this.form.submit()">
                <option value="">-- Semua Departemen --</option>
                @foreach ($departemenList as $key => $label)
                    <option value="{{ $key }}" {{ ($departemen ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
            @if(!empty($search) || !empty($departemen))
                <a href="{{ route('master.karyawan.index') }}" class="btn btn-secondary" title="Reset Filter">&times;</a>
            @endif
        </form>
        <span style="color: #64748b; font-size: 0.875rem;">Total Karyawan: <strong>{{ $karyawanList->total() }}</strong></span>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>NIK</th>
                    <th>Nama Karyawan</th>
                    <th>Departemen</th>
                    <th>Jabatan</th>
                    <th>Kontak / Telp</th>
                    <th>Akun Sistem & Gudang</th>
                    <th style="width: 150px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($karyawanList as $index => $item)
                    <tr>
                        <td>{{ $karyawanList->firstItem() + $index }}</td>
                        <td>
                            <strong style="color: #0284c7; font-family: monospace; font-size: 0.9rem;">{{ $item->nik }}</strong>
                        </td>
                        <td style="font-weight: 600; color: #0f172a;">
                            {{ $item->karyawan_nm }}
                            @if ($item->email)
                                <span style="display: block; font-size: 0.75rem; color: #64748b; font-weight: normal;">{{ $item->email }}</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $deptBg = '#f1f5f9';
                                $deptClr = '#475569';
                                if ($item->departemen_cd === 'PRODUKSI') { $deptBg = '#ffedd5'; $deptClr = '#c2410c'; }
                                elseif ($item->departemen_cd === 'GUDANG') { $deptBg = '#e0f2fe'; $deptClr = '#0369a1'; }
                                elseif ($item->departemen_cd === 'PURCHASING') { $deptBg = '#fef3c7'; $deptClr = '#92400e'; }
                                elseif ($item->departemen_cd === 'FINANCE') { $deptBg = '#dcfce7'; $deptClr = '#15803d'; }
                                elseif ($item->departemen_cd === 'QC') { $deptBg = '#f3e8ff'; $deptClr = '#7e22ce'; }
                            @endphp
                            <span class="badge" style="background: {{ $deptBg }}; color: {{ $deptClr }}; font-weight: 700;">
                                {{ $departemenList[$item->departemen_cd] ?? $item->departemen_cd }}
                            </span>
                        </td>
                        <td>{{ $item->jabatan_nm }}</td>
                        <td>{{ $item->telepon_no ?? '-' }}</td>
                        <td>
                            @if ($item->user)
                                <span class="badge" style="background: #ecfdf5; color: #065f46; font-weight: 600;">
                                    ✓ Akun Aktif: {{ $item->user->role_cd }}
                                </span>
                                @if ($item->user->gudang)
                                    <span style="display: block; font-size: 0.75rem; color: #0284c7; margin-top: 0.15rem;">
                                        📍 {{ $item->user->gudang->gudang_nm }}
                                    </span>
                                @else
                                    <span style="display: block; font-size: 0.75rem; color: #94a3b8; margin-top: 0.15rem;">
                                        🌐 Multi-Gudang (Pusat)
                                    </span>
                                @endif
                            @else
                                <span class="badge" style="background: #f1f5f9; color: #94a3b8;">
                                    Belum ada akun
                                </span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 0.35rem;">
                                <button type="button" 
                                    class="btn btn-secondary btn-sm" 
                                    onclick="editKaryawan({{ $item->karyawan_id }}, '{{ addslashes($item->nik) }}', '{{ addslashes($item->karyawan_nm) }}', '{{ $item->departemen_cd }}', '{{ addslashes($item->jabatan_nm) }}', '{{ addslashes($item->telepon_no ?? '') }}', '{{ addslashes($item->email ?? '') }}', '{{ addslashes($item->alamat_txt ?? '') }}')"
                                    title="Edit Data">
                                    Edit
                                </button>
                                <form action="{{ route('master.karyawan.destroy', $item->karyawan_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan data karyawan ini?')">
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
                        <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            Belum ada data karyawan. Klik tombol <strong>"Tambah Karyawan Baru"</strong> di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($karyawanList->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $karyawanList->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODAL TAMBAH KARYAWAN --}}
<div id="modalTambahKaryawan" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Tambah Karyawan Baru</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahKaryawan')">&times;</button>
        </div>
        <form action="{{ route('master.karyawan.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="create_nik" class="form-label">Nomor Induk Karyawan (NIK) <span style="color:#ef4444;">*</span></label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" id="create_nik" name="nik" value="{{ old('nik', $nextNik) }}" class="form-control" required placeholder="Contoh: KRY-0001">
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('create_nik').value='{{ $nextNik }}'" title="Reset NIK Otomatis">↺</button>
                    </div>
                    <small style="color: #64748b; font-size: 0.75rem;">Otomatis terisi nomor urut karyawan, dapat disesuaikan dengan NIK resmi pabrik.</small>
                </div>

                <div class="form-group">
                    <label for="create_karyawan_nm" class="form-label">Nama Lengkap Karyawan <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_karyawan_nm" name="karyawan_nm" value="{{ old('karyawan_nm') }}" class="form-control" required placeholder="Contoh: Budi Santoso">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="create_departemen_cd" class="form-label">Departemen <span style="color:#ef4444;">*</span></label>
                        <select id="create_departemen_cd" name="departemen_cd" class="form-control" required>
                            <option value="">-- Pilih Departemen --</option>
                            @foreach ($departemenList as $key => $label)
                                <option value="{{ $key }}" {{ old('departemen_cd') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="create_jabatan_nm" class="form-label">Jabatan Kerja <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="create_jabatan_nm" name="jabatan_nm" value="{{ old('jabatan_nm') }}" class="form-control" required placeholder="Contoh: Mandor Produksi">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="create_telepon_no" class="form-label">Nomor Telepon / WhatsApp</label>
                        <input type="text" id="create_telepon_no" name="telepon_no" value="{{ old('telepon_no') }}" class="form-control" placeholder="Contoh: 08123456789">
                    </div>
                    <div class="form-group">
                        <label for="create_email" class="form-label">Email</label>
                        <input type="email" id="create_email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Contoh: budi@mirasa.co.id">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="create_alamat_txt" class="form-label">Alamat Domisili</label>
                    <textarea id="create_alamat_txt" name="alamat_txt" class="form-control" rows="2" placeholder="Alamat lengkap karyawan...">{{ old('alamat_txt') }}</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahKaryawan')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Karyawan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT KARYAWAN --}}
<div id="modalEditKaryawan" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Edit Data Karyawan</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalEditKaryawan')">&times;</button>
        </div>
        <form id="formEditKaryawan" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_nik" class="form-label">Nomor Induk Karyawan (NIK) <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_nik" name="nik" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_karyawan_nm" class="form-label">Nama Lengkap Karyawan <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_karyawan_nm" name="karyawan_nm" class="form-control" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="edit_departemen_cd" class="form-label">Departemen <span style="color:#ef4444;">*</span></label>
                        <select id="edit_departemen_cd" name="departemen_cd" class="form-control" required>
                            @foreach ($departemenList as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_jabatan_nm" class="form-label">Jabatan Kerja <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="edit_jabatan_nm" name="jabatan_nm" class="form-control" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="edit_telepon_no" class="form-label">Nomor Telepon / WhatsApp</label>
                        <input type="text" id="edit_telepon_no" name="telepon_no" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" id="edit_email" name="email" class="form-control">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_alamat_txt" class="form-label">Alamat Domisili</label>
                    <textarea id="edit_alamat_txt" name="alamat_txt" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditKaryawan')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editKaryawan(id, nik, nama, departemen, jabatan, telp, email, alamat) {
        document.getElementById('formEditKaryawan').action = '{{ url("master-karyawan") }}/' + id;
        document.getElementById('edit_nik').value = nik;
        document.getElementById('edit_karyawan_nm').value = nama;
        document.getElementById('edit_departemen_cd').value = departemen;
        document.getElementById('edit_jabatan_nm').value = jabatan;
        document.getElementById('edit_telepon_no').value = telp;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_alamat_txt').value = alamat;
        openModal('modalEditKaryawan');
    }
</script>
@endsection
