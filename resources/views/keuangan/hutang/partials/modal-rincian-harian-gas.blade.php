{{-- ========================================================
     MODAL: RINCIAN PEMAKAIAN HARIAN PABRIK (POP-UP DIALOG)
     Lokasi: resources/views/keuangan/hutang/partials/modal-rincian-harian-gas.blade.php
     ======================================================== --}}
<div id="modalRincianHarian" class="bkk-modal-backdrop" aria-hidden="true">
    <div class="gas-modal-dialog-wide" role="dialog" aria-modal="true" aria-labelledby="modalRincianTitle">
        <div class="gas-modal-header">
            <div class="gas-modal-title-wrap">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <div class="gas-modal-title-group">
                    <h3 class="gas-modal-title" id="modalRincianTitle">Log Rincian Meteran Harian Pabrik (Periode {{ $periode }})</h3>
                    <p class="gas-modal-subtitle">Ditarik otomatis dari pencatatan SPK dapur penggorengan pabrik (dat_produksi_hdr).</p>
                </div>
                <span class="bkk-pill-badge bkk-pill-count">{{ count($gasSummary['daily_details']) }} Baris</span>
            </div>
            <button type="button" class="btn-modal-close" onclick="closeModalRincianHarian()" aria-label="Tutup dialog">&times;</button>
        </div>

        <div class="gas-modal-body gas-modal-body-p0">
            {{-- Toolbar Filter & Pencarian Tanggal/Shift --}}
            <div class="modal-table-toolbar">
                <div class="gas-search-wrap">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text"
                           id="inputTableSearch"
                           placeholder="Cari tanggal, no SPK, lini produksi..."
                           class="gas-search-field"
                           autocomplete="off">
                    <button type="button" id="btnClearSearch" class="btn-search-clear" aria-label="Hapus pencarian">&times;</button>
                </div>

                <div class="gas-shift-pills">
                    <button type="button" class="btn-shift-tag active" data-shift="ALL">Semua Shift</button>
                    <button type="button" class="btn-shift-tag" data-shift="1">Shift 1</button>
                    <button type="button" class="btn-shift-tag" data-shift="2">Shift 2</button>
                    <button type="button" class="btn-shift-tag" data-shift="3">Shift 3</button>
                </div>

                <span class="gas-table-rowcount" id="tableRowCount">
                    {{ count($gasSummary['daily_details']) }} Baris
                </span>
            </div>

            {{-- Tabel Data dengan Scroll Internal --}}
            <div class="gas-table-box-scroll">
                <table class="gas-data-table" id="tablePemakaianHarian">
                    <thead>
                        <tr>
                            <th class="th-no">No</th>
                            <th class="th-tgl">Tanggal</th>
                            <th class="th-spk">No SPK / Batch</th>
                            <th class="th-shift">Shift</th>
                            <th class="th-lini">Lini Produksi</th>
                            <th class="th-num th-nm3">Volume (Nm³)</th>
                            <th class="th-num th-mmbtu">Energi (MMBTU)</th>
                            <th class="th-num th-kurs">Kurs JISDOR</th>
                            <th class="th-num th-rp">Estimasi Akrual (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($gasSummary['daily_details'] as $idx => $d)
                            <tr class="gas-table-row" data-shift="{{ $d['shift_cd'] }}">
                                <td class="col-row-num text-center">{{ $idx + 1 }}</td>
                                <td>
                                    <div class="table-tgl-main">{{ $d['tgl_formatted'] }}</div>
                                    <div class="table-tgl-sub">{{ $d['hari_nm'] }}</div>
                                </td>
                                <td>
                                    <span class="table-spk-pill">{{ $d['produksi_no'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="table-shift-badge">Shift {{ $d['shift_cd'] }}</span>
                                </td>
                                <td>
                                    <span class="table-lini-text">{{ $d['lini_produksi'] }}</span>
                                </td>
                                <td class="gas-cell-num">{{ number_format($d['cng_nm3'], 2) }}</td>
                                <td class="gas-cell-num font-bold">{{ number_format($d['cng_mmbtu'], 4) }}</td>
                                <td class="gas-cell-num text-slate-500">
                                    Rp {{ number_format($d['cng_kurs_jisdor'] > 0 ? $d['cng_kurs_jisdor'] : ($gasSummary['avg_kurs_jisdor'] > 0 ? $gasSummary['avg_kurs_jisdor'] : 16000), 0, ',', '.') }}
                                </td>
                                <td class="gas-cell-num font-bold cell-rp-highlight">
                                    Rp {{ number_format($d['cng_nilai'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyInitialRow">
                                <td colspan="9" class="gas-table-empty">
                                    Tidak ditemukan data pemakaian gas pada bulan {{ $periode }}. Pastikan SPK produksi penggorengan di bulan ini sudah dicatat.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="noFilterMatchRow" class="is-hidden">
                            <td colspan="9" class="gas-table-empty">
                                Tidak ada data yang sesuai dengan kata kunci pencarian.
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="tfoot-total-label">TOTAL ACUAN PABRIK:</td>
                            <td class="gas-cell-num font-bold" id="footTotalNm3">{{ number_format($gasSummary['total_cng_nm3'], 2) }} Nm³</td>
                            <td class="gas-cell-num font-bold" id="footTotalMmbtu">{{ number_format($gasSummary['total_cng_mmbtu'], 4) }} MMBTU</td>
                            <td class="gas-cell-num text-slate-500">-</td>
                            <td class="gas-cell-num font-bold cell-rp-highlight" id="footTotalRp">
                                Rp {{ number_format($gasSummary['estimasi_biaya'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="gas-modal-footer">
            <span class="modal-footer-hint">Data pemakaian ditarik otomatis dari SPK dapur penggorengan pabrik.</span>
            <button type="button" class="btn-dock-cancel" onclick="closeModalRincianHarian()">
                Tutup Rincian
            </button>
        </div>
    </div>
</div>
