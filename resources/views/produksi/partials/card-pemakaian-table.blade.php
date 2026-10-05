{{-- ═════════════════════════════════════════════════════════════
     PARTIAL: TABEL PEMAKAIAN BAHAN BAKU (BLUEPRINT 9.B.1)
     Menampilkan: Tanggal Pemakaian, Kode Barang, Nama Barang,
     Jumlah Pemakaian, dan Sisa Stok Barang di Gudang.
     ═════════════════════════════════════════════════════════════ --}}
<div id="wrapperTabelPemakaian" style="margin-top: 1rem; display: none;">
    <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        <div style="background: #f8fafc; padding: 0.65rem 1rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 0.85rem; font-weight: 700; color: #0f172a;">
                    Rincian Pemakaian Bahan Baku (BPPB)
                </span>
                <span id="badgeJumlahItemPakai" style="font-size: 0.725rem; font-weight: 700; background: #e2e8f0; color: #334155; padding: 0.15rem 0.5rem; border-radius: 9999px;">
                    0 Item
                </span>
            </div>
            <span style="font-size: 0.75rem; color: #64748b;">
                Bahan yang dikeluarkan dari gudang untuk proses produksi shift ini.
            </span>
        </div>

        <div style="overflow-x: auto; max-height: 260px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569; font-weight: 700; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.04em;">
                        <th style="padding: 0.6rem 0.85rem; width: 40px; text-align: center;">No</th>
                        <th style="padding: 0.6rem 0.85rem;">Tanggal Pemakaian</th>
                        <th style="padding: 0.6rem 0.85rem;">Kode Barang</th>
                        <th style="padding: 0.6rem 0.85rem;">Nama Barang</th>
                        <th style="padding: 0.6rem 0.85rem; text-align: right;">Jumlah Pemakaian</th>
                        <th style="padding: 0.6rem 0.85rem; text-align: right;">Sisa Stok Gudang</th>
                    </tr>
                </thead>
                <tbody id="tbodyTabelPemakaian">
                    {{-- Diisi secara dinamis oleh JavaScript produksi-create.js --}}
                    <tr>
                        <td colspan="6" style="padding: 1rem; text-align: center; color: #94a3b8; font-style: italic;">
                            Pilih dokumen BPPB di atas untuk melihat rincian pemakaian bahan.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
