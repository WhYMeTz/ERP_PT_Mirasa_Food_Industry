{{-- MODAL KELOLA SELURUH ROLE / PERAN --}}
<div id="modalKelolaRoles" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 780px;">
        <div class="modal-header">
            <h2 class="modal-title" style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Kelola Daftar Peran (Roles) Sistem</span>
            </h2>
            <button type="button" class="modal-close" onclick="closeModal('modalKelolaRoles')">&times;</button>
        </div>
        <div class="modal-body" style="padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                <p style="font-size: 0.8125rem; color: #64748b; margin: 0;">
                    Peran bawaan sistem diproteksi untuk integritas modul. Anda dapat menambah peran kustom sesuai kebutuhan struktur operasional PT Mirasa.
                </p>
                <button type="button" onclick="closeModal('modalKelolaRoles'); openModal('modalTambahRole');" class="btn btn-primary btn-sm" style="font-weight: 700;">
                    + Tambah Peran Baru
                </button>
            </div>

            <div style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.8125rem;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569;">
                            <th style="padding: 0.6rem 0.75rem; text-align: left;">Kode &amp; Nama Peran</th>
                            <th style="padding: 0.6rem 0.75rem; text-align: left;">Deskripsi</th>
                            <th style="padding: 0.6rem 0.75rem; text-align: center; width: 100px;">Tipe</th>
                            <th style="padding: 0.6rem 0.75rem; text-align: center; width: 90px;">Pengguna</th>
                            <th style="padding: 0.6rem 0.75rem; text-align: center; width: 130px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allRoles as $role)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 0.65rem 0.75rem;">
                                    <div style="font-weight: 800; color: #0284c7; font-family: monospace; font-size: 0.875rem;">
                                        {{ $role->role_cd }}
                                    </div>
                                    <div style="font-weight: 600; color: #0f172a; margin-top: 0.15rem;">
                                        {{ $role->role_nm }}
                                    </div>
                                </td>
                                <td style="padding: 0.65rem 0.75rem; color: #64748b; max-width: 250px;">
                                    {{ $role->desc_txt ?? '-' }}
                                </td>
                                <td style="padding: 0.65rem 0.75rem; text-align: center;">
                                    @if ($role->is_system)
                                        <span style="background: #e2e8f0; color: #334155; font-size: 0.7rem; font-weight: 800; padding: 0.2rem 0.5rem; border-radius: 4px;">
                                            Sistem
                                        </span>
                                    @else
                                        <span style="background: #ecfdf5; color: #059669; font-size: 0.7rem; font-weight: 800; padding: 0.2rem 0.5rem; border-radius: 4px; border: 1px solid #a7f3d0;">
                                            Kustom
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 0.65rem 0.75rem; text-align: center; font-weight: 700; color: #334155;">
                                    {{ $role->users_count ?? 0 }} akun
                                </td>
                                <td style="padding: 0.65rem 0.75rem; text-align: center;">
                                    <div style="display: inline-flex; gap: 0.35rem; align-items: center;">
                                        <button type="button" 
                                            class="btn btn-secondary btn-sm" 
                                            style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                            onclick='editRoleData({{ $role->role_id }}, "{{ addslashes($role->role_cd) }}", "{{ addslashes($role->role_nm) }}", "{{ addslashes($role->desc_txt ?? "") }}")'
                                            title="Ubah Nama/Deskripsi">
                                            Edit
                                        </button>
                                        @if (!$role->is_system)
                                            <button type="button" 
                                                class="btn btn-danger btn-sm" 
                                                style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                                onclick='openDeleteRoleModal({{ $role->role_id }}, "{{ addslashes($role->role_cd) }}", "{{ addslashes($role->role_nm) }}", {{ $role->users_count ?? 0 }})'
                                                title="Hapus Peran">
                                                Hapus
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer" style="padding: 0.75rem 1.25rem;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modalKelolaRoles')">Tutup</button>
        </div>
    </div>
</div>
