{{-- MODAL TAMBAH JENIS BARANG --}}
<div id="modalTambahJenis" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 480px;">
        <div class="modal-header">
            <h2 class="modal-title">Tambah Jenis Barang Baru</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahJenis')">&times;</button>
        </div>
        <form action="{{ route('master.jenis.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="create_jenis_cd" class="form-label">Kode Jenis Barang <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_jenis_cd" name="jenis_barang_cd" class="form-control" placeholder="Contoh: RAW, WIP, FG, PACK" style="text-transform: uppercase;" required>
                    <span style="font-size: 0.775rem; color: #64748b; margin-top: 0.25rem; display: block;">Singkatan klasifikasi standar (huruf besar).</span>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="create_jenis_nm" class="form-label">Nama Jenis Barang <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_jenis_nm" name="jenis_barang_nm" class="form-control" placeholder="Contoh: Bahan Baku Singkong & Minyak" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahJenis')">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Jenis Barang</button>
            </div>
        </form>
    </div>
</div>
