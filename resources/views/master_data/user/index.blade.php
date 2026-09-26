@extends('layouts.app')

@section('title', 'Manajemen Pengguna & Hak Akses Gudang - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Manajemen Pengguna & Hak Akses</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Pengaturan akun login, hak akses (Role), dan penugasan lokasi gudang/pabrik (Gudang Magelang, Gudang Bahan Baku, dll).</p>
    </div>
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <button type="button" onclick="openRolePermissionModal('ADMIN_GUDANG')" class="btn btn-secondary" style="border: 1px solid #cbd5e1; color: #0284c7; font-weight: 700;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            🛡️ Atur Hak Akses Peran (Popup)
        </button>
        <button type="button" onclick="openModal('modalTambahUser')" class="btn btn-primary">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Tambah Akun Pengguna
        </button>
    </div>
</div>

{{-- TAB SWITCHER --}}
<div style="display: flex; gap: 0.5rem; margin-bottom: 1.25rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">
    <a href="{{ route('admin.users.index') }}" 
       style="padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.875rem; font-weight: 700; background: #0284c7; color: #ffffff;">
        👥 Daftar Akun Pengguna
    </a>
    <a href="{{ route('admin.users.permissions') }}" 
       style="padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.875rem; font-weight: 500; color: #64748b;">
        🛡️ Pengaturan Izin Peran (Permissions)
    </a>
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
                            @if ($item->role_cd === 'SUPERADMIN')
                                <div style="display: inline-flex; align-items: center; gap: 0.35rem; background: #fef2f2; border: 1px solid #fecaca; padding: 0.25rem 0.5rem; border-radius: 6px;">
                                    <span style="font-size: 0.85rem;">🌐</span>
                                    <div>
                                        <strong style="color: #991b1b; font-size: 0.8125rem;">Akses Seluruh Gudang</strong>
                                        <span style="display: block; font-size: 0.7rem; color: #b91c1c;">Super Administrator</span>
                                    </div>
                                </div>
                            @elseif ($item->assignedGudangs && $item->assignedGudangs->count() > 0)
                                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                    @foreach ($item->assignedGudangs as $ag)
                                        <span style="display: inline-flex; align-items: center; gap: 0.3rem; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 0.15rem 0.45rem; border-radius: 4px; font-size: 0.75rem; color: #166534; font-weight: 600;">
                                            <span>🔒</span> {{ $ag->display_name }} ({{ $ag->gudang_cd }})
                                        </span>
                                    @endforeach
                                    @if ($item->assignedGudangs->count() > 1)
                                        <span style="font-size: 0.7rem; color: #0284c7; font-weight: 700;">★ Mengelola {{ $item->assignedGudangs->count() }} Entitas</span>
                                    @endif
                                </div>
                            @elseif ($item->gudang)
                                <div style="display: inline-flex; align-items: center; gap: 0.35rem; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 0.2rem 0.5rem; border-radius: 6px;">
                                    <span style="font-size: 0.85rem;">🔒</span>
                                    <div>
                                        <strong style="color: #15803d; font-size: 0.8125rem;">{{ $item->gudang->display_name }}</strong>
                                        <span style="display: block; font-size: 0.7rem; color: #16a34a;">Kode: {{ $item->gudang->gudang_cd }}</span>
                                    </div>
                                </div>
                            @else
                                <div style="display: inline-flex; align-items: center; gap: 0.35rem; background: #fff7ed; border: 1px solid #fed7aa; padding: 0.2rem 0.5rem; border-radius: 6px;">
                                    <span style="font-size: 0.85rem;">⚠️</span>
                                    <div>
                                        <strong style="color: #c2410c; font-size: 0.8125rem;">Belum Ditugaskan</strong>
                                        <span style="display: block; font-size: 0.7rem; color: #ea580c;">Akses Terkunci / Terbatas</span>
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
                                @if ($item->role_cd !== 'SUPERADMIN')
                                    <button type="button" 
                                        class="btn btn-secondary btn-sm" 
                                        onclick="openRolePermissionModal('{{ $item->role_cd }}')"
                                        style="color: #0284c7;"
                                        title="Atur Hak Akses Role {{ $item->role_cd }}">
                                        🛡️ Izin
                                    </button>
                                @endif
                                <button type="button" 
                                    class="btn btn-secondary btn-sm" 
                                    onclick='editUser({{ $item->id }}, "{{ addslashes($item->name) }}", "{{ addslashes($item->email) }}", "{{ $item->karyawan_id ?? "" }}", "{{ $item->role_cd }}", "{{ $item->gudang_id ?? "" }}", @json($item->assignedGudangs->pluck("gudang_id")->toArray()))'
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

                <div class="form-group">
                    <label for="create_role_cd" class="form-label">Peran Hak Akses (Role) <span style="color:#ef4444;">*</span></label>
                    <select id="create_role_cd" name="role_cd" class="form-control" required>
                        @foreach ($roleList as $key => $label)
                            <option value="{{ $key }}" {{ old('role_cd', 'ADMIN_GUDANG') === $key ? 'selected' : '' }}>{{ $key }} - {{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-top: 1rem;">
                    <label class="form-label" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                        <span style="font-weight: 700; color: #0f172a;">🏭 Penugasan Gudang (Kelola 1, 2, atau Lebih Gudang)</span>
                        <span style="font-size: 0.75rem; color: #0284c7; font-weight: normal;">* Centang gudang yang diizinkan</span>
                    </label>
                    <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.75rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; max-height: 190px; overflow-y: auto;">
                        @foreach ($gudangList as $gdg)
                            <label style="display: flex; align-items: flex-start; gap: 0.5rem; margin: 0; cursor: pointer; font-size: 0.8125rem; color: #1e293b; background: #ffffff; padding: 0.45rem 0.6rem; border-radius: 4px; border: 1px solid #e2e8f0;">
                                <input type="checkbox" name="gudang_ids[]" value="{{ $gdg->gudang_id }}" class="create-gudang-checkbox" style="margin-top: 0.15rem;">
                                <div>
                                    <strong style="font-size: 0.8125rem; display: block; color: #0f172a;">{{ $gdg->display_name }}</strong>
                                    <span style="color: #64748b; font-size: 0.7rem;">Kode: {{ $gdg->gudang_cd }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <small style="color: #64748b; font-size: 0.75rem; margin-top: 0.25rem; display: block;">
                        💡 <strong>Isolasi Data:</strong> Admin Gudang ini hanya akan melihat stok, dokumen penerimaan, dan pengeluaran pada gudang yang dicentang. Data gudang lain tidak akan tercampur dan tidak terlihat.
                    </small>
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
            <input type="hidden" name="id" id="edit_user_id">
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

                <div class="form-group">
                    <label for="edit_role_cd" class="form-label">Peran Hak Akses (Role) <span style="color:#ef4444;">*</span></label>
                    <select id="edit_role_cd" name="role_cd" class="form-control" required>
                        @foreach ($roleList as $key => $label)
                            <option value="{{ $key }}">{{ $key }} - {{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-top: 1rem;">
                    <label class="form-label" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                        <span style="font-weight: 700; color: #0f172a;">🏭 Penugasan Gudang (Kelola 1, 2, atau Lebih Gudang)</span>
                        <span style="font-size: 0.75rem; color: #0284c7; font-weight: normal;">* Centang gudang yang diizinkan</span>
                    </label>
                    <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.75rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; max-height: 190px; overflow-y: auto;">
                        @foreach ($gudangList as $gdg)
                            <label style="display: flex; align-items: flex-start; gap: 0.5rem; margin: 0; cursor: pointer; font-size: 0.8125rem; color: #1e293b; background: #ffffff; padding: 0.45rem 0.6rem; border-radius: 4px; border: 1px solid #e2e8f0;">
                                <input type="checkbox" name="gudang_ids[]" value="{{ $gdg->gudang_id }}" class="edit-gudang-checkbox" id="edit_gudang_{{ $gdg->gudang_id }}" style="margin-top: 0.15rem;">
                                <div>
                                    <strong style="font-size: 0.8125rem; display: block; color: #0f172a;">{{ $gdg->display_name }}</strong>
                                    <span style="color: #64748b; font-size: 0.7rem;">Kode: {{ $gdg->gudang_cd }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <small style="color: #64748b; font-size: 0.75rem; margin-top: 0.25rem; display: block;">
                        💡 <strong>Isolasi Data:</strong> Admin Gudang ini hanya akan melihat stok, dokumen penerimaan, dan pengeluaran pada gudang yang dicentang. Data gudang lain tidak akan tercampur dan tidak terlihat.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditUser')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL POPUP ATUR HAK AKSES PERAN --}}
<div id="modalAturIzinPeran" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 760px;">
        <div class="modal-header">
            <div>
                <h2 class="modal-title" style="display: flex; align-items: center; gap: 0.5rem;">
                    <span>🛡️ Pengaturan Hak Akses Peran:</span>
                    <span id="labelModalRoleName" style="color: #0284c7;">Admin Gudang</span>
                </h2>
                <p style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem;">
                    Centang menu dan aksi operasional yang diizinkan untuk peran ini.
                </p>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalAturIzinPeran')">&times;</button>
        </div>

        <form action="{{ route('admin.users.permissions.update') }}" method="POST">
            @csrf
            <input type="hidden" id="modal_single_role" name="single_role" value="ADMIN_GUDANG">

            <div class="modal-body" style="padding: 1.25rem 1.5rem;">
                {{-- ROLE TABS DI DALAM POPUP --}}
                <div style="display: flex; gap: 0.35rem; margin-bottom: 1.25rem; flex-wrap: wrap; background: #f1f5f9; padding: 0.35rem; border-radius: 8px;">
                    @foreach ($matrix as $roleKey => $roleInfo)
                        <button type="button" 
                                id="btnRoleTab_{{ $roleKey }}" 
                                onclick="switchRoleInModal('{{ $roleKey }}')"
                                class="role-tab-btn"
                                style="padding: 0.35rem 0.75rem; font-size: 0.8rem; border-radius: 6px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; background: transparent; color: #475569;">
                            {{ $roleKey }}
                        </button>
                    @endforeach
                </div>

                {{-- QUICK SELECT ALL / DESELECT ALL --}}
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px dashed #e2e8f0;">
                    <span style="font-size: 0.8rem; color: #64748b; font-weight: 600;">Daftar Hak Akses & Menu Terkait:</span>
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="button" onclick="toggleAllModalCheckboxes(true)" class="btn btn-secondary btn-sm" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">
                            ✓ Centang Semua
                        </button>
                        <button type="button" onclick="toggleAllModalCheckboxes(false)" class="btn btn-secondary btn-sm" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">
                            ✕ Kosongkan
                        </button>
                    </div>
                </div>

                {{-- PERMISSIONS CHECKBOX GROUPS --}}
                <div style="display: flex; flex-direction: column; gap: 1rem; max-height: 50vh; overflow-y: auto; padding-right: 0.25rem;">
                    @foreach ($modules as $modKey => $module)
                        <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                            <div style="background: #f8fafc; padding: 0.5rem 0.85rem; font-size: 0.8125rem; font-weight: 700; color: #334155; border-bottom: 1px solid #e2e8f0;">
                                {{ $module['title'] }}
                            </div>
                            <div style="padding: 0.65rem 0.85rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                @foreach ($module['items'] as $itemKey => $item)
                                    <label style="display: flex; align-items: flex-start; gap: 0.5rem; padding: 0.45rem; border-radius: 6px; border: 1px solid #f1f5f9; background: #ffffff; cursor: pointer; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#ffffff'">
                                        <input type="checkbox" 
                                               class="modal-perm-checkbox" 
                                               id="chk_perm_{{ $itemKey }}" 
                                               name="permissions[]" 
                                               value="{{ $itemKey }}" 
                                               style="width: 17px; height: 17px; accent-color: #0284c7; margin-top: 0.15rem; cursor: pointer;">
                                        <div style="font-size: 0.8rem; line-height: 1.3;">
                                            <strong style="color: #0f172a; display: block;">{{ $item['label'] }}</strong>
                                            <span style="color: #64748b; font-size: 0.7rem;">{{ $item['desc'] }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalAturIzinPeran')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.5rem; font-weight: 700;">
                    💾 Simpan Hak Akses <span id="btnModalRoleName"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const rolePermissionMatrix = @json($matrix);

    function openRolePermissionModal(roleCd) {
        if (!roleCd || roleCd === 'SUPERADMIN') {
            roleCd = 'ADMIN_GUDANG';
        }
        switchRoleInModal(roleCd);
        openModal('modalAturIzinPeran');
    }

    function switchRoleInModal(roleCd) {
        document.getElementById('modal_single_role').value = roleCd;
        
        const roleData = rolePermissionMatrix[roleCd] || { name: roleCd, permissions: {} };
        document.getElementById('labelModalRoleName').textContent = roleData.name || roleCd;
        document.getElementById('btnModalRoleName').textContent = `(${roleCd})`;

        // Update tabs styling
        document.querySelectorAll('.role-tab-btn').forEach(btn => {
            btn.style.background = 'transparent';
            btn.style.color = '#475569';
            btn.style.boxShadow = 'none';
        });
        const activeBtn = document.getElementById('btnRoleTab_' + roleCd);
        if (activeBtn) {
            activeBtn.style.background = '#ffffff';
            activeBtn.style.color = '#0284c7';
            activeBtn.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
        }

        // Update checkboxes
        const activePerms = roleData.permissions || {};
        document.querySelectorAll('.modal-perm-checkbox').forEach(chk => {
            const permKey = chk.value;
            chk.checked = !!activePerms[permKey];
        });
    }

    function toggleAllModalCheckboxes(check) {
        document.querySelectorAll('.modal-perm-checkbox').forEach(chk => {
            chk.checked = check;
        });
    }

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

    function editUser(id, name, email, karyawanId, role, gudangId, assignedGudangIds) {
        document.getElementById('formEditUser').action = '{{ url("pengguna-sistem") }}/' + id;
        document.getElementById('edit_user_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_karyawan_id').value = karyawanId || '';
        document.getElementById('edit_role_cd').value = role;
        document.getElementById('edit_password').value = '';

        // Reset semua checkbox gudang
        document.querySelectorAll('.edit-gudang-checkbox').forEach(function(chk) {
            chk.checked = false;
        });

        // Centang gudang yang telah di-assign
        var ids = Array.isArray(assignedGudangIds) ? assignedGudangIds : [];
        if (ids.length === 0 && gudangId) {
            ids = [parseInt(gudangId)];
        }

        ids.forEach(function(gid) {
            var chk = document.getElementById('edit_gudang_' + gid);
            if (chk) {
                chk.checked = true;
            }
        });

        openModal('modalEditUser');
    }
</script>
@endsection
