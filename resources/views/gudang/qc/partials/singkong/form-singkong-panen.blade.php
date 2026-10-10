{{-- ========================================================================= --}}
{{-- PARTIAL: FIELD KHUSUS PANEN SINGKONG (LOKASI, UMUR, TANGGAL PANEN)       --}}
{{-- HANYA DITAMPILKAN KETIKA KOMODITAS SINGKONG DIPILIH                      --}}
{{-- ========================================================================= --}}
<div id="singkongHarvestContainer" style="display: {{ old('kategori_barang', $initialKomoditas ?? 'SINGKONG') === 'SINGKONG' ? 'block' : 'none' }}; margin-top: 0.85rem;">
    <div style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 10px; padding: 0.95rem 1rem;">
        <div style="font-size: 0.775rem; font-weight: 800; color: #166534; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.65rem; display: flex; align-items: center; justify-content: space-between;">
            <span style="display: flex; align-items: center; gap: 0.35rem;">
                <span>🌾</span> <span>Data Panen Kebun Singkong</span>
            </span>
            <span style="font-size: 0.675rem; font-weight: 800; color: #15803d; background: #dcfce7; padding: 1px 6px; border-radius: 4px;">
                Asal Bibit &amp; Panen
            </span>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem;">
            <div class="form-group" id="groupLokasiPanen" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700; font-size: 0.825rem; color: #14532d;">📍 Lokasi / Daerah Panen <span style="color:#ef4444;">*</span></label>
                <input type="text" name="lokasi_panen" class="form-control" placeholder="Contoh: Wonosobo / Kebumen / Banjarnegara" value="{{ old('lokasi_panen') }}" style="background: #ffffff;">
            </div>

            <div class="form-group" id="groupUmurSingkong" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700; font-size: 0.825rem; color: #14532d;">🌱 Umur Panen Singkong</label>
                <div style="display: flex; align-items: center; position: relative;">
                    <input type="number" step="0.5" min="1" name="umur_singkong_bln" class="form-control" placeholder="9.0" value="{{ old('umur_singkong_bln', 9.0) }}" style="background: #ffffff; padding-right: 3.5rem;">
                    <span style="position: absolute; right: 0.85rem; font-size: 0.75rem; font-weight: 700; color: #64748b; pointer-events: none;">Bulan</span>
                </div>
            </div>

            <div class="form-group" id="groupTglPanen" style="margin-bottom: 0;">
                <label class="form-label" style="font-weight: 700; font-size: 0.825rem; color: #14532d;">📅 Tanggal Panen</label>
                <input type="date" name="tgl_panen" class="form-control" value="{{ old('tgl_panen', date('Y-m-d', strtotime('-1 day'))) }}" style="background: #ffffff;">
            </div>
        </div>
    </div>
</div>

