{{-- MODAL ADJUSTMENT HPP HARIAN (STANDAR AUDIT TRAIL AKUNTANSI) --}}
<div class="modal fade" id="modalAdjustHpp" tabindex="-1" aria-labelledby="modalAdjustHppLabel" aria-hidden="true" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); z-index: 1050; overflow-y: auto; backdrop-filter: blur(2px);">
    <div class="modal-dialog modal-lg" style="max-width: 780px; margin: 2rem auto; background: #ffffff; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; border: 1px solid #cbd5e1;">
        <div class="modal-content" style="border: none;">
            
            {{-- Modal Header --}}
            <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 1.15rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <div style="width: 34px; height: 34px; border-radius: 8px; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <h2 id="modalAdjustHppLabel" style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">
                            Adjustment HPP Lembar Produksi
                        </h2>
                        <span style="font-size: 0.775rem; color: #64748b;">
                            Dokumen: <strong>{{ $produksi->produksi_no }}</strong> &bull; Tgl: {{ Carbon\Carbon::parse($produksi->produksi_tgl)->format('d/m/Y') }} &bull; Versi Saat Ini: v{{ $produksi->version_no ?? 1 }}
                        </span>
                    </div>
                </div>
                <button type="button" onclick="closeModalAdjustHpp()" style="background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer; line-height: 1; padding: 0.2rem 0.5rem;" title="Tutup">&times;</button>
            </div>

            <form action="{{ route('produksi.adjust-hpp', $produksi->produksi_id) }}" method="POST" id="formAdjustHpp">
                @csrf
                <div style="padding: 1.5rem;">
                    
                    {{-- Notice Standar Akuntansi --}}
                    <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 8px; padding: 0.85rem 1rem; color: #166534; font-size: 0.8rem; margin-bottom: 1.25rem; display: flex; align-items: flex-start; gap: 0.5rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0; margin-top: 2px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <strong>Standar Audit Trail Akuntansi:</strong>
                            Setiap adjustment nominal akan dicatat ke dalam Berita Acara Audit Akuntansi (Log History), menghitung ulang HPP riil secara transparan, dan otomatis memperbarui nilai HPP pada saldo batch stok gudang.
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                        
                        {{-- Kolom Kiri: Input Nominal Komponen Biaya --}}
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem;">
                            <div style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #334155; margin-bottom: 0.75rem; border-bottom: 1px solid #cbd5e1; padding-bottom: 0.35rem;">
                                Nominal Komponen Biaya (Rp)
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                                {{-- 1. Minyak Goreng --}}
                                <div>
                                    <div style="display: flex; justify-content: space-between; font-size: 0.775rem; margin-bottom: 0.2rem;">
                                        <span style="font-weight: 700; color: #1e293b;">Biaya Minyak Goreng</span>
                                        <span style="color: #64748b;">Awal: Rp {{ number_format($produksi->minyak_nilai, 0, ',', '.') }}</span>
                                    </div>
                                    <input type="number" step="1" min="0" name="minyak_nilai" id="input_adj_minyak" 
                                           class="form-control adj-calc-input" value="{{ round($produksi->minyak_nilai) }}"
                                           style="text-align: right; font-weight: 700; font-size: 0.85rem;" oninput="recalculateHppSimulation()">
                                </div>

                                {{-- 2. Singkong Mentah --}}
                                <div>
                                    <div style="display: flex; justify-content: space-between; font-size: 0.775rem; margin-bottom: 0.2rem;">
                                        <span style="font-weight: 700; color: #1e293b;">Biaya Singkong Mentah</span>
                                        <span style="color: #64748b;">Awal: Rp {{ number_format($produksi->singkong_nilai, 0, ',', '.') }}</span>
                                    </div>
                                    <input type="number" step="1" min="0" name="singkong_nilai" id="input_adj_singkong" 
                                           class="form-control adj-calc-input" value="{{ round($produksi->singkong_nilai) }}"
                                           style="text-align: right; font-weight: 700; font-size: 0.85rem;" oninput="recalculateHppSimulation()">
                                </div>

                                {{-- 3. Bumbu Masak --}}
                                <div>
                                    <div style="display: flex; justify-content: space-between; font-size: 0.775rem; margin-bottom: 0.2rem;">
                                        <span style="font-weight: 700; color: #1e293b;">Biaya Bumbu</span>
                                        <span style="color: #64748b;">Awal: Rp {{ number_format($produksi->bumbu_nilai, 0, ',', '.') }}</span>
                                    </div>
                                    <input type="number" step="1" min="0" name="bumbu_nilai" id="input_adj_bumbu" 
                                           class="form-control adj-calc-input" value="{{ round($produksi->bumbu_nilai) }}"
                                           style="text-align: right; font-weight: 700; font-size: 0.85rem;" oninput="recalculateHppSimulation()">
                                </div>

                                {{-- 4. Tenaga Kerja (BTKL) --}}
                                <div>
                                    <div style="display: flex; justify-content: space-between; font-size: 0.775rem; margin-bottom: 0.2rem;">
                                        <span style="font-weight: 700; color: #1e293b;">Upah Tenaga Kerja (BTKL)</span>
                                        <span style="color: #64748b;">Awal: Rp {{ number_format($produksi->tk_total_nilai, 0, ',', '.') }}</span>
                                    </div>
                                    <input type="number" step="1" min="0" name="tk_total_nilai" id="input_adj_tk" 
                                           class="form-control adj-calc-input" value="{{ round($produksi->tk_total_nilai) }}"
                                           style="text-align: right; font-weight: 700; font-size: 0.85rem;" oninput="recalculateHppSimulation()">
                                </div>

                                {{-- 5. Utilitas & Pemeliharaan (Readonly - Dikelola via Rekonsiliasi Bulanan di Buku Rekap) --}}
                                <div style="background: #f1f5f9; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 0.5rem 0.65rem;">
                                    <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; margin-bottom: 0.35rem; display: flex; justify-content: space-between; align-items: center;">
                                        <span>Biaya Utilitas (PLN &amp; Gas)</span>
                                        <span style="font-size: 0.65rem; background: #e2e8f0; color: #475569; padding: 1px 4px; border-radius: 3px;">Terkunci / Bulanan</span>
                                    </div>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                        <div>
                                            <label style="font-size: 0.7rem; color: #64748b; display: block;">Listrik (PLN)</label>
                                            <input type="number" step="1" min="0" name="listrik_air_telp_nilai" id="input_adj_listrik" 
                                                   class="form-control adj-calc-input" value="{{ round($produksi->listrik_air_telp_nilai) }}" readonly
                                                   style="text-align: right; font-size: 0.8rem; background: #ffffff; color: #64748b; cursor: not-allowed;" title="Disesuaikan secara massal via Rekonsiliasi Utilitas Bulanan">
                                        </div>
                                        <div>
                                            <label style="font-size: 0.7rem; color: #64748b; display: block;">Gas CNG</label>
                                            <input type="number" step="1" min="0" name="cng_nilai" id="input_adj_cng" 
                                                   class="form-control adj-calc-input" value="{{ round($produksi->cng_nilai) }}" readonly
                                                   style="text-align: right; font-size: 0.8rem; background: #ffffff; color: #64748b; cursor: not-allowed;" title="Disesuaikan secara massal via Rekonsiliasi Utilitas Bulanan">
                                        </div>
                                    </div>
                                    <div style="font-size: 0.675rem; color: #94a3b8; margin-top: 0.3rem;">
                                        &bull; Tagihan PLN &amp; Gas disesuaikan serentak di akhir bulan via Buku Rekap HPP.
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom Kanan: Live Simulation & Komparasi Dampak --}}
                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            
                            {{-- Box HPP Summary --}}
                            <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em; margin-bottom: 0.5rem;">
                                    Simulasi Dampak HPP / Kg
                                </div>
                                <div style="display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span style="font-size: 0.8rem; color: #64748b;">HPP Saat Ini:</span>
                                    <span style="font-size: 0.95rem; font-weight: 700; color: #334155; font-family: monospace;">
                                        Rp {{ number_format($produksi->hpp_per_kg, 2, ',', '.') }}
                                    </span>
                                </div>
                                <div style="display: flex; align-items: baseline; justify-content: space-between; border-top: 1px dashed #cbd5e1; padding-top: 0.5rem; margin-bottom: 0.5rem;">
                                    <span style="font-size: 0.85rem; font-weight: 800; color: #0f172a;">HPP Baru (Simulasi):</span>
                                    <span id="simulasiHppBaruText" style="font-size: 1.25rem; font-weight: 900; color: #0284c7; font-family: monospace;">
                                        Rp {{ number_format($produksi->hpp_per_kg, 2, ',', '.') }}
                                    </span>
                                </div>
                                <div id="simulasiSelisihHppBadge" style="font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.5rem; border-radius: 4px; text-align: center; background: #f1f5f9; color: #64748b;">
                                    Selisih HPP: Rp 0 / kg
                                </div>
                            </div>

                            {{-- Box Total Biaya Summary --}}
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem;">
                                <div style="display: flex; justify-content: space-between; font-size: 0.775rem; margin-bottom: 0.35rem;">
                                    <span style="color: #64748b;">Output WIP Total:</span>
                                    <strong style="color: #0f172a;">{{ number_format($produksi->total_wip_qty, 2, ',', '.') }} kg</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-size: 0.775rem; margin-bottom: 0.35rem;">
                                    <span style="color: #64748b;">Total Biaya Lama:</span>
                                    <strong style="font-family: monospace;">Rp {{ number_format($produksi->total_biaya_produksi, 0, ',', '.') }}</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-size: 0.775rem; border-top: 1px solid #f1f5f9; padding-top: 0.35rem;">
                                    <span style="color: #0f172a; font-weight: 700;">Total Biaya Baru:</span>
                                    <strong id="simulasiTotalBiayaBaruText" style="font-family: monospace; color: #0f172a; font-weight: 800;">
                                        Rp {{ number_format($produksi->total_biaya_produksi, 0, ',', '.') }}
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ALASAN ADJUSTMENT / BERITA ACARA (WAJIB STANDAR AKUNTANSI) --}}
                    <div>
                        <label for="alasan_txt" style="display: block; font-size: 0.825rem; font-weight: 800; color: #0f172a; margin-bottom: 0.35rem;">
                            Alasan Adjustment / Dasar Dokumen Berita Acara <span style="color: #ef4444;">*</span>
                        </label>
                        <textarea name="alasan_txt" id="alasan_txt" rows="2" required class="form-control"
                                  placeholder="Contoh: Koreksi faktur minyak sawit supplier PT XYZ nota No. INV-8890 atau selisih timbangan tangki..."
                                  style="font-size: 0.825rem; resize: vertical;"></textarea>
                        <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.25rem; display: block;">
                            Wajib diisi sebagai pertanggungjawaban audit akuntansi pabrik.
                        </small>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.85rem 1.5rem; display: flex; justify-content: flex-end; gap: 0.5rem; align-items: center;">
                    <button type="button" onclick="closeModalAdjustHpp()" class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary" style="font-size: 0.85rem; padding: 0.5rem 1.25rem; font-weight: 700; background: #0284c7; border-color: #0284c7; display: inline-flex; align-items: center; gap: 0.4rem;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan &amp; Terapkan Adjustment HPP</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
