{{-- MODAL PERINGATAN KUANTITAS MELEBIHI SISA STOK BATCH (STOCK LIMIT EXCEEDED) --}}
<div id="modalWarningStockLimit" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,0.65); align-items:center; justify-content:center; padding:1rem; backdrop-filter: blur(2px);">
    <div style="background:#fff; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.25); width:100%; max-width:720px; overflow:hidden; border: 1.5px solid #fca5a5; animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
        {{-- Header Modal Red Warning --}}
        <div style="background:#fef2f2; border-bottom:1px solid #fca5a5; padding:1rem 1.25rem; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:0.65rem;">
                <div style="width:36px; height:36px; border-radius:50%; background:#dc2626; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; flex-shrink:0; box-shadow:0 2px 4px rgba(220,38,38,0.3);">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 style="margin:0; font-size:1.05rem; font-weight:700; color:#991b1b;" id="stockLimitModalTitle">Validasi Stok: Kuantitas Melebihi Saldo Bebas / Bentrok DRAFT!</h3>
                    <div style="font-size:0.75rem; color:#b91c1c; margin-top:2px;" id="stockLimitModalSub">Kuantitas melebihi sisa bebas batch atau batch telah terikat di dokumen DRAFT lain. Silakan pilih cara pemecahan batch di bawah.</div>
                </div>
            </div>
            <button type="button" onclick="closeWarningStockLimitModal()" style="background:none; border:none; font-size:1.4rem; color:#94a3b8; cursor:pointer; line-height:1; padding:0 4px;" title="Tutup">&times;</button>
        </div>

        {{-- Body Modal --}}
        <div style="padding:1.25rem;">
            <p style="font-size:0.85rem; color:#334155; margin-top:0; margin-bottom:0.75rem; line-height:1.5;">
                Sistem mendeteksi <strong>kuantitas pengeluaran melebihi sisa bebas atau batch telah terikat di dokumen DRAFT lain</strong>. Anda dapat memecah baris secara <strong>Khusus Grade Sama</strong> atau <strong>Campur (Semua Grade)</strong>:
            </p>

            {{-- Container Tabel Batch yang Melanggar --}}
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden; margin-bottom:1rem; max-height:260px; overflow-y:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:0.8rem; text-align:left;">
                    <thead style="background:#f1f5f9; color:#475569; font-weight:700; border-bottom:1px solid #cbd5e1;">
                        <tr>
                            <th style="padding:0.55rem 0.65rem;">Nama Bahan &amp; Baris</th>
                            <th style="padding:0.55rem 0.65rem; color:#b45309;">Batch Dipilih</th>
                            <th style="padding:0.55rem 0.65rem; text-align:right;">Diminta</th>
                            <th style="padding:0.55rem 0.65rem; text-align:right; color:#065f46;">Sisa Bebas</th>
                            <th style="padding:0.55rem 0.65rem; text-align:center;">Pilihan Pemecahan</th>
                        </tr>
                    </thead>
                    <tbody id="stockLimitWarningListBody">
                        {{-- Diisi secara dinamis oleh JS --}}
                    </tbody>
                </table>
            </div>

            {{-- Solusi Otomatis --}}
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; padding:0.75rem 0.85rem; margin-bottom:1.25rem; font-size:0.775rem; color:#166534; line-height:1.45;">
                <div style="display:flex; gap:0.5rem; align-items:flex-start;">
                    <span style="font-size:1.1rem; line-height:1;">💡</span>
                    <div>
                        <strong>Pilihan Pemecahan Cerdas:</strong>
                        <div style="margin-top: 0.2rem;">
                            &bull; <strong>Pecah Sesuai Grade Asal:</strong> Mengambil sisa kuantitas hanya dari batch sesama grade (Grade A tetap Grade A, Grade B tetap Grade B).<br>
                            &bull; <strong>Pecah Campur (Semua Grade):</strong> Mengambil sisa kuantitas dari batch tertua FIFO apa saja yang ada di gudang (boleh campur Grade A &amp; Grade B).
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div style="display:flex; justify-content:space-between; gap:0.6rem; align-items:center; flex-wrap:wrap;">
                <div style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
                    <button type="button" onclick="applyAutoSplitAllViolations('SAME_GRADE')" class="btn btn-primary" style="background:#059669; border-color:#059669; color:#ffffff; font-size:0.825rem; font-weight:700; padding:0.55rem 0.95rem; border-radius:6px; display:inline-flex; align-items:center; gap:0.35rem; box-shadow:0 2px 4px rgba(5,150,105,0.25);">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>⚡ Pecah Semua (Sesuai Grade Asal)</span>
                    </button>
                    <button type="button" onclick="applyAutoSplitAllViolations('CAMPUR')" class="btn btn-warning" style="background:#d97706; border-color:#d97706; color:#ffffff; font-size:0.825rem; font-weight:700; padding:0.55rem 0.95rem; border-radius:6px; display:inline-flex; align-items:center; gap:0.35rem; box-shadow:0 2px 4px rgba(217,119,6,0.25);">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>🔄 Pecah Semua (Campur / FIFO)</span>
                    </button>
                </div>

                <button type="button" onclick="closeWarningStockLimitModal()" class="btn btn-secondary" style="font-size:0.825rem; padding:0.55rem 0.85rem; border-radius:6px; font-weight:600; background:#f1f5f9; color:#475569; border:1px solid #cbd5e1;">
                    Tutup &amp; Koreksi Manual
                </button>
            </div>
        </div>
    </div>
</div>
