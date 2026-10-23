{{-- Modal Edit Akun (COA) --}}
<div id="modalEditCoa" class="modal-backdrop-custom" style="display: none;">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom" style="background: #fffbeb; border-bottom: 1.5px solid #fef3c7;">
            <div>
                <h3 class="modal-title-custom" style="color: #92400e;">✏️ Sesuaikan Akun Keuangan (COA)</h3>
                <p style="font-size: 0.75rem; color: #b45309; margin: 2px 0 0 0;">
                    Perbarui nama, klasifikasi, atau saldo akun
                </p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeEditModal()">&times;</button>
        </div>

        <form action="" method="POST" id="formEditCoa">
            @csrf
            @method('PUT')
            <input type="hidden" name="coa_id" id="edit_coa_id">

            <div class="modal-body-custom" style="display: flex; flex-direction: column; gap: 0.85rem;">
                <div style="display: grid; grid-template-columns: 140px 1fr; gap: 0.75rem;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.3rem;">
                            Kode Akun <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="text" 
                               name="kode_akun" 
                               id="edit_kode_akun" 
                               class="form-control" 
                               style="height: 38px; font-size: 0.85rem; font-family: monospace; font-weight: 700;" 
                               required>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.3rem;">
                            Nama Akun Keuangan <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="text" 
                               name="nama_akun" 
                               id="edit_nama_akun" 
                               class="form-control" 
                               style="height: 38px; font-size: 0.85rem;" 
                               required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.3rem;">
                            Kategori Akun <span style="color: #dc2626;">*</span>
                        </label>
                        <select name="kategori_akun" 
                                id="edit_kategori_akun" 
                                class="form-control" 
                                style="height: 38px; font-size: 0.85rem;" 
                                onchange="onKategoriChange(this, 'edit')" 
                                required>
                            <option value="AKTIVA">AKTIVA (Harta / Kas / Persediaan)</option>
                            <option value="PASIVA">PASIVA (Hutang / Kewajiban)</option>
                            <option value="MODAL">MODAL (Ekuitas Pemilik)</option>
                            <option value="PENDAPATAN">PENDAPATAN (Penjualan / Omset)</option>
                            <option value="HPP">HPP (Harga Pokok Penjualan)</option>
                            <option value="BEBAN">BEBAN (Biaya Operasional Pabrik)</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.3rem;">
                            Sub-Kelompok Akun
                        </label>
                        <input type="text" 
                               name="sub_kategori" 
                               id="edit_sub_kategori" 
                               class="form-control" 
                               style="height: 38px; font-size: 0.85rem;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.3rem;">
                            Posisi Laporan <span style="color: #dc2626;">*</span>
                        </label>
                        <select name="posisi_keuangan" id="edit_posisi_keuangan" class="form-control" style="height: 38px; font-size: 0.85rem;" required>
                            <option value="NERACA">NERACA (Kekayaan &amp; Hutang)</option>
                            <option value="LABA/RUGI">LABA/RUGI (Pendapatan &amp; Biaya)</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.3rem;">
                            Saldo Normal <span style="color: #dc2626;">*</span>
                        </label>
                        <select name="saldo_normal" id="edit_saldo_normal" class="form-control" style="height: 38px; font-size: 0.85rem;" required>
                            <option value="DEBET">DEBET (Harta &amp; Beban)</option>
                            <option value="KREDIT">KREDIT (Hutang, Modal &amp; Omset)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.3rem;">
                        Saldo Awal (Opening Balance) [ Rp ]
                    </label>
                    <input type="number" 
                           step="1" 
                           name="saldo_awal" 
                           id="edit_saldo_awal" 
                           class="form-control" 
                           style="height: 38px; font-size: 0.85rem; font-weight: 700; color: #0284c7;">
                </div>

                <div id="edit_kas_bank_group" style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 8px; padding: 0.65rem 0.85rem;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.8125rem; font-weight: 700; color: #166534; margin: 0;">
                        <input type="checkbox" name="is_kas_bank" id="edit_is_kas_bank" value="1" style="width: 16px; height: 16px; accent-color: #059669;">
                        <span>Tandai sebagai Kas Tunai / Rekening Bank (Bisa Dipakai Transaksi Bayar)</span>
                    </label>
                </div>

                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.3rem;">
                        Keterangan Tambahan
                    </label>
                    <input type="text" 
                           name="keterangan" 
                           id="edit_keterangan" 
                           class="form-control" 
                           style="height: 38px; font-size: 0.85rem;">
                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()" style="font-weight: 700; font-size: 0.825rem;">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700; font-size: 0.825rem; background: #d97706; border-color: #d97706; color: #ffffff;">
                    💾 Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
