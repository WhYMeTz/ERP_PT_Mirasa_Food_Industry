{{-- ════════════════════════════════════════════════════════════════ --}}
{{--  TABEL REKAPITULASI HPP FORMAT SPREADSHEET EXCEL LENGKAP (30 KOLOM) --}}
{{--  ERP PT Mirasa Food Industry - 100% Mengikuti Format Lembar Excel  --}}
{{-- ════════════════════════════════════════════════════════════════ --}}

<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-size: 0.85rem; font-weight: 700; color: #1e293b;">
                Tabel Rekapitulasi HPP Harian (Format Asli Spreadsheet Mirasa)
            </span>
            <span style="font-size: 0.75rem; background: #f1f5f9; color: #475569; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 600;">
                {{ $report['count'] }} Hari Produksi
            </span>
        </div>
        {{-- Switch Mode Kembali ke Ringkas --}}
        <div class="view-mode-pill-group">
            <button type="button" class="btn-view-mode" onclick="switchHppViewMode('compact')" title="Tampilan modern 7 kolom pas di layar tanpa scroll">
                <span>Mode Ringkas</span>
            </button>
            <button type="button" class="btn-view-mode active" onclick="switchHppViewMode('spreadsheet')" title="Tampilan format spreadsheet Excel asli Mirasa">
                <span>Mode Spreadsheet (Excel)</span>
            </button>
        </div>
    </div>

    {{-- CONTAINER SCROLLABLE HORIZONTAL (HANYA SCROLL KIRI-KANAN) --}}
    <div style="overflow-x: auto; position: relative; border-radius: 0 0 12px 12px;">
        <table class="table-rekap-mirasa" style="width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.75rem; white-space: nowrap;">
            {{-- HEADER LEVEL 1, 2, & 3 MENGIKUTI 100% SPREADSHEET EXCEL MIRASA --}}
            <thead style="position: sticky; top: 0; z-index: 20;">
                {{-- ROW 1: MEGA HEADERS --}}
                <tr style="text-align: center; font-weight: 800; font-size: 0.75rem;">
                    <th rowspan="3" class="th-orange" style="position: sticky; left: 0; z-index: 25; background-color: #f4b084 !important; color: #000000 !important; border: 1px solid #7f1d1d !important; padding: 0.5rem 0.6rem; min-width: 85px; font-weight: 800; text-align: center !important; vertical-align: middle !important;">HARI</th>
                    <th rowspan="3" class="th-orange" style="position: sticky; left: 85px; z-index: 25; background-color: #f4b084 !important; color: #000000 !important; border: 1px solid #7f1d1d !important; padding: 0.5rem 0.6rem; min-width: 80px; font-weight: 800; text-align: center !important; vertical-align: middle !important;">TANGGAL</th>
                    <th colspan="28" class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.6rem 0.5rem; font-weight: 900; font-size: 0.85rem; letter-spacing: 0.15em; text-align: center !important; vertical-align: middle !important;">TOTAL BIAYA PRODUKSI / KG</th>
                    <th rowspan="3" class="th-yellow" style="background-color: #ffc000 !important; color: #000000 !important; border: 1px solid #ca8a04 !important; padding: 0.5rem 0.75rem; min-width: 110px; font-weight: 900; font-size: 0.8rem; line-height: 1.2; text-align: center !important; vertical-align: middle !important;">TOTAL<br>BIAYA</th>
                    <th colspan="13" class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.6rem 0.5rem; font-weight: 900; font-size: 0.85rem; letter-spacing: 0.1em; text-align: center !important; vertical-align: middle !important;">TOTAL WIP</th>
                    <th rowspan="3" class="th-grey" style="background-color: #f2f2f2 !important; color: #000000 !important; border: 1px solid #94a3b8 !important; padding: 0.5rem 0.6rem; min-width: 85px; font-weight: 900; font-size: 0.75rem; line-height: 1.2; text-align: center !important; vertical-align: middle !important;">HARGA POKOK<br>PRODUKSI</th>
                    <th rowspan="3" class="th-blue-action" style="position: sticky; right: 0; z-index: 25; background-color: #0284c7 !important; color: #ffffff !important; border: 1px solid #0369a1 !important; padding: 0.5rem 0.65rem; min-width: 75px; font-weight: 800; text-align: center !important; vertical-align: middle !important;">AKSI</th>
                </tr>

                {{-- ROW 2: KATEGORI BIAYA --}}
                <tr style="text-align: center; font-weight: 800; font-size: 0.72rem;">
                    <th colspan="2" class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.35rem 0.4rem; text-align: center !important; vertical-align: middle !important;">SINGKONG</th>
                    <th colspan="4" class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.35rem 0.4rem; text-align: center !important; vertical-align: middle !important;">MINYAK GORENG</th>
                    <th colspan="2" class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.35rem 0.4rem; text-align: center !important; vertical-align: middle !important;">CNG</th>
                    <th colspan="4" class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.35rem 0.4rem; text-align: center !important; vertical-align: middle !important;">TENAGA KERJA</th>
                    <th rowspan="2" class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.35rem 0.4rem; min-width: 85px; line-height: 1.2; text-align: center !important; vertical-align: middle !important;">BUMBU<br>PERENYAH</th>
                    <th colspan="2" class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.35rem 0.4rem; text-align: center !important; vertical-align: middle !important;">KARTON FL</th>
                    <th rowspan="2" class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.35rem 0.4rem; min-width: 80px; line-height: 1.2; text-align: center !important; vertical-align: middle !important;">PLASTIK HD<br>90x100</th>
                    <th colspan="2" class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.35rem 0.4rem; text-align: center !important; vertical-align: middle !important;">LAKBAN</th>
                    <th rowspan="2" class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.35rem 0.4rem; min-width: 70px; line-height: 1.2; text-align: center !important; vertical-align: middle !important;">TALI<br>RAFIA</th>
                    <th rowspan="2" class="th-green" style="background-color: #92d050 !important; color: #c00000 !important; border: 1px solid #3f6212 !important; padding: 0.35rem 0.4rem; min-width: 70px; line-height: 1.2; text-align: center !important; vertical-align: middle !important; font-weight: 900;">FOTO<br>COPY</th>
                    <th colspan="2" class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.35rem 0.4rem; text-align: center !important; vertical-align: middle !important;">SARUNG TANGAN</th>
                    <th rowspan="2" class="th-green" style="background-color: #92d050 !important; color: #c00000 !important; border: 1px solid #3f6212 !important; padding: 0.35rem 0.4rem; min-width: 85px; line-height: 1.2; text-align: center !important; vertical-align: middle !important; font-weight: 900;">PENGAWASAN<br>MUTU</th>
                    <th rowspan="2" class="th-green" style="background-color: #92d050 !important; color: #c00000 !important; border: 1px solid #3f6212 !important; padding: 0.35rem 0.4rem; min-width: 90px; line-height: 1.2; text-align: center !important; vertical-align: middle !important; font-weight: 900;">LISTRIK &amp;<br>AIR - TELP</th>
                    <th rowspan="2" class="th-green" style="background-color: #92d050 !important; color: #c00000 !important; border: 1px solid #3f6212 !important; padding: 0.35rem 0.4rem; min-width: 80px; line-height: 1.2; text-align: center !important; vertical-align: middle !important; font-weight: 900;">PEMLHR<br>MESIN</th>
                    <th rowspan="2" class="th-green" style="background-color: #92d050 !important; color: #c00000 !important; border: 1px solid #3f6212 !important; padding: 0.35rem 0.4rem; min-width: 80px; line-height: 1.2; text-align: center !important; vertical-align: middle !important; font-weight: 900;">PENYS<br>MESIN</th>
                    <th colspan="2" class="th-green" style="background-color: #92d050 !important; color: #c00000 !important; border: 1px solid #3f6212 !important; padding: 0.35rem 0.4rem; text-align: center !important; vertical-align: middle !important; font-weight: 900;">B. PNGOLHN LIMBAH</th>

                    {{-- Under TOTAL WIP Sesuai Format Asli Excel Mirasa (13 Kolom) --}}
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.35rem 0.4rem; min-width: 70px; text-align: center !important; vertical-align: middle !important;">IFL</th>
                    <th colspan="6" class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.35rem 0.4rem; text-align: center !important; vertical-align: middle !important;">MANUAL</th>
                    <th colspan="4" class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.35rem 0.4rem; text-align: center !important; vertical-align: middle !important;">BERKO + BERKO ME</th>
                    <th rowspan="2" class="th-green" style="background-color: #92d050 !important; color: #002060 !important; border: 1px solid #3f6212 !important; padding: 0.35rem 0.4rem; min-width: 85px; line-height: 1.2; text-align: center !important; vertical-align: middle !important; font-weight: 900;">TOTAL<br>KG</th>
                    <th rowspan="2" class="th-green" style="background-color: #92d050 !important; color: #002060 !important; border: 1px solid #3f6212 !important; padding: 0.35rem 0.4rem; min-width: 80px; line-height: 1.2; text-align: center !important; vertical-align: middle !important; font-weight: 900;">RENDE<br>MEN %</th>
                </tr>

                {{-- ROW 3: SUB-KOLOM SPESIFIK --}}
                <tr style="text-align: center; font-weight: 700; font-size: 0.7rem;">
                    {{-- Singkong --}}
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 70px; text-align: center !important; vertical-align: middle !important;">KG</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 95px; text-align: center !important; vertical-align: middle !important;">Rp</th>

                    {{-- Minyak Goreng --}}
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 65px; text-align: center !important; vertical-align: middle !important;">SAWIT</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 65px; text-align: center !important; vertical-align: middle !important;">KELAPA</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 95px; text-align: center !important; vertical-align: middle !important;">Rp</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #002060 !important; border: 1px solid #3f6212 !important; font-weight: 900; padding: 0.3rem 0.4rem; min-width: 60px; text-align: center !important; vertical-align: middle !important;">%</th>

                    {{-- CNG --}}
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 70px; text-align: center !important; vertical-align: middle !important;">MMBTU</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 95px; text-align: center !important; vertical-align: middle !important;">Rp</th>

                    {{-- Tenaga Kerja --}}
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.35rem; min-width: 65px; text-align: center !important; vertical-align: middle !important;">LANGSUNG</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.35rem; min-width: 65px; line-height: 1.1; text-align: center !important; vertical-align: middle !important;">TIDAK<br>LANGSUNG</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.35rem; min-width: 60px; text-align: center !important; vertical-align: middle !important;">TRAINING</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 95px; text-align: center !important; vertical-align: middle !important;">Rp</th>

                    {{-- Karton FL --}}
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 80px; text-align: center !important; vertical-align: middle !important;">BARU</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 80px; text-align: center !important; vertical-align: middle !important;">BEKAS</th>

                    {{-- Lakban --}}
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 70px; text-align: center !important; vertical-align: middle !important;">BESAR</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 70px; text-align: center !important; vertical-align: middle !important;">KECIL</th>

                    {{-- Sarung Tangan --}}
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.35rem; min-width: 65px; text-align: center !important; vertical-align: middle !important;">PLASTIK</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.35rem; min-width: 60px; text-align: center !important; vertical-align: middle !important;">KAIN</th>

                    {{-- B. Pngolhn Limbah --}}
                    <th class="th-green" style="background-color: #92d050 !important; color: #c00000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 80px; line-height: 1.1; text-align: center !important; vertical-align: middle !important; font-weight: 800;">LIMBAH<br>PADAT</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #c00000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 80px; line-height: 1.1; text-align: center !important; vertical-align: middle !important; font-weight: 800;">BAHAN<br>KIMIA</th>

                    {{-- Sub-kolom TOTAL WIP Sesuai Format Asli Excel Mirasa (13 Kolom) --}}
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 70px; text-align: center !important; vertical-align: middle !important;">KG</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 75px; text-align: center !important; vertical-align: middle !important;">ASIN BARC</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 75px; text-align: center !important; vertical-align: middle !important;">ASIN SAWT</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 75px; text-align: center !important; vertical-align: middle !important;">NO SALT</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 75px; text-align: center !important; vertical-align: middle !important;">U/CAMP</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 85px; text-align: center !important; vertical-align: middle !important;">D/LM GELOMBA</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 70px; text-align: center !important; vertical-align: middle !important;">BAL Q</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 70px; text-align: center !important; vertical-align: middle !important;">BERKO</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 75px; text-align: center !important; vertical-align: middle !important;">BERKO ME</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #000000 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 75px; text-align: center !important; vertical-align: middle !important; font-weight: 800;">TOTAL</th>
                    <th class="th-green" style="background-color: #92d050 !important; color: #002060 !important; border: 1px solid #3f6212 !important; padding: 0.3rem 0.4rem; min-width: 60px; text-align: center !important; vertical-align: middle !important; font-weight: 900;">%</th>
                </tr>
            </thead>

            {{-- BODY: 31 BARIS HARIAN DENGAN FORMAT TANGGAL DD/MM/YY SEPERTI EXCEL --}}
            <tbody>
                @foreach ($report['days'] as $d)
                    @php
                        $isSunday = in_array(strtolower($d['hari_nm']), ['minggu', 'ahad']) || (\Carbon\Carbon::parse($d['date'])->dayOfWeek === 0);
                        $rowBg = $isSunday ? '#fff1f2' : ($d['has_data'] ? '#ffffff' : '#f8fafc');
                        $cellBorder = '1px solid #e2e8f0';
                    @endphp
                    <tr style="background: {{ $rowBg }}; text-align: right; color: #1e293b;" onmouseover="this.style.background='#f0f9ff'" onmouseout="this.style.background='{{ $rowBg }}'">
                        {{-- Sticky Kolom 1 (HARI: English Sesuai Excel Mirasa) & Kolom 2 (TANGGAL: dd/mm/yy) --}}
                        <td style="position: sticky; left: 0; z-index: 10; background: {{ $rowBg }}; border: {{ $cellBorder }}; padding: 0.45rem 0.6rem; text-align: left; font-weight: 700; color: {{ $isSunday ? '#e11d48' : '#0f172a' }};">
                            {{ \Carbon\Carbon::parse($d['date'])->format('l') }}
                        </td>
                        <td style="position: sticky; left: 85px; z-index: 10; background: {{ $rowBg }}; border: {{ $cellBorder }}; padding: 0.45rem 0.6rem; text-align: center; font-weight: 600;">
                            <div>{{ \Carbon\Carbon::parse($d['date'])->format('d/m/y') }}</div>
                            @if($d['has_data'] && !empty($d['shift_cd']))
                                @if(($d['shift_count'] ?? 1) > 1)
                                    <span style="display: inline-block; font-size: 0.625rem; background: #e0e7ff; color: #3730a3; padding: 1px 5px; border-radius: 4px; font-weight: 700; margin-top: 2px;" title="{{ $d['shift_count'] }} shift tercatat pada tanggal ini">
                                        {{ $d['shift_count'] }} Shift ({{ $d['shift_cd'] }})
                                    </span>
                                @else
                                    <span style="display: inline-block; font-size: 0.625rem; background: #f1f5f9; color: #475569; padding: 1px 4px; border-radius: 4px; font-weight: 600; margin-top: 2px;">
                                        Shift {{ $d['shift_cd'] }}
                                    </span>
                                @endif
                            @endif
                        </td>

                        {{-- Singkong --}}
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['singkong_qty'] > 0 ? number_format($d['singkong_qty'], 0, ',', '.') : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['singkong_nilai'] > 0 ? number_format($d['singkong_nilai'], 0, ',', '.') : '-' }}
                        </td>

                        {{-- Minyak Goreng --}}
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['minyak_sawit_qty'] > 0 ? number_format($d['minyak_sawit_qty'], 1, ',', '.') : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['minyak_kelapa_qty'] > 0 ? number_format($d['minyak_kelapa_qty'], 1, ',', '.') : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['minyak_nilai'] > 0 ? number_format($d['minyak_nilai'], 0, ',', '.') : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem; text-align: center;">
                            <span style="color: #002060; font-weight: 800;">
                                {{ $d['has_data'] && $d['minyak_rasio_persen'] > 0 ? number_format($d['minyak_rasio_persen'], 2, ',', '.') . '%' : '-' }}
                            </span>
                        </td>

                        {{-- CNG --}}
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['cng_mmbtu'] > 0 ? number_format($d['cng_mmbtu'], 2, ',', '.') : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['cng_nilai'] > 0 ? number_format($d['cng_nilai'], 0, ',', '.') : '-' }}
                        </td>

                        {{-- Tenaga Kerja --}}
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.35rem; text-align: center;">
                            {{ $d['has_data'] && $d['tk_langsung_org'] > 0 ? $d['tk_langsung_org'] : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.35rem; text-align: center;">
                            {{ $d['has_data'] && $d['tk_tidak_langsung_org'] > 0 ? $d['tk_tidak_langsung_org'] : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.35rem; text-align: center;">
                            {{ $d['has_data'] && $d['tk_training_org'] > 0 ? $d['tk_training_org'] : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['tk_total_nilai'] > 0 ? number_format($d['tk_total_nilai'], 0, ',', '.') : '-' }}
                        </td>

                        {{-- Bahan Pembantu & Pengemas --}}
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['bumbu_nilai'] > 0 ? number_format($d['bumbu_nilai'], 0, ',', '.') : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['karton_baru_nilai'] > 0 ? number_format($d['karton_baru_nilai'], 0, ',', '.') : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['karton_bekas_nilai'] > 0 ? number_format($d['karton_bekas_nilai'], 0, ',', '.') : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['plastik_hd_nilai'] > 0 ? number_format($d['plastik_hd_nilai'], 0, ',', '.') : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['lakban_besar_nilai'] > 0 ? number_format($d['lakban_besar_nilai'], 0, ',', '.') : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['lakban_kecil_nilai'] > 0 ? number_format($d['lakban_kecil_nilai'], 0, ',', '.') : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['tali_rafia_nilai'] > 0 ? number_format($d['tali_rafia_nilai'], 0, ',', '.') : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['fotocopy_nilai'] > 0 ? number_format($d['fotocopy_nilai'], 0, ',', '.') : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.35rem;">
                            {{ $d['has_data'] && $d['sarung_tangan_plastik_nilai'] > 0 ? number_format($d['sarung_tangan_plastik_nilai'], 0, ',', '.') : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.35rem;">
                            {{ $d['has_data'] && $d['sarung_tangan_kain_nilai'] > 0 ? number_format($d['sarung_tangan_kain_nilai'], 0, ',', '.') : '-' }}
                        </td>

                        {{-- FOH & Operasional --}}
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['qc_pengawasan_nilai'] > 0 ? number_format($d['qc_pengawasan_nilai'], 0, ',', '.') : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['listrik_air_telp_nilai'] > 0 ? number_format($d['listrik_air_telp_nilai'], 0, ',', '.') : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['pemeliharaan_mesin_nilai'] > 0 ? number_format($d['pemeliharaan_mesin_nilai'], 0, ',', '.') : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['penyusutan_mesin_nilai'] > 0 ? number_format($d['penyusutan_mesin_nilai'], 0, ',', '.') : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['limbah_padat_nilai'] > 0 ? number_format($d['limbah_padat_nilai'], 0, ',', '.') : '-' }}
                        </td>
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['limbah_kimia_nilai'] > 0 ? number_format($d['limbah_kimia_nilai'], 0, ',', '.') : '-' }}
                        </td>

                        {{-- TOTAL BIAYA (Warna Kuning Stabilo Seperti Excel, Nilai Tebal Biru) --}}
                        <td style="border: 1px solid #ca8a04; background: {{ $d['has_data'] && $d['total_biaya_produksi'] > 0 ? '#ffeb3b' : 'inherit' }}; font-weight: 800; color: #002060; padding: 0.45rem 0.65rem;">
                            {{ $d['has_data'] && $d['total_biaya_produksi'] > 0 ? number_format($d['total_biaya_produksi'], 0, ',', '.') : '-' }}
                        </td>

                        {{-- IFL (KG) --}}
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['ifl_qty'] > 0 ? number_format($d['ifl_qty'], 2, ',', '.') : '-' }}
                        </td>

                        {{-- MANUAL: ASIN BARC --}}
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['asin_barco_qty'] > 0 ? number_format($d['asin_barco_qty'], 2, ',', '.') : '-' }}
                        </td>

                        {{-- MANUAL: ASIN SAWT --}}
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['asin_sawit_qty'] > 0 ? number_format($d['asin_sawit_qty'], 2, ',', '.') : '-' }}
                        </td>

                        {{-- MANUAL: NO SALT --}}
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['no_salt_qty'] > 0 ? number_format($d['no_salt_qty'], 2, ',', '.') : '-' }}
                        </td>

                        {{-- MANUAL: U/CAMP --}}
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['ucamp_qty'] > 0 ? number_format($d['ucamp_qty'], 2, ',', '.') : '-' }}
                        </td>

                        {{-- MANUAL: D/LM GELOMBA --}}
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['balo_gelombang_qty'] > 0 ? number_format($d['balo_gelombang_qty'], 2, ',', '.') : '-' }}
                        </td>

                        {{-- MANUAL: BAL Q --}}
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['balqi_qty'] > 0 ? number_format($d['balqi_qty'], 2, ',', '.') : '-' }}
                        </td>

                        {{-- BERKO --}}
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['berko_qty'] > 0 ? number_format($d['berko_qty'], 2, ',', '.') : '-' }}
                        </td>

                        {{-- BERKO ME --}}
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem;">
                            {{ $d['has_data'] && $d['berko_me_qty'] > 0 ? number_format($d['berko_me_qty'], 2, ',', '.') : '-' }}
                        </td>

                        {{-- TOTAL BERKO --}}
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem; font-weight: 700;">
                            {{ $d['has_data'] && $d['total_berko_qty'] > 0 ? number_format($d['total_berko_qty'], 2, ',', '.') : '-' }}
                        </td>

                        {{-- BERKO % --}}
                        <td style="border: {{ $cellBorder }}; padding: 0.45rem 0.5rem; text-align: center; color: #002060; font-weight: 800;">
                            {{ $d['has_data'] && $d['berko_persen'] > 0 ? number_format($d['berko_persen'], 2, ',', '.') . '%' : '-' }}
                        </td>

                        {{-- TOTAL KG (WIP) (Teks Biru Tebal Seperti Excel) --}}
                        <td style="border: {{ $cellBorder }}; font-weight: 800; color: #002060; padding: 0.45rem 0.65rem;">
                            {{ $d['has_data'] && $d['total_wip_qty'] > 0 ? number_format($d['total_wip_qty'], 2, ',', '.') : '-' }}
                        </td>

                        {{-- RENDEMEN % (Teks Biru Tebal Seperti Excel) --}}
                        <td style="border: {{ $cellBorder }}; font-weight: 800; color: #002060; padding: 0.45rem 0.5rem; text-align: center;">
                            {{ $d['has_data'] && $d['rendemen_persen'] > 0 ? number_format($d['rendemen_persen'], 2, ',', '.') . '%' : '-' }}
                        </td>

                        {{-- HARGA POKOK PRODUKSI / KG --}}
                        <td style="border: {{ $cellBorder }}; font-weight: 800; color: #000000; padding: 0.45rem 0.65rem;">
                            {{ $d['has_data'] && $d['hpp_per_kg'] > 0 ? number_format($d['hpp_per_kg'], 0, ',', '.') : '-' }}
                        </td>

                        {{-- Sticky Kolom Kanan Aksi --}}
                        <td style="position: sticky; right: 0; z-index: 10; background: {{ $rowBg }}; border: {{ $cellBorder }}; padding: 0.35rem 0.5rem; text-align: center;">
                            @if($d['has_data'] && !empty($d['produksi_id']))
                                @php $rekapMenuId = 'menu-rekap-' . $d['produksi_id']; @endphp
                                <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $rekapMenuId }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div id="{{ $rekapMenuId }}" class="action-dropdown-menu">
                                    @if(($d['shift_count'] ?? 1) > 1 && !empty($d['shift_records']))
                                        <div style="padding: 0.35rem 0.75rem; font-size: 0.675rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; background: #f8fafc; border-bottom: 1px solid #f1f5f9;">
                                            Rincian Shift ({{ $d['shift_count'] }} Dokumen)
                                        </div>
                                        @foreach($d['shift_records'] as $sRec)
                                            <a href="{{ route('produksi.show', $sRec['produksi_id']) }}" class="action-dropdown-item">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span>Detail Shift {{ $sRec['shift_cd'] }} ({{ $sRec['produksi_no'] }})</span>
                                            </a>
                                            <a href="{{ route('produksi.cetak-stiker', $sRec['produksi_id']) }}" class="action-dropdown-item" target="_blank" style="padding-left: 1.75rem; font-size: 0.75rem; color: #475569;">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                <span>Cetak Stiker Shift {{ $sRec['shift_cd'] }}</span>
                                            </a>
                                        @endforeach
                                        <div class="action-dropdown-divider"></div>
                                        @php
                                            $tglSpreadsheet = sprintf('%02d/%02d/%04d', $d['day'], $month, $year);
                                        @endphp
                                        <button type="button" class="action-dropdown-item danger-item" onclick="openDeleteProduksiModal({{ $d['produksi_id'] }}, '{{ $d['produksi_no'] }}', '{{ $tglSpreadsheet }}', '{{ $d['shift_cd'] }}', '{{ $d['batch_wip_no'] }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Hapus Data Produksi</span>
                                        </button>
                                    @else
                                        <a href="{{ route('produksi.show', $d['produksi_id']) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Detail Dokumen</span>
                                        </a>
                                        <a href="{{ route('produksi.cetak-stiker', $d['produksi_id']) }}" class="action-dropdown-item" target="_blank">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            <span>Cetak Stiker Karton</span>
                                        </a>
                                        <div class="action-dropdown-divider"></div>
                                        @php
                                            $tglSpreadsheet = sprintf('%02d/%02d/%04d', $d['day'], $month, $year);
                                        @endphp
                                        <button type="button" class="action-dropdown-item danger-item" onclick="openDeleteProduksiModal({{ $d['produksi_id'] }}, '{{ $d['produksi_no'] }}', '{{ $tglSpreadsheet }}', '{{ $d['shift_cd'] }}', '{{ $d['batch_wip_no'] }}')">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span>Hapus Produksi</span>
                                        </button>
                                    @endif
                                </div>
                            @else
                                <span style="color: #cbd5e1;">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>

            {{-- TFOOT: BARIS TOTAL & RATA-RATA SEPERTI EXCEL MIRASA --}}
            @if($report['count'] > 0)
                @php 
                    $cnt = max(1, $report['count']); 
                    $tot = $report['totals'];
                @endphp
                <tfoot style="position: sticky; bottom: 0; z-index: 20;">
                    {{-- ROW 1: T O T A L --}}
                    <tr style="background: #ffffff; color: #000000; font-weight: 800; font-size: 0.75rem; text-align: right;">
                        <td colspan="2" style="position: sticky; left: 0; z-index: 25; background: #ffc000; color: #000000; padding: 0.55rem 0.65rem; text-align: center; border: 1px solid #ca8a04; font-weight: 900; letter-spacing: 0.1em;">
                            T O T A L
                        </td>

                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['singkong_qty'], 0, ',', '.') }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['singkong_nilai'], 0, ',', '.') }}</td>

                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['minyak_sawit_qty'], 1, ',', '.') }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['minyak_kelapa_qty'], 1, ',', '.') }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['minyak_nilai'], 0, ',', '.') }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem; text-align: center;">
                            <span style="color: #002060; font-weight: 900;">{{ number_format($tot['minyak_rasio_persen'], 2, ',', '.') }}%</span>
                        </td>

                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['cng_mmbtu'], 2, ',', '.') }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['cng_nilai'], 0, ',', '.') }}</td>

                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.35rem; text-align: center;">{{ $tot['tk_langsung_org'] }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.35rem; text-align: center;">{{ $tot['tk_tidak_langsung_org'] }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.35rem; text-align: center;">{{ $tot['tk_training_org'] }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['tk_total_nilai'], 0, ',', '.') }}</td>

                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['bumbu_nilai'], 0, ',', '.') }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['karton_baru_nilai'], 0, ',', '.') }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['karton_bekas_nilai'], 0, ',', '.') }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['plastik_hd_nilai'], 0, ',', '.') }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['lakban_besar_nilai'], 0, ',', '.') }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['lakban_kecil_nilai'], 0, ',', '.') }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['tali_rafia_nilai'], 0, ',', '.') }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['fotocopy_nilai'], 0, ',', '.') }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.35rem;">{{ number_format($tot['sarung_tangan_plastik_nilai'], 0, ',', '.') }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.35rem;">{{ number_format($tot['sarung_tangan_kain_nilai'], 0, ',', '.') }}</td>

                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['qc_pengawasan_nilai'], 0, ',', '.') }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['listrik_air_telp_nilai'], 0, ',', '.') }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['pemeliharaan_mesin_nilai'], 0, ',', '.') }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['penyusutan_mesin_nilai'], 0, ',', '.') }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['limbah_padat_nilai'], 0, ',', '.') }}</td>
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['limbah_kimia_nilai'], 0, ',', '.') }}</td>

                        {{-- Total Biaya Grand --}}
                        <td style="background: #ffeb3b; color: #002060; font-weight: 900; border: 1px solid #ca8a04; padding: 0.55rem 0.65rem;">
                            {{ number_format($tot['total_biaya_produksi'], 0, ',', '.') }}
                        </td>

                        {{-- Total IFL (KG) --}}
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['ifl_qty'], 2, ',', '.') }}</td>

                        {{-- Total ASIN BARC --}}
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['asin_barco_qty'], 2, ',', '.') }}</td>

                        {{-- Total ASIN SAWT --}}
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['asin_sawit_qty'], 2, ',', '.') }}</td>

                        {{-- Total NO SALT --}}
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['no_salt_qty'], 2, ',', '.') }}</td>

                        {{-- Total U/CAMP --}}
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['ucamp_qty'], 2, ',', '.') }}</td>

                        {{-- Total D/LM GELOMBA --}}
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['balo_gelombang_qty'], 2, ',', '.') }}</td>

                        {{-- Total BAL Q --}}
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['balqi_qty'], 2, ',', '.') }}</td>

                        {{-- Total BERKO --}}
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['berko_qty'], 2, ',', '.') }}</td>

                        {{-- Total BERKO ME --}}
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem;">{{ number_format($tot['berko_me_qty'], 2, ',', '.') }}</td>

                        {{-- Grand TOTAL BERKO --}}
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem; font-weight: 900;">{{ number_format($tot['total_berko_qty'], 2, ',', '.') }}</td>

                        {{-- Weighted BERKO % --}}
                        <td style="border: 1px solid #cbd5e1; padding: 0.45rem 0.5rem; text-align: center; color: #002060; font-weight: 900;">
                            {{ number_format($tot['berko_persen'], 2, ',', '.') }}%
                        </td>

                        {{-- Grand Total WIP --}}
                        <td style="border: 1px solid #cbd5e1; color: #002060; font-weight: 900; padding: 0.55rem 0.65rem;">
                            {{ number_format($tot['total_wip_qty'], 2, ',', '.') }}
                        </td>

                        {{-- Rata-rata Rendemen --}}
                        <td style="border: 1px solid #cbd5e1; color: #002060; font-weight: 900; padding: 0.55rem 0.5rem; text-align: center;">
                            {{ number_format($tot['rendemen_persen'], 2, ',', '.') }}%
                        </td>

                        {{-- Rata-rata HPP per Kg --}}
                        <td style="border: 1px solid #cbd5e1; color: #000000; font-weight: 900; padding: 0.55rem 0.65rem;">
                            {{ number_format($tot['hpp_per_kg'], 0, ',', '.') }}
                        </td>

                        {{-- Sticky Col Right Aksi --}}
                        <td style="position: sticky; right: 0; z-index: 25; background: #0284c7; border: 1px solid #0369a1;"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
