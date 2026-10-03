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
                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label for="create_lini_cd" class="form-label">Kode Lini <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_lini_cd" name="lini_cd" class="form-control" placeholder="Contoh: IFM, PP2000, RETAIL, EXP" style="text-transform: uppercase;" required>
                    <span style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem; display: block;">Kode unik singkatan lini kerja (huruf kapital tanpa spasi).</span>
                </div>

                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label for="create_lini_nm" class="form-label">Nama Lini Produksi / Tujuan <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_lini_nm" name="lini_nm" class="form-control" placeholder="Contoh: PRODUKSI IFM, PRODUKSI BERKO" style="text-transform: uppercase;" required>
                    <span style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem; display: block;">Nama yang akan tampil pada dropdown pilihan input produksi.</span>
                </div>

                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label for="create_kategori_lini" class="form-label">Kelompok Kategori Hasil Produksi <span style="color:#ef4444;">*</span></label>
                    <select id="create_kategori_lini" name="kategori_lini" class="form-control" required>
                        <option value="FINISH GOOD (FG)">📦 FINISH GOOD (FG) - Barang Jadi</option>
                        <option value="WORK IN PROGRESS (WIP)">⚖️ WORK IN PROGRESS (WIP) - Barang Setengah Jadi Olahan</option>
                        <option value="LAINNYA">📂 LAINNYA / UMUM</option>
                    </select>
                    <span style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem; display: block;">Menentukan optgroup pengelompokan di dropdown formulir produksi harian.</span>
                </div>

                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label for="create_tipe_batch" class="form-label">Format Penomoran Batch &amp; Kemasan <span style="color:#ef4444;">*</span></label>
                    <select id="create_tipe_batch" name="tipe_batch" class="form-control" required>
                        <option value="REGULER">📦 REGULER - Format Tanggal DD MM YYYY (Kemasan Retail Mirasa)</option>
                        <option value="IFM">🏭 IFM - Format Shift &amp; Rentang Karton Box (Standar Indofood WIP-FCC 6kg)</option>
                    </select>
                    <span style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem; display: block;">Menentukan apakah input produksi meminta nomor karton shift atau tanggal standar persediaan.</span>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="create_keterangan" class="form-label">Keterangan / Deskripsi Operasional</label>
                    <input type="text" id="create_keterangan" name="keterangan" class="form-control" placeholder="Contoh: Penggorengan & Keripik Singkong Retail">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahLini')">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Lini Produksi</button>
            </div>
        </form>
    </div>
</div>
