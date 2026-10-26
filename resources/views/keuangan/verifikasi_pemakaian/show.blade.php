@extends('layouts.app')

@section('title', 'Pemeriksaan Dokumen ' . $pakai->pakai_no . ' - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/verifikasi_pemakaian/verifikasi-pemakaian.css') }}?v={{ file_exists(public_path('css/keuangan/verifikasi_pemakaian/verifikasi-pemakaian.css')) ? filemtime(public_path('css/keuangan/verifikasi_pemakaian/verifikasi-pemakaian.css')) : time() }}">
    @if($pakai->isDraft())
        <link rel="stylesheet" href="{{ asset('css/keuangan/verifikasi_pemakaian/verifikasi-pemakaian-edit.css') }}?v={{ file_exists(public_path('css/keuangan/verifikasi_pemakaian/verifikasi-pemakaian-edit.css')) ? filemtime(public_path('css/keuangan/verifikasi_pemakaian/verifikasi-pemakaian-edit.css')) : time() }}">
    @endif
    <style>
        @media print {
            header, nav, .btn, .no-print, .alert, footer, .sticky-action-sidebar, .cat-pill-group, .grade-pill-group, th:last-child, td:last-child {
                display: none !important;
            }
            body, main {
                padding: 0 !important;
                margin: 0 !important;
                background: #ffffff !important;
            }
            .verifikasi-card, .card {
                box-shadow: none !important;
                border: 1px solid #000000 !important;
            }
            .order-station-grid {
                display: block !important;
            }
        }
    </style>
@endpush

@section('content')
<div class="no-print" style="margin-bottom: 1.25rem;">
    <a href="{{ route('keuangan.verifikasi_pemakaian.index', ['tab' => $pakai->status_st]) }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Daftar Antrean Verifikasi
    </a>

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                <span>{{ $pakai->pakai_no }}</span>
                <span class="badge" style="background: #ffedd5; color: #c2410c; font-size: 0.85rem;">
                    {{ $pakai->tujuan_pemakaian }}
                </span>
                @if($pakai->status_st === 'DRAFT')
                    <span class="badge-status badge-draft" style="font-size: 0.8rem; padding: 0.25rem 0.65rem;">
                        ⏳ DRAFT (Siap Verifikasi &amp; Edit Langsung)
                    </span>
                @else
                    <span class="badge-status badge-posted" style="font-size: 0.8rem; padding: 0.25rem 0.65rem;">
                        ✓ POSTED (Sudah Dijurnal &amp; Dipotong Stok)
                    </span>
                @endif
            </h1>
            <p style="font-size: 0.85rem; color: #64748b; margin-top: 0.25rem; margin-bottom: 0;">
                Gudang Asal: <strong>{{ $pakai->gudang?->gudang_nm }}</strong> &bull; Tanggal Catat: <strong>{{ \Carbon\Carbon::parse($pakai->pakai_tgl)->format('d F Y') }}</strong> &bull; Dicatat oleh: <strong>{{ $pakai->created_by ?? '-' }}</strong>
            </p>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            @if($pakai->isDraft())
                {{-- LIVE KPI WIDGET DI POJOK KANAN ATAS --}}
                <div id="liveTotalWidget" class="live-kpi-widget">
                    <div>
                        <div style="font-size: 0.675rem; font-weight: 700; text-transform: uppercase; color: #059669; letter-spacing: 0.04em;">
                            Total Fisik Keluar:
                        </div>
                        <div style="display: flex; align-items: baseline; gap: 0.25rem;">
                            <span id="sideTotalQtyLarge" style="font-size: 1.65rem; font-weight: 800; font-family: monospace; color: #047857; line-height: 1;">0,00</span>
                            <span style="font-size: 0.85rem; font-weight: 700; color: #059669;">Kg</span>
                        </div>
                    </div>
                    <div style="height: 32px; width: 1px; background: #e2e8f0;"></div>
                    <div style="font-size: 0.775rem; line-height: 1.45;">
                        <div style="color: #065f46; font-weight: 700;">🟢 Grade A: <strong id="sideGradeAQty" style="font-family: monospace;">0,00 Kg</strong></div>
                        <div style="color: #b45309; font-weight: 700;">🟡 Grade B: <strong id="sideGradeBQty" style="font-family: monospace;">0,00 Kg</strong></div>
                    </div>
                    <div style="height: 32px; width: 1px; background: #e2e8f0;"></div>
                    <div style="font-size: 0.775rem; color: #64748b; font-weight: 600;">
                        <strong id="sideTotalItems" style="font-weight: 800; color: #0f172a;">0</strong> Baris Item
                    </div>
                    <div style="height: 32px; width: 1px; background: #e2e8f0;"></div>
                    <div>
                        <div style="font-size: 0.675rem; font-weight: 700; text-transform: uppercase; color: #0284c7; letter-spacing: 0.04em;">
                            Total Biaya HPP:
                        </div>
                        <span id="sideGrandTotal" style="font-size: 1.25rem; font-weight: 800; font-family: monospace; color: #0284c7; line-height: 1;">Rp 0</span>
                    </div>
                </div>
            @endif

            @if($pakai->isPosted())
                @if($pakai->jurnal_id)
                    <button type="button" onclick="openModalDetailJurnal({{ $pakai->jurnal_id }})" class="btn btn-secondary" style="color: #065f46; border-color: #a7f3d0; background: #f0fdf4; font-weight: 700;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Lihat Jurnal Umum
                    </button>
                @endif
                <button onclick="window.print()" class="btn btn-secondary">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Bukti
                </button>
            @endif
        </div>
    </div>
