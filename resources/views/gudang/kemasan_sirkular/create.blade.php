@extends('layouts.app')

@section('title', 'Catat Kemasan Sirkular Masuk - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/kemasan_sirkular/sirkular-form.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="sirkular-form-container">
    {{-- BREADCRUMBS & PAGE TITLE --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <a href="{{ route('gudang.kemasan-sirkular.index') }}" style="color: #64748b; font-size: 0.825rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Riwayat Kemasan Sirkular
            </a>
            <h1 style="font-size: 1.45rem; font-weight: 700; color: #0f172a; margin: 0;">Penerimaan Kemasan Sirkular</h1>
            <p style="color: #64748b; font-size: 0.85rem; margin-top: 0.25rem; margin-bottom: 0;">Pencatatan fisik balikan kardus karton pengiriman, pemisahan sortir (Puteran, BMG, Reject), dan sinkronisasi kartu stok gudang.</p>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <a href="{{ route('gudang.kemasan-sirkular.mobile') }}" class="btn btn-outline-primary" style="font-size: 0.8rem; font-weight: 700; padding: 0.45rem 0.85rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.4rem;" title="Beralih ke tampilan layar sentuh HP / Tablet">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Buka Mode HP (Checker Mobile)</span>
            </a>
        </div>
    </div>

    <form action="{{ route('gudang.kemasan-sirkular.store') }}" method="POST" id="formKemasanSirkular">
        @csrf

        {{-- KARTU 1: DOKUMEN PENGIRIMAN & PENERIMAAN FISIK --}}
        <div class="sirkular-card">
            <div class="sirkular-card-header">
                <div class="sirkular-card-title">
                    <span>1. Dokumen Pengiriman &amp; Penerimaan Fisik</span>
                </div>
            </div>

            <div class="sirkular-card-body">
                <div class="form-grid-3" style="margin-bottom: 1.25rem;">
                    {{-- No. Dokumen Penerimaan --}}
                    <div>
                        <label for="sirkular_no" class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                            No. Dokumen Penerimaan <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" name="sirkular_no" id="sirkular_no" value="{{ old('sirkular_no', $sirkularNo) }}" class="form-control" readonly style="background: #f8fafc; font-family: monospace; font-weight: 700; height: 38px;">
                    </div>

                    {{-- Tanggal Penerimaan --}}
                    <div>
                        <label for="sirkular_tgl" class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                            Tanggal Penerimaan Fisik <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" name="sirkular_tgl" id="sirkular_tgl" value="{{ old('sirkular_tgl', date('Y-m-d')) }}" class="form-control" required style="height: 38px;">
                    </div>

                    {{-- Gudang Penerima --}}
                    <div>
                        <label for="gudang_id" class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                            Gudang Penyimpanan <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="gudang_id" id="gudang_id" class="form-control" required style="height: 38px;">
                            @foreach($gudangs as $g)
                                <option value="{{ $g->gudang_id }}" {{ (old('gudang_id') == $g->gudang_id || str_contains(strtoupper($g->gudang_nm), 'KEMASAN') || str_contains(strtoupper($g->gudang_nm), 'BAHAN')) ? 'selected' : '' }}>
                                    {{ $g->gudang_nm }} ({{ $g->gudang_cd }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-grid-3" style="margin-bottom: 1.25rem;">
                    {{-- Referensi Surat Jalan Pengiriman (DO PT Mirasa) --}}
                    <div>
                        <label for="pengeluaran_id" class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                            Ref. Surat Jalan Pengiriman Mirasa (DO)
                        </label>
                        <select name="pengeluaran_id" id="pengeluaran_id" class="form-control" style="height: 38px;">
                            <option value="">-- Pilih Surat Jalan DO (Auto-Fill) --</option>
                            @foreach($pengeluaranList as $p)
                                <option value="{{ $p->pengeluaran_id }}" {{ old('pengeluaran_id') == $p->pengeluaran_id ? 'selected' : '' }}>
                                    [{{ $p->pengeluaran_no }}] - {{ $p->customer?->customer_nm }} ({{ number_format($p->total_qty, 0, ',', '.') }} Box)
                                </option>
                            @endforeach
                        </select>
                        <small style="font-size: 0.725rem; color: #64748b; margin-top: 0.25rem; display: block;">Pilih dokumen pengiriman asal untuk otomatis memuat data muatan.</small>
                    </div>

                    {{-- Customer / Mitra Pengirim --}}
                    <div>
                        <label for="customer_id" class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                            Customer / Pengirim
                        </label>
                        <select name="customer_id" id="customer_id" class="form-control" style="height: 38px;">
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->customer_id }}" {{ (old('customer_id') == $c->customer_id || str_contains(strtoupper($c->customer_nm), 'INDOFOOD')) ? 'selected' : '' }}>
                                    {{ $c->customer_nm }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- No. Dokumen / Bukti Eksternal --}}
                    <div>
                        <label for="surat_jalan_customer_no" class="form-label" style="font-weight: 600; font-size: 0.85rem;">
                            No. Dokumen Eksternal / Bukti Serah Terima <span style="font-weight: 400; color: #64748b; font-size: 0.75rem;">(Opsional)</span>
                        </label>
                        <input type="text" name="surat_jalan_customer_no" id="surat_jalan_customer_no" value="{{ old('surat_jalan_customer_no') }}" class="form-control" placeholder="Contoh: No. tanda terima / memo pembeli" style="height: 38px;">
                        <small style="font-size: 0.725rem; color: #64748b; margin-top: 0.25rem; display: block;">Dapat dikosongkan bila tidak terdapat tanda terima tertulis dari pembeli.</small>
                    </div>
                </div>

                {{-- Baris Armada & Sopir --}}
                <div class="form-grid-3" style="padding-top: 1rem; border-top: 1px dashed #e2e8f0;">
                    <div>
                        <label for="supir_nm" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Nama Sopir</label>
                        <input type="text" name="supir_nm" id="supir_nm" value="{{ old('supir_nm') }}" class="form-control" placeholder="Nama pengemudi armada" style="height: 38px;">
                    </div>
                    <div>
                        <label for="nopol_kendaraan" class="form-label" style="font-weight: 600; font-size: 0.85rem;">No. Polisi Kendaraan</label>
                        <input type="text" name="nopol_kendaraan" id="nopol_kendaraan" value="{{ old('nopol_kendaraan') }}" class="form-control" placeholder="Contoh: B 9123 KPS" style="height: 38px;">
                    </div>
                    <div>
                        <label for="ekspedisi_nm" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Nama Ekspedisi / Armada</label>
                        <input type="text" name="ekspedisi_nm" id="ekspedisi_nm" value="{{ old('ekspedisi_nm', 'Internal Mirasa') }}" class="form-control" style="height: 38px;">
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 2: RINCIAN PENERIMAAN & HASIL SORTIR KEMASAN --}}
        <div class="sirkular-card">
            <div class="sirkular-card-header">
                <div class="sirkular-card-title">
                    <span>2. Rincian Penerimaan &amp; Hasil Sortir Kemasan</span>
                </div>
            </div>

            <div class="sirkular-card-body">
                {{-- RINGKASAN REKONSILIASI KORPORAT --}}
                <div class="sirkular-summary-bar">
                    <div class="sirkular-summary-col">
                        <span class="summary-label">Total Kirim (DO)</span>
                        <span class="summary-val" id="stat_total_kirim">0 Box</span>
                    </div>
                    <div class="sirkular-summary-col">
                        <span class="summary-label">Fisik Kembali</span>
                        <span class="summary-val" id="stat_total_kembali" style="color: #0284c7;">0 Box</span>
                    </div>
                    <div class="sirkular-summary-col">
                        <span class="summary-label">Layak (Puteran)</span>
                        <span class="summary-val" id="stat_total_mulus" style="color: #059669;">0 Box</span>
                    </div>
                    <div class="sirkular-summary-col">
                        <span class="summary-label">Karton BMG</span>
                        <span class="summary-val" id="stat_total_bmg" style="color: #6366f1;">0 Box</span>
                    </div>
                    <div class="sirkular-summary-col">
                        <span class="summary-label">Rusak (Reject)</span>
                        <span class="summary-val" id="stat_total_rusak" style="color: #d97706;">0 Box</span>
                    </div>
                    <div class="sirkular-summary-col">
                        <span class="summary-label">Selisih Pengiriman</span>
                        <span class="summary-val" id="stat_total_selisih" style="color: #059669;">0 Box</span>
                    </div>
                </div>

                {{-- TABEL INPUT HASIL SORTIR (STANDAR EXCEL GRID ERP MIRASA) --}}
                <div style="overflow-x: auto; border: 1px solid #cbd5e1; border-radius: 6px;">
                    <table class="excel-grid-table">
                        <thead>
                            <tr>
                                <th style="width: 36px; text-align: center;">No</th>
                                <th style="text-align: left; min-width: 250px;">Nama Komoditas / Jenis Kemasan</th>
                                <th style="width: 125px; text-align: right;">Qty Kirim (Box)</th>
                                <th style="width: 140px; text-align: right;">Layak / Puteran (Pcs) <span style="color: #ef4444;">*</span></th>
                                <th style="width: 130px; text-align: right;">Karton BMG (Pcs)</th>
                                <th style="width: 140px; text-align: right;">Rusak / Reject (Pcs) <span style="color: #ef4444;">*</span></th>
                                <th style="width: 125px; text-align: right; background: #064e3b; color: #ffffff;">Total Kembali</th>
                                <th style="width: 110px; text-align: right;">Selisih DO</th>
                                <th style="min-width: 220px; text-align: left;">Kondisi Fisik / Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="excel-row-num">1</td>
                                <td>
                                    <strong style="color: #0f172a; font-size: 0.85rem; display: block;">{{ $barangKartonBaru?->barang_nm ?? 'KARTON POLOS IFM (BARU)' }}</strong>
                                    <div style="font-size: 0.725rem; color: #64748b; margin-top: 3px; display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                        <span class="badge" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; font-size: 0.6875rem; font-family: monospace;">{{ $barangKartonPuteran?->barang_cd ?? 'KIF00G-PUT' }} (Layak)</span>
                                        <span class="badge" style="background: #eef2ff; border: 1px solid #c7d2fe; color: #4338ca; font-size: 0.6875rem; font-family: monospace;">{{ $barangKartonBmg?->barang_cd ?? 'KIF00G-BMG' }} (BMG)</span>
                                        <span class="badge" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; font-size: 0.6875rem; font-family: monospace;">{{ $barangKartonReject?->barang_cd ?? 'KMS-KTN-REJ' }} (Reject)</span>
                                    </div>
                                    <input type="hidden" name="items[0][barang_asal_id]" value="{{ $barangKartonBaru?->barang_id ?? 29 }}">
                                    <input type="hidden" name="items[0][barang_mulus_id]" value="{{ $barangKartonPuteran?->barang_id ?? 82 }}">
                                    <input type="hidden" name="items[0][barang_bmg_id]" value="{{ $barangKartonBmg?->barang_id ?? 83 }}">
                                    <input type="hidden" name="items[0][barang_rusak_id]" value="{{ $barangKartonReject?->barang_id ?? 84 }}">
                                </td>
                                <td>
                                    <input type="number" step="1" min="0" name="items[0][kirim_qty]" id="kirim_qty_0" value="{{ old('items.0.kirim_qty', 0) }}" class="form-control" style="text-align: right;" placeholder="0" oninput="calcSortir(0)">
                                </td>
                                <td>
                                    <input type="number" step="1" min="0" name="items[0][mulus_qty]" id="mulus_qty_0" value="{{ old('items.0.mulus_qty', 0) }}" class="form-control" style="text-align: right;" required placeholder="0" oninput="calcSortir(0)">
                                </td>
                                <td>
                                    <input type="number" step="1" min="0" name="items[0][bmg_qty]" id="bmg_qty_0" value="{{ old('items.0.bmg_qty', 0) }}" class="form-control" style="text-align: right;" placeholder="0" oninput="calcSortir(0)">
                                </td>
                                <td>
                                    <input type="number" step="1" min="0" name="items[0][rusak_qty]" id="rusak_qty_0" value="{{ old('items.0.rusak_qty', 0) }}" class="form-control" style="text-align: right;" required placeholder="0" oninput="calcSortir(0)">
                                </td>
                                <td>
                                    <input type="number" step="1" name="items[0][kembali_qty]" id="kembali_qty_0" value="{{ old('items.0.kembali_qty', 0) }}" class="form-control input-readonly-cell" style="text-align: right;" readonly>
                                </td>
                                <td style="text-align: right;">
                                    <span id="selisih_qty_0" style="font-family: monospace; font-weight: 700; font-size: 0.9rem; color: #059669;">0</span>
                                </td>
                                <td>
                                    <input type="text" name="items[0][catatan_txt]" value="{{ old('items.0.catatan_txt') }}" class="form-control text-input-cell" placeholder="Contoh: Lakban sobek, kotor debu, dll">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Catatan Dokumen --}}
                <div style="margin-top: 1.25rem;">
                    <label for="catatan_txt" class="form-label" style="font-weight: 600; font-size: 0.85rem;">Catatan Tambahan (Opsional)</label>
                    <textarea name="catatan_txt" id="catatan_txt" rows="2" class="form-control" placeholder="Tuliskan catatan kondisi muatan atau keterangan selisih jika ada..." style="font-size: 0.85rem;">{{ old('catatan_txt') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; align-items: center; margin-top: 1.25rem;">
            <a href="{{ route('gudang.kemasan-sirkular.index') }}" class="btn btn-secondary" style="padding: 0.5rem 1.25rem; font-weight: 600; font-size: 0.875rem;">Batal</a>
            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1.5rem; font-size: 0.875rem; font-weight: 700;">
                Simpan Penerimaan
            </button>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        window.appConfig = {
            ajaxPengeluaranUrl: "{{ route('gudang.kemasan-sirkular.ajax-pengeluaran-detail', ':id') }}"
        };
    </script>
    <script src="{{ asset('js/gudang/kemasan_sirkular/sirkular-form.js') }}?v={{ time() }}"></script>
@endpush
@endsection
