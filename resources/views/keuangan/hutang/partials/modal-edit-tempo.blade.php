<div class="modal-backdrop" id="modalEditTempo" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 1050; align-items: center; justify-content: center; padding: 1rem;">
    <div class="modal-dialog" style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; animation: modalFadeIn 0.2s ease;">
        <div class="modal-header" style="background: #f0fdf4; border-bottom: 1.5px solid #dcfce7; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #dcfce7; color: #166534; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                    ✏️
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: #14532d;">Sesuaikan Faktur &amp; Jatuh Tempo</h3>
                    <div style="font-size: 0.75rem; color: #166534;">Perbarui nomor faktur fisik supplier dan tanggal batas bayar</div>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="closeEditTempoModal()" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <form id="formEditTempo" method="POST" action="{{ route('keuangan.hutang.update-tempo', $hutang->hutang_id) }}">
            @csrf
            @method('PATCH')
            
            <div class="modal-body" style="padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <label for="modal_tgl_jatuh_tempo" style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Tanggal Jatuh Tempo <span style="color: #dc2626;">*</span>
                    </label>
                    <input type="date" id="modal_tgl_jatuh_tempo" name="tgl_jatuh_tempo" 
                           value="{{ old('tgl_jatuh_tempo', $hutang->tgl_jatuh_tempo ? \Carbon\Carbon::parse($hutang->tgl_jatuh_tempo)->format('Y-m-d') : '') }}" 
                           class="form-control" style="width: 100%; height: 38px; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0 0.75rem; font-size: 0.85rem;" required>
                    <small style="font-size: 0.72rem; color: #64748b;">Tenggat waktu pembayaran tagihan ini kepada supplier.</small>
                </div>

                <div>
                    <label for="modal_no_faktur_supplier" style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        No. Faktur Fisik Supplier <span style="font-size: 0.75rem; font-weight: 400; color: #64748b;">(Opsional)</span>
                    </label>
                    <input type="text" id="modal_no_faktur_supplier" name="no_faktur_supplier" 
                           value="{{ old('no_faktur_supplier', $hutang->no_faktur_supplier) }}" 
                           placeholder="Contoh: INV-2026/10/8892" 
                           class="form-control" style="width: 100%; height: 38px; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0 0.75rem; font-size: 0.85rem;">
                    <small style="font-size: 0.72rem; color: #64748b;">Nomor invoice resmi dari supplier jika fisik faktur sudah diterima.</small>
                </div>

                <div>
                    <label for="modal_catatan_edit" style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Catatan Perubahan <span style="font-size: 0.75rem; font-weight: 400; color: #64748b;">(Opsional)</span>
                    </label>
                    <input type="text" id="modal_catatan_edit" name="catatan" 
                           placeholder="Contoh: Penyesuaian tempo sesuai invoice fisik supplier" 
                           class="form-control" style="width: 100%; height: 38px; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0 0.75rem; font-size: 0.85rem;">
                </div>
            </div>

            <div class="modal-footer" style="padding: 0.85rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.6rem;">
                <button type="button" class="btn btn-secondary" onclick="closeEditTempoModal()" style="border: 1px solid #cbd5e1; background: #ffffff; color: #475569; padding: 0.5rem 1rem; border-radius: 6px; font-weight: 700; font-size: 0.82rem; cursor: pointer;">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" style="background: #059669; border: none; color: #ffffff; padding: 0.5rem 1.25rem; border-radius: 6px; font-weight: 700; font-size: 0.82rem; cursor: pointer; box-shadow: 0 2px 4px rgba(5, 150, 105, 0.25);">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
