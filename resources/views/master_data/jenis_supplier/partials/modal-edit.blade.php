{{-- MODAL EDIT JENIS SUPPLIER --}}
<div id="modalEditJenisSupplier" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Edit Data Jenis Supplier</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalEditJenisSupplier')">&times;</button>
        </div>
        <form id="formEditJenisSupplier" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_jenis_supplier_cd" class="form-label">Kode Jenis Supplier <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_jenis_supplier_cd" name="jenis_supplier_cd" class="form-control" style="text-transform: uppercase;" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_jenis_supplier_nm" class="form-label">Nama Jenis Supplier <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_jenis_supplier_nm" name="jenis_supplier_nm" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditJenisSupplier')">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
