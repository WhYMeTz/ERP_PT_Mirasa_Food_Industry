{{-- 
  Kartu Shift Kerja, Penomoran Batch & Kemasan Karton / Barang Jadi
  Partials Blade ERP PT Mirasa Food Industry (Dual-Mode: IFM vs Reguler)
--}}

<div class="card" style="margin-bottom: 1.25rem; border: 1.5px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.04);">
    <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <strong id="cardHeaderTitle" style="color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
            <span>🏷️ 2. Shift Kerja, Penomoran Batch &amp; Kemasan Karton (Standar Indofood IFM / WIP-FCC)</span>
        </strong>
        <span id="cardHeaderBadge" class="badge" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 0.75rem; font-weight: 700;">
            Format Batch Karton: [Shift][NoAwal] - [Shift][NoAkhir]
        </span>
    </div>

    <div style="padding: 1.25rem;">
        {{-- Hidden Input Shift --}}
        <input type="hidden" name="shift_cd" id="shift_cd" value="{{ old('shift_cd', 'A') }}">

        {{-- BANNER INFORMASI MODE BARANG JADI REGULER (PING-PING, RETAIL) --}}
        <div id="bannerRegularFG" style="display: none; background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem;">
            <div style="display: flex; align-items: flex-start; gap: 0.65rem;">
                <span style="font-size: 1.25rem; line-height: 1;">📋</span>
                <div>
                    <div style="font-weight: 800; font-size: 0.85rem; color: #065f46;">
                        Mode Produksi Barang Jadi Reguler (Retail / Siap Konsumsi)
                    </div>
                    <div style="font-size: 0.75rem; color: #047857; margin-top: 0.2rem; line-height: 1.4;">
                        Sesuai standar buku persediaan PT Mirasa, nomor batch otomatis menggunakan <strong>Format Tanggal Produksi (DD MM YYYY)</strong> dan masa kedaluwarsa otomatis <strong>1 Tahun minus 1 Hari</strong>.
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION TOMBOL PILIHAN SHIFT (KHUSUS PRODUKSI IFM) --}}
        <div id="sectionShiftSelection">
            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.5rem;">
                Pilih Shift Kerja Produksi <span style="color: #ef4444;">*</span>
            </label>
            <div class="shift-selection-grid">
                {{-- Tombol Shift A --}}
                <div id="btnShiftA" class="shift-card-btn {{ old('shift_cd', 'A') === 'A' ? 'active-a' : '' }}" onclick="selectShift('A')">
                    <div>
                        <div class="shift-title">
                            <span>☀️ Shift A (Pagi / Siang)</span>
                        </div>
                        <div class="shift-desc">
                            Jam kerja 07:00 - 15:00 WIB &bull; Default nomor karton dimulai dari 0001
                        </div>
                    </div>
                    <span class="shift-badge">Shift A</span>
                </div>

                {{-- Tombol Shift B --}}
                <div id="btnShiftB" class="shift-card-btn {{ old('shift_cd', 'A') === 'B' ? 'active-b' : '' }}" onclick="selectShift('B')">
                    <div>
                        <div class="shift-title">
                            <span>🌙 Shift B (Malam)</span>
                        </div>
                        <div class="shift-desc">
                            Jam kerja 15:00 - 23:00 WIB &bull; Otomatis melanjutkan nomor karton akhir hari ini
                        </div>
                    </div>
                    <span class="shift-badge">Shift B</span>
                </div>
            </div>
        </div>

        {{-- FORM INPUT SPESIFIKASI KARTON & WIDGET PREVIEW --}}
        <div style="display: grid; grid-template-columns: 1.3fr 1fr; gap: 1.5rem; align-items: start;">
            {{-- KOLOM KIRI: INPUT PARAMETER KARTON --}}
            <div>
                <div class="karton-spec-grid" style="margin-bottom: 1rem;">
                    {{-- Input Qty Karton Selesai --}}
                    <div>
                        <label id="labelKartonTitle" style="display: block; font-size: 0.775rem; font-weight: 700; color: #0f172a; margin-bottom: 0.35rem;">
                            Karton Selesai Dikemas <span style="color: #ef4444;">*</span>
                        </label>
                        <div style="position: relative;">
                            <input type="number" step="1" min="0" name="qty_karton" id="qty_karton" 
                                value="{{ old('qty_karton', 0) }}" 
                                class="form-control" 
                                style="font-weight: 800; font-size: 1.15rem; color: #0f172a; padding-right: 4.5rem;" 
                                placeholder="0" 
                                oninput="updateKartonRangeAndBatch()">
                            <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); font-size: 0.8rem; font-weight: 700; color: #64748b; pointer-events: none;">
                                Kemasan
                            </span>
                        </div>
                        <small style="color: #64748b; font-size: 0.72rem; margin-top: 0.25rem; display: block;">
                            Estimasi berat: <strong id="liveEstimasiKg" style="color: #0284c7;">0.00 Kg</strong> <span id="liveEstimasiDesc">(Netto 6 Kg/Karton)</span>.
                        </small>
                    </div>

                    {{-- No Karton Awal & Akhir (Khusus IFM) --}}
                    <div id="rowKartonRange" style="display: contents;">
                        {{-- No Karton Awal --}}
                        <div>
                            <label style="display: block; font-size: 0.775rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                                No. Karton Awal <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="number" step="1" min="1" name="no_karton_awal" id="no_karton_awal" 
                                value="{{ old('no_karton_awal', 1) }}" 
                                class="form-control" 
                                style="font-family: monospace; font-weight: 700; font-size: 0.95rem;" 
                                oninput="updateKartonRangeAndBatch()">
                            <small id="kartonSourceInfo" style="color: #0284c7; font-size: 0.72rem; margin-top: 0.25rem; display: block;">
                                Memuat riwayat...
                            </small>
                        </div>

                        {{-- No Karton Akhir (Readonly) --}}
                        <div>
                            <label style="display: block; font-size: 0.775rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                                No. Karton Akhir
                            </label>
                            <input type="number" name="no_karton_akhir" id="no_karton_akhir" 
                                value="{{ old('no_karton_akhir', 1) }}" 
                                class="form-control" 
                                readonly 
                                style="background: #f1f5f9; font-family: monospace; font-weight: 700; font-size: 0.95rem; color: #475569;">
                            <small style="color: #64748b; font-size: 0.72rem; margin-top: 0.25rem; display: block;">
                                Otomatis: Awal + Qty - 1
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Baris 2: Varietas & Jam Packing --}}
                <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div>
                        <label style="display: block; font-size: 0.775rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Varietas Singkong Mentah
                        </label>
                        <input type="text" name="varietas_singkong" id="varietas_singkong" 
                            value="{{ old('varietas_singkong', 'STP / MGU') }}" 
                            class="form-control" 
                            placeholder="Contoh: STP, MGU, atau STP / MGU" 
                            style="font-weight: 600; font-size: 0.85rem;" 
                            oninput="updateStickerPreview()">
                        <small style="color: #64748b; font-size: 0.72rem;">Singkong Tape (STP) / Manggu (MGU).</small>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.775rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Jam Produksi / Cetak Stiker
                        </label>
                        <input type="time" name="jam_produksi" id="jam_produksi" 
                            value="{{ old('jam_produksi', date('H:i')) }}" 
                            class="form-control" 
                            style="font-family: monospace; font-weight: 700; font-size: 0.85rem;" 
                            oninput="updateStickerPreview()">
                        <small style="color: #64748b; font-size: 0.72rem;">Waktu packing fisik.</small>
                    </div>
                </div>

                {{-- HASIL KODE BATCH RANGE WIP & QUICK COPY BUTTON --}}
                <div class="batch-result-display">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.05em;">
                            Kode Batch Resmi (Kartu Persediaan):
                        </span>
                        <span style="font-size: 0.72rem; color: #38bdf8; font-weight: 600;">Otomatis Sesuai Standar Lini</span>
                    </div>
                    <div class="batch-result-code" id="liveBatchCode">
                        {{ old('batch_wip_no', 'A0001 - A0001') }}
                    </div>
                    <input type="hidden" name="batch_wip_no" id="batch_wip_no" value="{{ old('batch_wip_no') }}">
                    
                    {{-- Tampilan Estimasi Kedaluwarsa --}}
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.4rem; font-size: 0.75rem; border-top: 1px dashed #334155; padding-top: 0.4rem;">
                        <span style="color: #94a3b8;">📅 Estimasi Kedaluwarsa:</span>
                        <strong id="liveExpDate" style="color: #38bdf8;">-</strong>
                    </div>

                    {{-- Quick Action Buttons Salin ke Timbangan WIP --}}
                    <div style="display: flex; gap: 0.5rem; align-items: center; margin-top: 0.65rem; padding-top: 0.65rem; border-top: 1px dashed #334155; flex-wrap: wrap;">
                        <span style="font-size: 0.725rem; color: #94a3b8;">Salin hasil ke timbangan:</span>
                        <button type="button" class="btn-copy-wip" onclick="copyKartonToWipKg('asin_barco_qty')">
                            📥 Asin Barco
                        </button>
                        <button type="button" class="btn-copy-wip" style="background: #059669;" onclick="copyKartonToWipKg('asin_sawit_qty')">
                            📥 Asin Sawit
                        </button>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: LIVE PREVIEW STIKER KARTON FISIK / LABEL RETAIL --}}
            <div class="sticker-wrapper">
                <div style="font-size: 0.75rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.35rem;">
                    <span>🏷️ Preview Label Stiker Fisik Kemasan</span>
                </div>

                {{-- KOTAK STIKER FISIK ASLI PABRIK (PERSIS FISIK PABRIK) --}}
                <div class="carton-physical-sticker">
                    {{-- Header: WIP-FCC & Halal Emblem --}}
                    <div class="sticker-header">
                        <div class="sticker-title" id="stMainTitle">WIP-FCC</div>
                        <div class="sticker-halal-box">
                            <svg viewBox="0 0 100 100" width="34" height="34" style="display: block; margin: 0 auto;">
                                <circle cx="50" cy="50" r="46" fill="none" stroke="#000000" stroke-width="4"/>
                                <circle cx="50" cy="50" r="39" fill="none" stroke="#000000" stroke-width="1.5"/>
                                <text x="50" y="30" font-size="8.5" font-weight="900" text-anchor="middle" font-family="Arial, sans-serif">MAJELIS ULAMA</text>
                                <text x="50" y="58" font-size="18" font-weight="900" text-anchor="middle" font-family="'Times New Roman', serif">حلال</text>
                                <text x="50" y="73" font-size="8" font-weight="900" text-anchor="middle" font-family="Arial, sans-serif">INDONESIA</text>
                            </svg>
                            <div class="halal-cert-id">ID3321000001931219</div>
                            <div class="halal-cert-date">6 Februari 2024</div>
                        </div>
                    </div>

                    {{-- Body: 4 Baris Sinkron Presisi Tinggi --}}
                    <table class="st-table">
                        <tbody>
                            <tr>
                                <td class="st-lbl-left">No Batch</td>
                                <td class="st-colon">:</td>
                                <td class="st-val-left st-batch-num" id="stShiftKarton">A / 0001</td>
                                <td class="st-lbl-right">Tgl. Produksi</td>
                                <td class="st-colon">:</td>
                                <td class="st-val-right" id="stDate">{{ strtoupper(date('d M Y')) }}</td>
                            </tr>
                            <tr>
                                <td class="st-lbl-left">Gross</td>
                                <td class="st-colon">:</td>
                                <td class="st-val-left" id="stGross">7.08 kg</td>
                                <td class="st-lbl-right">Tgl. Kadaluarsa</td>
                                <td class="st-colon">:</td>
                                <td class="st-val-right" id="stExpDate">{{ strtoupper(date('d M Y', strtotime('+6 months'))) }}</td>
                            </tr>
                            <tr>
                                <td class="st-lbl-left">Netto</td>
                                <td class="st-colon">:</td>
                                <td class="st-val-left" id="stNetto">6 kg</td>
                                <td class="st-lbl-right">Varietas RM</td>
                                <td class="st-colon">:</td>
                                <td class="st-val-right" id="stVarietas">STP / MGU</td>
                            </tr>
                            <tr>
                                <td class="st-lbl-left">Jam</td>
                                <td class="st-colon">:</td>
                                <td class="st-val-left" id="stTime">14.03</td>
                                <td colspan="3" class="st-plant-cell">
                                    <div class="plant-code-box">M029 / - / ISA</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div style="font-size: 0.7rem; color: #64748b; text-align: center; max-width: 380px;">
                    💡 Label ini adalah identitas fisik yang ditempel pada kemasan saat hasil olahan disimpan ke gudang barang jadi.
                </div>
            </div>
        </div>

    </div>
</div>
