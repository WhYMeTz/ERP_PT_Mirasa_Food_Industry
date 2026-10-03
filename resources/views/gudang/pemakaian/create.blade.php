@extends('layouts.app')

@section('title', 'Catat Pemakaian Bahan - ERP PT Mirasa')

<style>
    .order-station-grid {
        display: grid;
        grid-template-columns: 2.3fr 1fr;
        gap: 1.5rem;
        align-items: start;
        margin-bottom: 2rem;
    }
    @media (max-width: 1100px) {
        .order-station-grid { grid-template-columns: 1fr; }
        .sticky-action-sidebar { position: static !important; top: auto !important; }
    }

    /* Excel Table Styling */
    .excel-grid-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.825rem;
    }
    .excel-grid-table th {
        background: #0f172a;
        color: #f8fafc;
        font-weight: 600;
        padding: 0.55rem 0.45rem;
        border: 1px solid #334155;
        font-size: 0.775rem;
        letter-spacing: 0.02em;
        text-align: left;
    }
    .excel-grid-table td {
        padding: 0.35rem 0.45rem;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        vertical-align: middle;
    }
    .excel-grid-table tr:nth-child(even) td {
        background: #fafafa;
    }
    .excel-grid-table tr:hover td {
        background: #fff1f2;
    }
    .excel-grid-table .form-control {
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 0.35rem 0.45rem !important;
        font-size: 0.825rem !important;
        height: 32px;
        box-sizing: border-box;
        width: 100%;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .excel-grid-table .form-control:focus {
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.2) !important;
        background: #ffffff !important;
    }

    .btn-chip {
        padding: 0.2rem 0.5rem;
        font-size: 0.725rem;
        font-weight: 600;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #475569;
        cursor: pointer;
        transition: all 0.15s ease;
        line-height: 1.2;
    }
    .btn-chip:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .btn-chip.active {
        background: #0284c7;
        color: #ffffff;
        border-color: #0284c7;
        box-shadow: 0 1px 2px rgba(2, 132, 199, 0.2);
    }
</style>

@section('content')
<div style="margin-bottom: 1.25rem;">
    <a href="{{ route('gudang.pemakaian.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Daftar Pemakaian Bahan
    </a>
    <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">
        Form Pemakaian Bahan (Outbound)
    </h1>
    <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
        Pencatatan pemakaian bahan baku &amp; penolong ke lini produksi atau packing dengan pemotongan stok otomatis (FIFO).
    </p>
</div>

