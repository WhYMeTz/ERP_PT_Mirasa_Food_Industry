@extends('layouts.app')

@section('title', 'Sortir Ulang & Rework Produk (KW 2) - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/rework-create.css') }}">
@endpush

@section('content')
<div class="rework-page-container">
    {{-- Breadcrumb Navigation --}}
    <a href="{{ route('produksi.index') }}" class="rework-breadcrumb">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Kembali ke Riwayat Hasil Produksi</span>
    </a>

    {{-- Page Header --}}
    <div class="rework-page-header">
        <h1 class="rework-page-title">Formulir Sortir Ulang &amp; Rework Produk (KW 2)</h1>
        <p class="rework-page-desc">
            Pencatatan pemilahan batch tidak lolos standar mutu rilis IFM, retur ekspor, atau sortiran gudang menjadi keripik curah KW 2, berko lengket, remahan, serta pemulihan kemasan dus.
        </p>
    </div>

    {{-- Error Alert --}}
    @if(session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600;">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem;">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="formRework" method="POST" action="{{ route('produksi.rework.store') }}">
        @csrf
        <input type="hidden" name="barang_id" id="reworkBarangId" value="{{ old('barang_id') }}">
        <input type="hidden" name="batch_no" id="reworkBatchNo" value="{{ old('batch_no') }}">

        {{-- BAGIAN 1: SUMBER BATCH ASAL --}}
        <div class="rework-card">
            <div class="rework-card-header">
                <h2>
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>1. Dokumen Sumber &amp; Parameter Pengerjaan Sortir</span>
                </h2>
            </div>
            <div class="rework-card-body">
                <div class="rework-form-grid">
                    <div class="rework-form-group">
                        <label class="rework-form-label" for="produksiTgl">Tanggal Pengerjaan Sortir <span class="req">*</span></label>
                        <input type="date" name="produksi_tgl" id="produksiTgl" class="rework-form-control" value="{{ old('produksi_tgl', date('Y-m-d')) }}" required>
                    </div>

                    <div class="rework-form-group">
                        <label class="rework-form-label" for="gudangId">Lokasi Gudang Penyimpanan <span class="req">*</span></label>
                        <select name="gudang_id" id="gudangId" class="rework-form-control" required onchange="loadBatchesForWarehouse()">
                            @foreach($gudangList as $g)
                                <option value="{{ $g->gudang_id }}" {{ old('gudang_id') == $g->gudang_id ? 'selected' : '' }}>
                                    {{ $g->gudang_nm }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="rework-form-group rework-col-full">
                        <label class="rework-form-label" for="batchSelect">Pilih Batch / Barang Sumber yang Disortir <span class="req">*</span></label>
                        <select id="batchSelect" class="rework-form-control" required onchange="onBatchChange(this)">
                            <option value="">-- Memuat daftar batch tersedia... --</option>
                        </select>
                    </div>
                </div>

                {{-- Detail Batch Terpilih --}}
                <div id="batchSummaryBox" class="batch-summary-box">
                    <div class="batch-summary-grid">
                        <div class="batch-summary-item">
                            <span class="batch-summary-label">Nama &amp; Kode Barang Asal</span>
                            <span class="batch-summary-value" id="summaryBarang">-</span>
                        </div>
                        <div class="batch-summary-item">
                            <span class="batch-summary-label">Klasifikasi Barang</span>
                            <span class="batch-summary-value" id="summaryJenis">-</span>
                        </div>
                        <div class="batch-summary-item">
                            <span class="batch-summary-label">Sisa Stok di Gudang</span>
                            <span class="batch-summary-value" id="summarySisaStok" style="color: #059669;">-</span>
                        </div>
                        <div class="batch-summary-item">
                            <span class="batch-summary-label">Estimasi Nilai HPP Satuan</span>
                            <span class="batch-summary-value" id="summaryHpp">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BAGIAN 2: KUANTITAS & PEMILAHAN HASIL SORTIR --}}
        <div class="rework-card">
            <div class="rework-card-header">
                <h2>
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 18h12l3-18H3zm16 0V4a2 2 0 00-2-2H7a2 2 0 00-2 2v2"/></svg>
                    <span>2. Rincian Pemilahan Hasil Sortir Keripik (Output)</span>
                </h2>
            </div>
            <div class="rework-card-body">
                <div class="rework-form-grid" style="margin-bottom: 1.25rem;">
                    <div class="rework-form-group">
                        <label class="rework-form-label" for="reworkQty">
                            Total Kuantitas Bahan yang Disortir <span class="req">*</span>
                        </label>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="number" step="0.01" min="0.01" name="qty_rework" id="reworkQty" class="rework-form-control" style="font-weight: 700; font-size: 1rem;" placeholder="0.00" value="{{ old('qty_rework') }}" required oninput="calculateAllocation()">
                            <span id="reworkSatuanLabel" style="font-weight: 700; color: #475569; width: 60px;">KG</span>
                        </div>
                    </div>
                </div>

                <div class="rework-table-container">
                    <table class="rework-table">
                        <thead>
                            <tr>
                                <th style="width: 50px; text-align: center;">No</th>
                                <th>Klasifikasi &amp; Hasil Pemilahan</th>
                                <th>Kode Barang Tujuan</th>
                                <th style="width: 80px; text-align: center;">Satuan</th>
                                <th style="width: 170px; text-align: right;">Kuantitas Hasil (KG)</th>
                                <th style="width: 100px; text-align: right;">Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="text-align: center; font-weight: 600;">1</td>
                                <td>
                                    <div style="font-weight: 700; color: #0f172a;">Keripik Curah Hasil Sortir (KW 2)</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">Hasil penyortiran layak jual sebagai produk curah sekunder.</div>
                                </td>
                                <td>
                                    <span class="rework-badge-grade badge-kw2">KS CURAH SORTIR (KW 2)</span>
                                </td>
                                <td style="text-align: center; font-weight: 600;">KG</td>
                                <td style="text-align: right;">
                                    <input type="number" step="0.01" min="0.01" name="qty_kw2" id="qtyKw2" class="rework-input-table" placeholder="0.00" value="{{ old('qty_kw2') }}" required oninput="calculateAllocation()">
                                </td>
                                <td style="text-align: right; font-weight: 700;" id="pctKw2Display">0.0%</td>
                            </tr>
                            <tr>
                                <td style="text-align: center; font-weight: 600;">2</td>
                                <td>
                                    <div style="font-weight: 700; color: #0f172a;">Afkir Keripik Lengket (Berko)</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">Keripik melekat / karamelisasi tinggi yang dipisahkan ke limbah padat.</div>
                                </td>
                                <td>
                                    <span class="rework-badge-grade badge-lengket">KS BERKO LENGKET</span>
                                </td>
                                <td style="text-align: center; font-weight: 600;">KG</td>
                                <td style="text-align: right;">
                                    <input type="number" step="0.01" min="0" name="qty_lengket" id="qtyLengket" class="rework-input-table" placeholder="0.00" value="{{ old('qty_lengket', 0) }}" oninput="calculateAllocation()">
                                </td>
                                <td style="text-align: right; font-weight: 700;" id="pctLengketDisplay">0.0%</td>
                            </tr>
                            <tr>
                                <td style="text-align: center; font-weight: 600;">3</td>
                                <td>
                                    <div style="font-weight: 700; color: #0f172a;">Afkir Remahan / Hancuran</div>
                                    <div style="font-size: 0.75rem; color: #64748b;">Pecahan kecil / serpihan keripik dari proses penanganan.</div>
                                </td>
                                <td>
                                    <span class="rework-badge-grade badge-remahan">KS REMAHAN</span>
                                </td>
                                <td style="text-align: center; font-weight: 600;">KG</td>
                                <td style="text-align: right;">
                                    <input type="number" step="0.01" min="0" name="qty_remahan" id="qtyRemahan" class="rework-input-table" placeholder="0.00" value="{{ old('qty_remahan', 0) }}" oninput="calculateAllocation()">
                                </td>
                                <td style="text-align: right; font-weight: 700;" id="pctRemahanDisplay">0.0%</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right;">Total Kuantitas Hasil Pemilahan:</td>
                                <td style="text-align: right; font-weight: 700; color: #0f172a;" id="totalOutputFooter">0.00 KG</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Status Bar Alokasi --}}
                <div id="balanceStatusBar" class="balance-status-bar valid" style="margin-top: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span id="balanceStatusText">Masukkan kuantitas bahan yang disortir ulang dan rincian alokasi hasil.</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- BAGIAN 3: PEMULIHAN KARTON DUS (TAMPIL OTOMATIS JIKA SUMBER FG / DUS) --}}
        <div class="rework-card" id="cardPemulihanKarton" style="display: none;">
            <div class="rework-card-header">
                <h2>
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>3. Pemulihan Kemasan Dus / Karton (Sirkular vs Afkir)</span>
                </h2>
            </div>
            <div class="rework-card-body">
                <p style="font-size: 0.8125rem; color: #64748b; margin-top: 0; margin-bottom: 1rem;">
                    Barang sumber teridentifikasi sebagai barang kemasan karton (Finished Goods). Catat jumlah dus yang dibongkar dan alokasi pemulihan karton layak pakai ke gudang kemasan sirkular.
                </p>

                <div class="rework-form-grid">
                    <div class="rework-form-group">
                        <label class="rework-form-label" for="dusDibongkar">Total Dus Dibongkar (pcs)</label>
                        <input type="number" min="0" name="dus_dibongkar" id="dusDibongkar" class="rework-form-control" placeholder="0" value="{{ old('dus_dibongkar', 0) }}">
                    </div>

                    <div class="rework-form-group">
                        <label class="rework-form-label" for="dusPuteran">Dus Layak Puteran &rarr; KARTON PUTERAN (pcs)</label>
                        <input type="number" min="0" name="dus_puteran" id="dusPuteran" class="rework-form-control" placeholder="0" value="{{ old('dus_puteran', 0) }}">
                        <span style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Dikembalikan ke stok kemasan sirkular internal.</span>
                    </div>

                    <div class="rework-form-group">
                        <label class="rework-form-label" for="dusRusak">Dus Rusak / Cacat &rarr; AFKIR DUS (pcs)</label>
                        <input type="number" min="0" name="dus_rusak" id="dusRusak" class="rework-form-control" placeholder="0" value="{{ old('dus_rusak', 0) }}">
                        <span style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Dicatat sebagai limbah afkir karton.</span>
                    </div>
                </div>

                <div id="kartonBalanceNotice" style="display: none; font-size: 0.8125rem; font-weight: 600; margin-top: 0.75rem;"></div>
            </div>
        </div>

        {{-- BAGIAN 4: JUSTIFIKASI MUTU & DISPOSISI QC --}}
        <div class="rework-card">
            <div class="rework-card-header">
                <h2>
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>4. Justifikasi Mutu &amp; Disposisi QC</span>
                </h2>
            </div>
            <div class="rework-card-body">
                <div class="rework-form-grid">
                    <div class="rework-form-group rework-col-full">
                        <label class="rework-form-label" for="alasanQc">Kategori / Alasan QC Penyortiran <span class="req">*</span></label>
                        <select name="alasan_qc" id="alasanQc" class="rework-form-control" required>
                            <option value="Persentase lengket tinggi / tidak jadi rilis IFM" {{ old('alasan_qc') == 'Persentase lengket tinggi / tidak jadi rilis IFM' ? 'selected' : '' }}>
                                Reject Mutu: Persentase lengket tinggi / tidak memenuhi standar rilis IFM
                            </option>
                            <option value="Barang reject tidak disetujui QC buyer ekspor" {{ old('alasan_qc') == 'Barang reject tidak disetujui QC buyer ekspor' ? 'selected' : '' }}>
                                Reject Ekspor: Barang ditolak / tidak disetujui standar QC buyer ekspor
                            </option>
                            <option value="Retur pelanggan akibat kemasan rusak atau cacat fisik" {{ old('alasan_qc') == 'Retur pelanggan akibat kemasan rusak atau cacat fisik' ? 'selected' : '' }}>
                                Retur Distribusi: Retur pelanggan akibat kemasan rusak atau cacat fisik
                            </option>
                            <option value="Kemasan bocor / rusak saat penyimpanan atau handling" {{ old('alasan_qc') == 'Kemasan bocor / rusak saat penyimpanan atau handling' ? 'selected' : '' }}>
                                Internal Gudang: Kemasan bocor / rusak saat handling dan penyimpanan
                            </option>
                            <option value="Lainnya" {{ old('alasan_qc') == 'Lainnya' ? 'selected' : '' }}>
                                Lainnya: Alasan khusus (uraikan secara jelas di catatan)
                            </option>
                        </select>
                    </div>

                    <div class="rework-form-group rework-col-full">
                        <label class="rework-form-label" for="catatanTxt">Catatan Teknis QC / Memo Lapangan</label>
                        <textarea name="catatan_txt" id="catatanTxt" class="rework-form-control" rows="3" placeholder="Tuliskan catatan teknis hasil uji organoleptik, nomor instruksi memo, atau disposisi QC...">{{ old('catatan_txt') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="rework-actions-bar">
            <a href="{{ route('produksi.index') }}" class="btn-secondary-action">Batal</a>
            <button type="submit" id="btnSubmit" class="btn-primary-action">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Simpan Dokumen Sortir Rework</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script>
        window.appConfig = {
            ajaxBatchesUrl: "{{ route('produksi.ajax-batches-rework') }}"
        };
    </script>
    <script src="{{ asset('js/produksi/rework-create.js') }}"></script>
@endpush

