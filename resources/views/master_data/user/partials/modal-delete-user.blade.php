{{-- MODAL KONFIRMASI NONAKTIFKAN AKUN PENGGUNA --}}
<div id="modalDeleteUser" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 440px;">
        <div class="modal-header" style="background: #fef2f2; border-bottom: 1px solid #fecaca;">
            <h3 class="modal-title" style="color: #991b1b; display: flex; align-items: center; gap: 0.5rem; font-size: 1rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Konfirmasi Nonaktifkan Akun</span>
            </h3>
            <button type="button" class="modal-close" onclick="closeModal('modalDeleteUser')">&times;</button>
        </div>
        <form id="formDeleteUser" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="modal-body" style="padding: 1.25rem;">
                <p style="font-size: 0.875rem; color: #334155; margin-bottom: 0.75rem;">
                    Apakah Anda yakin ingin menonaktifkan akun login pengguna berikut?
                </p>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem; margin-bottom: 0.75rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                        <span id="deleteUserRoleBadge" class="badge" style="background: #e2e8f0; color: #334155; font-size: 0.7rem; font-weight: 700;">ROLE</span>
                        <span style="font-size: 0.7rem; color: #94a3b8; font-family: monospace;">ID: #<span id="deleteUserId">0</span></span>
                    </div>
                    <div style="font-weight: 700; color: #0f172a; font-size: 0.95rem;" id="deleteUserName">-</div>
                    <div style="font-family: monospace; font-size: 0.8rem; color: #0284c7; margin-top: 0.15rem;" id="deleteUserEmail">-</div>
                </div>
                <small style="color: #dc2626; font-size: 0.75rem; display: block; line-height: 1.4;">
                    ⚠️ Pengguna yang dinonaktifkan tidak akan bisa masuk (login) ke modul web maupun mobile. Riwayat transaksi sebelumnya tetap tersimpan utuh di sistem.
                </small>
            </div>
            <div class="modal-footer" style="padding: 0.75rem 1.25rem; background: #fafafa; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('modalDeleteUser')">Batal</button>
                <button type="submit" class="btn btn-danger btn-sm" style="font-weight: 700;">
                    Ya, Nonaktifkan Akun
                </button>
            </div>
        </form>
    </div>
</div>
