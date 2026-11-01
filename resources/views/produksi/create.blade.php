@extends('layouts.app')

@section('title', 'Catat Hasil Produksi Harian - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-create.css') }}">
@endpush

@section('content')
<div class="produksi-container">
    {{-- Breadcrumb & Header --}}
    <div style="margin-bottom: 1.25rem;">
        <a href="{{ route('produksi.rekap') }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Buku Rekap HPP Produksi
        </a>
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">
                    Formulir Hasil Produksi &amp; Kalkulasi HPP Harian
                </h1>
                <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
                    Pencatatan aktual hasil produksi harian, pemakaian bahan baku, energi CNG, upah operator, dan kalkulasi rendemen otomatis.
                </p>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600;">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('produksi.store') }}" method="POST" id="formProduksi">
        @csrf

        {{-- BAGIAN 1: INFORMASI DOKUMEN & INTEGRASI PEMAKAIAN BAHAN --}}
        <div class="card" style="margin-bottom: 1.25rem;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem;">
                <strong style="color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
                    <span>1. Dokumen Pengeluaran Gudang &amp; Parameter Operasional Produksi</span>
                </strong>
            </div>
            <div style="padding: 1.25rem;">
                {{-- Status Dokumen otomatis POSTED (Pencatatan aktual pasca-produksi final) --}}
                <input type="hidden" name="status_cd" id="status_cd" value="POSTED">

                {{-- LANGKAH UTAMA: TARIK DATA DARI DOKUMEN BPPB GUDANG --}}
                <div style="background: #f8fafc; padding: 0.95rem 1.15rem; border-radius: 8px; border: 1px solid #cbd5e1; margin-bottom: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.35rem;">
                            <span>Tarik Data Dokumen Pengeluaran Gudang (BPPB):</span>
                        </span>
                        <span style="font-size: 0.75rem; color: #64748b;">
                            Memilih dokumen BPPB otomatis menyinkronkan Lini Produksi dan Rincian Bahan Baku.
                        </span>
                    </div>

                    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                        <select name="pakai_id" id="pakai_id" class="form-control" style="flex: 1; min-width: 280px; font-size: 0.85rem;" onchange="loadPakaiData(this.value)">
                            <option value="">-- Pilih Dokumen Pengeluaran Bahan (BPPB Siap Proses) --</option>
                            @foreach ($pakaiList as $pk)
                                <option value="{{ $pk->pakai_id }}" data-tujuan="{{ $pk->tujuan_pemakaian }}" data-tgl="{{ Carbon\Carbon::parse($pk->pakai_tgl)->format('Y-m-d') }}" {{ (string)old('pakai_id', request('pakai_id')) === (string)$pk->pakai_id ? 'selected' : '' }}>
                                    [{{ $pk->pakai_no }}] {{ Carbon\Carbon::parse($pk->pakai_tgl)->format('d/m/Y') }} - {{ $pk->tujuan_pemakaian }} ({{ $pk->details->count() }} item bahan)
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="loadPakaiData(document.getElementById('pakai_id').value)" style="padding: 0.45rem 0.85rem;">
                            Tarik Ulang
                        </button>
                    </div>
                    <div id="pakaiMatchNotice" style="display: none; font-size: 0.75rem; font-weight: 600; margin-top: 0.4rem;"></div>
                    <div id="pakaiLoading" style="display: none; font-size: 0.75rem; color: #0284c7; margin-top: 0.35rem; font-weight: 600;">
                        Memuat rincian bahan dari dokumen gudang...
                    </div>

                    {{-- TABEL PEMAKAIAN BAHAN BAKU (SESUAI BLUEPRINT 9.B.1) --}}
                    @include('produksi.partials.card-pemakaian-table')
                </div>

                {{-- PARAMETER OPERASIONAL PRODUKSI (TANGGAL, SHIFT, LINI, GUDANG) --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; align-items: start;">
                    {{-- 1. Tanggal Produksi --}}
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Tanggal Produksi <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" name="produksi_tgl" id="produksi_tgl" class="form-control" value="{{ old('produksi_tgl', date('Y-m-d')) }}" required onchange="updateHariLabel(); fetchNextKarton(); syncLaborFromKaryawan();">
                        <div id="hariLabel" style="font-size: 0.75rem; color: #0284c7; font-weight: 600; margin-top: 0.25rem;"></div>
                    </div>

                    {{-- 2. Shift Kerja Produksi --}}
                    <div id="sectionShiftSelection">
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Shift Kerja Produksi <span style="color: #ef4444;">*</span>
                        </label>
                        {{-- Hidden Input Shift & Jam --}}
                        <input type="hidden" name="shift_cd" id="shift_cd" value="{{ old('shift_cd', 'A') }}">
                        <input type="hidden" name="jam_produksi" id="jam_produksi" value="{{ old('jam_produksi', date('H:i')) }}">
                        <div style="display: flex; background: #f1f5f9; padding: 3px; border-radius: 8px; border: 1px solid #cbd5e1; gap: 3px; height: 38px;">
                            <button type="button" id="btnShiftA" onclick="selectShift('A')" 
                                style="flex: 1; padding: 0.4rem 0.75rem; font-weight: 800; font-size: 0.85rem; border: none; border-radius: 6px; cursor: pointer; transition: all 0.2s ease;"
                                class="{{ old('shift_cd', 'A') === 'A' ? 'shift-segmented-active active-a' : 'shift-segmented-inactive' }}">
                                Shift A
                            </button>
                            <button type="button" id="btnShiftB" onclick="selectShift('B')" 
                                style="flex: 1; padding: 0.4rem 0.75rem; font-weight: 800; font-size: 0.85rem; border: none; border-radius: 6px; cursor: pointer; transition: all 0.2s ease;"
                                class="{{ old('shift_cd', 'A') === 'B' ? 'shift-segmented-active active-b' : 'shift-segmented-inactive' }}">
                                Shift B
                            </button>
                        </div>
                        <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.25rem; display: block;">Regu operasional lantai pabrik.</small>
                    </div>

                    {{-- 3. Lini Produksi --}}
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Lini Produksi <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="lini_produksi" id="lini_produksi" class="form-control" required onchange="onLiniProduksiChange()">
                            <option value="">-- Pilih Lini Produksi --</option>
                            @if(isset($liniList) && $liniList->isNotEmpty())
                                @foreach($liniList as $lini)
                                    <option value="{{ $lini->lini_nm }}" 
                                        data-tipe="{{ $lini->tipe_batch }}" 
                                        data-kategori="{{ $lini->kategori_lini }}" 
                                        data-cd="{{ $lini->lini_cd }}" 
                                        {{ old('lini_produksi') === $lini->lini_nm ? 'selected' : '' }}>
                                        {{ $lini->lini_nm }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.25rem; display: block;">Pilih lini mesin penggorengan.</small>
                    </div>

                    {{-- 4. Gudang Penerima Hasil Produksi --}}
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Gudang Penerima Hasil Produksi <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="gudang_id" id="gudang_id" class="form-control" required>
                            <option value="">-- Pilih Gudang Penerima --</option>
                            @foreach ($gudangList as $gdg)
                                <option value="{{ $gdg->gudang_id }}" {{ old('gudang_id') == $gdg->gudang_id ? 'selected' : '' }}>
                                    {{ $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                                </option>
                            @endforeach
                        </select>
                        <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.25rem; display: block;">Lokasi gudang fisik tempat penyetoran barang jadi/WIP.</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- BAGIAN 2: HASIL BARANG PRODUKSI (BARANG JADI & OLAHAN CURAH) --}}
        @include('produksi.partials.card-output-dinamis')

        {{-- BAGIAN 3: PENOMORAN BATCH, KEMASAN & KEDALUWARSA PRODUK --}}
        @include('produksi.partials.card-shift-karton')

        {{-- BAGIAN 4: BIAYA BAHAN BAKU & KEMASAN (DIRECT MATERIALS) --}}
        <div class="card" style="margin-bottom: 1.25rem;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                <strong style="color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
                    <span>4. Biaya Bahan Baku &amp; Kemasan (Direct Materials)</span>
                </strong>
                <span id="badgeSubtotalBahan" style="font-size: 0.8rem; font-weight: 700; color: #334155; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 0.2rem 0.6rem; border-radius: 4px;">
                    Subtotal Bahan: Rp 0
                </span>
            </div>
            <div style="padding: 1.25rem;">
                {{-- Baris 1: Singkong Mentah & Minyak Goreng --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    <div style="background: #ffffff; padding: 0.85rem; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <span style="display: block; font-size: 0.775rem; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 0.5rem;">
                            Singkong Mentah Masuk
                        </span>
                        <div style="display: flex; gap: 0.5rem;">
                            <div style="flex: 1;">
                                <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Kuantitas (Kg)</label>
                                <input type="number" step="0.0001" min="0" name="singkong_qty" id="singkong_qty" class="form-control calc-trigger" style="font-weight: 700; text-align: right;" value="{{ old('singkong_qty', 0) }}" placeholder="0" oninput="calcAll()">
                            </div>
                            <div style="flex: 1.2;">
                                <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Nilai Rupiah (Rp)</label>
                                <input type="number" step="0.01" min="0" name="singkong_nilai" id="singkong_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('singkong_nilai', 0) }}" placeholder="0" oninput="calcAll()">
                            </div>
                        </div>
                    </div>

                    <div style="background: #ffffff; padding: 0.85rem; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <span style="display: block; font-size: 0.775rem; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 0.5rem;">
                            Minyak Sawit &amp; Kelapa
                        </span>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.4rem; margin-bottom: 0.4rem;">
                            <div>
                                <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Sawit (Kg)</label>
                                <input type="number" step="0.0001" min="0" name="minyak_sawit_qty" id="minyak_sawit_qty" class="form-control calc-trigger" style="text-align: right;" value="{{ old('minyak_sawit_qty', 0) }}" placeholder="0" oninput="calcAll()">
                            </div>
                            <div>
                                <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Kelapa (Kg)</label>
                                <input type="number" step="0.0001" min="0" name="minyak_kelapa_qty" id="minyak_kelapa_qty" class="form-control calc-trigger" style="text-align: right;" value="{{ old('minyak_kelapa_qty', 0) }}" placeholder="0" oninput="calcAll()">
                            </div>
                        </div>
                        <div>
                            <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Total Rupiah Minyak (Rp)</label>
                            <input type="number" step="0.01" min="0" name="minyak_nilai" id="minyak_nilai" class="form-control calc-trigger" style="text-align: right; font-weight: 600;" value="{{ old('minyak_nilai', 0) }}" placeholder="0" oninput="calcAll()">
                        </div>
                        <div id="liveMinyakRasio" style="font-size: 0.725rem; color: #475569; font-weight: 600; margin-top: 0.35rem;">
                            Rasio Minyak: 0.00%
                        </div>
                    </div>

                    <div style="background: #ffffff; padding: 0.85rem; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <span style="display: block; font-size: 0.775rem; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 0.5rem;">
                            Bumbu &amp; Perenyah
                        </span>
                        <div style="display: flex; gap: 0.5rem;">
                            <div style="flex: 1;">
                                <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Kuantitas (Kg)</label>
                                <input type="number" step="0.0001" min="0" name="bumbu_qty" id="bumbu_qty" class="form-control" style="text-align: right;" value="{{ old('bumbu_qty', 0) }}" placeholder="0" oninput="calcKemasanItem('bumbu_qty')">
                            </div>
                            <div style="flex: 1.2;">
                                <label style="font-size: 0.7rem; color: #475569; font-weight: 600;">Nilai Rupiah (Rp)</label>
                                <input type="number" step="0.01" min="0" name="bumbu_nilai" id="bumbu_nilai" class="form-control calc-trigger" style="text-align: right;" value="{{ old('bumbu_nilai', 0) }}" placeholder="0" oninput="calcAll()">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Baris 2: Kemasan & Packaging --}}
                <div style="background: #f8fafc; padding: 1rem 1.15rem; border-radius: 8px; border: 1px solid #cbd5e1;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                        <span style="display: block; font-size: 0.8rem; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.03em;">
                            Rincian Bahan Kemasan &amp; Packaging
                        </span>
                        <span style="font-size: 0.725rem; color: #64748b; font-weight: 600;">
                            6 Komponen Material Kemas
                        </span>
                    </div>

                    <div class="kemasan-grid-layout">
                        {{-- 1. Karton Sirkular / Bekas (Karton Standar Utama) --}}
                        <div class="kemasan-card-item" id="card_karton_puteran_item">
                            <div class="kemasan-card-header" style="flex-wrap: wrap;">
                                <div>
                                    <span class="kemasan-card-title" id="label_karton_puteran_title">Karton Sirkular IFM</span>
                                    <small id="badge_karton_puteran_desc" style="display: block; font-size: 0.675rem; color: #059669; font-weight: 600; margin-top: 1px;">
                                        Standar Sirkular 5x Pakai
                                    </small>
                                </div>
                                <span class="kemasan-card-badge" id="badge_karton_puteran_type" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;">Sirkular 5x</span>
                            </div>
                            <div class="kemasan-input-dual">
                                <div class="kemasan-input-col col-qty">
                                    <label class="kemasan-input-label">Kuantitas</label>
                                    <input type="number" step="1" min="0" name="karton_bekas_qty" id="karton_bekas_qty" class="form-control" style="text-align: right; font-size: 0.825rem; font-weight: 600;" value="{{ old('karton_bekas_qty', 0) }}" placeholder="0" oninput="calcKemasanItem('karton_bekas_qty')">
                                </div>
                                <div class="kemasan-input-col col-val">
                                    <label class="kemasan-input-label">Nilai Rupiah (Rp)</label>
                                    <input type="number" step="0.01" min="0" name="karton_bekas_nilai" id="karton_bekas_nilai" class="form-control calc-trigger" style="text-align: right; font-size: 0.825rem; font-weight: 600;" value="{{ old('karton_bekas_nilai', 0) }}" placeholder="0" oninput="calcAll()">
                                </div>
                            </div>
                        </div>

                        {{-- 2. Karton Baru (Hanya Tampil di Lini Manual untuk Pesanan Khusus Baru Sekali Pakai) --}}
                        <div class="kemasan-card-item" id="card_karton_baru_item" style="display: none;">
                            <div class="kemasan-card-header" style="flex-wrap: wrap;">
                                <div>
                                    <span class="kemasan-card-title" id="label_karton_baru_title">Karton Box Baru (Opsional)</span>
                                    <small id="badge_karton_baru_desc" style="display: block; font-size: 0.675rem; color: #b45309; font-weight: 600; margin-top: 1px;">
                                        Khusus Pesanan Baru Sekali Pakai
                                    </small>
                                </div>
                                <span class="kemasan-card-badge" id="badge_karton_baru_type" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a;">Manual Baru</span>
                            </div>
                            <div class="kemasan-input-dual">
                                <div class="kemasan-input-col col-qty">
                                    <label class="kemasan-input-label">Kuantitas</label>
                                    <input type="number" step="1" min="0" name="karton_baru_qty" id="karton_baru_qty" class="form-control" style="text-align: right; font-size: 0.825rem; font-weight: 600;" value="{{ old('karton_baru_qty', 0) }}" placeholder="0" oninput="calcKemasanItem('karton_baru_qty')">
                                </div>
                                <div class="kemasan-input-col col-val">
                                    <label class="kemasan-input-label">Nilai Rupiah (Rp)</label>
                                    <input type="number" step="0.01" min="0" name="karton_baru_nilai" id="karton_baru_nilai" class="form-control calc-trigger" style="text-align: right; font-size: 0.825rem; font-weight: 600;" value="{{ old('karton_baru_nilai', 0) }}" placeholder="0" oninput="calcAll()">
                                </div>
                            </div>
                        </div>

                        {{-- 3. Plastik HD 90x100 --}}
                        <div class="kemasan-card-item">
                            <div class="kemasan-card-header">
                                <span class="kemasan-card-title">Plastik HD 90x100</span>
                                <span class="kemasan-card-badge">Lbr</span>
                            </div>
                            <div class="kemasan-input-dual">
                                <div class="kemasan-input-col col-qty">
                                    <label class="kemasan-input-label">Kuantitas</label>
                                    <input type="number" step="0.01" min="0" name="plastik_hd_qty" id="plastik_hd_qty" class="form-control" style="text-align: right; font-size: 0.825rem; font-weight: 600;" value="{{ old('plastik_hd_qty', 0) }}" placeholder="0" oninput="calcKemasanItem('plastik_hd_qty')">
                                </div>
                                <div class="kemasan-input-col col-val">
                                    <label class="kemasan-input-label">Nilai Rupiah (Rp)</label>
                                    <input type="number" step="0.01" min="0" name="plastik_hd_nilai" id="plastik_hd_nilai" class="form-control calc-trigger" style="text-align: right; font-size: 0.825rem; font-weight: 600;" value="{{ old('plastik_hd_nilai', 0) }}" placeholder="0" oninput="calcAll()">
                                </div>
                            </div>
                        </div>

                        {{-- 4. Lakban Besar --}}
                        <div class="kemasan-card-item">
                            <div class="kemasan-card-header">
                                <span class="kemasan-card-title">Lakban Besar</span>
                                <span class="kemasan-card-badge">Roll</span>
                            </div>
                            <div class="kemasan-input-dual">
                                <div class="kemasan-input-col col-qty">
                                    <label class="kemasan-input-label">Kuantitas</label>
                                    <input type="number" step="0.01" min="0" name="lakban_besar_qty" id="lakban_besar_qty" class="form-control" style="text-align: right; font-size: 0.825rem; font-weight: 600;" value="{{ old('lakban_besar_qty', 0) }}" placeholder="0" oninput="calcKemasanItem('lakban_besar_qty')">
                                </div>
                                <div class="kemasan-input-col col-val">
                                    <label class="kemasan-input-label">Nilai Rupiah (Rp)</label>
                                    <input type="number" step="0.01" min="0" name="lakban_besar_nilai" id="lakban_besar_nilai" class="form-control calc-trigger" style="text-align: right; font-size: 0.825rem; font-weight: 600;" value="{{ old('lakban_besar_nilai', 0) }}" placeholder="0" oninput="calcAll()">
                                </div>
                            </div>
                        </div>

                        {{-- 5. Lakban Kecil --}}
                        <div class="kemasan-card-item">
                            <div class="kemasan-card-header">
                                <span class="kemasan-card-title">Lakban Kecil</span>
                                <span class="kemasan-card-badge">Roll</span>
                            </div>
                            <div class="kemasan-input-dual">
                                <div class="kemasan-input-col col-qty">
                                    <label class="kemasan-input-label">Kuantitas</label>
                                    <input type="number" step="0.01" min="0" name="lakban_kecil_qty" id="lakban_kecil_qty" class="form-control" style="text-align: right; font-size: 0.825rem; font-weight: 600;" value="{{ old('lakban_kecil_qty', 0) }}" placeholder="0" oninput="calcKemasanItem('lakban_kecil_qty')">
                                </div>
                                <div class="kemasan-input-col col-val">
                                    <label class="kemasan-input-label">Nilai Rupiah (Rp)</label>
                                    <input type="number" step="0.01" min="0" name="lakban_kecil_nilai" id="lakban_kecil_nilai" class="form-control calc-trigger" style="text-align: right; font-size: 0.825rem; font-weight: 600;" value="{{ old('lakban_kecil_nilai', 0) }}" placeholder="0" oninput="calcAll()">
                                </div>
                            </div>
                        </div>

                        {{-- 6. Tali Rafia --}}
                        <div class="kemasan-card-item">
                            <div class="kemasan-card-header">
                                <span class="kemasan-card-title">Tali Rafia / Strapping</span>
                                <span class="kemasan-card-badge">Roll</span>
                            </div>
                            <div class="kemasan-input-dual">
                                <div class="kemasan-input-col col-qty">
                                    <label class="kemasan-input-label">Kuantitas</label>
                                    <input type="number" step="0.01" min="0" name="tali_rafia_qty" id="tali_rafia_qty" class="form-control" style="text-align: right; font-size: 0.825rem; font-weight: 600;" value="{{ old('tali_rafia_qty', 0) }}" placeholder="0" oninput="calcKemasanItem('tali_rafia_qty')">
                                </div>
                                <div class="kemasan-input-col col-val">
                                    <label class="kemasan-input-label">Nilai Rupiah (Rp)</label>
                                    <input type="number" step="0.01" min="0" name="tali_rafia_nilai" id="tali_rafia_nilai" class="form-control calc-trigger" style="text-align: right; font-size: 0.825rem; font-weight: 600;" value="{{ old('tali_rafia_nilai', 0) }}" placeholder="0" oninput="calcAll()">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BAGIAN 5 & 6: ENERGI (CNG) & TENAGA KERJA --}}
        <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 1.25rem; margin-bottom: 1.25rem;">
            {{-- ENERGI GAS CNG --}}
            <div class="card">
                <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                    <strong style="color: #0f172a; font-size: 0.95rem;">5. Gas Alam / CNG (Boiler &amp; Fryer)</strong>
                    <span style="font-size: 0.7rem; color: #475569; background: #f1f5f9; border: 1px solid #cbd5e1; padding: 0.15rem 0.45rem; border-radius: 4px; font-weight: 700;">Flow Meter Harian</span>
                </div>
                <div style="padding: 1.25rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Meteran CNG (MMBTU) <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="number" step="0.0001" min="0" name="cng_mmbtu" id="cng_mmbtu" class="form-control calc-trigger" style="text-align: right; font-weight: 700;" value="{{ old('cng_mmbtu', 0) }}" placeholder="0" oninput="calcCng()">
                            <small style="color: #64748b; font-size: 0.7rem;">Delta flow meter fisik.</small>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Tarif per MMBTU (Rp)
                            </label>
                            <input type="number" step="0.01" min="0" name="cng_tarif" id="cng_tarif" class="form-control calc-trigger" style="text-align: right;" value="{{ old('cng_tarif', $defaultCngTarif ?? 226800.00) }}" oninput="calcCng()">
                            <small style="color: #64748b; font-size: 0.7rem;">Tarif acuan (dapat diedit).</small>
                        </div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                            Total Rupiah CNG (Rp)
                        </label>
                        <input type="number" step="0.01" min="0" name="cng_nilai" id="cng_nilai" class="form-control calc-trigger" style="text-align: right; font-weight: 700; color: #0f172a; background: #f8fafc;" value="{{ old('cng_nilai', 0) }}" oninput="calcAll()">
                        <small style="color: #64748b; font-size: 0.7rem;">Otomatis (MMBTU x Tarif) atau isi manual.</small>
                    </div>
                </div>
            </div>

            {{-- TENAGA KERJA --}}
            <div class="card">
                <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <strong style="color: #0f172a; font-size: 0.95rem;">6. Tenaga Kerja Produksi</strong>
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <button type="button" id="btnSyncKaryawan" class="btn btn-sm btn-outline-primary" onclick="syncLaborFromKaryawan(true, true)" style="font-size: 0.725rem; padding: 0.2rem 0.6rem; display: inline-flex; align-items: center; gap: 0.25rem; border-radius: 4px; font-weight: 600; cursor: pointer;" title="Muat ulang data presensi & upah dari modul Karyawan">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Muat Ulang Presensi</span>
                        </button>
                        <span style="font-size: 0.7rem; color: #0284c7; background: #e0f2fe; border: 1px solid #bae6fd; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 700;">Otomatis dari Tanggal &amp; Lini</span>
                    </div>
                </div>
                <div style="padding: 1.25rem;">
                    {{-- Hidden fields backward compatibility --}}
                    <input type="hidden" name="tk_langsung_org" id="tk_langsung_org" value="{{ old('tk_langsung_org', old('tk_jumlah_org', 0)) }}">
                    <input type="hidden" name="tk_tidak_langsung_org" id="tk_tidak_langsung_org" value="0">
                    <input type="hidden" name="tk_training_org" id="tk_training_org" value="0">
                    <input type="hidden" name="tk_tarif_per_org" id="tk_tarif_per_org" value="{{ old('tk_tarif_per_org', 0) }}">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; margin-bottom: 0.75rem;">
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Tenaga Kerja Hadir (Orang) <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="number" min="0" name="tk_jumlah_org" id="tk_jumlah_org" class="form-control calc-trigger" style="text-align: center; font-weight: 700; font-size: 1rem; color: #0f172a;" value="{{ old('tk_jumlah_org', old('tk_langsung_org', 0)) }}" placeholder="0" oninput="onTkJumlahChange()">
                            <small style="color: #64748b; font-size: 0.7rem; display: block; margin-top: 0.25rem;">Otomatis terisi dari absensi kelompok lini kerja.</small>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">
                                Total Upah Tenaga Kerja (Rp) <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="number" step="0.01" min="0" name="tk_total_nilai" id="tk_total_nilai" class="form-control calc-trigger" style="text-align: right; font-weight: 700; font-size: 1rem; color: #0f172a;" value="{{ old('tk_total_nilai', 0) }}" placeholder="0" oninput="calcAll()">
                            <small style="color: #64748b; font-size: 0.7rem; display: block; margin-top: 0.25rem;">Otomatis terisi dari akumulasi upah harian riil.</small>
                        </div>
                    </div>

                    {{-- Status sinkronisasi modul karyawan --}}
                    <div id="tkSyncStatus" style="font-size: 0.725rem; color: #64748b; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.4rem 0.75rem; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                        <span id="tkSyncText">Otomatis tersinkron saat Tanggal Produksi &amp; Lini Produksi dipilih.</span>
                        <span id="tkSyncIndicator" style="display: none; font-weight: 600; color: #0284c7;">Memuat...</span>
                    </div>
                </div>
            </div>
        </div>


        {{-- BIAYA OVERHEAD PABRIK (FOH) DIALIHKAN KE JURNAL KEUANGAN & PEMAKAIAN BAHAN GUDANG --}}
        <input type="hidden" name="qc_pengawasan_nilai" id="qc_pengawasan_nilai" value="0">
        <input type="hidden" name="limbah_kimia_nilai" id="limbah_kimia_nilai" value="0">
        <input type="hidden" name="fotocopy_nilai" id="fotocopy_nilai" value="0">
        <input type="hidden" name="sarung_tangan_plastik_nilai" id="sarung_tangan_plastik_nilai" value="0">
        <input type="hidden" name="sarung_tangan_kain_nilai" id="sarung_tangan_kain_nilai" value="0">
        <input type="hidden" name="sarung_tangan_plastik_qty" id="sarung_tangan_plastik_qty" value="0">
        <input type="hidden" name="sarung_tangan_kain_qty" id="sarung_tangan_kain_qty" value="0">
        <input type="hidden" name="listrik_air_telp_nilai" id="listrik_air_telp_nilai" value="0">
        <input type="hidden" name="pemeliharaan_mesin_nilai" id="pemeliharaan_mesin_nilai" value="0">
        <input type="hidden" name="penyusutan_mesin_nilai" id="penyusutan_mesin_nilai" value="0">
        <input type="hidden" name="limbah_padat_nilai" id="limbah_padat_nilai" value="0">

        {{-- RINGKASAN BIAYA PRODUKSI & ALOKASI HPP PER HASIL PRODUKSI (TERPADU) --}}
        @include('produksi.partials.card-breakdown-hpp')

        {{-- ACTION BUTTONS --}}
        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; align-items: center; margin-top: 1.25rem;">
            <a href="{{ route('produksi.index') }}" class="btn btn-secondary" style="padding: 0.65rem 1.25rem; font-weight: 600;">Batal</a>
            <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.5rem; font-size: 0.9rem; font-weight: 700;">
                Simpan Lembar Produksi Harian
            </button>
        </div>
    </form>
</div>

{{-- MODAL PENGATURAN CEPAT STANDAR TARIF & FOH --}}
@include('produksi.partials.modal-quick-tarif')

@push('scripts')
    <script>
        window.appConfig = {
            nextKartonUrl: "{{ route('produksi.next-karton') }}",
            pakaiDataUrl: "{{ url('produksi/pakai-data') }}",
            syncKaryawanUrl: "{{ route('produksi.sync-karyawan') }}",
            barangHasilList: @json($barangHasilList ?? []),
            oldOutputItems: @json(old('output_items', [])),
            initialFohRates: @json($fohRates ?? []),
            initialEnergiTkRates: @json($energiTkRates ?? []),
            kartonRates: @json($kartonRates ?? ['baru' => 8741, 'puteran' => 1748.20, 'reject' => 0]),
            quickUpdateTarifUrl: "{{ route('master.tarif_produksi.quick-update') }}"
        };
    </script>
    <script src="{{ asset('js/produksi/produksi-create.js') }}?v={{ time() }}"></script>
@endpush
@endsection
