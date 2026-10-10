{{-- ========================================================================= --}}
{{-- PARTIAL: FORM INSPEKSI QC KHUSUS MINYAK GORENG (TAHAP 2)                  --}}
{{-- SESUAI GAMBAR 3: UJI FISIK & KIMIA + KOMENTAR & KESIMPULAN AKHIR          --}}
{{-- NO DOKUMEN: MFI/HACCP-04/FRM-03/029/VIII/2021                             --}}
{{-- ========================================================================= --}}

{{-- CARD 3: UJI FISIK & KIMIA RAW MATERIAL (SESUAI GAMBAR 3) --}}
<div class="card" style="border-radius: 12px; border: 1.5px solid #fed7aa; box-shadow: 0 2px 6px rgba(0,0,0,0.03); overflow: hidden; background: #ffffff;">
    <div class="card-header" style="background: #fffbeb; padding: 1rem 1.25rem; border-bottom: 1px solid #fef3c7; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 0.65rem;">
            <span class="card-step-badge amber">3</span>
            <div>
                <h2 style="font-size: 1.05rem; font-weight: 800; color: #92400e; margin: 0; line-height: 1.25;">Uji Fisik &amp; Kimia Raw Material</h2>
                <span style="font-size: 0.725rem; color: #b45309; font-weight: 700;">Spesifikasi Penerimaan Laboratorium</span>
            </div>
        </div>
        <span style="font-size: 1.25rem;">🧪</span>
    </div>

    <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
        {{-- KONDISI TANGKI / JERIGEN PENGANGKUT --}}
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.45rem; flex-wrap: wrap; gap: 0.35rem;">
                <label class="form-label" style="font-size: 0.875rem; font-weight: 800; color: #0f172a; margin: 0;">
                    Kondisi Tangki / Jerigen Pengangkut
                </label>
                <div style="display: flex; gap: 0.65rem; font-size: 0.75rem; font-weight: 700;">
                    <label style="cursor: pointer; display: flex; align-items: center; gap: 0.25rem; color: #334155;">
                        <input type="radio" name="minyak_tipe_wadah" value="TANGKI" {{ old('minyak_tipe_wadah', 'TANGKI') === 'TANGKI' ? 'checked' : '' }} onchange="if(typeof updateWadahChoice === 'function') updateWadahChoice(this)"> 🚚 Tangki
                    </label>
                    <label style="cursor: pointer; display: flex; align-items: center; gap: 0.25rem; color: #334155;">
                        <input type="radio" name="minyak_tipe_wadah" value="JERIGEN" {{ old('minyak_tipe_wadah') === 'JERIGEN' ? 'checked' : '' }} onchange="if(typeof updateWadahChoice === 'function') updateWadahChoice(this)"> 🛢️ Jerigen
                    </label>
                </div>
            </div>

            <div class="big-choice-grid">
                {{-- OK (Standar) --}}
                <label class="big-choice-btn {{ old('minyak_kondisi_wadah', 'OK') === 'OK' ? 'active-ok' : '' }}" id="cardKondisiOk">
                    <input type="radio" name="minyak_kondisi_wadah" value="OK" {{ old('minyak_kondisi_wadah', 'OK') === 'OK' ? 'checked' : '' }} onchange="updateKondisiWadahCard(this)" style="display: none;">
                    <span style="font-weight: 900;">✔</span>
                    <span>OK (Standar)</span>
                </label>
                {{-- TIDAK STANDAR --}}
                <label class="big-choice-btn {{ old('minyak_kondisi_wadah') === 'TIDAK_STANDARD' ? 'active-danger' : '' }}" id="cardKondisiTdkStd">
                    <input type="radio" name="minyak_kondisi_wadah" value="TIDAK_STANDARD" {{ old('minyak_kondisi_wadah') === 'TIDAK_STANDARD' ? 'checked' : '' }} onchange="updateKondisiWadahCard(this)" style="display: none;">
                    <span style="font-weight: 900;">✖</span>
                    <span>TIDAK STANDAR</span>
                </label>
            </div>
            <input type="hidden" name="minyak_status_raw_material" id="minyakStatusRawMaterialHidden" value="{{ old('minyak_status_raw_material', 'OK') }}">
        </div>

        {{-- 2 CHECKBOX CARDS (MINYAK JERNIH & TANGKI BERSIH) --}}
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            {{-- Minyak Jernih --}}
            <label class="minyak-check-card" id="cardCheckJernih">
                <div class="minyak-check-left">
                    <input type="checkbox" name="minyak_jernih_st" value="1" {{ old('minyak_jernih_st', '1') == '1' ? 'checked' : '' }} onchange="updateCheckCard(this)">
                    <span>Minyak Jernih</span>
                </div>
                <span class="minyak-check-badge">Bebas Endapan</span>
            </label>

            {{-- Tangki Bagian Dalam Bersih --}}
            <label class="minyak-check-card" id="cardCheckTangki">
                <div class="minyak-check-left">
                    <input type="checkbox" name="minyak_tangki_bersih_st" value="1" {{ old('minyak_tangki_bersih_st', '1') == '1' ? 'checked' : '' }} onchange="updateCheckCard(this)">
                    <span>Tangki Bagian Dalam Bersih</span>
                </div>
                <span class="minyak-check-badge">Saniter</span>
            </label>
        </div>

        {{-- PARAMETER FFA (FREE FATTY ACID) (SESUAI GAMBAR 3) --}}
        <div class="ffa-parameter-box">
            <div class="ffa-param-header">
                <div class="ffa-param-title">PARAMETER FFA (FREE FATTY ACID)</div>
                <div class="ffa-param-badge">Std Max: ≤ 0.20%</div>
            </div>

            <div class="qc-grid-2col">
                <div class="ffa-param-card">
                    <div class="qc-field-header" style="min-height: 1.8rem;">
                        <label class="qc-field-label" style="font-size: 0.75rem; color: #475569;">FFA di COA (Vendor)</label>
                    </div>
                    <div class="input-suffix-wrap">
                        <input type="number" step="0.001" min="0" max="100" name="minyak_ffa_coa" id="inputMinyakFfaCoa" class="form-control" placeholder="0.05" value="{{ old('minyak_ffa_coa') }}" style="font-weight: 800;">
                        <span class="input-suffix-text">%</span>
                    </div>
                </div>

                <div class="ffa-param-card">
                    <div class="qc-field-header" style="min-height: 1.8rem;">
                        <label class="qc-field-label" style="font-size: 0.75rem; color: #0284c7;">FFA Cek QC Mirasa <span style="color:#ef4444;">*</span></label>
                    </div>
                    <div class="input-suffix-wrap">
                        <input type="number" step="0.001" min="0" max="100" name="minyak_ffa_qc" id="inputMinyakFfaQc" class="form-control" placeholder="0.06" value="{{ old('minyak_ffa_qc') }}" style="font-weight: 800; border-color: #0284c7;" oninput="checkMinyakAcceptance()">
                        <span class="input-suffix-text">%</span>
                    </div>
                </div>
            </div>

            <div class="ffa-status-row">
                <span style="font-size: 0.8rem; font-weight: 700; color: #475569;">Status Pengujian Kimia:</span>
                <div class="ffa-status-badge" id="badgeFfaStatus">
                    <span id="badgeFfaIcon">✔</span>
                    <span id="badgeFfaText">Sesuai Standar (≤0.20%)</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- CARD 4: KOMENTAR & KESIMPULAN AKHIR (SESUAI GAMBAR 3) --}}
