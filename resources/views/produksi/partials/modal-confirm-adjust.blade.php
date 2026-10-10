{{-- Modal Konfirmasi Penerapan Penyesuaian Utilitas (Sesuai Standar AGENTS.md) --}}
<div id="modalConfirmAdjust" class="modal-overlay" style="display: none;" onclick="if(event.target === this) closeModalConfirmAdjust()">
    <div class="modal-dialog-custom" style="max-width: 520px;">
        {{-- Header Konfirmasi (Tema Biru Enterprise) --}}
        <div style="background: #f0f9ff; border-bottom: 1.5px solid #bae6fd; padding: 1.1rem 1.35rem; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: #0284c7; flex-shrink: 0;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 style="font-size: 1rem; font-weight: 800; color: #0369a1; margin: 0;">
                        Konfirmasi Penyesuaian Biaya Utilitas
                    </h3>
                    <p style="font-size: 0.75rem; color: #0284c7; margin: 0.15rem 0 0 0;">
                        Periode: <strong>{{ $monthsList[$month] ?? '' }} {{ $year }}</strong>
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeModalConfirmAdjust()" style="background: none; border: none; font-size: 1.3rem; color: #0369a1; cursor: pointer; padding: 0.2rem 0.4rem; line-height: 1;">
                &times;
            </button>
        </div>

        {{-- Ringkasan Parameter yang Akan Diterapkan --}}
        <div style="padding: 1.25rem 1.35rem;">
            <p style="font-size: 0.85rem; color: #334155; line-height: 1.5; margin: 0 0 1rem 0;">
                Apakah Anda yakin ingin menerapkan rekonsiliasi biaya utilitas berikut ke seluruh lembar produksi harian?
            </p>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem; font-size: 0.8rem; margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem;">
                    <span style="color: #64748b;">Periode Kalender:</span>
                    <strong style="color: #0f172a;">{{ $monthsList[$month] ?? '' }} {{ $year }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem;">
                    <span style="color: #64748b;">Jumlah Hari Kerja:</span>
                    <strong style="color: #0f172a;">{{ $prodDays->count() }} Hari ({{ $modalCount }} Dokumen)</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem;">
                    <span style="color: #64748b;">Beban Listrik PLN:</span>
                    <strong id="confirmSummaryListrik" style="color: #0284c7; font-family: monospace;">-</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #64748b;">Beban Gas CNG:</span>
                    <strong id="confirmSummaryGas" style="color: #d97706; font-family: monospace;">-</strong>
                </div>
            </div>

            <div style="background: #fffbeb; border-left: 3px solid #f59e0b; padding: 0.65rem 0.85rem; border-radius: 4px; font-size: 0.75rem; color: #92400e; line-height: 1.4;">
                <strong>Dampak Operasional:</strong> Beban utilitas dan kalkulasi HPP per kilogram pada setiap lembar catatan produksi di bulan ini akan otomatis diselaraskan secara permanen (*Actual Costing*).
            </div>
        </div>

        {{-- Footer Tombol Aksi --}}
        <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.85rem 1.35rem; display: flex; justify-content: flex-end; gap: 0.65rem;">
            <button type="button" onclick="closeModalConfirmAdjust()" class="btn btn-secondary" style="font-size: 0.825rem; padding: 0.45rem 1rem;">
                Batal
            </button>
            <button type="button" onclick="submitAdjustmentForm()" class="btn btn-primary" style="font-size: 0.825rem; padding: 0.45rem 1.15rem; background: #059669; border-color: #059669; color: #ffffff; font-weight: 700;">
                Ya, Terapkan Penyesuaian
            </button>
        </div>
    </div>
</div>
