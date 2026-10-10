{{-- ════════════════════════════════════════════════════════════════ --}}
{{--  MODAL PENYESUAIAN BIAYA UTILITAS (LISTRIK, AIR & GAS CNG)        --}}
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

    // Seluruh hari kerja produksi aktif di bulan ini
    $prodDays = collect($report['days'] ?? [])
        ->filter(fn($d) => !empty($d['has_data']));
@endphp

<div id="modal-adjust-utilitas" 
     class="modal-overlay" 
     data-hari="{{ $modalCount }}" 
     data-wip="{{ (float) ($modalTot['total_wip_qty'] ?? 0) }}" 
     data-mmbtu="{{ (float) ($modalTot['cng_mmbtu'] ?? 0) }}" 
     onclick="if(event.target === this) closeModalAdjustUtilitas()">
    <div class="modal-utilitas-dialog">

        {{-- Header Modal --}}
        <div class="modal-utilitas-header">
            <div>
                <h3 class="modal-utilitas-title">Penyesuaian Biaya Utilitas Produksi</h3>
                <div class="modal-utilitas-subtitle">
                    Periode: <strong>{{ $monthsList[$month] ?? '' }} {{ $year }}</strong> &bull; {{ $modalCount }} Hari Kerja Produksi
                </div>
            </div>
            <button type="button" class="modal-utilitas-close" onclick="closeModalAdjustUtilitas()" title="Tutup">&times;</button>
        </div>

        {{-- Body Modal --}}
        <div class="modal-utilitas-body">
            @if($modalCount == 0)
                <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:1.25rem; text-align:center; color:#991b1b; font-size:0.85rem;">
                    Tidak ada catatan produksi pada periode <strong>{{ $monthsList[$month] ?? '' }} {{ $year }}</strong>.
                </div>
            @else
                <form id="formAdjustUtilitas" action="{{ route('produksi.adjust-utilitas.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tahun" value="{{ $year }}">
                    <input type="hidden" name="bulan" value="{{ $month }}">

                    {{-- KARTU 1: LISTRIK & AIR --}}
                    <div class="utilitas-card">
                        <div class="utilitas-card-header">
                            <label class="utilitas-card-title" for="chk_adjust_listrik" style="cursor:pointer; margin:0; display:flex; align-items:center; gap:0.5rem;">
                                <input type="checkbox" name="adjust_listrik" id="chk_adjust_listrik" value="1" checked onchange="toggleListrikSection()" style="width:16px; height:16px; accent-color:#0284c7; cursor:pointer;">
                                <span>1. Beban Listrik &amp; Air (PLN / PDAM)</span>
                            </label>
                            <span class="utilitas-badge-pill">Otomatis Tiap Hari</span>
                        </div>

                        <div id="section_input_listrik">
                            <div class="utilitas-radio-strip">
                                <label class="utilitas-radio-lbl">
                                    <input type="radio" name="mode_alokasi_listrik" value="tarif_per_kg" checked onchange="toggleListrikMode()">
                                    <span>Tarif per Kg WIP (Standar Excel)</span>
                                </label>
                                <label class="utilitas-radio-lbl">
                                    <input type="radio" name="mode_alokasi_listrik" value="total_tagihan" onchange="toggleListrikMode()">
                                    <span>Total Tagihan PLN Sebulan</span>
                                </label>
                            </div>

                            {{-- Input 1: Tarif Standar per Kg --}}
                            <div id="listrik_box_tarif">
                                <div class="input-addon-group">
                                    <span class="addon-prefix">Rp</span>
                                    <input type="number" step="0.01" min="0" name="listrik_tarif_per_kg" id="listrik_tarif_per_kg" 
                                        value="{{ old('listrik_tarif_per_kg', 223.80) }}" 
                                        class="addon-input" 
                                        oninput="updateLivePreview()">
                                    <span class="addon-prefix">/ Kg WIP</span>
                                </div>
                                <div style="font-size:0.725rem; color:#64748b; margin-top:0.35rem;">
                                    Tiap hari otomatis dihitung: <code>Kg Keripik WIP &times; Rp 223,80</code>.
                                </div>
                            </div>

                            {{-- Input 2: Total Tagihan Rekening PLN --}}
                            <div id="listrik_box_total" style="display:none;">
                                <div class="input-addon-group">
                                    <span class="addon-prefix">Rp</span>
                                    <input type="number" step="0.01" min="0" name="total_listrik_air" id="total_listrik_air" 
                                        value="{{ old('total_listrik_air', $modalTot['listrik_air_telp_nilai']) }}" 
                                        class="addon-input" 
                                        oninput="updateLivePreview()">
                                </div>
                                <div style="font-size:0.725rem; color:#64748b; margin-top:0.35rem;">
                                    Total rekening PLN akan dibagi proporsional ke tiap hari kerja sesuai tonase keripik.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- KARTU 2: GAS ALAM (CNG) --}}
                    <div class="utilitas-card">
                        <div class="utilitas-card-header">
                            <label class="utilitas-card-title" for="chk_adjust_cng" style="cursor:pointer; margin:0; display:flex; align-items:center; gap:0.5rem;">
                                <input type="checkbox" name="adjust_cng" id="chk_adjust_cng" value="1" onchange="toggleCngSection()" style="width:16px; height:16px; accent-color:#d97706; cursor:pointer;">
                                <span>2. Beban Gas Alam / CNG (Boiler &amp; Fryer)</span>
                            </label>
                            <span class="utilitas-badge-pill">Otomatis Tiap Hari</span>
                        </div>

                        <div id="section_input_cng" style="display:none;">
                            <div class="utilitas-radio-strip">
                                <label class="utilitas-radio-lbl">
                                    <input type="radio" name="mode_cng" value="update_tarif" checked onchange="toggleCngMode()">
                                    <span>Tarif Gas Resmi (Rp / MMBTU)</span>
                                </label>
                                <label class="utilitas-radio-lbl">
                                    <input type="radio" name="mode_cng" value="total_tagihan" onchange="toggleCngMode()">
                                    <span>Total Tagihan Faktur Gas</span>
                                </label>
                            </div>

                            {{-- Input 1: Tarif Gas --}}
                            <div id="cng_box_tarif">
                                <div class="input-addon-group">
                                    <span class="addon-prefix">Rp</span>
                                    <input type="number" step="0.01" min="0" name="cng_tarif_baru" id="cng_tarif_baru" 
                                        value="{{ old('cng_tarif_baru', 232500.00) }}" 
                                        class="addon-input" 
                                        oninput="updateLivePreview()">
                                    <span class="addon-prefix">/ MMBTU</span>
                                </div>
                                <div style="font-size:0.725rem; color:#64748b; margin-top:0.35rem;">
                                    Tiap hari otomatis dihitung: <code>MMBTU Gas &times; Rp 232.500</code>.
                                </div>
                            </div>

                            {{-- Input 2: Total Faktur Gas --}}
                            <div id="cng_box_total" style="display:none;">
                                <div class="input-addon-group">
                                    <span class="addon-prefix">Rp</span>
                                    <input type="number" step="0.01" min="0" name="total_cng_tagihan" id="total_cng_tagihan" 
                                        value="{{ old('total_cng_tagihan', $modalTot['cng_nilai']) }}" 
                                        class="addon-input" 
                                        oninput="updateLivePreview()">
                                </div>
                                <div style="font-size:0.725rem; color:#64748b; margin-top:0.35rem;">
                                    Total tagihan gas akan dibagi sesuai porsi MMBTU masing-masing hari kerja.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- RINGKASAN AKUMULASI (3 STRIP KPI) --}}
                    <div class="utilitas-summary-strip">
                        <div class="summary-strip-tile">
                            <span class="summary-tile-label">Hari Kerja Produksi</span>
                            <span class="summary-tile-val">{{ $modalCount }} Hari</span>
                        </div>
                        <div class="summary-strip-tile">
                            <span class="summary-tile-label">Estimasi Total Listrik</span>
                            <span class="summary-tile-val" id="kpi_summary_listrik" style="color:#0284c7;">
                                Rp {{ number_format($modalTot['total_wip_qty'] * 223.80, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="summary-strip-tile">
                            <span class="summary-tile-label">Estimasi Total Gas</span>
                            <span class="summary-tile-val" id="kpi_summary_cng" style="color:#059669;">
                                Rp {{ number_format($modalTot['cng_mmbtu'] * 232500, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    {{-- ACCORDION PRATINJAU RINCIAN TANGGAL (MENJAWAB TANGGAL BANYAK) --}}
                    <div>
                        <button type="button" class="btn-accordion-preview" id="btn_toggle_date_preview" onclick="toggleDateAccordion()">
                            <span>Pratinjau Rincian Tanggal ({{ $modalCount }} Hari Kerja)</span>
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke-width:2.5;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div class="accordion-table-container" id="container_date_preview">
                            <table class="table-accordion-grid">
                                <thead>
                                    <tr>
                                        <th style="width:40px; text-align:center;">No</th>
                                        <th>Tanggal</th>
                                        <th style="text-align:right;">Output WIP</th>
                                        <th style="text-align:right; color:#64748b;">Listrik Lama</th>
                                        <th style="text-align:right; color:#0284c7;">Listrik Baru</th>
                                        <th style="text-align:right; color:#059669;">Gas CNG Baru</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prodDays as $sd)
                                        <tr class="preview-row" data-wip="{{ (float) $sd['total_wip_qty'] }}" data-mmbtu="{{ (float) $sd['cng_mmbtu'] }}">
                                            <td style="text-align:center; color:#94a3b8;">{{ $loop->iteration }}</td>
                                            <td>
                                                <span style="font-weight:700;">{{ date('d/m/Y', strtotime($sd['date'])) }}</span>
                                                <span style="font-size:0.675rem; color:#64748b;">({{ $sd['hari_nm'] }})</span>
                                            </td>
                                            <td style="text-align:right; font-weight:600;">
                                                {{ number_format($sd['total_wip_qty'], 2, ',', '.') }} kg
                                            </td>
                                            <td style="text-align:right; color:#64748b;">
                                                Rp {{ number_format($sd['listrik_air_telp_nilai'], 0, ',', '.') }}
                                            </td>
                                            <td style="text-align:right; font-weight:800; color:#0284c7;" class="cell-new-listrik">
                                                Rp -
                                            </td>
                                            <td style="text-align:right; font-weight:800; color:#059669;" class="cell-new-cng">
                                                Rp -
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Footer Modal Action Buttons --}}
                    <div class="modal-utilitas-footer">
                        <button type="button" class="btn-corp" onclick="closeModalAdjustUtilitas()">
                            Batal
                        </button>
                        <button type="submit" id="btnSubmitAdjustUtilitas" class="btn-corp btn-corp-primary">
                            Simpan &amp; Terapkan Penyesuaian
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
