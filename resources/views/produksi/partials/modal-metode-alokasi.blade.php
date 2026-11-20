{{-- 
    Partial Modal: Metode Diferensiasi HPP & Pembebanan Sisa Biaya ke Produk Utama
    ERP PT Mirasa Food Industry
    Standar: Enterprise Window & Separation of Concerns (AGENTS.md)
--}}
<div id="modalMetodeAlokasi" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 99999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #ffffff; border-radius: 10px; max-width: 560px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; border: 1px solid #cbd5e1; animation: modalFadeIn 0.2s ease-out;">
        {{-- 1. Header Modal --}}
        <div style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <div style="width: 34px; height: 34px; border-radius: 6px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; border: 1px solid #a7f3d0;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #0f172a;">Metode Diferensiasi HPP Produk WIP</h3>
                    <span style="font-size: 0.75rem; color: #64748b;">Perhitungan fleksibel pengali produk KW 2 &amp; pembebanan sisa biaya ke Produk Utama</span>
                </div>
            </div>
            <button type="button" onclick="closeModalMetodeAlokasi()" style="background: none; border: none; font-size: 1.25rem; color: #94a3b8; cursor: pointer; padding: 0.25rem; line-height: 1;">&times;</button>
        </div>

        {{-- 2. Body Modal --}}
        <div style="padding: 1.25rem;">
            {{-- Info Baris Biaya Dasar Shift --}}
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="display: block; font-size: 0.7rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Total Biaya Manufaktur</span>
                    <strong id="modalDispTotalBiaya" style="font-size: 1rem; color: #0f172a;">Rp 0</strong>
                </div>
                <div style="text-align: right;">
                    <span style="display: block; font-size: 0.7rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Harga Beli Singkong (Basis)</span>
                    <strong id="modalDispHargaSingkong" style="font-size: 1rem; color: #0284c7;">Rp 0 / kg</strong>
                </div>
            </div>

            {{-- Form Konfigurasi Fleksibel --}}
            <div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                {{-- Pilihan Target Baris KW 2 --}}
                <div>
                    <label for="modalTargetSelect" style="display: block; font-size: 0.775rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Pilih Produk yang Dikalikan <span style="color: #ef4444;">*</span>
                    </label>
                    <select id="modalTargetSelect" class="form-control" onchange="calcModalSimulation()" style="width: 100%; height: 38px; font-size: 0.825rem; font-weight: 600; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0 0.65rem;">
                        <option value="all_kw2">Semua KW 2 (Berko, Lengket, Remahan)</option>
                        <option value="berko_only">Hanya Berko Biasa</option>
                        <option value="lengket_only">Hanya Berko Lengket</option>
                        <option value="remahan_only">Hanya Remahan</option>
                    </select>
                    <small style="color: #64748b; font-size: 0.7rem; margin-top: 0.25rem; display: block;">Tentukan baris produk sampingan sasaran.</small>
                </div>

                {{-- Faktor Pengali Bebas --}}
                <div>
                    <label for="modalFactorInput" style="display: block; font-size: 0.775rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Faktor Pengali <span style="color: #ef4444;">*</span>
                    </label>
                    <div style="display: flex; align-items: center; border: 1.5px solid #cbd5e1; border-radius: 6px; overflow: hidden; height: 38px;">
                        <span style="background: #f8fafc; padding: 0 0.6rem; color: #64748b; font-weight: 700; font-size: 0.85rem; border-right: 1px solid #e2e8f0;">&times;</span>
                        <input type="number" step="0.1" min="0" id="modalFactorInput" value="2.5" oninput="calcModalSimulation()" style="width: 100%; height: 100%; border: none; outline: none; padding: 0 0.5rem; font-weight: 700; font-size: 0.9rem; color: #0f172a; text-align: center;">
                    </div>
                    <small style="color: #64748b; font-size: 0.7rem; margin-top: 0.25rem; display: block;">Bebas diisi 2.5, 2.0, 3.0, dll.</small>
                </div>
            </div>

            {{-- Kartu Hasil Simulasi Alokasi Real-Time --}}
            <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 8px; padding: 0.85rem 1.15rem; margin-bottom: 0.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; padding-bottom: 0.4rem; border-bottom: 1px dashed #bbf7d0;">
                    <span style="font-size: 0.775rem; color: #166534; font-weight: 600;">HPP Produk KW 2 Terpilih:</span>
                    <strong id="modalSimKw2Hpp" style="font-size: 0.875rem; color: #047857;">Rp 10.688 / kg</strong>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; padding-bottom: 0.4rem; border-bottom: 1px dashed #bbf7d0;">
                    <span style="font-size: 0.775rem; color: #166534; font-weight: 600;">Total Nilai Terserap KW 2 (<span id="modalSimKw2Kg">0</span> kg):</span>
                    <strong id="modalSimKw2Nilai" style="font-size: 0.875rem; color: #047857;">Rp 0</strong>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; padding-bottom: 0.4rem; border-bottom: 1px dashed #bbf7d0;">
                    <span style="font-size: 0.775rem; color: #166534; font-weight: 600;">Sisa Biaya untuk Produk Utama:</span>
                    <strong id="modalSimUtamaNilai" style="font-size: 0.875rem; color: #047857;">Rp 0</strong>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.6rem;">
                    <div>
                        <span style="display: block; font-size: 0.725rem; color: #15803d; font-weight: 700; text-transform: uppercase;">HPP HASIL PRODUK UTAMA:</span>
                        <small id="modalSimUtamaNama" style="color: #166534; font-size: 0.725rem;">KS ASIN BARCO (0 kg)</small>
                    </div>
                    <strong id="modalSimUtamaHpp" style="font-size: 1.15rem; color: #14532d; font-weight: 800;">Rp 0 / kg</strong>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.75rem; color: #059669; font-weight: 600; margin-top: 0.4rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Total Alokasi 100% klop (Balance) dengan Total Biaya Manufaktur.</span>
            </div>
        </div>

        {{-- 3. Footer Modal --}}
        <div style="background: #f8fafc; border-top: 1.5px solid #e2e8f0; padding: 0.85rem 1.25rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button type="button" onclick="closeModalMetodeAlokasi()" style="padding: 0.5rem 1rem; border: 1.5px solid #cbd5e1; background: #ffffff; color: #475569; border-radius: 6px; font-weight: 600; font-size: 0.8125rem; cursor: pointer;">
                Batal
            </button>
            <button type="button" onclick="applyModalDiferensiasiToTable()" style="padding: 0.5rem 1.25rem; border: 1px solid #047857; background: #059669; color: #ffffff; border-radius: 6px; font-weight: 700; font-size: 0.8125rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Terapkan ke Tabel HPP</span>
            </button>
        </div>
    </div>
</div>

