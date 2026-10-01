{{-- MODAL EDIT JENIS BARANG --}}
<div id="modalEditJenis" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 480px;">
        <div class="modal-header">
            <h2 class="modal-title">Edit Jenis Barang</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalEditJenis')">&times;</button>
        </div>
        <form id="formEditJenis" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_jenis_cd" class="form-label">Kode Jenis Barang <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_jenis_cd" name="jenis_barang_cd" class="form-control" style="text-transform: uppercase;" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_jenis_nm" class="form-label">Nama Jenis Barang <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_jenis_nm" name="jenis_barang_nm" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditJenis')">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
