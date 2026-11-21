{{-- Modal Tambah Kategori Kas Keluar --}}
<div id="modalCreateKategori" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <div class="modal-title-wrap">
                <div class="modal-icon-badge badge-dark">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <h3 class="modal-title">Tambah Kategori Kas Keluar</h3>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeModal('modalCreateKategori')" title="Tutup Modal">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('master.kategori_pengeluaran.store') }}" method="POST" id="formCreateKategori">
            @csrf
            <div class="modal-body">
                {{-- Nama Kategori --}}
                <div class="form-group">
                    <label for="create_nama_kategori" class="form-label">
                        Nama Kategori <span class="required-star">*</span>
                    </label>
                    <input type="text" 
                        name="nama_kategori" 
                        id="create_nama_kategori" 
                        class="form-control" 
                        placeholder="Misal: Perawatan & Servis Mesin" 
                        required 
                        maxlength="100" 
                        autocomplete="off">
                    <div class="form-hint">Kode kategori akan otomatis dibuat dari nama ini jika tidak diisi manual.</div>
                </div>

                {{-- Kode Kategori --}}
                <div class="form-group">
                    <label for="create_kode_kategori" class="form-label">
                        Kode Kategori <span class="required-star">*</span>
                    </label>
                    <input type="text" 
                        name="kode_kategori" 
                        id="create_kode_kategori" 
                        class="form-control font-mono" 
                        placeholder="Misal: PERAWATAN_MESIN" 
                        required 
                        maxlength="30" 
                        autocomplete="off">
                    <div class="form-hint">Identitas unik kategori (huruf kapital, angka, dan garis bawah).</div>
                </div>

                {{-- Pemetaan Akun Beban COA Default --}}
                <div class="form-group">
                    <label for="create_coa_id_default" class="form-label">
                        Akun Beban COA Default
                    </label>
                    <select name="coa_id_default" id="create_coa_id_default" class="form-control form-control-select">
                        <option value="">-- Tanpa Akun Default (Pilih Nanti Saat Transaksi) --</option>
                        @foreach($coaList as $coa)
                            <option value="{{ $coa->coa_id }}">
                                [{{ $coa->kode_akun }}] {{ $coa->nama_akun }} ({{ $coa->kategori_akun }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-hint">Otomatisasi pemetaan jurnal beban saat bukti kas keluar diverifikasi oleh Akuntansi.</div>
                </div>

                {{-- Keterangan --}}
                <div class="form-group">
                    <label for="create_keterangan" class="form-label">Keterangan Tambahan</label>
                    <textarea name="keterangan" 
                        id="create_keterangan" 
                        class="form-control" 
                        rows="2" 
                        placeholder="Catatan peruntukan pos pengeluaran ini..." 
                        maxlength="255"></textarea>
                </div>

                {{-- Status Aktif --}}
                <div class="form-group">
                    <label class="form-checkbox-label">
                        <input type="checkbox" name="active_st" value="1" checked>
                        <span>Langsung aktifkan kategori ini untuk transaksi kas keluar</span>
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('modalCreateKategori')">
                    Batal
                </button>
                <button type="submit" class="btn-modal-submit">
                    Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</div>
