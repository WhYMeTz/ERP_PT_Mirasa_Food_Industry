{{-- MODAL TAMBAH KARYAWAN BARU --}}
<div id="modalTambahKaryawan" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 600px;">
        <div class="modal-header">
            <h2 class="modal-title">Tambah Karyawan Baru</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahKaryawan')">&times;</button>
        </div>
        <form action="{{ route('master.karyawan.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="create_nik" class="form-label">Nomor Induk Karyawan (NIK) <span style="color:#ef4444;">*</span></label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" id="create_nik" name="nik" value="{{ old('nik', $nextNik) }}" class="form-control" required placeholder="Contoh: KRY-0001">
                        <button type="button" class="btn btn-secondary" onclick="resetNikOtomatis()" title="Reset NIK Otomatis">↺</button>
                    </div>
                    <small style="color: #64748b; font-size: 0.75rem;">Otomatis terisi nomor urut karyawan, dapat disesuaikan dengan NIK resmi pabrik.</small>
                </div>

                <div class="form-group">
                    <label for="create_karyawan_nm" class="form-label">Nama Lengkap Karyawan <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_karyawan_nm" name="karyawan_nm" value="{{ old('karyawan_nm') }}" class="form-control" required placeholder="Contoh: Budi Santoso">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="create_departemen_cd" class="form-label">Departemen <span style="color:#ef4444;">*</span></label>
                        <select id="create_departemen_cd" name="departemen_cd" class="form-control" required>
                            <option value="">-- Pilih Departemen --</option>
                            @foreach ($departemenList as $key => $label)
                                <option value="{{ $key }}" {{ old('departemen_cd') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="create_jabatan_nm" class="form-label">Jabatan Kerja <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="create_jabatan_nm" name="jabatan_nm" value="{{ old('jabatan_nm') }}" class="form-control" required placeholder="Contoh: Mandor Produksi">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="create_telepon_no" class="form-label">Nomor Telepon / WhatsApp</label>
                        <input type="text" id="create_telepon_no" name="telepon_no" value="{{ old('telepon_no') }}" class="form-control" placeholder="Contoh: 08123456789">
                    </div>
                    <div class="form-group">
                        <label for="create_email" class="form-label">Email</label>
                        <input type="email" id="create_email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Contoh: budi@mirasa.co.id">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="create_alamat_txt" class="form-label">Alamat Domisili</label>
                    <textarea id="create_alamat_txt" name="alamat_txt" class="form-control" rows="2" placeholder="Alamat lengkap tempat tinggal karyawan...">{{ old('alamat_txt') }}</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahKaryawan')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Karyawan</button>
            </div>
        </form>
    </div>
</div>
