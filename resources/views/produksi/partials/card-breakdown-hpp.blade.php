{{-- 
  Ringkasan Biaya Produksi & Alokasi HPP per Hasil Produksi (Terpadu)
  ERP PT Mirasa Food Industry
--}}
<div class="card" id="cardBreakdownHpp" style="margin-bottom: 1.5rem; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); background: #ffffff;">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.85rem 1.25rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
        <div style="min-width: 0;">
            <strong style="color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
                <span>7. Ringkasan Biaya Produksi &amp; Alokasi HPP Hasil Panen</span>
            </strong>
            <span style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.15rem;">
                Rekapitulasi total biaya kumulatif, performa rendemen singkong, dan alokasi nilai modal ke masing-masing barang hasil panen.
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 0.4rem; flex-shrink: 0;">
            <span id="badgeHppBalance" style="font-size: 0.775rem; font-weight: 700; color: #059669; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 0.25rem 0.65rem; border-radius: 6px; white-space: nowrap;">
                Alokasi Klop (100%)
            </span>
        </div>
    </div>

    <div style="padding: 1.25rem;">
        {{-- 1. SUB-PANEL KPI METRIK (3 KOLOM RINGKASAN UTAMA) --}}
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.1rem 1.25rem; margin-bottom: 1.25rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; align-items: center;">
                {{-- Live Biaya --}}
                <div>
                    <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em; display: block;">
                        Total Biaya Produksi (Shift)
                    </span>
                    <div id="liveTotalBiaya" style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-top: 0.2rem;">
                        Rp 0
                    </div>
                    <small style="color: #64748b; font-size: 0.725rem;">
                        Bahan + Kemasan + CNG + Upah
                    </small>
                </div>

                {{-- Live Rendemen --}}
                <div style="text-align: center;">
                    <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em; display: block;">
                        Rendemen Singkong
                    </span>
                    <div id="liveRendemen" style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-top: 0.2rem;">
                        0.00%
                    </div>
                    <span id="liveRendemenStatus" style="font-size: 0.725rem; font-weight: 600; color: #64748b;">
                        Menunggu timbangan
                    </span>
                </div>

                {{-- Live HPP / Kg --}}
                <div style="text-align: right;">
                    <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em; display: block;">
                        HPP Riil Produk Utama
                    </span>
                    <div id="liveHpp" style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-top: 0.2rem;">
                        Rp 0 / kg
                    </div>
                    <small style="color: #059669; font-size: 0.725rem; font-weight: 600;">
                        (Otomatis seimbang)
                    </small>
                </div>
            </div>
        </div>

        {{-- 2. SUB-PANEL TABEL BREAKDOWN ALOKASI HPP --}}
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.65rem;">
            <div>
                <span style="font-size: 0.85rem; font-weight: 700; color: #1e293b;">
                    Alokasi Nilai Modal per Barang Hasil:
                </span>
                <span id="noticeHppSingleProduct" style="font-size: 0.75rem; color: #64748b; margin-left: 0.35rem; display: none;">
                    (100% modal dialokasikan ke Produk Utama)
                </span>
            </div>

            {{-- Toolbar Metode Alokasi (Selalu tampil untuk konfigurasi rasio & acuan alokasi) --}}
            <div id="toolbarMetodeAlokasi" style="display: inline-flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <span style="font-size: 0.775rem; color: #64748b; font-weight: 600;">Metode Alokasi:</span>
                <div style="display: inline-flex; align-items: center; border: 1px solid #cbd5e1; border-radius: 6px; background: #ffffff; overflow: hidden; height: 32px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                    <span style="background: #f8fafc; padding: 0 0.55rem; font-size: 0.75rem; color: #475569; font-weight: 600; border-right: 1px solid #cbd5e1; line-height: 30px; white-space: nowrap;">
                        Rasio: Utama =
                    </span>
                    <input type="number" id="inputRasioMutu" step="0.1" min="1.0" max="10.0" value="2.5" 
                           style="width: 48px; border: none; padding: 0 0.35rem; text-align: center; font-weight: 700; font-size: 0.8rem; color: #0f172a; outline: none; height: 100%;"
                           oninput="if(document.getElementById('selectBasisBarang')?.value){ applyStandarRasio(); }" title="Rasio pengali harga Produk Utama terhadap Barang Acuan">
                    <span style="background: #f8fafc; padding: 0 0.4rem; font-size: 0.75rem; color: #64748b; font-weight: 700; border-left: 1px solid #cbd5e1; border-right: 1px solid #cbd5e1; line-height: 30px;">
                        ×
                    </span>
                    <select id="selectBasisBarang" onchange="onSelectBasisChange(this.value)" style="border: none; padding: 0 0.55rem; font-size: 0.75rem; font-weight: 600; color: #0f172a; background: #ffffff; outline: none; height: 100%; cursor: pointer;" title="Pilih baris barang yang menjadi acuan pengali">
                        <option value="">-- Pilih Baris Acuan --</option>
                    </select>
                    <button type="button" onclick="applyStandarRasio()" style="border: none; border-left: 1px solid #cbd5e1; background: #f8fafc; padding: 0 0.75rem; font-size: 0.75rem; font-weight: 600; color: #334155; cursor: pointer; height: 100%;" title="Terapkan alokasi standar rasio" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                        Hitung
                    </button>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetModeBagiRata()" style="padding: 0 0.75rem; font-size: 0.75rem; font-weight: 600; border-color: #cbd5e1; color: #334155; height: 32px;" title="Bagi rata biaya produksi ke seluruh output tanpa rasio mutu">
                    Bagi Rata
                </button>
            </div>
        </div>

        <div style="overflow-x: auto; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 0.85rem;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem; text-align: left;" id="tableBreakdownHpp">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1; color: #475569; font-weight: 700; font-size: 0.725rem; text-transform: uppercase; letter-spacing: 0.03em;">
                        <th style="padding: 0.65rem 0.85rem; width: 45px; text-align: center;">No</th>
                        <th style="padding: 0.65rem 0.85rem; min-width: 260px;">Barang Hasil Produksi</th>
                        <th style="padding: 0.65rem 0.85rem; width: 140px; text-align: center;">Kategori Mutu</th>
                        <th style="padding: 0.65rem 0.85rem; width: 130px; text-align: right;">Output (Kg)</th>
                        <th style="padding: 0.65rem 0.85rem; width: 90px; text-align: right;">Porsi (%)</th>
                        <th style="padding: 0.65rem 0.85rem; width: 190px; text-align: right;">HPP Satuan (Rp/Kg)</th>
                        <th style="padding: 0.65rem 0.85rem; width: 170px; text-align: right;">Total Nilai Modal</th>
                    </tr>
                </thead>
                <tbody id="tbodyBreakdownHpp">
                    <tr>
                        <td colspan="7" style="padding: 2.25rem 1rem; text-align: center; color: #94a3b8; background: #ffffff;">
                            <div style="font-size: 0.875rem; font-weight: 700; color: #475569;">Belum Ada Barang Hasil Produksi yang Dipilih</div>
                            <div style="font-size: 0.775rem; color: #94a3b8; margin-top: 0.35rem;">Silakan pilih barang hasil olahan setengah jadi (WIP) pada Bagian 2 di atas untuk mengaktifkan tabel alokasi HPP.</div>
                        </td>
                    </tr>
                </tbody>
                <tfoot style="background: #f8fafc; border-top: 2px solid #cbd5e1; font-weight: 700;">
                    <tr>
                        <td colspan="3" style="padding: 0.65rem 0.85rem; text-align: right; color: #0f172a; font-size: 0.785rem; text-transform: uppercase; letter-spacing: 0.02em;">
                            Total Keluaran Produksi:
                        </td>
                        <td id="footerBreakdownTotalKg" style="padding: 0.65rem 0.85rem; text-align: right; color: #0284c7; font-weight: 800; font-size: 0.85rem; font-variant-numeric: tabular-nums;">
                            0.00 kg
                        </td>
                        <td id="footerBreakdownTotalPorsi" style="padding: 0.65rem 0.85rem; text-align: right; color: #64748b; font-variant-numeric: tabular-nums;">
                            100.0%
                        </td>
                        <td style="padding: 0.65rem 0.85rem; text-align: right; color: #059669; font-size: 0.75rem; font-weight: 600;">
                            Rekonsiliasi 100%
                        </td>
                        <td id="footerBreakdownTotalNilai" style="padding: 0.65rem 0.85rem; text-align: right; color: #0f172a; font-size: 0.95rem; font-weight: 800; font-variant-numeric: tabular-nums;">
                            Rp 0
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div id="reconciliationNotice" style="background: #f8fafc; padding: 0.65rem 1rem; border-radius: 6px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; font-size: 0.775rem;">
            <div style="display: flex; align-items: center; gap: 0.45rem; color: #475569;">
                <svg width="15" height="15" fill="none" stroke="#64748b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>
                    <strong>Panduan:</strong> HPP Produk Utama otomatis menyerap sisa modal. Nilai HPP produk sampingan dapat dihitung via rasio pengali atau diketik manual.
                </span>
            </div>
            <span id="reconciliationDiff" style="font-weight: 700; color: #059669; font-family: ui-monospace, SFMono-Regular, monospace; white-space: nowrap; background: #ffffff; padding: 0.2rem 0.55rem; border-radius: 4px; border: 1px solid #cbd5e1;">
                Selisih Alokasi: Rp 0
            </span>
        </div>
    </div>
</div>
