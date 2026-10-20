{{-- MODAL TAMBAH MESIN OPERASIONAL BARU --}}
<div id="modalTambahMesin" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 640px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <div style="width: 34px; height: 34px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                <div>
                    <h2 class="modal-title" style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin: 0;">Tambah Mesin Operasional Pabrik</h2>
                    <p style="font-size: 0.75rem; color: #64748b; margin: 0.1rem 0 0 0;">Daftarkan mesin baru untuk lini penggorengan, pemotongan, atau pengemasan.</p>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalTambahMesin')">&times;</button>
        </div>
        <form action="{{ route('master.mesin.store') }}" method="POST">
            @csrf
            <div class="modal-body" style="padding: 1.25rem 1.5rem;">
                {{-- Baris 1: Kode & Nama Mesin --}}
                <div class="form-row" style="margin-bottom: 1rem;">
                    <div>
                        <label for="create_mesin_cd" class="form-label">
                            Kode Mesin <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text" id="create_mesin_cd" name="mesin_cd" class="form-control" placeholder="Contoh: FRY-IFL-02, PKG-CS-03" style="text-transform: uppercase;" required>
                        <span class="form-hint">Kode unik mesin di lantai pabrik</span>
                    </div>
                    <div>
                        <label for="create_mesin_nm" class="form-label">
                            Nama Mesin Operasional <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text" id="create_mesin_nm" name="mesin_nm" class="form-control" placeholder="Contoh: Continuous Fryer Line 2" required>
                        <span class="form-hint">Nama spesifik / label identitas mesin</span>
                    </div>
                </div>

                {{-- Baris 2: Kategori & Jenis Proses --}}
                <div class="form-row" style="margin-bottom: 1rem;">
                    <div>
                        <label for="create_kategori_mesin" class="form-label">
                            Kategori Mesin <span style="color:#ef4444;">*</span>
                        </label>
                        <select id="create_kategori_mesin" name="kategori_mesin" class="form-control" onchange="onKategoriMesinChanged(this.value, 'create')" required>
                            <option value="PRODUKSI">PRODUKSI (Penggorengan / Pengolahan)</option>
                            <option value="PACKAGING">PACKAGING (Pengemasan Retail / Karton)</option>
                        </select>
                        <span class="form-hint">Tahap operasional penggunaan mesin</span>
                    </div>
                    <div>
                        <label for="create_jenis_proses" class="form-label">
                            Jenis Proses Kerja
                        </label>
                        <select id="create_jenis_proses" name="jenis_proses" class="form-control">
                            <option value="">-- Pilih Jenis Proses --</option>
                            <optgroup label="Proses Produksi">
                                <option value="PENGGORENGAN">PENGGORENGAN (Fryer Continuous / Manual)</option>
                                <option value="PEMOTONGAN">PEMOTONGAN (Slicer Singkong)</option>
                                <option value="PENIRISAN">PENIRISAN (Spinner Minyak)</option>
                                <option value="PENCAMPURAN_BUMBU">PENCAMPURAN BUMBU (Seasoning)</option>
                            </optgroup>
                            <optgroup label="Proses Packaging">
                                <option value="PENGEMASAN_SEALER">PENGEMASAN BAND SEALER</option>
                                <option value="PENGEMASAN_OTOMATIS">PENGEMASAN OTOMATIS (VFFS Sachet)</option>
                                <option value="PENGEMASAN_KARTON">PENGEMASAN KARTON (Strapping Band)</option>
                            </optgroup>
                        </select>
                    </div>
                </div>

                {{-- Baris 3: Kapasitas Output & Satuan --}}
                <div class="form-row" style="margin-bottom: 1rem;">
                    <div>
                        <label for="create_kapasitas_per_jam" class="form-label">
                            Kapasitas Standar / Jam
                        </label>
                        <input type="number" step="0.01" min="0" id="create_kapasitas_per_jam" name="kapasitas_per_jam" class="form-control" placeholder="Contoh: 150">
                        <span class="form-hint">Kemampuan output ideal mesin</span>
                    </div>
                    <div>
                        <label for="create_satuan_kapasitas" class="form-label">
                            Satuan Kapasitas
                        </label>
                        <select id="create_satuan_kapasitas" name="satuan_kapasitas" class="form-control">
                            <option value="KG/JAM">KG/JAM (Untuk Pengolahan / Bahan)</option>
                            <option value="KEMASAN/MENIT">KEMASAN/MENIT (Untuk Sealer / VFFS)</option>
                            <option value="KARTON/MENIT">KARTON/MENIT (Untuk Strapping Box)</option>
                            <option value="KARTON/JAM">KARTON/JAM</option>
                        </select>
                    </div>
                </div>

                {{-- Baris 4: Tipe Energi & Kondisi Awal --}}
                <div class="form-row" style="margin-bottom: 1rem;">
                    <div>
                        <label for="create_tipe_energi" class="form-label">
                            Sumber Energi / Daya
                        </label>
                        <select id="create_tipe_energi" name="tipe_energi" class="form-control">
                            <option value="LISTRIK">LISTRIK (PLN / Genset)</option>
                            <option value="GAS_CNG">GAS CNG (Pipa Gas Alam)</option>
                            <option value="LPG">LPG (Tabung Gas)</option>
                            <option value="SOLAR">SOLAR (Diesel)</option>
                            <option value="MANUAL">MANUAL (Tenaga Kerja)</option>
                        </select>
                    </div>
                    <div>
                        <label for="create_status_kondisi" class="form-label">
                            Status Kesiapan Awal <span style="color:#ef4444;">*</span>
                        </label>
                        <select id="create_status_kondisi" name="status_kondisi" class="form-control" required>
                            <option value="SIAP">SIAP OPERASI (Ready)</option>
                            <option value="MAINTENANCE">MAINTENANCE (Sedang Servis)</option>
                            <option value="RUSAK">RUSAK (Perlu Perbaikan)</option>
                        </select>
                    </div>
                </div>

                {{-- Baris 5: Keterangan Operasional --}}
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="create_keterangan" class="form-label">Catatan Spesifikasi / Penempatan Mesin</label>
                    <input type="text" id="create_keterangan" name="keterangan" class="form-control" placeholder="Contoh: Terpasang di Jalur Penggorengan 1 sebelah barat">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahMesin')">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700; background: #0284c7;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Mesin Baru
                </button>
            </div>
        </form>
    </div>
</div>
