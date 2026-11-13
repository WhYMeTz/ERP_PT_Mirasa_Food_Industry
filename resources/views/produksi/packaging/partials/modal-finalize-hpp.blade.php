{{-- ════════════════════════════════════════════════════════════════ --}}
{{--  MODAL KONFIRMASI TARIK & FINALISASI HPP PACKAGING (BARANG JADI) --}}
{{--  ERP PT Mirasa Food Industry - Standar Enterprise NetSuite        --}}
{{-- ════════════════════════════════════════════════════════════════ --}}

<div id="modalFinalizePackagingHpp" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 999999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.08); overflow: hidden; animation: popIn 0.18s cubic-bezier(0.16, 1, 0.3, 1);">
        
        {{-- Header Modal --}}
        <div style="background: #f0fdf4; border-bottom: 1px solid #bbf7d0; padding: 1.15rem 1.5rem; display: flex; align-items: center; justify-content: space-between;">
            <div style="color: #166534; font-weight: 800; font-size: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Tarik &amp; Finalisasi HPP Kemasan (FG)</span>
            </div>
            <button type="button" onclick="closeFinalizeHppModal()" style="background: none; border: none; font-size: 1.25rem; line-height: 1; color: #64748b; cursor: pointer; padding: 0.2rem;">
                &times;
            </button>
        </div>

        {{-- Body Form --}}
        <form id="formFinalizePackagingHpp" method="POST" action="">
            @csrf
            <div style="padding: 1.35rem 1.5rem;">
                <p style="font-size: 0.85rem; color: #334155; line-height: 1.5; margin: 0 0 1rem 0;">
                    Anda akan memfinalkan perhitungan Harga Pokok Produksi (HPP) untuk dokumen pengemasan ini:
                </p>

                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.35rem; font-size: 0.825rem;">
                        <span style="color: #64748b;">No Dokumen:</span>
                        <strong id="finalizePackNoLabel" style="color: #0284c7; font-family: monospace;">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.35rem; font-size: 0.825rem;">
                        <span style="color: #64748b;">Produk Jadi (FG):</span>
                        <strong id="finalizeProductLabel" style="color: #0f172a;">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.825rem;">
                        <span style="color: #64748b;">Dokumen Produksi WIP Asal:</span>
                        <strong id="finalizeProdNoLabel" style="color: #059669;">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 0.35rem; padding-top: 0.35rem; border-top: 1px dashed #cbd5e1; font-size: 0.825rem;">
                        <span style="color: #64748b;">HPP Produksi WIP:</span>
                        <strong id="finalizeWipHppLabel" style="color: #059669; font-family: monospace;">-</strong>
                    </div>
                </div>

                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 0.75rem 0.95rem; font-size: 0.775rem; color: #1e40af; line-height: 1.45;">
                    💡 <strong>Otomatisasi Sistem:</strong> Sistem akan menarik HPP WIP riil dari SPK produksi di atas, menjumlahkannya dengan konsumsi dus/plastik/lakban, dan menyinkronkan harga modal persediaan FG di kartu stok gudang.
                </div>
            </div>

            {{-- Footer Aksi --}}
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.85rem 1.5rem; display: flex; justify-content: flex-end; gap: 0.65rem;">
                <button type="button" onclick="closeFinalizeHppModal()" class="btn btn-secondary" style="font-size: 0.825rem; font-weight: 700; padding: 0.45rem 1rem; border: 1.5px solid #cbd5e1; background: #ffffff; color: #475569; border-radius: 6px; cursor: pointer;">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" style="font-size: 0.825rem; font-weight: 700; padding: 0.45rem 1.25rem; background: #059669; border-color: #059669; color: #ffffff; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Finalkan HPP Sekarang</span>
                </button>
            </div>
        </form>
    </div>
</div>

