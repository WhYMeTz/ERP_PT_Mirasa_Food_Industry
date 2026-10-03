{{-- MODAL TAMBAH LINI PRODUKSI --}}
<div id="modalTambahLini" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 520px;">
        <div class="modal-header">
            <h2 class="modal-title">Tambah Lini Produksi / Tujuan Baru</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahLini')">&times;</button>
        </div>
        <form action="{{ route('master.lini_produksi.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="create_lini_cd" class="form-label">Kode Lini <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_lini_cd" name="lini_cd" class="form-control" placeholder="Contoh: IFM, JUMBO20, BERKO" style="text-transform: uppercase;" required>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="create_lini_nm" class="form-label">Nama Lini Produksi / Tujuan <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_lini_nm" name="lini_nm" class="form-control" placeholder="Contoh: PRODUKSI IFM, PRODUKSI BERKO" style="text-transform: uppercase;" required>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="create_kategori_lini" class="form-label">Kategori Hasil Produksi <span style="color:#ef4444;">*</span></label>
                    <select id="create_kategori_lini" name="kategori_lini" class="form-control" required>
                        <option value="FINISH GOOD (FG)">Finish Good (FG)</option>
                        <option value="WORK IN PROGRESS (WIP)">Work In Progress (WIP)</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="create_tipe_batch" class="form-label">Format Penomoran Batch <span style="color:#ef4444;">*</span></label>
                    <select id="create_tipe_batch" name="tipe_batch" class="form-control" required>
                        <option value="REGULER">Reguler (Format Tanggal)</option>
                        <option value="IFM">IFM (Format Shift &amp; Karton Box)</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="create_keterangan" class="form-label">Keterangan</label>
                    <input type="text" id="create_keterangan" name="keterangan" class="form-control" placeholder="Keterangan operasional (opsional)">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahLini')">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Lini Produksi</button>
            </div>
        </form>
    </div>
</div>
