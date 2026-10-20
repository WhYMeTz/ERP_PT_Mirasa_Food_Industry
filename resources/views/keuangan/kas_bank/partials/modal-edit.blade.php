{{-- Modal Edit Akun Kas / Rekening Bank --}}
<div id="modalEditKasBank" class="kb-modal-backdrop">
    <div class="kb-modal">
        <div class="kb-modal-header">
            <h3 class="kb-modal-title">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Informasi Rekening / Kas</span>
            </h3>
            <button type="button" class="kb-modal-close" onclick="closeModal('modalEditKasBank')">&times;</button>
        </div>
        <form id="formEditKasBank" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="kb-modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div class="kb-form-group">
                        <label class="kb-form-label">Kode Akun (Permanen)</label>
                        <input type="text" id="edit_kode_akun_display" class="kb-form-input" disabled style="background-color: var(--kb-gray-100); font-family: monospace; font-weight: 700;">
                    </div>
                    <div class="kb-form-group">
                        <label class="kb-form-label">Saldo Berjalan Saat Ini</label>
                        <input type="text" id="edit_saldo_berjalan_display" class="kb-form-input" disabled style="background-color: var(--kb-gray-100); font-family: monospace; font-weight: 700; color: var(--kb-primary);">
                    </div>
                </div>

                <div class="kb-form-group">
                    <label class="kb-form-label">Tipe Akun Keuangan</label>
                    <input type="text" id="edit_tipe_akun" class="kb-form-input" disabled style="background-color: var(--kb-gray-100); font-weight: 600;">
                </div>

                <div class="kb-form-group">
                    <label class="kb-form-label">Nama Akun <span class="req">*</span></label>
                    <input type="text" name="nama_akun" id="edit_nama_akun" class="kb-form-input" required maxlength="150">
                </div>

                <div id="edit_bank_section">
                    <div style="display: grid; grid-template-columns: 1fr 1.25fr; gap: 0.75rem;">
                        <div class="kb-form-group">
                            <label class="kb-form-label">Nama Bank</label>
                            <input type="text" name="bank_nama" id="edit_bank_nama" class="kb-form-input" maxlength="100">
                        </div>
                        <div class="kb-form-group">
                            <label class="kb-form-label">Nomor Rekening</label>
                            <input type="text" name="nomor_rekening" id="edit_nomor_rekening" class="kb-form-input" maxlength="100">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1.25fr 1fr; gap: 0.75rem;">
                        <div class="kb-form-group">
                            <label class="kb-form-label">Atas Nama Rekening</label>
                            <input type="text" name="atas_nama" id="edit_atas_nama" class="kb-form-input" maxlength="150">
                        </div>
                        <div class="kb-form-group">
                            <label class="kb-form-label">Kantor Cabang</label>
                            <input type="text" name="cabang" id="edit_cabang" class="kb-form-input" maxlength="100">
                        </div>
                    </div>
                </div>

                <div class="kb-form-group">
                    <label class="kb-form-label">Catatan / Keterangan</label>
                    <textarea name="keterangan" id="edit_keterangan" class="kb-form-textarea" rows="2"></textarea>
                </div>
            </div>
            <div class="kb-modal-footer">
                <button type="button" class="kb-btn kb-btn-secondary" onclick="closeModal('modalEditKasBank')">Batal</button>
                <button type="submit" class="kb-btn kb-btn-primary">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Perbarui Informasi</span>
                </button>
            </div>
        </form>
    </div>
</div>
