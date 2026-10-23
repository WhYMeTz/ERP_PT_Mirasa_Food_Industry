{{-- Modal Konfirmasi Hapus Akun (COA) --}}
<div id="modalDeleteCoa" class="modal-backdrop-custom" style="display: none;">
    <div class="modal-dialog-custom" style="max-width: 440px;">
        <div class="modal-header-custom" style="background: #fef2f2; border-bottom: 1.5px solid #fee2e2;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">
                    🗑️
                </div>
                <div>
                    <h3 class="modal-title-custom" style="color: #991b1b;">Hapus Akun Keuangan?</h3>
                    <p style="font-size: 0.725rem; color: #b91c1c; margin: 2px 0 0 0;">
                        Tindakan ini akan menonaktifkan akun dari Bagan Akun
                    </p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeDeleteModal()">&times;</button>
        </div>

        <form action="" method="POST" id="formDeleteCoa">
            @csrf
            @method('DELETE')

            <div class="modal-body-custom" style="padding: 1.25rem;">
                <p style="font-size: 0.85rem; color: #334155; margin: 0 0 0.75rem 0; line-height: 1.45;">
                    Apakah Anda yakin ingin menghapus akun berikut?
                </p>

                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 0.75rem;">
                    <strong id="deleteAccountName" style="color: #0f172a; font-size: 0.925rem; display: block;">
                        -
                    </strong>
                </div>

                <div style="font-size: 0.75rem; color: #64748b; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 6px; padding: 0.5rem 0.75rem;">
                    ⚠️ <em>Catatan: Akun yang masih memiliki saldo berjalan tidak dapat dihapus demi menjaga keseimbangan neraca keuangan.</em>
                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()" style="font-weight: 700; font-size: 0.825rem;">
                    Batal
                </button>
                <button type="submit" class="btn btn-danger" style="font-weight: 700; font-size: 0.825rem; background: #dc2626; border-color: #dc2626; color: #ffffff;">
                    🗑️ Ya, Hapus Akun
                </button>
            </div>
        </form>
    </div>
</div>
