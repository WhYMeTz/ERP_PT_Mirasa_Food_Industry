@extends('layouts.app')

@section('title', 'Edit Pemakaian Bahan #' . $pakai->pakai_no . ' - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/pemakaian/pemakaian-form.css') }}?v={{ filemtime(public_path('css/gudang/pemakaian/pemakaian-form.css')) }}">
@endpush

@section('content')
{{-- HEADER BAR: JUDUL & WIDGET TOTAL LIVE DI POJOK KANAN (DENGAN FLOATING WRAPPER) --}}
<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <a href="{{ route('gudang.pemakaian.show', $pakai->pakai_id) }}" style="color: #64748b; text-decoration: none; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.2rem;">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Detail Pemakaian #{{ $pakai->pakai_no }}
        </a>
        <div style="display: flex; align-items: center; gap: 0.65rem;">
            <h1 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0;">
                Edit Pemakaian Bahan (BPPB): {{ $pakai->pakai_no }}
            </h1>
            <span class="badge" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-weight: 700; font-size: 0.75rem; padding: 0.25rem 0.6rem; border-radius: 6px;">
                ⏳ DRAFT GUDANG
            </span>
        </div>
    </div>

    {{-- WIDGET TOTAL LIVE WORKSTATION DI KANAN ATAS (FLOATING OTOMATIS SAAT SCROLL KE BAWAH) --}}
    <div id="liveTotalWidgetWrapper" style="min-height: 52px; display: flex; align-items: center;">
        <div id="liveTotalWidget" class="live-kpi-widget">
            <div>
                <div style="font-size: 0.675rem; font-weight: 700; text-transform: uppercase; color: #059669; letter-spacing: 0.04em;">
                    Total Fisik Keluar:
                </div>
                <div style="display: flex; align-items: baseline; gap: 0.25rem;">
                    <span id="sideTotalQtyLarge" style="font-size: 1.85rem; font-weight: 800; font-family: monospace; color: #047857; line-height: 1;">0,00</span>
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
            <span id="sideTotalQty" style="display:none;">0,00</span>
            <span id="sideGudangName" style="display:none;">-</span>
            <span id="sideTujuanName" style="display:none;">-</span>
        </div>
    </div>
</div>

