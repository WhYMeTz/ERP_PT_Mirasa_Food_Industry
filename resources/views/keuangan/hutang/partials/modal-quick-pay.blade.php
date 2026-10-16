{{-- Modal Quick Pay (Bayar Langsung dengan Visual Card Rekening Kas/Bank) --}}
<div class="modal-backdrop" id="modalQuickPay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(3px); z-index: 1060; align-items: center; justify-content: center; padding: 1rem; opacity: 0; transition: opacity 0.2s ease;">
    <div class="modal-dialog-quick-pay" style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 620px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; transform: translateY(15px); transition: transform 0.25s ease;">
        
        {{-- Header Modal --}}
        <div style="padding: 1.15rem 1.5rem; background: linear-gradient(135deg, #065f46 0%, #059669 100%); color: #ffffff; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="background: rgba(255, 255, 255, 0.2); width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                    ⚡
                </div>
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; margin: 0; color: #ffffff; letter-spacing: -0.01em;">
                        Bayar Cepat Tagihan Hutang
                    </h3>
                    <p style="font-size: 0.725rem; color: #d1fae5; margin: 0.15rem 0 0 0;">
                        Catat Bukti Kas Keluar (BKK) instan dan potong saldo rekening
                    </p>
                </div>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeModal('modalQuickPay')" style="background: transparent; border: none; color: #ffffff; font-size: 1.5rem; line-height: 1; cursor: pointer; opacity: 0.8; transition: opacity 0.15s;">
                &times;
            </button>
        </div>

        <form action="{{ route('keuangan.bayar.store') }}" method="POST" id="formQuickPay">
            @csrf
            
            {{-- Hidden Field Alokasi Pembayaran --}}
            <input type="hidden" name="allocations[0][hutang_id]" id="qp_hutang_id">
            <input type="hidden" name="kas_bank_id" id="qp_kas_bank_id">

            <div style="padding: 1.25rem 1.5rem; max-height: calc(85vh - 130px); overflow-y: auto;">
                
                {{-- KARTU INFO TAGIHAN YANG DIBAYAR --}}
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                    <div>
                        <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">
                            TAGIHAN REKANAN SUPPLIER
                        </div>
                        <div style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-top: 2px;" id="qp_supplier_nm">
                            -
                        </div>
                        <div style="font-size: 0.75rem; color: #0284c7; font-family: monospace; font-weight: 700;" id="qp_hutang_no">
                            -
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">
                            SISA TAGIHAN SAAT INI
                        </div>
                        <div style="font-size: 1.2rem; font-weight: 900; color: #dc2626; font-family: monospace;" id="qp_sisa_hutang_disp">
                            Rp 0
                        </div>
                    </div>
                </div>

                {{-- PILIH REKENING KAS / BANK DENGAN VISUAL CARD SELECTOR --}}
                <div style="margin-bottom: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label style="font-size: 0.8125rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.35rem;">
                            <span>🏦</span> <span>Pilih Rekening Sumber Pembayaran</span>
                            <span style="color: #dc2626;">*</span>
                        </label>
                        <span style="font-size: 0.7rem; color: #64748b;">Klik pada kartu untuk memilih</span>
                    </div>

                    <div class="qp-bank-grid" id="qpBankGrid">
                        @foreach ($kasBanks as $kb)
                            @php
                                $bankLower = strtolower($kb->bank_nama ?? '');
                                $cardVisual = 'generic';
                                if ($kb->tipe_akun === 'KAS') {
                                    $cardVisual = 'kas';
                                } elseif (str_contains($bankLower, 'bca')) {
                                    $cardVisual = 'bca';
                                } elseif (str_contains($bankLower, 'mandiri')) {
                                    $cardVisual = 'mandiri';
                                } elseif (str_contains($bankLower, 'bni')) {
                                    $cardVisual = 'bni';
                                } elseif (str_contains($bankLower, 'bri')) {
                                    $cardVisual = 'bri';
                                }
                            @endphp
                            <div class="qp-bank-card {{ $cardVisual }}" 
                                 data-id="{{ $kb->kas_bank_id }}" 
                                 data-saldo="{{ (float)$kb->saldo_berjalan }}"
                                 data-tipe="{{ $kb->tipe_akun }}"
                                 onclick="selectQuickPayBank(this)">
                                
                                <div class="qp-card-decor"></div>
                                <div class="qp-card-check">✓</div>

                                <div class="qp-card-head">
                                    <span class="qp-card-brand">
                                        @if ($kb->tipe_akun === 'KAS')
                                            💵 KAS TUNAI
                                        @else
                                            💳 {{ $kb->bank_nama ?: 'BANK' }}
                                        @endif
                                    </span>
                                    <span class="qp-card-code">{{ $kb->kode_akun }}</span>
                                </div>

                                <div class="qp-card-acc-num">
                                    @if ($kb->tipe_akun === 'KAS')
                                        Brankas Operasional Pabrik
                                    @else
                                        {{ $kb->nomor_rekening ?: ($kb->no_rekening ?: 'No. Rekening -') }}
                                    @endif
                                </div>

                                <div class="qp-card-footer">
                                    <span class="qp-card-holder">{{ $kb->nama_akun }}</span>
                                    <span class="qp-card-balance">Rp {{ number_format($kb->saldo_berjalan, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- OPSI NOMINAL PEMBAYARAN (LUNAS vs PARSIAL) --}}
                <div style="margin-bottom: 1.15rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <label style="font-size: 0.8125rem; font-weight: 800; color: #0f172a; margin: 0;">
                            Nominal Pembayaran <span style="color: #dc2626;">*</span>
                        </label>
                        <div style="display: flex; gap: 0.35rem;">
                            <button type="button" class="btn-qp-mode active" id="btnModeFull" onclick="setQuickPayMode('full')">
                                Bayar 100% Lunas
                            </button>
                            <button type="button" class="btn-qp-mode" id="btnModePartial" onclick="setQuickPayMode('partial')">
                                Cicilan / Parsial
                            </button>
                        </div>
                    </div>

                    <div style="position: relative;">
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 800; color: #64748b; font-size: 0.95rem;">
                            Rp
                        </span>
                        <input type="number" 
                               step="1" 
                               min="1" 
                               name="allocations[0][nominal_bayar]" 
                               id="qp_nominal_bayar" 
                               class="form-control" 
                               style="padding-left: 42px; font-size: 1.1rem; font-weight: 800; height: 42px; color: #0f172a;" 
                               required>
                    </div>
                    <div id="qp_terbilang_preview" style="font-size: 0.725rem; color: #64748b; margin-top: 0.25rem; font-style: italic;">
                    </div>
                </div>

                {{-- TANGGAL, METODE & NO. REFERENSI --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.15rem;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                            Tanggal Bayar <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="date" name="tgl_bayar" id="qp_tgl_bayar" class="form-control" style="font-size: 0.8125rem; height: 36px; font-weight: 600;" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                            Metode Bayar <span style="color: #dc2626;">*</span>
                        </label>
                        <select name="metode_bayar" id="qp_metode_bayar" class="form-control" style="font-size: 0.8125rem; height: 36px; font-weight: 600;" required>
                            <option value="TRANSFER">📲 Transfer Bank</option>
                            <option value="CASH">💵 Kas Tunai (Brankas)</option>
                            <option value="GIRO">📜 Bilyet Giro</option>
                            <option value="CEK">📝 Cek Bank</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.15rem;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                            No. Referensi / Bukti Transfer
                        </label>
                        <input type="text" name="no_referensi_bank" class="form-control" placeholder="Misal: REF/BCA/1829" style="font-size: 0.8125rem; height: 36px;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                            Nama Penerima / Kasir
                        </label>
                        <input type="text" name="penerima_nama" id="qp_penerima_nama" class="form-control" placeholder="Kontak rekanan supplier" style="font-size: 0.8125rem; height: 36px;">
                    </div>
                </div>

                {{-- LIVE BALANCE CALCULATION PREVIEW BOX --}}
                <div class="qp-calc-box">
                    <div class="qp-calc-row">
                        <span>Saldo Rekening Sumber:</span>
                        <span id="qp_calc_saldo_sumber" style="font-weight: 700;">Rp 0</span>
                    </div>
                    <div class="qp-calc-row">
                        <span>Nominal Bayar:</span>
                        <span id="qp_calc_nominal_bayar" style="font-weight: 700; color: #059669;">Rp 0</span>
                    </div>
                    <div class="qp-calc-row qp-calc-total">
                        <span>Sisa Saldo Rekening Sesudah Bayar:</span>
                        <span id="qp_calc_sisa_saldo" style="font-weight: 800;">Rp 0</span>
                    </div>
                </div>

                <div id="qp_warning_saldo" style="display: none; margin-top: 0.75rem; padding: 0.65rem 0.85rem; border-radius: 8px; background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; font-size: 0.75rem; font-weight: 700;">
                    ⚠️ Saldo rekening sumber tidak mencukupi untuk nominal pembayaran ini.
                </div>
            </div>

            {{-- Footer Modal --}}
            <div style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalQuickPay')" style="font-weight: 700; font-size: 0.8125rem; padding: 0.55rem 1.15rem;">
                    Batal
                </button>
                <button type="submit" id="btnSubmitQuickPay" class="btn btn-primary" style="background: #059669; border-color: #059669; font-weight: 800; font-size: 0.85rem; padding: 0.55rem 1.35rem; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 2px 4px rgba(5, 150, 105, 0.25);">
                    <span>⚡</span> <span>Konfirmasi &amp; Bayar Sekarang</span>
                </button>
            </div>
        </form>
    </div>
</div>
