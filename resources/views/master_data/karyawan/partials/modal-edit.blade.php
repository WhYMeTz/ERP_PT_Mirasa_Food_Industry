{{-- MODAL EDIT DATA KARYAWAN --}}
<style>
    #modalEditKaryawan .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem 1.25rem;
    }
    #modalEditKaryawan .form-grid .form-group {
        margin-bottom: 0;
    }
    #modalEditKaryawan .form-grid .col-full {
        grid-column: 1 / -1;
    }
    @media (max-width: 600px) {
        #modalEditKaryawan .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div id="modalEditKaryawan" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 700px;">
        <div class="modal-header">
            <h2 class="modal-title">Edit Data Karyawan</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalEditKaryawan')">&times;</button>
        </div>
        <form id="formEditKaryawan" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div class="form-grid">

                    <div class="form-group">
                        <label for="edit_nik" class="form-label">NIK / ID Karyawan <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="edit_nik" name="nik" class="form-control" required placeholder="Contoh: AC1A117" oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div class="form-group">
                        <label for="edit_id_absensi" class="form-label">ID Absensi</label>
                        <input type="text" id="edit_id_absensi" name="id_absensi" class="form-control" placeholder="ID di mesin absen">
                    </div>

                    <div class="form-group col-full">
                        <label for="edit_karyawan_nm" class="form-label">Nama Lengkap Karyawan <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="edit_karyawan_nm" name="karyawan_nm" class="form-control" required placeholder="Contoh: Budi Santoso">
                    </div>

                    <div class="form-group">
                        <label for="edit_departemen_cd" class="form-label">Bagian / Divisi <span style="color:#ef4444;">*</span></label>
                        <select id="edit_departemen_cd" name="departemen_cd" class="form-control" required>
                            <option value="">-- Pilih Departemen --</option>
                            @foreach ($departemenList as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_jabatan_nm" class="form-label">Jabatan Kerja <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="edit_jabatan_nm" name="jabatan_nm" class="form-control" required placeholder="Contoh: Mandor Produksi">
                    </div>

                    <div class="form-group">
                        <label for="edit_kelompok" class="form-label">Kelompok</label>
                        <select id="edit_kelompok" name="kelompok" class="form-control">
                            <option value="">-- Pilih --</option>
                            @foreach (\App\Models\MasterData\MstKaryawan::KELOMPOK as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_shift" class="form-label">Shift</label>
                        <select id="edit_shift" name="shift" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option value="SHIFT A">Shift A</option>
                            <option value="SHIFT B">Shift B</option>
                            <option value="NON SHIFT">Non Shift</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_status_kerja" class="form-label">Status Karyawan</label>
                        <select id="edit_status_kerja" name="status_kerja" class="form-control">
                            <option value="Tetap">Tetap</option>
                            <option value="Kontrak">Kontrak</option>
                            <option value="Harian">Harian</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_tgl_masuk" class="form-label">Tanggal Masuk Kerja</label>
                        <input type="date" id="edit_tgl_masuk" name="tgl_masuk" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="edit_telepon_no" class="form-label">Nomor Telepon / WhatsApp</label>
                        <input type="text" id="edit_telepon_no" name="telepon_no" class="form-control" placeholder="Contoh: 08123456789">
                    </div>
                    <div class="form-group">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" id="edit_email" name="email" class="form-control" placeholder="Contoh: budi@mirasa.co.id">
                    </div>

                    <div class="form-group">
                        <label for="edit_bank_name" class="form-label">Bank</label>
                        <input type="text" id="edit_bank_name" name="bank_name" class="form-control" placeholder="Contoh: BCA">
                    </div>
                    <div class="form-group">
                        <label for="edit_no_rekening" class="form-label">No. Rekening</label>
                        <input type="text" id="edit_no_rekening" name="no_rekening" class="form-control" placeholder="Nomor rekening">
                    </div>

                    <div class="form-group col-full">
                        <label for="edit_alamat_txt" class="form-label">Alamat Domisili</label>
                        <textarea id="edit_alamat_txt" name="alamat_txt" class="form-control" rows="2" placeholder="Alamat lengkap tempat tinggal karyawan..."></textarea>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditKaryawan')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>