<form action="{{ route('gudang.pemakaian.update', $pakai->pakai_id) }}" method="POST" id="pemakaianForm">
    @csrf
    @method('PUT')

    {{-- PANEL TOOLBAR TERPADU (1 KOTAK RAMPING 2 BARIS: INFO DOKUMEN & RESEP BOM) --}}
    <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        {{-- BARIS 1: NO BPPB, TANGGAL, GUDANG ASAL, TUJUAN --}}
        <div style="display: grid; grid-template-columns: 1.1fr 1fr 1.6fr 1.8fr; gap: 0.85rem; align-items: flex-end; margin-bottom: 0.85rem;">
            <div>
                <label for="pakai_no" class="toolbar-label">
                    No. BPPB <span style="color:#ef4444;">*</span>
                </label>
                <input type="text" id="pakai_no" name="pakai_no" value="{{ old('pakai_no', $pakai->pakai_no) }}" class="toolbar-input" style="font-family: monospace; font-weight: 700;" required>
            </div>
            <div>
                <label for="pakai_tgl" class="toolbar-label">
                    Tanggal Fisik <span style="color:#ef4444;">*</span>
                </label>
                <input type="date" id="pakai_tgl" name="pakai_tgl" value="{{ old('pakai_tgl', $pakai->pakai_tgl ? $pakai->pakai_tgl->format('Y-m-d') : date('Y-m-d')) }}" class="toolbar-input" required>
            </div>
            <div>
                <label for="gudang_id" class="toolbar-label">
                    Gudang Asal Barang <span style="color:#ef4444;">*</span>
                </label>
                <select name="gudang_id" id="gudang_id" class="toolbar-select" required onchange="onGudangChanged()">
                    @foreach ($gudangList as $gdg)
                        <option value="{{ $gdg->gudang_id }}" {{ old('gudang_id', $pakai->gudang_id) == $gdg->gudang_id ? 'selected' : '' }}>
                            {{ $gdg->display_name }} ({{ $gdg->gudang_cd }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="tujuan_pemakaian" class="toolbar-label">
                    Tujuan Pemakaian / Lini Kerja (WIP) <span style="color:#ef4444;">*</span>
                </label>
                <select name="tujuan_pemakaian" id="tujuan_pemakaian" class="toolbar-select" required onchange="updateSidebarInfo()">
                    <option value="">-- Pilih Lini Produksi (WIP) --</option>
                    @if(isset($liniList) && $liniList->isNotEmpty())
                        @foreach($liniList as $lini)
                            <option value="{{ $lini->lini_nm }}" {{ old('tujuan_pemakaian', $pakai->tujuan_pemakaian) === $lini->lini_nm ? 'selected' : '' }}>
                                {{ $lini->lini_nm }} {{ $lini->keterangan ? '('.$lini->keterangan.')' : '' }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>

        {{-- BARIS 2: RESEP BOM & AUTO-FIFO + CATATAN SEJAJAR --}}
        <div style="display: flex; gap: 0.75rem; align-items: center; padding-top: 0.75rem; border-top: 1px dashed #cbd5e1; flex-wrap: wrap;">
            <div style="display: inline-flex; align-items: center; gap: 0.4rem; background: #e0f2fe; color: #0369a1; border: 1.5px solid #bae6fd; padding: 0 0.85rem; border-radius: 6px; font-weight: 800; font-size: 0.875rem; white-space: nowrap; height: 42px; box-sizing: border-box;">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                <span>Resep BOM:</span>
            </div>
            
            <div style="flex: 2.5; min-width: 280px;">
                <select id="bom_select" class="toolbar-select">
                    <option value="">-- Pilih Formula Resep Standar --</option>
                    @foreach ($bomList as $bom)
                        <option value="{{ $bom->bom_id }}" data-nomor="{{ $bom->bom_no }}" data-nama="{{ $bom->bom_nm }}" data-batch="{{ (float) $bom->batch_ukuran_qty }}">
                            [{{ $bom->bom_no }}] {{ $bom->bom_nm }} (Basis: {{ number_format($bom->batch_ukuran_qty, 0, ',', '.') }} Ktn)
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; align-items: center; gap: 0.35rem;">
                <input type="number" id="target_produksi_qty" value="100" min="1" step="1" class="toolbar-input" style="width: 80px; text-align: right; font-weight: 800; font-size: 1rem;" title="Target Produksi">
                <span style="font-size: 0.825rem; font-weight: 700; color: #334155;">Ktn</span>
            </div>

            <input type="hidden" id="bom_grade_filter" value="">
            <div class="grade-pill-group" style="display: flex; gap: 0.35rem; height: 42px; align-items: center;">
                <button type="button" class="btn-chip active" id="btnBomGradeAll" onclick="setBomGradeFilter(this, '')" style="height: 40px; padding: 0 0.85rem;">Campur</button>
                <button type="button" class="btn-chip btn-chip-grade-a" id="btnBomGradeA" onclick="setBomGradeFilter(this, 'A')" style="height: 40px; padding: 0 0.85rem;">🟢 Grade A</button>
                <button type="button" class="btn-chip btn-chip-grade-b" id="btnBomGradeB" onclick="setBomGradeFilter(this, 'B')" style="height: 40px; padding: 0 0.85rem;">🟡 Grade B</button>
            </div>

            <button type="button" id="btnTarikResep" class="btn btn-primary" onclick="tarikBahanResepFifo()" style="background: #0284c7; border: 1.5px solid #0284c7; font-weight: 700; height: 42px; padding: 0 1.25rem; font-size: 0.875rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap; cursor: pointer;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span id="btnTarikText">Muat Ulang Resep</span>
            </button>

            <button type="button" class="btn btn-secondary" onclick="resetItemsTable()" style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #475569; font-weight: 700; border-radius: 6px; height: 42px; padding: 0 0.95rem; font-size: 0.85rem; cursor: pointer;" title="Kosongkan Baris">
                Reset
            </button>

            <div style="flex: 2; min-width: 180px;">
                <input type="text" id="catatan_txt" name="catatan_txt" value="{{ old('catatan_txt', $pakai->catatan_txt) }}" placeholder="Catatan shift / operator (opsional)..." class="toolbar-input" style="font-size: 0.85rem;">
            </div>
        </div>

        {{-- Container Notifikasi / Status Alokasi (Hanya muncul jika ada warning atau info FIFO) --}}
        <div id="resepFeedback" style="margin-top: 0.65rem; display: none;"></div>
    </div>

    {{-- TABEL RINCIAN FISIK BAHAN (LANGSUNG TAMPIL DI LAYAR ATAS TANPA SCROLL!) --}}
    <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.04); margin-bottom: 5.5rem;">
        <div style="background: #ffffff; padding: 0.75rem 1rem; border-bottom: 1.5px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <strong style="color: #0f172a; font-size: 0.975rem;">Rincian Fisik Bahan Dikeluarkan &amp; Alokasi Batch</strong>
                <span style="font-size: 0.775rem; color: #64748b;">
                    💡 Tekan <kbd style="background:#e2e8f0; padding:1px 5px; border-radius:3px; font-weight:700;">Enter</kbd> untuk baris baru
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

        <div style="overflow-x: auto;">
            <table class="excel-grid-table" id="itemsTable" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="min-width: 320px;">Kategori &amp; Nama Bahan Produksi <span style="color:#ef4444;">*</span></th>
                        <th style="min-width: 340px;">Pilihan Grade &amp; Batch (FIFO) <span style="color:#ef4444;">*</span></th>
                        <th style="width: 180px; text-align: right;">Qty Keluar <span style="color:#ef4444;">*</span></th>
                        <th style="width: 90px; text-align: center;">Satuan</th>
                        <th style="width: 55px; text-align: center;">Hapus</th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    {{-- Baris dirender via JS dari data eksisting --}}
                </tbody>
                <tfoot>
                    <tr style="border-top: 2px solid #cbd5e1; font-weight: 700; background: #f8fafc;">
                        <td colspan="3" style="padding: 0.75rem 1rem; text-align: right; color: #475569; font-size: 0.825rem; text-transform: uppercase; letter-spacing: 0.05em;">
                            Total Akumulasi Fisik:
                            <span style="margin-left: 0.85rem; text-transform: none; font-size: 0.8rem; display: inline-flex; gap: 0.5rem; vertical-align: middle;">
                                <span style="background: #ecfdf5; color: #065f46; border: 1.5px solid #a7f3d0; padding: 3px 8px; border-radius: 6px; font-weight: 700;">
                                    🟢 Grade A: <strong id="footGradeAQty" style="font-family: monospace; font-size: 0.95rem;">0,00</strong>
                                </span>
                                <span style="background: #fffbeb; color: #b45309; border: 1.5px solid #fde68a; padding: 3px 8px; border-radius: 6px; font-weight: 700;">
                                    🟡 Grade B: <strong id="footGradeBQty" style="font-family: monospace; font-size: 0.95rem;">0,00</strong>
                                </span>
                            </span>
                        </td>
                        <td style="padding: 0.75rem 0.75rem; text-align: right; color: #047857; font-family: monospace; font-size: 1.45rem; font-weight: 800;" id="grandTotalQty">
                            0,00
                        </td>
                        <td style="text-align: center; color: #64748b; font-size: 0.85rem; font-weight: 700;">Total Fisik</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- STICKY BOTTOM ACTION BAR (TETAP MELAYANG DI BAWAH AGAR TIDAK MENGGANGGU AREA TABEL) --}}
    <div style="position: fixed; bottom: 0; left: 0; right: 0; background: #ffffff; border-top: 2px solid #cbd5e1; padding: 0.75rem 2rem; box-shadow: 0 -4px 12px rgba(0,0,0,0.08); display: flex; justify-content: space-between; align-items: center; z-index: 1000;">
        <div style="display: flex; align-items: center; gap: 0.65rem;">
            <div style="width: 32px; height: 32px; border-radius: 50%; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.15rem; font-weight: 800;">
                ✓
            </div>
            <div>
                <strong style="color: #0f172a; font-size: 0.875rem; display: block;">Dokumen Disimpan Sebagai DRAFT Pengeluaran Fisik</strong>
                <span style="font-size: 0.75rem; color: #64748b;">Stok dipotong &amp; Jurnal Umum terbit setelah diverifikasi Admin Produksi / Office.</span>
            </div>
        </div>

        {{-- MINI LIVE SUMMARY DI BOTTOM BAR --}}
        <div style="display: flex; align-items: center; gap: 0.75rem; background: #f0fdf4; border: 1.5px solid #a7f3d0; padding: 0.4rem 1rem; border-radius: 8px;">
            <span style="font-size: 0.775rem; font-weight: 800; color: #065f46; text-transform: uppercase; letter-spacing: 0.03em;">Total Fisik:</span>
            <span style="font-size: 1.3rem; font-weight: 800; font-family: monospace; color: #047857; line-height: 1;" id="botBarTotalQty">0,00 Kg</span>
        </div>

        <div style="display: flex; gap: 0.65rem; align-items: center;">
            <a href="{{ route('gudang.pemakaian.show', $pakai->pakai_id) }}" class="btn btn-secondary" style="padding: 0.65rem 1.25rem; font-weight: 700; font-size: 0.875rem; border-radius: 6px; background: #f8fafc; border: 1.5px solid #cbd5e1;">
                Batal &amp; Kembali
            </a>
            <button type="submit" id="btnSubmitPemakaian" class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 1rem; font-weight: 800; background: #059669; border: none; border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.35); display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>Simpan Perubahan Dokumen (Draft)</span>
            </button>
        </div>
    </div>
</form>

@include('gudang.pemakaian.partials.modal-warning-stock-limit')
@include('gudang.pemakaian.partials.modal-warning-fifo')
@include('gudang.pemakaian.partials.modal-warning-grade-shortage')
@endsection

@push('scripts')
<script>
    window.appConfig = {
        barangList: @json($barangList),
        existingDetails: @json($pakai->details),
        batchesUrl: "{{ route('gudang.pemakaian.batches', [], false) }}",
        alokasiResepUrl: "{{ route('gudang.pemakaian.alokasi-resep', [], false) }}"
    };
</script>
<script src="{{ asset('js/gudang/pemakaian/pemakaian-edit.js') }}?v={{ filemtime(public_path('js/gudang/pemakaian/pemakaian-edit.js')) }}"></script>
@endpush
