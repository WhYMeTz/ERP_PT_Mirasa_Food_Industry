{{-- ════════════════════════════════════════════════════════════════ --}}
{{--  MODAL PENYESUAIAN BIAYA UTILITAS BULANAN (LISTRIK, AIR, CNG)    --}}
{{--  ERP PT Mirasa Food Industry - Modul Produksi & Rekap HPP         --}}
{{-- ════════════════════════════════════════════════════════════════ --}}

@php
    $modalCount = (int) (($report ?? [])['count'] ?? 0);
    $modalTot = (($report ?? [])['totals'] ?? [
        'total_wip_qty' => 0,
        'cng_mmbtu' => 0,
        'listrik_air_telp_nilai' => 0,
        'cng_nilai' => 0,
    ]);
@endphp

<div id="modal-adjust-utilitas" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(15,23,42,0.55); align-items:center; justify-content:center; padding:1rem;">
    <div style="background:#fff; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,0.2); width:100%; max-width:620px; overflow:hidden; display:flex; flex-direction:column; max-height:92vh;">

        {{-- Header Modal --}}
        <div style="background:linear-gradient(135deg, #d97706 0%, #b45309 100%); padding:1rem 1.5rem; display:flex; justify-content:space-between; align-items:center; color:#ffffff;">
            <div>
                <div style="font-weight:800; font-size:1.05rem; display:flex; align-items:center; gap:0.45rem;">
                    <span>⚡ Penyesuaian Biaya Utilitas Bulanan</span>
                </div>
                <div style="font-size:0.8rem; color:#fef3c7; margin-top:2px;">
                    Alokasi tagihan riil Listrik, Air &amp; rekonsiliasi kurs Gas CNG periode <strong>{{ $monthsList[$month] ?? '' }} {{ $year }}</strong>
                </div>
            </div>
            <button type="button" onclick="closeModalAdjustUtilitas()" style="background:none; border:none; color:#fff; cursor:pointer; font-size:1.5rem; line-height:1; padding:0.2rem 0.5rem; border-radius:4px;">&times;</button>
        </div>

        {{-- Body Modal (Scrollable if needed) --}}
        <div style="padding:1.25rem 1.5rem; overflow-y:auto; flex:1;">
            
            {{-- Kartu Ringkasan Data Bulan Aktif --}}
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:0.85rem 1rem; margin-bottom:1.25rem; font-size:0.8rem; color:#334155;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.4rem;">
                    <strong style="color:#0f172a;">📊 Ringkasan Produksi Periode Ini:</strong>
                    <span style="background:#e0f2fe; color:#0369a1; font-weight:700; font-size:0.75rem; padding:0.15rem 0.5rem; border-radius:4px;">
                        {{ $modalCount }} Hari Produksi
                    </span>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem; font-size:0.775rem;">
                    <div>• Total Output WIP: <strong>{{ number_format($modalTot['total_wip_qty'], 2, ',', '.') }} kg</strong></div>
                    <div>• Total CNG Terpakai: <strong>{{ number_format($modalTot['cng_mmbtu'], 3, ',', '.') }} MMBTU</strong></div>
                    <div>• Biaya Listrik/Air Saat Ini: <strong>Rp {{ number_format($modalTot['listrik_air_telp_nilai'], 0, ',', '.') }}</strong></div>
                    <div>• Total Biaya CNG Saat Ini: <strong>Rp {{ number_format($modalTot['cng_nilai'], 0, ',', '.') }}</strong></div>
                </div>
            </div>

            @if($modalCount == 0)
                <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:1rem; text-align:center; color:#991b1b; font-size:0.85rem;">
                    ⚠️ Tidak ada catatan produksi yang tersimpan pada bulan <strong>{{ $monthsList[$month] ?? '' }} {{ $year }}</strong>. Silakan pilih bulan yang memiliki data produksi untuk melakukan penyesuaian.
                </div>
            @else
                <form id="formAdjustUtilitas" action="{{ route('produksi.adjust-utilitas') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tahun" value="{{ $year }}">
                    <input type="hidden" name="bulan" value="{{ $month }}">

                    {{-- BLOK 1: LISTRIK & AIR --}}
                    <div style="border:1px solid #cbd5e1; border-radius:8px; padding:1rem; margin-bottom:1.25rem; background:#ffffff;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                            <label style="display:flex; align-items:center; gap:0.5rem; font-weight:700; font-size:0.875rem; color:#0f172a; cursor:pointer; margin:0;">
                                <input type="checkbox" name="adjust_listrik" id="chk_adjust_listrik" value="1" checked onchange="toggleListrikSection()" style="width:16px; height:16px; accent-color:#d97706;">
                                <span>1. Penyesuaian Listrik, Air &amp; Telepon (Tagihan PLN/PDAM)</span>
                            </label>
                            <span style="font-size:0.725rem; color:#64748b; background:#f1f5f9; padding:0.15rem 0.45rem; border-radius:4px;">Bulanan</span>
                        </div>

                        <div id="section_input_listrik" style="display:flex; flex-direction:column; gap:0.75rem;">
                            <div>
                                <label style="display:block; font-size:0.775rem; font-weight:600; color:#475569; margin-bottom:0.3rem;">
                                    Total Faktur / Rekening Tagihan PLN &amp; Air Bulan Ini (Rp) <span style="color:#ef4444;">*</span>
                                </label>
                                <div style="position:relative;">
                                    <span style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); font-weight:700; color:#64748b; font-size:0.85rem;">Rp</span>
                                    <input type="number" step="0.01" min="0" name="total_listrik_air" id="total_listrik_air" 
                                        class="form-control" 
                                        value="{{ old('total_listrik_air', $modalTot['listrik_air_telp_nilai']) }}" 
                                        placeholder="0" 
                                        style="padding-left:2.5rem; font-weight:800; font-size:0.95rem; color:#0f172a;" 
                                        oninput="calcListrikPreview()">
                                </div>
                                <small id="preview_alokasi_listrik" style="display:block; color:#0284c7; font-size:0.725rem; margin-top:0.35rem; font-weight:600;">
                                    Perkiraan alokasi: Rp {{ $modalCount > 0 ? number_format($modalTot['listrik_air_telp_nilai'] / $modalCount, 0, ',', '.') : 0 }} / hari
                                </small>
                            </div>

                            <div>
                                <label style="display:block; font-size:0.775rem; font-weight:600; color:#475569; margin-bottom:0.3rem;">
                                    Metode Pembagian / Alokasi ke Hari Produksi:
                                </label>
                                <div style="display:flex; gap:1.25rem; font-size:0.8rem; color:#334155;">
                                    <label style="display:inline-flex; align-items:center; gap:0.35rem; cursor:pointer;">
                                        <input type="radio" name="mode_alokasi_listrik" value="bagi_rata" checked onchange="calcListrikPreview()">
                                        <span><strong>Bagi Rata</strong> (Rata per hari kerja)</span>
                                    </label>
                                    <label style="display:inline-flex; align-items:center; gap:0.35rem; cursor:pointer;">
                                        <input type="radio" name="mode_alokasi_listrik" value="proporsional_wip" onchange="calcListrikPreview()">
                                        <span><strong>Proporsional Output</strong> (Sesuai kg WIP)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- BLOK 2: GAS ALAM (CNG) --}}
                    <div style="border:1px solid #cbd5e1; border-radius:8px; padding:1rem; margin-bottom:1.25rem; background:#ffffff;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                            <label style="display:flex; align-items:center; gap:0.5rem; font-weight:700; font-size:0.875rem; color:#0f172a; cursor:pointer; margin:0;">
                                <input type="checkbox" name="adjust_cng" id="chk_adjust_cng" value="1" onchange="toggleCngSection()" style="width:16px; height:16px; accent-color:#d97706;">
                                <span>2. Penyesuaian Gas Alam / CNG (Fluktuasi Kurs USD &amp; Tagihan)</span>
                            </label>
                            <span style="font-size:0.725rem; color:#64748b; background:#f1f5f9; padding:0.15rem 0.45rem; border-radius:4px;">Boiler &amp; Fryer</span>
                        </div>

                        <div id="section_input_cng" style="display:none; flex-direction:column; gap:0.75rem;">
                            <div style="display:flex; gap:1.25rem; font-size:0.8rem; color:#334155; margin-bottom:0.25rem;">
                                <label style="display:inline-flex; align-items:center; gap:0.35rem; cursor:pointer;">
                                    <input type="radio" name="mode_cng" value="update_tarif" checked onchange="toggleCngMode()">
                                    <span><strong>Input Tarif Baru</strong> (Rp / MMBTU)</span>
                                </label>
                                <label style="display:inline-flex; align-items:center; gap:0.35rem; cursor:pointer;">
                                    <input type="radio" name="mode_cng" value="total_tagihan" onchange="toggleCngMode()">
                                    <span><strong>Input Total Tagihan Akhir</strong> (Rp)</span>
                                </label>
                            </div>

                            {{-- Pilihan 1: Tarif Baru --}}
                            <div id="cng_mode_tarif_box">
                                <label style="display:block; font-size:0.775rem; font-weight:600; color:#475569; margin-bottom:0.3rem;">
                                    Tarif Final Gas CNG Hasil Penyesuaian Kurs (Rp / MMBTU)
                                </label>
                                <div style="position:relative;">
                                    <span style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); font-weight:700; color:#64748b; font-size:0.85rem;">Rp</span>
                                    <input type="number" step="0.01" min="0" name="cng_tarif_baru" id="cng_tarif_baru" 
                                        class="form-control" 
                                        value="{{ old('cng_tarif_baru', 232500.00) }}" 
                                        placeholder="232500.00" 
                                        style="padding-left:2.5rem; font-weight:800; font-size:0.95rem; color:#0f172a;" 
                                        oninput="calcCngPreview()">
                                </div>
                                <small style="display:block; color:#64748b; font-size:0.725rem; margin-top:0.35rem;">
                                    Seluruh pemakaian MMBTU harian bulan ini akan dikalikan dengan tarif ini.
                                </small>
                            </div>

                            {{-- Pilihan 2: Total Tagihan --}}
                            <div id="cng_mode_total_box" style="display:none;">
                                <label style="display:block; font-size:0.775rem; font-weight:600; color:#475569; margin-bottom:0.3rem;">
                                    Total Faktur Tagihan CNG Bulan Ini (Rp)
                                </label>
                                <div style="position:relative;">
                                    <span style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); font-weight:700; color:#64748b; font-size:0.85rem;">Rp</span>
                                    <input type="number" step="0.01" min="0" name="total_cng_tagihan" id="total_cng_tagihan" 
                                        class="form-control" 
                                        value="{{ old('total_cng_tagihan', $modalTot['cng_nilai']) }}" 
                                        placeholder="0" 
                                        style="padding-left:2.5rem; font-weight:800; font-size:0.95rem; color:#0f172a;" 
                                        oninput="calcCngPreview()">
                                </div>
                                <small id="preview_cng_effective" style="display:block; color:#0284c7; font-size:0.725rem; margin-top:0.35rem; font-weight:600;">
                                    Tarif riil efektif = Rp {{ ($modalTot['cng_mmbtu'] ?? 0) > 0 ? number_format(($modalTot['cng_nilai'] ?? 0) / $modalTot['cng_mmbtu'], 2, ',', '.') : 0 }} / MMBTU
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Informasi Dampak Otomatis --}}
                    <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:0.75rem 1rem; margin-bottom:1.25rem; font-size:0.775rem; color:#166534; display:flex; gap:0.5rem; align-items:flex-start;">
                        <span style="font-size:1rem; line-height:1;">💡</span>
                        <div>
                            <strong>Otomasi Perhitungan HPP:</strong> Saat disimpan, sistem akan secara otomatis menghitung ulang Biaya Overhead (FOH), Total Biaya Produksi, dan Harga Pokok Produksi (HPP/Kg) pada seluruh hari kerja di bulan <strong>{{ $monthsList[$month] ?? '' }} {{ $year }}</strong>.
                        </div>
                    </div>

                    {{-- Footer Modal Action Buttons --}}
                    <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
                        <button type="button" onclick="closeModalAdjustUtilitas()" style="background:#f1f5f9; border:1px solid #cbd5e1; color:#475569; padding:0.6rem 1.1rem; border-radius:6px; font-weight:600; font-size:0.85rem; cursor:pointer;">
                            Batal
                        </button>
                        <button type="submit" id="btnSubmitAdjustUtilitas" style="background:#d97706; border:none; color:#ffffff; padding:0.6rem 1.25rem; border-radius:6px; font-weight:700; font-size:0.875rem; cursor:pointer;">
                            <span>Simpan &amp; Terapkan Penyesuaian</span>
                        </button>
                    </div>
                </form>
            @endif

        </div>
    </div>
