{{-- ════════════════════════════════════════════════════════════════ --}}
{{--  TABEL REKAPITULASI HPP RINGKAS & MODERN (7 KOLOM + ACCORDION)  --}}
{{--  ERP PT Mirasa Food Industry - Standar Enterprise SAP / NetSuite --}}
{{-- ════════════════════════════════════════════════════════════════ --}}

<div class="card" style="margin-bottom: 2rem; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); overflow: hidden;">
    <div class="card-header" style="background: #ffffff; padding: 0.85rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.65rem;">
            <div>
                <span style="font-size: 0.925rem; font-weight: 700; color: #0f172a; letter-spacing: -0.01em;">
                    Rekapitulasi Harga Pokok Produksi (HPP)
                </span>
                <span style="font-size: 0.725rem; color: #64748b; margin-left: 0.4rem;">
                    Buku Jurnal Manufaktur Harian
                </span>
            </div>
            <span style="font-size: 0.725rem; background: #f8fafc; color: #475569; padding: 0.2rem 0.55rem; border-radius: 4px; font-weight: 600; border: 1px solid #e2e8f0;">
                {{ $report['count'] }} Hari Kerja
            </span>
        </div>

        {{-- Switch Mode Tampilan (Ringkas vs Spreadsheet Asli) --}}
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div class="view-mode-pill-group">
                <button type="button" id="btn-mode-compact" class="btn-view-mode active" onclick="switchHppViewMode('compact')" title="Tampilan terstruktur pas di layar tanpa geser horizontal">
                    <span>Ringkasan Eksekutif</span>
                </button>
                <button type="button" id="btn-mode-spreadsheet" class="btn-view-mode" onclick="switchHppViewMode('spreadsheet')" title="Tampilan format spreadsheet 30 kolom asli Mirasa">
                    <span>Spreadsheet Lengkap</span>
                </button>
            </div>
        </div>
    </div>

    {{-- KONTEN TABEL RINGKAS (100% FIT DI LAYAR TANPA SCROLL KANAN-KIRI) --}}
    <table class="table-compact-hpp">
        <thead>
            <tr>
                <th style="width: 14%;">Tanggal &amp; Shift</th>
                <th style="width: 12%;">Lini &amp; Batch</th>
                <th style="width: 13%; text-align: right;">Bahan Baku (Kg)</th>
                <th style="width: 13%; text-align: right;">Output WIP (Kg)</th>
                <th style="width: 10%; text-align: center;">Rendemen</th>
                <th style="width: 14%; text-align: right;">Total Biaya (Rp)</th>
                <th style="width: 11%; text-align: right;">HPP / Kg</th>
                <th style="width: 6%; text-align: center;">Audit</th>
                <th style="width: 7%; text-align: center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report['days'] as $d)
                @if ($d['has_data'])
                    @php
                        // Status Rendemen Pabrik Mirasa (Target Standar >= 33.0%)
                        $rendemen = (float) $d['rendemen_persen'];
                        if ($rendemen >= 33.0) {
                            $rBadgeBg = '#f0fdf4'; $rBadgeColor = '#166534'; $rBorder = '#bbf7d0';
                        } elseif ($rendemen >= 30.0) {
                            $rBadgeBg = '#fffbeb'; $rBadgeColor = '#92400e'; $rBorder = '#fde68a';
                        } else {
                            $rBadgeBg = '#fef2f2'; $rBadgeColor = '#991b1b'; $rBorder = '#fecaca';
                        }

                        // Lini Produksi Styling
                        $liniUpper = strtoupper($d['lini_produksi'] ?? 'REGULER');
                        $isIfm = str_contains($liniUpper, 'IFM');
                        $isBarco = str_contains($liniUpper, 'BARCO');
                    @endphp

                    {{-- 1. BARIS DATA UTAMA --}}
                    <tr class="main-data-row" id="main-row-{{ $d['day'] }}">
                        {{-- Tanggal & Shift --}}
                        <td>
                            <div style="font-weight: 700; color: #0f172a; font-size: 0.825rem;">
                                {{ $d['hari_nm'] }}, {{ date('d M Y', strtotime($d['date'])) }}
                            </div>
                            <div style="display: flex; gap: 0.35rem; align-items: center; margin-top: 0.2rem;">
                                @if(!empty($d['shift_cd']))
                                    <span style="font-size: 0.65rem; font-weight: 700; padding: 0.1rem 0.35rem; border-radius: 3px; {{ $d['shift_cd'] === 'A' ? 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;' : 'background: #fdf4ff; color: #86198f; border: 1px solid #f5d0fe;' }}">
                                        Shift {{ $d['shift_cd'] }}
                                    </span>
                                @endif
                                <span style="font-size: 0.7rem; color: #64748b;">
                                    Tgl {{ $d['day'] }}
                                </span>
                            </div>
                        </td>

                        {{-- Lini Produksi & No Batch --}}
                        <td>
                            <div style="font-size: 0.75rem; font-weight: 700; color: #1e293b;">
                                {{ $d['lini_produksi'] ?? 'Produksi Reguler' }}
                            </div>
                            @if(!empty($d['produksi_no']))
                                <div style="font-family: monospace; font-size: 0.675rem; color: #64748b; margin-top: 0.15rem;">
                                    {{ $d['produksi_no'] }}
                                </div>
                            @endif
                        </td>

                        {{-- Singkong Mentah (Kg & Nilai) --}}
                        <td style="text-align: right;">
                            <div style="font-weight: 700; font-size: 0.875rem; color: #0f172a; font-variant-numeric: tabular-nums;">
                                {{ number_format($d['singkong_qty'], 0, ',', '.') }} <span style="font-size: 0.7rem; color: #64748b; font-weight: 400;">kg</span>
                            </div>
                            <div style="color: #64748b; font-size: 0.675rem; font-variant-numeric: tabular-nums;">
                                Rp {{ number_format($d['singkong_nilai'], 0, ',', '.') }}
                            </div>
                        </td>

                        {{-- Hasil Jadi WIP (Kg) --}}
                        <td style="text-align: right;">
                            <div style="font-weight: 700; font-size: 0.875rem; color: #0284c7; font-variant-numeric: tabular-nums;">
                                {{ number_format($d['total_wip_qty'], 2, ',', '.') }} <span style="font-size: 0.7rem; color: #64748b; font-weight: 400;">kg</span>
                            </div>
                            @if(!empty($d['berko_persen']) && $d['berko_persen'] > 0)
                                <div style="color: #94a3b8; font-size: 0.65rem;">
                                    Remukan: {{ number_format($d['berko_persen'], 1) }}%
                                </div>
                            @endif
                        </td>

                        {{-- Rendemen (%) --}}
                        <td style="text-align: center;">
                            <span style="display: inline-block; font-weight: 700; font-size: 0.775rem; background: {{ $rBadgeBg }}; color: {{ $rBadgeColor }}; border: 1px solid {{ $rBorder }}; padding: 0.2rem 0.5rem; border-radius: 4px; font-variant-numeric: tabular-nums;">
                                {{ number_format($rendemen, 2, ',', '.') }}%
                            </span>
                        </td>

                        {{-- Total Biaya Produksi --}}
                        <td style="text-align: right;">
                            <div style="font-weight: 700; font-size: 0.875rem; color: #0f172a; font-variant-numeric: tabular-nums;">
                                Rp {{ number_format($d['total_biaya_produksi'], 0, ',', '.') }}
                            </div>
                            <div style="color: #64748b; font-size: 0.65rem;">
                                Bahan + Energi + TK + FOH
                            </div>
                        </td>

                        {{-- HPP / Kg --}}
                        <td style="text-align: right;">
                            <div style="font-weight: 800; font-size: 0.925rem; color: #0f172a; font-family: monospace; font-variant-numeric: tabular-nums;">
                                Rp {{ number_format($d['hpp_per_kg'], 0, ',', '.') }}
                            </div>
                            <span style="font-size: 0.625rem; color: #059669; font-weight: 600; text-transform: uppercase;">
                                Beban Pokok / Kg
                            </span>
                        </td>

                        {{-- Aksi Expand Akordion (Audit Rincian Biaya) --}}
                        <td style="text-align: center;">
                            <button type="button" class="btn-accordion-toggle" onclick="toggleCostAccordion({{ $d['day'] }}, this)" title="Tampilkan rincian komponen biaya akuntansi">
                                <span>Audit</span>
                                <span style="font-size: 0.65rem;">▼</span>
                            </button>
                        </td>

                        {{-- Smart Action Dropdown (Sesuai Standar AGENTS.md & Bagian Lain) --}}
                        <td style="text-align: center; position: relative;">
                            @if(!empty($d['produksi_id']))
                                @php
                                    $compactMenuId = 'dropdown-compact-' . $d['produksi_id'];
                                    $formattedDate = sprintf('%02d/%02d/%04d', $d['day'], $month, $year);
                                @endphp
                                <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $compactMenuId }}')">
                                    Aksi ▼
                                </button>
                                <div id="{{ $compactMenuId }}" class="action-dropdown-menu">
                                    <a href="{{ route('produksi.show', $d['produksi_id']) }}" class="action-dropdown-item">
                                        <span>Detail Dokumen</span>
                                    </a>
                                    <a href="{{ route('produksi.cetak-stiker', $d['produksi_id']) }}" class="action-dropdown-item" target="_blank">
                                        <span>Cetak Stiker Karton</span>
                                    </a>
                                    <div class="action-dropdown-divider"></div>
                                    <button type="button" class="action-dropdown-item danger-item" onclick="openDeleteProduksiModal({{ $d['produksi_id'] }}, '{{ $d['produksi_no'] }}', '{{ $formattedDate }}', '{{ $d['shift_cd'] }}', '{{ $d['batch_wip_no'] }}')">
                                        <span>Hapus Produksi</span>
                                    </button>
                                </div>
                            @else
                                <span style="color: #cbd5e1;">-</span>
                            @endif
                        </td>
                    </tr>

                    {{-- 2. BARIS DETAIL AKORDION (EXPANDABLE COST AUDIT SHEET) --}}
                    <tr class="accordion-detail-row" id="detail-row-{{ $d['day'] }}">
                        <td colspan="9" class="accordion-detail-cell">
                            <div class="cost-breakdown-grid">
                                
                                {{-- KOMPARTEMEN 1: BAHAN BAKU UTAMA & MINYAK GORENG --}}
                                <div class="mini-cost-card">
                                    <div class="mini-cost-header">
                                        <div class="mini-cost-title">
                                            <span>1. Bahan Baku &amp; Minyak</span>
                                        </div>
                                        <span class="mini-cost-badge" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;">Bahan Utama</span>
                                    </div>
                                    <div class="mini-cost-list">
                                        <div class="mini-cost-row">
                                            <span>Singkong Mentah (Kg):</span>
                                            <strong>{{ number_format($d['singkong_qty'], 0, ',', '.') }} kg</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Singkong Mentah (Rp):</span>
                                            <strong>Rp {{ number_format($d['singkong_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Minyak Sawit:</span>
                                            <strong>{{ number_format($d['minyak_sawit_qty'], 1, ',', '.') }} kg</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Minyak Kelapa:</span>
                                            <strong style="color: {{ $d['minyak_kelapa_qty'] > 0 ? '#92400e' : '#64748b' }};">{{ number_format($d['minyak_kelapa_qty'], 1, ',', '.') }} kg</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Total Biaya Minyak:</span>
                                            <strong>Rp {{ number_format($d['minyak_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Rasio Minyak (%):</span>
                                            <strong style="color: #002060;">{{ number_format($d['minyak_rasio_persen'], 2, ',', '.') }}%</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Bumbu &amp; Perenyah:</span>
                                            <strong>Rp {{ number_format($d['bumbu_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                </div>

                                {{-- KOMPARTEMEN 2: BAHAN KEMASAN & PACKAGING --}}
                                <div class="mini-cost-card">
                                    <div class="mini-cost-header">
                                        <div class="mini-cost-title">
                                            <span>2. Bahan Kemasan</span>
                                        </div>
                                        <span class="mini-cost-badge" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe;">Packaging</span>
                                    </div>
                                    <div class="mini-cost-list">
                                        <div class="mini-cost-row">
                                            <span>Karton Baru:</span>
                                            <strong>Rp {{ number_format($d['karton_baru_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Karton Bekas:</span>
                                            <strong>Rp {{ number_format($d['karton_bekas_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Plastik HD 90x100:</span>
                                            <strong>Rp {{ number_format($d['plastik_hd_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Lakban Besar:</span>
                                            <strong>Rp {{ number_format($d['lakban_besar_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Lakban Kecil:</span>
                                            <strong>Rp {{ number_format($d['lakban_kecil_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Tali Rafia:</span>
                                            <strong>Rp {{ number_format($d['tali_rafia_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                </div>

                                {{-- KOMPARTEMEN 3: ENERGI GAS CNG & TENAGA KERJA --}}
                                <div class="mini-cost-card">
                                    <div class="mini-cost-header">
                                        <div class="mini-cost-title">
                                            <span>3. Energi &amp; Upah Kerja</span>
                                        </div>
                                        <span class="mini-cost-badge" style="background: #fefce8; color: #854d0e; border: 1px solid #fef08a;">Konversi</span>
                                    </div>
                                    <div class="mini-cost-list">
                                        <div class="mini-cost-row">
                                            <span>Meteran Gas (CNG):</span>
                                            <strong>{{ number_format($d['cng_mmbtu'], 3, ',', '.') }} MMBTU</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Biaya Gas CNG:</span>
                                            <strong>Rp {{ number_format($d['cng_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>TK Langsung:</span>
                                            <strong>{{ $d['tk_langsung_org'] }} Orang</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>TK Tdk Langsung:</span>
                                            <strong>{{ $d['tk_tidak_langsung_org'] }} Orang</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>TK Training:</span>
                                            <strong>{{ $d['tk_training_org'] }} Orang</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Total Upah Kerja:</span>
                                            <strong>Rp {{ number_format($d['tk_total_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                </div>

                                {{-- KOMPARTEMEN 4: OVERHEAD PABRIK (FOH) & LIMBAH --}}
                                <div class="mini-cost-card">
                                    <div class="mini-cost-header">
                                        <div class="mini-cost-title">
                                            <span>4. FOH &amp; Pengolahan Limbah</span>
                                        </div>
                                        <span class="mini-cost-badge" style="background: #fdf2f8; color: #9d174d; border: 1px solid #fbcfe8;">Overhead</span>
                                    </div>
                                    <div class="mini-cost-list">
                                        <div class="mini-cost-row">
                                            <span>Foto Copy / ATK:</span>
                                            <strong>Rp {{ number_format($d['fotocopy_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Sarung Tangan Plastik:</span>
                                            <strong>Rp {{ number_format($d['sarung_tangan_plastik_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Sarung Tangan Kain:</span>
                                            <strong>Rp {{ number_format($d['sarung_tangan_kain_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Pengawasan Mutu (QC):</span>
                                            <strong>Rp {{ number_format($d['qc_pengawasan_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Listrik &amp; Air - Telp:</span>
                                            <strong>Rp {{ number_format($d['listrik_air_telp_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Pemeliharaan Mesin:</span>
                                            <strong>Rp {{ number_format($d['pemeliharaan_mesin_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Penyusutan Mesin:</span>
                                            <strong>Rp {{ number_format($d['penyusutan_mesin_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Limbah Padat:</span>
                                            <strong>Rp {{ number_format($d['limbah_padat_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Bahan Kimia Limbah:</span>
                                            <strong>Rp {{ number_format($d['limbah_kimia_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                </div>

                                {{-- KOMPARTEMEN 5: TOTAL AKUMULASI BIAYA, WIP & HPP --}}
                                <div class="mini-cost-card" style="border-color: #fde047; background: #fefce8;">
                                    <div class="mini-cost-header" style="border-bottom-color: #fef08a;">
                                        <div class="mini-cost-title" style="color: #713f12;">
                                            <span>5. Output WIP, Rendemen &amp; HPP</span>
                                        </div>
                                        <span class="mini-cost-badge" style="background: #ca8a04; color: #ffffff; font-weight: 700;">HPP Final</span>
                                    </div>
                                    <div class="mini-cost-list">
                                        <div class="mini-cost-row">
                                            <span>Total Output WIP:</span>
                                            <strong style="color: #0369a1; font-size: 0.85rem;">{{ number_format($d['total_wip_qty'], 2, ',', '.') }} kg</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Rendemen Bersih:</span>
                                            <strong style="color: {{ $rBadgeColor }};">{{ number_format($rendemen, 2, ',', '.') }}%</strong>
                                        </div>
                                        @if($d['total_berko_qty'] > 0)
                                            <div class="mini-cost-row">
                                                <span>Remukan (Berko):</span>
                                                <strong style="color: #b91c1c;">{{ number_format($d['total_berko_qty'], 2, ',', '.') }} kg ({{ number_format($d['berko_persen'], 1) }}%)</strong>
                                            </div>
                                        @endif
                                        <div class="mini-cost-divider" style="background: #fde047;"></div>
                                        <div class="mini-cost-row">
                                            <span style="font-weight: 700; color: #713f12;">Total Biaya:</span>
                                            <strong style="color: #713f12; font-size: 0.875rem;">Rp {{ number_format($d['total_biaya_produksi'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row" style="background: #ffffff; padding: 0.35rem 0.5rem; border-radius: 4px; border: 1px solid #fef08a;">
                                            <span style="font-weight: 800; color: #0f172a; font-size: 0.775rem;">HPP / Kg:</span>
                                            <strong style="color: #047857; font-size: 1rem; font-family: monospace;">Rp {{ number_format($d['hpp_per_kg'], 0, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </td>
                    </tr>
                @else
                    {{-- 3. BARIS HARI LIBUR / TIDAK BEROPERASI --}}
                    @php
                        $isSunday = ($d['hari_nm'] ?? '') === 'Minggu';
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9; background: {{ $isSunday ? '#fafafa' : '#ffffff' }};">
                        <td style="padding: 0.5rem 0.85rem; color: {{ $isSunday ? '#94a3b8' : '#94a3b8' }}; font-weight: 500; font-size: 0.8rem;">
                            {{ $d['hari_nm'] }}, {{ date('d M Y', strtotime($d['date'])) }}
                        </td>
                        <td colspan="8" style="padding: 0.5rem 0.85rem; color: #94a3b8; font-style: italic; font-size: 0.75rem;">
                            {{ $isSunday ? 'Hari Minggu — Libur Rutin Manufaktur' : 'Tidak ada kegiatan operasional produksi.' }}
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>

        {{-- FOOTER REKAPITULASI TOTAL --}}
        @php
            $cnt = $report['count'] > 0 ? $report['count'] : 1;
            $tot = $report['totals'];
        @endphp
        @if ($report['count'] > 0)
            <tfoot style="background: #f8fafc; border-top: 2px solid #cbd5e1; font-weight: 700; font-size: 0.825rem;">
                <tr>
                    <td colspan="2" style="padding: 0.75rem 0.85rem; color: #0f172a; text-transform: uppercase; font-size: 0.775rem;">
                        TOTAL AKUMULASI ({{ $report['count'] }} HARI KERJA)
                    </td>
                    <td style="text-align: right; padding: 0.75rem 0.85rem; color: #0f172a;">
                        <div>{{ number_format($tot['singkong_qty'], 0, ',', '.') }} kg</div>
                        <div style="color: #64748b; font-weight: 500; font-size: 0.675rem;">Rp {{ number_format($tot['singkong_nilai'], 0, ',', '.') }}</div>
                    </td>
                    <td style="text-align: right; padding: 0.75rem 0.85rem; color: #0284c7;">
                        {{ number_format($tot['total_wip_qty'], 2, ',', '.') }} kg
                    </td>
                    <td style="text-align: center; padding: 0.75rem 0.85rem; color: #0f172a;">
                        {{ number_format($tot['rendemen_persen'], 2, ',', '.') }}%
                    </td>
                    <td style="text-align: right; padding: 0.75rem 0.85rem; color: #0f172a;">
                        Rp {{ number_format($tot['total_biaya_produksi'], 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; padding: 0.75rem 0.85rem; color: #0f172a; font-family: monospace;">
                        Rp {{ number_format($tot['hpp_per_kg'], 0, ',', '.') }}
                    </td>
                    <td></td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
