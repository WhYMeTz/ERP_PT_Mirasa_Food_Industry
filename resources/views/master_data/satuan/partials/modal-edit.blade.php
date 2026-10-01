{{-- MODAL EDIT SATUAN --}}
<div id="modalEditSatuan" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 480px;">
        <div class="modal-header">
            <h2 class="modal-title">Edit Data Satuan</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalEditSatuan')">&times;</button>
        </div>
        <form id="formEditSatuan" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_satuan_cd" class="form-label">Kode Satuan <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_satuan_cd" name="satuan_cd" class="form-control" style="text-transform: uppercase;" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_satuan_nm" class="form-label">Nama Satuan <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_satuan_nm" name="satuan_nm" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditSatuan')">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
