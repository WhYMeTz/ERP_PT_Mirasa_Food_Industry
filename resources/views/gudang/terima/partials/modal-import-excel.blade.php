{{-- ════════════════════════════════════════════════════════ --}}
{{--  MODAL IMPORT EXCEL BARANG MASUK                         --}}
{{-- ════════════════════════════════════════════════════════ --}}
<div id="modal-import-excel" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(15,23,42,0.55); align-items:center; justify-content:center; padding:1rem;">
    <div style="background:#fff; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.2); width:100%; max-width:520px; overflow:hidden;">

        {{-- Header Modal --}}
        <div style="background:#134e5e; padding:1rem 1.5rem; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div style="font-weight:700; font-size:1rem; color:#fff;">Import Data Barang Masuk dari Excel</div>
                <div style="font-size:0.8rem; color:#a5f3d8; margin-top:2px;">Upload file .xlsx sesuai template resmi ERP Mirasa</div>
            </div>
            <button type="button" onclick="document.getElementById('modal-import-excel').style.display='none'" style="background:none; border:none; color:#fff; cursor:pointer; font-size:1.4rem; line-height:1;">&times;</button>
        </div>

        {{-- Body Modal --}}
        <div style="padding:1.5rem;">

            {{-- Notifikasi sukses/warning/error --}}
            @if(session('success'))
                <div style="background:#dcfce7; border:1px solid #86efac; color:#14532d; border-radius:8px; padding:0.75rem 1rem; margin-bottom:1rem; font-size:0.85rem;">
                    ✅ {{ session('success') }}
                </div>
            @endif
            @if(session('warning'))
                <div style="background:#fef9c3; border:1px solid #fde68a; color:#713f12; border-radius:8px; padding:0.75rem 1rem; margin-bottom:1rem; font-size:0.85rem;">
                    ⚠️ {{ session('warning') }}
                </div>
            @endif
            @if(session('error'))
                <div style="background:#fee2e2; border:1px solid #fca5a5; color:#7f1d1d; border-radius:8px; padding:0.75rem 1rem; margin-bottom:1rem; font-size:0.85rem;">
                    ❌ {{ session('error') }}
                </div>
            @endif

            {{-- Langkah-langkah --}}
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:0.9rem 1rem; margin-bottom:1.25rem; font-size:0.83rem; color:#475569;">
                <div style="font-weight:700; color:#0f172a; margin-bottom:0.4rem;">📋 Cara Import:</div>
                <ol style="margin:0; padding-left:1.25rem; line-height:1.8;">
                    <li>Download template resmi terlebih dahulu</li>
                    <li>Isi data di sheet <strong>Barang Masuk</strong> mulai baris ke-7</li>
                    <li>Lihat sheet <em>Ref. Kode Barang</em>, <em>Ref. Supplier</em>, <em>Ref. Gudang</em> sebagai referensi</li>
                    <li>Baris dengan <strong>No. GRN sama</strong> dianggap satu dokumen GRN</li>
                    <li>Simpan file lalu upload di sini</li>
                </ol>
            </div>

            {{-- Download Template --}}
            <a href="{{ route('gudang.terima.download-template') }}" class="btn" style="display:flex; align-items:center; justify-content:center; gap:0.5rem; background:#f0fdf4; border:1.5px solid #059669; color:#059669; font-weight:700; padding:0.65rem 1rem; border-radius:8px; text-decoration:none; font-size:0.875rem; margin-bottom:1.25rem; width:100%;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                Download Template Import (.xlsx)
            </a>

            {{-- Form Upload --}}
            <form method="POST" action="{{ route('gudang.terima.import-excel') }}" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:1rem;">
                    <label style="display:block; font-size:0.85rem; font-weight:600; color:#374151; margin-bottom:0.5rem;">Pilih File Excel (.xlsx / .xls)</label>
                    <input type="file" name="import_file" id="import_file" accept=".xlsx,.xls"
                        style="display:block; width:100%; font-size:0.875rem; border:1.5px solid #cbd5e1; border-radius:8px; padding:0.5rem; background:#f8fafc; cursor:pointer;"
                        required>
                    @error('import_file')
                        <div style="color:#dc2626; font-size:0.8rem; margin-top:0.3rem;">{{ $message }}</div>
                    @enderror
                    <div style="font-size:0.77rem; color:#94a3b8; margin-top:0.3rem;">Maksimal 5 MB. Hanya format .xlsx dan .xls yang diterima.</div>
                </div>

                <div style="display:flex; gap:0.75rem;">
                    <button type="submit" id="btn-import-submit" style="flex:1; background:#134e5e; color:#fff; border:none; border-radius:8px; padding:0.7rem 1rem; font-size:0.9rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:0.5rem;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Proses Import
                    </button>
                    <button type="button" onclick="document.getElementById('modal-import-excel').style.display='none'" style="background:#f1f5f9; color:#475569; border:1.5px solid #e2e8f0; border-radius:8px; padding:0.7rem 1rem; font-size:0.9rem; font-weight:600; cursor:pointer;">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
