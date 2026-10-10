{{-- ========================================================================= --}}
{{-- TAB 3: UJI PENGGORENGAN (HASIL FRYER) & KESIMPULAN DISPOSISI QC --}}
{{-- ========================================================================= --}}
<div id="qcSection3" style="display: none; flex-direction: column; gap: 1.15rem;">

    {{-- ===================================================================== --}}
    {{-- CARD 4: UJI PENGGORENGAN (HASIL FRYER) --}}
    {{-- ===================================================================== --}}
    <div class="card" style="border-radius: 12px; border: 1.5px solid #fed7aa; box-shadow: 0 2px 6px rgba(0,0,0,0.03); overflow: hidden; background: #ffffff;">
        <div class="card-header" style="background: #fffbeb; padding: 1rem 1.25rem; border-bottom: 1px solid #fef3c7; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <span class="card-step-badge amber">4</span>
                <div>
                    <h2 style="font-size: 1.05rem; font-weight: 800; color: #92400e; margin: 0; line-height: 1.25;">UJI PENGGORENGAN (HASIL FRYER)</h2>
                    <span style="font-size: 0.725rem; color: #b45309; font-weight: 700;">Tes sensori rasa, tekstur, &amp; penampakan</span>
                </div>
            </div>
            <span style="font-size: 1.35rem;">🍳</span>
        </div>

        <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
            <div id="fryerParamsContainer" style="display: flex; flex-direction: column; gap: 1rem;">
                {{-- Dynamically rendered from JS for Singkong Item --}}
            </div>

            {{-- Banner Peringatan Rasa Pahit --}}
            <div id="bannerPahitWarning" style="display: none; background: #fef2f2; border: 1.5px solid #fca5a5; border-radius: 10px; padding: 0.85rem 1rem; color: #991b1b; font-size: 0.825rem;">
                <div style="font-weight: 800; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.25rem;">
                    <span>⚠️</span> <span>PERINGATAN: RASA PAHIT TERDETEKSI!</span>
                </div>
                <div id="textPahitWarning">Singkong beracun sianida / tidak layak konsumsi pabrik. Keputusan otomatis dialihkan ke <strong>TOLAK TOTAL</strong>. Truk tidak diizinkan bongkar ke gudang dan Admin Gudang akan menerbitkan Berita Acara Penolakan.</div>
            </div>

            {{-- Kotak Diskusi Atasan (Khusus Pengujian 2 atau Kondisi Kritis) --}}
            <div id="boxDiskusiAtasan" style="display: none; background: #fff7ed; border: 1.5px solid #fdba74; border-radius: 10px; padding: 0.85rem 1rem; color: #9a3412; font-size: 0.825rem;">
                <div style="font-weight: 800; display: flex; align-items: center; justify-content: space-between; gap: 0.4rem; margin-bottom: 0.35rem; flex-wrap: wrap;">
                    <span style="display: flex; align-items: center; gap: 0.35rem;">
                        <span>🗣️</span> <span>HASIL DISKUSI DENGAN ATASAN (SUPERVISOR / KEPALA DIREKTUR)</span>
                    </span>
                    <span style="font-size: 0.7rem; font-weight: 700; background: #ffedd5; color: #c2410c; padding: 2px 7px; border-radius: 4px;">
                        SOP Pengujian 2
                    </span>
                </div>
                <div style="line-height: 1.4; margin-bottom: 0.5rem;">
                    Jika hasil Pengujian 2 gagal atau terdapat cacat mutu pada sisa muatan bak, diskusikan langkah operasional dengan atasan:
                </div>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <button type="button" onclick="setDiskusiAction('TOLAK_SISA')" style="background: #dc2626; color: #ffffff; border: none; font-size: 0.75rem; font-weight: 800; padding: 0.4rem 0.75rem; border-radius: 6px; cursor: pointer;">
                        🚫 Tolak Sisa Muatan (Truk Dipulangkan)
                    </button>
                    <button type="button" onclick="setDiskusiAction('PENYESUAIAN_REFRAKSI')" style="background: #ea580c; color: #ffffff; border: none; font-size: 0.75rem; font-weight: 800; padding: 0.4rem 0.75rem; border-radius: 6px; cursor: pointer;">
                        ⚖️ Terima Bersyarat (Potongan Refraksi Ekstra)
                    </button>
                    <button type="button" onclick="setDiskusiAction('DOWNGRADE_B')" style="background: #ca8a04; color: #ffffff; border: none; font-size: 0.75rem; font-weight: 800; padding: 0.4rem 0.75rem; border-radius: 6px; cursor: pointer;">
                        🟡 Turunkan Mutu ke Grade B
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================================== --}}
    {{-- CARD 5: KESIMPULAN & DISPOSISI QC (KEPUTUSAN AKHIR) --}}
    {{-- ===================================================================== --}}
    <div class="card" style="border-radius: 12px; border: 1.5px solid #cbd5e1; box-shadow: 0 2px 6px rgba(0,0,0,0.03); overflow: hidden; background: #ffffff;">
        <div class="card-header" style="background: #f8fafc; padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <span class="card-step-badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">5</span>
                <div>
                    <h2 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.25;">KESIMPULAN &amp; DISPOSISI QC</h2>
                    <span style="font-size: 0.725rem; color: #64748b; font-weight: 700;">Hasil Keputusan Pengujian Lapangan</span>
                </div>
            </div>
            <span style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.75rem; font-weight: 800; color: #475569; background: #ffffff; border: 1px solid #cbd5e1; padding: 3px 8px; border-radius: 6px;">
                KEPUTUSAN AKHIR
            </span>
        </div>

        <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
            {{-- RINGKASAN TIMBANGAN TOTAL LANGSUNG DARI TAB 2 --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.65rem;">
                <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 0.75rem 0.85rem;">
                    <span style="font-size: 0.725rem; color: #64748b; font-weight: 700;">Timbangan Kotor Total:</span>
                    <div id="summaryGross" style="font-size: 1.15rem; font-weight: 900; color: #0f172a; margin-top: 0.2rem;">0.00 KG</div>
                </div>
                <div style="background: #ffffff; border: 1.5px solid #fde047; border-radius: 10px; padding: 0.75rem 0.85rem;">
                    <span style="font-size: 0.725rem; color: #ca8a04; font-weight: 700;">Refraksi Tanah:</span>
                    <div id="summaryRefraksi" style="font-size: 1.15rem; font-weight: 900; color: #b45309; margin-top: 0.2rem;">- 0.00 KG</div>
                </div>
                <div style="background: #ffffff; border: 1.5px solid #fca5a5; border-radius: 10px; padding: 0.75rem 0.85rem;">
                    <span style="font-size: 0.725rem; color: #dc2626; font-weight: 700;">Afkir / Cacat Busuk:</span>
                    <div id="summaryReject" style="font-size: 1.15rem; font-weight: 900; color: #dc2626; margin-top: 0.2rem;">- 0.00 KG</div>
                </div>
                <div style="background: #dcfce7; border: 1.5px solid #86efac; border-radius: 10px; padding: 0.75rem 0.85rem;">
                    <span style="font-size: 0.725rem; color: #15803d; font-weight: 800;">TOTAL BERSIH TERIMA:</span>
                    <div id="summaryNetto" style="font-size: 1.25rem; font-weight: 900; color: #15803d; margin-top: 0.2rem;">0.00 KG</div>
                </div>
            </div>

            {{-- 2 PILIHAN DISPOSISI: TERIMA vs TOLAK (SESUAI GAMBAR 2) --}}
            <input type="hidden" name="kesimpulan_qc" id="inputKesimpulanQc" value="TERIMA">
            
            <div class="qc-disposisi-grid">
                <!-- KARTU TERIMA -->
                <div class="qc-disposisi-card active terima" id="cardDisposisiTerima" onclick="selectDisposisi('TERIMA')">
                    <div class="qc-disposisi-head">
                        <div class="qc-disposisi-title-wrap">
                            <span class="qc-disposisi-radio-icon"></span>
                            <span class="qc-disposisi-title">TERIMA</span>
                        </div>
                        <span class="qc-disposisi-status-badge">✓</span>
                    </div>
                    <div class="qc-disposisi-qty-wrap">
                        <span class="qc-disposisi-qty-label">Kuantitas Diterima:</span>
                        <div class="qc-disposisi-input-box">
                            <input type="number" step="0.01" min="0" name="qty_terima" id="inputQtyTerima" class="qc-disposisi-input" value="0" readonly>
                            <span class="qc-disposisi-unit">kg</span>
                        </div>
                    </div>
                </div>

                <!-- KARTU TOLAK -->
                <div class="qc-disposisi-card tolak" id="cardDisposisiTolak" onclick="selectDisposisi('TOLAK')">
                    <div class="qc-disposisi-head">
                        <div class="qc-disposisi-title-wrap">
                            <span class="qc-disposisi-radio-icon"></span>
                            <span class="qc-disposisi-title">TOLAK</span>
                        </div>
                        <span class="qc-disposisi-status-badge">✕</span>
                    </div>
                    <div class="qc-disposisi-qty-wrap">
                        <span class="qc-disposisi-qty-label">Kuantitas Ditolak:</span>
                        <div class="qc-disposisi-input-box">
                            <input type="number" step="0.01" min="0" name="qty_tolak" id="inputQtyTolak" class="qc-disposisi-input" value="0" readonly>
                            <span class="qc-disposisi-unit">kg</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CATATAN / KOMENTAR PENGUJIAN --}}
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 0.825rem; font-weight: 800; color: #0f172a; margin-bottom: 0.35rem; display: block;">
                    Catatan / Komentar Pengujian:
                </label>
                <textarea name="catatan_umum" id="inputCatatanUmum" rows="3" class="form-control" placeholder="Singkong kualitas baik, aroma segar normal, kadar pati cukup tinggi. Lolos uji penggorengan awal tanpa cacat rasa pahit. Diizinkan bongkar ke silo pencucian." style="font-size: 0.85rem; line-height: 1.45;">{{ old('catatan_umum', 'Singkong kualitas baik, aroma segar normal, kadar pati cukup tinggi. Lolos uji penggorengan awal tanpa cacat rasa pahit. Diizinkan bongkar ke silo pencucian.') }}</textarea>
            </div>

            {{-- Hidden Submit Trigger Button --}}
            <button type="submit" id="btnSubmitPengujian1" style="display: none;"></button>
        </div>
    </div>

    {{-- ===================================================================== --}}
    {{-- CARD 6: PENUGASAN PETUGAS QC & SUPERVISOR (BISA DIEDIT/KETIK LANGSUNG) --}}
    {{-- ===================================================================== --}}
    <div style="display: flex; flex-direction: column; gap: 0.85rem;">
        <!-- KARTU 1: PETUGAS QC RAW MATERIAL -->
        <div class="qc-auth-card">
            <div class="qc-auth-header">
                <div>
                    <div class="qc-auth-title">Petugas QC Raw Material</div>
                    <div class="qc-auth-sub">Pemeriksa kedatangan langsung</div>
                </div>
                <span class="qc-auth-badge verified">Terverifikasi ID</span>
            </div>
            <div>
                <label class="qc-auth-label">Nama Petugas QC (Bisa Diedit/Ketik Langsung)</label>
                <input type="text" name="petugas_qc_nama" id="inputPetugasQcNama" class="form-control qc-auth-input" value="{{ old('petugas_qc_nama', auth()->user()?->name ?? 'Dedi (QC Bahan Baku)') }}" placeholder="Ketik nama petugas QC...">
            </div>
        </div>

        <!-- KARTU 2: QC SUPERVISOR / LAB HEAD -->
        <div class="qc-auth-card">
            <div class="qc-auth-header">
                <div>
                    <div class="qc-auth-title">QC Supervisor / Lab Head</div>
                    <div class="qc-auth-sub">Peninjau &amp; Persetujuan Rilis Bahan</div>
                </div>
                <span class="qc-auth-badge review">Menunggu Review</span>
            </div>
            <div>
                <label class="qc-auth-label">Tugaskan Supervisor Peninjau (Bisa Diedit/Ketik Langsung)</label>
                <input type="text" name="qc_supervisor_nama" id="inputQcSupervisorNama" class="form-control qc-auth-input" value="{{ old('qc_supervisor_nama', 'Hendrawan, S.TP (SPV QC Shift Pagi)') }}" placeholder="Ketik nama supervisor...">
            </div>
        </div>
    </div>

</div>
