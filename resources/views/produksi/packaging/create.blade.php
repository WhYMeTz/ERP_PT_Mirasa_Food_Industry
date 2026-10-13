@extends('layouts.app')

@section('title', 'Formulir Pengemasan & Kalkulasi HPP (Packaging) - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-create.css') }}">
    <link rel="stylesheet" href="{{ asset('css/produksi/packaging/packaging-create.css') }}">
@endpush

@section('content')
<div class="create-packaging-container">
    {{-- Breadcrumb & Header Bar (Identik dengan Formulir Hasil Produksi) --}}
    <div style="margin-bottom: 1.25rem;">
        <a href="{{ route('produksi.packaging.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Riwayat Pengemasan
        </a>
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">
                    Formulir Pengemasan &amp; Kalkulasi HPP Barang Jadi (Packaging)
                </h1>
                <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
                    Pencatatan konversi keripik curah (WIP) ke produk kemasan (FG), penarikan resep otomatis (BOM), upah borongan packing, dan pembentukan HPP/Karton.
                </p>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600;">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('produksi.packaging.store') }}" id="formPackagingCreate">
        @csrf
        <input type="hidden" name="bom_id" id="bom_id_hidden" value="">

        {{-- BAGIAN 1: INFORMASI HASIL PENGEMASAN & PARAMETER BARANG JADI --}}
                <div class="card" style="margin-bottom: 1.25rem;">
                    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem;">
                        <strong style="color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
                            <span>1. Informasi Hasil Pengemasan &amp; Parameter Barang Jadi (FG)</span>
                        </strong>
                    </div>

                    <div style="padding: 1.25rem;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                                    Tanggal Pengemasan <span style="color: #ef4444;">*</span>
                                </label>
                                <input type="date" id="pack_tgl" name="pack_tgl" required
                                       class="form-control" value="{{ old('pack_tgl', date('Y-m-d')) }}"
                                       onchange="updateBatchNumber()">
                            </div>

                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                                    Gudang Penyimpanan FG <span style="color: #ef4444;">*</span>
                                </label>
                                <select id="gudang_id" name="gudang_id" required class="form-control">
                                    @foreach($gudangList as $g)
                                        <option value="{{ $g->gudang_id }}" {{ (old('gudang_id', $defaultGudang?->gudang_id) == $g->gudang_id) ? 'selected' : '' }}>
                                            {{ $g->gudang_nm }} ({{ $g->tipe_gudang_cd ?? 'Gudang' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                                    Target Produk Jadi Kemasan <span style="color: #ef4444;">*</span>
                                </label>
                                <select id="barang_jadi_id" name="barang_jadi_id" required class="form-control">
                                    <option value="">-- Pilih Produk Jadi yang Dikemas --</option>
                                    @foreach($barangJadiList as $b)
                                        <option value="{{ $b->barang_id }}" {{ (old('barang_jadi_id', request('barang_jadi_id')) == $b->barang_id) ? 'selected' : '' }}>
                                            {{ $b->barang_nm }} ({{ $b->barang_cd }}) - Satuan: {{ $b->satuanDasar?->satuan_cd ?? 'KARTON' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.25rem;">
                                    Memilih produk ini otomatis memuat formula resep standar (BOM) & kebutuhan keripik curah.
                                </div>
                            </div>

                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                                    Target Rencana (Karton) <span style="color: #ef4444;">*</span>
                                </label>
                                <input type="number" step="0.01" min="0.01" id="target_karton_qty" name="target_karton_qty" required
                                       class="form-control" style="text-align: right; font-weight: 700;" value="{{ old('target_karton_qty', 50) }}">
                                <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.25rem;">
                                    Dasar kalkulasi proporsional BOM.
                                </div>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                                    Hasil Kemas (Karton) <span style="color: #ef4444;">*</span>
                                </label>
                                <input type="number" step="0.01" min="0.01" id="hasil_karton_qty" name="hasil_karton_qty" required
                                       class="form-control" style="text-align: right; font-weight: 800; color: #059669;" value="{{ old('hasil_karton_qty', 50) }}">
                                <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.25rem;">Kuantitas riil fisik.</div>
                            </div>

                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                                    Isi / Karton (Pcs)
                                </label>
                                <input type="number" step="1" min="1" id="isi_per_karton_pcs" name="isi_per_karton_pcs"
                                       class="form-control" style="text-align: right;" value="{{ old('isi_per_karton_pcs', 20) }}">
                                <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.25rem;">Pcs sachet dlm 1 dus.</div>
                            </div>

                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                                    Nomor Lot / Batch FG
                                </label>
                                <input type="text" id="batch_no" name="batch_no"
                                       class="form-control" placeholder="LOT-FG-..." value="{{ old('batch_no') }}"
                                       style="font-family: monospace;">
                                <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.25rem;">Kode stiker box karton.</div>
                            </div>

                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                                    Tanggal Expired
                                </label>
                                <input type="date" id="expired_tgl" name="expired_tgl"
                                       class="form-control" value="{{ old('expired_tgl', date('Y-m-d', strtotime('+6 months'))) }}">
                                <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.25rem;">Masa simpan produk.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- BAGIAN 2: RINCIAN BAHAN KEMASAN & KERIPIK CURAH (WIP) DENGAN STANDAR BOM --}}
                <div class="card" style="margin-bottom: 1.25rem;">
                    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem;">
                        <strong style="color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
                            <span>2. Komponen Bahan Kemasan &amp; Keripik Curah (WIP) Terpakai</span>
                        </strong>
                    </div>

                    <div style="padding: 1.25rem;">
                        {{-- Container Dinamis Info Resep BOM --}}
                        <div id="bomBannerContainer"></div>

                        {{-- Tabel Item Konsumsi --}}
                        <div style="overflow-x: auto; margin-bottom: 0.5rem;">
                            <table class="table" style="width: 100%; border-collapse: collapse; margin: 0; font-size: 0.825rem;">
                                <thead style="background: #f8fafc; color: #334155; font-weight: 700; border-bottom: 1.5px solid #e2e8f0;">
                                    <tr>
                                        <th style="padding: 0.65rem 0.75rem; width: 100px; text-align: left;">KATEGORI</th>
                                        <th style="padding: 0.65rem 0.75rem; text-align: left;">KOMPONEN BAHAN</th>
                                        <th style="padding: 0.65rem 0.75rem; text-align: right; width: 120px;">BOM STANDAR</th>
                                        <th style="padding: 0.65rem 0.75rem; text-align: right; width: 140px;">QTY AKTUAL <span style="color: #ef4444;">*</span></th>
                                        <th style="padding: 0.65rem 0.75rem; text-align: right; width: 130px;">HPP SATUAN (RP)</th>
                                        <th style="padding: 0.65rem 0.75rem; text-align: right; width: 140px;">SUBTOTAL</th>
                                        <th style="padding: 0.65rem 0.75rem; text-align: center; width: 45px;">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody id="materialItemsTbody">
                                    <tr id="emptyMaterialRow">
                                        <td colspan="7" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                                            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.35rem;">
                                                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #cbd5e1;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                <span style="font-weight: 700; color: #64748b;">Silakan pilih "Target Produk Jadi Kemasan" di Bagian 1</span>
                                                <span style="font-size: 0.775rem;">Sistem akan secara otomatis memuat kebutuhan keripik curah, sachet, dus karton, dan lakban.</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- BAGIAN 3: BIAYA TENAGA KERJA KEMAS & OPERASIONAL LAIN --}}
                <div class="card" style="margin-bottom: 1.25rem;">
                    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem;">
                        <strong style="color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
                            <span>3. Biaya Tenaga Kerja Kemas &amp; Catatan Khusus</span>
                        </strong>
                    </div>

                    <div style="padding: 1.25rem;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                                    Upah Tenaga Kerja Kemas (Rp)
                                </label>
                                <input type="number" step="1" min="0" id="total_biaya_tenaga_kerja" name="total_biaya_tenaga_kerja"
                                       class="form-control" style="text-align: right;" value="{{ old('total_biaya_tenaga_kerja', 0) }}"
                                       placeholder="0">
                                <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.25rem;">
                                    Total ongkos borongan/harian packing krupuk ke sachet &amp; dus.
                                </div>
                            </div>

                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                                    Biaya Lain-Lain / Overhead (Rp)
                                </label>
                                <input type="number" step="1" min="0" id="total_biaya_lain" name="total_biaya_lain"
                                       class="form-control" style="text-align: right;" value="{{ old('total_biaya_lain', 0) }}"
                                       placeholder="0">
                                <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.25rem;">
                                    Biaya sealer listrik, lakban tambahan, reject kemasan, dll.
                                </div>
                            </div>
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                                Catatan Operasional Pengemasan
                            </label>
                            <textarea id="catatan_txt" name="catatan_txt" rows="2" class="form-control"
                                      placeholder="Tambahkan catatan nomor meja kemas, tim shift, atau kondisi kemasan...">{{ old('catatan_txt') }}</textarea>
                        </div>
                    </div>
                </div>

        {{-- RINGKASAN KALKULASI REAL-TIME (SERAGAM DENGAN FORM HPP WIP) --}}
        <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 1.15rem 1.25rem; margin-bottom: 1.5rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; align-items: center;">
                {{-- Live Biaya --}}
                <div>
                    <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em;">
                        Total Biaya Pengemasan
                    </span>
                    <div id="sumTotalBiayaProduksi" style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-top: 0.2rem;">
                        Rp 0
                    </div>
                    <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.2rem;">
                        <span id="sumTotalWip" style="color: #c2410c; font-weight: 600;">WIP Rp 0</span> &bull; 
                        <span id="sumTotalKemasan" style="color: #0284c7; font-weight: 600;">Kemas Rp 0</span> &bull; 
                        <span id="sumTotalUpah" style="color: #475569; font-weight: 600;">Upah Rp 0</span>
                    </div>
                </div>

                {{-- Live Hasil Kemas (Karton & Pcs) --}}
                <div style="text-align: center;">
                    <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em;">
                        Hasil Kemas (Finish Good)
                    </span>
                    <div id="sumHasilKemasInfo" style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-top: 0.2rem;">
                        50 Karton
                    </div>
                    <span id="sumTotalPcsInfo" style="font-size: 0.725rem; font-weight: 600; color: #64748b;">
                        Setara 1.000 Pcs Siap Edar
                    </span>
                </div>

                {{-- Live HPP / Karton & Pcs --}}
                <div style="text-align: right;">
                    <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em;">
                        HPP Riil per Karton FG
                    </span>
                    <div id="sumHppPerKarton" style="font-size: 1.5rem; font-weight: 800; color: #059669; margin-top: 0.2rem;">
                        Rp 0
                    </div>
                    <span id="sumHppPerPcs" style="font-size: 0.75rem; font-weight: 700; color: #059669;">
                        Rp 0 / Pcs
                    </span>
                </div>
            </div>
        </div>

        {{-- ACTION BUTTONS (SERAGAM DENGAN FORM HPP WIP) --}}
        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; align-items: center; margin-bottom: 2rem;">
            <a href="{{ route('produksi.packaging.index') }}" class="btn btn-secondary" style="padding: 0.65rem 1.25rem; font-weight: 600;">
                Batal
            </a>
            <button type="submit" id="btnSubmitPackaging" class="btn btn-primary" style="padding: 0.65rem 1.5rem; font-size: 0.9rem; font-weight: 700; background: #059669; border: none; box-shadow: 0 2px 4px rgba(5, 150, 105, 0.2);">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right: 0.25rem;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan &amp; Posting Pengemasan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/produksi/packaging/packaging-create.js') }}"></script>
@endpush
