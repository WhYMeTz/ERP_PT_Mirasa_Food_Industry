{{-- MODAL EDIT CUSTOMER --}}
<div id="modalEditCustomer" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Edit Data Customer</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalEditCustomer')">&times;</button>
        </div>
        <form id="formEditCustomer" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_customer_cd" class="form-label">Kode Customer <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_customer_cd" name="customer_cd" class="form-control" style="text-transform: uppercase;" required>
                </div>
                <div class="form-group">
                    <label for="edit_customer_nm" class="form-label">Nama Customer <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_customer_nm" name="customer_nm" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_customer_kontak" class="form-label">Kontak PIC / No Telepon</label>
                    <input type="text" id="edit_customer_kontak" name="kontak_no" class="form-control">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_customer_alamat" class="form-label">Alamat Pengiriman</label>
                    <textarea id="edit_customer_alamat" name="alamat_txt" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditCustomer')">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
