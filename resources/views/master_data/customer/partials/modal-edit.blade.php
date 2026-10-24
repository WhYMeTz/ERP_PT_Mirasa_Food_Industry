{{-- MODAL EDIT CUSTOMER --}}
<div id="modalEditCustomer" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Edit Data Customer</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalEditCustomer')">&times;</button>
        </div>
        <form id="formEditCustomer" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_customer_cd" class="form-label">Kode Customer <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_customer_cd" name="customer_cd" class="form-control" style="text-transform: uppercase;" required>
                </div>
                <div class="form-group">
                    <label for="edit_customer_nm" class="form-label">Nama Customer <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="edit_customer_nm" name="customer_nm" class="form-control" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.875rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="edit_klasifikasi" class="form-label">Kategori Customer <span style="color:#ef4444;">*</span></label>
                        <select id="edit_klasifikasi" name="klasifikasi" class="form-control" onchange="handleKlasifikasiChange('edit')" required>
                            <option value="MANUAL">MANUAL (Toko Retail / Sales Biasa)</option>
                            <option value="IFM">IFM (PT Indofood Fortuna Makmur)</option>
                            <option value="MIDI_ALFA">Midi/Alfa (Modern Trade: Alfamart &amp; Alfamidi)</option>
                            <option value="IDM">IDM (Modern Trade: Indomaret / DC Jogja)</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="edit_wilayah" class="form-label">Wilayah / Rute (Opsional)</label>
                        <input type="text" id="edit_wilayah" name="wilayah" class="form-control" placeholder="Contoh: SEMARANG / CIKOKOL - TANGERANG">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.875rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="edit_kode_pelanggan" class="form-label">Kode Pelanggan (Ref Customer)</label>
                        <input type="text" id="edit_kode_pelanggan" name="kode_pelanggan" class="form-control" placeholder="Contoh: IFM01.012007 (Opsional)">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="edit_top_txt" class="form-label">Terms of Payment (TOP)</label>
                        <input type="text" id="edit_top_txt" name="top_txt" class="form-control" placeholder="Contoh: 7 Hari / Tinggal 1 Nota">
                    </div>
                </div>

                <div class="form-group" id="group_edit_default_ppn">
                    <label for="edit_default_ppn" class="form-label">Default Pajak Faktur (Khusus Manual)</label>
                    <select id="edit_default_ppn" name="default_ppn" class="form-control">
                        <option value="NON_PPN">Non-PPN (Format Faktur: XX/PM.B/MM/YY)</option>
                        <option value="PPN_11">Ber-PPN 11% (Format Faktur: XX/PM.A/MM/YY)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_customer_kontak" class="form-label">Kontak PIC / No Telepon</label>
                    <input type="text" id="edit_customer_kontak" name="kontak_no" class="form-control">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit_customer_alamat" class="form-label">Alamat Lengkap Customer (Faktur &amp; Surat Jalan)</label>
                    <textarea id="edit_customer_alamat" name="alamat_txt" class="form-control" rows="2" placeholder="Contoh: Jl. Magelang KM 4.5, Sleman, D.I. Yogyakarta"></textarea>
                    <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">Alamat ini akan otomatis ditarik dan dicetak pada Faktur Penjualan serta Surat Jalan.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditCustomer')">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
