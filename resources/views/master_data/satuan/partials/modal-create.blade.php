{{-- MODAL TAMBAH SATUAN --}}
<div id="modalTambahSatuan" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 480px;">
        <div class="modal-header">
            <h2 class="modal-title">Tambah Satuan Baru</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahSatuan')">&times;</button>
        </div>
        <form action="{{ route('master.satuan.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="create_satuan_cd" class="form-label">Kode Satuan <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_satuan_cd" name="satuan_cd" class="form-control" placeholder="Contoh: KG, SAK, DUS, LTR" style="text-transform: uppercase;" required>
                    <span style="font-size: 0.775rem; color: #64748b; margin-top: 0.25rem; display: block;">Singkatan standar huruf besar tanpa spasi.</span>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="create_satuan_nm" class="form-label">Nama Satuan <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_satuan_nm" name="satuan_nm" class="form-control" placeholder="Contoh: Kilogram, Sak 50Kg, Dus Karton" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahSatuan')">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Satuan</button>
            </div>
        </form>
    </div>
</div>
