{{-- MODAL TAMBAH MASTER PAJAK - MODERN ENTERPRISE DESIGN --}}
<div id="modalTambahPajak" class="modal-overlay pajak-modal-overlay" style="display: none;">
    <div class="modal-dialog modern-pajak-dialog">
        {{-- Modern Modal Header --}}
        <div class="modern-modal-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.85rem;">
                <div class="modern-header-icon">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="modern-modal-title">Tambah Pajak Baru</h3>
                    <p class="modern-modal-subtitle">Pengaturan tarif dan integrasi akun pembukuan otomatis</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalTambahPajak')" title="Tutup Modal">&times;</button>
        </div>

        <form action="{{ route('master.pajak.store') }}" method="POST" id="formTambahPajak">
            @csrf
            <div class="modern-modal-body">
                {{-- Baris 1: Nama & Kode Pajak --}}
                <div class="form-grid-2" style="margin-bottom: 1.15rem;">
                    <div>
                        <label class="form-label-modern" for="create_pajak_nm">
                            Nama Pajak <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="create_pajak_nm" name="pajak_nm" class="form-control-modern" placeholder="Contoh: PPN 11% (Standar Nasional)" required autofocus>
                    </div>

                    <div>
                        <label class="form-label-modern" for="create_pajak_cd">
                            Kode Singkat <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="create_pajak_cd" name="pajak_cd" class="form-control-modern font-mono" placeholder="Contoh: PPN-11, PPH-23" required style="text-transform: uppercase; font-weight: 700;">
                    </div>
                </div>

                {{-- Baris 2: Persentase (%) & Sifat Perlakuan --}}
                <div class="form-grid-2" style="margin-bottom: 1.35rem;">
                    <div>
                        <label class="form-label-modern" for="create_tarif_persen">
                            Persentase Tarif <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-modern">
                            <input type="number" id="create_tarif_persen" name="tarif_persen" class="form-control-modern font-mono" step="0.01" min="0" max="100" placeholder="0" required style="text-align: right; font-weight: 700; font-size: 1rem;">
                            <span class="input-addon-modern">%</span>
                        </div>
                    </div>

                    <div>
                        <label class="form-label-modern">
                            Sifat Perlakuan <span class="text-danger">*</span>
                        </label>
                        <div class="sifat-segmented-wrap">
                            <label class="sifat-option-card active-tambah" id="card_sifat_create_tambah" onclick="selectSifatOption('create', 'TAMBAH')">
                                <input type="radio" name="sifat_pajak" value="TAMBAH" checked>
                                <div class="sifat-option-icon">➕</div>
                                <div class="sifat-option-text">
                                    <span class="sifat-option-title">Menambah</span>
                                    <span class="sifat-option-desc">Total Invoice (PPN)</span>
                                </div>
                            </label>

                            <label class="sifat-option-card" id="card_sifat_create_potong" onclick="selectSifatOption('create', 'POTONG')">
                                <input type="radio" name="sifat_pajak" value="POTONG">
                                <div class="sifat-option-icon">➖</div>
                                <div class="sifat-option-text">
                                    <span class="sifat-option-title">Memotong</span>
                                    <span class="sifat-option-desc">Kas Vendor (PPh)</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Channel Card 1: Akun Pajak Pembelian (PO / Faktur Beli) --}}
                <div class="tax-channel-card channel-active" id="card_channel_create_pembelian">
                    <div class="channel-card-header">
                        <div class="channel-header-info">
                            <div class="channel-icon-wrap icon-buy">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="channel-title">Akun Pajak Pembelian</h4>
                                <p class="channel-desc">Digunakan otomatis pada transaksi PO Pembelian &amp; Penerimaan Barang</p>
                            </div>
                        </div>

                        {{-- Modern Switch --}}
                        <label class="modern-switch">
                            <input type="checkbox" id="create_is_pembelian" name="is_pembelian" value="1" checked onchange="onChannelToggleChanged(this, 'card_channel_create_pembelian', 'create_coa_pembelian_id')">
                            <span class="modern-switch-slider"></span>
                        </label>
                    </div>

                    <div class="channel-select-wrap" id="wrap_create_coa_pembelian">
                        <label class="form-label-modern font-xs text-muted" for="create_coa_pembelian_id">Pilih Akun Buku Besar (PPN Masukan):</label>
                        <select id="create_coa_pembelian_id" name="coa_pembelian_id" class="form-control-modern">
                            <option value="">-- Pilih Akun Pembelian (COA) --</option>
                            @foreach ($coaList as $coa)
                                <option value="{{ $coa->coa_id }}" {{ str_contains(strtoupper($coa->nama_akun), 'MASUKAN') ? 'selected' : '' }}>
                                    [{{ $coa->kode_akun }}] {{ $coa->nama_akun }} ({{ $coa->kategori_akun }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Channel Card 2: Akun Pajak Penjualan (Faktur Jual / Invoice) --}}
                <div class="tax-channel-card channel-active" id="card_channel_create_penjualan">
                    <div class="channel-card-header">
                        <div class="channel-header-info">
                            <div class="channel-icon-wrap icon-sell">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="channel-title">Akun Pajak Penjualan</h4>
                                <p class="channel-desc">Digunakan otomatis pada penagihan Invoice &amp; Penjualan ke Customer</p>
                            </div>
                        </div>

                        {{-- Modern Switch (Blue accent) --}}
                        <label class="modern-switch switch-blue">
                            <input type="checkbox" id="create_is_penjualan" name="is_penjualan" value="1" checked onchange="onChannelToggleChanged(this, 'card_channel_create_penjualan', 'create_coa_penjualan_id')">
                            <span class="modern-switch-slider"></span>
                        </label>
                    </div>

                    <div class="channel-select-wrap" id="wrap_create_coa_penjualan">
                        <label class="form-label-modern font-xs text-muted" for="create_coa_penjualan_id">Pilih Akun Buku Besar (PPN Keluaran):</label>
                        <select id="create_coa_penjualan_id" name="coa_penjualan_id" class="form-control-modern">
                            <option value="">-- Pilih Akun Penjualan (COA) --</option>
                            @foreach ($coaList as $coa)
                                <option value="{{ $coa->coa_id }}" {{ str_contains(strtoupper($coa->nama_akun), 'KELUARAN') ? 'selected' : '' }}>
                                    [{{ $coa->kode_akun }}] {{ $coa->nama_akun }} ({{ $coa->kategori_akun }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Status Aktif Baris --}}
                <div class="status-toggle-card">
                    <div class="status-toggle-left">
                        <svg width="22" height="22" fill="none" stroke="#059669" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="status-toggle-text">
                            <span class="status-toggle-title">Status Pajak Aktif</span>
                            <span class="status-toggle-sub">Pajak ini langsung dapat dipilih pada transaksi operasional</span>
                        </div>
                    </div>

                    <label class="modern-switch">
                        <input type="checkbox" id="create_active_st" name="active_st" value="1" checked>
                        <span class="modern-switch-slider"></span>
                    </label>
                </div>
            </div>

            {{-- Modern Modal Footer --}}
            <div class="modern-modal-footer">
                <button type="button" class="btn-modern-cancel" onclick="closeModal('modalTambahPajak')">
                    Batal
                </button>
                <button type="submit" class="btn-modern-submit">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan Pajak</span>
                </button>
            </div>
        </form>
    </div>
</div>
