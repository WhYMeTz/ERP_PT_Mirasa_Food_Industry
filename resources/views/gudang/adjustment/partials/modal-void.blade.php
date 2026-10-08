{{-- 
    Modal Konfirmasi Bahaya Pembatalan Dokumen Adjustment (Void)
    ERP PT Mirasa Food Industry - Standar Keamanan & Tanpa Alert Browser
--}}
<div id="modalVoidAdjustment" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 10px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15); overflow: hidden; border: 1px solid #fecaca;">
        {{-- Modal Header Bahaya --}}
        <div style="background: #fef2f2; padding: 1.25rem 1.5rem; border-bottom: 1px solid #fee2e2; display: flex; align-items: center; gap: 0.75rem;">
            <div style="background: #fee2e2; color: #dc2626; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #991b1b;">Konfirmasi Pembatalan (Void)</h3>
                <p style="margin: 0.15rem 0 0 0; font-size: 0.775rem; color: #b91c1c;">Dokumen: <span id="voidAdjNoLabel" style="font-weight: 700;">-</span></p>
            </div>
        </div>

        {{-- Form Isi Alasan --}}
        <form id="formVoidAdjustment" method="POST" action="">
            @csrf
            <div style="padding: 1.5rem;">
                <p style="margin: 0 0 1rem 0; font-size: 0.85rem; color: #475569; line-height: 1.5;">
                    Apakah Anda yakin ingin membatalkan dokumen penyesuaian stok ini? 
                    <strong style="color: #b91c1c;">Seluruh kuantitas selisih yang pernah diposting akan dikembalikan (rollback) ke kartu stok gudang.</strong>
                </p>

                <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                    <label for="alasan_batal" style="font-size: 0.8rem; font-weight: 600; color: #334155;">Alasan Pembatalan <span style="color: #dc2626;">*</span></label>
                    <textarea name="alasan_batal" id="alasan_batal" rows="3" required
                              style="width: 100%; padding: 0.5rem 0.75rem; font-size: 0.85rem; border: 1px solid #cbd5e1; border-radius: 6px; outline: none;"
                              placeholder="Masukkan alasan pembatalan (misal: Salah hitung timbangan fisik, salah input batch)"></textarea>
                </div>
            </div>

            {{-- Footer Tombol --}}
            <div style="background: #f8fafc; padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" onclick="closeVoidModal()"
                        style="padding: 0.5rem 1rem; font-size: 0.825rem; font-weight: 600; color: #475569; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer;">
                    Batal
                </button>
                <button type="submit"
                        style="padding: 0.5rem 1.125rem; font-size: 0.825rem; font-weight: 600; color: #ffffff; background: #dc2626; border: 1px solid #dc2626; border-radius: 6px; cursor: pointer;">
                    Ya, Batalkan Dokumen
                </button>
            </div>
        </form>
    </div>
</div>
