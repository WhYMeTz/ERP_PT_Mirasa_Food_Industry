{{-- MODAL KONFIRMASI SMART APPROVAL & POSTING STASIUN VERIFIKASI --}}
<div id="modalConfirmApproveStation" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(15,23,42,0.7); align-items:center; justify-content:center; padding:1rem; backdrop-filter: blur(3px);">
    <div style="background:#ffffff; border-radius:12px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); width:100%; max-width:540px; overflow:hidden; border: 1.5px solid #a7f3d0; animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
        
        {{-- HEADER MODAL --}}
        <div style="background:#f0fdf4; border-bottom:1px solid #bbf7d0; padding:1.15rem 1.35rem; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <div style="width:40px; height:40px; border-radius:50%; background:#059669; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; flex-shrink:0; box-shadow:0 2px 6px rgba(5,150,105,0.3);">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 style="margin:0; font-size:1.05rem; font-weight:800; color:#065f46;">Konfirmasi Akhir &amp; Posting Jurnal</h3>
                    <div style="font-size:0.75rem; color:#047857; margin-top:2px;">
                        Dokumen: <strong style="font-family:monospace;">{{ $pakai->pakai_no }}</strong> &bull; {{ $pakai->tujuan_pemakaian }}
                    </div>
                </div>
            </div>
            <button type="button" onclick="closeModalConfirmApproveStation()" style="background:none; border:none; font-size:1.5rem; color:#94a3b8; cursor:pointer; line-height:1; padding:0 4px;" title="Tutup">&times;</button>
        </div>

        {{-- BODY MODAL --}}
        <div style="padding:1.25rem 1.35rem;">
            <p style="font-size:0.85rem; color:#334155; margin-top:0; margin-bottom:1rem; line-height:1.5;">
                Pemeriksaan fisik bahan selesai. Sistem akan memotong stok fisik batch di gudang dan menerbitkan jurnal akuntansi otomatis:
            </p>

            {{-- 1. HASIL AUDIT CHECKLIST --}}
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:0.85rem 1rem; margin-bottom:1rem;">
                <div style="font-size:0.725rem; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:0.5rem;">
                    Status Hasil Audit Fisik:
                </div>
                <div style="display:flex; flex-direction:column; gap:0.4rem; font-size:0.8rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="color:#64748b;">Kecukupan Stok Fisik:</span>
                        <strong style="color:#059669; display:inline-flex; align-items:center; gap:0.25rem;">
                            ✓ Semua Batch Terpenuhi
                        </strong>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="color:#64748b;">Akumulasi Kuantitas:</span>
                        <strong id="modalAuditTotalQty" style="color:#0f172a; font-family:monospace;">0,00 Kg</strong>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; padding-top:0.35rem; border-top:1px dashed #e2e8f0;">
                        <span style="color:#64748b;">Total Nilai HPP Bahan:</span>
                        <strong id="modalAuditGrandTotal" style="color:#059669; font-size:1.05rem; font-family:monospace;">Rp 0</strong>
                    </div>
                </div>
            </div>

            {{-- 2. SIMULASI JURNAL AKUNTANSI OTOMATIS TERBIT --}}
            <div style="background:#ffffff; border:1.5px solid #cbd5e1; border-radius:8px; padding:0.85rem 1rem; margin-bottom:1.15rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                    <div style="font-size:0.725rem; font-weight:700; color:#0f172a; text-transform:uppercase; letter-spacing:0.04em;">
                        Entri Jurnal Umum yang Akan Terbit:
                    </div>
                    <span style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; padding:1px 6px; border-radius:4px; font-size:0.7rem; font-weight:700;">
                        ✓ BALANCED
                    </span>
                </div>
                
                <div style="display:flex; flex-direction:column; gap:0.35rem; font-size:0.8rem; background:#f8fafc; padding:0.65rem; border-radius:6px; border:1px solid #e2e8f0;">
                    <div style="display:flex; justify-content:space-between; color:#065f46; font-weight:700;">
                        <span>(Debet) 1151 - Beban Pemakaian / WIP</span>
                        <span id="modalJournalDebet" style="font-family:monospace;">Rp 0</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; color:#b91c1c; font-weight:700; padding-left:0.75rem;">
                        <span>(Kredit) 1150 - Persediaan Bahan Baku/Penolong</span>
                        <span id="modalJournalKredit" style="font-family:monospace;">Rp 0</span>
                    </div>
                </div>
            </div>

            <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:6px; padding:0.65rem 0.85rem; margin-bottom:1.25rem; font-size:0.775rem; color:#1e40af; line-height:1.4;">
                🔒 <strong>Peringatan Keuangan:</strong> Begitu diposting, status dokumen akan resmi terkunci (POSTED) dan kartu stok gudang akan langsung terpotong.
            </div>

            {{-- FOOTER TOMBOL --}}
            <div style="display:flex; justify-content:flex-end; gap:0.65rem;">
                <button type="button" onclick="closeModalConfirmApproveStation()" class="btn btn-secondary" style="padding:0.6rem 1.15rem; font-size:0.85rem; font-weight:600;">
                    Batal / Cek Lagi
                </button>
                <button type="button" onclick="executeApproveStationForm()" class="btn btn-primary" style="background:#059669; border-color:#059669; color:#fff; font-size:0.85rem; font-weight:700; padding:0.6rem 1.35rem; display:inline-flex; align-items:center; gap:0.4rem; box-shadow:0 2px 6px rgba(5,150,105,0.3);">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Ya, Simpan &amp; Posting Sekarang</span>
                </button>
            </div>
        </div>

    </div>
</div>
