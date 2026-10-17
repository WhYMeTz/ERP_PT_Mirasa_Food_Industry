{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL UBAH STANDAR HARGA KEMASAN TUNGGAL                                  --}}
{{-- ERP PT Mirasa Food Industry                                               --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}

<div id="modalEditTarifKemasan" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-card-header">
            <div>
                <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0;">
                    Ubah Standar Harga Kemasan
                </h3>
                <span style="font-size: 0.75rem; color: #64748b;">
                    Parameter pengali biaya HPP kemasan produk jadi
                </span>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeEditTarifModal()">&times;</button>
        </div>

        <form id="formEditTarifKemasan" method="POST" action="">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_tarif_id" name="tarif_id">

            <div class="modal-card-body">
                {{-- Info Ringkas Item --}}
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                        <span id="edit_kode_tarif_badge" style="font-family: monospace; font-size: 0.75rem; font-weight: 700; background: #e0f2fe; color: #0369a1; padding: 0.15rem 0.4rem; border-radius: 4px;">
                            KARTON_XX
                        </span>
                        <span id="edit_kategori_display" style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase;">
                            KARTON
                        </span>
                    </div>
                    <div id="edit_nama_tarif_display" style="font-weight: 800; color: #0f172a; font-size: 0.95rem;">
                        Karton Box Ping-Ping (XX)
                    </div>
                </div>

                {{-- Input Nilai Tarif Utama --}}
                <div style="margin-bottom: 1rem;">
                    <label for="edit_nilai_tarif" style="display: block; font-size: 0.8125rem; font-weight: 700; color: #1e293b; margin-bottom: 0.35rem;">
                        Besaran Nilai Acuan Utama <span style="color: #ef4444;">*</span>
                    </label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); font-weight: 700; color: #64748b; font-size: 0.85rem;">Rp</span>
                        <input type="number" step="0.0001" min="0" id="edit_nilai_tarif" name="nilai_tarif" required class="form-control" style="padding-left: 2.25rem; font-weight: 800; font-size: 1rem; font-family: monospace; color: #0f172a;" placeholder="Contoh: 1338.90">
                    </div>
                    <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.25rem; display: block;">
                        Satuan basis acuan: <strong id="edit_satuan_label_display" style="color: #0284c7;">Rp / Lembar</strong>
                    </small>
                </div>

                {{-- Input Nilai Tarif Sekunder (Alternatif / Konversi) --}}
                <div style="margin-bottom: 1rem;">
                    <label for="edit_nilai_tarif_sekunder" style="display: block; font-size: 0.8125rem; font-weight: 700; color: #1e293b; margin-bottom: 0.35rem;">
                        Nilai Alternatif / Konversi Satuan <span style="font-weight: 400; color: #64748b;">(Opsional)</span>
                    </label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); font-weight: 700; color: #64748b; font-size: 0.85rem;">Rp</span>
                        <input type="number" step="0.0001" min="0" id="edit_nilai_tarif_sekunder" name="nilai_tarif_sekunder" class="form-control" style="padding-left: 2.25rem; font-weight: 700; font-size: 0.95rem; font-family: monospace; color: #334155;" placeholder="Contoh: 44819.00">
                    </div>
                    <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.25rem; display: block;">
                        Digunakan untuk konversi satuan lain (misal: harga per Kg untuk roll plastik atau harga update nota).
                    </small>
                </div>

                {{-- Tanggal Berlaku Sejak --}}
                <div style="margin-bottom: 1rem;">
                    <label for="edit_berlaku_sejak" style="display: block; font-size: 0.8125rem; font-weight: 700; color: #1e293b; margin-bottom: 0.35rem;">
                        Tanggal Efektif Nota Supplier <span style="font-weight: 400; color: #64748b;">(Opsional)</span>
                    </label>
                    <input type="date" id="edit_berlaku_sejak" name="berlaku_sejak" class="form-control" style="font-size: 0.85rem;">
                    <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.25rem; display: block;">
                        Sesuai catatan tanggal di Excel pabrik (misal tanggal pembelian atau update supplier).
                    </small>
                </div>

                {{-- Penjelasan / Keterangan --}}
                <div style="margin-bottom: 0.5rem;">
                    <label for="edit_keterangan" style="display: block; font-size: 0.8125rem; font-weight: 700; color: #1e293b; margin-bottom: 0.35rem;">
                        Penjelasan / Formula Komponen
                    </label>
                    <textarea id="edit_keterangan" name="keterangan" rows="2" class="form-control" style="font-size: 0.85rem;" placeholder="Catatan operasional..."></textarea>
                </div>
            </div>

            <div class="modal-card-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditTarifModal()" style="font-size: 0.825rem; padding: 0.45rem 1rem;">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" style="font-size: 0.825rem; padding: 0.45rem 1.25rem; background: #0284c7; border-color: #0284c7; font-weight: 700;">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
