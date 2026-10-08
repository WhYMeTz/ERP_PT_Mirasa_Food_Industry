{{-- ========================================================================= --}}
{{-- PARTIAL: FIELD KHUSUS PANEN SINGKONG (LOKASI, UMUR, TANGGAL PANEN)       --}}
{{-- HANYA DITAMPILKAN KETIKA KOMODITAS SINGKONG DIPILIH                      --}}
{{-- ========================================================================= --}}
<div id="singkongHarvestContainer" style="display: {{ old('kategori_barang', $initialKomoditas ?? 'SINGKONG') === 'SINGKONG' ? 'block' : 'none' }}; margin-top: 0.85rem;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <div class="form-group" id="groupLokasiPanen" style="margin-bottom: 0;">
            <label class="form-label" style="font-weight: 700;">Lokasi Panen</label>
            <input type="text" name="lokasi_panen" class="form-control" placeholder="Contoh: Wonosobo / Kebumen" value="{{ old('lokasi_panen') }}">
        </div>

        <div class="form-group" id="groupUmurSingkong" style="margin-bottom: 0;">
            <label class="form-label">Umur Singkong (Bulan)</label>
            <input type="number" step="0.5" name="umur_singkong_bln" class="form-control" placeholder="Contoh: 9.0" value="{{ old('umur_singkong_bln', 9.0) }}">
        </div>

        <div class="form-group" id="groupTglPanen" style="margin-bottom: 0;">
            <label class="form-label">Tanggal Panen</label>
            <input type="date" name="tgl_panen" class="form-control" value="{{ old('tgl_panen', date('Y-m-d', strtotime('-1 day'))) }}">
        </div>
    </div>
</div>
