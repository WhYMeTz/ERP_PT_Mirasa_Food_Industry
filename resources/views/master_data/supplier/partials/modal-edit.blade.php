{{-- MODAL EDIT SUPPLIER --}}
<div id="modalEditSupplier" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Edit Data Supplier</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalEditSupplier')">&times;</button>
        </div>
        <form id="formEditSupplier" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_supplier_cd" class="form-label">Kode Supplier <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_supplier_cd" name="supplier_cd" class="form-control" style="text-transform: uppercase;" required>
                </div>
                <div class="form-group">
                    <label for="edit_supplier_nm" class="form-label">Nama Supplier / Mitra <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_supplier_nm" name="supplier_nm" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_jenis_supplier_id" class="form-label">Jenis Supplier</label>
                    <select id="edit_jenis_supplier_id" name="jenis_supplier_id" class="form-control">
                        <option value="">-- Pilih Jenis Supplier (Opsional) --</option>
                        @foreach ($jenisSupplierList as $js)
                            <option value="{{ $js->jenis_supplier_id }}">{{ $js->jenis_supplier_nm }} ({{ $js->jenis_supplier_cd }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_kontak_no" class="form-label">Kontak / No Telepon</label>
                    <input type="text" id="edit_kontak_no" name="kontak_no" class="form-control">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_alamat_txt" class="form-label">Alamat Lengkap</label>
                    <textarea id="edit_alamat_txt" name="alamat_txt" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditSupplier')">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