</div>

<script>
    const totalHariProduksi = {{ $modalCount }};
    const totalWipQty = {{ (float) ($modalTot['total_wip_qty'] ?? 0) }};
    const totalMmbtu = {{ (float) ($modalTot['cng_mmbtu'] ?? 0) }};

    function openModalAdjustUtilitas() {
        const modal = document.getElementById('modal-adjust-utilitas');
        if (modal) {
            modal.style.display = 'flex';
            calcListrikPreview();
            calcCngPreview();
        }
    }

    function closeModalAdjustUtilitas() {
        const modal = document.getElementById('modal-adjust-utilitas');
        if (modal) modal.style.display = 'none';
    }

    function toggleListrikSection() {
        const chk = document.getElementById('chk_adjust_listrik');
        const sec = document.getElementById('section_input_listrik');
        if (sec && chk) {
            sec.style.display = chk.checked ? 'flex' : 'none';
        }
    }

    function toggleCngSection() {
        const chk = document.getElementById('chk_adjust_cng');
        const sec = document.getElementById('section_input_cng');
        if (sec && chk) {
            sec.style.display = chk.checked ? 'flex' : 'none';
        }
    }

    function toggleCngMode() {
        const mode = document.querySelector('input[name="mode_cng"]:checked')?.value || 'update_tarif';
        const boxTarif = document.getElementById('cng_mode_tarif_box');
        const boxTotal = document.getElementById('cng_mode_total_box');
        if (boxTarif && boxTotal) {
            if (mode === 'update_tarif') {
                boxTarif.style.display = 'block';
                boxTotal.style.display = 'none';
            } else {
                boxTarif.style.display = 'none';
                boxTotal.style.display = 'block';
            }
        }
        calcCngPreview();
    }

    function calcListrikPreview() {
        const input = document.getElementById('total_listrik_air');
        const preview = document.getElementById('preview_alokasi_listrik');
        const mode = document.querySelector('input[name="mode_alokasi_listrik"]:checked')?.value || 'bagi_rata';
        if (!input || !preview) return;

        const val = parseFloat(input.value || 0);
        if (totalHariProduksi > 0) {
            if (mode === 'bagi_rata') {
                const perDay = val / totalHariProduksi;
                preview.innerHTML = `Perkiraan alokasi: <strong>Rp ${Math.round(perDay).toLocaleString('id-ID')}</strong> / hari (${totalHariProduksi} hari produksi).`;
            } else {
                preview.innerHTML = `Dialokasikan proporsional ke <strong>${totalHariProduksi} hari</strong> berdasarkan output WIP (${totalWipQty.toLocaleString('id-ID')} kg).`;
            }
        }
    }

    function calcCngPreview() {
        const mode = document.querySelector('input[name="mode_cng"]:checked')?.value || 'update_tarif';
        const preview = document.getElementById('preview_cng_effective');
        if (mode === 'total_tagihan' && preview) {
            const input = document.getElementById('total_cng_tagihan');
            const val = parseFloat(input?.value || 0);
            if (totalMmbtu > 0) {
                const effective = val / totalMmbtu;
                preview.innerHTML = `Tarif efektif: <strong>Rp ${effective.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong> / MMBTU (dari akumulasi ${totalMmbtu.toLocaleString('id-ID')} MMBTU).`;
            }
        }
    }
</script>
