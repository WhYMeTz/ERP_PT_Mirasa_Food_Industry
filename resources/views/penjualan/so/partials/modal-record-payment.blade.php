{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL PENCATATAN PEMBAYARAN KAS MASUK (BKM) - BISA PARSIAL / TERMIN / LUNAS --}}
{{-- ERP PT Mirasa Food Industry                                               --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}

<div id="modalRecordPayment" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 99999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 540px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; border: 1px solid #e2e8f0; animation: modalPop 0.2s ease-out;">
        
        {{-- HEADER MODAL --}}
        <div style="background: #f0fdf4; border-bottom: 1.5px solid #bbf7d0; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #dcfce7; color: #15803d; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: #14532d; margin: 0;">
                        Catat Pembayaran / Kas Masuk (BKM)
                    </h3>
                    <span style="font-size: 0.75rem; color: #166534;">
                        Dokumen: <strong>{{ $order->so_no }}</strong> &bull; {{ $order->customer?->customer_nm }}
                    </span>
                </div>
            </div>
            <button type="button" onclick="closeRecordPaymentModal()" style="border: none; background: transparent; font-size: 1.5rem; color: #94a3b8; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        {{-- FORM PEMBAYARAN --}}
        <form id="formRecordPayment" method="POST" action="{{ route('penjualan.so.record-payment', $order->so_id) }}">
            @csrf

            <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                
                {{-- Banner Ringkasan Piutang --}}
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.825rem;">
                    <div>
                        <span style="color: #64748b; font-size: 0.75rem; display: block;">Total Tagihan Faktur:</span>
                        <strong style="color: #0f172a; font-family: monospace; font-size: 0.95rem;">Rp {{ number_format((float) $order->total_tagihan, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span style="color: #64748b; font-size: 0.75rem; display: block;">Sisa Piutang Saat Ini:</span>
                        <strong style="color: #dc2626; font-family: monospace; font-size: 0.95rem;" id="modalSisaPiutangText">Rp {{ number_format((float) $order->sisa_tagihan, 0, ',', '.') }}</strong>
                    </div>
                </div>

                {{-- Rekening Kas / Bank Tujuan --}}
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Rekening Kas / Bank Tujuan Penerimaan <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="kas_bank_id" class="form-control" style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.45rem 0.75rem; font-size: 0.85rem;" required>
                        <option value="">-- Pilih Rekening Kas / Bank --</option>
                        @foreach ($kasBanks as $kb)
                            <option value="{{ $kb->kas_bank_id }}" {{ $kb->tipe_akun === 'BANK' ? 'selected' : '' }}>
                                [{{ $kb->kode_akun }}] {{ $kb->nama_akun }} (Saldo: Rp {{ number_format((float) $kb->saldo_berjalan, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    {{-- Tanggal Terima --}}
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Tanggal Terima Kas <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" name="tgl_terima" value="{{ date('Y-m-d') }}" class="form-control" style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.45rem 0.75rem; font-size: 0.85rem;" required>
                    </div>

                    {{-- Metode Pembayaran --}}
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Metode Pembayaran <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="metode_terima" class="form-control" style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.45rem 0.75rem; font-size: 0.85rem;" required>
                            <option value="TRANSFER" selected>Transfer Bank</option>
                            <option value="CASH">Tunai / Cash Kasir</option>
                            <option value="GIRO_CEK">Giro / Cek Bank</option>
                            <option value="EDC_QRIS">EDC / QRIS</option>
                        </select>
                    </div>
                </div>

                {{-- Nominal Pembayaran (Bisa Cicilan / Parsial) --}}
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.35rem;">
                        <label style="font-size: 0.8rem; font-weight: 700; color: #334155;">
                            Nominal Diterima (Rp) <span style="color: #ef4444;">*</span>
                        </label>
                        <button type="button" 
                                onclick="fillFullPayment({{ (float) $order->sisa_tagihan }})" 
                                style="background: none; border: none; color: #0284c7; font-size: 0.75rem; font-weight: 700; cursor: pointer; text-decoration: underline; padding: 0;">
                            Set Bayar Penuh (Rp {{ number_format((float) $order->sisa_tagihan, 0, ',', '.') }})
                        </button>
                    </div>
                    <input type="number" 
                           step="any" 
                           min="1" 
                           max="{{ (float) $order->sisa_tagihan }}" 
                           name="nominal" 
                           id="modalNominalInput" 
                           class="form-control" 
                           value="{{ (float) $order->sisa_tagihan }}" 
                           placeholder="Masukkan nominal DP / cicilan / pelunasan" 
                           style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.45rem 0.75rem; font-size: 0.95rem; font-weight: 700; font-family: monospace; color: #059669;" 
                           required>
                    <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.25rem;">
                        Bisa diisi nominal cicilan/termin (parsial). Jika nominal &lt; sisa piutang, status otomatis menjadi <strong>Sebagian</strong>.
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    {{-- Nama Penyetor --}}
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Diterima Dari (Penyetor)
                        </label>
                        <input type="text" name="penyetor_nama" value="{{ $order->customer?->customer_nm }}" class="form-control" style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.45rem 0.75rem; font-size: 0.825rem;">
                    </div>

                    {{-- No. Referensi Bank --}}
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            No. Ref Transfer Bank
                        </label>
                        <input type="text" name="no_referensi_bank" placeholder="Contoh: TRF-BCA-98124" class="form-control" style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.45rem 0.75rem; font-size: 0.825rem;">
                    </div>
                </div>

                {{-- Catatan --}}
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Catatan Transaksi
                    </label>
                    <input type="text" name="catatan" value="Pembayaran SO {{ $order->so_no }} - {{ $order->customer?->customer_nm }}" class="form-control" style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.45rem 0.75rem; font-size: 0.825rem;">
                </div>
            </div>

            {{-- FOOTER MODAL --}}
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.85rem 1.25rem; display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeRecordPaymentModal()" style="font-size: 0.825rem; padding: 0.45rem 1rem; font-weight: 600;">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" style="background: #059669; border-color: #059669; font-size: 0.825rem; padding: 0.45rem 1.15rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Bukti Kas Masuk (BKM)</span>
                </button>
            </div>
        </form>
    </div>
</div>
