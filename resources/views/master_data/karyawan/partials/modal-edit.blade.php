{{-- MODAL EDIT DATA KARYAWAN --}}
<div id="modalEditKaryawan" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 600px;">
        <div class="modal-header">
            <h2 class="modal-title">Edit Data Karyawan</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalEditKaryawan')">&times;</button>
        </div>
        <form id="formEditKaryawan" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_nik" class="form-label">Nomor Induk Karyawan (NIK) <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_nik" name="nik" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_karyawan_nm" class="form-label">Nama Lengkap Karyawan <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_karyawan_nm" name="karyawan_nm" class="form-control" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="edit_departemen_cd" class="form-label">Departemen <span style="color:#ef4444;">*</span></label>
                        <select id="edit_departemen_cd" name="departemen_cd" class="form-control" required>
                            @foreach ($departemenList as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_jabatan_nm" class="form-label">Jabatan Kerja <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="edit_jabatan_nm" name="jabatan_nm" class="form-control" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="edit_telepon_no" class="form-label">Nomor Telepon / WhatsApp</label>
                        <input type="text" id="edit_telepon_no" name="telepon_no" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" id="edit_email" name="email" class="form-control">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_alamat_txt" class="form-label">Alamat Domisili</label>
                    <textarea id="edit_alamat_txt" name="alamat_txt" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditKaryawan')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
