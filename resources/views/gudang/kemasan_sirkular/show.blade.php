@extends('layouts.app')

@section('title', 'Detail Penerimaan Kemasan Sirkular ' . $kemasan->sirkular_no . ' - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/kemasan_sirkular/sirkular-show.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="sirkular-show-container">

    {{-- HEADER DOKUMEN & TOMBOL AKSI OPERASIONAL --}}
    <div class="sirkular-header-row no-print">
        <div>
            <a href="{{ route('gudang.kemasan-sirkular.index') }}" class="sirkular-back-link">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Riwayat Kemasan Sirkular</span>
            </a>
            <div class="sirkular-title-row">
                <h1 class="sirkular-title">{{ $kemasan->sirkular_no }}</h1>
                @if ($kemasan->status_cd === 'VOID')
                    <span class="badge-status batal">
                        <svg width="8" height="8" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                        <span>Dibatalkan (Void)</span>
                    </span>
                @else
                    <span class="badge-status selesai">
                        <svg width="8" height="8" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                        <span>Selesai Diterima</span>
                    </span>
                @endif
            </div>
            <p class="sirkular-subtitle">
                Diterima pada {{ \Carbon\Carbon::parse($kemasan->sirkular_tgl)->translatedFormat('d F Y') }} oleh {{ $kemasan->created_by ?? 'Admin Gudang' }}.
            </p>
        </div>

        <div class="sirkular-actions-group">
            {{-- Cetak Slip Serah Terima --}}
            <button type="button" onclick="window.print()" class="btn-enterprise-sec" title="Cetak Lembar Tanda Terima Fisik">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Lembar Serah Terima</span>
            </button>

            @if ($kemasan->pengeluaran)
                <a href="{{ route('penjualan.pengeluaran.show', $kemasan->pengeluaran_id) }}" class="btn-enterprise-primary-subtle" title="Buka Detail Surat Jalan Terkait">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Lihat Surat Jalan ({{ $kemasan->pengeluaran->pengeluaran_no }})</span>
                </a>
            @endif

            @if ($kemasan->status_cd !== 'VOID')
                <button type="button" class="btn-enterprise-danger" onclick="openModalVoid()" title="Batalkan Dokumen dan Tarik Kembali Stok">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Batalkan (Void Dokumen)</span>
                </button>
            @endif
        </div>
    </div>

    {{-- DUA KOLOM KARTU INFORMASI DOKUMEN & LOGISTIK --}}
    <div class="sirkular-grid-2-col">

        {{-- KARTU 1: PENGIRIM & DOKUMEN REFERENSI --}}
        <div class="sirkular-card">
            <div class="sirkular-card-header">
                <div class="sirkular-card-header-left">
                    <span class="sirkular-card-icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </span>
                    <div>
                        <h3 class="sirkular-card-title">Tujuan Pengiriman &amp; Dokumen Referensi</h3>
                        <div class="sirkular-card-subtitle">Identitas pelanggan dan bukti serah terima</div>
                    </div>
                </div>
            </div>

            <div class="sirkular-card-body">
                <table class="sirkular-kv-table">
                    <tr>
                        <td class="sirkular-kv-label">Customer / Pengirim:</td>
                        <td class="sirkular-kv-value">
                            {{ $kemasan->customer?->customer_nm ?? 'PT INDOFOOD CBP SUKSES MAKMUR TBK' }}
                            <span class="badge-tag-ifm" style="margin-left: 0.35rem;">
                                {{ $kemasan->customer?->customer_cd ?? 'CUST-IFM' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="sirkular-kv-label">Ref. Surat Jalan DO Mirasa:</td>
                        <td class="sirkular-kv-value">
                            @if ($kemasan->pengeluaran)
                                <a href="{{ route('penjualan.pengeluaran.show', $kemasan->pengeluaran_id) }}" class="sirkular-kv-value highlight-blue" style="text-decoration: underline;">
                                    {{ $kemasan->pengeluaran->pengeluaran_no }}
                                </a>
                                <span style="font-weight: 500; font-size: 0.75rem; color: #64748b; margin-left: 0.35rem;">
                                    (Muatan DO: {{ number_format($kemasan->total_kirim_qty, 0, ',', '.') }} Box)
                                </span>
                            @else
                                <span style="color: #94a3b8; font-weight: normal; font-style: italic;">Tanpa Referensi DO</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="sirkular-kv-label">Bukti Tanda Terima Fisik:</td>
                        <td class="sirkular-kv-value" style="font-family: 'Consolas', 'Courier New', monospace;">
                            {{ $kemasan->surat_jalan_customer_no ?: '(Tidak ada dokumen fisik)' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="sirkular-kv-label">Gudang Penyimpanan:</td>
                        <td class="sirkular-kv-value highlight-green">
                            {{ $kemasan->gudang?->gudang_nm ?? 'Gudang Barang Jadi (GDG-FG)' }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- KARTU 2: LOGISTIK ARMADA & STATUS POSTING --}}
        <div class="sirkular-card">
            <div class="sirkular-card-header">
                <div class="sirkular-card-header-left">
                    <span class="sirkular-card-icon emerald">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </span>
                    <div>
                        <h3 class="sirkular-card-title">Data Logistik Armada &amp; Administrasi</h3>
                        <div class="sirkular-card-subtitle">Supir, armada truk, dan status kartu stok</div>
                    </div>
                </div>
            </div>

            <div class="sirkular-card-body">
                <table class="sirkular-kv-table">
                    <tr>
                        <td class="sirkular-kv-label">Tanggal Masuk Fisik:</td>
                        <td class="sirkular-kv-value">
                            {{ \Carbon\Carbon::parse($kemasan->sirkular_tgl)->translatedFormat('l, d F Y') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="sirkular-kv-label">Nama Supir / Driver:</td>
                        <td class="sirkular-kv-value">
                            {{ $kemasan->supir_nm ?: 'Belum Dicatat' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="sirkular-kv-label">No. Polisi Armada:</td>
                        <td class="sirkular-kv-value highlight-blue">
                            {{ $kemasan->nopol_kendaraan ?: '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="sirkular-kv-label">Ekspedisi / Armada:</td>
                        <td class="sirkular-kv-value" style="color: #334155;">
                            {{ $kemasan->ekspedisi_nm ?: 'Armada Internal PT Mirasa' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="sirkular-kv-label">Status Posting Stok:</td>
                        <td class="sirkular-kv-value">
                            @if ($kemasan->status_cd === 'VOID')
                                <span class="badge-status batal">Rollback (Batal)</span>
                            @else
                                <span class="badge-status selesai">Tercatat di Kartu Stok</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

    </div>

    {{-- KARTU 3: RINCIAN TABEL SORTIR KEMASAN & BATCH --}}
    <div class="sirkular-card">
        <div class="sirkular-card-header">
            <div class="sirkular-card-header-left">
                <span class="sirkular-card-icon amber">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
                <div>
                    <h3 class="sirkular-card-title">Rincian Fisik Balikan &amp; Alokasi Batch Stok</h3>
                    <div class="sirkular-card-subtitle">Pemisahan karton layak pakai (puteran), reject fisik, dan traceability perputaran</div>
                </div>
            </div>
        </div>

        <div class="sirkular-table-wrap">
            <table class="sirkular-table">
                <thead>
                    <tr>
                        <th class="cell-center" style="width: 45px;">No.</th>
                        <th>Nama Kemasan / Dus</th>
                        <th class="cell-num" style="width: 110px;">Qty Kirim</th>
                        <th class="cell-num" style="width: 130px;">Puteran (BMG)</th>
                        <th class="cell-center" style="width: 110px;">Siklus Pakai</th>
                        <th class="cell-num" style="width: 130px;">Rusak / Reject</th>
                        <th class="cell-num" style="width: 120px;">Total Diterima</th>
                        <th class="cell-num" style="width: 100px;">Selisih DO</th>
                        <th style="min-width: 200px;">Keterangan Fisik</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kemasan->details as $index => $dtl)
                        @php
                            $puteranTotal = (float) $dtl->mulus_qty + (float) $dtl->bmg_qty;
                            $rejectTotal = (float) $dtl->rusak_qty + (float) $dtl->afkir_siklus_qty;
                        @endphp
                        <tr>
                            <td class="cell-center" style="color: #94a3b8; font-weight: 600;">
                                {{ $index + 1 }}
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #0f172a;">
                                    {{ $dtl->barangMulus?->barang_nm ?? ($dtl->barangAsal?->barang_nm ?? 'KARTON POLOS IFM') }}
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 3px; margin-top: 4px; align-items: flex-start;">
                                    @if ($dtl->batch_no_mulus)
                                        <span class="batch-chip-mulus">
                                            Puteran: {{ $dtl->batch_no_mulus }}
                                        </span>
                                    @endif
                                    @if ($dtl->batch_no_rusak)
                                        <span class="batch-chip-reject">
                                            Reject: {{ $dtl->batch_no_rusak }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="cell-num" style="color: #0f172a;">
                                {{ number_format($dtl->kirim_qty, 0, ',', '.') }}
                            </td>
                            <td class="cell-num" style="color: #059669;">
                                {{ number_format($puteranTotal, 0, ',', '.') }}
                            </td>
                            <td class="cell-center" style="font-size: 0.8125rem;">
                                @if ($dtl->puteran_ke >= 5 || $dtl->afkir_siklus_qty > 0)
                                    <span style="color: #dc2626; font-weight: 700;">Putaran 5 (Limit)</span>
                                @else
                                    <span style="color: #334155; font-weight: 600;">Putaran {{ $dtl->puteran_ke ?? 1 }}x</span>
                                @endif
                            </td>
                            <td class="cell-num" style="color: {{ $rejectTotal > 0 ? '#dc2626' : '#64748b' }};">
                                {{ number_format($rejectTotal, 0, ',', '.') }}
                            </td>
                            <td class="cell-num" style="color: #0f172a; background: #f8fafc;">
                                {{ number_format($dtl->kembali_qty, 0, ',', '.') }}
                            </td>
                            <td class="cell-num" style="color: {{ $dtl->selisih_qty > 0 ? '#dc2626' : '#059669' }};">
                                {{ number_format($dtl->selisih_qty, 0, ',', '.') }}
                            </td>
                            <td style="color: #475569; font-size: 0.8125rem;">
                                {{ $dtl->catatan_txt ?: 'Kondisi fisik bersih dan layak perputaran.' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="cell-center" style="padding: 2.5rem; color: #64748b;">
                                Tidak ada rincian kemasan tercatat pada dokumen ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- TABLE FOOTER SUMMARY BAR (STANDAR MIRASA ERP) --}}
        <div class="sirkular-table-footer-bar">
            <div class="sirkular-footer-left">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Dokumen ini resmi menambah saldo fisik di kartu stok Gudang Penyimpanan ({{ $kemasan->gudang?->gudang_cd ?? 'GDG-FG' }}).</span>
            </div>

            <div class="sirkular-footer-right">
                <div class="sirkular-summary-item">
                    <div class="sirkular-summary-label">Total Kirim (DO):</div>
                    <div class="sirkular-summary-value">{{ number_format($kemasan->total_kirim_qty, 0, ',', '.') }} Box</div>
                </div>

                <div class="sirkular-summary-item">
                    <div class="sirkular-summary-label">Total Diterima:</div>
                    <div class="sirkular-summary-value" style="color: #059669;">{{ number_format($kemasan->total_kembali_qty, 0, ',', '.') }} Box</div>
                </div>

                <div class="sirkular-summary-item">
                    <div class="sirkular-summary-label">Selisih:</div>
                    <div class="sirkular-summary-value" style="color: {{ $kemasan->total_selisih_qty > 0 ? '#dc2626' : '#059669' }};">
                        {{ number_format($kemasan->total_selisih_qty, 0, ',', '.') }} Box
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CATATAN OPERASIONAL LAPANGAN (JIKA ADA) --}}
    @if ($kemasan->catatan_txt)
        <div class="sirkular-card">
            <div class="sirkular-card-body">
                <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 0.25rem;">
                    Catatan Pemeriksaan Lapangan:
                </div>
                <div style="font-size: 0.85rem; color: #1e293b; line-height: 1.45;">
                    {{ $kemasan->catatan_txt }}
                </div>
            </div>
        </div>
    @endif

</div>

{{-- MODAL VOID / PEMBATALAN DOKUMEN PENERIMAAN --}}
@if ($kemasan->status_cd !== 'VOID')
<div id="modalVoid" class="sirkular-modal-backdrop">
    <div class="sirkular-modal-content">
        <div class="sirkular-modal-header">
            <div class="sirkular-modal-icon">!</div>
            <div>
                <h4 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #991b1b;">Batalkan Dokumen Penerimaan (Void)</h4>
                <div style="font-size: 0.75rem; color: #b91c1c; margin-top: 2px;">Tindakan ini akan membatalkan dan menarik mutasi stok gudang.</div>
            </div>
        </div>

        <form action="{{ route('gudang.kemasan-sirkular.void', $kemasan->kemasan_sirkular_id) }}" method="POST">
            @csrf
            <div class="sirkular-modal-body">
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.8125rem; color: #92400e; line-height: 1.45;">
                    Dokumen <strong style="font-family: monospace;">{{ $kemasan->sirkular_no }}</strong> akan ditandai sebagai <strong>VOID</strong>. Seluruh saldo stok kemasan puteran maupun reject yang telah bertambah akan di-<em>rollback</em> otomatis.
                </div>

                <label for="alasan_batal" style="display: block; font-weight: 700; font-size: 0.8125rem; color: #334155; margin-bottom: 0.35rem;">
                    Alasan Pembatalan Dokumen <span style="color: #ef4444;">*</span>
                </label>
                <textarea name="alasan_batal" id="alasan_batal" rows="3" class="form-control" required placeholder="Tuliskan alasan pembatalan dokumen ini secara jelas..." style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 0.6rem; font-size: 0.85rem; font-family: inherit; box-sizing: border-box;"></textarea>
            </div>

            <div class="sirkular-modal-footer">
                <button type="button" onclick="closeModalVoid()" class="btn-enterprise-sec">Batal &bull; Tutup</button>
                <button type="submit" class="btn btn-danger" style="background: #dc2626; color: #ffffff; border: none; font-size: 0.825rem; padding: 0.5rem 1.25rem; font-weight: 700; border-radius: 6px; cursor: pointer;">Ya, Batalkan Dokumen</button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
    <script src="{{ asset('js/gudang/kemasan_sirkular/sirkular-show.js') }}?v={{ time() }}"></script>
@endpush

@endsection
