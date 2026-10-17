{{-- MODAL TARIK LAPORAN PENGEMASAN OPERATOR LAPANGAN --}}
<div id="modalPullOperator" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(2px); z-index: 99999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 850px; width: 100%; max-height: 85vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; animation: popIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
        
        {{-- Header Modal --}}
        <div style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                        📥
                    </div>
                    <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">
                        Tarik Data Laporan Operator Lapangan
                    </h3>
                </div>
                <p style="font-size: 0.8rem; color: #64748b; margin: 0.25rem 0 0 40px;">
                    Pilih laporan kemas shift lapangan (pengganti Google Form) untuk otomatis mengisi bahan riil, bumbu, plastik, dan jumlah karton.
                </p>
            </div>

            <button type="button" onclick="closePullOperatorModal()" style="background: #f1f5f9; border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; color: #64748b; display: flex; align-items: center; justify-content: center;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Body Modal --}}
        <div style="padding: 1.25rem 1.5rem; overflow-y: auto; flex: 1;">
            {{-- Loading State --}}
            <div id="pullOperatorLoading" style="text-align: center; padding: 2.5rem 1rem; color: #64748b;">
                <div style="display: inline-block; width: 32px; height: 32px; border: 3px solid #cbd5e1; border-top-color: #059669; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
                <div style="margin-top: 0.75rem; font-size: 0.85rem; font-weight: 600;">Memuat laporan pengemasan lapangan...</div>
            </div>

            {{-- Empty State --}}
            <div id="pullOperatorEmpty" style="display: none; text-align: center; padding: 3rem 1rem; color: #64748b;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📭</div>
                <strong style="color: #1e293b; font-size: 1rem; display: block;">Tidak Ada Laporan Operator yang Menunggu</strong>
                <p style="font-size: 0.825rem; color: #64748b; margin: 0.25rem 0 1rem 0; max-width: 450px; margin-inline: auto;">
                    Semua laporan pengemasan lapangan yang dikirim operator sudah diproses ke HPP FG, atau belum ada laporan baru dari HP operator.
                </p>
                <a href="{{ route('produksi.packaging.mobile') }}" target="_blank" class="btn" style="background: #fff1f2; color: #dc2626; border: 1.5px solid #fecdd3; font-weight: 700; font-size: 0.8125rem; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    <span>📱 Buka Form Input Operator Lapangan</span>
                </a>
            </div>

            {{-- Table List of Unprocessed Reports --}}
            <div id="pullOperatorContent" style="display: none;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.825rem;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; color: #475569; text-transform: uppercase; font-size: 0.725rem; letter-spacing: 0.03em;">
                            <th style="padding: 0.65rem 0.75rem; text-align: left;">No. Laporan &amp; Tgl</th>
                            <th style="padding: 0.65rem 0.75rem; text-align: left;">Shift &amp; Leader</th>
                            <th style="padding: 0.65rem 0.75rem; text-align: left;">Produk Jadi (FG)</th>
                            <th style="padding: 0.65rem 0.75rem; text-align: right;">Output Jadi</th>
                            <th style="padding: 0.65rem 0.75rem; text-align: center; width: 110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="pullOperatorTableBody">
                        {{-- Injected dynamically by JS --}}
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer Modal --}}
        <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.875rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 0.75rem; color: #64748b;">
                *Setelah data ditarik dan disimpan di HPP FG, status tiket operator akan otomatis berubah menjadi <strong>Sudah Jadi HPP FG</strong>.
            </span>
            <button type="button" onclick="closePullOperatorModal()" class="btn btn-secondary" style="font-size: 0.8125rem; font-weight: 600; padding: 0.45rem 1rem; border-radius: 6px;">
                Tutup
            </button>
        </div>
    </div>
</div>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
@keyframes popIn {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}
</style>
