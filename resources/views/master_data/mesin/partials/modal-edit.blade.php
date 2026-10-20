{{-- MODAL EDIT DATA MESIN OPERASIONAL --}}
<div id="modalEditMesin" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 640px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <div style="width: 34px; height: 34px; border-radius: 8px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <div>
                    <h2 class="modal-title" style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin: 0;">Ubah Data Mesin Operasional</h2>
                    <p style="font-size: 0.75rem; color: #64748b; margin: 0.1rem 0 0 0;">Perbarui data spesifikasi teknis atau status kesiapan mesin pabrik.</p>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalEditMesin')">&times;</button>
        </div>
        <form id="formEditMesin" method="POST" action="">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_mesin_id" name="mesin_id">

            <div class="modal-body" style="padding: 1.25rem 1.5rem;">
                {{-- Baris 1: Kode & Nama Mesin --}}
                <div class="form-row" style="margin-bottom: 1rem;">
                    <div>
                        <label for="edit_mesin_cd" class="form-label">
                            Kode Mesin <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text" id="edit_mesin_cd" name="mesin_cd" class="form-control" style="text-transform: uppercase;" required>
                        <span class="form-hint">Kode unik identifikasi mesin</span>
                    </div>
                    <div>
                        <label for="edit_mesin_nm" class="form-label">
                            Nama Mesin Operasional <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text" id="edit_mesin_nm" name="mesin_nm" class="form-control" required>
                    </div>
                </div>

                {{-- Baris 2: Kategori & Jenis Proses --}}
                <div class="form-row" style="margin-bottom: 1rem;">
                    <div>
                        <label for="edit_kategori_mesin" class="form-label">
                            Kategori Mesin <span style="color:#ef4444;">*</span>
                        </label>
                        <select id="edit_kategori_mesin" name="kategori_mesin" class="form-control" onchange="onKategoriMesinChanged(this.value, 'edit')" required>
                            <option value="PRODUKSI">PRODUKSI (Penggorengan / Pengolahan)</option>
                            <option value="PACKAGING">PACKAGING (Pengemasan Retail / Karton)</option>
                        </select>
                    </div>
                    <div>
                        <label for="edit_jenis_proses" class="form-label">
                            Jenis Proses Kerja
                        </label>
                        <select id="edit_jenis_proses" name="jenis_proses" class="form-control">
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
                        <label for="edit_kapasitas_per_jam" class="form-label">
                            Kapasitas Standar / Jam
                        </label>
                        <input type="number" step="0.01" min="0" id="edit_kapasitas_per_jam" name="kapasitas_per_jam" class="form-control">
                    </div>
                    <div>
                        <label for="edit_satuan_kapasitas" class="form-label">
                            Satuan Kapasitas
                        </label>
                        <select id="edit_satuan_kapasitas" name="satuan_kapasitas" class="form-control">
                            <option value="KG/JAM">KG/JAM (Untuk Pengolahan / Bahan)</option>
                            <option value="KEMASAN/MENIT">KEMASAN/MENIT (Untuk Sealer / VFFS)</option>
                            <option value="KARTON/MENIT">KARTON/MENIT (Untuk Strapping Box)</option>
                            <option value="KARTON/JAM">KARTON/JAM</option>
                        </select>
                    </div>
                </div>

                {{-- Baris 4: Tipe Energi & Kondisi --}}
                <div class="form-row" style="margin-bottom: 1rem;">
                    <div>
                        <label for="edit_tipe_energi" class="form-label">
                            Sumber Energi / Daya
                        </label>
                        <select id="edit_tipe_energi" name="tipe_energi" class="form-control">
                            <option value="LISTRIK">LISTRIK (PLN / Genset)</option>
                            <option value="GAS_CNG">GAS CNG (Pipa Gas Alam)</option>
                            <option value="LPG">LPG (Tabung Gas)</option>
                            <option value="SOLAR">SOLAR (Diesel)</option>
                            <option value="MANUAL">MANUAL (Tenaga Kerja)</option>
                        </select>
                    </div>
                    <div>
                        <label for="edit_status_kondisi" class="form-label">
                            Status Kesiapan Mesin <span style="color:#ef4444;">*</span>
                        </label>
                        <select id="edit_status_kondisi" name="status_kondisi" class="form-control" required>
                            <option value="SIAP">SIAP OPERASI (Ready)</option>
                            <option value="MAINTENANCE">MAINTENANCE (Sedang Servis)</option>
                            <option value="RUSAK">RUSAK (Perlu Perbaikan)</option>
                        </select>
                    </div>
                </div>

                {{-- Baris 5: Keterangan Operasional --}}
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="edit_keterangan" class="form-label">Catatan Spesifikasi / Penempatan Mesin</label>
                    <input type="text" id="edit_keterangan" name="keterangan" class="form-control">
                </div>

                {{-- Status Aktif Checkbox --}}
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <span style="font-size: 0.825rem; font-weight: 700; color: #1e293b; display: block;">Status Operasional Mesin</span>
                        <span style="font-size: 0.75rem; color: #64748b;">Nonaktifkan jika mesin sudah tidak digunakan lagi di pabrik.</span>
                    </div>
                    <label style="display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                        <input type="hidden" name="active_st" value="0">
                        <input type="checkbox" id="edit_active_st" name="active_st" value="1" style="width: 1.15rem; height: 1.15rem; accent-color: #059669;">
                        <span style="font-size: 0.825rem; font-weight: 600; color: #334155;">Aktif</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditMesin')">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700; background: #059669;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
