{{-- MODAL PERINGATAN FIFO: MEMILIH BATCH LEBIH BARU PADAHAL ADA BATCH LEBIH LAMA --}}
<div id="modalWarningFifo" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,0.65); align-items:center; justify-content:center; padding:1rem; backdrop-filter: blur(2px);">
    <div style="background:#fff; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.25); width:100%; max-width:620px; overflow:hidden; border: 1.5px solid #fde68a; animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
        {{-- Header Modal Amber Warning --}}
        <div style="background:#fffbeb; border-bottom:1px solid #fde68a; padding:1rem 1.25rem; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:0.65rem;">
                <div style="width:36px; height:36px; border-radius:50%; background:#d97706; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; flex-shrink:0; box-shadow:0 2px 4px rgba(217,119,6,0.3);">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 style="margin:0; font-size:1.05rem; font-weight:700; color:#92400e;">Peringatan Urutan FIFO (First-In First-Out)</h3>
                    <div style="font-size:0.75rem; color:#b45309; margin-top:2px;">Terdapat Pemilihan Batch Baru Padahal Masih Ada Batch Terlama</div>
                </div>
            </div>
            <button type="button" onclick="closeWarningFifoModal()" style="background:none; border:none; font-size:1.4rem; color:#94a3b8; cursor:pointer; line-height:1; padding:0 4px;" title="Tutup">&times;</button>
        </div>

        {{-- Body Modal --}}
        <div style="padding:1.25rem;">
            <p style="font-size:0.85rem; color:#334155; margin-top:0; margin-bottom:0.75rem; line-height:1.5;">
                Sistem mendeteksi Anda memilih <strong>batch yang lebih baru</strong> pada bahan berikut, padahal masih tersedia <strong>batch paling lama</strong> yang seharusnya diprioritaskan untuk diproduksi:
            </p>

            {{-- Container Tabel Batch yang Dilompati --}}
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden; margin-bottom:1rem; max-height:240px; overflow-y:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:0.8rem; text-align:left;">
                    <thead style="background:#f1f5f9; color:#475569; font-weight:700; border-bottom:1px solid #cbd5e1;">
                        <tr>
                            <th style="padding:0.5rem 0.65rem;">Nama Bahan &amp; Grade</th>
                            <th style="padding:0.5rem 0.65rem; color:#b45309;">Batch Dipilih (Baru)</th>
                            <th style="padding:0.5rem 0.65rem; color:#065f46;">Batch Tertua (Harus Dimasak)</th>
                        </tr>
                    </thead>
                    <tbody id="fifoWarningListBody">
                        {{-- Diisi secara dinamis oleh JS --}}
                    </tbody>
                </table>
            </div>

            {{-- Notice Risiko Mutu & Kebusukan Bahan Baku --}}
            <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:6px; padding:0.75rem 0.85rem; margin-bottom:1.25rem; font-size:0.775rem; color:#92400e; line-height:1.45;">
                <div style="display:flex; gap:0.5rem; align-items:flex-start;">
                    <span style="font-size:1.1rem; line-height:1;">⚠️</span>
                    <div>
                        <strong>Risiko Mutu Operasional:</strong> Bahan baku singkong dan bahan pangan memiliki masa simpan terbatas. Melompati batch tertua dapat menyebabkan <strong>bahan lama mengendap, susut, atau membusuk</strong> di bak/gudang penampungan.
                    </div>
                </div>
            </div>

            {{-- Input Catatan Alasan Override (Jika operator memaksa) --}}
            <div id="overrideReasonContainer" style="margin-bottom:1.25rem;">
                <label for="override_reason_input" style="display:block; font-size:0.8rem; font-weight:600; color:#475569; margin-bottom:0.35rem;">
                    Alasan Melompati Batch Tertua (Wajib diisi jika tetap ingin melanjutkan):
                </label>
                <input type="text" id="override_reason_input" placeholder="Contoh: Batch lama sedang karantina / permintaan formula khusus kepala produksi" class="form-control" style="font-size:0.825rem; height:36px; border:1px solid #cbd5e1; border-radius:6px; width:100%; box-sizing:border-box;">
            </div>

            {{-- Action Buttons --}}
            <div style="display:flex; justify-content:space-between; gap:0.6rem; align-items:center; flex-wrap:wrap;">
                <button type="button" onclick="applyFixAllToOldestBatch()" class="btn btn-primary" style="background:#059669; border-color:#059669; color:#ffffff; font-size:0.85rem; font-weight:700; padding:0.55rem 1rem; border-radius:6px; display:inline-flex; align-items:center; gap:0.35rem; box-shadow:0 2px 4px rgba(5,150,105,0.25);">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>🔄 Perbaiki Otomatis ke Batch Tertua</span>
                </button>

                <div style="display:flex; gap:0.5rem; align-items:center;">
                    <button type="button" onclick="closeWarningFifoModal()" class="btn btn-secondary" style="font-size:0.825rem; padding:0.55rem 0.85rem; border-radius:6px; font-weight:600; background:#f1f5f9; color:#475569; border:1px solid #cbd5e1;">
                        Batal &amp; Cek Ulang
                    </button>
                    <button type="button" onclick="proceedOverrideFifoSubmit()" class="btn btn-warning" style="background:#d97706; border-color:#b45309; color:#ffffff; font-size:0.825rem; font-weight:700; padding:0.55rem 1rem; border-radius:6px;">
                        Tetap Simpan (Override FIFO)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
