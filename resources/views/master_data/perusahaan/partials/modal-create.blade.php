{{-- MODAL TAMBAH PERUSAHAAN / ENTITAS BARU --}}
<div id="modalTambahPerusahaan" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Tambah Entitas Perusahaan Baru</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahPerusahaan')">&times;</button>
        </div>
        <form action="{{ route('master.perusahaan.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="create_perusahaan_nm" class="form-label">Nama Perusahaan / Entitas Bisnis <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_perusahaan_nm" name="gudang_nm" class="form-control" placeholder="Contoh: PT Mirasa Food Industry" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="create_perusahaan_cd" class="form-label">Kode Perusahaan <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="create_perusahaan_cd" name="gudang_cd" value="{{ $nextGudangCode ?? '' }}" class="form-control" placeholder="Contoh: MFI-PST, CV-BMB" style="text-transform: uppercase;" required>
                        <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">Singkatan unik entitas.</small>
                    </div>
                    <div class="form-group">
                        <label for="create_tipe_perusahaan_cd" class="form-label">Tipe Entitas</label>
                        <select id="create_tipe_perusahaan_cd" name="tipe_gudang_cd" class="form-control">
                            <option value="Pusat">Kantor / Pabrik Pusat</option>
                            <option value="Cabang">Kantor Cabang</option>
                            <option value="Anak Perusahaan">Anak Perusahaan</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="create_perusahaan_telepon" class="form-label">No. Telepon / Kontak</label>
                    <input type="text" id="create_perusahaan_telepon" name="telepon" class="form-control" placeholder="Contoh: 087880809279 atau (0293) 123456">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="create_perusahaan_alamat" class="form-label">Alamat Kantor / Pabrik</label>
                    <textarea id="create_perusahaan_alamat" name="alamat_txt" class="form-control" rows="2" placeholder="Contoh: Jalan Munggur No. 2 Ambartawang, Kec. Mungkid, Kab. Magelang, Jawa Tengah"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahPerusahaan')">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Perusahaan</button>
            </div>
        </form>
    </div>
</div>
