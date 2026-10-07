<div class="card" style="margin-bottom: 1.25rem; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); background: #ffffff;">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <strong style="color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
                <span>7. Hasil Barang Produksi (Barang Jadi &amp; Olahan Curah)</span>
            </strong>
            <span style="font-size: 0.775rem; color: #64748b; display: block; margin-top: 0.15rem;">
                Pencatatan rincian output barang yang dihasilkan (kemasan barang jadi FG maupun timbangan olahan curah WIP).
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span id="badgeTotalOutputKg" style="font-size: 0.825rem; font-weight: 700; color: #0284c7; background: #f0f9ff; border: 1px solid #bae6fd; padding: 0.3rem 0.75rem; border-radius: 6px;">
                Total Output: 0.00 kg
            </span>
        </div>
    </div>

    <div style="padding: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem; flex-wrap: wrap; gap: 0.5rem;">
            <span style="font-size: 0.825rem; font-weight: 700; color: #1e293b;">
                Daftar Barang Hasil Produksi:
            </span>
            <button type="button" onclick="addFgRow()" class="btn btn-sm btn-primary" style="padding: 0.35rem 0.75rem; font-size: 0.775rem;">
                <span>+ Tambah Baris Barang</span>
            </button>
        </div>

        <div style="overflow-x: auto; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 1rem;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem; text-align: left;" id="tableOutputFg">
                <thead>
                    <tr style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1; color: #334155; font-weight: 700; font-size: 0.725rem; text-transform: uppercase;">
                        <th style="padding: 0.5rem 0.75rem; width: 35px; text-align: center;">No</th>
                        <th style="padding: 0.5rem 0.75rem; width: 85px; text-align: center;">Jenis</th>
                        <th style="padding: 0.5rem 0.75rem; min-width: 260px;">Nama &amp; Kode Barang</th>
                        <th style="padding: 0.5rem 0.75rem; width: 130px; text-align: right;">QTY Hasil</th>
                        <th style="padding: 0.5rem 0.75rem; width: 90px; text-align: center;">Satuan</th>
                        <th style="padding: 0.5rem 0.75rem; width: 130px; text-align: right;">Total Berat (Kg)</th>
                        <th style="padding: 0.5rem 0.75rem; min-width: 140px; text-align: center;">Kode Batch (Otomatis)</th>
                        <th style="padding: 0.5rem 0.75rem; width: 50px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodyOutputFg">
                    {{-- Baris dirender secara dinamis oleh JavaScript --}}
                </tbody>
            </table>
        </div>

        {{-- CATATAN PRODUKSI / KUALITAS --}}
        <div>
            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                Catatan Produksi / Kualitas Shift
            </label>
            <textarea name="catatan_txt" id="catatan_txt" class="form-control" rows="2" placeholder="Catatan shift, deviasi bumbu, atau kendala pengerjaan...">{{ old('catatan_txt') }}</textarea>
        </div>
    </div>
</div>

