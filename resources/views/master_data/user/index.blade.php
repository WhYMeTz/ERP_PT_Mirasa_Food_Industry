@extends('layouts.app')

@section('title', 'Manajemen Pengguna & Hak Akses Gudang - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Manajemen Pengguna & Hak Akses</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Pengaturan akun login, hak akses (Role), dan penugasan lokasi gudang/pabrik (Gudang Magelang, Gudang Bahan Baku, dll).</p>
    </div>
    <div>
        <button type="button" onclick="openModal('modalTambahUser')" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Tambah Akun Pengguna
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form action="{{ route('admin.users.index') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; max-width: 600px; width: 100%;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama, email, role, atau karyawan..." class="form-control" style="padding: 0.5rem 0.75rem; max-width: 260px;">
            <select name="role" class="form-control" style="padding: 0.5rem 0.75rem; max-width: 220px;" onchange="this.form.submit()">
                <option value="">-- Semua Hak Akses / Role --</option>
                @foreach ($roleList as $key => $label)
                    <option value="{{ $key }}" {{ ($role ?? '') === $key ? 'selected' : '' }}>{{ $key }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
            @if(!empty($search) || !empty($role))
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary" title="Reset Filter">&times;</a>
            @endif
        </form>
        <span style="color: #64748b; font-size: 0.875rem;">Total Akun: <strong>{{ $userList->total() }}</strong></span>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Pengguna</th>
                    <th>Email Login</th>
                    <th>Profil Karyawan</th>
                    <th>Peran (Role)</th>
                    <th>Penugasan Gudang / Cabang</th>
                    <th>Status</th>
                    <th style="width: 150px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($userList as $index => $item)
                    <tr>
                        <td>{{ $userList->firstItem() + $index }}</td>
                        <td>
                            <strong style="color: #0f172a;">{{ $item->name }}</strong>
                        </td>
                        <td>
                            <span style="color: #0284c7; font-family: monospace;">{{ $item->email }}</span>
                        </td>
                        <td>
                            @if ($item->karyawan)
                                <strong style="color: #334155;">{{ $item->karyawan->karyawan_nm }}</strong>
                                <span style="display: block; font-size: 0.75rem; color: #64748b;">NIK: {{ $item->karyawan->nik }} ({{ $item->karyawan->departemen_cd }})</span>
                            @else
                                <span style="color: #94a3b8; font-style: italic;">Akun Sistem Tanpa Karyawan</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $roleBg = '#f1f5f9';
                                $roleClr = '#475569';
                                if ($item->role_cd === 'SUPERADMIN') { $roleBg = '#fee2e2'; $roleClr = '#991b1b'; }
                                elseif ($item->role_cd === 'STAFF_PRODUKSI') { $roleBg = '#ffedd5'; $roleClr = '#c2410c'; }
                                elseif ($item->role_cd === 'ADMIN_GUDANG') { $roleBg = '#e0f2fe'; $roleClr = '#0369a1'; }
                                elseif ($item->role_cd === 'PURCHASING') { $roleBg = '#fef3c7'; $roleClr = '#92400e'; }
                                elseif ($item->role_cd === 'FINANCE') { $roleBg = '#dcfce7'; $roleClr = '#15803d'; }
                                elseif ($item->role_cd === 'QC') { $roleBg = '#f3e8ff'; $roleClr = '#7e22ce'; }
                            @endphp
                            <span class="badge" style="background: {{ $roleBg }}; color: {{ $roleClr }}; font-weight: 700;">
                                {{ $item->role_cd }}
                            </span>
                        </td>
                        <td>
                            @if ($item->gudang)
                                <div style="display: inline-flex; align-items: center; gap: 0.35rem; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 0.2rem 0.5rem; border-radius: 6px;">
                                    <span style="font-size: 0.85rem;">🔒</span>
                                    <div>
                                        <strong style="color: #15803d; font-size: 0.8125rem;">{{ $item->gudang->gudang_nm }}</strong>
                                        <span style="display: block; font-size: 0.7rem; color: #16a34a;">Kode: {{ $item->gudang->gudang_cd }}</span>
                                    </div>
                                </div>
                            @else
                                <div style="display: inline-flex; align-items: center; gap: 0.35rem; background: #f8fafc; border: 1px solid #e2e8f0; padding: 0.2rem 0.5rem; border-radius: 6px;">
                                    <span style="font-size: 0.85rem;">🌐</span>
                                    <div>
                                        <strong style="color: #475569; font-size: 0.8125rem;">Multi-Gudang (Bebas)</strong>
                                        <span style="display: block; font-size: 0.7rem; color: #94a3b8;">Akses Seluruh Lokasi Pabrik</span>
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td>
                            @if ($item->active_st)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 0.35rem;">
                                <button type="button" 
                                    class="btn btn-secondary btn-sm" 
                                    onclick="editUser({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ addslashes($item->email) }}', '{{ $item->karyawan_id ?? '' }}', '{{ $item->role_cd }}', '{{ $item->gudang_id ?? '' }}')"
                                    title="Edit Akun & Akses">
                                    Edit
                                </button>
                                <form action="{{ route('admin.users.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan akun pengguna ini?')">
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
                            Belum ada akun pengguna. Klik tombol <strong>"Tambah Akun Pengguna"</strong> di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($userList->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $userList->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- MODAL TAMBAH USER --}}
<div id="modalTambahUser" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Tambah Akun Pengguna & Hak Akses</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahUser')">&times;</button>
        </div>
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="create_karyawan_id" class="form-label">Tautkan Profil Karyawan (Opsional)</label>
                    <select id="create_karyawan_id" name="karyawan_id" class="form-control" onchange="autoFillKaryawan(this)">
                        <option value="">-- Tanpa Karyawan (Akun Umum/Sistem) --</option>
                        @foreach ($karyawanList as $k)
                            <option value="{{ $k->karyawan_id }}" data-nama="{{ $k->karyawan_nm }}" data-email="{{ $k->email }}" data-dept="{{ $k->departemen_cd }}">
                                {{ $k->karyawan_nm }} ({{ $k->nik }} - {{ $k->departemen_cd }})
                            </option>
                        @endforeach
                    </select>
                    <small style="color: #64748b; font-size: 0.75rem;">Menghubungkan akun login dengan profil fisik staf PT Mirasa.</small>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="create_name" class="form-label">Nama Pengguna (Display) <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="create_name" name="name" value="{{ old('name') }}" class="form-control" required placeholder="Contoh: Budi Produksi">
                    </div>
                    <div class="form-group">
                        <label for="create_email" class="form-label">Email Login <span style="color:#ef4444;">*</span></label>
                        <input type="email" id="create_email" name="email" value="{{ old('email') }}" class="form-control" required placeholder="Contoh: budi@mirasa.co.id">
                    </div>
                </div>

                <div class="form-group">
                    <label for="create_password" class="form-label">Password Login <span style="color:#ef4444;">*</span></label>
                    <input type="password" id="create_password" name="password" class="form-control" required minlength="6" placeholder="Minimal 6 karakter...">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="create_role_cd" class="form-label">Peran Hak Akses (Role) <span style="color:#ef4444;">*</span></label>
                        <select id="create_role_cd" name="role_cd" class="form-control" required>
                            @foreach ($roleList as $key => $label)
                                <option value="{{ $key }}" {{ old('role_cd', 'STAFF_PRODUKSI') === $key ? 'selected' : '' }}>{{ $key }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create_gudang_id" class="form-label">Gudang Tugas (Lokasi Pabrik)</label>
                        <select id="create_gudang_id" name="gudang_id" class="form-control">
                            <option value="">-- Multi-Gudang (Bebas / Pusat) --</option>
                            @foreach ($gudangList as $gdg)
                                <option value="{{ $gdg->gudang_id }}" {{ old('gudang_id') == $gdg->gudang_id ? 'selected' : '' }}>
                                    {{ $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem 1rem; margin-top: 0.5rem;">
                    <strong style="color: #0f172a; font-size: 0.8125rem;">💡 Info Penguncian Gudang (Sesuai Arahan Mentor):</strong>
                    <p style="color: #64748b; font-size: 0.75rem; margin-top: 0.2rem; margin-bottom: 0;">
                        Jika Gudang Tugas dipilih (misal: <em>Gudang Magelang</em>), saat staf ini membuat PO Bahan Baku atau Permintaan Produksi, sistem akan <strong>mengunci pilihan gudang secara otomatis</strong> ke gudang tersebut agar tidak salah kirim barang.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahUser')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Akun Pengguna</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT USER --}}
<div id="modalEditUser" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Edit Akun Pengguna & Hak Akses</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalEditUser')">&times;</button>
        </div>
        <form id="formEditUser" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_karyawan_id" class="form-label">Tautkan Profil Karyawan</label>
                    <select id="edit_karyawan_id" name="karyawan_id" class="form-control">
                        <option value="">-- Tanpa Karyawan (Akun Umum/Sistem) --</option>
                        @foreach ($karyawanList as $k)
                            <option value="{{ $k->karyawan_id }}">
                                {{ $k->karyawan_nm }} ({{ $k->nik }} - {{ $k->departemen_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="edit_name" class="form-label">Nama Pengguna <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="edit_name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email" class="form-label">Email Login <span style="color:#ef4444;">*</span></label>
                        <input type="email" id="edit_email" name="email" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_password" class="form-label">Ganti Password (Kosongkan jika tidak diubah)</label>
                    <input type="password" id="edit_password" name="password" class="form-control" minlength="6" placeholder="Isi hanya jika ingin ganti password...">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="edit_role_cd" class="form-label">Peran Hak Akses (Role) <span style="color:#ef4444;">*</span></label>
                        <select id="edit_role_cd" name="role_cd" class="form-control" required>
                            @foreach ($roleList as $key => $label)
                                <option value="{{ $key }}">{{ $key }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_gudang_id" class="form-label">Gudang Tugas (Lokasi Pabrik)</label>
                        <select id="edit_gudang_id" name="gudang_id" class="form-control">
                            <option value="">-- Multi-Gudang (Bebas / Pusat) --</option>
                            @foreach ($gudangList as $gdg)
                                <option value="{{ $gdg->gudang_id }}">
                                    {{ $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditUser')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function autoFillKaryawan(selectElem) {
        const option = selectElem.options[selectElem.selectedIndex];
        if (option && option.value) {
            const nama = option.getAttribute('data-nama');
            const email = option.getAttribute('data-email');
            const dept = option.getAttribute('data-dept');

            if (nama) document.getElementById('create_name').value = nama;
            if (email) document.getElementById('create_email').value = email;

            // Auto match role based on department
            const roleSelect = document.getElementById('create_role_cd');
            if (dept === 'PRODUKSI') roleSelect.value = 'STAFF_PRODUKSI';
            else if (dept === 'GUDANG') roleSelect.value = 'ADMIN_GUDANG';
            else if (dept === 'PURCHASING') roleSelect.value = 'PURCHASING';
            else if (dept === 'FINANCE') roleSelect.value = 'FINANCE';
            else if (dept === 'QC') roleSelect.value = 'QC';
            else if (dept === 'MANAJEMEN') roleSelect.value = 'SUPERADMIN';
        }
    }

    function editUser(id, name, email, karyawanId, role, gudangId) {
        document.getElementById('formEditUser').action = '{{ url("pengguna-sistem") }}/' + id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_karyawan_id').value = karyawanId || '';
        document.getElementById('edit_role_cd').value = role;
        document.getElementById('edit_gudang_id').value = gudangId || '';
        document.getElementById('edit_password').value = '';
        openModal('modalEditUser');
    }
</script>
@endsection
