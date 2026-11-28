{{-- MODAL VERIFIKASI & TERBITKAN JURNAL BUKTI KAS MASUK (BKM) --}}
<div id="modalVerifikasiBkm" class="bkm-modal-backdrop" style="display: none;">
    <div class="bkm-modal-dialog">
        
        {{-- Header Verifikasi --}}
        <div class="bkm-modal-header bkm-modal-header-emerald">
            <div class="bkm-modal-icon-badge bkm-icon-badge-emerald">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="bkm-modal-title">
                    Verifikasi &amp; Terbitkan Jurnal
                </h3>
                <p class="bkm-modal-desc">
                    Tahap 2 Akuntansi: Validasi penerimaan kas/bank dan bukukan Double-Entry General Ledger (GL).
                </p>
            </div>
        </div>

        {{-- Form Body --}}
        <form id="formVerifikasiBkm" method="POST" action="">
            @csrf
            <div class="bkm-modal-body">
                {{-- Info Ringkasan BKM --}}
                <div class="bkm-info-card">
                    <div class="bkm-info-row">
                        <span class="bkm-info-label">No. Bukti Kas Masuk:</span>
                        <strong id="verifBkmNo" class="bkm-info-value-mono">-</strong>
                    </div>
                    <div class="bkm-info-row">
                        <span class="bkm-info-label">Tanggal Penerimaan:</span>
                        <span id="verifBkmTgl" class="bkm-info-value">-</span>
                    </div>
                    <div class="bkm-info-row">
                        <span class="bkm-info-label">Diterima Dari (Penyetor):</span>
                        <span id="verifBkmPenyetor" class="bkm-info-value">-</span>
                    </div>
                    <div class="bkm-info-row">
                        <span class="bkm-info-label">Rekening Tujuan (Debet GL):</span>
                        <span id="verifBkmKasBank" class="bkm-info-value bkm-text-emerald" style="font-weight: 700;">-</span>
                    </div>
                    <div class="bkm-info-row" style="border-top: 1px dashed #e2e8f0; padding-top: 0.5rem; margin-top: 0.5rem;">
                        <span class="bkm-info-label" style="font-weight: 700; color: #0f172a;">Total Kas Diterima:</span>
                        <strong id="verifBkmTotal" class="bkm-info-total">Rp 0</strong>
                    </div>
                </div>

                {{-- Input Akun Kredit COA Lawan --}}
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="verifCoaId" class="bkm-field-label">
                        Akun Kredit Lawan (COA Kredit) <span style="color: #dc2626;">*</span>
                    </label>
                    <select name="coa_id" id="verifCoaId" class="form-control" required style="font-size: 0.85rem; height: 42px;">
                        <option value="">-- Pilih Akun Pendapatan / Modal Lawan --</option>
                        @if (isset($revenueCoas) && $revenueCoas->count() > 0)
                            <optgroup label="Akun Pendapatan &amp; Penjualan (4xxx / 6xxx)">
                                @foreach ($revenueCoas->where('kategori_akun', 'PENDAPATAN') as $coa)
                                    <option value="{{ $coa->coa_id }}">
                                        {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Akun Modal &amp; Ekuitas (3xxx)">
                                @foreach ($revenueCoas->whereIn('kategori_akun', ['MODAL', 'EKUITAS']) as $coa)
                                    <option value="{{ $coa->coa_id }}">
                                        {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                    </option>
                                @endforeach
                            </optgroup>
                            @php
                                $otherCoas = $revenueCoas->whereNotIn('kategori_akun', ['PENDAPATAN', 'MODAL', 'EKUITAS']);
                            @endphp
                            @if ($otherCoas->count() > 0)
                                <optgroup label="Akun Lainnya">
                                    @foreach ($otherCoas as $coa)
                                        <option value="{{ $coa->coa_id }}">
                                            {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @else
                            <option value="" disabled>Data COA Pendapatan/Modal tidak tersedia</option>
                        @endif
                    </select>
                    <span class="bkm-field-help">
                        Pilih pos akun kredit yang sesuai (misal: 4100 Pendapatan Usaha, 6114 Pendapatan Lain-lain, 3100 Modal).
                    </span>
                </div>

                {{-- Input Tanggal Jurnal --}}
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="verifTglJurnal" class="bkm-field-label">
                        Tanggal Jurnal Umum (Opsional)
                    </label>
                    <input type="date" 
                           name="tgl_jurnal" 
                           id="verifTglJurnal" 
                           class="form-control" 
                           style="font-size: 0.85rem; height: 40px;">
                    <span class="bkm-field-help">
                        Kosongkan bila ingin menggunakan tanggal transaksi penerimaan BKM.
                    </span>
                </div>
            </div>

            {{-- Footer Buttons --}}
            <div class="bkm-modal-footer">
                <button type="button" 
                        class="btn-pill-outline" 
                        onclick="closeModalVerifikasiBkm()" 
                        style="padding: 0.5rem 1rem; font-size: 0.825rem;">
                    Batal
                </button>
                <button type="submit" 
                        class="btn-pill-emerald" 
                        style="padding: 0.5rem 1.25rem; font-size: 0.825rem;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Verifikasi &amp; Terbitkan Jurnal</span>
                </button>
            </div>
        </form>
    </div>
</div>
