{{-- ════════════════════════════════════════════════════════ --}}
{{--  MODAL IMPORT EXCEL HASIL BARANG PRODUKSI (POINT 9)      --}}
{{-- ════════════════════════════════════════════════════════ --}}
<div id="modal-import-hasil" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(15,23,42,0.55); align-items:center; justify-content:center; padding:1rem;">
    <div style="background:#fff; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.2); width:100%; max-width:520px; overflow:hidden;">

        {{-- Header Modal --}}
        <div style="background:#0284c7; padding:1rem 1.5rem; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div style="font-weight:700; font-size:1rem; color:#fff;">Import Data Hasil Produksi dari Excel</div>
                <div style="font-size:0.8rem; color:#e0f2fe; margin-top:2px;">Upload file .xlsx hasil barang produksi &amp; persediaan WIP</div>
            </div>
            <button type="button" onclick="document.getElementById('modal-import-hasil').style.display='none'" style="background:none; border:none; color:#fff; cursor:pointer; font-size:1.4rem; line-height:1;">&times;</button>
        </div>

        {{-- Body Modal --}}
        <div style="padding:1.5rem;">

            {{-- Langkah-langkah --}}
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:0.9rem 1rem; margin-bottom:1.25rem; font-size:0.83rem; color:#475569;">
                <div style="font-weight:700; color:#0f172a; margin-bottom:0.4rem;">📋 Petunjuk Import Excel:</div>
                <ol style="margin:0; padding-left:1.25rem; line-height:1.8;">
                    <li>Download template resmi terlebih dahulu melalui tombol di bawah.</li>
                    <li>Isi data barang jadi/WIP di sheet <strong>Hasil Produksi</strong> mulai baris ke-7.</li>
                    <li>Lihat sheet <em>Ref. Kode Barang</em> dan <em>Ref. Gudang</em> untuk kode acuan.</li>
                    <li>Baris dengan No. Dokumen sama akan dikelompokkan ke satu lembar kerja.</li>
                    <li>Simpan file Excel lalu upload pada formulir di bawah ini.</li>
                </ol>
            </div>

            {{-- Download Template --}}
            <a href="{{ route('produksi.download-template') }}" class="btn" style="display:flex; align-items:center; justify-content:center; background:#f0fdf4; border:1px solid #cbd5e1; color:#0284c7; font-weight:700; padding:0.65rem 1rem; border-radius:6px; text-decoration:none; font-size:0.875rem; margin-bottom:1.25rem; width:100%;">
                <span>Download Template Hasil Produksi (.xlsx)</span>
            </a>

            {{-- Form Upload --}}
            <form method="POST" action="{{ route('produksi.import-excel') }}" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:1rem;">
                    <label style="display:block; font-size:0.85rem; font-weight:600; color:#374151; margin-bottom:0.5rem;">Pilih File Excel (.xlsx / .xls)</label>
                    <input type="file" name="import_file" id="import_file" accept=".xlsx,.xls"
                        style="display:block; width:100%; font-size:0.875rem; border:1px solid #cbd5e1; border-radius:6px; padding:0.5rem; background:#f8fafc; cursor:pointer;"
                        required>
                    @error('import_file')
                        <div style="color:#dc2626; font-size:0.8rem; margin-top:0.3rem;">{{ $message }}</div>
                    @enderror
                    <div style="font-size:0.77rem; color:#94a3b8; margin-top:0.3rem;">Maksimal 5 MB. Format didukung: .xlsx, .xls</div>
                </div>

                <div style="display:flex; gap:0.75rem;">
                    <button type="submit" id="btn-import-submit" style="flex:1; background:#0284c7; color:#fff; border:none; border-radius:6px; padding:0.7rem 1rem; font-size:0.9rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                        <span>Mulai Import Data</span>
                    </button>
                    <button type="button" onclick="document.getElementById('modal-import-hasil').style.display='none'" style="background:#f1f5f9; color:#475569; border:1.5px solid #e2e8f0; border-radius:8px; padding:0.7rem 1rem; font-size:0.9rem; font-weight:600; cursor:pointer;">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
