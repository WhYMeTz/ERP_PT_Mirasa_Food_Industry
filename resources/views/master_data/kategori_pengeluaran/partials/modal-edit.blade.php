{{-- Modal Edit Kategori Kas Keluar --}}
<div id="modalEditKategori" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <div class="modal-title-wrap">
                <div class="modal-icon-badge badge-dark">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <h3 class="modal-title">Edit Kategori Kas Keluar</h3>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeModal('modalEditKategori')" title="Tutup Modal">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="" method="POST" id="formEditKategori">
            @csrf
            @method('PUT')
            <div class="modal-body">
                {{-- Nama Kategori --}}
                <div class="form-group">
                    <label for="edit_nama_kategori" class="form-label">
                        Nama Kategori <span class="required-star">*</span>
                    </label>
                    <input type="text" 
                        name="nama_kategori" 
                        id="edit_nama_kategori" 
                        class="form-control" 
                        required 
                        maxlength="100" 
                        autocomplete="off">
                </div>

                {{-- Kode Kategori --}}
                <div class="form-group">
                    <label for="edit_kode_kategori" class="form-label">
                        Kode Kategori <span class="required-star">*</span>
                    </label>
                    <input type="text" 
                        name="kode_kategori" 
                        id="edit_kode_kategori" 
                        class="form-control font-mono" 
                        required 
                        maxlength="30" 
                        autocomplete="off">
                    <div class="form-hint">Kode kategori unik pengidentifikasi pos transaksi.</div>
                </div>

                {{-- Pemetaan Akun Beban COA Default --}}
                <div class="form-group">
                    <label for="edit_coa_id_default" class="form-label">
                        Akun Beban COA Default
                    </label>
                    <select name="coa_id_default" id="edit_coa_id_default" class="form-control form-control-select">
                        <option value="">-- Tanpa Akun Default (Pilih Nanti Saat Transaksi) --</option>
                        @foreach($coaList as $coa)
                            <option value="{{ $coa->coa_id }}">
                                [{{ $coa->kode_akun }}] {{ $coa->nama_akun }} ({{ $coa->kategori_akun }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-hint">Akun beban yang otomatis dipilih saat transaksi kas keluar ini masuk proses jurnal.</div>
                </div>

                {{-- Keterangan --}}
                <div class="form-group">
                    <label for="edit_keterangan" class="form-label">Keterangan Tambahan</label>
                    <textarea name="keterangan" 
                        id="edit_keterangan" 
                        class="form-control" 
                        rows="2" 
                        maxlength="255"></textarea>
                </div>

                {{-- Status Aktif --}}
                <div class="form-group">
                    <label class="form-checkbox-label">
                        <input type="checkbox" name="active_st" id="edit_active_st" value="1">
                        <span>Status kategori aktif (dapat dipilih di bukti kas keluar)</span>
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalEditKategori')">
                    Batal
                </button>
                <button type="submit" class="btn-modal-submit">
                    Perbarui Kategori
                </button>
            </div>
        </form>
    </div>
</div>
