{{-- MODAL PERINGATAN KONFIRMASI SIMPAN DOKUMEN PERIODE BULAN SEBELUMNYA --}}
<div id="modalConfirmPreviousMonth" class="no-print" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 99999; align-items: center; justify-content: center; padding: 1rem; backdrop-filter: blur(2px);">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 540px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; border: 1.5px solid #fde68a; animation: modalFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
        
        {{-- Header Modal Bertema Peringatan Operasional --}}
        <div style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-bottom: 1.5px solid #fde68a; padding: 1.15rem 1.35rem; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: #fef3c7; border: 1.5px solid #fcd34d; display: flex; align-items: center; justify-content: center; color: #b45309; flex-shrink: 0;">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <strong id="mcpmTitle" style="color: #92400e; font-size: 1.05rem; display: block; line-height: 1.25;">Peringatan Dokumen Pengadaan</strong>
                    <span id="mcpmSubtitle" style="font-size: 0.775rem; color: #b45309;">Perhatian khusus penomoran dokumen &amp; periode pembukuan</span>
                </div>
            </div>
            <button type="button" onclick="closePreviousMonthModal()" style="background: transparent; border: none; font-size: 1.35rem; color: #92400e; cursor: pointer; line-height: 1; padding: 0.2rem 0.45rem; border-radius: 6px;">&times;</button>
        </div>

        {{-- Isi Modal --}}
        <div style="padding: 1.35rem;">
            
            {{-- Kartu Ringkasan Dokumen --}}
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.15rem; font-size: 0.85rem;">
                <div style="display: flex; justify-content: space-between; padding-bottom: 0.45rem; border-bottom: 1px dashed #cbd5e1; margin-bottom: 0.45rem;">
                    <span style="color: #64748b;">Nomor PO Diinput:</span>
                    <strong id="mcpmPoNo" style="color: #0f172a; font-family: monospace; font-size: 0.95rem;">-</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 0.45rem; border-bottom: 1px dashed #cbd5e1; margin-bottom: 0.45rem;">
                    <span style="color: #64748b;">Tanggal Dokumen PO:</span>
                    <strong id="mcpmPoTgl" style="color: #b45309;">-</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding-top: 0.15rem;">
                    <span style="color: #64748b;">Periode Aktif Saat Ini:</span>
                    <strong style="color: #047857;">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</strong>
                </div>
            </div>

            {{-- Pesan Dampak Operasional Dinamis --}}
            <div class="alert" style="background: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid #f59e0b; color: #92400e; padding: 0.85rem 1rem; font-size: 0.8125rem; border-radius: 6px; margin-bottom: 1.25rem; line-height: 1.5;">
                <strong style="display: block; font-size: 0.85rem; margin-bottom: 0.35rem;">⚠️ Perhatian Operasional:</strong>
                <div id="mcpmAlertContent">
                    {{-- Diisi secara dinamis oleh JavaScript --}}
                </div>
            </div>

            <p id="mcpmQuestion" style="font-size: 0.825rem; color: #475569; margin: 0 0 1.25rem 0; line-height: 1.45; font-weight: 500;">
                Apakah Anda yakin data yang dimasukkan sudah benar dan ingin tetap melanjutkan penyimpanan dokumen ini?
            </p>

            {{-- Tombol Aksi --}}
            <div style="display: flex; justify-content: flex-end; gap: 0.65rem; border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                <button type="button" onclick="closePreviousMonthModal()" class="btn btn-secondary" style="padding: 0.6rem 1.15rem; font-size: 0.85rem; font-weight: 600;">
                    Periksa Kembali / Batal
                </button>
                <button type="button" id="btnConfirmSubmitPreviousMonth" style="background: #d97706; color: #ffffff; border: none; border-radius: 6px; padding: 0.6rem 1.25rem; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem; cursor: pointer; box-shadow: 0 2px 4px rgba(217, 119, 6, 0.3);">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Ya, Lanjutkan Simpan
                </button>
            </div>
        </div>
    </div>
</div>

