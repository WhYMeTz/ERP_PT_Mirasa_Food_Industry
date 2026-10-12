@extends('layouts.app')

@section('title', 'Buat Adjustment Baru - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/adjustment/adjustment-index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/adjustment/adjustment-form.css') }}">
@endpush

@section('content')
<div class="adj-container">

    {{-- 1. Header Halaman Standar Mirasa --}}
    <div class="adj-page-header">
        <a href="{{ route('gudang.adjustment.index') }}" class="adj-back-link">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Adjustment
        </a>
        <h1 class="adj-page-title">Buat Adjustment Baru</h1>
        <p class="adj-page-subtitle">Sesuaikan stok sistem dengan hasil timbangan fisik di gudang. Sistem otomatis menghitung selisih dan mengoreksi kartu stok.</p>
    </div>

    {{-- Form Start --}}
    <form method="POST" action="{{ route('gudang.adjustment.store') }}">
        @csrf

        {{-- KARTU 1: INFORMASI DOKUMEN & LOKASI OPNAME --}}
        <div class="adj-card">
            <div class="adj-card-header">
                <strong class="adj-card-header-title">1. Informasi Dokumen &amp; Lokasi Opname</strong>
            </div>

            <div class="adj-card-body">
                <div class="adj-header-fields-grid">
                    {{-- Tanggal Pelaksanaan --}}
                    <div class="form-group">
                        <label for="adj_tgl" class="form-label">Tanggal Pelaksanaan <span style="color: #ef4444;">*</span></label>
                        <input type="date" name="adj_tgl" id="adj_tgl" class="form-control" 
                               value="{{ old('adj_tgl', date('Y-m-d')) }}" required>
                        <small style="color: #64748b; font-size: 0.725rem; display: block; margin-top: 0.25rem;">Waktu pelaksanaan stock opname fisik.</small>
                    </div>

                    {{-- Gudang Penyimpanan --}}
                    <div class="form-group">
                        <label for="adjGudangSelect" class="form-label">Gudang Penyimpanan <span style="color: #ef4444;">*</span></label>
                        <select name="gudang_id" id="adjGudangSelect" class="form-control" required onchange="onGudangChanged(this.value)">
                            <option value="" {{ old('gudang_id') ? '' : 'selected' }} disabled>-- Pilih Gudang Penyimpanan Terlebih Dahulu --</option>
                            @foreach($gudangList as $g)
                                <option value="{{ $g->gudang_id }}" {{ old('gudang_id') == $g->gudang_id ? 'selected' : '' }}>
                                    {{ $g->display_name ?? $g->gudang_nm }} ({{ $g->gudang_cd }})
                                </option>
                            @endforeach
                        </select>
                        <small id="gudangInfoBadge" style="color: #64748b; font-size: 0.725rem; display: block; margin-top: 0.25rem;">
                            Pilih gudang agar daftar komoditas yang tersedia di gudang tersebut tampil otomatis.
                        </small>
                    </div>

                    {{-- Kategori Default Otomatis (Hidden) --}}
                    <input type="hidden" name="kategori_adj" value="OPNAME_RUTIN">

                    {{-- Catatan / Berita Acara --}}
                    <div class="form-group">
                        <label for="catatan_txt" class="form-label">Catatan / Berita Acara</label>
                        <input type="text" name="catatan_txt" id="catatan_txt" class="form-control" 
                               placeholder="Contoh: Berita acara opname drum minyak line 1" 
                               value="{{ old('catatan_txt') }}">
                        <small style="color: #64748b; font-size: 0.725rem; display: block; margin-top: 0.25rem;">Keterangan tambahan untuk audit fisik.</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 2: DETAIL BARANG & HASIL HITUNG FISIK (EXCEL GRID TABLE - SAMA DENGAN BARANG MASUK) --}}
        <div class="card" style="border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 1.5rem; background: #ffffff; border-radius: 8px;">
            <div class="card-header" style="background: #ffffff; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #cbd5e1; padding: 0.875rem 1.25rem; flex-wrap: wrap; gap: 0.5rem; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                <div>
                    <strong style="color: #0f172a; font-size: 0.95rem;">2. Daftar Komoditas &amp; Hasil Hitung Fisik</strong>
                    <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.5rem;">
                        💡 Tekan <kbd style="background:#e2e8f0; padding:2px 5px; border-radius:3px; font-weight:700;">Tab</kbd> untuk berpindah kolom layaknya Excel
                    </span>
                </div>
                <button type="button" onclick="addAdjustmentRow()" class="btn btn-primary btn-sm" style="font-weight: 600; background: #059669; border: none; padding: 0.4rem 0.85rem; border-radius: 6px; color: #ffffff; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;">
                    + Tambah Baris
                </button>
            </div>

            <div class="excel-table-scroll" style="overflow-x: auto;">
                <table class="excel-grid-table" id="tableAdjItems" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 35px; text-align: center;">No</th>
                            <th class="col-adj-barang" style="width: 380px; min-width: 350px; max-width: 420px; text-align: left;">Nama Komoditas / Barang <span style="color:#ef4444;">*</span></th>
                            <th style="width: 55px; text-align: center;">Satuan</th>
                            <th style="width: 90px; text-align: right;">Stok Sistem</th>
                            <th style="width: 100px; text-align: right; background: #064e3b; color: #ffffff;">Fisik Aktual <span style="color:#ef4444;">*</span></th>
                            <th style="width: 85px; text-align: right;">Selisih Qty</th>
                            <th style="width: 105px; text-align: right;">Harga Satuan (HPP)</th>
                            <th style="width: 115px; text-align: right;">Total Fisik (Rp)</th>
                            <th style="width: 115px; text-align: right; background: #0f172a; color: #ffffff;">Nilai Selisih (Rp)</th>
                            <th class="col-adj-catatan" style="min-width: 200px; text-align: left;">Catatan / Alasan</th>
                            <th style="width: 40px; text-align: center;">Hapus</th>
                        </tr>
                    </thead>
                    <tbody id="adjItemsTbody">
                        {{-- Baris pertama akan diisi otomatis oleh JavaScript saat dibuka --}}
                    </tbody>
                    <tfoot>
                        <tr style="background: #f8fafc; font-weight: 700; border-top: 2px solid #cbd5e1;">
                            <td colspan="3" style="text-align: right; padding: 0.55rem 0.75rem; color: #475569; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.03em;">
                                Total:
                            </td>
                            <td id="tfootTotalSistemDisplay" style="padding: 0.45rem 0.5rem; color: #334155; font-size: 0.825rem; text-align: right; font-family: monospace; font-weight: 700;" title="Total Stok Sistem">
                                0,00
                            </td>
                            <td id="tfootTotalFisikDisplay" style="padding: 0.45rem 0.5rem; color: #047857; font-size: 0.85rem; text-align: right; background: #dcfce7; font-family: monospace; font-weight: 800;" title="Total Fisik Aktual">
                                0,00
                            </td>
                            <td id="tfootTotalSelisihDisplay" style="padding: 0.45rem 0.5rem; color: #0f172a; font-size: 0.825rem; text-align: right; font-family: monospace; font-weight: 800;" title="Total Selisih Qty">
                                0,00
                            </td>
                            <td></td>
                            <td id="tfootTotalFisikNilaiDisplay" style="padding: 0.45rem 0.5rem; color: #0f172a; font-size: 0.825rem; text-align: right; font-family: monospace; font-weight: 700;" title="Total Nilai Fisik">
                                Rp 0
                            </td>
                            <td id="tfootTotalSelisihNilaiDisplay" style="padding: 0.45rem 0.5rem; color: #0f172a; font-size: 0.85rem; text-align: right; font-family: monospace; font-weight: 800;" title="Total Nilai Selisih">
                                Rp 0
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Kartu Ringkasan & Footer Aksi Standar Mirasa (Sama dengan Barang Masuk) --}}
            <div class="card-footer" style="background: #ffffff; border-top: 1px solid #cbd5e1; padding: 0.85rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
                    <div>
                        <span style="font-size: 0.68rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; display: block; line-height: 1;">Total Komoditas</span>
                        <div style="margin-top: 0.2rem; font-size: 1.15rem; font-weight: 800; color: #0f172a; font-family: monospace; line-height: 1.2;" id="summaryTotalItem">
                            0 Komoditas
                        </div>
                    </div>
                    <div style="width: 1px; height: 30px; background: #cbd5e1;"></div>
                    <div>
                        <span style="font-size: 0.68rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; display: block; line-height: 1;">Total Selisih Qty</span>
                        <div style="margin-top: 0.2rem; font-size: 1.15rem; font-weight: 800; color: #0f172a; font-family: monospace; line-height: 1.2;" id="summarySelisihQty">
                            0
                        </div>
                    </div>
                    <div style="width: 1px; height: 30px; background: #cbd5e1;"></div>
                    <div>
                        <span style="font-size: 0.68rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; display: block; line-height: 1;">Net Dampak Persediaan</span>
                        <div style="margin-top: 0.2rem; font-size: 1.15rem; font-weight: 800; color: #0f172a; font-family: monospace; line-height: 1.2;" id="summarySelisihNilai">
                            Rp 0
                        </div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <a href="{{ route('gudang.adjustment.index') }}" class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.55rem 1.15rem; border-radius: 6px; font-weight: 600; background: #ffffff; border: 1px solid #cbd5e1; color: #475569; text-decoration: none;">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary" style="background: #059669; font-size: 0.875rem; font-weight: 700; padding: 0.55rem 1.35rem; border-radius: 6px; box-shadow: 0 1px 3px rgba(5, 150, 105, 0.25); display: inline-flex; align-items: center; gap: 0.45rem; cursor: pointer; border: none; color: #ffffff;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan &amp; Posting Adjustment</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

