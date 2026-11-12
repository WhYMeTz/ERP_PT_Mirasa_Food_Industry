{{-- 
    LEMBAR LAJUR KONTINU / SPREADSHEET KLASIK JURNAL UMUM
    Format: Rekap multi-kolom horizontal kontinu (CV Bahtera Mandiri Bersama Style)
    Sesuai Pedoman Pemisahan Berkas & antislop
--}}

<div class="sheet-outer-container">
    {{-- TOOLBAR CONTROL SPREADSHEET --}}
    <div class="sheet-control-bar no-print">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <span class="sheet-status-indicator">
                <span class="sheet-status-dot"></span>
                Mode Lembar Lajur Spreadsheet Aktif
            </span>
            <span style="font-size: 0.75rem; color: #64748b;">
                Menampilkan <strong>{{ $jurnals->total() }}</strong> voucher jurnal ({{ $jurnals->sum(fn($j) => $j->details->count()) }} baris akun pada halaman ini)
            </span>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <button type="button" 
                    class="btn btn-secondary" 
                    onclick="exportSpreadsheetToCsv()" 
                    style="font-size: 0.8125rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.4rem 0.8rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export Excel / CSV</span>
            </button>

            <button type="button" 
                    class="btn btn-primary" 
                    onclick="window.print()" 
                    style="font-size: 0.8125rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.4rem 0.85rem; background: #166534; border-color: #166534;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Lembar Lajur A4</span>
            </button>
        </div>
    </div>

    {{-- KOP SPREADSHEET HIJAU KLASIK (EXCEL THEME) --}}
    <div class="sheet-header-banner">
        <div class="sheet-banner-company">PT MIRASA FOOD INDUSTRY</div>
        <div class="sheet-banner-title">JURNAL UMUM</div>
        @if (!empty($filters['start_date']) || !empty($filters['end_date']))
            <div class="sheet-banner-period">
                Periode: {{ !empty($filters['start_date']) ? \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') : 'Awal' }} 
                s/d 
                {{ !empty($filters['end_date']) ? \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') : 'Sekarang' }}
            </div>
        @endif
    </div>

    {{-- TABEL SPREADSHEET KONTINU --}}
    <div class="sheet-table-scroll-wrapper">
        <table class="spreadsheet-table" id="spreadsheetJurnalTable">
            <thead>
                <tr>
                    <th style="width: 80px;" class="col-tgl">
                        <div class="th-content">
                            <span>TGL</span>
                            <span class="excel-filter-arrow">▼</span>
                        </div>
                    </th>
                    <th style="min-width: 210px;" class="col-nama-akun">
                        <div class="th-content">
                            <span>NAMA AKUN</span>
                            <span class="excel-filter-arrow">▼</span>
                        </div>
                    </th>
                    <th style="width: 80px; text-align: center;" class="col-no-akun">
                        <div class="th-content" style="justify-content: center;">
                            <span>NO AKUN</span>
                            <span class="excel-filter-arrow">▼</span>
                        </div>
                    </th>
                    <th style="min-width: 260px;" class="col-keterangan">
                        <div class="th-content">
                            <span>KETERANGAN</span>
                            <span class="excel-filter-arrow">▼</span>
                        </div>
                    </th>
                    <th style="width: 120px; text-align: right;" class="col-debet">
                        <div class="th-content" style="justify-content: flex-end;">
                            <span>DEBET</span>
                            <span class="excel-filter-arrow">▼</span>
                        </div>
                    </th>
                    <th style="width: 120px; text-align: right;" class="col-kredit">
                        <div class="th-content" style="justify-content: flex-end;">
                            <span>KREDIT</span>
                            <span class="excel-filter-arrow">▼</span>
                        </div>
                    </th>
                    <th style="width: 120px; text-align: right;" class="col-saldo">
                        <div class="th-content" style="justify-content: flex-end;">
                            <span>SALDO</span>
                            <span class="excel-filter-arrow">▼</span>
                        </div>
                    </th>
                    <th style="width: 55px; text-align: center;" class="col-jp">
                        <div class="th-content" style="justify-content: center;">
                            <span>JP</span>
                            <span class="excel-filter-arrow">▼</span>
                        </div>
                    </th>
                    <th style="width: 60px; text-align: center;" class="col-bulan">
                        <div class="th-content" style="justify-content: center;">
                            <span>BULAN</span>
                            <span class="excel-filter-arrow">▼</span>
                        </div>
                    </th>
                    <th style="width: 75px; text-align: center;" class="col-sn">
                        <div class="th-content" style="justify-content: center;">
                            <span>SN</span>
                            <span class="excel-filter-arrow">▼</span>
                        </div>
                    </th>
                    <th style="width: 95px; text-align: center;" class="col-posisi">
                        <div class="th-content" style="justify-content: center;">
                            <span>POSISI</span>
                            <span class="excel-filter-arrow">▼</span>
                        </div>
                    </th>
                    <th style="width: 110px; text-align: center;" class="col-kepala">
                        <div class="th-content" style="justify-content: center;">
                            <span>KEPALA AKUN</span>
                            <span class="excel-filter-arrow">▼</span>
                        </div>
                    </th>
                    <th style="width: 65px; text-align: center;" class="col-tahun">
                        <div class="th-content" style="justify-content: center;">
                            <span>TAHUN</span>
                            <span class="excel-filter-arrow">▼</span>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @php
                    $pageDebet = 0;
                    $pageKredit = 0;
                @endphp

                @forelse ($jurnals as $vIndex => $j)
                    @php
                        $isOddVoucher = ($vIndex % 2 !== 0);
                        $groupClass = $isOddVoucher ? 'voucher-tint-blue' : 'voucher-tint-white';
                        $tglStr = \Carbon\Carbon::parse($j->tgl_jurnal)->format('d-M');
                        $bulanStr = \Carbon\Carbon::parse($j->tgl_jurnal)->format('M');
                        $tahunStr = \Carbon\Carbon::parse($j->tgl_jurnal)->format('Y');

                        $jpCode = match($j->tipe_transaksi) {
                            'HUTANG_PEMBELIAN'   => 'GRN',
                            'BAYAR_HUTANG'       => 'BKK',
                            'KAS_MASUK'          => 'BKM',
                            'PEMAKAIAN_PRODUKSI' => 'BPPB',
                            'JURNAL_PENYESUAIAN' => 'ADJ',
                            'SALDO_AWAL'         => 'SA',
                            default              => 'JU',
                        };

                        $pageDebet += $j->total_debet;
                        $pageKredit += $j->total_kredit;
                    @endphp

                    @foreach ($j->details as $dIndex => $d)
                        @php
                            $isFirstInVoucher = $loop->first;
                            $isLastInVoucher = $loop->last;

                            $sn = strtoupper($d->coa?->saldo_normal ?? 'DEBET');
                            $saldoVal = ($sn === 'DEBET') ? ($d->debet - $d->kredit) : ($d->kredit - $d->debet);
                            $saldoText = ($saldoVal < 0) ? '(' . number_format(abs($saldoVal), 0, ',', '.') . ')' : number_format($saldoVal, 0, ',', '.');
                            $posisi = ($d->coa?->posisi_keuangan === 'LABA_RUGI') ? 'LABA/RUGI' : ($d->coa?->posisi_keuangan ?? 'NERACA');
                            $kepala = $d->coa?->kategori_akun ?? 'AKTIVA';
                            $keteranganLine = $d->keterangan ?: $j->keterangan;
                        @endphp

                        <tr class="sheet-row {{ $groupClass }} {{ $isLastInVoucher ? 'voucher-bottom-separator' : '' }}"
                            title="No. Jurnal: {{ $j->jurnal_no }} | Ref: {{ $j->ref_no ?: '-' }}">
                            {{-- TGL --}}
                            <td class="cell-tgl font-tabular" style="font-size: 0.8rem; font-weight: 600; color: #1e293b;">
                                {{ $tglStr }}
                            </td>

                            {{-- NAMA AKUN --}}
                            <td class="cell-nama-akun" style="font-size: 0.8125rem; font-weight: 600; color: #0f172a;">
                                {{ $d->coa?->nama_akun ?? 'Akun #' . $d->coa_id }}
                            </td>

                            {{-- NO AKUN --}}
                            <td class="cell-no-akun font-tabular" style="text-align: center; font-size: 0.8125rem; font-weight: 700; color: #0284c7;">
                                {{ $d->coa?->kode_akun ?? '-' }}
                            </td>

                            {{-- KETERANGAN --}}
                            <td class="cell-keterangan" style="font-size: 0.8rem; color: #334155;">
                                <div style="display: flex; flex-direction: column;">
                                    <span>{{ $keteranganLine }}</span>
                                    @if ($isFirstInVoucher && $j->ref_no)
                                        <span class="ref-subtext no-print">
                                            [Ref: {{ $j->ref_no }} &bull; {{ $j->jurnal_no }}]
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- DEBET --}}
                            <td class="cell-nominal font-tabular" style="text-align: right; font-weight: 600; color: {{ $d->debet > 0 ? '#0f172a' : '#94a3b8' }};">
                                {{ $d->debet > 0 ? number_format($d->debet, 0, ',', '.') : '-' }}
                            </td>

                            {{-- KREDIT --}}
                            <td class="cell-nominal font-tabular" style="text-align: right; font-weight: 600; color: {{ $d->kredit > 0 ? '#0f172a' : '#94a3b8' }};">
                                {{ $d->kredit > 0 ? number_format($d->kredit, 0, ',', '.') : '-' }}
                            </td>

                            {{-- SALDO --}}
                            <td class="cell-nominal font-tabular" style="text-align: right; font-weight: 700; color: {{ $saldoVal < 0 ? '#dc2626' : '#0f172a' }};">
                                {{ $saldoText }}
                            </td>

                            {{-- JP --}}
                            <td class="cell-center font-tabular" style="text-align: center; font-size: 0.75rem; font-weight: 700; color: #475569;">
                                {{ $jpCode }}
                            </td>

                            {{-- BULAN --}}
                            <td class="cell-center font-tabular" style="text-align: center; font-size: 0.75rem; color: #334155;">
                                {{ $bulanStr }}
                            </td>

                            {{-- SN --}}
                            <td class="cell-center font-tabular" style="text-align: center; font-size: 0.75rem; font-weight: 700; color: {{ $sn === 'DEBET' ? '#0369a1' : '#b45309' }};">
                                {{ $sn }}
                            </td>

                            {{-- POSISI --}}
                            <td class="cell-center" style="text-align: center; font-size: 0.75rem; font-weight: 700; color: #334155;">
                                {{ $posisi }}
                            </td>

                            {{-- KEPALA AKUN --}}
                            <td class="cell-center" style="text-align: center; font-size: 0.75rem; font-weight: 700; color: #0f172a;">
                                {{ $kepala }}
                            </td>

                            {{-- TAHUN --}}
                            <td class="cell-center font-tabular" style="text-align: center; font-size: 0.8rem; color: #64748b;">
                                {{ $tahunStr }}
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="13" style="text-align: center; padding: 3rem 1rem; color: #94a3b8; font-size: 0.875rem;">
                            Tidak ada data transaksi jurnal yang sesuai dengan kriteria filter saat ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="sheet-footer-total">
                    <td colspan="4" style="text-align: right; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.8125rem;">
                        TOTAL HALAMAN INI:
                    </td>
                    <td class="cell-nominal font-tabular" style="text-align: right; font-weight: 800; font-size: 0.875rem; color: #166534;">
                        {{ number_format($pageDebet, 0, ',', '.') }}
                    </td>
                    <td class="cell-nominal font-tabular" style="text-align: right; font-weight: 800; font-size: 0.875rem; color: #166534;">
                        {{ number_format($pageKredit, 0, ',', '.') }}
                    </td>
                    <td colspan="7" style="font-size: 0.75rem; color: #475569; font-weight: 600; padding-left: 0.75rem;">
                        @if (abs($pageDebet - $pageKredit) < 0.01)
                            <span style="color: #166534; font-weight: 800;">✓ Keseimbangan Debet = Kredit (Rp 0 Variance)</span>
                        @else
                            <span style="color: #dc2626; font-weight: 800;">⚠️ Selisih: Rp {{ number_format(abs($pageDebet - $pageKredit), 0, ',', '.') }}</span>
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- PAGINASI HALAMAN SPREADSHEET --}}
    @if ($jurnals->hasPages())
        <div class="sheet-pagination-box no-print">
            <div style="font-size: 0.8rem; color: #64748b;">
                Halaman {{ $jurnals->currentPage() }} dari {{ $jurnals->lastPage() }}
            </div>
            <div>
                {{ $jurnals->links() }}
            </div>
        </div>
    @endif
</div>

