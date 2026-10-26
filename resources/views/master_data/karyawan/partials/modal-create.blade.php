{{-- MODAL TAMBAH KARYAWAN BARU --}}
<style>
    #modalTambahKaryawan .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem 1.25rem;
    }
    #modalTambahKaryawan .form-grid .form-group {
        margin-bottom: 0;
    }
    #modalTambahKaryawan .form-grid .col-full {
        grid-column: 1 / -1;
    }
    @media (max-width: 600px) {
        #modalTambahKaryawan .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div id="modalTambahKaryawan" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 700px;">
        <div class="modal-header">
            <h2 class="modal-title">Tambah Karyawan Baru</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahKaryawan')">&times;</button>
        </div>
        <form action="{{ route('master.karyawan.store') }}" method="POST">
            @csrf
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div class="form-grid">

                    <div class="form-group">
                        <label for="create_nik" class="form-label">NIK / ID Karyawan <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="create_nik" name="nik" value="{{ old('nik') }}" class="form-control" required placeholder="Contoh: AC1A117" oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div class="form-group">
                        <label for="create_id_absensi" class="form-label">ID Absensi</label>
                        <input type="text" id="create_id_absensi" name="id_absensi" value="{{ old('id_absensi') }}" class="form-control" placeholder="ID di mesin absen">
                    </div>

                    <div class="form-group col-full">
                        <label for="create_karyawan_nm" class="form-label">Nama Lengkap Karyawan <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="create_karyawan_nm" name="karyawan_nm" value="{{ old('karyawan_nm') }}" class="form-control" required placeholder="Contoh: Budi Santoso">
                    </div>

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
                        <label for="create_jabatan_nm" class="form-label">Divisi / Bagian <span style="color:#ef4444;">*</span></label>
                        <select id="create_jabatan_nm" name="jabatan_nm" class="form-control" required>
                            <option value="">-- Pilih Divisi / Bagian --</option>
                            @foreach (\App\Models\MasterData\MstKaryawan::DIVISI as $divisi)
                                <option value="{{ $divisi }}" {{ old('jabatan_nm') === $divisi ? 'selected' : '' }}>{{ $divisi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create_kelompok" class="form-label">Kelompok</label>
                        <select id="create_kelompok" name="kelompok" class="form-control">
                            <option value="">-- Pilih --</option>
                            @foreach (\App\Models\MasterData\MstKaryawan::KELOMPOK as $key => $label)
                                <option value="{{ $key }}" {{ old('kelompok') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="create_shift" class="form-label">Shift</label>
                        <select id="create_shift" name="shift" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option value="SHIFT A" {{ old('shift') === 'SHIFT A' ? 'selected' : '' }}>Shift A</option>
                            <option value="SHIFT B" {{ old('shift') === 'SHIFT B' ? 'selected' : '' }}>Shift B</option>
                            <option value="NON SHIFT" {{ old('shift') === 'NON SHIFT' ? 'selected' : '' }}>Non Shift</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create_status_kerja" class="form-label">Status Karyawan</label>
                        <select id="create_status_kerja" name="status_kerja" class="form-control">
                            <option value="Tetap" {{ old('status_kerja', 'Tetap') === 'Tetap' ? 'selected' : '' }}>Tetap</option>
                            <option value="Kontrak" {{ old('status_kerja') === 'Kontrak' ? 'selected' : '' }}>Kontrak</option>
                            <option value="Harian" {{ old('status_kerja') === 'Harian' ? 'selected' : '' }}>Harian</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="create_tgl_masuk" class="form-label">Tanggal Masuk Kerja</label>
                        <input type="date" id="create_tgl_masuk" name="tgl_masuk" value="{{ old('tgl_masuk') }}" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="create_telepon_no" class="form-label">Nomor Telepon / WhatsApp</label>
                        <input type="text" id="create_telepon_no" name="telepon_no" value="{{ old('telepon_no') }}" class="form-control" placeholder="Contoh: 08123456789">
                    </div>
                    <div class="form-group">
                        <label for="create_email" class="form-label">Email</label>
                        <input type="email" id="create_email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Contoh: budi@mirasa.co.id">
                    </div>

                    <div class="form-group">
                        <label for="create_bank_name" class="form-label">Bank</label>
                        <input type="text" id="create_bank_name" name="bank_name" value="{{ old('bank_name', 'BCA') }}" class="form-control" placeholder="Contoh: BCA">
                    </div>
                    <div class="form-group">
                        <label for="create_no_rekening" class="form-label">No. Rekening</label>
                        <input type="text" id="create_no_rekening" name="no_rekening" value="{{ old('no_rekening') }}" class="form-control" placeholder="Nomor rekening">
                    </div>

                    <div class="form-group col-full">
                        <label for="create_alamat_txt" class="form-label">Alamat Domisili</label>
                        <textarea id="create_alamat_txt" name="alamat_txt" class="form-control" rows="2" placeholder="Alamat lengkap tempat tinggal karyawan...">{{ old('alamat_txt') }}</textarea>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahKaryawan')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Karyawan</button>
            </div>
        </form>
    </div>
</div>