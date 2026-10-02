{{-- MODAL EDIT DATA PERUSAHAAN / ENTITAS --}}
<div id="modalEditPerusahaan" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Edit Data Perusahaan / Entitas</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalEditPerusahaan')">&times;</button>
        </div>
        <form id="formEditPerusahaan" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_perusahaan_nm" class="form-label">Nama Perusahaan / Entitas Bisnis <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_perusahaan_nm" name="gudang_nm" class="form-control" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="edit_perusahaan_cd" class="form-label">Kode Perusahaan <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="edit_perusahaan_cd" name="gudang_cd" class="form-control" style="text-transform: uppercase;" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_tipe_perusahaan_cd" class="form-label">Tipe Entitas</label>
                        <select id="edit_tipe_perusahaan_cd" name="tipe_gudang_cd" class="form-control">
                            <option value="Pusat">Kantor / Pabrik Pusat</option>
                            <option value="Cabang">Kantor Cabang</option>
                            <option value="Anak Perusahaan">Anak Perusahaan</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_perusahaan_telepon" class="form-label">No. Telepon / Kontak</label>
                    <input type="text" id="edit_perusahaan_telepon" name="telepon" class="form-control">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_perusahaan_alamat" class="form-label">Alamat Kantor / Pabrik</label>
                    <textarea id="edit_perusahaan_alamat" name="alamat_txt" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditPerusahaan')">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
