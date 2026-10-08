@extends('layouts.app')

@section('title', 'Buat Adjustment Baru')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/adjustment/adjustment-index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/adjustment/adjustment-form.css') }}">
@endpush

@section('content')
<div class="adj-container" style="max-width: 1400px;">

    {{-- Breadcrumb / Back Link --}}
    <div style="margin-bottom: 1rem;">
        <a href="{{ route('gudang.adjustment.index') }}" style="display: inline-flex; align-items: center; gap: 0.35rem; color: #64748b; font-size: 0.85rem; text-decoration: none; font-weight: 500;">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Adjustment
        </a>
    </div>

    {{-- Header Form --}}
    <div class="adj-header-wrap">
        <div class="adj-title-area">
            <h1>
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Buat Adjustment Baru
            </h1>
            <p class="adj-subtitle">
                Sesuaikan stok sistem dengan hasil timbangan fisik di gudang. Sistem akan otomatis menghitung selisih dan mengoreksi kartu stok.
            </p>
        </div>
    </div>

    {{-- Form Start --}}
    <form method="POST" action="{{ route('gudang.adjustment.store') }}">
        @csrf

        {{-- Kartu Data Utama / Dokumen --}}
        <div class="adj-form-card">
            <h3 class="adj-section-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Informasi Dokumen &amp; Lokasi Opname
            </h3>

            <div class="adj-header-fields-grid">
                {{-- Tanggal Penyesuaian --}}
                <div class="adj-form-group">
                    <label for="adj_tgl">Tanggal Pelaksanaan <span style="color: #dc2626;">*</span></label>
                    <input type="date" name="adj_tgl" id="adj_tgl" class="adj-form-control" 
                           value="{{ old('adj_tgl', date('Y-m-d')) }}" required>
                </div>

                {{-- Pilihan Gudang Target --}}
                <div class="adj-form-group">
                    <label for="adjGudangSelect">Gudang Penyimpanan <span style="color: #dc2626;">*</span></label>
                    <select name="gudang_id" id="adjGudangSelect" class="adj-form-control" required>
                        @foreach($gudangList as $g)
                            <option value="{{ $g->gudang_id }}" {{ old('gudang_id') == $g->gudang_id ? 'selected' : '' }}>
                                {{ $g->gudang_nm }} ({{ $g->gudang_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Kategori Penyesuaian --}}
                <div class="adj-form-group">
                    <label for="kategori_adj">Kategori Penyesuaian <span style="color: #dc2626;">*</span></label>
                    <select name="kategori_adj" id="kategori_adj" class="adj-form-control" required>
                        <option value="OPNAME_RUTIN" {{ old('kategori_adj') == 'OPNAME_RUTIN' ? 'selected' : '' }}>Stock Opname Rutin (Bulanan/Mingguan)</option>
                        <option value="SUSUT_MINYAK" {{ old('kategori_adj') == 'SUSUT_MINYAK' ? 'selected' : '' }}>Penyusutan Residu Drum Minyak &amp; Tetesan</option>
                        <option value="SUSUT_ALAMI" {{ old('kategori_adj') == 'SUSUT_ALAMI' ? 'selected' : '' }}>Penyusutan Alami Kadar Air (Singkong Mentah)</option>
                        <option value="KERUSAKAN" {{ old('kategori_adj') == 'KERUSAKAN' ? 'selected' : '' }}>Barang Rusak / Kadaluwarsa / Spoilage</option>
                        <option value="SELISIH_TIMBANG" {{ old('kategori_adj') == 'SELISIH_TIMBANG' ? 'selected' : '' }}>Koreksi Kalibrasi &amp; Timbangan</option>
                        <option value="LAINNYA" {{ old('kategori_adj') == 'LAINNYA' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>

                {{-- Catatan Umum Dokumen --}}
                <div class="adj-form-group">
                    <label for="catatan_txt">Catatan / Berita Acara</label>
                    <input type="text" name="catatan_txt" id="catatan_txt" class="adj-form-control" 
                           placeholder="Contoh: Berita acara opname drum minyak line 1" 
                           value="{{ old('catatan_txt') }}">
                </div>
            </div>
        </div>

        {{-- Kartu Tabel Rincian Barang --}}
        <div class="adj-form-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem;">
                <h3 class="adj-section-title" style="margin: 0; border: none; padding: 0;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Daftar Barang &amp; Perhitungan Selisih (Blueprint)
                </h3>
                <button type="button" class="btn-adj-primary" onclick="addAdjustmentRow()" style="padding: 0.45rem 0.85rem; font-size: 0.775rem;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Barang
                </button>
            </div>

            <div class="adj-items-table-wrap">
                <table class="adj-items-table">
                    <thead>
                        <tr>
                            <th>Barang (Kode &amp; Nama)</th>
                            <th style="text-align: center;">Satuan</th>
                            <th class="th-num">d. Stok Sistem</th>
                            <th class="th-num">e. @Harga Pokok</th>
                            <th class="th-num">f. Total Awal</th>
                            <th class="th-num" style="background: #cffafe; color: #0891b2;">g. Stok Fisik Riil *</th>
                            <th class="th-num" style="background: #cffafe; color: #0891b2;">i. Total Fisik</th>
                            <th class="th-num" style="background: #fef08a; color: #854d0e;">j. Selisih Qty</th>
                            <th class="th-num" style="background: #fef08a; color: #854d0e;">l. Total Selisih (Rp)</th>
                            <th>Alasan / Keterangan Spesifik</th>
                            <th style="text-align: center;">Hapus</th>
                        </tr>
                    </thead>
                    <tbody id="adjItemsTbody">
                        {{-- Baris akan dibuat secara dinamis oleh JavaScript --}}
                    </tbody>
                </table>
            </div>

            {{-- Ringkasan Bawah --}}
            <div class="adj-summary-bar">
                <div class="adj-summary-item">
                    <span class="adj-summary-label">Total Item Barang:</span>
                    <span class="adj-summary-val" id="summaryTotalItem">0</span>
                </div>
                <div class="adj-summary-item">
                    <span class="adj-summary-label">Total Selisih Kuantitas:</span>
                    <span class="adj-summary-val" id="summarySelisihQty">0</span>
                </div>
                <div class="adj-summary-item">
                    <span class="adj-summary-label">Net Selisih Persediaan (Rp):</span>
                    <span class="adj-summary-val" id="summarySelisihNilai">Rp 0</span>
                </div>
            </div>

            {{-- Tombol Submit Form --}}
            <div class="adj-form-footer">
                <a href="{{ route('gudang.adjustment.index') }}" class="btn-adj-secondary">
                    Batal
                </a>
                <button type="submit" class="btn-adj-primary" style="padding: 0.65rem 1.5rem;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan &amp; Posting Adjustment
                </button>
            </div>
        </div>
    </form>

</div>

{{-- Template Tersembunyi untuk Dropdown Opsi Barang --}}
<select id="barangOptionsTemplate" style="display: none;">
    <option value="">-- Pilih Barang --</option>
    @foreach($barangList as $b)
        <option value="{{ $b->barang_id }}">
            {{ $b->barang_cd }} - {{ $b->barang_nm }}
        </option>
    @endforeach
</select>

@endsection

@push('scripts')
    <script src="{{ asset('js/gudang/adjustment/adjustment-form.js') }}"></script>
@endpush
