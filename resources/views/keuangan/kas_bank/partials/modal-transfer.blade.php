{{-- Modal Transfer Dana Antar Rekening Kas & Bank --}}
<div id="modalTransferDana" class="kb-modal-backdrop">
    <div class="kb-modal">
        <div class="kb-modal-header" style="background: var(--kb-blue-light); border-bottom-color: #bfdbfe;">
            <h3 class="kb-modal-title" style="color: var(--kb-blue);">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Transfer Dana Antar Rekening / Kas</span>
            </h3>
            <button type="button" class="kb-modal-close" onclick="closeModal('modalTransferDana')">&times;</button>
        </div>
        <form action="{{ route('keuangan.kas_bank.transfer') }}" method="POST">
            @csrf
            <div class="kb-modal-body">
                <div class="kb-form-group">
                    <label class="kb-form-label">Rekening Sumber (Pengirim) <span class="req">*</span></label>
                    <select name="kas_bank_asal_id" id="transfer_kas_asal_id" class="kb-form-select" required>
                        <option value="">-- Pilih Rekening Sumber --</option>
                        @foreach ($kasBanks as $item)
                            <option value="{{ $item->kas_bank_id }}" data-saldo="{{ $item->saldo_berjalan }}">
                                [{{ $item->kode_akun }}] {{ $item->nama_akun }} (Saldo: Rp {{ number_format($item->saldo_berjalan, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="kb-form-group">
                    <label class="kb-form-label">Rekening Tujuan (Penerima) <span class="req">*</span></label>
                    <select name="kas_bank_tujuan_id" id="transfer_kas_tujuan_id" class="kb-form-select" required>
                        <option value="">-- Pilih Rekening Penerima --</option>
                        @foreach ($kasBanks as $item)
                            <option value="{{ $item->kas_bank_id }}">
                                [{{ $item->kode_akun }}] {{ $item->nama_akun }} (Saldo: Rp {{ number_format($item->saldo_berjalan, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1.25fr 1fr; gap: 0.75rem;">
                    <div class="kb-form-group">
                        <label class="kb-form-label">Nominal Transfer (Rp) <span class="req">*</span></label>
                        <input type="number" step="any" min="1" name="nominal" id="transfer_nominal" class="kb-form-input" placeholder="0" required>
                    </div>
                    <div class="kb-form-group">
                        <label class="kb-form-label">Tanggal Transaksi <span class="req">*</span></label>
                        <input type="date" name="tgl_mutasi" class="kb-form-input" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                {{-- Live Calculation Box --}}
                <div class="kb-form-calc-box" id="transfer_calc_box">
                    <div class="kb-calc-row">
                        <span style="color: var(--kb-gray-500);">Saldo Sumber Saat Ini:</span>
                        <span id="calc_asal_saldo" style="font-weight: 600;">Rp 0</span>
                    </div>
                    <div class="kb-calc-row">
                        <span style="color: var(--kb-gray-500);">Estimasi Sisa Saldo Sumber:</span>
                        <span id="calc_sisa_saldo" style="font-weight: 700; color: #059669;">Rp 0</span>
                    </div>
                </div>

                <div id="transfer_warning_box" style="display: none; margin-top: 0.65rem; padding: 0.65rem; border-radius: 6px; background-color: #fef2f2; border: 1px solid #fecaca; color: #dc2626; font-size: 0.75rem; font-weight: 600;">
                </div>

                <div class="kb-form-group" style="margin-top: 0.85rem;">
                    <label class="kb-form-label">Keterangan / Keperluan Transfer</label>
                    <input type="text" name="keterangan" class="kb-form-input" placeholder="Misal: Penarikan tunai operasional kas kecil pabrik" maxlength="255">
                </div>
            </div>
            <div class="kb-modal-footer">
                <button type="button" class="kb-btn kb-btn-secondary" onclick="closeModal('modalTransferDana')">Batal</button>
                <button type="submit" id="btnSubmitTransfer" class="kb-btn kb-btn-primary">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    <span>Kirim Transfer Dana</span>
                </button>
            </div>
        </form>
    </div>
</div>