</div>

{{-- Template Tersembunyi untuk Dropdown Opsi Barang (Dikelompokkan sesuai format Barang Masuk) --}}
<select id="barangOptionsTemplate" style="display: none;">
    <option value="">-- Pilih Barang (Bahan Baku / Penolong / Kemasan / Jadi) --</option>
    
    @php
        $rawItems = $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['RAW', 'BB']));
        $suppItems = $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['SUPP', 'BUMBU', 'BP']) && !preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm));
        $packItems = $barangList->filter(fn($b) => in_array($b->jenisBarang?->jenis_barang_cd, ['PACK']) || (in_array($b->jenisBarang?->jenis_barang_cd, ['BP']) && preg_match('/(PLASTIK|KARTON|ROLL|LAKBAN|SARUNG|RAFIA)/i', $b->barang_nm)));
        $otherItems = $barangList->reject(fn($b) => $rawItems->contains($b) || $suppItems->contains($b) || $packItems->contains($b));
    @endphp

    @if($rawItems->isNotEmpty())
        <optgroup label="BAHAN BAKU (RAW MATERIAL)">
            @foreach($rawItems as $b)
                <option value="{{ $b->barang_id }}" data-cd="{{ $b->barang_cd }}" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}">
                    {{ $b->barang_nm }} ({{ $b->barang_cd }})
                </option>
            @endforeach
        </optgroup>
    @endif

    @if($suppItems->isNotEmpty())
        <optgroup label="BUMBU &amp; BAHAN PENOLONG">
            @foreach($suppItems as $b)
                <option value="{{ $b->barang_id }}" data-cd="{{ $b->barang_cd }}" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}">
                    {{ $b->barang_nm }} ({{ $b->barang_cd }})
                </option>
            @endforeach
        </optgroup>
    @endif

    @if($packItems->isNotEmpty())
        <optgroup label="BAHAN KEMASAN &amp; PACKAGING">
            @foreach($packItems as $b)
                <option value="{{ $b->barang_id }}" data-cd="{{ $b->barang_cd }}" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}">
                    {{ $b->barang_nm }} ({{ $b->barang_cd }})
                </option>
            @endforeach
        </optgroup>
    @endif

    @if($otherItems->isNotEmpty())
        <optgroup label="PRODUK LAINNYA / JADI / WIP">
            @foreach($otherItems as $b)
                <option value="{{ $b->barang_id }}" data-cd="{{ $b->barang_cd }}" data-satuan="{{ $b->satuanDasar?->satuan_nm ?? '-' }}">
                    {{ $b->barang_nm }} ({{ $b->barang_cd }})
                </option>
            @endforeach
        </optgroup>
    @endif
</select>

@endsection

@push('scripts')
    <script>
        window.adjConfig = {
            ajaxItemInfoUrl: "{{ route('gudang.adjustment.ajax-item-info') }}",
            ajaxBarangByGudangUrl: "{{ route('gudang.adjustment.ajax-barang-by-gudang') }}"
        };
    </script>
    <script src="{{ asset('js/gudang/adjustment/adjustment-form.js') }}"></script>
@endpush
