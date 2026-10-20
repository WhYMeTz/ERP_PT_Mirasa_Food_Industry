{{-- Modal Tambah Akun Kas / Rekening Bank --}}
<div id="modalCreateKasBank" class="kb-modal-backdrop">
    <div class="kb-modal">
        <div class="kb-modal-header">
            <h3 class="kb-modal-title">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Tambah Akun Kas & Rekening Bank</span>
            </h3>
            <button type="button" class="kb-modal-close" onclick="closeModal('modalCreateKasBank')">&times;</button>
        </div>
        <form action="{{ route('keuangan.kas_bank.store') }}" method="POST">
            @csrf
            <div class="kb-modal-body">
                <div class="kb-form-group">
                    <label class="kb-form-label">Tipe Akun Keuangan <span class="req">*</span></label>
                    <select name="tipe_akun" id="create_tipe_akun" class="kb-form-select" required>
                        <option value="BANK" selected>Rekening Bank (Giro / Tabungan Operasional)</option>
                        <option value="KAS">Kas Fisik / Kas Kecil Tunai (Brankas Pabrik)</option>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 0.75rem;">
                    <div class="kb-form-group">
                        <label class="kb-form-label">Kode Akun <span class="req">*</span></label>
                        <input type="text" name="kode_akun" class="kb-form-input" placeholder="Misal: BCA-01 / KAS-PBRK" required maxlength="50" style="text-transform: uppercase;">
                    </div>
                    <div class="kb-form-group">
                        <label class="kb-form-label">Nama Akun <span class="req">*</span></label>
                        <input type="text" name="nama_akun" class="kb-form-input" placeholder="Misal: Bank BCA Operasional" required maxlength="150">
                    </div>
                </div>

                {{-- Khusus Perbankan --}}
                <div id="create_bank_section">
                    <div style="display: grid; grid-template-columns: 1fr 1.25fr; gap: 0.75rem;">
                        <div class="kb-form-group">
                            <label class="kb-form-label">Nama Bank</label>
                            <input type="text" name="bank_nama" id="create_bank_nama" class="kb-form-input" placeholder="BCA / Mandiri / BNI / BRI" maxlength="100">
                        </div>
                        <div class="kb-form-group">
                            <label class="kb-form-label">Nomor Rekening</label>
                            <input type="text" name="nomor_rekening" id="create_nomor_rekening" class="kb-form-input" placeholder="Nomor rekening bank" maxlength="100">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1.25fr 1fr; gap: 0.75rem;">
                        <div class="kb-form-group">
                            <label class="kb-form-label">Atas Nama Rekening</label>
                            <input type="text" name="atas_nama" class="kb-form-input" placeholder="PT Mirasa Food Industry" maxlength="150">
                        </div>
                        <div class="kb-form-group">
                            <label class="kb-form-label">Kantor Cabang</label>
                            <input type="text" name="cabang" class="kb-form-input" placeholder="KCP Purwokerto" maxlength="100">
                        </div>
                    </div>
                </div>

                <div class="kb-form-group">
                    <label class="kb-form-label">Saldo Awal Pembukuan (Rp)</label>
                    <input type="number" step="any" min="0" name="saldo_awal" class="kb-form-input" placeholder="0" value="0">
                    <div class="kb-form-hint">Saldo awal akan otomatis dicatat sebagai transaksi inisialisasi pada buku kas.</div>
                </div>

                <div class="kb-form-group">
                    <label class="kb-form-label">Catatan / Keterangan</label>
                    <textarea name="keterangan" class="kb-form-textarea" rows="2" placeholder="Catatan peruntukan akun kas/bank ini..."></textarea>
                </div>
            </div>
            <div class="kb-modal-footer">
                <button type="button" class="kb-btn kb-btn-secondary" onclick="closeModal('modalCreateKasBank')">Batal</button>
                <button type="submit" class="kb-btn kb-btn-primary">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Akun Baru</span>
                </button>
            </div>
        </form>
    </div>
</div>
