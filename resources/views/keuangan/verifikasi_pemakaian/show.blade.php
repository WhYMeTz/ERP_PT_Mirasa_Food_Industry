@extends('layouts.app')

@section('title', 'Pemeriksaan Dokumen ' . $pakai->pakai_no . ' - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/verifikasi_pemakaian/verifikasi-pemakaian.css') }}">
    @if($pakai->isDraft())
        <link rel="stylesheet" href="{{ asset('css/keuangan/verifikasi_pemakaian/verifikasi-pemakaian-edit.css') }}">
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

    <div class="no-print" style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 8px; padding: 0.85rem 1.25rem; margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 38px; height: 38px; border-radius: 50%; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                ✏️
            </div>
            <div>
                <h4 style="margin: 0; font-size: 0.925rem; font-weight: 700; color: #92400e;">
                    Layar Terpadu: Inspeksi &amp; Koreksi Bahan Langsung di Tempat
                </h4>
                <p style="margin: 0.15rem 0 0; font-size: 0.8rem; color: #b45309;">
                    Anda dapat langsung mengoreksi nomor batch, kuantitas timbangan, atau mengganti item bahan di bawah ini. Setelah sesuai, pilih <strong>"Simpan &amp; Langsung Setujui"</strong> untuk memotong stok fisik dan membukukan Jurnal Umum secara instan.
                </p>
            </div>
        </div>
    </div>

    {{-- FORM UTAMA VERIFIKASI & UPDATE LANGSUNG DI SHOW --}}
    <form action="{{ route('keuangan.verifikasi_pemakaian.update', $pakai->pakai_id) }}" method="POST" id="verifikasiEditForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="action_type" id="action_type" value="save_draft">

        <div class="order-station-grid">
            {{-- KOLOM KIRI: FORMULIR UTAMA & TABEL (2.3fr) --}}
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                
                {{-- KARTU 1: INFORMASI DOKUMEN & PENGELUARAN --}}
                <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                    <div class="card-header" style="background: #ffffff; padding: 0.85rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                        <strong style="color: #0f172a; font-size: 0.95rem;">1. Informasi Dokumen &amp; Tujuan Pengeluaran</strong>
                        <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 0.725rem;">Stasiun Verifikasi Terpadu</span>
                    </div>
                    <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                        
                        {{-- BARIS 1: NO DOKUMEN, TANGGAL, GUDANG ASAL, TUJUAN / SPK --}}
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="pakai_no" class="form-label" style="font-weight: 600; font-size: 0.825rem; color: #334155; margin-bottom: 0.35rem;">
                                    Nomor Bukti Pengeluaran (BPPB)
                                </label>
                                <input type="text" id="pakai_no" value="{{ $pakai->pakai_no }}" class="form-control" style="font-family: monospace; font-weight: 700; height: 38px; border-radius: 6px; font-size: 0.85rem; background: #f8fafc;" disabled>
                                <small style="color: #64748b; font-size: 0.725rem;">Nomor urut resmi dokumen terkunci.</small>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="pakai_tgl" class="form-label" style="font-weight: 600; font-size: 0.825rem; color: #334155; margin-bottom: 0.35rem;">
                                    Tanggal Pengeluaran Fisik <span style="color: #ef4444;">*</span>
                                </label>
                                <input type="date" id="pakai_tgl" name="pakai_tgl" value="{{ old('pakai_tgl', $pakai->pakai_tgl ? \Carbon\Carbon::parse($pakai->pakai_tgl)->format('Y-m-d') : date('Y-m-d')) }}" class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;" required>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="gudang_id" class="form-label" style="font-weight: 600; font-size: 0.825rem; color: #334155; margin-bottom: 0.35rem;">
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

                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="tujuan_pemakaian" class="form-label" style="font-weight: 600; font-size: 0.825rem; color: #334155; margin-bottom: 0.35rem;">
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
                        </div>

                        {{-- BARIS 2: CATATAN TAMBAHAN --}}
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="catatan_txt" class="form-label" style="font-weight: 600; font-size: 0.825rem; color: #334155; margin-bottom: 0.35rem;">
                                Catatan Tambahan Verifikasi / Koreksi (Opsional)
                            </label>
                            <input type="text" id="catatan_txt" name="catatan_txt" value="{{ old('catatan_txt', $pakai->catatan_txt) }}" placeholder="Keterangan koreksi timbangan, memo supervisor, instruksi verifikator, dll." class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;">
                        </div>
                    </div>
                </div>

                {{-- KARTU 2: RINCIAN BAHAN YANG DIKELUARKAN (INTERAKTIF & BISA DIEDIT LANGSUNG) --}}
                <div class="card" style="border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div class="card-header" style="background: #ffffff; padding: 0.85rem 1.25rem; border-bottom: 1px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <div>
                            <strong style="color: #0f172a; font-size: 0.95rem;">2. Rincian Fisik Bahan Dikeluarkan &amp; Pilihan Batch (FIFO)</strong>
                            <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.5rem;">
                                💡 Tekan <kbd style="background:#e2e8f0; padding:2px 5px; border-radius:3px; font-weight:700;">Enter</kbd> untuk berpindah antar-baris layaknya Excel
                            </span>
                        </div>
                        <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; align-items: center;">
                            <span style="font-size: 0.75rem; font-weight: 600; color: #64748b; margin-right: 0.25rem;">+ Tambah Cepat:</span>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addRow('BAHAN_BAKU')" style="font-weight: 600; font-size: 0.775rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; padding: 0.3rem 0.65rem;">
                                <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #d97706; margin-right: 4px;"></span>Bahan Baku
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addRow('BAHAN_PENOLONG')" style="font-weight: 600; font-size: 0.775rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; padding: 0.3rem 0.65rem;">
                                <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #0284c7; margin-right: 4px;"></span>Bumbu / Penolong
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addRow('KEMASAN')" style="font-weight: 600; font-size: 0.775rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; padding: 0.3rem 0.65rem;">
                                <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #059669; margin-right: 4px;"></span>Kemasan
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="addRow('')" style="font-weight: 600; font-size: 0.775rem; border-radius: 6px; background: #0284c7; border: none; padding: 0.3rem 0.75rem;">
                                + Baris Umum
                            </button>
                        </div>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="excel-grid-table" id="itemsTable" style="width: 100%; min-width: 980px;">
                            <thead>
                                <tr>
                                    <th style="width: 35px; text-align: center;">No</th>
                                    <th style="min-width: 240px;">Kategori &amp; Nama Bahan Produksi <span style="color:#ef4444;">*</span></th>
                                    <th style="min-width: 260px;">Pilihan Grade &amp; Batch (FIFO) <span style="color:#ef4444;">*</span></th>
                                    <th style="width: 115px; text-align: right;">Qty Keluar <span style="color:#ef4444;">*</span></th>
                                    <th style="width: 65px; text-align: center;">Satuan</th>
                                    <th style="width: 125px; text-align: right;">Harga Satuan (Rp)</th>
                                    <th style="width: 135px; text-align: right;">Total Biaya (HPP)</th>
                                    <th style="width: 40px; text-align: center;">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                {{-- Di-render via JavaScript (addRowWithItemData) --}}
                            </tbody>
                            <tfoot>
                                <tr style="border-top: 2px solid #cbd5e1; font-weight: 700; background: #f8fafc;">
                                    <td colspan="3" style="padding: 0.65rem 0.75rem; text-align: right; color: #475569; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                        Total Akumulasi Fisik &amp; HPP:
                                        <span style="margin-left: 0.65rem; text-transform: none; font-size: 0.75rem; display: inline-flex; gap: 0.35rem; vertical-align: middle;">
                                            <span style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 2px 7px; border-radius: 4px; font-weight: 700;">
                                                🟢 Grade A: <strong id="footGradeAQty" style="font-family: monospace;">0,00</strong>
                                            </span>
                                            <span style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 2px 7px; border-radius: 4px; font-weight: 700;">
                                                🟡 Grade B: <strong id="footGradeBQty" style="font-family: monospace;">0,00</strong>
                                            </span>
                                        </span>
                                    </td>
                                    <td style="padding: 0.65rem 0.5rem; text-align: right; color: #0284c7; font-family: monospace; font-size: 0.95rem; font-weight: 700;" id="grandTotalQty">
                                        0,00
                                    </td>
                                    <td style="text-align: center; color: #64748b; font-size: 0.725rem;">Subtotal:</td>
                                    <td></td>
                                    <td style="padding: 0.65rem 0.5rem; text-align: right; color: #0f172a; font-family: monospace; font-size: 1.05rem; font-weight: 800;" id="grandTotalNilai">
                                        Rp 0
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div> {{-- End Kolom Kiri (.order-station-grid left) --}}

            {{-- KOLOM KANAN: STICKY ACTION SIDEBAR (1fr) --}}
            <div class="sticky-action-sidebar" style="position: sticky; top: 1.25rem; display: flex; flex-direction: column; gap: 1.25rem;">
                
                {{-- KARTU SUMMARY & ACTION UTAMA --}}
                <div class="card" style="border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06); overflow: hidden;">
                    <div class="card-header" style="background: #0f172a; color: #ffffff; padding: 0.85rem 1.25rem;">
                        <div style="font-size: 0.725rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; color: #94a3b8;">
                            Panel Verifikasi
                        </div>
                        <strong style="color: #ffffff; font-size: 1.05rem;">Ringkasan Biaya HPP</strong>
                    </div>

                    <div style="padding: 1.25rem;">
                        {{-- TOTAL NOMINAL DISPLAY BESAR --}}
                        <div style="margin-bottom: 1.25rem;">
                            <span style="font-size: 0.725rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; display: block;">Total Biaya Bahan (HPP):</span>
                            <div id="sideGrandTotal" style="font-size: 1.65rem; font-weight: 800; color: #0f172a; margin-top: 0.2rem; font-family: monospace; letter-spacing: -0.02em;">
                                Rp 0
                            </div>
                        </div>

                        {{-- STATISTIK DAMPAK KARTU STOK --}}
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; display: flex; flex-direction: column; gap: 0.55rem; font-size: 0.85rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: #64748b;">Jumlah Bahan:</span>
                                <strong style="color: #0f172a;"><span id="sideTotalItems">0</span> Baris Item</strong>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.4rem; border-top: 1px dashed #e2e8f0;">
                                <span style="color: #64748b;">Gudang Asal:</span>
                                <strong id="sideGudangName" style="color: #0f172a; font-size: 0.8rem; max-width: 150px; text-align: right; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">-</strong>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.4rem; border-top: 1px dashed #e2e8f0;">
                                <span style="color: #64748b;">Tujuan / SPK:</span>
                                <strong id="sideTujuanName" style="color: #0f172a; font-size: 0.8rem; max-width: 150px; text-align: right; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">-</strong>
                            </div>

                            {{-- BREAKDOWN REKAPITULASI GRADE A & GRADE B --}}
                            <div style="padding: 0.55rem 0.65rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 5px; margin-top: 0.2rem;">
                                <div style="font-size: 0.7rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.35rem; display: flex; justify-content: space-between;">
                                    <span>Pemakaian per Grade:</span>
                                    <span style="color: #94a3b8; font-weight: normal;">(Bahan Baku)</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; padding: 0.2rem 0; border-bottom: 1px dashed #f1f5f9;">
                                    <span style="display: inline-flex; align-items: center; gap: 0.3rem; color: #065f46; font-weight: 600;">
                                        <span style="width: 7px; height: 7px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                                        Grade A (Super)
                                    </span>
                                    <strong id="sideGradeAQty" style="color: #065f46; font-family: monospace;">0,00 Kg</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; padding-top: 0.2rem;">
                                    <span style="display: inline-flex; align-items: center; gap: 0.3rem; color: #b45309; font-weight: 600;">
                                        <span style="width: 7px; height: 7px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
                                        Grade B (Standar)
                                    </span>
                                    <strong id="sideGradeBQty" style="color: #b45309; font-family: monospace;">0,00 Kg</strong>
                                </div>
                            </div>

                            {{-- HIGHLIGHT TOTAL QTY KELUAR --}}
                            <div style="padding: 0.55rem 0.65rem; background: #e0f2fe; border: 1px solid #bae6fd; border-radius: 5px; margin-top: 0.2rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: #0369a1; font-size: 0.775rem; font-weight: 700; text-transform: uppercase;">Total Qty Keluar:</span>
                                    <strong style="color: #0284c7; font-size: 1.05rem; font-family: monospace;"><span id="sideTotalQty">0,00</span></strong>
                                </div>
                            </div>
                        </div>

                        {{-- TOMBOL 1: SIMPAN PERUBAHAN & LANGSUNG SETUJUI (POSTING) --}}
                        <button type="button" onclick="submitVerifikasiForm('save_and_approve')" class="btn btn-primary" style="width: 100%; padding: 0.75rem 1rem; font-size: 0.925rem; font-weight: 700; background: #059669; border: 1px solid #059669; justify-content: center; box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.25); display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 0.65rem;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Simpan &amp; Langsung Setujui (Posting)</span>
                        </button>

                        {{-- TOMBOL 2: SIMPAN PERUBAHAN SAJA (TETAP DRAFT) --}}
                        <button type="button" onclick="submitVerifikasiForm('save_draft')" class="btn btn-secondary" style="width: 100%; padding: 0.65rem 1rem; font-size: 0.85rem; font-weight: 600; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; justify-content: center; display: flex; align-items: center; gap: 0.4rem; cursor: pointer; margin-bottom: 0.65rem;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            <span>Simpan Perubahan Saja (Tetap DRAFT)</span>
                        </button>

                        {{-- TOMBOL 3: TOLAK / BATALKAN DOKUMEN --}}
                        <button type="button" onclick="openModalReject({{ $pakai->pakai_id }}, '{{ $pakai->pakai_no }}')" class="btn btn-secondary" style="width: 100%; padding: 0.6rem 1rem; font-size: 0.825rem; font-weight: 600; border: 1px solid #fecaca; background: #fef2f2; color: #dc2626; justify-content: center; display: flex; align-items: center; gap: 0.35rem; cursor: pointer; margin-bottom: 0.65rem;">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Tolak / Batalkan Dokumen</span>
                        </button>
                    </div>
                </div>

                {{-- KARTU PANDUAN CEPAT --}}
                <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03); background: #ffffff;">
                    <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                        <strong style="color: #0f172a; font-size: 0.85rem;">Panduan Verifikasi &amp; Edit Terpadu</strong>
                    </div>
                    <div style="padding: 1rem 1.25rem; font-size: 0.8rem; color: #475569; line-height: 1.5;">
                        <div style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                            <span style="color: #059669; font-weight: 700;">&bull;</span>
                            <span><strong>Koreksi di Tempat:</strong> Ganti batch atau timbangan riil yang dimasukkan orang gudang langsung di tabel ini.</span>
                        </div>
                        <div style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                            <span style="color: #059669; font-weight: 700;">&bull;</span>
                            <span><strong>Simpan &amp; Langsung Setujui:</strong> Otomatis memotong saldo fisik batch dan membukukan Jurnal HPP (Debet 1151, Kredit 1150).</span>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <span style="color: #059669; font-weight: 700;">&bull;</span>
                            <span><strong>Simpan Perubahan Saja:</strong> Menyimpan rincian baru sebagai DRAFT jika masih menunggu konfirmasi lanjutan.</span>
                        </div>
                    </div>
                </div>

            </div> {{-- End Kolom Kanan (.sticky-action-sidebar) --}}
        </div> {{-- End .order-station-grid --}}
    </form>

    @include('keuangan.verifikasi_pemakaian.partials.modal-warning-fifo')

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
<script src="{{ asset('js/keuangan/verifikasi_pemakaian/verifikasi-pemakaian.js') }}"></script>
@if($pakai->isDraft())
<script>
    window.appConfig = {
        barangList: @json($barangList ?? []),
        initialItems: @json($initialItems ?? []),
        urlGetBatches: "{{ route('gudang.pemakaian.batches') }}",
        urlAlokasiResep: "{{ route('gudang.pemakaian.alokasi-resep') }}"
    };
</script>
<script src="{{ asset('js/keuangan/verifikasi_pemakaian/verifikasi-pemakaian-edit.js') }}"></script>
@endif
@endpush
