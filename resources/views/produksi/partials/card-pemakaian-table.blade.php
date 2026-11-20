<div id="wrapperTabelPemakaian" style="margin-top: 1rem; display: none;">
    <div style="border: 1px solid #cbd5e1; border-radius: 8px; background: #ffffff; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        {{-- HEADER RINGKASAN BPPB --}}
        <div style="background: #f8fafc; border-bottom: 1px solid #cbd5e1; padding: 0.65rem 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <strong style="color: #0f172a; font-size: 0.85rem;">Rincian Pemakaian Bahan Baku (BPPB)</strong>
                <span id="badgeJumlahItemPakai" style="font-size: 0.725rem; font-weight: 700; background: #e2e8f0; color: #334155; padding: 0.15rem 0.45rem; border-radius: 4px;">0 Item</span>
                <span id="bppbMetaDoc" style="font-size: 0.75rem; color: #64748b; font-weight: 500;">
                    {{-- Diisi dinamis: • [No. BPPB] • Gudang • Tanggal --}}
                </span>
            </div>

            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span id="badgeTotalBahanQty" style="font-size: 0.75rem; font-weight: 700; color: #0284c7; background: #f0f9ff; border: 1px solid #bae6fd; padding: 0.2rem 0.6rem; border-radius: 4px;">
                    Total: 0,00 KG
                </span>
            </div>
        </div>

        {{-- NOTIFIKASI KOMPOSISI MULTI-GRADE SINGKONG --}}
        <div id="singkongGradeNotice" style="display: none; padding: 0.45rem 1rem; background: #f0fdf4; border-bottom: 1px solid #bbf7d0; font-size: 0.75rem; color: #166534; font-weight: 600; display: flex; align-items: center; gap: 0.4rem;">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0; color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span id="singkongGradeNoticeText"></span>
        </div>

        {{-- TABEL RINCIAN BAHAN DENGAN FIXED LAYOUT & ENTERPRISE GRID --}}
        <div style="overflow-x: auto;">
            <table class="table-bppb-item" style="width: 100%; border-collapse: collapse; font-size: 0.8rem; text-align: left; table-layout: fixed;" id="tableBppbItems">
                <thead>
                    <tr style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1; color: #334155; font-weight: 700; font-size: 0.725rem; text-transform: uppercase; letter-spacing: 0.03em;">
                        <th style="padding: 0.65rem 0.75rem; width: 4%; text-align: center;">No</th>
                        <th style="padding: 0.65rem 0.75rem; width: 14%;">Kategori</th>
                        <th style="padding: 0.65rem 0.75rem; width: 13%;">Kode Barang</th>
                        <th style="padding: 0.65rem 0.75rem; width: 27%;">Nama Bahan Baku</th>
                        <th style="padding: 0.65rem 0.75rem; width: 8%; text-align: center;">Grade</th>
                        <th style="padding: 0.65rem 0.75rem; width: 14%;">Batch Gudang</th>
                        <th style="padding: 0.65rem 0.75rem; width: 10%; text-align: right;">Jumlah Pakai</th>
                        <th style="padding: 0.65rem 0.75rem; width: 10%; text-align: right;">Sisa Stok Gudang</th>
                    </tr>
                </thead>
                <tbody id="tbodyTabelPemakaian">
                    <tr>
                        <td colspan="8" style="padding: 1.25rem 1rem; text-align: center; color: #94a3b8; font-style: italic;">
                            Pilih dokumen BPPB di atas untuk melihat rincian pemakaian bahan.
                        </td>
                    </tr>
                </tbody>
                <tfoot id="tfootTabelPemakaian" style="display: none;">
                    <tr>
                        <td colspan="6" style="padding: 0.75rem 0.85rem; text-align: right; color: #1e293b; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; background: #f8fafc; border-top: 2px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">
                            Total Akumulasi Bahan:
                        </td>
                        <td id="tfootTotalQty" style="padding: 0.75rem 0.85rem; text-align: right; color: #0284c7; font-size: 0.9rem; font-weight: 800; font-variant-numeric: tabular-nums; background: #f8fafc; border-top: 2px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">
                            0,00 KG
                        </td>
                        <td style="padding: 0.75rem 0.85rem; text-align: right; color: #64748b; font-size: 0.75rem; font-weight: 600; background: #f8fafc; border-top: 2px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">
                            Stok Fisik
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