<div class="card" style="border-radius: 12px; border: 1.5px solid #e2e8f0; box-shadow: 0 2px 6px rgba(0,0,0,0.03); overflow: hidden; background: #ffffff;">
    <div class="card-header" style="background: #ffffff; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 0.65rem;">
            <span class="card-step-badge">4</span>
            <div>
                <h2 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.25;">Komentar &amp; Kesimpulan Akhir</h2>
            </div>
        </div>
        <span style="font-size: 1.25rem;">📋</span>
    </div>

    <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
        {{-- SINKRONISASI HASIL TIMBANGAN & NETTO (SEJAJAR SEMPURNA) --}}
        <div class="qc-grid-2col">
            <div class="form-group">
                <div class="qc-field-header">
                    <label class="qc-field-label">Timbangan Gross (<span class="label-satuan-text">KG</span>) <span style="color:#ef4444;">*</span></label>
                </div>
                <div class="input-suffix-wrap">
                    <input type="number" step="0.01" min="0" name="minyak_qty_gross" id="minyakQtyGross" class="form-control" placeholder="15985" value="{{ old('minyak_qty_gross') }}" oninput="syncMinyakQuantity('gross')" style="font-weight: 800; font-size: 0.95rem;">
                    <span class="input-suffix-text suffix-satuan-text">KG</span>
                </div>
            </div>
            <div class="form-group">
                <div class="qc-field-header">
                    <label class="qc-field-label" style="color: #dc2626;">Qty Reject / Tolak (<span class="label-satuan-text">KG</span>)</label>
                </div>
                <div class="input-suffix-wrap">
                    <input type="number" step="0.01" min="0" name="minyak_qty_reject" id="minyakQtyReject" class="form-control" placeholder="0" value="{{ old('minyak_qty_reject', 0) }}" oninput="syncMinyakQuantity('reject')" style="font-weight: 800; font-size: 0.95rem;">
                    <span class="input-suffix-text suffix-satuan-text">KG</span>
                </div>
            </div>
        </div>

        <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 10px; padding: 0.75rem 1rem; display: flex; flex-direction: column; gap: 0.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.825rem; font-weight: 800; color: #065f46;">Netto Lolos Diterima Pabrik:</span>
                <strong id="labelMinyakNetto" style="font-size: 1.25rem; font-weight: 900; color: #059669;">0,00 KG</strong>
            </div>
            <span id="labelMinyakNettoFormula" style="font-size: 0.725rem; color: #047857; font-weight: 700;">
                Sesuai timbangan datang (tanpa reject)
            </span>
        </div>

        {{-- KOMENTAR / CATATAN KHUSUS QC (GAMBAR 3) --}}
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-weight: 800; font-size: 0.85rem; color: #0f172a;">Komentar / Catatan Khusus QC</label>
            <textarea name="minyak_komentar" rows="3" class="form-control" placeholder="Kondisi segel tangki utuh dan sesuai dokumen DO. Suhu penerimaan kamar normal, minyak jernih kuning keemasan, FFA aman.">{{ old('minyak_komentar') }}</textarea>
        </div>

        {{-- KESIMPULAN AKHIR QC --}}
        <div>
            <label class="form-label" style="font-weight: 800; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.45rem; display: block;">
                Keputusan Akhir Inspeksi QC:
            </label>
            <div class="big-choice-grid">
                <label class="big-choice-btn {{ old('minyak_kesimpulan', 'TERIMA') === 'TERIMA' ? 'active-ok' : '' }}" id="cardTerima">
                    <input type="radio" name="minyak_kesimpulan" id="minyakRadioTerima" value="TERIMA" {{ old('minyak_kesimpulan', 'TERIMA') === 'TERIMA' ? 'checked' : '' }} onchange="updateKesimpulanCard(this)" style="display: none;">
                    <span style="font-weight: 900;">✔</span>
                    <span>TERIMA (LOLOS)</span>
                </label>
                <label class="big-choice-btn {{ old('minyak_kesimpulan') === 'TOLAK' ? 'active-danger' : '' }}" id="cardTolak">
                    <input type="radio" name="minyak_kesimpulan" id="minyakRadioTolak" value="TOLAK" {{ old('minyak_kesimpulan') === 'TOLAK' ? 'checked' : '' }} onchange="updateKesimpulanCard(this)" style="display: none;">
                    <span style="font-weight: 900;">✖</span>
                    <span>TOLAK (REJECT)</span>
                </label>
            </div>
        </div>

        {{-- OTORISASI & PEMERIKSA (BISA DITAMBAHKAN/DIEDIT LANGSUNG SESUAI PERMINTAAN USER) --}}
        <div style="border-top: 1px dashed #cbd5e1; padding-top: 0.85rem; margin-top: 0.35rem; display: flex; flex-direction: column; gap: 0.75rem;">
            {{-- PETUGAS QC RAW MATERIAL --}}
            <div class="qc-auth-card">
                <div class="qc-auth-header">
                    <div>
                        <div class="qc-auth-title">Petugas QC Raw Material</div>
                        <div class="qc-auth-sub">Pemeriksa kedatangan langsung</div>
                    </div>
                    <span class="qc-auth-badge verified">Terverifikasi ID</span>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <div class="qc-field-header">
                        <label class="qc-field-label">Nama Petugas QC (Bisa Diedit/Ketik Langsung)</label>
                    </div>
                    <input type="text" name="petugas_qc_nama" id="inputPetugasQc" class="form-control" placeholder="Nama Petugas QC" value="{{ old('petugas_qc_nama', auth()->user()?->name ?? 'Petugas QC') }}" style="font-weight: 800; color: #0f172a;">
                </div>
            </div>

            {{-- QC SUPERVISOR / LAB HEAD --}}
            <div class="qc-auth-card">
                <div class="qc-auth-header">
                    <div>
                        <div class="qc-auth-title">QC Supervisor / Lab Head</div>
                        <div class="qc-auth-sub">Peninjau &amp; Persetujuan Rilis Bahan</div>
                    </div>
                    <span class="qc-auth-badge pending">Menunggu Review</span>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <div class="qc-field-header">
                        <label class="qc-field-label">Tugaskan Supervisor Peninjau (Bisa Diedit/Ketik Langsung)</label>
                    </div>
                    <input type="text" name="qc_supervisor_nama" id="inputSupervisorQc" list="listSupervisorQc" class="form-control" placeholder="Pilih atau ketik nama Supervisor..." value="{{ old('qc_supervisor_nama', 'Hendrawan, S.TP (SPV QC Shift Pagi)') }}" style="font-weight: 800; color: #0f172a;">
                    <datalist id="listSupervisorQc">
                        <option value="Hendrawan, S.TP (SPV QC Shift Pagi)">
                        <option value="Rina Wijaya, S.Si (Supervisor QC Lab)">
                        <option value="Dedi (QC Bahan Baku)">
                        <option value="Bambang (Produksi Magelang)">
                        <option value="Super Administrator">
                    </datalist>
                </div>
            </div>
        </div>
    </div>
</div>

