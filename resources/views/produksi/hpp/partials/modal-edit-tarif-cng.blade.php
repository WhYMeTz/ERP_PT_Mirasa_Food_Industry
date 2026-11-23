{{-- Modal Ubah Standar Acuan Tarif Kontrak Gas (CNG) --}}
<div id="modalEditTarifCng" class="hpp-loading-backdrop" style="display: none; align-items: center; justify-content: center; z-index: 9999;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 440px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); overflow: hidden; border: 1px solid #e2e8f0;">
        <div style="padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.15rem;">🔥</span>
                <strong style="font-size: 0.95rem; color: #0f172a;">Ubah Standar Tarif Kontrak Gas (CNG)</strong>
            </div>
            <button type="button" onclick="closeModalEditTarifCng()" style="background: none; border: none; font-size: 1.35rem; color: #94a3b8; cursor: pointer; line-height: 1;">&times;</button>
        </div>
        <div style="padding: 1.25rem;">
            <p style="font-size: 0.8125rem; color: #64748b; margin-top: 0; margin-bottom: 1rem; line-height: 1.45;">
                Tarif kontrak gas disepakati jangka panjang dengan vendor penyedia gas (biasanya berlaku 1&ndash;2 tahun). Mengubah nilai ini akan memperbarui <strong>Master Data Standar Tarif</strong> untuk seluruh kalkulasi HPP ke depan.
            </p>
            <div class="form-group-hpp" style="margin-bottom: 1rem;">
                <label for="modal_new_tarif_usd" class="hpp-label-sm" style="font-weight: 700; color: #334155;">Tarif Kontrak Baru (USD / MMBTU) <span class="required">*</span></label>
                <div class="input-usd-wrap" style="margin-top: 0.35rem;">
                    <span class="prefix-usd">$</span>
                    <input type="number" step="0.01" min="0" id="modal_new_tarif_usd" class="hpp-input-sm" style="font-size: 1rem; font-weight: 700; color: #0f172a;" value="{{ $defaultCngTarifUsd }}">
                </div>
                <small class="helper-text">Standar saat ini: <strong>${{ number_format($defaultCngTarifUsd, 2) }}</strong></small>
            </div>
            <div id="modalTarifCngAlert" style="display: none; padding: 0.6rem 0.75rem; border-radius: 6px; font-size: 0.8rem; margin-bottom: 1rem;"></div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.25rem;">
                <a href="{{ route('master.tarif_produksi.index', ['kategori' => 'ENERGI']) }}" target="_blank" style="font-size: 0.75rem; color: #0284c7; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;">
                    <span>Buka Master Data &rarr;</span>
                </a>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="button" onclick="closeModalEditTarifCng()" class="btn btn-secondary btn-sm" style="font-weight: 600;">Batal</button>
                    <button type="button" id="btnSaveTarifCng" onclick="saveNewTarifCngStandar()" class="btn btn-primary btn-sm" style="background: #0284c7; border-color: #0284c7; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Standar Baru</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
