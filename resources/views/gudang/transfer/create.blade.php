@extends('layouts.app')

@section('title', 'Catat Transfer Antar-Gudang - ERP PT Mirasa Food Industry')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/transfer/transfer-form.css') }}">
@endpush

@section('content')
<div class="transfer-form-container">
    {{-- TOP ACTION BAR --}}
    <div class="transfer-header-bar">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <a href="{{ route('gudang.transfer.index') }}" style="color: #64748b; text-decoration: none; display: inline-flex; align-items: center;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <h1 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0;">
                    Catat Transfer Antar-Gudang
                </h1>
                <span style="background: #f0fdfa; color: #0f766e; border: 1.5px solid #99f6e4; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 6px;">
                    Surat Jalan &amp; Mutasi
                </span>
                <span style="background: #f1f5f9; color: #475569; font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 6px; font-family: monospace;">
                    {{ $suggestedNo }}
                </span>
            </div>
            <p style="font-size: 0.8125rem; color: #64748b; margin: 0.25rem 0 0 1.75rem;">
                Stok di gudang asal otomatis terpotong dan stok di gudang penerima (tujuan) langsung bertambah secara real-time.
            </p>
        </div>

        <div>
            <a href="{{ route('gudang.transfer.index') }}" class="btn btn-secondary" style="font-weight: 600; font-size: 0.8125rem;">
                &larr; Kembali ke Daftar Transfer
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
            <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                <svg width="20" height="20" fill="none" stroke="#dc2626" viewBox="0 0 24 24" style="flex-shrink: 0; margin-top: 1px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <h4 style="font-size: 0.875rem; font-weight: 700; color: #991b1b; margin: 0 0 0.35rem 0;">Perhatian: Ada data yang belum lengkap</h4>
                    <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.8125rem; color: #b91c1c;">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('gudang.transfer.store') }}" id="formTransfer">
        @csrf

        <div class="transfer-workspace-grid">
            
            {{-- KOLOM KIRI: PARAMETER SURAT JALAN & LOKASI GUDANG --}}
            <div>
                <div class="transfer-card">
                    <div class="transfer-card-header">
                        <span class="transfer-card-title">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Parameter Dokumen</span>
                        </span>
                        <span style="font-size: 0.725rem; font-weight: 700; color: #0d9488;">Langkah 1</span>
                    </div>
                    <div class="transfer-card-body">
                        {{-- NO TRANSFER & TANGGAL --}}
                        <div class="form-group">
                            <label for="transfer_no" class="form-label">No. Dokumen Transfer</label>
                            <input type="text" name="transfer_no" id="transfer_no" value="{{ old('transfer_no', $suggestedNo) }}" class="form-control" style="font-family: monospace; font-weight: 700;">
                        </div>

                        <div class="form-group">
                            <label for="transfer_tgl" class="form-label">Tanggal Mutasi / Pengiriman <span style="color: #dc2626;">*</span></label>
                            <input type="date" name="transfer_tgl" id="transfer_tgl" value="{{ old('transfer_tgl', date('Y-m-d')) }}" class="form-control" required>
                        </div>

                        {{-- GUDANG ASAL (PENGIRIM) --}}
                        <div class="form-group">
                            <label for="gudang_asal_id" class="form-label">
                                Gudang Asal (Pengirim) <span style="color: #dc2626;">*</span>
                                <span id="batchLoadingNotice" style="display: none; color: #0d9488; font-size: 0.725rem; margin-left: 0.35rem; font-weight: normal;">(Memuat stok...)</span>
                            </label>
                            <select name="gudang_asal_id" id="gudang_asal_id" class="form-control" required onchange="onGudangAsalChange(this)">
                                <option value="">-- Pilih Gudang Asal --</option>
                                @foreach ($allowedGudangs as $gdg)
                                    <option value="{{ $gdg->gudang_id }}" {{ old('gudang_asal_id') == $gdg->gudang_id ? 'selected' : '' }}>
                                        {{ $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                                    </option>
                                @endforeach
                            </select>
                            <small style="color: #64748b; font-size: 0.7rem; margin-top: 2px; display: block;">Stok batch akan otomatis dipotong dari gudang ini.</small>
                        </div>

                        {{-- GUDANG TUJUAN (PENERIMA) --}}
                        <div class="form-group">
                            <label for="gudang_tujuan_id" class="form-label">Gudang Tujuan (Penerima) <span style="color: #dc2626;">*</span></label>
                            <select name="gudang_tujuan_id" id="gudang_tujuan_id" class="form-control" required>
                                <option value="">-- Pilih Gudang Tujuan --</option>
                                @foreach ($allGudangs as $gdg)
                                    <option value="{{ $gdg->gudang_id }}" {{ old('gudang_tujuan_id') == $gdg->gudang_id ? 'selected' : '' }}>
                                        {{ $gdg->gudang_nm }} ({{ $gdg->gudang_cd }}) - {{ $gdg->tipe_gudang_cd ?? 'Gudang' }}
                                    </option>
                                @endforeach
                            </select>
                            <small style="color: #64748b; font-size: 0.7rem; margin-top: 2px; display: block;">Stok batch akan otomatis ditambahkan ke gudang ini.</small>
                        </div>
                    </div>
                </div>

                {{-- SURAT JALAN & LOGISTIK TRANSPORTASI --}}
                <div class="transfer-card">
                    <div class="transfer-card-header">
                        <span class="transfer-card-title">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                            <span>Logistik &amp; Transportasi</span>
                        </span>
                        <span style="font-size: 0.725rem; font-weight: 700; color: #64748b;">Opsional</span>
                    </div>
                    <div class="transfer-card-body">
                        <div class="form-group">
                            <label for="no_surat_jalan" class="form-label">No. Surat Jalan Manual</label>
                            <input type="text" name="no_surat_jalan" id="no_surat_jalan" value="{{ old('no_surat_jalan') }}" class="form-control" placeholder="Contoh: SJ-2026/XI/001">
                        </div>

                        <div class="form-group">
                            <label for="sopir_nama" class="form-label">Nama Supir / Pengemudi</label>
                            <input type="text" name="sopir_nama" id="sopir_nama" value="{{ old('sopir_nama') }}" class="form-control" placeholder="Nama sopir armada">
                        </div>

                        <div class="form-group">
                            <label for="nopol_kendaraan" class="form-label">Nomor Polisi (Nopol Mobil/Truk)</label>
                            <input type="text" name="nopol_kendaraan" id="nopol_kendaraan" value="{{ old('nopol_kendaraan') }}" class="form-control" placeholder="Contoh: AA 1234 XY">
                        </div>

                        <div class="form-group">
                            <label for="catatan_txt" class="form-label">Catatan Operasional</label>
                            <textarea name="catatan_txt" id="catatan_txt" rows="2" class="form-control" placeholder="Catatan tujuan kirim, kondisi barang...">{{ old('catatan_txt') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: TABEL ITEM BARANG & NOMOR BATCH YANG DIPINDAHKAN --}}
            <div>
                <div class="transfer-card">
                    <div class="transfer-card-header">
                        <span class="transfer-card-title">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>Rincian Barang &amp; Batch yang Dipindahkan</span>
                        </span>
                        <span style="font-size: 0.725rem; font-weight: 700; color: #0d9488;">Langkah 2</span>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="table-item-input">
                            <thead>
                                <tr>
                                    <th style="width: 45%;">Barang &amp; Batch Asal <span style="color: #dc2626;">*</span></th>
                                    <th style="width: 15%; text-align: center;">Sisa Stok Asal</th>
                                    <th style="width: 18%; text-align: right;">Qty Ditransfer <span style="color: #dc2626;">*</span></th>
                                    <th style="width: 18%;">Keterangan</th>
                                    <th style="width: 4%;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                {{-- Baris dinamis diisi oleh JS --}}
                            </tbody>
                        </table>
                    </div>

                    <div style="padding: 1rem 1.25rem; border-top: 1px dashed #cbd5e1;">
                        <button type="button" class="btn-add-item" onclick="addItemRow()">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>+ Tambah Baris Barang</span>
                        </button>
                    </div>

                    {{-- LIVE SUMMARY BAR --}}
                    <div class="transfer-summary-box">
                        <div class="summary-metric">
                            <span class="summary-metric-label">Total Baris:</span>
                            <div class="summary-metric-val" id="liveTotalRows" style="color: #334155;">0</div>
                        </div>
                        <div class="summary-metric">
                            <span class="summary-metric-label">Total Kuantitas Ditransfer:</span>
                            <div class="summary-metric-val" id="liveTotalQty">0,00</div>
                        </div>
                    </div>
                </div>

                {{-- SUBMIT BUTTONS --}}
                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem;">
                    <a href="{{ route('gudang.transfer.index') }}" class="btn btn-secondary" style="font-weight: 700; font-size: 0.8125rem;">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary" style="background: #0d9488; border-color: #0f766e; color: #ffffff; font-weight: 700; font-size: 0.85rem; padding: 0.65rem 1.5rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan &amp; Mutasi Stok Sekarang</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/gudang/transfer/transfer-create.js') }}"></script>
@endpush
