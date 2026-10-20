{{-- Modal Penyesuaian Saldo Fisik / Opname Kas / Rekonsiliasi Bank --}}
<div id="modalPenyesuaianSaldo" class="kb-modal-backdrop">
    <div class="kb-modal">
        <div class="kb-modal-header" style="background: var(--kb-amber-light); border-bottom-color: #fde68a;">
            <h3 class="kb-modal-title" style="color: var(--kb-amber);">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Penyesuaian Saldo (Opname / Rekonsiliasi)</span>
            </h3>
            <button type="button" class="kb-modal-close" onclick="closeModal('modalPenyesuaianSaldo')">&times;</button>
        </div>
        <form action="{{ route('keuangan.kas_bank.penyesuaian') }}" method="POST">
            @csrf
            <input type="hidden" name="kas_bank_id" id="penyesuaian_kas_bank_id">
            <input type="hidden" id="penyesuaian_saldo_sistem">

            <div class="kb-modal-body">
                <div style="background-color: var(--kb-gray-50); border: 1px solid var(--kb-gray-200); border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.15rem;">
                    <div style="font-size: 0.72rem; color: var(--kb-gray-500); text-transform: uppercase; font-weight: 700;">Akun yang Disesuaikan</div>
                    <div id="penyesuaian_nama_akun" style="font-size: 0.95rem; font-weight: 800; color: var(--kb-dark); margin: 0.2rem 0;">-</div>
                    <div style="font-size: 0.78rem; color: var(--kb-gray-700);">Saldo Catatan Sistem Saat Ini: <strong id="penyesuaian_saldo_sistem_disp" style="color: var(--kb-primary);">Rp 0</strong></div>
                </div>

                <div style="display: grid; grid-template-columns: 1.25fr 1fr; gap: 0.75rem;">
                    <div class="kb-form-group">
                        <label class="kb-form-label">Saldo Riil Fisik / Bank Baru (Rp) <span class="req">*</span></label>
                        <input type="number" step="any" min="0" name="saldo_fisik_baru" id="penyesuaian_saldo_fisik" class="kb-form-input" placeholder="0" required>
                    </div>
                    <div class="kb-form-group">
                        <label class="kb-form-label">Tanggal Penyesuaian <span class="req">*</span></label>
                        <input type="date" name="tgl_mutasi" class="kb-form-input" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                {{-- Live Calculation Box --}}
                <div class="kb-form-calc-box">
                    <div class="kb-calc-row">
                        <span style="color: var(--kb-gray-500);">Selisih Mutasi Buku:</span>
                        <span id="calc_selisih_saldo" style="font-size: 0.95rem;">Rp 0</span>
                    </div>
                    <div class="kb-calc-row">
                        <span style="color: var(--kb-gray-500);">Kategori Penyesuaian:</span>
                        <span id="calc_status_selisih">-</span>
                    </div>
                </div>

                <div class="kb-form-group" style="margin-top: 0.85rem;">
                    <label class="kb-form-label">Alasan / Keterangan Penyesuaian <span class="req">*</span></label>
                    <input type="text" name="keterangan" class="kb-form-input" placeholder="Contoh: Bunga bank bulanan, biaya adm buku kas, atau opname brankas" required maxlength="255">
                </div>
            </div>
            <div class="kb-modal-footer">
                <button type="button" class="kb-btn kb-btn-secondary" onclick="closeModal('modalPenyesuaianSaldo')">Batal</button>
                <button type="submit" id="btnSubmitPenyesuaian" class="kb-btn kb-btn-primary" disabled>
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Penyesuaian Saldo</span>
                </button>
            </div>
        </form>
    </div>
</div>
