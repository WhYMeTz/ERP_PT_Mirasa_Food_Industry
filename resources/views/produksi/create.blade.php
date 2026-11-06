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
                    Pencatatan Hasil Produksi Fisik
                </h1>
                <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem; margin-bottom: 0;">
                    Pencatatan aktual hasil penggorengan, penimbangan olahan curah &amp; kemasan, flow meter gas CNG, dan absensi regu kerja berdasarkan dokumen pengeluaran gudang (BPPB).
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
                        <button type="button" id="btnTarikPakai" class="btn btn-secondary btn-sm" onclick="loadPakaiData(document.getElementById('pakai_id').value)" style="padding: 0.45rem 0.95rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem;">
                            <svg id="iconTarikPakai" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span id="btnTarikPakaiText">Tarik Ulang</span>
                        </button>
                    </div>
                    <div id="pakaiMatchNotice" style="display: none; font-size: 0.775rem; font-weight: 600; margin-top: 0.5rem;"></div>

                    {{-- TABEL PEMAKAIAN BAHAN BAKU (SESUAI BLUEPRINT 9.B.1) --}}
                    @include('produksi.partials.card-pemakaian-table')
                </div>

                {{-- PARAMETER OPERASIONAL PRODUKSI (NOMOR DOKUMEN, TANGGAL, SHIFT, LINI, GUDANG) --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: start;">
                    {{-- 1. No. Dokumen Produksi Resmi (Standar Mirasa: {seq}/PRD/MFI/{mm}/{yy}) --}}
                    <div>
                        <label for="produksi_no" style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            No. Dokumen Produksi <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" name="produksi_no" id="produksi_no" class="form-control" value="{{ old('produksi_no', $autoProduksiNo ?? '') }}" style="font-family: monospace; font-weight: 700; color: #0f172a; background: #ffffff;" required>
                        <small id="produksi_no_help" style="display: none; color: #64748b; font-size: 0.725rem; margin-top: 0.25rem;">Nomor urut resmi dokumen hasil produksi.</small>
                    </div>

                    {{-- 2. Tanggal Produksi --}}
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Tanggal Produksi <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" name="produksi_tgl" id="produksi_tgl" class="form-control" value="{{ old('produksi_tgl', date('Y-m-d')) }}" required onchange="updateHariLabel(); if(typeof window.fetchAndApplyProduksiNo === 'function') window.fetchAndApplyProduksiNo(this.value); fetchNextKarton(); syncLaborFromKaryawan(); fetchKursJisdor();">
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
                        <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.25rem; display: block;">Regu operasional shift.</small>
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

        {{-- BAGIAN 4: PEMANTAUAN RENDEMEN & PERFORMA MASAK FISIK (LIVE MONITOR) --}}
        <div class="card" style="margin-bottom: 1.25rem; border: 1px solid #cbd5e1; border-radius: 8px; background: #ffffff;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem;">
                <strong style="color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
                    <span>4. Pemantauan Rendemen &amp; Performa Masak Fisik</span>
                </strong>
            </div>
            <div style="padding: 1.25rem;">
                {{-- 4 KOTAK KPI METRIK FISIK --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    {{-- Metrik 1: Singkong Mentah Masuk --}}
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; text-align: center;">
                        <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em; display: block;">
                            Singkong Masuk (BPPB)
                        </span>
                        <div id="liveSingkongFisik" style="font-size: 1.4rem; font-weight: 800; color: #0f172a; margin-top: 0.25rem;">
                            0.00 kg
                        </div>
                        <small style="color: #64748b; font-size: 0.7rem;">Bahan baku dari gudang</small>
                    </div>

                    {{-- Metrik 2: Total Output Jadi --}}
                    <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 1rem; text-align: center;">
                        <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #0369a1; letter-spacing: 0.04em; display: block;">
                            Total Output Hasil Jadi
                        </span>
                        <div id="liveOutputFisik" style="font-size: 1.4rem; font-weight: 800; color: #0284c7; margin-top: 0.25rem;">
                            0.00 kg
                        </div>
                        <small style="color: #0369a1; font-size: 0.7rem;">Karton + Curah + Berko</small>
                    </div>

                    {{-- Metrik 3: Rendemen Masak --}}
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 1rem; text-align: center;">
                        <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #15803d; letter-spacing: 0.04em; display: block;">
                            Rendemen Penggorengan
                        </span>
                        <div id="liveRendemenDisplay" style="font-size: 1.4rem; font-weight: 800; color: #16a34a; margin-top: 0.25rem;">
                            0.00%
                        </div>
                        <span id="liveRendemenStatusDisplay" style="font-size: 0.725rem; font-weight: 700; color: #15803d; display: block;">
                            Menunggu timbangan
                        </span>
                    </div>

                    {{-- Metrik 4: Rasio Minyak --}}
                    <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 1rem; text-align: center;">
                        <span style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: #b45309; letter-spacing: 0.04em; display: block;">
                            Rasio Pemakaian Minyak
                        </span>
                        <div id="liveMinyakRasioDisplay" style="font-size: 1.4rem; font-weight: 800; color: #d97706; margin-top: 0.25rem;">
                            0.00%
                        </div>
                        <small style="color: #b45309; font-size: 0.7rem;">Sawit &amp; kelapa vs singkong</small>
                    </div>
                </div>

                {{-- CATATAN OPERASIONAL SHIFT --}}
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                        Catatan &amp; Kendala Operasional Shift
                    </label>
                    <textarea name="catatan_txt" id="catatan_txt" rows="2" class="form-control" style="font-size: 0.825rem;" placeholder="Catatan kendala teknis mesin, karakteristik singkong hari ini, pergantian regu, atau keterangan tambahan lain...">{{ old('catatan_txt') }}</textarea>
                </div>
            </div>
        </div>

        {{-- BAGIAN 5: PEMAKAIAN ENERGI (GAS CNG) & KEHADIRAN TENAGA KERJA FISIK --}}
        <div class="card" style="margin-bottom: 1.25rem; border: 1px solid #cbd5e1; border-radius: 8px; background: #ffffff;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem;">
                <strong style="color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
                    <span>5. Pemakaian Energi (Gas CNG) &amp; Kehadiran Tenaga Kerja Fisik</span>
                </strong>
            </div>
            <div style="padding: 1.25rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                            <label style="font-size: 0.8rem; font-weight: 700; color: #334155; margin: 0;">Meteran Gas Alam / CNG (MMBTU) <span style="color: #ef4444;">*</span></label>
                            <span style="font-size: 0.7rem; font-weight: 600; color: #0284c7; background: #e0f2fe; padding: 0.15rem 0.45rem; border-radius: 4px;">Flow Meter Fisik</span>
                        </div>
                        <input type="number" step="0.0001" min="0" name="cng_mmbtu" id="cng_mmbtu" class="form-control" style="text-align: right; font-weight: 700; font-size: 1.1rem; color: #0284c7;" value="{{ old('cng_mmbtu', 0) }}" placeholder="0" required>
                        <small style="color: #64748b; font-size: 0.725rem; display: block; margin-top: 0.25rem;">Masukkan angka delta pemakaian flow meter fisik boiler &amp; fryer untuk shift ini.</small>
                    </div>
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                            <label style="font-size: 0.8rem; font-weight: 700; color: #334155; margin: 0;">Tenaga Kerja Hadir (Orang) <span style="color: #ef4444;">*</span></label>
                            <div style="display: flex; gap: 0.35rem;">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="syncLaborFromKaryawan(true, true)" style="font-size: 0.7rem; padding: 0.15rem 0.45rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Muat Presensi</span>
                                </button>
                                <span style="font-size: 0.7rem; font-weight: 600; color: #059669; background: #ecfdf5; padding: 0.15rem 0.45rem; border-radius: 4px;">Regu Lini</span>
                            </div>
                        </div>
                        <input type="number" min="0" name="tk_jumlah_org" id="tk_jumlah_org" class="form-control" style="text-align: right; font-weight: 700; font-size: 1.1rem; color: #0f172a;" value="{{ old('tk_jumlah_org', 0) }}" placeholder="0" required>
                        <small style="color: #64748b; font-size: 0.725rem; display: block; margin-top: 0.25rem;">Jumlah personel/karyawan yang bertugas di lini produksi shift ini.</small>
                    </div>
                </div>
                <div id="tkSyncStatus" style="font-size: 0.725rem; color: #64748b; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.4rem 0.75rem; margin-top: 0.75rem;">
                    <span id="tkSyncText">Otomatis tersinkron saat Tanggal Produksi &amp; Lini Produksi dipilih. Nilai dapat diisi manual.</span>
                </div>
            </div>
        </div>

        {{-- VARIABEL BIAYA & FINANSIAL HPP (DIKELOLA & DIKALKULASI DI MODUL FINANCE: /produksi/hpp/create) --}}
        <input type="hidden" name="singkong_nilai" id="singkong_nilai" value="{{ old('singkong_nilai', 0) }}">
        <input type="hidden" name="minyak_nilai" id="minyak_nilai" value="{{ old('minyak_nilai', 0) }}">
        <input type="hidden" name="bumbu_nilai" id="bumbu_nilai" value="{{ old('bumbu_nilai', 0) }}">
        <input type="hidden" name="karton_baru_nilai" id="karton_baru_nilai" value="{{ old('karton_baru_nilai', 0) }}">
        <input type="hidden" name="karton_bekas_nilai" id="karton_bekas_nilai" value="{{ old('karton_bekas_nilai', 0) }}">
        <input type="hidden" name="plastik_hd_nilai" id="plastik_hd_nilai" value="{{ old('plastik_hd_nilai', 0) }}">
        <input type="hidden" name="lakban_besar_nilai" id="lakban_besar_nilai" value="{{ old('lakban_besar_nilai', 0) }}">
        <input type="hidden" name="lakban_kecil_nilai" id="lakban_kecil_nilai" value="{{ old('lakban_kecil_nilai', 0) }}">
        <input type="hidden" name="tali_rafia_nilai" id="tali_rafia_nilai" value="{{ old('tali_rafia_nilai', 0) }}">
        <input type="hidden" name="cng_tarif_usd" id="cng_tarif_usd" value="{{ old('cng_tarif_usd', 14.50) }}">
        <input type="hidden" name="cng_kurs_jisdor" id="cng_kurs_jisdor" value="{{ old('cng_kurs_jisdor', 18138) }}">
        <input type="hidden" name="cng_tarif" id="cng_tarif" value="{{ old('cng_tarif', 263001) }}">
        <input type="hidden" name="cng_nilai" id="cng_nilai" value="{{ old('cng_nilai', 0) }}">
        <input type="hidden" name="tk_total_nilai" id="tk_total_nilai" value="{{ old('tk_total_nilai', 0) }}">
        <input type="hidden" name="total_biaya_produksi" id="total_biaya_produksi" value="0">
        <input type="hidden" name="hpp_per_kg" id="hpp_per_kg" value="0">
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

        {{-- ACTION BUTTONS --}}
        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; align-items: center; margin-top: 1.25rem;">
            <a href="{{ route('produksi.index') }}" class="btn btn-secondary" style="padding: 0.65rem 1.25rem; font-weight: 600;">Batal</a>
            <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.5rem; font-size: 0.9rem; font-weight: 700;">
                Simpan Lembar Produksi Harian
            </button>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        window.appConfig = {
            nextKartonUrl: "{{ route('produksi.next-karton') }}",
            pakaiDataUrl: "{{ url('produksi/pakai-data') }}",
            syncKaryawanUrl: "{{ route('produksi.sync-karyawan') }}",
            kursJisdorUrl: "{{ route('produksi.kurs-jisdor') }}",
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
