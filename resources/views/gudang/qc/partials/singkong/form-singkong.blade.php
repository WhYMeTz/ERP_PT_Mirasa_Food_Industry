{{-- ========================================================================= --}}
{{-- PARTIAL: FORM INSPEKSI QC KHUSUS SINGKONG                                 --}}
{{-- NO DOKUMEN: MFI/HACCP-04/FRM-03/048/VIII/2021                             --}}
{{-- ========================================================================= --}}

{{-- WIDGET INFORMASI STOK GUDANG SINGKONG GRADE A & GRADE B (REAL-TIME) --}}
<div id="singkongStockWidget" style="display: {{ old('kategori_barang', $initialKomoditas ?? 'SINGKONG') === 'SINGKONG' ? 'block' : 'none' }}; margin: 1rem 1.25rem 0 1.25rem; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 0.85rem 1.15rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem; flex-wrap: wrap; gap: 0.4rem;">
        <div style="font-size: 0.8rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 0.4rem;">
            <span>🏢</span> <span>STATUS STOK GUDANG SINGKONG SAAT INI (REAL-TIME):</span>
        </div>
        <span style="font-size: 0.7rem; color: #0284c7; font-weight: 700; background: #e0f2fe; padding: 2px 7px; border-radius: 4px;">
            Data Riil Gudang
        </span>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem;">
        <div style="background: #ffffff; border: 1.5px solid #86efac; border-radius: 8px; padding: 0.65rem 0.85rem; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <span style="font-size: 0.725rem; font-weight: 800; color: #166534; text-transform: uppercase;">🟢 Singkong Grade A</span>
                <div style="font-size: 1.2rem; font-weight: 900; color: #15803d;">
                    {{ number_format($stokSingkongA ?? 0, 0, ',', '.') }} <span style="font-size: 0.75rem; font-weight: 700; color: #166534;">KG</span>
                </div>
            </div>
            <span style="font-size: 0.7rem; font-weight: 700; color: #15803d; background: #dcfce7; padding: 3px 8px; border-radius: 6px;">Prioritas Produksi</span>
        </div>
        <div style="background: #ffffff; border: 1.5px solid #fde047; border-radius: 8px; padding: 0.65rem 0.85rem; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <span style="font-size: 0.725rem; font-weight: 800; color: #854d0e; text-transform: uppercase;">🟡 Singkong Grade B</span>
                <div style="font-size: 1.2rem; font-weight: 900; color: #b45309;" id="widgetStokGradeB">
                    {{ number_format($stokSingkongB ?? 0, 0, ',', '.') }} <span style="font-size: 0.75rem; font-weight: 700; color: #854d0e;">KG</span>
                </div>
            </div>
            <span style="font-size: 0.7rem; font-weight: 700; color: {{ ($stokSingkongB ?? 0) > 1000 ? '#b91c1c' : '#854d0e' }}; background: {{ ($stokSingkongB ?? 0) > 1000 ? '#fee2e2' : '#fef3c7' }}; padding: 3px 8px; border-radius: 6px;">
                {{ ($stokSingkongB ?? 0) > 1000 ? '⚠️ Stok Tinggi' : 'Stok Aman' }}
            </span>
        </div>
    </div>
    <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.45rem; line-height: 1.35;">
        💡 <em>Pedoman QC: Jika hasil fisik kedatangan tergolong <strong>Grade B</strong>, periksa stok Grade B di atas. Jika stok Grade B sudah menumpuk / kapasitas penuh, atasan menyarankan kedatangan ditolak.</em>
    </div>
</div>

{{-- 1. FORM KHUSUS SINGKONG (MULTIPLE ITEMS / DYNAMIC CARDS DARI JS) --}}
<div id="singkongContainer" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1.25rem;">
    {{-- Item Cards rendered by JS (createItemCard) --}}
</div>
