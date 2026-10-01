{{-- MODAL EDIT DATA ROLE --}}
<div id="modalEditRole" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 500px;">
        <div class="modal-header">
            <h2 class="modal-title" style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Data Peran (Role)</span>
            </h2>
            <button type="button" class="modal-close" onclick="closeModal('modalEditRole')">&times;</button>
        </div>
        <form id="formEditRole" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Kode Peran (Role Code)</label>
                    <input type="text" id="edit_role_code" class="form-control" style="background: #f1f5f9; cursor: not-allowed; font-weight: 700;" readonly>
                    <small style="color: #64748b; font-size: 0.75rem;">Kode peran adalah identifier unik sistem dan tidak dapat diubah.</small>
                </div>

                <div class="form-group">
                    <label for="edit_role_name" class="form-label">Nama Peran Lengkap <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_role_name" name="role_nm" class="form-control" required>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_role_desc" class="form-label">Deskripsi &amp; Tanggung Jawab</label>
                    <textarea id="edit_role_desc" name="desc_txt" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditRole'); openModal('modalKelolaRoles');">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
