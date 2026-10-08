{{-- ========================================================================= --}}
{{-- PARTIAL: FORM INSPEKSI QC KHUSUS KARTON BOX                              --}}
{{-- NO DOKUMEN: MFI/HACCP-04/FRM-03/031/VIII/2021                             --}}
{{-- ========================================================================= --}}
<div id="kartonContainer" style="display: {{ old('kategori_barang', $initialKomoditas ?? 'SINGKONG') === 'KARTON' ? 'flex' : 'none' }}; padding: 1.4rem; flex-direction: column; gap: 1.25rem;">
    <div class="form-group" style="margin-bottom: 0;">
        <label class="form-label" style="font-weight: 700;">Komoditas Karton Box <span style="color:red;">*</span></label>
        <select name="karton_barang_id" id="kartonBarangSelect" class="form-control" style="font-weight: 700;" onchange="syncNamaRmFromSelect(this)">
            @foreach ($barangs as $b)
                @if (stripos($b->barang_nm, 'karton') !== false || stripos($b->barang_nm, 'dus') !== false || stripos($b->barang_nm, 'box') !== false)
                    <option value="{{ $b->barang_id }}" data-nama="{{ $b->barang_nm }}">{{ $b->barang_cd }} - {{ $b->barang_nm }} ({{ $b->satuanDasar?->satuan_nm ?? 'PCS' }})</option>
                @endif
            @endforeach
        </select>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
        <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
            <span style="font-weight: 800; font-size: 0.85rem; color: #0f172a;">2. ISI RAW MATERIAL :</span>
            <div style="display: flex; gap: 1.25rem; margin-top: 0.5rem;">
                <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #15803d; cursor: pointer;">
                    <input type="radio" name="karton_status_raw_material" value="OK" checked> OK
                </label>
                <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #dc2626; cursor: pointer;">
                    <input type="radio" name="karton_status_raw_material" value="TDK_STD"> TDK STD
                </label>
            </div>
        </div>

        <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
            <span style="font-weight: 800; font-size: 0.85rem; color: #0f172a;">KEMASAN :</span>
            <div style="display: flex; gap: 1.25rem; margin-top: 0.5rem;">
                <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #15803d; cursor: pointer;">
                    <input type="radio" name="karton_kemasan_kondisi" value="OK" checked> OK
                </label>
                <label style="display: flex; align-items: center; gap: 0.35rem; font-weight: 700; color: #dc2626; cursor: pointer;">
                    <input type="radio" name="karton_kemasan_kondisi" value="TIDAK_STANDARD"> TIDAK STANDARD
                </label>
            </div>
        </div>
    </div>

    <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
        <div style="font-weight: 800; font-size: 0.825rem; color: #0f172a; margin-bottom: 0.5rem;">
            ⚠️ KONDISI KEMASAN (Centang jika ditemukan cacat):
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 0.75rem; font-size: 0.85rem;">
            <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                <input type="checkbox" name="karton_kemasan_kotor" value="1"> KOTOR
            </label>
            <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                <input type="checkbox" name="karton_kemasan_apek" value="1"> APEK
            </label>
            <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                <input type="checkbox" name="karton_kemasan_basah" value="1"> BASAH
            </label>
            <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer; color: #b45309; font-weight: 700;">
                <input type="checkbox" name="karton_kemasan_jamur" value="1"> JAMUR
            </label>
            <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer; color: #dc2626; font-weight: 700;">
                <input type="checkbox" name="karton_kemasan_sobek" value="1"> SOBEK
            </label>
            <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                <input type="checkbox" name="karton_kemasan_berminyak" value="1"> BERMINYAK
            </label>
            <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                <input type="checkbox" name="karton_kemasan_berdebu" value="1"> BERDEBU
            </label>
        </div>
    </div>

    <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
        <div style="font-weight: 800; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.75rem;">
            🔬 ANALISA PARAMETER DIMENSI &amp; SPESIFIKASI KARTON
        </div>
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
            <thead>
                <tr style="background: #f1f5f9; border-bottom: 1.5px solid #cbd5e1;">
                    <th style="padding: 0.5rem; text-align: left; width: 140px;">Parameter</th>
                    <th style="padding: 0.5rem; text-align: left;">Hasil Analisa</th>
                    <th style="padding: 0.5rem; text-align: left; width: 220px;">Standard</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 0.5rem; font-weight: 700;">PANJANG</td>
                    <td style="padding: 0.5rem;">
                        <input type="text" name="karton_dimensi_panjang_analisa" class="form-control" placeholder="Contoh: 45 cm" value="{{ old('karton_dimensi_panjang_analisa') }}">
                    </td>
                    <td style="padding: 0.5rem;">
                        <input type="text" name="karton_dimensi_panjang_standar" class="form-control" placeholder="Contoh: 45 cm" value="{{ old('karton_dimensi_panjang_standar') }}">
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 0.5rem; font-weight: 700;">LEBAR</td>
                    <td style="padding: 0.5rem;">
                        <input type="text" name="karton_dimensi_lebar_analisa" class="form-control" placeholder="Contoh: 30 cm" value="{{ old('karton_dimensi_lebar_analisa') }}">
                    </td>
                    <td style="padding: 0.5rem;">
                        <input type="text" name="karton_dimensi_lebar_standar" class="form-control" placeholder="Contoh: 30 cm" value="{{ old('karton_dimensi_lebar_standar') }}">
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 0.5rem; font-weight: 700;">TINGGI</td>
                    <td style="padding: 0.5rem;">
                        <input type="text" name="karton_dimensi_tinggi_analisa" class="form-control" placeholder="Contoh: 25 cm" value="{{ old('karton_dimensi_tinggi_analisa') }}">
                    </td>
                    <td style="padding: 0.5rem;">
                        <input type="text" name="karton_dimensi_tinggi_standar" class="form-control" placeholder="Contoh: 25 cm" value="{{ old('karton_dimensi_tinggi_standar') }}">
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem; font-weight: 700;">SPESIFIKASI</td>
                    <td style="padding: 0.5rem;">
                        <input type="text" name="karton_spesifikasi_analisa" class="form-control" placeholder="Contoh: K125/M125/K125 B/F" value="{{ old('karton_spesifikasi_analisa') }}">
                    </td>
                    <td style="padding: 0.5rem;">
                        <input type="text" name="karton_spesifikasi_standar" class="form-control" placeholder="Contoh: K125/M125/K125 B/F" value="{{ old('karton_spesifikasi_standar') }}">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
        <div style="font-weight: 800; font-size: 0.85rem; color: #0f172a; margin-bottom: 0.5rem;">
            ⚖️ KUANTITAS DITERIMA DI PABRIK
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem;">
            <div>
                <label class="form-label" style="font-weight: 700;">Jumlah Lolos (Pcs / Lembar)</label>
                <input type="number" step="1" min="0" name="karton_qty_gross" id="kartonQtyGross" class="form-control" placeholder="0" value="{{ old('karton_qty_gross') }}">
            </div>
            <div>
                <label class="form-label" style="color: #dc2626; font-weight: 700;">Qty Reject / Rusak (Pcs)</label>
                <input type="number" step="1" min="0" name="karton_qty_reject" id="kartonQtyReject" class="form-control" placeholder="0" value="{{ old('karton_qty_reject', 0) }}">
            </div>
        </div>
    </div>

    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 1rem;">
        <div class="form-group" style="margin-bottom: 0.75rem;">
            <label class="form-label" style="font-weight: 700;">KOMENTAR PEMERIKSAAN :</label>
            <textarea name="karton_komentar" rows="2" class="form-control" placeholder="Komentar hasil uji karton box...">{{ old('karton_komentar') }}</textarea>
        </div>
        <div style="display: align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; border-top: 1px solid #e2e8f0; padding-top: 0.75rem; display: flex;">
            <span style="font-weight: 900; font-size: 0.95rem; color: #0f172a;">KESIMPULAN QC :</span>
            <div style="display: flex; gap: 1.5rem;">
                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 1rem; font-weight: 800; color: #15803d; cursor: pointer;">
                    <input type="radio" name="karton_kesimpulan" value="TERIMA" checked> ✅ TERIMA
                </label>
                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 1rem; font-weight: 800; color: #dc2626; cursor: pointer;">
                    <input type="radio" name="karton_kesimpulan" value="TOLAK"> ❌ TOLAK
                </label>
            </div>
        </div>
    </div>
</div>
