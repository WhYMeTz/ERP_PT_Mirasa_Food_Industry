{{-- ========================================================================= --}}
{{-- TAHAP 3: UJI CEPAT RASA FRYER & KESIMPULAN DUA PERSETUJUAN (KHUSUS SINGKONG) --}}
{{-- ========================================================================= --}}
<div id="qcSection3" class="card" style="display: none; border-radius: 12px; border-top: 5px solid #f59e0b; box-shadow: 0 2px 5px rgba(0,0,0,0.04);">
    <div class="card-header" style="background: #ffffff; padding: 1.1rem 1.4rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
        <div>
            <h2 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">Tahap 3: Uji Cepat Rasa Fryer &amp; Kesimpulan QC</h2>
            <span style="font-size: 0.8rem; color: #64748b;">Sampel langsung digoreng &amp; dicek rasanya di depan gerbang: Wajib gurih / tidak pahit. Jika pahit langsung tolak!</span>
        </div>
        <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
            <button type="button" class="btn btn-sm btn-secondary" onclick="switchQcTab(2)" style="border-radius: 8px; padding: 0.4rem 0.65rem; font-size: 0.8rem;">
                &larr; Tahap 2
            </button>
            <button type="button" class="btn btn-sm" onclick="document.getElementById('btnSubmitPengujian1').click()" style="border-radius: 8px; font-weight: 800; background: #059669; color: #ffffff; border: none; padding: 0.4rem 0.85rem; font-size: 0.8rem; box-shadow: 0 2px 5px rgba(5,150,105,0.25);">
                💾 Simpan QC
            </button>
        </div>
    </div>

    <div style="padding: 1.4rem; display: flex; flex-direction: column; gap: 1.25rem;">
        <div id="fryerParamsContainer" style="display: flex; flex-direction: column; gap: 1rem;">
            {{-- Dynamically mirrored from Tab 2 items --}}
        </div>

        {{-- Banner Peringatan Rasa Pahit --}}
        <div id="bannerPahitWarning" style="display: none; background: #fef2f2; border: 1.5px solid #fca5a5; border-radius: 10px; padding: 0.85rem 1rem; color: #991b1b; font-size: 0.825rem;">
            <div style="font-weight: 800; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.25rem;">
                <span>⚠️</span> <span>PERINGATAN: RASA PAHIT TERDETEKSI!</span>
            </div>
            <div id="textPahitWarning">Singkong beracun sianida / tidak layak konsumsi pabrik. Keputusan otomatis dialihkan ke <strong>TOLAK TOTAL</strong>. Truk tidak diizinkan bongkar ke gudang dan Admin Gudang akan menerbitkan Berita Acara Penolakan.</div>
        </div>

        {{-- KOTAK DISKUSI DENGAN ATASAN (KHUSUS PENGUJIAN 2 ATAU KEPUTUSAN KRITIS) --}}
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

        {{-- KESIMPULAN AKHIR & DUA PERSETUJUAN --}}
        <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1.25rem;">
            <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a; margin-bottom: 0.75rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <span id="labelKesimpulanTitle">📋 KESIMPULAN AKHIR MUTU BAHAN BAKU SINGKONG</span>
                <div style="display: flex; gap: 0.75rem; align-items: center; background: #ffffff; padding: 0.25rem 0.65rem; border-radius: 8px; border: 1.5px solid #cbd5e1;">
                    <label style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 800; font-size: 0.85rem; color: #15803d; cursor: pointer;">
                        <input type="radio" name="kesimpulan_qc" id="radioTerima" value="TERIMA" checked onchange="onKesimpulanChange('TERIMA')">
                        <span id="labelRadioTerima">✔ TERIMA</span>
                    </label>
                    <label style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 800; font-size: 0.85rem; color: #dc2626; cursor: pointer;">
                        <input type="radio" name="kesimpulan_qc" id="radioTolak" value="TOLAK" onchange="onKesimpulanChange('TOLAK')">
                        <span id="labelRadioTolak">✖ TOLAK TOTAL</span>
                    </label>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; margin-bottom: 1rem;">
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem;">
                    <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Timbangan Kotor (Gross):</span>
                    <div id="summaryGross" style="font-size: 1.15rem; font-weight: 800; color: #0f172a;">0.00 KG</div>
                </div>
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem;">
                    <span style="font-size: 0.75rem; color: #ca8a04; font-weight: 600;">Potongan Refraksi Tanah:</span>
                    <div id="summaryRefraksi" style="font-size: 1.15rem; font-weight: 800; color: #b45309;">- 0.00 KG</div>
                </div>
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem;">
                    <span style="font-size: 0.75rem; color: #dc2626; font-weight: 600;">Afkir / Cacat Busuk (Tolak):</span>
                    <div id="summaryReject" style="font-size: 1.15rem; font-weight: 800; color: #dc2626;">- 0.00 KG</div>
                </div>
                <div style="background: #dcfce7; border: 1.5px solid #86efac; border-radius: 8px; padding: 0.75rem;">
                    <span style="font-size: 0.75rem; color: #15803d; font-weight: 800;">TOTAL BERSIH TERIMA:</span>
                    <div id="summaryNetto" style="font-size: 1.35rem; font-weight: 900; color: #15803d;">0.00 KG</div>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" style="font-weight: 700;">Catatan / Komentar Tambahan QC</label>
                <textarea name="catatan_umum" rows="2" class="form-control" placeholder="Contoh: Singkong panen umur 9 bulan kualitas super, rasa gurih tidak pahit, potongan refraksi tanah 2%.">{{ old('catatan_umum') }}</textarea>
            </div>

            {{-- DUA PERSETUJUAN (QC & KEPALA DIREKTUR) --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; border-top: 1px dashed #cbd5e1; padding-top: 0.85rem;">
                <div>
                    <label class="form-label" style="font-weight: 700; color: #1e293b;">1. Petugas QC Pemeriksa (Depan)</label>
                    <input type="text" name="petugas_qc_nama" class="form-control" value="{{ old('petugas_qc_nama', auth()->user()?->name ?? 'Petugas QC') }}" readonly style="background: #ffffff; font-weight: 700;">
                </div>
                <div>
                    <label class="form-label" style="font-weight: 700; color: #1e293b;">2. QC Supervisor (ACC)</label>
                    <input type="text" name="qc_supervisor_nama" class="form-control" placeholder="Nama QC Supervisor " value="{{ old('qc_supervisor_nama', 'Kepala Direktur / Supervisor QC') }}" style="font-weight: 700;">
                </div>
            </div>
        </div>

        {{-- SUBMIT BUTTON --}}
        <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
            <button type="button" class="btn btn-secondary" onclick="switchQcTab(2)" style="border-radius: 8px;">
                &larr; Kembali ke Pemeriksaan Fisik
            </button>
            <button type="submit" id="btnSubmitPengujian1" class="btn btn-primary" style="flex: 1; justify-content: center; font-size: 1.05rem; padding: 0.85rem; border-radius: 10px; background: #059669; border: none; font-weight: 800; box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.3);">
                💾 Simpan Pengujian I (Selesai Inspeksi &amp; Siap Bongkar)
            </button>
        </div>
    </div>
</div>
