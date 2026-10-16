<div class="card" style="margin-bottom: 2rem; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); overflow: hidden;">
    <div class="card-header" style="background: #ffffff; padding: 0.85rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.65rem;">
            <div>
                <span style="font-size: 0.925rem; font-weight: 700; color: #0f172a; letter-spacing: -0.01em;">
                    Rekapitulasi Harga Pokok Produksi (HPP) - Pengemasan &amp; Finish Good
                </span>
                <span style="font-size: 0.725rem; color: #64748b; margin-left: 0.4rem;">
                    Buku Jurnal Pengemasan Harian
                </span>
            </div>
            <span style="font-size: 0.725rem; background: #f0fdf4; color: #166534; padding: 0.2rem 0.55rem; border-radius: 4px; font-weight: 700; border: 1px solid #bbf7d0;">
                {{ $report['count'] }} Hari Kerja &bull; {{ $report['total_dokumen'] }} Dokumen PCK
            </span>
        </div>

        {{-- Switch Mode Tampilan (Ringkas vs Spreadsheet Asli) --}}
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div class="view-mode-pill-group">
                <button type="button" id="btn-mode-compact" class="btn-view-mode active" onclick="switchHppViewMode('compact')" title="Tampilan ringkas pas di layar dengan accordion detail">
                    <span>Ringkasan Eksekutif</span>
                </button>
                <button type="button" id="btn-mode-spreadsheet" class="btn-view-mode" onclick="switchHppViewMode('spreadsheet')" title="Tampilan spreadsheet matrix lengkap">
                    <span>Spreadsheet Lengkap</span>
                </button>
            </div>
        </div>
    </div>

    {{-- KONTEN TABEL RINGKAS --}}
    <table class="table-compact-hpp">
        <thead>
            <tr>
                <th style="width: 13%;">Tanggal &amp; Hari</th>
                <th style="width: 17%;">Target Produk &amp; Batch FG</th>
                <th style="width: 12%; text-align: right;">Biaya Curah WIP</th>
                <th style="width: 12%; text-align: right;">Biaya Kemasan</th>
                <th style="width: 10%; text-align: right;">Upah Packing</th>
                <th style="width: 13%; text-align: right;">Total Biaya Kemas</th>
                <th style="width: 9%; text-align: center;">Hasil (FG)</th>
                <th style="width: 11%; text-align: right;">HPP / Karton</th>
                <th style="width: 6%; text-align: center;">Audit</th>
                <th style="width: 7%; text-align: center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report['days'] as $d)
                @if ($d['has_data'])
                    @php
                        $dayId = 'pck-day-' . $d['day'];
                    @endphp

                    {{-- 1. BARIS DATA UTAMA --}}
                    <tr class="main-data-row" id="main-row-{{ $d['day'] }}">
                        {{-- Tanggal & Hari --}}
                        <td>
                            <div style="font-weight: 700; color: #0f172a; font-size: 0.825rem;">
                                {{ $d['hari_nm'] }}, {{ date('d M Y', strtotime($d['date'])) }}
                            </div>
                            <div style="display: flex; gap: 0.35rem; align-items: center; margin-top: 0.2rem;">
                                <span style="font-size: 0.65rem; font-weight: 700; padding: 0.1rem 0.35rem; border-radius: 3px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">
                                    {{ $d['count_dokumen'] }} Dokumen
                                </span>
                                <span style="font-size: 0.7rem; color: #64748b;">
                                    Tgl {{ $d['day'] }}
                                </span>
                            </div>
                        </td>

                        {{-- Target Produk & Batch --}}
                        <td>
                            <div style="font-size: 0.8rem; font-weight: 700; color: #0f172a; line-height: 1.25;">
                                {{ $d['primary_product_nm'] }}
                            </div>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.25rem; align-items: center; margin-top: 0.25rem;">
                                @foreach($d['batch_list'] as $bNo)
                                    @if(!empty($bNo) && $bNo !== '-')
                                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.685rem; font-weight: 700; color: #0369a1; background: #f0f9ff; border: 1px solid #bae6fd; padding: 1.5px 6px; border-radius: 4px; white-space: nowrap; line-height: 1.2;">
                                            <svg width="9" height="9" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7; flex-shrink: 0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                            <span>{{ $bNo }}</span>
                                        </span>
                                    @endif
                                @endforeach

                                @if(!empty($d['primary_pack_no']))
                                    <span style="font-size: 0.65rem; color: #64748b; font-family: monospace;">
                                        ({{ $d['primary_pack_no'] }})
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Biaya Curah WIP --}}
                        <td style="text-align: right;">
                            <div style="font-weight: 700; color: #0f172a; font-size: 0.825rem;">
                                Rp {{ number_format($d['total_biaya_wip'], 0, ',', '.') }}
                            </div>
                            <div style="font-size: 0.7rem; color: #64748b;">
                                {{ number_format($d['wip_kg_total'], 1, ',', '.') }} kg curah
                            </div>
                        </td>

                        {{-- Biaya Kemasan --}}
                        <td style="text-align: right;">
                            <div style="font-weight: 700; color: #0f172a; font-size: 0.825rem;">
                                Rp {{ number_format($d['total_biaya_kemasan'], 0, ',', '.') }}
                            </div>
                            <div style="font-size: 0.7rem; color: #64748b;">
                                Dus, Plastik, Lakban
                            </div>
                        </td>

                        {{-- Upah Packing --}}
                        <td style="text-align: right;">
                            <div style="font-weight: 700; color: #0f172a; font-size: 0.825rem;">
                                Rp {{ number_format($d['total_biaya_tenaga_kerja'], 0, ',', '.') }}
                            </div>
                            <div style="font-size: 0.7rem; color: #64748b;">
                                Borongan Kemas
                            </div>
                        </td>

                        {{-- Total Biaya Kemas --}}
                        <td style="text-align: right;">
                            <div style="font-weight: 800; color: #0f172a; font-size: 0.875rem;">
                                Rp {{ number_format($d['total_biaya_produksi'], 0, ',', '.') }}
                            </div>
                            <div style="font-size: 0.7rem; color: #0284c7; font-weight: 600;">
                                Akumulasi Biaya
                            </div>
                        </td>

                        {{-- Hasil Kemas --}}
                        <td style="text-align: center;">
                            <span style="display: inline-block; font-weight: 800; font-size: 0.85rem; color: #059669; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 2px 7px; border-radius: 4px;">
                                {{ number_format($d['hasil_karton'], 0, ',', '.') }} <small style="font-size: 0.7rem; font-weight: 600;">Ktn</small>
                            </span>
                            <div style="font-size: 0.685rem; color: #64748b; margin-top: 2px;">
                                {{ number_format($d['hasil_pcs'], 0, ',', '.') }} Pcs
                            </div>
                        </td>

                        {{-- HPP / Karton & Pcs --}}
                        <td style="text-align: right;">
                            <div style="font-family: ui-monospace, monospace; font-size: 0.875rem; font-weight: 800; color: #059669;">
                                Rp {{ number_format($d['hpp_per_karton'], 0, ',', '.') }}
                            </div>
                            <div style="font-size: 0.7rem; color: #64748b; font-family: monospace;">
                                Rp {{ number_format($d['hpp_per_pcs'], 0, ',', '.') }} / pcs
                            </div>
                        </td>

                        {{-- Tombol Expand Accordion Audit --}}
                        <td style="text-align: center;">
                            <button type="button" 
                                    class="btn-accordion-toggle" 
                                    id="btn-accordion-{{ $d['day'] }}"
                                    onclick="toggleCostAccordion({{ $d['day'] }}, this)" 
                                    title="Tampilkan rincian audit komponen biaya akuntansi pengemasan">
                                <span>Audit</span>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </td>

                        {{-- Smart Action Dropdown --}}
                        <td style="text-align: center; position: relative;">
                            @if(!empty($d['primary_pack_id']))
                                @php
                                    $pckMenuId = 'dropdown-pck-' . $d['primary_pack_id'];
                                @endphp
                                <button type="button" class="btn-action-trigger" onclick="toggleSmartActionDropdown(this, event, '{{ $pckMenuId }}')">
                                    <span>Aksi</span>
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div id="{{ $pckMenuId }}" class="action-dropdown-menu">
                                    @foreach($d['pack_records'] as $pRec)
                                        <a href="{{ route('produksi.packaging.show', $pRec->pack_id) }}" class="action-dropdown-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Lihat Berita Acara ({{ $pRec->pack_no }})</span>
                                        </a>
                                    @endforeach
                                    <div class="action-dropdown-divider"></div>
                                    <a href="{{ route('produksi.packaging.index', ['pack_tgl' => $d['date']]) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                        <span>Buka di Daftar Pengemasan</span>
                                    </a>
                                </div>
                            @else
                                <span style="color: #94a3b8;">-</span>
                            @endif
                        </td>
                    </tr>

                    {{-- 2. ACCORDION ROW (DETAIL RINCIAN KOMPONEN BIAYA KEMAS) --}}
                    <tr class="accordion-detail-row" id="detail-row-{{ $d['day'] }}">
                        <td colspan="10" class="accordion-detail-cell">
                            <div class="cost-breakdown-grid">
                                
                                {{-- KOMPARTEMEN 1: BAHAN KERIPIK & BUMBU BALADO --}}
                                <div class="mini-cost-card">
                                    <div class="mini-cost-header">
                                        <div class="mini-cost-title">
                                            <span>1. Bahan Keripik &amp; Bumbu Balado</span>
                                        </div>
                                        <span class="mini-cost-badge" style="background: #fefce8; color: #854d0e; border: 1px solid #fef08a;">Bahan Baku</span>
                                    </div>
                                    <div class="mini-cost-list">
                                        <div class="mini-cost-row">
                                            <span>Konsumsi Berko:</span>
                                            <strong>{{ number_format($d['berko_kg'], 2, ',', '.') }} kg</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Konsumsi WIP Curah:</span>
                                            <strong>{{ number_format($d['wip_kg'], 2, ',', '.') }} kg</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Nilai Bahan Keripik:</span>
                                            <strong style="color: #854d0e;">Rp {{ number_format($d['bahan_keripik_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Bumbu Balado (5% Berko):</span>
                                            <strong style="color: #9a3412;">{{ number_format($d['balado_kg'], 2, ',', '.') }} kg (Rp {{ number_format($d['balado_nilai'], 0, ',', '.') }})</strong>
                                        </div>
                                        <div class="mini-cost-row" style="border-top: 1px dashed #e2e8f0; padding-top: 3px; margin-top: 2px;">
                                            <span>Subtotal Bahan &amp; Bumbu:</span>
                                            <strong style="color: #0f172a;">Rp {{ number_format($d['bahan_keripik_nilai'] + $d['balado_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row" style="margin-top: 2px;">
                                            <span>Sumber Biaya:</span>
                                            <span style="font-size: 0.675rem; font-weight: 700; color: #166534; background: #dcfce7; padding: 1px 6px; border-radius: 4px; border: 1px solid #86efac;">
                                                ✓ Ditarik dari HPP Produksi WIP
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- KOMPARTEMEN 2: KEMASAN (DUS KARTON, ROLL, LAKBAN, EXP) --}}
                                <div class="mini-cost-card">
                                    <div class="mini-cost-header">
                                        <div class="mini-cost-title">
                                            <span>2. Bahan Kemasan (Packaging)</span>
                                        </div>
                                        <span class="mini-cost-badge" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe;">Dus &amp; Roll</span>
                                    </div>
                                    <div class="mini-cost-list">
                                        <div class="mini-cost-row">
                                            <span>Karton XX / MX:</span>
                                            <strong>XX: {{ number_format($d['karton_kk_qty'], 0, ',', '.') }} | MX: {{ number_format($d['karton_mk_qty'], 0, ',', '.') }} dus</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Nilai Karton Dus:</span>
                                            <strong style="color: #92400e;">Rp {{ number_format($d['karton_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Roll Plastik (Kg):</span>
                                            <strong>XX: {{ number_format($d['roll_kk_kg'], 2, ',', '.') }}kg | MX: {{ number_format($d['roll_mk_kg'], 2, ',', '.') }}kg</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Nilai Roll Kemasan:</span>
                                            <strong style="color: #065f46;">Rp {{ number_format($d['roll_nilai'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Lakban Segel (Gram):</span>
                                            <strong>Atas {{ number_format($d['lakban_atas_gr'], 0, ',', '.') }}g | Bwh {{ number_format($d['lakban_bawah_gr'], 0, ',', '.') }}g (Rp {{ number_format($d['lakban_nilai'], 0, ',', '.') }})</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Kertas Expired:</span>
                                            <strong>{{ number_format($d['kertas_exp_qty'], 0, ',', '.') }} bks (Rp {{ number_format($d['kertas_exp_nilai'], 0, ',', '.') }})</strong>
                                        </div>
                                        <div class="mini-cost-row" style="border-top: 1px dashed #e2e8f0; padding-top: 3px; margin-top: 2px;">
                                            <span>Subtotal Biaya Kemasan:</span>
                                            <strong style="color: #0284c7;">Rp {{ number_format($d['total_biaya_kemasan'], 0, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                </div>

                                {{-- KOMPARTEMEN 3: KERUSAKAN BAHAN & UPAH PACKING --}}
                                <div class="mini-cost-card">
                                    <div class="mini-cost-header">
                                        <div class="mini-cost-title">
                                            <span>3. Reject &amp; Biaya Kemas</span>
                                        </div>
                                        <span class="mini-cost-badge" style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;">Reject &amp; Upah</span>
                                    </div>
                                    <div class="mini-cost-list">
                                        <div class="mini-cost-row">
                                            <span>Kerusakan Plastik:</span>
                                            <strong style="color: #991b1b;">{{ number_format($d['kerusakan_plastik_qty'], 1, ',', '.') }} gr (Rp {{ number_format($d['kerusakan_plastik_nilai'], 0, ',', '.') }})</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Kerusakan Karton:</span>
                                            <strong style="color: #9f1239;">{{ number_format($d['kerusakan_karton_qty'], 0, ',', '.') }} pcs (Rp {{ number_format($d['kerusakan_karton_nilai'], 0, ',', '.') }})</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Upah Operator Packing:</span>
                                            <strong style="color: #b45309;">Rp {{ number_format($d['total_biaya_tenaga_kerja'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Biaya Overhead/Lain:</span>
                                            <strong>Rp {{ number_format($d['total_biaya_lain'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row" style="border-top: 1px dashed #e2e8f0; padding-top: 3px; margin-top: 2px;">
                                            <span>Subtotal Reject &amp; Upah:</span>
                                            <strong style="color: #0f172a;">Rp {{ number_format($d['kerusakan_plastik_nilai'] + $d['kerusakan_karton_nilai'] + $d['total_biaya_tenaga_kerja'] + $d['total_biaya_lain'], 0, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                </div>

                                {{-- KOMPARTEMEN 4: REKAPITULASI OUTPUT & HPP UNIT --}}
                                <div class="mini-cost-card" style="background: #fafaf9; border-color: #d6d3d1;">
                                    <div class="mini-cost-header">
                                        <div class="mini-cost-title">
                                            <span>4. Rekapitulasi Output &amp; HPP</span>
                                        </div>
                                        <span class="mini-cost-badge" style="background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;">Finish Good (FG)</span>
                                    </div>
                                    <div class="mini-cost-list">
                                        <div class="mini-cost-row">
                                            <span>Hasil Ping-Ping:</span>
                                            <strong style="color: #6b21a8;">{{ number_format($d['hasil_ping_ping'], 0, ',', '.') }} Pcs</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Hasil Maksi:</span>
                                            <strong style="color: #6b21a8;">{{ number_format($d['hasil_maksi'], 0, ',', '.') }} Pcs</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Total Output Jadi:</span>
                                            <strong style="color: #059669;">{{ number_format($d['hasil_jumlah'], 0, ',', '.') }} Pcs ({{ number_format($d['hasil_karton'], 0, ',', '.') }} Karton)</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Gramasi Bersih:</span>
                                            <strong>{{ number_format($d['gramasi_kg'], 2, ',', '.') }} kg</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span>Total Biaya Pengemasan:</span>
                                            <strong style="font-size: 0.875rem; color: #713f12; background: #fef9c3; padding: 1px 4px; border-radius: 3px;">Rp {{ number_format($d['total_biaya_produksi'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row" style="border-top: 1.5px dashed #cbd5e1; padding-top: 4px; margin-top: 4px;">
                                            <span style="font-weight: 800; color: #059669;">HPP Riil per Pcs:</span>
                                            <strong style="font-family: monospace; font-size: 0.95rem; color: #059669;">Rp {{ number_format($d['hpp_per_unit'], 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mini-cost-row">
                                            <span style="font-weight: 700; color: #64748b; font-size: 0.72rem;">Setara per Karton:</span>
                                            <strong style="font-family: monospace; font-size: 0.8rem; color: #64748b;">Rp {{ number_format($d['hpp_per_karton'], 0, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </td>
                    </tr>
                @else
                    {{-- BARIS TANGGAL LIBUR / TANPA AKTIVITAS PENGEMASAN --}}
                    <tr style="background: #fbfcfd; color: #94a3b8;">
                        <td>
                            <div style="font-weight: 600; color: #94a3b8; font-size: 0.8rem;">
                                {{ $d['hari_nm'] }}, {{ date('d M Y', strtotime($d['date'])) }}
                            </div>
                            <span style="font-size: 0.685rem; color: #cbd5e1;">Tgl {{ $d['day'] }}</span>
                        </td>
                        <td colspan="7" style="text-align: center; font-size: 0.775rem; color: #94a3b8; font-style: italic;">
                            &mdash; Tidak ada jadwal proses pengemasan &mdash;
                        </td>
                        <td style="text-align: center; color: #cbd5e1;">-</td>
                        <td style="text-align: center; color: #cbd5e1;">-</td>
                    </tr>
                @endif
            @endforeach
        </tbody>

        {{-- FOOTER REKAPITULASI TOTAL BULANAN --}}
        @php
            $tot = $report['totals'];
        @endphp
        <tfoot>
            <tr style="background: #f8fafc; font-weight: 800; border-top: 2px solid #cbd5e1;">
                <td colspan="2" style="font-size: 0.85rem; color: #0f172a; text-transform: uppercase;">
                    TOTAL KONSOLIDASI BULAN {{ strtoupper($monthName) }} {{ $year }}
                </td>
                <td style="text-align: right; color: #0f172a; font-size: 0.85rem;">
                    Rp {{ number_format($tot['total_biaya_wip'], 0, ',', '.') }}
                </td>
                <td style="text-align: right; color: #0f172a; font-size: 0.85rem;">
                    Rp {{ number_format($tot['total_biaya_kemasan'], 0, ',', '.') }}
                </td>
                <td style="text-align: right; color: #0f172a; font-size: 0.85rem;">
                    Rp {{ number_format($tot['total_biaya_tenaga_kerja'], 0, ',', '.') }}
                </td>
                <td style="text-align: right; color: #0f172a; font-size: 0.95rem;">
                    Rp {{ number_format($tot['total_biaya_produksi'], 0, ',', '.') }}
                </td>
                <td style="text-align: center; color: #059669; font-size: 0.9rem;">
                    {{ number_format($tot['total_karton'], 0, ',', '.') }} Ktn
                </td>
                <td style="text-align: right; font-family: monospace; font-size: 0.95rem; color: #059669;">
                    Rp {{ number_format($tot['rata_hpp_karton'], 0, ',', '.') }}
                </td>
                <td colspan="2" style="text-align: center; font-size: 0.75rem; color: #64748b;">
                    Rp {{ number_format($tot['rata_hpp_pcs'], 0, ',', '.') }} / pcs
                </td>
            </tr>
        </tfoot>
    </table>
</div>
