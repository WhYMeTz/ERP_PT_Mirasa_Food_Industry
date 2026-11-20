{{-- MODAL TAMBAH CUSTOMER --}}
<div id="modalTambahCustomer" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h2 class="modal-title">Tambah Customer Baru</h2>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahCustomer')">&times;</button>
        </div>
        <form action="{{ route('master.customer.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.375rem;">
                        <label for="create_customer_cd" class="form-label" style="margin-bottom: 0;">Kode Customer <span style="color:#ef4444;">*</span></label>
                        <div style="display: flex; gap: 0.35rem;">
                            <button type="button" class="btn btn-secondary btn-sm" data-target="create_customer_cd" onclick="fetchNextCode('customer', 'create_customer_cd', {name: document.getElementById('create_customer_nm').value})" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" title="Buat kode dari singkatan nama">
                                ✨ Dari Singkatan
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" data-target="create_customer_cd" onclick="fetchNextCode('customer', 'create_customer_cd')" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" title="Reset ke nomor urut standar">
                                ↺ Reset
                            </button>
                        </div>
                    </div>
                    <input type="text" id="create_customer_cd" name="customer_cd" value="{{ $nextCustomerCode ?? '' }}" class="form-control" placeholder="Contoh: CUST-ICBP-01" style="text-transform: uppercase;" required>
                    <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">Otomatis mengikuti singkatan nama (misal: Sumber Rezeki → CUST-SR-01) atau nomor urut.</small>
                </div>
                <div class="form-group">
                    <label for="create_customer_nm" class="form-label">Nama Perusahaan / Customer <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="create_customer_nm" name="customer_nm" class="form-control" placeholder="Contoh: PT Indofood Fortuna Makmur" oninput="debounceCodeFromName('customer', 'create_customer_nm', 'create_customer_cd')" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.875rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="create_klasifikasi" class="form-label">Kategori Customer <span style="color:#ef4444;">*</span></label>
                        <select id="create_klasifikasi" name="klasifikasi" class="form-control" onchange="handleKlasifikasiChange('create')" required>
                            <option value="MANUAL">MANUAL (Toko Retail / Sales Biasa)</option>
                            <option value="IFM">IFM (PT Indofood Fortuna Makmur)</option>
                            <option value="MIDI_ALFA">Midi/Alfa (Modern Trade: Alfamart &amp; Alfamidi)</option>
                            <option value="IDM">IDM (Modern Trade: Indomaret / DC Jogja)</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="create_wilayah" class="form-label">Wilayah / Rute (Opsional)</label>
                        <input type="text" id="create_wilayah" name="wilayah" class="form-control" placeholder="Contoh: SEMARANG / CIKOKOL - TANGERANG">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.875rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="create_kode_pelanggan" class="form-label">Kode Pelanggan (Ref Customer)</label>
                        <input type="text" id="create_kode_pelanggan" name="kode_pelanggan" class="form-control" placeholder="Contoh: IFM01.012007 (Opsional)">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="create_top_txt" class="form-label">Terms of Payment (TOP)</label>
                        <input type="text" id="create_top_txt" name="top_txt" class="form-control" value="Tinggal 1 Nota" placeholder="Contoh: 7 Hari / Tinggal 1 Nota">
                    </div>
                </div>

                <div class="form-group" id="group_create_default_ppn">
                    <label for="create_default_ppn" class="form-label">Default Pajak Faktur (Khusus Manual)</label>
                    <select id="create_default_ppn" name="default_ppn" class="form-control">
                        <option value="NON_PPN">Non-PPN (Format Faktur: XX/PM.B/MM/YY)</option>
                        <option value="PPN_11">Ber-PPN 11% (Format Faktur: XX/PM.A/MM/YY)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="create_customer_kontak" class="form-label">Kontak PIC / No Telepon</label>
                    <input type="text" id="create_customer_kontak" name="kontak_no" class="form-control" placeholder="Contoh: 021-57958822 (Bpk. Hendra)">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="create_customer_alamat" class="form-label">Alamat Lengkap Customer (Faktur &amp; Surat Jalan)</label>
                    <textarea id="create_customer_alamat" name="alamat_txt" class="form-control" rows="2" placeholder="Contoh: Kawasan Industri Indofood, Blok B No. 4, Cikarang"></textarea>
                    <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">Alamat ini akan otomatis ditarik dan dicetak pada Faktur Penjualan serta Surat Jalan.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahCustomer')">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">Simpan Customer</button>
            </div>
        </form>
    </div>
</div>
