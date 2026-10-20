{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{--  TABEL REKAPITULASI HPP PENGEMASAN PING-PING & MAKSI (SPREADSHEET MATRIX)  --}}
{{--  ERP PT Mirasa Food Industry                                               --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}

@php
    $dh = $report['daftar_harga'] ?? [];
    $tot = $report['totals'] ?? [];
    $avg = $report['averages'] ?? [];
@endphp

{{-- 1. PANEL ACUAN DAFTAR HARGA STANDAR BIAYA PENGEMASAN --}}
<div class="daftar-harga-card">
    <div class="daftar-harga-header">
        <div class="daftar-harga-title">
            <svg width="16" height="16" fill="none" stroke="#0284c7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <span>DAFTAR HARGA ACUAN PRODUKSI (PING-PING &amp; MAKSI) &bull; PERIODE {{ strtoupper($monthName) }} {{ $year }}</span>
        </div>
        <div style="display: flex; align-items: center; gap: 0.65rem;">
            @if(Auth::user()?->canManageTarifKemasan())
                <button type="button" 
                        onclick="openQuickUpdateModal()" 
                        class="btn btn-sm" 
                        style="font-size: 0.75rem; font-weight: 700; background: #0284c7; color: #ffffff; border: none; border-radius: 4px; padding: 0.2rem 0.6rem; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; transition: background 0.15s ease;"
                        onmouseover="this.style.background='#0369a1'"
                        onmouseout="this.style.background='#0284c7'"
                        title="Perbarui nilai master standar harga acuan kemasan">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    <span>Ubah Standar Acuan</span>
                </button>
            @endif
            <span style="font-size: 0.72rem; color: #64748b; font-style: italic;">
                Formula Standar Akuntansi Biaya Manufaktur PT Mirasa
            </span>
        </div>
    </div>

    <div class="daftar-harga-grid">
        {{-- Parameter 1: Harga WIP --}}
        <div class="price-ref-box" style="border-left: 3px solid #eab308;">
            <span class="price-ref-label">Harga WIP / Curah</span>
            <div class="price-ref-val" style="color: #854d0e;">
                Rp {{ number_format($dh['harga_wip'] ?? 29500, 0, ',', '.') }}<span style="font-size: 0.75rem; font-weight: normal; color: #64748b;"> / kg</span>
            </div>
            <span class="price-ref-sub">Acuan: {{ $dh['tgl_wip'] ?? '20/01/2026' }}</span>
        </div>

        {{-- Parameter 2: Harga Karton Dus --}}
        <div class="price-ref-box" style="border-left: 3px solid #f97316;">
            <span class="price-ref-label">Harga Karton / Dus</span>
            <div class="price-ref-val" style="font-size: 0.85rem; color: #9a3412;">
                XX: Rp {{ number_format($dh['karton_kk'] ?? 1358.3, 1, ',', '.') }}
            </div>
            <span class="price-ref-sub">MX: Rp {{ number_format($dh['karton_mk'] ?? 1350, 1, ',', '.') }} / dus</span>
        </div>

        {{-- Parameter 3: Harga Bumbu Balado --}}
        <div class="price-ref-box" style="border-left: 3px solid #ef4444;">
            <span class="price-ref-label">Harga Bumbu Balado</span>
            <div class="price-ref-val" style="color: #991b1b;">
                Rp {{ number_format($dh['bumbu_balado'] ?? 39450, 0, ',', '.') }}<span style="font-size: 0.75rem; font-weight: normal; color: #64748b;"> / kg</span>
            </div>
            <span class="price-ref-sub">Rasio: {{ $dh['bumbu_rasio'] ?? '5% dari Berko' }}</span>
        </div>

        {{-- Parameter 4: Harga Roll Kemasan --}}
        <div class="price-ref-box" style="border-left: 3px solid #10b981;">
            <span class="price-ref-label">Harga Roll Kemasan</span>
            <div class="price-ref-val" style="font-size: 0.85rem; color: #065f46;">
                XX: Rp {{ number_format($dh['roll_kk_unit'] ?? 364839, 0, ',', '.') }}
            </div>
            <span class="price-ref-sub">MX: Rp {{ number_format($dh['roll_mk_unit'] ?? 384471, 0, ',', '.') }} &bull; Rp {{ number_format($dh['roll_per_kg'] ?? 43482, 0, ',', '.') }}/kg</span>
        </div>

        {{-- Parameter 5: Harga Lakban --}}
        <div class="price-ref-box" style="border-left: 3px solid #06b6d4;">
            <span class="price-ref-label">Harga Lakban</span>
            <div class="price-ref-val" style="color: #0e7490;">
                Rp {{ number_format($dh['lakban_roll'] ?? 45000, 0, ',', '.') }}<span style="font-size: 0.75rem; font-weight: normal; color: #64748b;"> / roll</span>
            </div>
            <span class="price-ref-sub">Tarif: Rp {{ number_format($dh['lakban_per_gr'] ?? 106.63, 2, ',', '.') }} / gram</span>
        </div>

        {{-- Parameter 6: Kertas Exp & Kerusakan --}}
        <div class="price-ref-box" style="border-left: 3px solid #8b5cf6;">
            <span class="price-ref-label">Kertas Exp &amp; Kerusakan</span>
            <div class="price-ref-val" style="font-size: 0.85rem; color: #5b21b6;">
                Exp: Rp {{ number_format($dh['kertas_exp_lbr'] ?? 100, 0, ',', '.') }}<span style="font-size: 0.7rem; color: #64748b;"> / lbr</span>
            </div>
            <span class="price-ref-sub">Kerusakan Roll: Rp {{ number_format($dh['kerusakan_gr'] ?? 43.48, 2, ',', '.') }} / gr</span>
        </div>
    </div>
</div>

{{-- 2. KARTU TABEL UTAMA SPREADSHEET MATRIX --}}
<div class="card" style="margin-bottom: 2rem; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); overflow: hidden;">
    <div class="card-header" style="background: #ffffff; padding: 0.85rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.65rem;">
            <div>
                <span style="font-size: 0.95rem; font-weight: 800; color: #0f172a; letter-spacing: -0.01em;">
                    Buku Besar HPP Pengemasan Ping-Ping &amp; Maksi (Spreadsheet)
                </span>
                <span style="font-size: 0.725rem; color: #64748b; margin-left: 0.4rem;">
                    Bulan {{ $monthName }} {{ $year }}
                </span>
            </div>
            <span style="font-size: 0.725rem; background: #eff6ff; color: #1d4ed8; padding: 0.2rem 0.55rem; border-radius: 4px; font-weight: 700; border: 1px solid #bfdbfe;">
                {{ $report['count'] }} Hari Operasional
            </span>
        </div>

        {{-- Switch Mode Tampilan --}}
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div class="view-mode-pill-group">
                <button type="button" id="btn-mode-compact-sp" class="btn-view-mode" onclick="switchHppViewMode('compact')" title="Tampilan ringkas pas di layar">
                    <span>Ringkasan Eksekutif</span>
                </button>
                <button type="button" id="btn-mode-spreadsheet-sp" class="btn-view-mode active" onclick="switchHppViewMode('spreadsheet')" title="Tampilan spreadsheet matrix lengkap">
                    <span>Spreadsheet Lengkap</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Container Horizontal Scrollable --}}
    <div style="overflow-x: auto; width: 100%;">
        <table class="table-packaging-spreadsheet">
            <thead>
                {{-- BARIS HEADER 1: KELOMPOK UTAMA BIAYA & OUTPUT (WARNA SESUAI EXCEL MIRASA) --}}
                <tr>
                    <th rowspan="3" style="width: 70px; background: #f1f5f9; color: #334155;">HARI</th>
                    <th rowspan="3" style="width: 85px; background: #f1f5f9; color: #334155;">TANGGAL</th>

                    {{-- 1. BAHAN KERIPIK (KUNING LEMBUT) --}}
                    <th colspan="3" rowspan="2" style="background: #fef9c3; color: #854d0e; border-color: #fde047;">
                        BAHAN KERIPIK
                    </th>

                    {{-- 2. KARTON (AMBER LEMBUT) --}}
                    <th colspan="3" rowspan="2" style="background: #fef3c7; color: #92400e; border-color: #fcd34d;">
                        KARTON
                    </th>

                    {{-- 3. BUMBU BALADO (ORANYE LEMBUT) --}}
                    <th colspan="2" rowspan="2" style="background: #ffedd5; color: #9a3412; border-color: #fed7aa;">
                        BUMBU BALADO
                    </th>

                    {{-- 4. TOTAL BIAYA PRODUKSI / KG (HIJAU MINT / CYAN) --}}
                    <th colspan="13" style="background: #ecfdf5; color: #065f46; border-color: #a7f3d0;">
                        TOTAL BIAYA PRODUKSI / KG
                    </th>

                    {{-- 5. TOTAL BIAYA (KUNING EMAS / GOLD) --}}
                    <th rowspan="3" style="width: 125px; background: #fef08a; color: #713f12; border-color: #eab308; font-size: 0.8rem;">
                        TOTAL BIAYA
                    </th>

                    {{-- 6. HASIL PRODUKSI (UNGU LEMBUT) --}}
                    <th colspan="4" rowspan="2" style="background: #f3e8ff; color: #6b21a8; border-color: #d8b4fe;">
                        HASIL PRODUKSI
                    </th>

                    {{-- 7. HARGA POKOK PRODUKSI (HIJAU EMERALD) --}}
                    <th rowspan="3" style="width: 100px; background: #d1fae5; color: #065f46; border-color: #6ee7b7; font-size: 0.775rem;">
                        HARGA POKOK PRODUKSI
                    </th>

                    {{-- 8. AKSI --}}
                    <th rowspan="3" style="width: 75px; background: #f8fafc; color: #475569;">
                        AKSI
                    </th>
                </tr>

                {{-- BARIS HEADER 2: SUB-KELOMPOK BIAYA PRODUKSI (LEVEL 2) --}}
                <tr style="background: #f8fafc;">
                    <th colspan="3" style="background: #d1fae5; color: #065f46; font-size: 0.72rem;">PENGGUNAAN ROLL (KG)</th>
                    <th colspan="4" style="background: #ecfeff; color: #0e7490; font-size: 0.72rem;">LAKBAN (GR)</th>
                    <th colspan="2" style="background: #f0f9ff; color: #0369a1; font-size: 0.72rem;">KERTAS EXPIRED</th>
                    <th colspan="2" style="background: #fef2f2; color: #991b1b; font-size: 0.72rem;">KERUSAKAN PLASTIK</th>
                    <th colspan="2" style="background: #fff1f2; color: #9f1239; font-size: 0.72rem;">KERUSAKAN KARTON</th>
                </tr>

                {{-- BARIS HEADER 3: RINCIAN SUB-KOLOM (LEVEL 3) --}}
                <tr style="background: #f8fafc;">
                    {{-- Sub-Kolom Bahan Keripik --}}
                    <th style="width: 75px; background: #fefce8; color: #854d0e;">BERKO (KG)</th>
                    <th style="width: 75px; background: #fefce8; color: #854d0e;">WIP (KG)</th>
                    <th style="width: 105px; background: #fefce8; color: #854d0e;">RUPIAH</th>

                    {{-- Sub-Kolom Karton --}}
                    <th style="width: 65px; background: #fffbeb; color: #92400e;">XX</th>
                    <th style="width: 65px; background: #fffbeb; color: #92400e;">MX</th>
                    <th style="width: 105px; background: #fffbeb; color: #92400e;">RUPIAH</th>

                    {{-- Sub-Kolom Bumbu Balado --}}
                    <th style="width: 75px; background: #fff7ed; color: #9a3412;">BALADO</th>
                    <th style="width: 105px; background: #fff7ed; color: #9a3412;">RUPIAH</th>

                    {{-- Sub-Kolom Penggunaan Roll --}}
                    <th style="width: 65px; background: #f0fdf4; color: #065f46;">ROLL XX</th>
                    <th style="width: 65px; background: #f0fdf4; color: #065f46;">ROLL MX</th>
                    <th style="width: 105px; background: #f0fdf4; color: #065f46;">RUPIAH</th>

                    {{-- Sub-Kolom Lakban (gr) --}}
                    <th style="width: 60px; background: #ecfeff; color: #0e7490;">ATAS</th>
                    <th style="width: 60px; background: #ecfeff; color: #0e7490;">BAWAH</th>
                    <th style="width: 65px; background: #ecfeff; color: #0e7490;">JUMLAH</th>
                    <th style="width: 100px; background: #ecfeff; color: #0e7490;">RUPIAH</th>

                    {{-- Sub-Kolom Kertas Expired --}}
                    <th style="width: 65px; background: #f0f9ff; color: #0369a1;">BUNGKUS</th>
                    <th style="width: 90px; background: #f0f9ff; color: #0369a1;">RUPIAH</th>

                    {{-- Sub-Kolom Kerusakan Plastik --}}
                    <th style="width: 70px; background: #fef2f2; color: #991b1b;">JML (GR)</th>
                    <th style="width: 90px; background: #fef2f2; color: #991b1b;">RUPIAH</th>

                    {{-- Sub-Kolom Kerusakan Karton --}}
                    <th style="width: 65px; background: #fff1f2; color: #9f1239;">JML (PCS)</th>
                    <th style="width: 90px; background: #fff1f2; color: #9f1239;">RUPIAH</th>

                    {{-- Sub-Kolom Hasil Produksi --}}
                    <th style="width: 75px; background: #faf5ff; color: #6b21a8;">PING-PING</th>
                    <th style="width: 75px; background: #faf5ff; color: #6b21a8;">MAKSI</th>
                    <th style="width: 80px; background: #faf5ff; color: #6b21a8; font-weight: 800;">JUMLAH</th>
                    <th style="width: 80px; background: #faf5ff; color: #6b21a8;">GRAMASI</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($report['days'] as $d)
                    @if ($d['has_data'])
                        <tr>
                            <td style="text-align: center; font-weight: 700; color: #0f172a;">{{ $d['hari_nm'] }}</td>
                            <td style="text-align: center; font-size: 0.775rem;">{{ date('d/m/y', strtotime($d['date'])) }}</td>

                            {{-- 1. BAHAN KERIPIK --}}
                            <td style="text-align: right;">{{ $d['berko_kg'] > 0 ? number_format($d['berko_kg'], 2, ',', '.') : '-' }}</td>
                            <td style="text-align: right;">{{ $d['wip_kg'] > 0 ? number_format($d['wip_kg'], 2, ',', '.') : '-' }}</td>
                            <td style="text-align: right; font-weight: 700; color: #854d0e;">
                                {{ $d['bahan_keripik_nilai'] > 0 ? number_format($d['bahan_keripik_nilai'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- 2. KARTON --}}
                            <td style="text-align: right;">{{ $d['karton_kk_qty'] > 0 ? number_format($d['karton_kk_qty'], 0, ',', '.') : '-' }}</td>
                            <td style="text-align: right;">{{ $d['karton_mk_qty'] > 0 ? number_format($d['karton_mk_qty'], 0, ',', '.') : '-' }}</td>
                            <td style="text-align: right; font-weight: 700; color: #92400e;">
                                {{ $d['karton_nilai'] > 0 ? number_format($d['karton_nilai'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- 3. BUMBU BALADO --}}
                            <td style="text-align: right;">{{ $d['balado_kg'] > 0 ? number_format($d['balado_kg'], 2, ',', '.') : '-' }}</td>
                            <td style="text-align: right; font-weight: 700; color: #9a3412;">
                                {{ $d['balado_nilai'] > 0 ? number_format($d['balado_nilai'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- 4. PENGGUNAAN ROLL --}}
                            <td style="text-align: right;">{{ $d['roll_kk_kg'] > 0 ? number_format($d['roll_kk_kg'], 2, ',', '.') : '-' }}</td>
                            <td style="text-align: right;">{{ $d['roll_mk_kg'] > 0 ? number_format($d['roll_mk_kg'], 2, ',', '.') : '-' }}</td>
                            <td style="text-align: right; font-weight: 700; color: #065f46;">
                                {{ $d['roll_nilai'] > 0 ? number_format($d['roll_nilai'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- 5. LAKBAN --}}
                            <td style="text-align: right;">{{ $d['lakban_atas_gr'] > 0 ? number_format($d['lakban_atas_gr'], 0, ',', '.') : '-' }}</td>
                            <td style="text-align: right;">{{ $d['lakban_bawah_gr'] > 0 ? number_format($d['lakban_bawah_gr'], 0, ',', '.') : '-' }}</td>
                            <td style="text-align: right; font-weight: 600;">{{ $d['lakban_jumlah_gr'] > 0 ? number_format($d['lakban_jumlah_gr'], 0, ',', '.') : '-' }}</td>
                            <td style="text-align: right; font-weight: 700; color: #0e7490;">
                                {{ $d['lakban_nilai'] > 0 ? number_format($d['lakban_nilai'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- 6. KERTAS EXPIRED --}}
                            <td style="text-align: right;">{{ $d['kertas_exp_qty'] > 0 ? number_format($d['kertas_exp_qty'], 0, ',', '.') : '-' }}</td>
                            <td style="text-align: right; color: #0369a1;">
                                {{ $d['kertas_exp_nilai'] > 0 ? number_format($d['kertas_exp_nilai'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- 7. KERUSAKAN PLASTIK --}}
                            <td style="text-align: right;">{{ $d['kerusakan_plastik_qty'] > 0 ? number_format($d['kerusakan_plastik_qty'], 1, ',', '.') : '-' }}</td>
                            <td style="text-align: right; color: #991b1b;">
                                {{ $d['kerusakan_plastik_nilai'] > 0 ? number_format($d['kerusakan_plastik_nilai'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- 8. KERUSAKAN KARTON --}}
                            <td style="text-align: right;">{{ $d['kerusakan_karton_qty'] > 0 ? number_format($d['kerusakan_karton_qty'], 0, ',', '.') : '-' }}</td>
                            <td style="text-align: right; color: #9f1239;">
                                {{ $d['kerusakan_karton_nilai'] > 0 ? number_format($d['kerusakan_karton_nilai'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- 9. TOTAL BIAYA (KOLOM EMAS) --}}
                            <td style="text-align: right; font-weight: 800; background: #fefce8; color: #713f12; font-size: 0.825rem;">
                                {{ number_format($d['total_biaya_produksi'], 0, ',', '.') }}
                            </td>

                            {{-- 10. HASIL PRODUKSI --}}
                            <td style="text-align: right; color: #6b21a8;">{{ $d['hasil_ping_ping'] > 0 ? number_format($d['hasil_ping_ping'], 0, ',', '.') : '-' }}</td>
                            <td style="text-align: right; color: #6b21a8;">{{ $d['hasil_maksi'] > 0 ? number_format($d['hasil_maksi'], 0, ',', '.') : '-' }}</td>
                            <td style="text-align: right; font-weight: 800; color: #581c87; background: #faf5ff;">
                                {{ number_format($d['hasil_jumlah'], 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a;">
                                {{ $d['gramasi_kg'] > 0 ? number_format($d['gramasi_kg'], 2, ',', '.') : '-' }}
                            </td>

                            {{-- 11. HPP PER UNIT --}}
                            <td style="text-align: right; font-weight: 800; background: #f0fdf4; color: #166534; font-family: monospace;">
                                {{ number_format($d['hpp_per_unit'], 0, ',', '.') }}
                            </td>

                            {{-- 12. AKSI --}}
                            <td style="text-align: center; position: relative;">
                                @if(!empty($d['primary_pack_id']))
                                    @php
                                        $spPckMenuId = 'menu-sp-pck-' . $d['primary_pack_id'];
                                        $docCountSp = $d['count_dokumen'] ?? count($d['pack_records'] ?? []);
                                    @endphp
                                    <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $spPckMenuId }}')">
                                        <span>Aksi</span>
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <div id="{{ $spPckMenuId }}" class="action-dropdown-menu">
                                        @if($docCountSp > 1 && !empty($d['pack_records']))
                                            <div style="padding: 0.35rem 0.75rem; font-size: 0.675rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; background: #f8fafc; border-bottom: 1px solid #f1f5f9;">
                                                Detail Dokumen Kemas ({{ $docCountSp }})
                                            </div>
                                            @foreach($d['pack_records'] as $pRec)
                                                <a href="{{ route('produksi.packaging.show', $pRec->pack_id) }}" class="action-dropdown-item" style="display: flex; align-items: flex-start; gap: 0.5rem; padding: 0.5rem 0.75rem;">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-top: 2px; flex-shrink: 0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    <div style="display: flex; flex-direction: column; text-align: left; line-height: 1.25;">
                                                        <span style="font-weight: 700; color: #0f172a; font-size: 0.8rem;">{{ $pRec->pack_no }}</span>
                                                        <span style="font-size: 0.72rem; color: #0284c7; font-weight: 600; margin-top: 0.15rem;">
                                                            {{ number_format($pRec->hasil_karton_qty, 0) }} Ktn &bull; Batch {{ $pRec->batch_no ?? '-' }}
                                                        </span>
                                                    </div>
                                                </a>
                                            @endforeach

                                            <div class="action-dropdown-divider"></div>
                                            <a href="{{ route('produksi.packaging.index', ['pack_tgl' => $d['date']]) }}" class="action-dropdown-item">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                                <span>Riwayat Tanggal Ini</span>
                                            </a>

                                            @if(Auth::user()->canCreateProduksi())
                                                <div class="action-dropdown-divider"></div>
                                                <button type="button" class="action-dropdown-item danger-item" onclick="openVoidPackagingModal({{ $d['primary_pack_id'] }}, '{{ $d['primary_pack_no'] }}', '{{ addslashes($d['primary_product_nm'] ?? '') }}')">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    <span>Batalkan (Void)</span>
                                                </button>
                                            @endif
                                        @else
                                            <a href="{{ route('produksi.packaging.show', $d['primary_pack_id']) }}" class="action-dropdown-item">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span>Detail Dokumen</span>
                                            </a>
                                            @if(Auth::user()->canCreateProduksi())
                                                <div class="action-dropdown-divider"></div>
                                                <button type="button" class="action-dropdown-item danger-item" onclick="openVoidPackagingModal({{ $d['primary_pack_id'] }}, '{{ $d['primary_pack_no'] }}', '{{ addslashes($d['primary_product_nm'] ?? '') }}')">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    <span>Batalkan (Void)</span>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                @else
                                    <span style="color: #cbd5e1;">-</span>
                                @endif
                            </td>
                        </tr>
                    @else
                        {{-- HARI TANPA PRODUKSI --}}
                        <tr style="background: #fbfcfd; color: #cbd5e1;">
                            <td style="text-align: center; color: #94a3b8;">{{ $d['hari_nm'] }}</td>
                            <td style="text-align: center; font-size: 0.775rem; color: #94a3b8;">{{ date('d/m/y', strtotime($d['date'])) }}</td>
                            @for ($col = 1; $col <= 27; $col++)
                                <td style="text-align: center; color: #e2e8f0;">-</td>
                            @endfor
                            <td style="text-align: center; color: #cbd5e1;">-</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>

            {{-- 3. FOOTER: BARIS TOTAL & RATA-RATA PERSIS SEPERTI EXCEL MIRASA --}}
            <tfoot>
                {{-- BARIS 1: TOTAL BULAN INI --}}
                <tr style="background: #fef08a; font-weight: 800; border-top: 2px solid #ca8a04; color: #713f12;">
                    <td colspan="2" style="text-align: center; letter-spacing: 0.05em; font-size: 0.8rem;">
                        TOTAL
                    </td>

                    {{-- Total Bahan Keripik --}}
                    <td style="text-align: right;">{{ number_format($tot['tot_berko_kg'] ?? 0, 2, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($tot['tot_wip_kg'] ?? 0, 2, ',', '.') }}</td>
                    <td style="text-align: right; color: #713f12;">{{ number_format($tot['tot_bahan_keripik_nilai'] ?? 0, 0, ',', '.') }}</td>

                    {{-- Total Karton --}}
                    <td style="text-align: right;">{{ number_format($tot['tot_karton_kk'] ?? 0, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($tot['tot_karton_mk'] ?? 0, 0, ',', '.') }}</td>
                    <td style="text-align: right; color: #713f12;">{{ number_format($tot['tot_karton_nilai'] ?? 0, 0, ',', '.') }}</td>

                    {{-- Total Bumbu Balado --}}
                    <td style="text-align: right;">{{ number_format($tot['tot_balado_kg'] ?? 0, 2, ',', '.') }}</td>
                    <td style="text-align: right; color: #713f12;">{{ number_format($tot['tot_balado_nilai'] ?? 0, 0, ',', '.') }}</td>

                    {{-- Total Roll Kemasan --}}
                    <td style="text-align: right;">{{ number_format($tot['tot_roll_kk_kg'] ?? 0, 2, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($tot['tot_roll_mk_kg'] ?? 0, 2, ',', '.') }}</td>
                    <td style="text-align: right; color: #713f12;">{{ number_format($tot['tot_roll_nilai'] ?? 0, 0, ',', '.') }}</td>

                    {{-- Total Lakban --}}
                    <td style="text-align: right;">{{ number_format($tot['tot_lakban_atas'] ?? 0, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($tot['tot_lakban_bawah'] ?? 0, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($tot['tot_lakban_jumlah'] ?? 0, 0, ',', '.') }}</td>
                    <td style="text-align: right; color: #713f12;">{{ number_format($tot['tot_lakban_nilai'] ?? 0, 0, ',', '.') }}</td>

                    {{-- Total Kertas Expired --}}
                    <td style="text-align: right;">{{ number_format($tot['tot_kertas_exp_qty'] ?? 0, 0, ',', '.') }}</td>
                    <td style="text-align: right; color: #713f12;">{{ number_format($tot['tot_kertas_exp_nilai'] ?? 0, 0, ',', '.') }}</td>

                    {{-- Total Kerusakan Plastik --}}
                    <td style="text-align: right;">{{ number_format($tot['tot_kerusakan_plastik_qty'] ?? 0, 1, ',', '.') }}</td>
                    <td style="text-align: right; color: #713f12;">{{ number_format($tot['tot_kerusakan_plastik_nilai'] ?? 0, 0, ',', '.') }}</td>

                    {{-- Total Kerusakan Karton --}}
                    <td style="text-align: right;">{{ number_format($tot['tot_kerusakan_karton_qty'] ?? 0, 0, ',', '.') }}</td>
                    <td style="text-align: right; color: #713f12;">{{ number_format($tot['tot_kerusakan_karton_nilai'] ?? 0, 0, ',', '.') }}</td>

                    {{-- GRAND TOTAL BIAYA --}}
                    <td style="text-align: right; font-size: 0.85rem; font-weight: 900; background: #fde047; color: #713f12;">
                        {{ number_format($tot['total_biaya_produksi'] ?? 0, 0, ',', '.') }}
                    </td>

                    {{-- Total Hasil Produksi --}}
                    <td style="text-align: right;">{{ number_format($tot['tot_hasil_ping_ping'] ?? 0, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($tot['tot_hasil_maksi'] ?? 0, 0, ',', '.') }}</td>
                    <td style="text-align: right; font-size: 0.825rem; font-weight: 900;">{{ number_format($tot['tot_hasil_jumlah'] ?? 0, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($tot['tot_gramasi_kg'] ?? 0, 2, ',', '.') }}</td>

                    {{-- Rata-Rata HPP Unit --}}
                    <td style="text-align: right; font-family: monospace; font-size: 0.825rem; background: #a7f3d0; color: #064e3b;">
                        {{ number_format($tot['rata_hpp_unit'] ?? 0, 0, ',', '.') }}
                    </td>

                    <td></td>
                </tr>

                {{-- BARIS 2: RATA-RATA --}}
                <tr style="background: #f8fafc; font-weight: 700; color: #334155; border-bottom: 2px solid #cbd5e1;">
                    <td colspan="2" style="text-align: center; letter-spacing: 0.05em; font-size: 0.8rem;">
                        RATA - RATA
                    </td>

                    {{-- Rata-Rata Bahan Keripik --}}
                    <td style="text-align: right;">{{ number_format($avg['avg_berko_kg'] ?? 0, 2, ',', '.') }}</td>
                    <td style="text-align: right;">-</td>
                    <td style="text-align: right; font-weight: 700;">{{ number_format($avg['avg_bahan_nilai'] ?? 0, 0, ',', '.') }}</td>

                    {{-- Rata-Rata Karton --}}
                    <td style="text-align: right;">-</td>
                    <td style="text-align: right;">-</td>
                    <td style="text-align: right; font-weight: 700;">{{ number_format($avg['avg_karton_nilai'] ?? 0, 0, ',', '.') }}</td>

                    {{-- Rata-Rata Bumbu --}}
                    <td style="text-align: right;">-</td>
                    <td style="text-align: right;">-</td>

                    {{-- Rata-Rata Roll --}}
                    <td style="text-align: right;">-</td>
                    <td style="text-align: right;">-</td>
                    <td style="text-align: right;">-</td>

                    {{-- Rata-Rata Lakban --}}
                    <td style="text-align: right;">-</td>
                    <td style="text-align: right;">-</td>
                    <td style="text-align: right;">-</td>
                    <td style="text-align: right;">-</td>

                    {{-- Rata-Rata Expired & Kerusakan --}}
                    <td style="text-align: right;">-</td>
                    <td style="text-align: right;">-</td>
                    <td style="text-align: right;">-</td>
                    <td style="text-align: right;">-</td>
                    <td style="text-align: right;">-</td>
                    <td style="text-align: right;">-</td>

                    {{-- RATA TOTAL BIAYA --}}
                    <td style="text-align: right; font-weight: 800; background: #fefce8; color: #713f12;">
                        {{ number_format($avg['avg_total_biaya'] ?? 0, 0, ',', '.') }}
                    </td>

                    {{-- Rata-Rata Hasil Produksi --}}
                    <td style="text-align: right;">-</td>
                    <td style="text-align: right;">-</td>
                    <td style="text-align: right; font-weight: 800;">{{ number_format($avg['avg_hasil_jumlah'] ?? 0, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($avg['avg_gramasi_kg'] ?? 0, 2, ',', '.') }}</td>

                    {{-- RATA HPP UNIT --}}
                    <td style="text-align: right; font-family: monospace; font-weight: 800; background: #ecfdf5; color: #065f46;">
                        {{ number_format($avg['avg_hpp_unit'] ?? 0, 0, ',', '.') }}
                    </td>

                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
