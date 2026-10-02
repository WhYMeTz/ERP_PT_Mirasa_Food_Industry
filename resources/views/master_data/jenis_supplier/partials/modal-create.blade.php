{{-- MODAL TAMBAH JENIS SUPPLIER --}}
<div id="modalTambahJenisSupplier" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Tambah Jenis Supplier Baru</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahJenisSupplier')">&times;</button>
        </div>
        <form action="{{ route('master.jenis_supplier.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="create_jenis_supplier_cd" class="form-label">Kode Jenis Supplier <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_jenis_supplier_cd" name="jenis_supplier_cd" class="form-control" placeholder="Contoh: RAW, BUMBU, PACK, SPAREPART" style="text-transform: uppercase;" required>
                    <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">Gunakan kode singkatan huruf kapital untuk memudahkan kategorisasi vendor.</small>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="create_jenis_supplier_nm" class="form-label">Nama Jenis Supplier <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_jenis_supplier_nm" name="jenis_supplier_nm" class="form-control" placeholder="Contoh: Bahan Baku Singkong & Minyak" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahJenisSupplier')">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Jenis Supplier</button>
            </div>
        </form>
    </div>
</div>
