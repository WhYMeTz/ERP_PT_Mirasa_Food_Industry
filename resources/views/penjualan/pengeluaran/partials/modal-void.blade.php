{{-- MODAL VOID / PEMBATALAN SURAT JALAN (AGENTS.md Standard) --}}
<div id="modalVoidPengeluaran" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 99999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; border: 1px solid #fee2e2;">
        
        {{-- Header Bahaya (Merah Lembut) --}}
        <div style="background: #fef2f2; border-bottom: 1px solid #fee2e2; padding: 1.15rem 1.25rem; display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 38px; height: 38px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626; flex-shrink: 0; border: 2px solid #fca5a5;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: #991b1b; margin: 0;">
                    Batalkan Dokumen Pengeluaran (Surat Jalan)?
                </h3>
                <div style="font-size: 0.775rem; color: #b91c1c; margin-top: 0.15rem;">
                    Tindakan ini memiliki dampak operasional stok gudang.
                </div>
            </div>
        </div>

        {{-- Form & Body --}}
        <form id="formVoidPengeluaran" method="POST" action="">
            @csrf
            <div style="padding: 1.25rem;">
                {{-- Box Peringatan Dampak --}}
                <div style="background: #fff1f2; border: 1px dashed #fecdd3; border-radius: 8px; padding: 0.85rem; margin-bottom: 1.15rem;">
                    <div style="font-size: 0.8125rem; color: #be123c; line-height: 1.45;">
                        <strong>Peringatan Otomatisasi Sistem:</strong>
                        <ul style="margin: 0.35rem 0 0 0; padding-left: 1.15rem; font-size: 0.775rem;">
                            <li>Seluruh kuantitas barang pada Surat Jalan <strong id="voidSuratJalanNoSpan">-</strong> akan <strong>dikembalikan ke stok fisik Gudang Barang Jadi (GDG-FG)</strong>.</li>
                            <li>Status PO Penjualan terkait akan dipulihkan kembali ke status <strong>DISETUJUI / SEBAGIAN</strong>.</li>
                        </ul>
                    </div>
                </div>

                {{-- Input Alasan Pembatalan --}}
                <div style="margin-bottom: 0.5rem;">
                    <label for="voidReasonInput" style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Alasan Pembatalan / Void <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea name="reason" id="voidReasonInput" rows="3" required
                              placeholder="Contoh: Kesalahan armada truk / Truk mogok / Revisi pesanan customer..."
                              style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.6rem 0.75rem; font-size: 0.8125rem; color: #1e293b; outline: none; resize: vertical;"></textarea>
                </div>
            </div>

            {{-- Footer Tombol --}}
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.85rem 1.25rem; display: flex; justify-content: flex-end; gap: 0.65rem;">
                <button type="button" onclick="closeVoidModal()"
                        style="padding: 0.45rem 1rem; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.825rem; font-weight: 600; color: #475569; cursor: pointer;">
                    Tutup / Batal
                </button>
                <button type="submit"
                        style="padding: 0.45rem 1.15rem; background: #dc2626; border: 1px solid #b91c1c; border-radius: 6px; font-size: 0.825rem; font-weight: 700; color: #ffffff; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 1px 2px rgba(220, 38, 38, 0.2);">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Konfirmasi Pembatalan</span>
                </button>
            </div>
        </form>

    </div>
</div>