<form action="{{ route('gudang.pemakaian.store') }}" method="POST" id="pemakaianForm">
    @csrf

    <div class="order-station-grid">
        {{-- KOLOM KIRI: FORMULIR UTAMA & TABEL (2.3fr) --}}
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            {{-- KARTU 1: INFORMASI DOKUMEN & PENGELUARAN --}}
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                    <strong style="color: #0f172a; font-size: 0.95rem;">1. Informasi Dokumen &amp; Tujuan Pengeluaran</strong>
                    <span class="badge" style="background: #fee2e2; color: #991b1b; font-weight: 700; font-size: 0.725rem;">Transaksi Outbound</span>
                </div>
                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1.25rem;">
                    
                    {{-- BARIS 1: NO DOKUMEN, TANGGAL, GUDANG ASAL, TUJUAN / SPK --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="pakai_no" class="form-label" style="font-weight: 600; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem;">
                                Nomor Dokumen Pengeluaran <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" id="pakai_no" name="pakai_no" value="{{ old('pakai_no', $autoNo) }}" class="form-control" style="font-family: monospace; font-weight: 600; height: 38px; border-radius: 6px; font-size: 0.85rem;" required>
                            <small style="color: #64748b; font-size: 0.725rem;">Nomor urut Bukti Pengeluaran Barang (BPPB).</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="pakai_tgl" class="form-label" style="font-weight: 600; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem;">
                                Tanggal Pengeluaran Fisik <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="date" id="pakai_tgl" name="pakai_tgl" value="{{ old('pakai_tgl', date('Y-m-d')) }}" class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;" required>
                            <small style="color: #64748b; font-size: 0.725rem;">Waktu pengeluaran bahan dari gudang ke lantai pabrik.</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="gudang_id" class="form-label" style="font-weight: 600; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem;">
                                Gudang Asal Barang <span style="color: #ef4444;">*</span>
                            </label>
                            @if ($userGudangId)
                                @php $lockedGdg = $gudangList->firstWhere('gudang_id', $userGudangId); @endphp
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <input type="text" class="form-control" value="{{ $lockedGdg?->display_name }} ({{ $lockedGdg?->gudang_cd }})" disabled style="background: #f1f5f9; font-weight: 600; height: 38px; border-radius: 6px; font-size: 0.85rem;">
                                    <input type="hidden" name="gudang_id" id="gudang_id" value="{{ $userGudangId }}">
                                    <span class="badge" style="background: #e2e8f0; color: #475569; display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600; white-space: nowrap;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        Terkunci
                                    </span>
                                </div>
                            @else
                                <select name="gudang_id" id="gudang_id" class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;" required onchange="onGudangChanged()">
                                    <option value="">-- Pilih Lokasi Asal --</option>
                                    @foreach ($gudangList as $gdg)
                                        <option value="{{ $gdg->gudang_id }}" {{ old('gudang_id') == $gdg->gudang_id ? 'selected' : '' }}>
                                            {{ $gdg->display_name }} ({{ $gdg->gudang_cd }})
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            <small style="color: #64748b; font-size: 0.725rem;">Lokasi gudang fisik tempat stok dipotong.</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="tujuan_pemakaian" class="form-label" style="font-weight: 600; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem;">
                                Tujuan Pemakaian / Lini Kerja <span style="color: #ef4444;">*</span>
                            </label>
                            <select name="tujuan_pemakaian" id="tujuan_pemakaian" class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;" required onchange="updateSidebarInfo()">
                                <option value="">-- Pilih Lini Produksi / Tujuan Pengeluaran --</option>
                                @if(isset($liniList) && $liniList->isNotEmpty())
                                    @foreach($liniList->groupBy(fn($item) => $item->kategori_lini ?: 'UMUM') as $kategori => $items)
                                        <optgroup label="{{ $kategori }}">
                                            @foreach($items as $lini)
                                                <option value="{{ $lini->lini_nm }}" {{ old('tujuan_pemakaian', 'PRODUKSI IFM') === $lini->lini_nm ? 'selected' : '' }}>
                                                    {{ $lini->lini_nm }} {{ $lini->keterangan ? '('.$lini->keterangan.')' : '' }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                @endif
                                @if(isset($opsiKhusus) && !empty($opsiKhusus))
                                    <optgroup label="KEPERLUAN OPERASIONAL LAINNYA">
                                        @foreach($opsiKhusus as $opsi)
                                            <option value="{{ $opsi }}" {{ old('tujuan_pemakaian') === $opsi ? 'selected' : '' }}>
                                                {{ $opsi }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                            <small style="color: #64748b; font-size: 0.725rem;">Pilih lini kerja produksi atau peruntukan operasional pabrik.</small>
                        </div>
                    </div>

                    {{-- BARIS 2: CATATAN TAMBAHAN --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="catatan_txt" class="form-label" style="font-weight: 600; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem;">
                            Catatan Tambahan Pengeluaran (Opsional)
                        </label>
                        <input type="text" id="catatan_txt" name="catatan_txt" value="{{ old('catatan_txt') }}" placeholder="Keterangan shift kerja, operator penerima lini pabrik, memo internal, dll." class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;">
                    </div>
                </div>
            </div>

            {{-- KARTU 2: REKOMENDASI RESEP PRODUKSI (AUTO-FIFO) --}}
            <div class="card" style="border: 1px solid #cbd5e1; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; padding: 0.85rem 1.25rem;">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <div style="width: 28px; height: 28px; border-radius: 6px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        </div>
                        <div>
                            <strong style="font-size: 0.95rem; color: #0f172a; margin: 0; display: block;">
                                2. Tarik Kebutuhan Bahan Berdasarkan Resep Produksi (Auto-FIFO)
                            </strong>
                            <p style="color: #64748b; font-size: 0.775rem; margin: 0.15rem 0 0 0;">
                                Kalkulasi otomatis proporsi bahan baku &amp; alokasi nomor batch terlama yang dibeli (FIFO/FEFO).
                            </p>
                        </div>
                    </div>
                    <span class="badge" style="background: #f1f5f9; color: #334155; font-weight: 600; font-size: 0.725rem; padding: 0.3rem 0.6rem; border: 1px solid #cbd5e1;">
                        Kalkulasi Formula Otomatis
                    </span>
                </div>
                <div style="padding: 1.25rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; align-items: flex-end;">
                        <div>
                            <label style="display: block; font-weight: 600; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem;">
                                Pilih Formula Resep Produk (BOM)
                            </label>
                            <select id="bom_select" class="form-control" style="font-weight: 600; border-color: #cbd5e1; font-size: 0.85rem; height: 38px; border-radius: 6px;">
                                <option value="">-- Pilih Formula Resep Standar --</option>
                                @foreach ($bomList as $bom)
                                    <option value="{{ $bom->bom_id }}" data-nomor="{{ $bom->bom_no }}" data-nama="{{ $bom->bom_nm }}" data-batch="{{ (float) $bom->batch_ukuran_qty }}">
                                        [{{ $bom->bom_no }}] {{ $bom->bom_nm }} (Basis: {{ number_format($bom->batch_ukuran_qty, 0, ',', '.') }} Karton)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div style="max-width: 200px;">
                            <label style="display: block; font-weight: 600; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem;">
                                Target Rencana Produksi
                            </label>
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <input type="number" id="target_produksi_qty" value="100" min="1" step="1" class="form-control" style="font-weight: 700; font-size: 0.95rem; text-align: right; border-color: #cbd5e1; height: 38px; border-radius: 6px;">
                                <span style="font-size: 0.85rem; font-weight: 600; color: #475569;">Karton</span>
                            </div>
                        </div>

                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <button type="button" id="btnTarikResep" class="btn btn-primary" onclick="tarikBahanResepFifo()" style="background: #0284c7; border: 1px solid #0284c7; font-weight: 600; padding: 0.55rem 1.15rem; font-size: 0.85rem; border-radius: 6px; box-shadow: 0 1px 2px rgba(2, 132, 199, 0.2);">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span id="btnTarikText">Muat Batch FIFO Tertua</span>
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="resetItemsTable()" style="background: #ffffff; border: 1px solid #cbd5e1; color: #64748b; border-radius: 6px; padding: 0.55rem 0.85rem;" title="Kosongkan Baris">
                                Reset Baris
                            </button>
                        </div>
                    </div>

                    {{-- Container Notifikasi / Status Alokasi --}}
                    <div id="resepFeedback" style="margin-top: 1rem; display: none;"></div>
                </div>
            </div>

            {{-- KARTU 3: RINCIAN BAHAN YANG DIKELUARKAN --}}
            <div class="card" style="border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <div class="card-header" style="background: #ffffff; padding: 0.875rem 1.25rem; border-bottom: 1px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <strong style="color: #0f172a; font-size: 0.95rem;">3. Rincian Fisik Bahan Dikeluarkan &amp; Alokasi Batch</strong>
                        <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.5rem;">
                            💡 Tekan <kbd style="background:#e2e8f0; padding:2px 5px; border-radius:3px; font-weight:700;">Enter</kbd> untuk berpindah baris layaknya Excel
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
                        <button type="button" class="btn btn-primary btn-sm" onclick="addRow('')" style="font-weight: 600; font-size: 0.775rem; border-radius: 6px; background: #dc2626; border: none; padding: 0.3rem 0.75rem;">
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
                                <th style="min-width: 220px;">Pilih Batch (Sisa Stok FIFO) <span style="color:#ef4444;">*</span></th>
                                <th style="width: 115px; text-align: right;">Qty Keluar <span style="color:#ef4444;">*</span></th>
                                <th style="width: 65px; text-align: center;">Satuan</th>
                                <th style="width: 125px; text-align: right;">Harga Satuan (Rp)</th>
                                <th style="width: 135px; text-align: right;">Total Biaya (HPP)</th>
                                <th style="width: 40px; text-align: center;">Hapus</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            {{-- Row Template rendered via JS --}}
                        </tbody>
                        <tfoot>
                            <tr style="border-top: 2px solid #cbd5e1; font-weight: 700; background: #f8fafc;">
                                <td colspan="3" style="padding: 0.65rem 0.75rem; text-align: right; color: #475569; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                    Total Akumulasi Fisik &amp; HPP:
                                </td>
                                <td style="padding: 0.65rem 0.5rem; text-align: right; color: #dc2626; font-family: monospace; font-size: 0.95rem; font-weight: 700;" id="grandTotalQty">
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
                <div class="card-header" style="background: #0f172a; color: #ffffff; padding: 0.875rem 1.25rem;">
                    <div style="font-size: 0.725rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; color: #94a3b8;">
                        Ringkasan Pengeluaran
                    </div>
                    <strong style="color: #ffffff; font-size: 1.05rem;">Estimasi Nilai HPP Bahan Keluar</strong>
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

                        {{-- HIGHLIGHT TOTAL QTY KELUAR --}}
                        <div style="padding: 0.55rem 0.65rem; background: #fee2e2; border: 1px solid #fca5a5; border-radius: 5px; margin-top: 0.2rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: #991b1b; font-size: 0.775rem; font-weight: 700; text-transform: uppercase;">Total Qty Keluar:</span>
                                <strong style="color: #dc2626; font-size: 1.05rem; font-family: monospace;"><span id="sideTotalQty">0,00</span></strong>
                            </div>
                            <div style="font-size: 0.7rem; color: #991b1b; margin-top: 0.2rem;">
                                Dampak: Mengurangi Saldo Stok (FIFO)
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 1.25rem; font-size: 0.75rem; color: #64748b; line-height: 1.4; display: flex; gap: 0.35rem;">
                        <svg width="15" height="15" fill="none" stroke="#dc2626" viewBox="0 0 24 24" style="flex-shrink: 0; margin-top: 1px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Stok fisik gudang berkurang otomatis (FIFO) dan tercatat pada Kartu Stok saat disimpan.</span>
                    </div>

                    {{-- TOMBOL UTAMA: SIMPAN & POTONG STOK SEKARANG --}}
                    <button type="submit" id="btnSubmitPemakaian" class="btn btn-primary" style="width: 100%; padding: 0.75rem 1rem; font-size: 0.95rem; font-weight: 700; background: #dc2626; border: none; justify-content: center; box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.25); display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan &amp; Potong Stok</span>
                    </button>

                    <a href="{{ route('gudang.pemakaian.index') }}" class="btn btn-secondary" style="width: 100%; justify-content: center; margin-top: 0.65rem; font-size: 0.85rem; padding: 0.5rem;">
                        Batal &amp; Kembali ke Daftar
                    </a>
                </div>
            </div>

            {{-- KARTU PANDUAN CEPAT OPERATOR --}}
            <div class="card" style="border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03); background: #ffffff;">
                <div class="card-header" style="background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e2e8f0;">
                    <strong style="color: #0f172a; font-size: 0.85rem;">Panduan Pemakaian Bahan</strong>
                </div>
                <div style="padding: 1rem 1.25rem; font-size: 0.8rem; color: #475569; line-height: 1.5;">
                    <div style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                        <span style="color: #dc2626; font-weight: 700;">&bull;</span>
                        <span><strong>Alokasi Batch FIFO:</strong> Sistem otomatis memilih batch tertua yang masih memiliki saldo fisik di gudang.</span>
                    </div>
                    <div style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                        <span style="color: #dc2626; font-weight: 700;">&bull;</span>
                        <span><strong>Resep Produksi (BOM):</strong> Tarik formula produk untuk otomatis membagi proporsi singkong, bumbu, dan kemasan.</span>
                    </div>
                    <div style="margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                        <span style="color: #dc2626; font-weight: 700;">&bull;</span>
                        <span><strong>Cek Sisa Fisik:</strong> Baris akan berwarna merah jika kuantitas keluar melebihi stok batch yang tersedia.</span>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <span style="color: #dc2626; font-weight: 700;">&bull;</span>
                        <span>Gunakan tombol chip kategori untuk memfilter opsi pilihan bahan baku, bumbu, atau kemasan.</span>
                    </div>
                </div>
            </div>

        </div> {{-- End Kolom Kanan (.sticky-action-sidebar) --}}
    </div> {{-- End .order-station-grid --}}
</form>

{{-- Data Barang Cache untuk Client-side JS --}}
<script>
    const BARANG_LIST = @json($barangList);
    let rowIndex = 0;

    function getSelectedGudangId() {
        const el = document.getElementById('gudang_id');
        return el ? el.value : '';
    }

    function updateSidebarInfo() {
        const gdgSelect = document.getElementById('gudang_id');
        const sideGdg = document.getElementById('sideGudangName');
        if (sideGdg && gdgSelect) {
            if (gdgSelect.tagName === 'SELECT') {
                const opt = gdgSelect.options[gdgSelect.selectedIndex];
                sideGdg.innerText = (opt && opt.value) ? opt.text : '- Belum Dipilih -';
            } else {
                sideGdg.innerText = gdgSelect.value ? gdgSelect.value : '- Belum Dipilih -';
            }
        }

        const tujuanInput = document.getElementById('tujuan_pemakaian');
        const sideTujuan = document.getElementById('sideTujuanName');
        if (sideTujuan && tujuanInput) {
            sideTujuan.innerText = tujuanInput.value ? tujuanInput.value : '- Belum Diisi -';
        }
    }

    function onGudangChanged() {
        updateSidebarInfo();
        // Reset all batch selections when warehouse changes
        document.querySelectorAll('#itemsBody tr').forEach(row => {
            const barangSelect = row.querySelector('.barang-select');
            if (barangSelect && barangSelect.value) {
                fetchBatchesForRow(row, barangSelect.value);
            }
        });
    }

    function renderBarangOptions(selectedBarangId = '', categoryFilter = '') {
        const filtered = categoryFilter 
            ? BARANG_LIST.filter(b => b.kategori_kelompok === categoryFilter)
            : BARANG_LIST;

        let html = '<option value="">-- Pilih Bahan Produksi --</option>';

        if (categoryFilter) {
            filtered.forEach(b => {
                const jenis = b.jenis_barang ? b.jenis_barang.jenis_barang_cd : '';
                const harga = b.harga_beli_standar || 0;
                const isSelected = (parseInt(b.barang_id) === parseInt(selectedBarangId)) ? 'selected' : '';
                html += `<option value="${b.barang_id}" data-harga="${harga}" data-satuan="${b.satuan_dasar?.satuan_nm || ''}" data-kategori="${b.kategori_kelompok}" ${isSelected}>[${b.barang_cd}] ${b.barang_nm} (${jenis})</option>`;
            });
        } else {
            const groups = {
                'BAHAN_BAKU': { label: 'Bahan Baku (Singkong, Ubi, Opak, Puyur)', items: [] },
                'BAHAN_PENOLONG': { label: 'Bahan Penolong & Bumbu (Minyak, Bumbu, dll)', items: [] },
                'KEMASAN': { label: 'Kemasan & Packaging (Karton, Plastik, Lakban)', items: [] },
            };

            filtered.forEach(b => {
                const grp = b.kategori_kelompok || 'BAHAN_PENOLONG';
                if (groups[grp]) {
                    groups[grp].items.push(b);
                } else {
                    groups['BAHAN_PENOLONG'].items.push(b);
                }
            });

            for (const [key, grp] of Object.entries(groups)) {
                if (grp.items.length > 0) {
                    html += `<optgroup label="${grp.label}">`;
                    grp.items.forEach(b => {
                        const jenis = b.jenis_barang ? b.jenis_barang.jenis_barang_cd : '';
                        const harga = b.harga_beli_standar || 0;
                        const isSelected = (parseInt(b.barang_id) === parseInt(selectedBarangId)) ? 'selected' : '';
                        html += `<option value="${b.barang_id}" data-harga="${harga}" data-satuan="${b.satuan_dasar?.satuan_nm || ''}" data-kategori="${b.kategori_kelompok}" ${isSelected}>[${b.barang_cd}] ${b.barang_nm} (${jenis})</option>`;
                    });
                    html += `</optgroup>`;
                }
            }
        }

        return html;
    }

    function setRowCategory(btn, cat) {
        const group = btn.closest('.cat-pill-group');
        if (group) {
            group.querySelectorAll('.btn-chip').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }

        const row = btn.closest('tr');
        const barangSelect = row.querySelector('.barang-select');
        const currentVal = barangSelect.value;
        barangSelect.innerHTML = renderBarangOptions(currentVal, cat);
        // Jika barang yang terpilih sebelumnya tidak ada di kategori baru, reset
        if (currentVal && !barangSelect.value) {
            onBarangSelect(barangSelect);
        }
    }

    function updateRowNumbers() {
        document.querySelectorAll('#itemsBody tr').forEach((row, idx) => {
            const numEl = row.querySelector('.row-num');
            if (numEl) numEl.innerText = idx + 1;
        });
    }

    function attachExcelKeyboardEvents(rowElement) {
        const inputs = rowElement.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const allRows = Array.from(document.querySelectorAll('#itemsBody tr'));
                    const currentRowIdx = allRows.indexOf(rowElement);

                    if (input.classList.contains('qty-input') || input.classList.contains('harga-input')) {
                        if (currentRowIdx === allRows.length - 1) {
                            addRow();
                            const newRows = document.querySelectorAll('#itemsBody tr');
                            const lastRow = newRows[newRows.length - 1];
                            lastRow.querySelector('.barang-select')?.focus();
                        } else {
                            const nextRow = allRows[currentRowIdx + 1];
                            const targetClass = input.classList.contains('qty-input') ? '.qty-input' : '.harga-input';
                            const target = nextRow.querySelector(targetClass);
                            if (target) target.focus();
                        }
                    }
                }
            });
        });
    }

    function addRow(initialCategory = '') {
        const tbody = document.getElementById('itemsBody');
        const tr = document.createElement('tr');
        tr.id = `row-${rowIndex}`;

        const barangOptions = renderBarangOptions('', initialCategory);

        tr.innerHTML = `
            <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f1f5f9; font-size: 0.8rem;">
                ${tbody.children.length + 1}
            </td>
            <td style="padding: 0.45rem 0.5rem;">
                <div class="cat-pill-group" style="display: flex; gap: 0.25rem; margin-bottom: 0.35rem; align-items: center; flex-wrap: wrap;">
                    <button type="button" class="btn-chip ${initialCategory === '' ? 'active' : ''}" onclick="setRowCategory(this, '')">Semua</button>
                    <button type="button" class="btn-chip ${initialCategory === 'BAHAN_BAKU' ? 'active' : ''}" onclick="setRowCategory(this, 'BAHAN_BAKU')">Bahan Baku</button>
                    <button type="button" class="btn-chip ${initialCategory === 'BAHAN_PENOLONG' ? 'active' : ''}" onclick="setRowCategory(this, 'BAHAN_PENOLONG')">Bumbu / Penolong</button>
                    <button type="button" class="btn-chip ${initialCategory === 'KEMASAN' ? 'active' : ''}" onclick="setRowCategory(this, 'KEMASAN')">Kemasan</button>
                </div>
                <select name="items[${rowIndex}][barang_id]" class="form-control barang-select" style="font-size: 0.85rem;" required onchange="onBarangSelect(this)">
                    ${barangOptions}
                </select>
            </td>
            <td style="padding: 0.45rem 0.5rem;">
                <select name="items[${rowIndex}][batch_no]" class="form-control batch-select" style="font-size: 0.85rem;" required onchange="onBatchSelect(this)">
                    <option value="">-- Pilih Barang Dulu --</option>
                </select>
                <div class="batch-info" style="font-size: 0.725rem; color: #059669; font-weight: 600; margin-top: 0.2rem;"></div>
            </td>
            <td style="padding: 0.45rem 0.5rem;">
                <input type="number" step="0.0001" min="0.0001" name="items[${rowIndex}][qty_keluar]" class="form-control qty-input" placeholder="0" style="font-weight: 700; text-align: right; font-size: 0.875rem;" required oninput="calcRow(this)">
            </td>
            <td style="text-align: center; padding: 0.45rem 0.5rem;">
                <span class="row-satuan" style="font-weight: 700; color: #475569; font-size: 0.8rem;">-</span>
            </td>
            <td style="padding: 0.45rem 0.5rem;">
                <input type="number" step="0.01" min="0" name="items[${rowIndex}][harga_satuan]" class="form-control harga-input" placeholder="0" style="text-align: right; font-size: 0.85rem;" oninput="calcRow(this)">
            </td>
            <td style="padding: 0.45rem 0.5rem; text-align: right; font-weight: 700; color: #0f172a; font-size: 0.875rem; font-family: monospace;" class="subtotal-cell">
                Rp 0
            </td>
            <td style="padding: 0.45rem 0.5rem; text-align: center;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="removeRow(this)" style="color: #ef4444; padding: 0.2rem 0.5rem; border-radius: 4px;" title="Hapus Baris">&times;</button>
            </td>
        `;

        tbody.appendChild(tr);
        attachExcelKeyboardEvents(tr);
        rowIndex++;
        updateRowNumbers();
        calculateGrandTotal();
    }

    function removeRow(btn) {
        const tbody = document.getElementById('itemsBody');
        if (tbody.children.length > 1) {
            btn.closest('tr').remove();
            updateRowNumbers();
            calculateGrandTotal();
        } else {
            alert('Minimal harus ada 1 item barang yang dikeluarkan.');
        }
    }

    function resetItemsTable() {
        const tbody = document.getElementById('itemsBody');
        tbody.innerHTML = '';
        rowIndex = 0;
        addRow();
        calculateGrandTotal();
        const feedback = document.getElementById('resepFeedback');
        if (feedback) feedback.style.display = 'none';
    }

    function addRowFromAllocation(item) {
        const tbody = document.getElementById('itemsBody');
        const tr = document.createElement('tr');
        tr.id = `row-${rowIndex}`;

        const itemObj = BARANG_LIST.find(b => parseInt(b.barang_id) === parseInt(item.barang_id));
        const itemCat = itemObj?.kategori_kelompok || '';
        const barangOptions = renderBarangOptions(item.barang_id, itemCat);

        let batchOptions = '';
        if (!item.all_batches || item.all_batches.length === 0) {
            batchOptions = '<option value="">(Stok Fisik Habis di Gudang ini)</option>';
        } else {
            item.all_batches.forEach(b => {
                const isSelected = (b.batch_no === item.batch_no) ? 'selected' : '';
                const prefix = b.is_fifo_top ? '[FIFO Prioritas] ' : '• ';
                const expInfo = b.expired_tgl ? ` | Exp: ${b.expired_tgl}` : '';
                const tglTerima = b.tgl_terima ? ` | Masuk: ${b.tgl_terima}` : '';
                batchOptions += `<option value="${b.batch_no}" data-sisa="${b.sisa_qty}" data-harga="${b.harga_satuan || 0}" data-masuk="${b.tgl_terima}" data-exp="${b.expired_tgl || '-'}" ${isSelected}>
                    ${prefix}${b.batch_no} (Sisa: ${parseFloat(b.sisa_qty).toLocaleString('id-ID')})${tglTerima}${expInfo}
                </option>`;
            });
        }

        let badgeHtml = '';
        if (item.is_allocated) {
            const badgeBg = item.is_split ? '#fef3c7' : '#ecfdf5';
            const badgeColor = item.is_split ? '#92400e' : '#065f46';
            const badgeBorder = item.is_split ? '#fde68a' : '#a7f3d0';
            badgeHtml = `<span style="color: ${badgeColor}; font-weight: 700; background: ${badgeBg}; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid ${badgeBorder}; display: inline-flex; align-items: center; gap: 0.25rem;">
                ${item.catatan_fifo} • Sisa: ${item.sisa_batch.toLocaleString('id-ID')} unit
            </span>`;
        } else {
            badgeHtml = `<span style="color: #dc2626; font-weight: 700; background: #fee2e2; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid #fecaca; display: inline-block;">
                ${item.catatan_fifo}
            </span>`;
        }

        const subtotal = (parseFloat(item.qty_keluar) || 0) * (parseFloat(item.harga_satuan) || 0);

        tr.innerHTML = `
            <td class="row-num" style="font-weight: 700; text-align: center; color: #475569; background: #f1f5f9; font-size: 0.8rem;">
                ${tbody.children.length + 1}
            </td>
            <td style="padding: 0.45rem 0.5rem;">
                <div class="cat-pill-group" style="display: flex; gap: 0.25rem; margin-bottom: 0.35rem; align-items: center; flex-wrap: wrap;">
                    <button type="button" class="btn-chip ${itemCat === '' ? 'active' : ''}" onclick="setRowCategory(this, '')">Semua</button>
                    <button type="button" class="btn-chip ${itemCat === 'BAHAN_BAKU' ? 'active' : ''}" onclick="setRowCategory(this, 'BAHAN_BAKU')">Bahan Baku</button>
                    <button type="button" class="btn-chip ${itemCat === 'BAHAN_PENOLONG' ? 'active' : ''}" onclick="setRowCategory(this, 'BAHAN_PENOLONG')">Bumbu / Penolong</button>
                    <button type="button" class="btn-chip ${itemCat === 'KEMASAN' ? 'active' : ''}" onclick="setRowCategory(this, 'KEMASAN')">Kemasan</button>
                </div>
                <select name="items[${rowIndex}][barang_id]" class="form-control barang-select" style="font-size: 0.85rem;" required onchange="onBarangSelect(this)">
                    ${barangOptions}
                </select>
            </td>
            <td style="padding: 0.45rem 0.5rem;">
                <select name="items[${rowIndex}][batch_no]" class="form-control batch-select" style="font-size: 0.85rem;" required onchange="onBatchSelect(this)">
                    ${batchOptions}
                </select>
                <div class="batch-info" style="font-size: 0.725rem; margin-top: 0.2rem;">
                    ${badgeHtml}
                </div>
            </td>
            <td style="padding: 0.45rem 0.5rem;">
                <input type="number" step="0.0001" min="0.0001" name="items[${rowIndex}][qty_keluar]" value="${item.qty_keluar}" class="form-control qty-input" placeholder="0" style="font-weight: 700; text-align: right; font-size: 0.875rem;" required oninput="calcRow(this)">
            </td>
            <td style="text-align: center; padding: 0.45rem 0.5rem;">
                <span class="row-satuan" style="font-weight: 700; color: #475569; font-size: 0.8rem;">${item.satuan_nm || '-'}</span>
            </td>
            <td style="padding: 0.45rem 0.5rem;">
                <input type="number" step="0.01" min="0" name="items[${rowIndex}][harga_satuan]" value="${item.harga_satuan}" class="form-control harga-input" placeholder="0" style="text-align: right; font-size: 0.85rem;" oninput="calcRow(this)">
            </td>
            <td style="padding: 0.45rem 0.5rem; text-align: right; font-weight: 700; color: #0f172a; font-size: 0.875rem; font-family: monospace;" class="subtotal-cell">
                Rp ${subtotal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
            </td>
            <td style="padding: 0.45rem 0.5rem; text-align: center;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="removeRow(this)" style="color: #ef4444; padding: 0.2rem 0.5rem; border-radius: 4px;" title="Hapus Baris">&times;</button>
            </td>
        `;

        tbody.appendChild(tr);
        attachExcelKeyboardEvents(tr);
        rowIndex++;
        updateRowNumbers();
    }

    function tarikBahanResepFifo() {
        const gudangId = getSelectedGudangId();
        if (!gudangId) {
            alert('Harap pilih Gudang Asal Barang terlebih dahulu di formulir bagian atas!');
            document.getElementById('gudang_id')?.focus();
            return;
        }

        const bomSelect = document.getElementById('bom_select');
        const bomId = bomSelect ? bomSelect.value : '';
        if (!bomId) {
            alert('Harap pilih Formula Resep Produk (BOM) terlebih dahulu!');
            bomSelect?.focus();
            return;
        }

        const targetQty = parseFloat(document.getElementById('target_produksi_qty').value) || 0;
        if (targetQty <= 0) {
            alert('Target Rencana Produksi harus lebih dari 0 karton!');
            document.getElementById('target_produksi_qty')?.focus();
            return;
        }

        const btn = document.getElementById('btnTarikResep');
        const btnText = document.getElementById('btnTarikText');
        const feedback = document.getElementById('resepFeedback');

        btn.disabled = true;
        btnText.innerHTML = 'Menghitung Alokasi Batch FIFO...';
        feedback.style.display = 'none';

        fetch(`{{ route('gudang.pemakaian.alokasi-resep') }}?gudang_id=${gudangId}&bom_id=${bomId}&target_qty=${targetQty}`)
            .then(res => res.json())
            .then(res => {
                btn.disabled = false;
                btnText.innerHTML = 'Muat Batch FIFO Tertua';

                if (res.status === 'success') {
                    const data = res.data;
                    const items = data.items || [];

                    if (items.length === 0) {
                        alert('Resep ini belum memiliki rincian bahan baku terdaftar.');
                        return;
                    }

                    // Sesuaikan Tujuan Pemakaian otomatis sesuai resep jika masih default
                    const tujuanInput = document.getElementById('tujuan_pemakaian');
                    if (tujuanInput) {
                        const namaResep = (data.bom_nm || '').toUpperCase();
                        if (namaResep.includes('IFM') || namaResep.includes('INDOFOOD')) {
                            tujuanInput.value = 'PRODUKSI IFM';
                        } else if (namaResep.includes('2000')) {
                            tujuanInput.value = 'PRODUKSI PING-PING 2000';
                        } else if (namaResep.includes('PING-PING')) {
                            tujuanInput.value = 'PRODUKSI PING-PING';
                        } else {
                            tujuanInput.value = `PRODUKSI ${data.bom_no}`;
                        }
                        updateSidebarInfo();
                    }

                    // Kosongkan baris tabel dan render baris alokasi FIFO
                    const tbody = document.getElementById('itemsBody');
                    tbody.innerHTML = '';
                    rowIndex = 0;

                    items.forEach(item => {
                        addRowFromAllocation(item);
                    });

                    calculateGrandTotal();

                    // Render banner status / feedback
                    feedback.style.display = 'block';
                    if (data.is_lengkap) {
                        feedback.innerHTML = `
                            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; padding: 0.75rem 1rem; color: #065f46; display: flex; align-items: flex-start; gap: 0.65rem;">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0; color: #059669; margin-top: 2px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <div>
                                    <strong style="display: block; font-size: 0.875rem;">Alokasi Batch Tertua (FIFO) Berhasil Dimuat:</strong>
                                    <span style="font-size: 0.8rem; line-height: 1.4;">
                                        Total <strong>${items.length} baris bahan baku</strong> telah dialokasikan otomatis dari batch paling lama untuk target <strong>${targetQty} ${data.satuan_target}</strong>.
                                        Operator gudang dapat memeriksa dan menyesuaikan angka timbangan aktual jika terdapat deviasi sebelum klik simpan.
                                    </span>
                                </div>
                            </div>
                        `;
                    } else {
                        const peringatanItems = (data.peringatan || []).map(p => `<li>${p}</li>`).join('');
                        feedback.innerHTML = `
                            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 0.75rem 1rem; color: #92400e;">
                                <div style="display: flex; align-items: flex-start; gap: 0.65rem; margin-bottom: 0.35rem;">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0; color: #d97706; margin-top: 2px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <div>
                                        <strong style="display: block; font-size: 0.875rem;">Perhatian: Ada Bahan Fisik yang Kurang di Gudang Ini</strong>
                                        <span style="font-size: 0.8rem;">
                                            Sistem berhasil memuat batch tertua yang tersedia, namun beberapa item tidak mencukupi untuk memenuhi seluruh kebutuhan resep (${targetQty} ${data.satuan_target}):
                                        </span>
                                    </div>
                                </div>
                                <ul style="margin: 0.35rem 0 0 1.75rem; font-size: 0.8rem; padding: 0; line-height: 1.5;">
                                    ${peringatanItems}
                                </ul>
                            </div>
                        `;
                    }

                    // Smooth scroll ke tabel rincian bahan
                    document.getElementById('itemsTable')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    alert('Gagal: ' + res.message);
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btnText.innerHTML = 'Muat Batch FIFO Tertua';
                alert('Terjadi kesalahan koneksi saat menghitung alokasi resep.');
            });
    }

    function onBarangSelect(selectEl) {
        const row = selectEl.closest('tr');
        const selectedOpt = selectEl.selectedOptions[0];
        const barangId = selectEl.value;
        const satuan = selectedOpt ? selectedOpt.getAttribute('data-satuan') : '';
        const defaultHarga = selectedOpt ? parseFloat(selectedOpt.getAttribute('data-harga') || 0) : 0;

        const satuanEl = row.querySelector('.row-satuan');
        if (satuanEl) satuanEl.textContent = satuan || '-';

        const hargaInput = row.querySelector('.harga-input');
        if (defaultHarga > 0) {
            hargaInput.value = defaultHarga;
        }

        if (barangId) {
            fetchBatchesForRow(row, barangId);
        } else {
            const batchSelect = row.querySelector('.batch-select');
            batchSelect.innerHTML = '<option value="">-- Pilih Barang Dulu --</option>';
            row.querySelector('.batch-info').textContent = '';
            if (satuanEl) satuanEl.textContent = '-';
        }

        calcRow(selectEl);
    }

    function fetchBatchesForRow(row, barangId) {
        const gudangId = getSelectedGudangId();
        const batchSelect = row.querySelector('.batch-select');
        const batchInfo = row.querySelector('.batch-info');

        if (!gudangId) {
            batchSelect.innerHTML = '<option value="">-- Pilih Gudang di Atas Dulu --</option>';
            batchInfo.innerHTML = '<span style="color: #dc2626; font-weight: 600;">⚠️ Gudang belum dipilih di bagian atas!</span>';
            return;
        }

        batchSelect.innerHTML = '<option value="">Memuat data batch FIFO...</option>';
        batchInfo.innerHTML = '<span style="color: #64748b;">Memeriksa stok fisik & urutan batch...</span>';

        fetch(`{{ route('gudang.pemakaian.batches') }}?gudang_id=${gudangId}&barang_id=${barangId}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    if (!res.batches || res.batches.length === 0) {
                        batchSelect.innerHTML = '<option value="">(Stok Fisik Habis di Gudang ini)</option>';
                        batchInfo.innerHTML = '<span style="color: #dc2626; font-weight: 700; background: #fee2e2; padding: 0.2rem 0.5rem; border-radius: 4px;">⚠️ Stok fisik habis total di gudang ini.</span>';
                        row.querySelector('.harga-input').value = 0;
                    } else {
                        let html = '';
                        res.batches.forEach((b, idx) => {
                            const isTop = (idx === 0);
                            const prefix = isTop ? '⭐ [FIFO PRIORITAS] ' : '• ';
                            const expInfo = b.expired_tgl ? ` | Exp: ${b.expired_tgl}` : '';
                            const tglTerima = b.tgl_terima ? ` | Masuk: ${b.tgl_terima}` : '';
                            html += `<option value="${b.batch_no}" data-sisa="${b.sisa_qty}" data-harga="${b.harga_satuan || 0}" data-masuk="${b.tgl_terima}" data-exp="${b.expired_tgl || '-'}" ${isTop ? 'selected' : ''}>
                                ${prefix}${b.batch_no} (Sisa: ${parseFloat(b.sisa_qty).toLocaleString('id-ID')})${tglTerima}${expInfo}
                            </option>`;
                        });
                        batchSelect.innerHTML = html;

                        // Otomatis pilih batch pertama (FIFO Prioritas) & isi harga beli riil
                        onBatchSelect(batchSelect);
                    }
                }
            })
            .catch(err => {
                console.error(err);
                batchSelect.innerHTML = '<option value="">Gagal memuat batch</option>';
                batchInfo.innerHTML = '<span style="color: #dc2626;">Koneksi gagal saat mengambil batch.</span>';
            });
    }

    function onBatchSelect(selectEl) {
        const row = selectEl.closest('tr');
        const selectedOpt = selectEl.selectedOptions[0];
        if (!selectedOpt || !selectedOpt.value) return;

        const sisa = parseFloat(selectedOpt.getAttribute('data-sisa') || 0);
        const harga = parseFloat(selectedOpt.getAttribute('data-harga') || 0);
        const tglMasuk = selectedOpt.getAttribute('data-masuk') || '-';
        const batchInfo = row.querySelector('.batch-info');
        const isFirstBatch = (selectEl.selectedIndex === 0);

        if (isFirstBatch) {
            batchInfo.innerHTML = `<span style="color: #065f46; font-weight: 700; background: #ecfdf5; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid #a7f3d0; display: inline-flex; align-items: center; gap: 0.25rem;">
                ⭐ Rekomendasi FIFO: Batch masuk paling awal (${tglMasuk}) • Maks: ${sisa.toLocaleString('id-ID')} unit
            </span>`;
        } else {
            batchInfo.innerHTML = `<span style="color: #0369a1; font-weight: 600; background: #f0f9ff; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid #bae6fd;">
                Pilihan Manual: Masuk ${tglMasuk} • Maks: ${sisa.toLocaleString('id-ID')} unit
            </span>`;
        }

        const hargaInput = row.querySelector('.harga-input');
        if (harga > 0) {
            hargaInput.value = harga;
        }

        calcRow(selectEl);
    }

    function calcRow(el) {
        const row = el.closest('tr');
        const qtyInput = row.querySelector('.qty-input');
        const hargaInput = row.querySelector('.harga-input');
        const batchSelect = row.querySelector('.batch-select');
        const batchInfo = row.querySelector('.batch-info');
        const selectedBatchOpt = batchSelect ? batchSelect.selectedOptions[0] : null;

        const qty = parseFloat(qtyInput.value) || 0;
        const harga = parseFloat(hargaInput.value) || 0;
        const sisa = selectedBatchOpt ? parseFloat(selectedBatchOpt.getAttribute('data-sisa') || 0) : Infinity;

        // Warning tegas jika qty yang diminta melebihi sisa fisik batch
        if (selectedBatchOpt && selectedBatchOpt.value && qty > sisa) {
            qtyInput.style.borderColor = '#dc2626';
            qtyInput.style.backgroundColor = '#fef2f2';
            batchInfo.innerHTML = `<span style="color: #dc2626; font-weight: 700; background: #fee2e2; padding: 0.2rem 0.5rem; border-radius: 4px; border: 1px solid #fecaca; display: inline-block;">
                ⚠️ Melebihi sisa batch (${sisa.toLocaleString('id-ID')}). Ambil ${sisa.toLocaleString('id-ID')} di baris ini, lalu klik "+ Tambah Baris" untuk sisa ${(qty - sisa).toLocaleString('id-ID')} dari batch berikutnya.
            </span>`;
        } else if (selectedBatchOpt && selectedBatchOpt.value) {
            qtyInput.style.borderColor = '#cbd5e1';
            qtyInput.style.backgroundColor = '#ffffff';
            const tglMasuk = selectedBatchOpt.getAttribute('data-masuk') || '-';
            const isFirstBatch = (batchSelect.selectedIndex === 0);
            if (isFirstBatch) {
                batchInfo.innerHTML = `<span style="color: #065f46; font-weight: 700; background: #ecfdf5; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid #a7f3d0;">
                    ⭐ Rekomendasi FIFO: Batch masuk paling awal (${tglMasuk}) • Maks: ${sisa.toLocaleString('id-ID')} unit
                </span>`;
            } else {
                batchInfo.innerHTML = `<span style="color: #0369a1; font-weight: 600; background: #f0f9ff; padding: 0.15rem 0.5rem; border-radius: 4px; border: 1px solid #bae6fd;">
                    Pilihan Manual: Masuk ${tglMasuk} • Maks: ${sisa.toLocaleString('id-ID')} unit
                </span>`;
            }
        }

        const subtotal = qty * harga;
        row.querySelector('.subtotal-cell').textContent = 'Rp ' + subtotal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let totalQty = 0;
        let totalNilai = 0;
        let activeItems = 0;

        document.querySelectorAll('#itemsBody tr').forEach(row => {
            const barangSelect = row.querySelector('.barang-select');
            if (barangSelect && barangSelect.value) {
                activeItems++;
            }
            const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
            const harga = parseFloat(row.querySelector('.harga-input')?.value) || 0;
            totalQty += qty;
            totalNilai += (qty * harga);
        });

        const grandTotalQty = document.getElementById('grandTotalQty');
        const grandTotalNilai = document.getElementById('grandTotalNilai');
        if (grandTotalQty) grandTotalQty.textContent = totalQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (grandTotalNilai) grandTotalNilai.textContent = 'Rp ' + totalNilai.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const sideGrandTotal = document.getElementById('sideGrandTotal');
        const sideTotalQty = document.getElementById('sideTotalQty');
        const sideTotalItems = document.getElementById('sideTotalItems');
        if (sideGrandTotal) sideGrandTotal.textContent = 'Rp ' + totalNilai.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (sideTotalQty) sideTotalQty.textContent = totalQty.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (sideTotalItems) sideTotalItems.textContent = activeItems;
    }

    // Inisialisasi awal saat halaman selesai dimuat
    document.addEventListener('DOMContentLoaded', () => {
        addRow();
        updateSidebarInfo();
        document.getElementById('tujuan_pemakaian')?.addEventListener('change', updateSidebarInfo);
        document.getElementById('tujuan_pemakaian')?.addEventListener('input', updateSidebarInfo);
        document.getElementById('gudang_id')?.addEventListener('change', () => {
            updateSidebarInfo();
            onGudangChanged();
        });
    });
</script>
@endsection