</div>

{{-- JIKA DOKUMEN MASIH DRAFT: TAMPILKAN STASIUN INSPEKSI & EDIT LANGSUNG DI TEMPAT --}}
@if($pakai->isDraft())

    <div class="no-print" style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 8px; padding: 0.75rem 1.25rem; margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">
                ✏️
            </div>
            <div>
                <h4 style="margin: 0; font-size: 0.9rem; font-weight: 700; color: #92400e;">
                    Layar Terpadu: Pemeriksaan &amp; Koreksi Bahan Langsung di Tabel
                </h4>
                <p style="margin: 0.15rem 0 0; font-size: 0.775rem; color: #b45309;">
                    Gunakan filter kategori (Bahan Baku / Penolong / Kemasan) &amp; Grade untuk mencari barang atau mengganti nomor batch. Verifikasi kuantitas timbangan fisik sebelum disetujui.
                </p>
            </div>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 0.75rem; padding: 0.35rem 0.75rem; border-radius: 6px;">
                💡 Shortcut: Tekan Enter pada Qty/Harga untuk baris baru
            </span>
        </div>
    </div>

    {{-- FORM UTAMA VERIFIKASI & UPDATE LANGSUNG DI SHOW --}}
    <form action="{{ route('keuangan.verifikasi_pemakaian.update', $pakai->pakai_id) }}" method="POST" id="verifikasiEditForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="action_type" id="action_type" value="save_draft">

        <div class="verifikasi-fullwidth-container">
            {{-- KARTU 1: INFORMASI DOKUMEN & PENGELUARAN (COMPACT & SLEEK) --}}
            <div class="card" style="border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.04); background: #ffffff; margin-bottom: 1rem;">
                <div style="background: #f8fafc; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                        <strong style="color: #0f172a; font-size: 0.95rem;">1. Dokumen Bukti Pengeluaran (BPPB)</strong>
                        <span style="font-family: monospace; font-size: 0.95rem; font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 2px 8px; border-radius: 4px; border: 1px solid #bae6fd;">
                            {{ $pakai->pakai_no }}
                        </span>
                        <span style="font-size: 0.775rem; color: #64748b;">
                            Dicatat oleh: <strong>{{ $pakai->created_by ?? 'Admin Gudang' }}</strong>
                        </span>
                    </div>
                    <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 0.725rem;">
                        Semua Data Dapat Dikoreksi Langsung
                    </span>
                </div>

                <div style="padding: 0.85rem 1.25rem; display: grid; grid-template-columns: 1.1fr 1.6fr 1.8fr 2fr; gap: 0.85rem; align-items: start;">
                    <div>
                        <label for="pakai_tgl" style="font-weight: 700; font-size: 0.75rem; color: #475569; display: block; margin-bottom: 3px;">
                            Tanggal Keluar <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" id="pakai_tgl" name="pakai_tgl" value="{{ old('pakai_tgl', $pakai->pakai_tgl ? \Carbon\Carbon::parse($pakai->pakai_tgl)->format('Y-m-d') : date('Y-m-d')) }}" class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;" required>
                    </div>

                    <div>
                        <label for="gudang_id" style="font-weight: 700; font-size: 0.75rem; color: #475569; display: block; margin-bottom: 3px;">
                            Lokasi Gudang Asal <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="gudang_id" id="gudang_id" class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;" required onchange="onGudangChanged()">
                            <option value="">-- Pilih Lokasi Asal --</option>
                            @foreach ($gudangList as $gdg)
                                <option value="{{ $gdg->gudang_id }}" {{ old('gudang_id', $pakai->gudang_id) == $gdg->gudang_id ? 'selected' : '' }}>
                                    {{ $gdg->display_name }} ({{ $gdg->gudang_cd }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="tujuan_pemakaian" style="font-weight: 700; font-size: 0.75rem; color: #475569; display: block; margin-bottom: 3px;">
                            Tujuan Pemakaian / Lini Kerja <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="tujuan_pemakaian" id="tujuan_pemakaian" class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;" required onchange="updateSidebarInfo()">
                            <option value="">-- Pilih Lini Produksi / Tujuan --</option>
                            @if(isset($liniList) && $liniList->isNotEmpty())
                                @foreach($liniList->groupBy(fn($item) => $item->kategori_lini ?: 'UMUM') as $kategori => $items)
                                    <optgroup label="{{ $kategori }}">
                                        @foreach($items as $lini)
                                            <option value="{{ $lini->lini_nm }}" {{ old('tujuan_pemakaian', $pakai->tujuan_pemakaian) === $lini->lini_nm ? 'selected' : '' }}>
                                                {{ $lini->lini_nm }} {{ $lini->keterangan ? '('.$lini->keterangan.')' : '' }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div>
                        <label for="catatan_txt" style="font-weight: 700; font-size: 0.75rem; color: #475569; display: block; margin-bottom: 3px;">
                            Catatan Koreksi / Memo Audit (Opsional)
                        </label>
                        <input type="text" id="catatan_txt" name="catatan_txt" value="{{ old('catatan_txt', $pakai->catatan_txt) }}" placeholder="Catatan memo audit..." class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;">
                    </div>
                </div>
            </div>

            {{-- KARTU 2: RINCIAN BAHAN YANG DIKELUARKAN (TABEL LENGKAP PENUH SEPERTI FORM PEMAKAIAN) --}}
            <div class="card" style="border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); background: #ffffff;">
                <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <strong style="color: #0f172a; font-size: 0.95rem;">2. Rincian Fisik Bahan &amp; Alokasi Batch (FIFO)</strong>
                        <span style="font-size: 0.775rem; color: #64748b;">
                            💡 Klik angka atau batch untuk mengubah data langsung
                        </span>
                    </div>
                    <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; align-items: center;">
                        <span style="font-size: 0.775rem; font-weight: 700; color: #64748b; margin-right: 0.25rem;">+ Tambah Cepat:</span>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="addRow('BAHAN_BAKU')" style="font-weight: 700; font-size: 0.8rem; border-radius: 6px; border: 1.5px solid #cbd5e1; background: #ffffff; padding: 0.35rem 0.75rem;">
                            <span style="display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #d97706; margin-right: 4px;"></span>Bahan Baku
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="addRow('BAHAN_PENOLONG')" style="font-weight: 700; font-size: 0.8rem; border-radius: 6px; border: 1.5px solid #cbd5e1; background: #ffffff; padding: 0.35rem 0.75rem;">
                            <span style="display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #0284c7; margin-right: 4px;"></span>Bumbu / Penolong
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="addRow('KEMASAN')" style="font-weight: 700; font-size: 0.8rem; border-radius: 6px; border: 1.5px solid #cbd5e1; background: #ffffff; padding: 0.35rem 0.75rem;">
                            <span style="display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #059669; margin-right: 4px;"></span>Kemasan
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addRow('')" style="font-weight: 700; font-size: 0.8rem; border-radius: 6px; background: #dc2626; border: none; padding: 0.35rem 0.85rem;">
                            + Baris Umum
                        </button>
                    </div>
                </div>

                {{-- SMART AUDIT STRIP: INDIKATOR CEPAT MATA ELANG UNTUK VERIFIKATOR --}}
                <div class="audit-status-strip">
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <span style="font-weight: 700; color: #334155; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.03em;">
                            🎯 Deteksi Audit Visual:
                        </span>
                        <span class="audit-indicator-pill audit-pill-ok" title="Bahan dengan stok fisik cukup & batch tertua (FIFO)">
                            🟢 Lolos Audit: <span id="auditCountOk">0</span> Item
                        </span>
                        <span class="audit-indicator-pill audit-pill-warning" title="Bahan yang mengambil batch lebih baru padahal batch lama masih ada sisa">
                            🟡 Peringatan FIFO: <span id="auditCountWarn">0</span> Item
                        </span>
                        <span class="audit-indicator-pill audit-pill-danger" title="Bahan yang kuantitasnya melebihi stok fisik (rawan salah input admin gudang)">
                            🔴 Anomali / Kurang Stok: <span id="auditCountDanger">0</span> Item
                        </span>
                    </div>
                    <div style="font-size: 0.725rem; color: #64748b;">
                        ⚡ <span style="color: #0f172a; font-weight: 600;">Tips:</span> Cukup fokuskan mata pada baris bertanda <span style="color: #dc2626; font-weight: 700;">merah</span> atau <span style="color: #b45309; font-weight: 700;">kuning</span>.
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table class="excel-grid-table" id="itemsTable" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 50px; text-align: center;">No &amp; Status</th>
                                <th style="min-width: 320px;">Kategori &amp; Nama Bahan Produksi <span style="color:#ef4444;">*</span></th>
                                <th style="min-width: 340px;">Pilihan Grade &amp; Batch (FIFO) <span style="color:#ef4444;">*</span></th>
                                <th style="width: 140px; text-align: right;">Qty Keluar <span style="color:#ef4444;">*</span></th>
                                <th style="width: 80px; text-align: center;">Satuan</th>
                                <th style="width: 140px; text-align: right;">Harga Satuan (Rp)</th>
                                <th style="width: 150px; text-align: right;">Total Biaya (HPP)</th>
                                <th style="width: 50px; text-align: center;">Hapus</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            {{-- Di-render via JavaScript (addRowWithItemData) --}}
                        </tbody>
                        <tfoot>
                            <tr style="border-top: 2px solid #cbd5e1; font-weight: 700; background: #f8fafc;">
                                <td colspan="3" style="padding: 0.75rem 1rem; text-align: right; color: #475569; font-size: 0.825rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                    Total Akumulasi Fisik &amp; HPP:
                                    <span style="margin-left: 0.85rem; text-transform: none; font-size: 0.8rem; display: inline-flex; gap: 0.5rem; vertical-align: middle;">
                                         <span style="background: #ecfdf5; color: #065f46; border: 1.5px solid #a7f3d0; padding: 3px 8px; border-radius: 6px; font-weight: 700;">
                                            🟢 Grade A: <strong id="footGradeAQty" style="font-family: monospace; font-size: 0.95rem;">0,00</strong>
                                        </span>
                                        <span style="background: #fffbeb; color: #b45309; border: 1.5px solid #fde68a; padding: 3px 8px; border-radius: 6px; font-weight: 700;">
                                            🟡 Grade B: <strong id="footGradeBQty" style="font-family: monospace; font-size: 0.95rem;">0,00</strong>
                                        </span>
                                    </span>
                                </td>
                                <td style="padding: 0.75rem 0.75rem; text-align: right; color: #047857; font-family: monospace; font-size: 1.35rem; font-weight: 800;" id="grandTotalQty">
                                    0,00
                                </td>
                                <td style="text-align: center; color: #64748b; font-size: 0.8rem; font-weight: 700;">Subtotal:</td>
                                <td></td>
                                <td style="padding: 0.75rem 0.75rem; text-align: right; color: #0f172a; font-family: monospace; font-size: 1.25rem; font-weight: 800;" id="grandTotalNilai">
                                    Rp 0
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div> {{-- End .verifikasi-fullwidth-container --}}

        {{-- STICKY BOTTOM ACTION BAR (TETAP MELAYANG DI BAWAH AGAR TIDAK MEMAKAN LEBAR TABEL) --}}
        <div class="verifikasi-sticky-bottom-bar">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 34px; height: 34px; border-radius: 50%; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.15rem; font-weight: 800;">
                    ✓
                </div>
                <div>
                    <strong style="color: #0f172a; font-size: 0.875rem; display: block;">Mode Verifikasi &amp; Pemeriksaan Fisik Bahan (DRAFT)</strong>
                    <span style="font-size: 0.75rem; color: #64748b;">Stok fisik terpotong &amp; Jurnal Umum dibukukan otomatis saat disetujui.</span>
                </div>
            </div>

            {{-- MINI LIVE SUMMARY DI BOTTOM BAR --}}
            <div class="verifikasi-bottom-summary-card">
                <div>
                    <div style="font-size: 0.675rem; font-weight: 700; color: #475569; text-transform: uppercase;">Total Fisik:</div>
                    <div style="font-size: 1.15rem; font-weight: 800; font-family: monospace; color: #047857;" id="botBarTotalQty">0,00 Kg</div>
                </div>
                <div style="height: 28px; width: 1px; background: #cbd5e1;"></div>
                <div>
                    <div style="font-size: 0.675rem; font-weight: 700; color: #475569; text-transform: uppercase;">Total Biaya (HPP):</div>
                    <div style="font-size: 1.15rem; font-weight: 800; font-family: monospace; color: #0f172a;" id="botBarGrandTotal">Rp 0</div>
                </div>
                <div style="height: 28px; width: 1px; background: #cbd5e1;"></div>
                <div style="font-size: 0.725rem; line-height: 1.3;">
                    <div style="color: #065f46; font-weight: 700;">(D) 1151: <span id="sideJournalDebet" style="font-family: monospace;">Rp 0</span></div>
                    <div style="color: #991b1b; font-weight: 700;">(K) 1150: <span id="sideJournalKredit" style="font-family: monospace;">Rp 0</span></div>
                </div>
            </div>

            <div style="display: flex; gap: 0.65rem; align-items: center;">
                <button type="button" onclick="openModalReject({{ $pakai->pakai_id }}, '{{ $pakai->pakai_no }}')" class="btn btn-secondary" style="padding: 0.65rem 1rem; font-size: 0.85rem; font-weight: 600; border: 1.5px solid #fecaca; background: #fef2f2; color: #dc2626; border-radius: 6px; cursor: pointer;">
                    Tolak / Batalkan
                </button>
                <button type="button" onclick="submitVerifikasiForm('save_draft')" class="btn btn-secondary" style="padding: 0.65rem 1.25rem; font-weight: 700; font-size: 0.875rem; border-radius: 6px; background: #f8fafc; border: 1.5px solid #cbd5e1;">
                    Simpan Perubahan (DRAFT)
                </button>
                <button type="button" onclick="submitVerifikasiForm('save_and_approve')" class="btn btn-primary" style="padding: 0.75rem 1.85rem; font-size: 0.95rem; font-weight: 800; background: #059669; border: none; border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.35); display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan &amp; Langsung Setujui (Posting)</span>
                </button>
            </div>
        </div>
    </form>

    @include('keuangan.verifikasi_pemakaian.partials.modal-warning-fifo')
    @include('keuangan.verifikasi_pemakaian.partials.modal-confirm-approve-station')

@else
{{-- JIKA DOKUMEN SUDAH POSTED: TAMPILKAN FORMAT RESMI TERKUNCI (READ-ONLY) --}}

    <div class="no-print" style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: #d1fae5; color: #065f46; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                ✓
            </div>
            <div>
                <h4 style="margin: 0; font-size: 0.925rem; font-weight: 700; color: #065f46;">Dokumen Ini Telah Resmi Diverifikasi &amp; Terposting</h4>
                <p style="margin: 0.15rem 0 0; font-size: 0.8rem; color: #047857;">
                    Diverifikasi pada <strong>{{ $pakai->posted_at ? $pakai->posted_at->format('d/m/Y H:i') : '-' }}</strong> oleh <strong>{{ $pakai->posted_by ?? 'SYSTEM' }}</strong>. Stok fisik gudang telah terpotong dan Jurnal Umum telah dibukukan.
                </p>
            </div>
        </div>
    </div>

    {{-- TABEL VALIDASI FISIK BATCH & KETERSEDIAAN STOK (READ-ONLY) --}}
    <div class="verifikasi-card">
        <div style="padding: 1.15rem 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <h2 style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin: 0;">
                1. Rincian Fisik Batch &amp; HPP Pemakaian Bahan
            </h2>
            <span style="font-size: 0.85rem; color: #64748b;">
                Total: <strong>{{ count($itemsCheck) }} Item Bahan</strong>
            </span>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="min-width: 190px;">Nama Bahan</th>
                        <th style="min-width: 130px;">Batch Dipakai</th>
                        <th style="min-width: 110px; text-align: right;">Qty Keluar</th>
                        <th style="min-width: 120px; text-align: center;">Status Verifikasi</th>
                        <th style="min-width: 130px; text-align: right;">Harga Satuan</th>
                        <th style="min-width: 140px; text-align: right;">Total Nilai HPP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($itemsCheck as $index => $item)
                        @php $dtl = $item['detail']; @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="text-align: center; color: #64748b; font-size: 0.85rem;">{{ $index + 1 }}</td>
                            <td>
                                <strong style="color: #0f172a; font-size: 0.875rem;">
                                    {{ $dtl->barang?->barang_nm }}
                                </strong>
                                <div style="font-size: 0.725rem; font-family: monospace; color: #64748b;">
                                    {{ $dtl->barang?->barang_cd }} &bull; {{ $dtl->barang?->jenisBarang?->jenis_barang_nm ?? '-' }}
                                </div>
                            </td>
                            <td>
                                <span style="font-family: monospace; font-weight: 700; color: #0f172a; font-size: 0.875rem;">
                                    {{ $dtl->batch_no }}
                                </span>
                                @if($dtl->grade_cd)
                                    <span style="margin-left: 0.35rem; font-size: 0.7rem; background: #f1f5f9; padding: 1px 5px; border-radius: 3px; font-weight: 700;">
                                        Grade {{ $dtl->grade_cd }}
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: right; font-weight: 700; font-family: monospace; font-size: 0.925rem; color: #0284c7;">
                                {{ number_format((float) $dtl->qty_keluar, 2, ',', '.') }}
                                <span style="font-size: 0.75rem; color: #64748b; font-weight: 400;">
                                    {{ $dtl->barang?->satuanDasar?->satuan_nm }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: #ecfdf5; color: #065f46; font-weight: 700; font-size: 0.75rem;">
                                    ✓ Terverifikasi
                                </span>
                            </td>
                            <td style="text-align: right; font-family: monospace; font-size: 0.85rem; color: #475569;">
                                Rp {{ number_format((float) $dtl->harga_satuan, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-family: monospace; font-weight: 700; font-size: 0.925rem; color: #0f172a;">
                                Rp {{ number_format((float) $dtl->total_harga, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: #f8fafc; font-weight: 800; border-top: 2px solid #e2e8f0;">
                        <td colspan="3" style="text-align: right; padding: 0.85rem 1.25rem; font-size: 0.85rem; color: #475569;">
                            TOTAL BIAYA BAHAN (HPP DIJURNAL):
                        </td>
                        <td style="text-align: right; padding: 0.85rem 1rem; color: #0284c7; font-family: monospace; font-size: 1rem;">
                            {{ number_format((float) $pakai->details->sum('qty_keluar'), 2, ',', '.') }}
                        </td>
                        <td colspan="2"></td>
                        <td style="text-align: right; padding: 0.85rem 1rem; color: #0f172a; font-family: monospace; font-size: 1.15rem;">
                            Rp {{ number_format($totalNominal, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- 2. INFORMASI JURNAL UMUM AKUNTANSI --}}
    @if($pakai->jurnal)
        <div class="verifikasi-card" style="margin-top: 1.5rem;">
            <div style="padding: 1.15rem 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin: 0;">
                        2. Entri Jurnal Umum Otomatis Terbit
                    </h2>
                    <p style="margin: 0.2rem 0 0; font-size: 0.8rem; color: #64748b;">
                        Nomor Bukti Jurnal: <strong style="font-family: monospace; color: #0284c7;">{{ $pakai->jurnal->nomor_jurnal }}</strong> &bull; Tanggal: <strong>{{ \Carbon\Carbon::parse($pakai->jurnal->tanggal_transaksi)->format('d F Y') }}</strong>
                    </p>
                </div>
                <span class="badge" style="background: #ecfdf5; color: #065f46; font-weight: 700;">
                    Status Jurnal: POSTED
                </span>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="width: 120px;">Kode Akun</th>
                            <th>Nama Akun Perkiraan</th>
                            <th>Keterangan Baris Jurnal</th>
                            <th style="width: 140px; text-align: right;">Debet</th>
                            <th style="width: 140px; text-align: right;">Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pakai->jurnal->details as $jDtl)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="font-family: monospace; font-weight: 700; color: #0284c7;">
                                    {{ $jDtl->coa?->nomor_akun ?? '-' }}
                                </td>
                                <td>
                                    <strong style="color: #0f172a;">{{ $jDtl->coa?->nama_akun ?? '-' }}</strong>
                                </td>
                                <td style="color: #475569; font-size: 0.825rem;">
                                    {{ $jDtl->keterangan }}
                                </td>
                                <td style="text-align: right; font-family: monospace; font-weight: 600; color: {{ $jDtl->debet > 0 ? '#0f172a' : '#94a3b8' }};">
                                    Rp {{ number_format((float) $jDtl->debet, 0, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-family: monospace; font-weight: 600; color: {{ $jDtl->kredit > 0 ? '#0f172a' : '#94a3b8' }};">
                                    Rp {{ number_format((float) $jDtl->kredit, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: #f8fafc; font-weight: 800; border-top: 2px solid #e2e8f0;">
                            <td colspan="3" style="text-align: right; padding: 0.85rem 1.25rem; font-size: 0.85rem; color: #475569;">
                                TOTAL SALDO JURNAL (SEIMBANG / BALANCED):
                            </td>
                            <td style="text-align: right; padding: 0.85rem 1rem; color: #059669; font-family: monospace; font-size: 1rem;">
                                Rp {{ number_format((float) $pakai->jurnal->details->sum('debet'), 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; padding: 0.85rem 1rem; color: #059669; font-family: monospace; font-size: 1rem;">
                                Rp {{ number_format((float) $pakai->jurnal->details->sum('kredit'), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

@endif

{{-- MODALS GLOBAL --}}
@include('gudang.pemakaian.partials.modal-warning-stock-limit')
@include('gudang.pemakaian.partials.modal-warning-fifo')
@include('keuangan.verifikasi_pemakaian.partials.modal-reject')
@include('keuangan.jurnal.partials.modal-detail-jurnal')

@endsection

@push('scripts')
<script src="{{ asset('js/keuangan/verifikasi_pemakaian/verifikasi-pemakaian.js') }}?v={{ file_exists(public_path('js/keuangan/verifikasi_pemakaian/verifikasi-pemakaian.js')) ? filemtime(public_path('js/keuangan/verifikasi_pemakaian/verifikasi-pemakaian.js')) : time() }}"></script>
@if($pakai->isDraft())
<script>
    window.appConfig = {
        barangList: @json($barangList ?? []),
        initialItems: @json($initialItems ?? []),
        urlGetBatches: "{{ route('gudang.pemakaian.batches') }}",
        urlAlokasiResep: "{{ route('gudang.pemakaian.alokasi-resep') }}"
    };
</script>
<script src="{{ asset('js/keuangan/verifikasi_pemakaian/verifikasi-pemakaian-edit.js') }}?v={{ file_exists(public_path('js/keuangan/verifikasi_pemakaian/verifikasi-pemakaian-edit.js')) ? filemtime(public_path('js/keuangan/verifikasi_pemakaian/verifikasi-pemakaian-edit.js')) : time() }}"></script>
@endif
@endpush
