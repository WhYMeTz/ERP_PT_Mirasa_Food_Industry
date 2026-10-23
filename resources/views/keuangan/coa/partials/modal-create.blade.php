{{-- Modal Tambah Akun Baru (COA) --}}
<div id="modalCreateCoa" class="modal-backdrop-custom" style="display: none;">
    <div class="modal-dialog-custom">
        <div class="modal-header-custom">
            <div>
                <h3 class="modal-title-custom">+ Tambah Akun Baru (COA)</h3>
                <p style="font-size: 0.75rem; color: #64748b; margin: 2px 0 0 0;">
                    Daftarkan pos akun baru ke Bagan Akun akuntansi PT Mirasa
                </p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeCreateModal()">&times;</button>
        </div>

        <form action="{{ route('keuangan.coa.store') }}" method="POST" id="formCreateCoa">
            @csrf

            <div class="modal-body-custom" style="display: flex; flex-direction: column; gap: 0.85rem;">
                <div style="display: grid; grid-template-columns: 140px 1fr; gap: 0.75rem;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.3rem;">
                            Kode Akun <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="text" 
                               name="kode_akun" 
                               id="create_kode_akun" 
                               class="form-control" 
                               placeholder="Contoh: 1107" 
                               style="height: 38px; font-size: 0.85rem; font-family: monospace; font-weight: 700;" 
                               required>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.3rem;">
                            Nama Akun Keuangan <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="text" 
                               name="nama_akun" 
                               id="create_nama_akun" 
                               class="form-control" 
                               placeholder="Contoh: Bank Mandiri Operasional" 
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
                                id="create_kategori_akun" 
                                class="form-control" 
                                style="height: 38px; font-size: 0.85rem;" 
                                onchange="onKategoriChange(this, 'create')" 
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
                               id="create_sub_kategori" 
                               class="form-control" 
                               placeholder="Contoh: Kas & Setara Kas, Beban Operasional" 
                               style="height: 38px; font-size: 0.85rem;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.3rem;">
                            Posisi Laporan <span style="color: #dc2626;">*</span>
                        </label>
                        <select name="posisi_keuangan" id="create_posisi_keuangan" class="form-control" style="height: 38px; font-size: 0.85rem;" required>
                            <option value="NERACA">NERACA (Kekayaan &amp; Hutang)</option>
                            <option value="LABA/RUGI">LABA/RUGI (Pendapatan &amp; Biaya)</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.3rem;">
                            Saldo Normal <span style="color: #dc2626;">*</span>
                        </label>
                        <select name="saldo_normal" id="create_saldo_normal" class="form-control" style="height: 38px; font-size: 0.85rem;" required>
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
                           id="create_saldo_awal" 
                           value="0" 
                           class="form-control" 
                           placeholder="0" 
                           style="height: 38px; font-size: 0.85rem; font-weight: 700; color: #0284c7;">
                    <small style="color: #64748b; font-size: 0.7rem; display: block; margin-top: 2px;">
                        Saldo bawaan saat awal mulai migrasi pembukuan ke ERP Mirasa.
                    </small>
                </div>

                <div id="create_kas_bank_group" style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 8px; padding: 0.65rem 0.85rem;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.8125rem; font-weight: 700; color: #166534; margin: 0;">
                        <input type="checkbox" name="is_kas_bank" id="create_is_kas_bank" value="1" style="width: 16px; height: 16px; accent-color: #059669;">
                        <span>Tandai sebagai Kas Tunai / Rekening Bank (Bisa Dipakai Transaksi Bayar)</span>
                    </label>
                </div>

                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.3rem;">
                        Keterangan Tambahan
                    </label>
                    <input type="text" 
                           name="keterangan" 
                           id="create_keterangan" 
                           class="form-control" 
                           placeholder="Contoh: Rekening utama penerimaan transfer penjualan" 
                           style="height: 38px; font-size: 0.85rem;">
                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn btn-secondary" onclick="closeCreateModal()" style="font-weight: 700; font-size: 0.825rem;">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700; font-size: 0.825rem; background: #0284c7; border-color: #0284c7;">
                    💾 Simpan Akun
                </button>
            </div>
        </form>
    </div>
</div>
