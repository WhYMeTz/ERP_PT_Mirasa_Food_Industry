{{-- MODAL KONFIRMASI HAPUS / NONAKTIFKAN KARYAWAN --}}
<div id="modalDeleteKaryawan" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 480px;">
        <div class="modal-header" style="background: #fef2f2; border-bottom: 1px solid #fee2e2;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h2 class="modal-title" style="color: #991b1b; font-size: 1.05rem;">Konfirmasi Nonaktifkan Karyawan</h2>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalDeleteKaryawan')">&times;</button>
        </div>
        <form id="formDeleteKaryawan" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-body" style="padding: 1.25rem;">
                <p style="font-size: 0.875rem; color: #475569; margin-bottom: 0.85rem; line-height: 1.5;">
                    Apakah Anda yakin ingin menonaktifkan data karyawan berikut?
                </p>
                <div id="deleteKaryawanInfo" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.875rem; color: #1e293b;">
                    {{-- Diisi secara dinamis oleh JavaScript --}}
                </div>
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 0.65rem 0.85rem; display: flex; gap: 0.5rem; align-items: flex-start;">
                    <span style="font-size: 1rem; line-height: 1;">⚠️</span>
                    <span style="font-size: 0.775rem; color: #92400e; line-height: 1.4;">
                        Karyawan yang dinonaktifkan tidak akan muncul pada daftar penugasan SPK dan akun pengguna yang tertaut akan kehilangan akses profil.
                    </span>
                </div>
            </div>
            <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalDeleteKaryawan')">Batal</button>
                <button type="submit" class="btn btn-danger" style="background: #dc2626; border-color: #dc2626;">Ya, Nonaktifkan Karyawan</button>
            </div>
        </form>
    </div>
</div>
