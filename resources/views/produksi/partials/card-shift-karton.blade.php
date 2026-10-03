{{-- 
  Kartu Shift Kerja, Penomoran Batch & Kemasan Karton / Barang Jadi
  Partials Blade ERP PT Mirasa Food Industry (Dual-Mode: IFM vs Reguler)
--}}

<div class="card" style="margin-bottom: 1.25rem; border: 1.5px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.04);">
    <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <strong id="cardHeaderTitle" style="color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
            <span>2. Shift Kerja, Penomoran Batch &amp; Kemasan Karton (Standar Indofood IFM / WIP-FCC)</span>
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
            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.45rem;">
                Pilih Shift Kerja Produksi <span style="color: #ef4444;">*</span>
            </label>
            <div class="shift-selection-grid">
                {{-- Tombol Shift A --}}
                <div id="btnShiftA" class="shift-card-btn {{ old('shift_cd', 'A') === 'A' ? 'active-a' : '' }}" onclick="selectShift('A')">
                    <div>
                        <div class="shift-title">
                            <span>Shift A (Pagi / Siang)</span>
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
                            <span>Shift B (Malam)</span>
                        </div>
                        <div class="shift-desc">
                            Jam kerja 15:00 - 23:00 WIB &bull; Otomatis melanjutkan nomor karton akhir hari ini
                        </div>
                    </div>
                    <span class="shift-badge">Shift B</span>
                </div>
            </div>
        </div>

        {{-- 2-COLUMN BALANCED WORKSPACE GRID --}}
        <div class="shift-workspace-grid">
            {{-- KOLOM KIRI: PARAMETER INPUT & SPESIFIKASI KARTON --}}
            <div class="shift-form-pane">
                {{-- Panel 1: Spesifikasi Nomor Karton & Waktu --}}
                <div class="form-subpanel">
                    <div class="subpanel-title">
                        <span>Spesifikasi Karton Box &amp; Penomoran</span>
                    </div>

                    {{-- Baris 1: 3 Kolom Rapi (Karton Selesai, No Awal, No Akhir) --}}
                    <div class="karton-trio-grid">
                        {{-- Qty Karton Selesai --}}
                        <div>
                            <label id="labelKartonTitle" style="display: block; font-size: 0.775rem; font-weight: 700; color: #0f172a; margin-bottom: 0.35rem;">
                                Karton Selesai <span style="color: #ef4444;">*</span>
                            </label>
                            <div class="input-suffix-wrapper">
                                <input type="number" step="1" min="0" name="qty_karton" id="qty_karton" 
                                    value="{{ old('qty_karton', 0) }}" 
                                    class="form-control" 
                                    style="font-weight: 800; font-size: 1.1rem; color: #0f172a; padding-right: 4.5rem;" 
                                    placeholder="0" 
                                    oninput="updateKartonRangeAndBatch()">
                                <span class="input-suffix-tag">Kemasan</span>
                            </div>
                            <small style="color: #64748b; font-size: 0.72rem; margin-top: 0.25rem; display: block;">
                                Berat: <strong id="liveEstimasiKg" style="color: #0284c7;">0.00 Kg</strong> <span id="liveEstimasiDesc">(Netto 6 Kg/Box)</span>
                            </small>
                        </div>

                        {{-- No. Karton Awal --}}
                        <div id="colKartonAwal">
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

                        {{-- No. Karton Akhir (Readonly) --}}
                        <div id="colKartonAkhir">
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

                    {{-- Baris 2: Varietas & Jam Packing --}}
                    <div class="varietas-time-grid" style="margin-top: 0.85rem;">
                        <div>
                            <label style="display: block; font-size: 0.775rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                                Varietas Singkong Mentah
                            </label>
                            <input type="text" name="varietas_singkong" id="varietas_singkong" 
                                value="{{ old('varietas_singkong', 'STP / MGU') }}" 
                                class="form-control" 
                                placeholder="Contoh: STP / MGU" 
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
                </div>

                {{-- Panel 2: Quick Fill Salin ke Timbangan --}}
                <div class="form-subpanel">
                    <div class="subpanel-title">
                        <span>Salin Hasil Estimasi ke Form Timbangan WIP</span>
                    </div>
                    <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.4rem;">
                        Klik tombol untuk menyalin hasil kalkulasi berat karton langsung ke rincian timbangan di bawah:
                    </div>
                    <div class="copy-actions-wrapper">
                        <button type="button" class="btn-copy-wip" onclick="copyKartonToWipKg('asin_barco_qty')">
                            Salin ke Asin Barco
                        </button>
                        <button type="button" class="btn-copy-wip btn-copy-sawit" onclick="copyKartonToWipKg('asin_sawit_qty')">
                            Salin ke Asin Sawit
                        </button>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: PREVIEW STIKER FISIK & BATCH SUMMARY RESMI --}}
            <div class="shift-preview-pane">
                <div class="preview-pane-header">
                    <span>PREVIEW LABEL STIKER FISIK KEMASAN</span>
                </div>

                {{-- KOTAK STIKER FISIK ASLI PABRIK (KOMPAK & PERSIS FISIK ASLI) --}}
                <div class="carton-physical-sticker">
                    {{-- Header: WIP-FCC & Halal Emblem --}}
                    <div class="sticker-header">
                        <div class="sticker-title" id="stMainTitle">WIP-FCC</div>
                        <div class="sticker-halal-box">
                            <svg viewBox="0 0 100 100" width="30" height="30" style="display: block; margin: 0 auto;">
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

                    {{-- Body: 2 Kolom Alami Tanpa Gap Canggung --}}
                    <div class="sticker-cols-container">
                        {{-- Kolom Kiri --}}
                        <div class="st-left-col">
                            <div class="st-row">
                                <span class="st-lbl">No Batch :</span>
                                <span class="st-val st-batch-num" id="stShiftKarton">A / 0001</span>
                            </div>
                            <div class="st-row">
                                <span class="st-lbl">Gross :</span>
                                <span class="st-val" id="stGross">7.08 kg</span>
                            </div>
                            <div class="st-row">
                                <span class="st-lbl">Netto :</span>
                                <span class="st-val" id="stNetto">6 kg</span>
                            </div>
                            <div class="st-row">
                                <span class="st-lbl">Jam :</span>
                                <span class="st-val" id="stTime">01.46</span>
                            </div>
                        </div>

                        {{-- Kolom Kanan --}}
                        <div class="st-right-col">
                            <div class="st-row">
                                <span class="st-lbl">Tgl. Produksi :</span>
                                <span class="st-val" id="stDate">{{ strtoupper(date('d M Y')) }}</span>
                            </div>
                            <div class="st-row">
                                <span class="st-lbl">Tgl. Kadaluarsa :</span>
                                <span class="st-val" id="stExpDate">{{ strtoupper(date('d M Y', strtotime('+6 months'))) }}</span>
                            </div>
                            <div class="st-row">
                                <span class="st-lbl">Varietas RM :</span>
                                <span class="st-val" id="stVarietas">STP / MGU</span>
                            </div>
                            <div class="st-row" style="justify-content: flex-end;">
                                <div class="plant-code-box" id="stPlantCode">M029 / - / ISA</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD BATCH SUMMARY RESMI PERSADAAN (KOMPAK & ELEGAN) --}}
                <div class="batch-summary-card">
                    <div class="header-line">
                        <span>Kode Batch Resmi Persadaan:</span>
                        <span style="color: #38bdf8;">Otomatis Standar Lini</span>
                    </div>
                    <div class="batch-code-val" id="liveBatchCode">
                        {{ old('batch_wip_no', 'A0001 - A0001') }}
                    </div>
                    <input type="hidden" name="batch_wip_no" id="batch_wip_no" value="{{ old('batch_wip_no') }}">
                    <div class="footer-line">
                        <span style="color: #94a3b8;">Estimasi Kedaluwarsa:</span>
                        <strong id="liveExpDate" style="color: #38bdf8;">-</strong>
                    </div>
                </div>

                <div style="font-size: 0.7rem; color: #64748b; text-align: center; max-width: 380px;">
                    Label ini adalah identitas fisik yang ditempel pada kemasan saat hasil olahan disimpan ke gudang barang jadi.
                </div>
            </div>
        </div>

    </div>
</div>
