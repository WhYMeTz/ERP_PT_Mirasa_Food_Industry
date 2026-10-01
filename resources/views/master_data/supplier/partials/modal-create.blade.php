{{-- MODAL TAMBAH SUPPLIER --}}
<div id="modalTambahSupplier" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Tambah Supplier Baru</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahSupplier')">&times;</button>
        </div>
        <form action="{{ route('master.supplier.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.375rem;">
                        <label for="create_supplier_cd" class="form-label" style="margin-bottom: 0;">Kode Supplier <span style="color:#ef4444;">*</span></label>
                        <button type="button" class="btn btn-secondary btn-sm" data-target="create_supplier_cd" onclick="const opt = document.getElementById('create_jenis_supplier_id').options[document.getElementById('create_jenis_supplier_id').selectedIndex]; fetchNextCode('supplier', 'create_supplier_cd', {name: document.getElementById('create_supplier_nm').value, jenis: opt?.dataset?.cd || ''})" style="padding: 0.2rem 0.6rem; font-size: 0.75rem;" title="Generate Kode Otomatis">
                            ↺ Auto Generate
                        </button>
                    </div>
                    <input type="text" id="create_supplier_cd" name="supplier_cd" value="{{ $nextSupplierCode ?? '' }}" class="form-control" placeholder="Contoh: SKG-UNT atau SUP-SMT" style="text-transform: uppercase;" required>
                    <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">Petani Singkong otomatis berawalan SKG- (misal: SKG-UNT), Vendor Industri berawalan SUP- (misal: SUP-SMT).</small>
                </div>
                <div class="form-group">
                    <label for="create_jenis_supplier_id" class="form-label">Jenis Supplier <span style="color:#ef4444;">*</span></label>
                    <select id="create_jenis_supplier_id" name="jenis_supplier_id" class="form-control" onchange="const opt = this.options[this.selectedIndex]; fetchNextCode('supplier', 'create_supplier_cd', {name: document.getElementById('create_supplier_nm')?.value || '', jenis: opt?.dataset?.cd || ''})" required>
                        <option value="">-- Pilih Jenis Supplier --</option>
                        @foreach ($jenisSupplierList as $js)
                            <option value="{{ $js->jenis_supplier_id }}" data-cd="{{ $js->jenis_supplier_cd }}">{{ $js->jenis_supplier_nm }} ({{ $js->jenis_supplier_cd }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="create_supplier_nm" class="form-label">Nama Supplier / Mitra <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_supplier_nm" name="supplier_nm" class="form-control" placeholder="Contoh: UNTUNG atau PT. SMART TBK" oninput="const opt = document.getElementById('create_jenis_supplier_id').options[document.getElementById('create_jenis_supplier_id').selectedIndex]; debounceCodeFromName('supplier', 'create_supplier_nm', 'create_supplier_cd', {jenis: opt?.dataset?.cd || ''})" required>
                </div>
                <div class="form-group">
                    <label for="create_kontak_no" class="form-label">Kontak / No Telepon (WhatsApp)</label>
                    <input type="text" id="create_kontak_no" name="kontak_no" class="form-control" placeholder="Contoh: 081234567890">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="create_alamat_txt" class="form-label">Alamat Lengkap</label>
                    <textarea id="create_alamat_txt" name="alamat_txt" class="form-control" rows="2" placeholder="Contoh: Desa Sukamaju, RT 02/05, Kec. Wonosobo"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahSupplier')">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Supplier</button>
            </div>
        </form>
    </div>
</div>
