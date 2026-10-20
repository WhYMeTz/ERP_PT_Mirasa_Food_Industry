{{-- MODAL POPUP DETAIL AKUN PENGGUNA --}}
<div id="modalDetailUser" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 680px;">
        <div class="modal-header" style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; padding: 1.15rem 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div id="detailUserAvatar" style="width: 44px; height: 44px; border-radius: 10px; background: #dc2626; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; font-weight: 800; box-shadow: 0 4px 10px rgba(220, 38, 38, 0.2);">
                    U
                </div>
                <div>
                    <h2 class="modal-title" id="detailModalTitle" style="font-size: 1.125rem; font-weight: 800; color: #0f172a; margin-bottom: 0.15rem;">
                        Detail Akun Pengguna
                    </h2>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span id="detailRoleBadge" class="badge" style="background: #fee2e2; color: #991b1b; font-size: 0.725rem; font-weight: 700;">
                            ROLE
                        </span>
                        <span id="detailStatusBadge" class="badge badge-success" style="font-size: 0.725rem;">
                            Aktif
                        </span>
                    </div>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalDetailUser')" style="font-size: 1.5rem; line-height: 1;">&times;</button>
        </div>

        <div class="modal-body" style="padding: 1.5rem; max-height: calc(85vh - 120px); overflow-y: auto;">
            {{-- Loading State --}}
            <div id="detailLoadingSpinner" style="text-align: center; padding: 2.5rem 1rem;">
                <div style="display: inline-block; width: 34px; height: 34px; border: 3px solid #e2e8f0; border-top-color: #dc2626; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
                <p style="color: #64748b; font-size: 0.825rem; margin-top: 0.75rem; font-weight: 600;">Memuat informasi akun pengguna...</p>
            </div>

            {{-- Detail Content Container --}}
            <div id="detailContentBody" style="display: none;">
                {{-- SECTION 1: KREDENSIAL LOGIN & PASSWORD (KHUSUS SUPERADMIN BISA LIHAT PASSWORD) --}}
                <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 1.25rem; margin-bottom: 1.25rem; box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem; border-bottom: 1px dashed #e2e8f0; padding-bottom: 0.65rem;">
                        <span style="font-size: 0.775rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #475569; display: flex; align-items: center; gap: 6px;">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Kredensial Login Akun
                        </span>
                        <span id="detailSecurityBadge" style="font-size: 0.7rem; font-weight: 700; color: #dc2626; background: #fef2f2; border: 1px solid #fecaca; padding: 0.2rem 0.55rem; border-radius: 9999px;">
                            Privilese Superadmin
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <span style="display: block; font-size: 0.725rem; color: #64748b; font-weight: 600; margin-bottom: 0.2rem;">Nama Lengkap Akun:</span>
                            <strong id="detailNamaVal" style="color: #0f172a; font-size: 0.95rem;">-</strong>
                        </div>

                        <div>
                            <span style="display: block; font-size: 0.725rem; color: #64748b; font-weight: 600; margin-bottom: 0.2rem;">Email Login:</span>
                            <span id="detailEmailVal" style="color: #0284c7; font-weight: 700; font-family: monospace; font-size: 0.875rem;">-</span>
                        </div>
                    </div>

                    {{-- PASSWORD CONTAINER (SUPERADMIN UNMASK / COPY FEATURE) --}}
                    <div style="margin-top: 1rem; padding-top: 0.85rem; border-top: 1px solid #f1f5f9;">
                        <span style="display: block; font-size: 0.725rem; color: #64748b; font-weight: 700; margin-bottom: 0.35rem;">
                            Kata Sandi (Password):
                        </span>

                        <div id="superadminPasswordBox" style="display: none;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="position: relative; flex: 1;">
                                    <input type="password" id="detailPasswordInput" readonly style="width: 100%; height: 40px; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.5rem 2.5rem 0.5rem 0.85rem; font-family: monospace; font-size: 0.95rem; font-weight: 700; color: #0f172a;">
                                    <button type="button" id="btnToggleDetailPwd" onclick="toggleDetailPasswordVisibility()" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #64748b; padding: 4px; display: flex;" title="Lihat/Sembunyikan Sandi">
                                        <svg id="eyeDetailIcon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </div>
                                <button type="button" id="btnCopyDetailPwd" onclick="copyDetailPassword()" style="height: 40px; padding: 0 0.85rem; background: #f1f5f9; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.8rem; font-weight: 700; color: #334155; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: all 0.15s ease;">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                    <span>Salin</span>
                                </button>
                            </div>
                            <span style="display: block; font-size: 0.685rem; color: #059669; font-weight: 600; margin-top: 0.35rem;">
                                🔒 Terdekripsi: Kata sandi ini hanya ditampilkan kepada pengguna dengan hak akses Superadmin.
                            </span>
                        </div>

                        <div id="restrictedPasswordBox" style="display: none;">
                            <div style="background: #f8fafc; border: 1px dashed #cbd5e1; padding: 0.5rem 0.85rem; border-radius: 8px; display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-family: monospace; font-size: 1rem; letter-spacing: 0.25em; color: #94a3b8;">••••••••••••</span>
                                <span style="font-size: 0.7rem; color: #dc2626; font-weight: 700;">Hanya Superadmin</span>
                            </div>
                            <span style="display: block; font-size: 0.685rem; color: #64748b; margin-top: 0.3rem;">
                                Kata sandi dienkripsi satu arah dan tidak dapat dilihat oleh peran selain Superadmin.
                            </span>
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: PROFIL KARYAWAN & HAK GUDANG --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    {{-- Card Karyawan --}}
                    <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 1.1rem; box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);">
                        <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #475569; display: flex; align-items: center; gap: 6px; margin-bottom: 0.65rem;">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Profil Karyawan Fisik
                        </span>
                        <div id="detailKaryawanFilled" style="display: none;">
                            <strong id="detailKaryawanNama" style="color: #0f172a; font-size: 0.9rem; display: block;">-</strong>
                            <span id="detailKaryawanNik" style="font-size: 0.75rem; color: #64748b; font-weight: 600; display: block; margin-top: 0.15rem;">NIK: -</span>
                            <span id="detailKaryawanDept" class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.7rem; font-weight: 700; margin-top: 0.4rem; display: inline-block;">DEPT</span>
                        </div>
                        <div id="detailKaryawanEmpty" style="color: #94a3b8; font-size: 0.8rem; font-style: italic; padding: 0.5rem 0;">
                            Akun Sistem Standalone (Tidak terikat master karyawan).
                        </div>
                    </div>

                    {{-- Card Gudang / Perusahaan --}}
                    <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 1.1rem; box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);">
                        <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #475569; display: flex; align-items: center; gap: 6px; margin-bottom: 0.65rem;">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Penugasan Entitas / Gudang
                        </span>
                        <div id="detailGudangsContainer" style="display: flex; flex-direction: column; gap: 0.35rem; max-height: 120px; overflow-y: auto;">
                            {{-- Injected dynamically --}}
                        </div>
                    </div>
                </div>

                {{-- SECTION 3: RINGKASAN HAK AKSES PERAN (PERMISSION PRIVILEGES) --}}
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 1.15rem; margin-bottom: 1.25rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.65rem;">
                        <span style="font-size: 0.775rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #334155; display: flex; align-items: center; gap: 6px;">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Hak Akses Modul (<span id="detailPermissionsCount">0</span> Izin Aktif)
                        </span>
                        <span id="detailRoleDesc" style="font-size: 0.725rem; color: #64748b; font-weight: 600;">
                            -
                        </span>
                    </div>

                    <div id="detailPermissionsChips" style="display: flex; flex-wrap: wrap; gap: 0.35rem; max-height: 150px; overflow-y: auto; padding: 0.25rem 0;">
                        {{-- Injected dynamically --}}
                    </div>
                </div>

                {{-- SECTION 4: AUDIT TRAIL LOG --}}
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.725rem; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 0.65rem;">
                    <span>Dibuat: <strong id="detailCreatedAt" style="color: #64748b;">-</strong></span>
                    <span>Diperbarui: <strong id="detailUpdatedAt" style="color: #64748b;">-</strong></span>
                </div>
            </div>
        </div>

        <div class="modal-footer" style="background: #f8fafc; border-top: 1.5px solid #e2e8f0; padding: 0.85rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 0.725rem; color: #64748b;">ID Pengguna: #<strong id="detailUserId">0</strong></span>
            <div style="display: flex; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalDetailUser')">Tutup</button>
                <button type="button" id="btnEditFromDetail" class="btn btn-primary" style="background: #0284c7; border-color: #0284c7;">
                    ✏️ Edit Akun
                </button>
            </div>
        </div>
    </div>
</div>
