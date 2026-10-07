{{-- ========================================================================= --}}
{{-- EDIT FORMULIR QC SINGKONG (BY INPUTAN BERSIH & MODERN)                   --}}
{{-- ========================================================================= --}}
@php
    $firstDetail = $qc->details->first();
    $qcdtlId = $firstDetail?->qcdtl_id ?? 0;
    $barangId = $firstDetail?->barang_id ?? ($barangs->firstWhere('barang_nm', 'like', '%Singkong%')?->barang_id ?? $barangs->first()?->barang_id);
    $isPengujian2 = ($qc->tahap_uji ?? 'PENGUJIAN_1') === 'PENGUJIAN_2';
@endphp

<div style="display: flex; flex-direction: column; gap: 1.25rem;">
    {{-- SUB-CARD 1: IDENTITAS PANEN & SPESIFIKASI SINGKONG --}}
    <div style="background: {{ $isPengujian2 ? '#faf5ff' : '#f8fafc' }}; border: 1px solid {{ $isPengujian2 ? '#e9d5ff' : '#e2e8f0' }}; border-radius: 8px; padding: 1.15rem;">
        <div style="font-size: 0.85rem; font-weight: 800; color: {{ $isPengujian2 ? '#6b21a8' : '#0f172a' }}; margin-bottom: 0.85rem; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.45rem;">
                <span>{{ $isPengujian2 ? '🍟' : '🥔' }}</span>
                <span>{{ $isPengujian2 ? 'Spesifikasi Singkong & Sampling Pengujian II (Lanjutan)' : 'Spesifikasi Komoditas & Identitas Panen' }}</span>
            </div>
            @if ($isPengujian2)
                <span class="badge" style="background: #9333ea; color: #ffffff; font-weight: 800; font-size: 0.72rem;">
                    Sisa Setengah Bak Tuntas
                </span>
            @endif
        </div>

        <div class="qc-grid-2" style="margin-bottom: 0.85rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Master Bahan Baku Terkait <span style="color:#ef4444;">*</span></label>
                <select name="items[{{ $qcdtlId }}][barang_id]" class="form-control" required style="font-weight: 700;">
                    @foreach ($barangs as $b)
                        <option value="{{ $b->barang_id }}" {{ old("items.{$qcdtlId}.barang_id", $barangId) == $b->barang_id ? 'selected' : '' }}>
                            {{ $b->barang_nm }} ({{ $b->barang_cd }}) - {{ $b->satuanDasar?->satuan_cd ?? 'KG' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Nama Jenis / Deskripsi Kedatangan</label>
                <input type="text" name="nama_jenis" class="form-control" value="{{ old('nama_jenis', $qc->nama_jenis) }}" placeholder="Contoh: SINGKONG KUPAS / SINGKONG KULIT" style="font-weight: 600;">
            </div>
        </div>

        <div class="qc-grid-4">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Lokasi Asal Panen</label>
                <input type="text" name="lokasi_panen" class="form-control" value="{{ old('lokasi_panen', $qc->lokasi_panen) }}" placeholder="Contoh: Wonosobo / Kebumen">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Umur Singkong (Bulan)</label>
                <input type="number" step="0.1" min="0" name="umur_singkong_bln" class="form-control" value="{{ old('umur_singkong_bln', $qc->umur_singkong_bln ?? 9.0) }}">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Tanggal Panen</label>
                <input type="date" name="tgl_panen" class="form-control" value="{{ old('tgl_panen', $qc->tgl_panen ? $qc->tgl_panen->format('Y-m-d') : '') }}">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Berat Sampel Uji (kg) <span style="color:#ef4444;">*</span></label>
                <input type="number" step="0.1" min="0.1" name="jumlah_sample_kg" class="form-control" value="{{ old('jumlah_sample_kg', $qc->jumlah_sample_kg ?? 7.0) }}" required style="font-weight: 700; color: #0284c7;">
                <small style="color: #64748b; font-size: 0.72rem;">Sampel gabungan merata (depan, tengah, belakang ~7 kg).</small>
            </div>
        </div>
    </div>

    {{-- SUB-CARD 2: KONDISI FISIK KEDATANGAN --}}
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.15rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
            <div style="font-size: 0.85rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 0.45rem;">
                <span>{{ $isPengujian2 ? '🍟' : '🔍' }}</span>
                <span>{{ $isPengujian2 ? 'Pemeriksaan Kondisi Fisik Lapisan Dalam Bak (Sisa Muatan Truk)' : 'Pemeriksaan Kondisi Fisik Singkong' }}</span>
            </div>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <span style="font-size: 0.8rem; font-weight: 700; color: #475569;">Status Raw Material:</span>
                <label style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.825rem; font-weight: 700; cursor: pointer;">
                    <input type="radio" name="items[{{ $qcdtlId }}][status_raw_material]" value="OK" {{ old("items.{$qcdtlId}.status_raw_material", $firstDetail?->status_raw_material ?? 'OK') === 'OK' ? 'checked' : '' }}>
                    <span style="color: #15803d;">OK (Standar)</span>
                </label>
                <label style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.825rem; font-weight: 700; cursor: pointer;">
                    <input type="radio" name="items[{{ $qcdtlId }}][status_raw_material]" value="TDK_STD" {{ old("items.{$qcdtlId}.status_raw_material", $firstDetail?->status_raw_material ?? '') === 'TDK_STD' ? 'checked' : '' }}>
                    <span style="color: #dc2626;">TDK STD (Menyimpang)</span>
                </label>
            </div>
        </div>

        <div style="font-size: 0.775rem; color: #64748b; margin-bottom: 0.65rem;">
            Centang kondisi fisik yang teramati saat {{ $isPengujian2 ? 'pembongkaran sisa muatan bak' : 'pembongkaran muatan truk' }}:
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.5rem;">
            <label style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem 0.65rem; display: flex; align-items: center; gap: 0.45rem; cursor: pointer; background: #f8fafc; font-size: 0.825rem;">
                <input type="checkbox" name="items[{{ $qcdtlId }}][kondisi_segar]" value="1" {{ old("items.{$qcdtlId}.kondisi_segar", $firstDetail?->kondisi_segar ?? 1) ? 'checked' : '' }}>
                <span style="font-weight: 600; color: #166534;">🌿 Segar</span>
            </label>

            <label style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem 0.65rem; display: flex; align-items: center; gap: 0.45rem; cursor: pointer; background: #f8fafc; font-size: 0.825rem;">
                <input type="checkbox" name="items[{{ $qcdtlId }}][kondisi_layu]" value="1" {{ old("items.{$qcdtlId}.kondisi_layu", $firstDetail?->kondisi_layu) ? 'checked' : '' }}>
                <span style="font-weight: 600; color: #b45309;">🍂 Layu</span>
            </label>

            <label style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem 0.65rem; display: flex; align-items: center; gap: 0.45rem; cursor: pointer; background: #f8fafc; font-size: 0.825rem;">
                <input type="checkbox" name="items[{{ $qcdtlId }}][kondisi_basah]" value="1" {{ old("items.{$qcdtlId}.kondisi_basah", $firstDetail?->kondisi_basah) ? 'checked' : '' }}>
                <span style="font-weight: 600; color: #0369a1;">💧 Basah</span>
            </label>

            <label style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem 0.65rem; display: flex; align-items: center; gap: 0.45rem; cursor: pointer; background: #f8fafc; font-size: 0.825rem;">
                <input type="checkbox" name="items[{{ $qcdtlId }}][kondisi_terkelupas]" value="1" {{ old("items.{$qcdtlId}.kondisi_terkelupas", $firstDetail?->kondisi_terkelupas) ? 'checked' : '' }}>
                <span style="font-weight: 600; color: #475569;">🪵 Terkelupas</span>
            </label>

            <label style="border: 1px solid #fecaca; border-radius: 6px; padding: 0.5rem 0.65rem; display: flex; align-items: center; gap: 0.45rem; cursor: pointer; background: #fef2f2; font-size: 0.825rem;">
                <input type="checkbox" name="items[{{ $qcdtlId }}][kondisi_busuk]" value="1" {{ old("items.{$qcdtlId}.kondisi_busuk", $firstDetail?->kondisi_busuk) ? 'checked' : '' }}>
                <span style="font-weight: 700; color: #dc2626;">🟤 Busuk</span>
            </label>

            <label style="border: 1px solid #fecaca; border-radius: 6px; padding: 0.5rem 0.65rem; display: flex; align-items: center; gap: 0.45rem; cursor: pointer; background: #fef2f2; font-size: 0.825rem;">
                <input type="checkbox" name="items[{{ $qcdtlId }}][kondisi_berjamur]" value="1" {{ old("items.{$qcdtlId}.kondisi_berjamur", $firstDetail?->kondisi_berjamur) ? 'checked' : '' }}>
                <span style="font-weight: 700; color: #dc2626;">🍄 Berjamur</span>
            </label>

            <label style="border: 1px solid #fecaca; border-radius: 6px; padding: 0.5rem 0.65rem; display: flex; align-items: center; gap: 0.45rem; cursor: pointer; background: #fef2f2; font-size: 0.825rem;">
                <input type="checkbox" name="items[{{ $qcdtlId }}][kondisi_lembek]" value="1" {{ old("items.{$qcdtlId}.kondisi_lembek", $firstDetail?->kondisi_lembek) ? 'checked' : '' }}>
                <span style="font-weight: 700; color: #dc2626;">⚠️ Lembek</span>
            </label>
        </div>
    </div>

    {{-- SUB-CARD 3: PROPORSI DIAMETER & DEFECT SORTIR --}}
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.15rem;">
        <div style="font-size: 0.85rem; font-weight: 800; color: #0f172a; margin-bottom: 0.85rem; display: flex; align-items: center; gap: 0.45rem;">
            <span>📏</span>
            <span>Proporsi Ukuran Diameter &amp; Defect Sortir Sampel</span>
        </div>

        <div class="qc-grid-3" style="margin-bottom: 0.85rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Diameter &ge; 4 cm (% Standar)</label>
                <input type="number" step="0.1" min="0" max="100" name="items[{{ $qcdtlId }}][diameter_lebih_4cm_persen]" class="form-control" value="{{ old("items.{$qcdtlId}.diameter_lebih_4cm_persen", $firstDetail?->diameter_lebih_4cm_persen ?? 100) }}" style="font-weight: 700; color: #15803d;">
                <span style="font-size: 0.72rem; color: #64748b; margin-top: 2px; display: block;">Persentase ukuran standar pabrik.</span>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Diameter &lt; 4 cm (% Kecil)</label>
                <input type="number" step="0.1" min="0" max="100" name="items[{{ $qcdtlId }}][diameter_kurang_4cm_persen]" class="form-control" value="{{ old("items.{$qcdtlId}.diameter_kurang_4cm_persen", $firstDetail?->diameter_kurang_4cm_persen ?? 0) }}" style="font-weight: 700; color: #b45309;">
                <span style="font-size: 0.72rem; color: #64748b; margin-top: 2px; display: block;">Persentase ukuran kecil/afkir.</span>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Defect Gambos / Gabuk (%)</label>
                <input type="number" step="0.1" min="0" max="100" name="items[{{ $qcdtlId }}][defect_gambos_persen]" class="form-control" value="{{ old("items.{$qcdtlId}.defect_gambos_persen", $firstDetail?->defect_gambos_persen ?? 0) }}" style="font-weight: 700; color: #dc2626;">
                <span style="font-size: 0.72rem; color: #64748b; margin-top: 2px; display: block;">Defect singkong gabuk/berongga.</span>
            </div>
        </div>

        <div class="qc-grid-2">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Defect Patah / Breakage (%)</label>
                <input type="number" step="0.1" min="0" max="100" name="items[{{ $qcdtlId }}][defect_breakage_persen]" class="form-control" value="{{ old("items.{$qcdtlId}.defect_breakage_persen", $firstDetail?->defect_breakage_persen ?? 0) }}" placeholder="0.0">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Defect Kulit / Cluster (%)</label>
                <input type="number" step="0.1" min="0" max="100" name="items[{{ $qcdtlId }}][defect_cluster_persen]" class="form-control" value="{{ old("items.{$qcdtlId}.defect_cluster_persen", $firstDetail?->defect_cluster_persen ?? 0) }}" placeholder="0.0">
            </div>
        </div>
    </div>

    {{-- SUB-CARD 4: HASIL UJI PENGGORENGAN (FRYER TEST) & PENETAPAN GRADE --}}
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.15rem;">
        <div style="font-size: 0.85rem; font-weight: 800; color: #0f172a; margin-bottom: 0.85rem; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.45rem;">
                <span>🍟</span>
                <span>{{ $isPengujian2 ? 'Uji Penggorengan Lab (Fryer Test) & Verifikasi Lolos Produksi' : 'Uji Penggorengan Lab (Fryer Test) & Penetapan Grade Mutu' }}</span>
            </div>
            <span class="badge" style="background: {{ $isPengujian2 ? '#f3e8ff' : '#fef3c7' }}; color: {{ $isPengujian2 ? '#7e22ce' : '#b45309' }}; font-weight: 800; font-size: 0.725rem;">
                {{ $isPengujian2 ? 'Kriteria Kritis Produksi' : 'Kriteria Utama Penerimaan' }}
            </span>
        </div>

        @if ($isPengujian2)
            <div style="background: #faf5ff; border: 1px solid #d8b4fe; border-radius: 6px; padding: 0.65rem 0.85rem; margin-bottom: 0.85rem; font-size: 0.78rem; color: #6b21a8; line-height: 1.45;">
                <strong>⚡ Titik Kritis Pengujian II:</strong> Pengujian ini memverifikasi rasa gurih dan kerenyahan singkong setelah digoreng pada wajan lab. Jika rasa <strong>PAHIT</strong> terdeteksi, muatan sisa ini otomatis berstatus <strong>REJECT / TOLAK TOTAL</strong> untuk mencegah risiko kontaminasi pada seluruh minyak wajan produksi.
            </div>
        @endif

        <div class="qc-grid-4" style="margin-bottom: 0.85rem;">
            {{-- RASA FRYER --}}
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Uji Rasa Setelah Digoreng <span style="color:#ef4444;">*</span></label>
                <select name="items[{{ $qcdtlId }}][fryer_rasa]" class="form-control" required style="font-weight: 800;">
                    <option value="TIDAK_PAHIT" {{ old("items.{$qcdtlId}.fryer_rasa", $firstDetail?->fryer_rasa ?? 'TIDAK_PAHIT') === 'TIDAK_PAHIT' ? 'selected' : '' }} style="color: #15803d;">
                        😋 TIDAK PAHIT (Gurih / Normal)
                    </option>
                    <option value="PAHIT" {{ old("items.{$qcdtlId}.fryer_rasa", $firstDetail?->fryer_rasa) === 'PAHIT' ? 'selected' : '' }} style="color: #dc2626;">
                        🤢 PAHIT (Singkong Racun / Afkir)
                    </option>
                </select>
            </div>

            {{-- TEKSTUR FRYER --}}
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Tekstur Singkong Goreng <span style="color:#ef4444;">*</span></label>
                <select name="items[{{ $qcdtlId }}][fryer_tekstur]" class="form-control" required style="font-weight: 700;">
                    <option value="RENYAH" {{ old("items.{$qcdtlId}.fryer_tekstur", $firstDetail?->fryer_tekstur ?? 'RENYAH') === 'RENYAH' ? 'selected' : '' }}>
                        ✨ RENYAH (Super)
                    </option>
                    <option value="EMPUK" {{ old("items.{$qcdtlId}.fryer_tekstur", $firstDetail?->fryer_tekstur) === 'EMPUK' ? 'selected' : '' }}>
                        🥟 EMPUK (Standar)
                    </option>
                    <option value="KERAS" {{ old("items.{$qcdtlId}.fryer_tekstur", $firstDetail?->fryer_tekstur) === 'KERAS' ? 'selected' : '' }}>
                        🧱 KERAS (Keras / Bantat)
                    </option>
                </select>
            </div>

            {{-- PENAMPAKAN MINYAK --}}
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Kondisi Minyak Goreng</label>
                <select name="items[{{ $qcdtlId }}][fryer_penampakan]" class="form-control" style="font-weight: 600;">
                    <option value="TIDAK_OILSOAKED" {{ old("items.{$qcdtlId}.fryer_penampakan", $firstDetail?->fryer_penampakan ?? 'TIDAK_OILSOAKED') === 'TIDAK_OILSOAKED' ? 'selected' : '' }}>
                        ☀️ Kering / Tidak Berminyak
                    </option>
                    <option value="OILSOAKED" {{ old("items.{$qcdtlId}.fryer_penampakan", $firstDetail?->fryer_penampakan) === 'OILSOAKED' ? 'selected' : '' }}>
                        💧 Berminyak (Oilsoaked)
                    </option>
                </select>
            </div>

            {{-- PENETAPAN GRADE MUTU --}}
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Penetapan Grade Mutu <span style="color:#ef4444;">*</span></label>
                <select name="items[{{ $qcdtlId }}][grade_cd]" class="form-control" required style="font-weight: 800; font-size: 0.95rem;">
                    <option value="A" {{ old("items.{$qcdtlId}.grade_cd", $firstDetail?->grade_cd ?? 'A') === 'A' ? 'selected' : '' }} style="color: #047857;">
                        🟢 Grade A (Super)
                    </option>
                    <option value="B" {{ old("items.{$qcdtlId}.grade_cd", $firstDetail?->grade_cd) === 'B' ? 'selected' : '' }} style="color: #b45309;">
                        🟡 Grade B (Standar)
                    </option>
                    <option value="C" {{ old("items.{$qcdtlId}.grade_cd", $firstDetail?->grade_cd) === 'C' ? 'selected' : '' }} style="color: #64748b;">
                        ⚪ Grade C (Campur)
                    </option>
                    <option value="REJECT" {{ old("items.{$qcdtlId}.grade_cd", $firstDetail?->grade_cd) === 'REJECT' ? 'selected' : '' }} style="color: #dc2626;">
                        🔴 REJECT (Afkir / Tolak)
                    </option>
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Catatan Hasil Uji Mutu / Spesifikasi Lapangan</label>
            <input type="text" name="items[{{ $qcdtlId }}][catatan_mutu]" value="{{ old("items.{$qcdtlId}.catatan_mutu", $firstDetail?->catatan_mutu ?? $firstDetail?->catatan_dtl) }}" class="form-control" placeholder="Contoh: Singkong panen segar kadar air normal, rasa gurih renyah, warna kuning mentega...">
        </div>
    </div>
</div>
