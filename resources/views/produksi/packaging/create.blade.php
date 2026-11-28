@extends('layouts.app')

@section('title', 'Catat Pengemasan Barang Jadi (FG) - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-create.css') }}">
    <link rel="stylesheet" href="{{ asset('css/produksi/packaging/packaging-create.css') }}?v={{ filemtime(public_path('css/produksi/packaging/packaging-create.css')) }}">
@endpush

@section('content')
<div class="create-packaging-container">
    {{-- BREADCRUMB & HEADER UTAMA --}}
    <div style="margin-bottom: 1.25rem;">
        <a href="{{ route('produksi.packaging.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Riwayat Pengemasan
        </a>
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <h1 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
                    Catat Pengemasan Barang Jadi (Finished Goods)
                </h1>
                <p style="color: #64748b; font-size: 0.825rem; margin-top: 0.25rem; margin-bottom: 0;">
                    Pencatatan konversi keripik curah (WIP) ke produk kemasan (FG) dan konsumsi fisik bahan kemasan di lantai gudang.
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
        {{-- HIDDEN PARAMETERS & METADATA --}}
        <input type="hidden" name="laporan_operator_id" id="laporan_operator_id" value="{{ old('laporan_operator_id', $selectedLaporan?->laporan_id) }}">
        <input type="hidden" name="wip_satuan" id="inputWipSatuan" value="{{ old('wip_satuan', $selectedLaporan?->wip_satuan ?? 'KG') }}">
        <input type="hidden" name="satuan_hasil" id="inputSatuanHasil" value="{{ old('satuan_hasil', $selectedLaporan?->satuan_hasil ?? 'KARTON') }}">
        <input type="hidden" name="produksi_id" id="hidden_produksi_id" value="{{ old('produksi_id', $selectedLaporan?->produksi_id) }}">
        <input type="hidden" name="wip_barang_id" id="hidden_wip_barang_id" value="{{ old('wip_barang_id', $selectedLaporan?->wip_barang_id) }}">
        <input type="hidden" name="wip_batch_no" id="hidden_wip_batch_no" value="{{ old('wip_batch_no', $selectedLaporan?->wip_batch_no) }}">
        <input type="hidden" id="karton_qty" name="karton_qty" value="{{ old('karton_qty', $selectedLaporan?->karton_qty ?? 0) }}">
        <input type="hidden" id="lakban_satuan_mode" name="lakban_satuan_mode" value="{{ old('lakban_satuan_mode', $selectedLaporan?->lakban_satuan_mode ?? 'CM') }}">
        <input type="hidden" id="lakban_atas_gr" name="lakban_atas_gr" value="{{ old('lakban_atas_gr', $selectedLaporan?->lakban_atas_gr ?? 0) }}">
        <input type="hidden" id="lakban_bawah_gr" name="lakban_bawah_gr" value="{{ old('lakban_bawah_gr', $selectedLaporan?->lakban_bawah_gr ?? 0) }}">
        <input type="hidden" name="karton_puteran_barang_id" value="{{ $kartonPuteran?->barang_id ?? 82 }}">
        <input type="hidden" name="karton_baru_barang_id" value="{{ $kartonBaru?->barang_id ?? 29 }}">

        {{-- ═════════════════════════════════════════════════════════════════ --}}
        {{-- KARTU 1: INFORMASI PELAKSANAAN & TIM SHIFT KEMAS                  --}}
        {{-- ═════════════════════════════════════════════════════════════════ --}}
        <div class="card-web-form">
            <div class="card-web-header">
                <h2 class="card-web-title">
                    <span class="card-web-num">1</span>
                    <span>Informasi Pelaksanaan &amp; Tim Shift Kemas</span>
                </h2>
                <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Shift &amp; Lokasi Gudang FG</span>
            </div>
            <div class="card-web-body">
                {{-- Baris 1: Tanggal, Shift & Mesin Aktif (3 Kolom Seimbang) --}}
                <div class="grid-web-3" style="margin-bottom: 1rem;">
                    {{-- 1. Tanggal Pelaksanaan --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="pack_tgl">Tanggal Pelaksanaan <span class="req">*</span></label>
                        <input type="date" id="pack_tgl" name="pack_tgl" class="form-control" value="{{ old('pack_tgl', $selectedLaporan?->tgl_kemas ? \Carbon\Carbon::parse($selectedLaporan->tgl_kemas)->format('Y-m-d') : date('Y-m-d')) }}" required>
                    </div>

                    {{-- 2. Shift Kerja Kemas --}}
                    <div id="sectionShiftSelection" class="form-group" style="margin-bottom: 0;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">
                            Shift Kerja Kemas <span style="color: #ef4444;">*</span>
                        </label>
                        @php
                            $currentShift = old('shift', $selectedLaporan?->shift ?? 'A');
                        @endphp
                        <input type="hidden" name="shift" id="inputShift" value="{{ $currentShift }}">
                        <div style="display: flex; background: #f1f5f9; padding: 3px; border-radius: 8px; border: 1px solid #cbd5e1; gap: 3px; height: 38px;">
                            <button type="button" id="btnWebShiftA" onclick="selectWebShift('A')" 
                                style="flex: 1; padding: 0.4rem 0.75rem; font-weight: 800; font-size: 0.85rem; border: none; border-radius: 6px; cursor: pointer; transition: all 0.2s ease;"
                                class="{{ $currentShift === 'A' ? 'shift-segmented-active active-a' : 'shift-segmented-inactive' }}">
                                Shift A
                            </button>
                            <button type="button" id="btnWebShiftB" onclick="selectWebShift('B')" 
                                style="flex: 1; padding: 0.4rem 0.75rem; font-weight: 800; font-size: 0.85rem; border: none; border-radius: 6px; cursor: pointer; transition: all 0.2s ease;"
                                class="{{ $currentShift === 'B' ? 'shift-segmented-active active-b' : 'shift-segmented-inactive' }}">
                                Shift B
                            </button>
                        </div>
                        <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.25rem; display: block;">Regu operasional shift.</small>
                    </div>

                    {{-- 3. Mesin Aktif --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="jumlah_mesin">Mesin Aktif (Unit)</label>
                        <input type="number" id="jumlah_mesin" name="jumlah_mesin" class="form-control" min="1" placeholder="0" value="{{ old('jumlah_mesin', $selectedLaporan?->jumlah_mesin ?? 2) }}">
                    </div>
                </div>

                {{-- Baris 2: Leader Kemas, Gudang Asal Pengambilan Bahan & Gudang Tujuan FG (3 Kolom Seimbang) --}}
                <div class="grid-web-3">
                    {{-- 4. Leader / Operator Kemas --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                            <label class="form-label" for="karyawan_id" style="margin-bottom: 0;">Leader / Operator Kemas <span class="req">*</span></label>
                            <a href="javascript:void(0)" onclick="toggleManualOperatorInput()" style="font-size: 0.725rem; color: #0284c7; text-decoration: none; font-weight: 600;" id="btnToggleManualOp">
                                Ketik Manual
                            </a>
                        </div>
                        <div id="wrapperKaryawanSelect">
                            <select id="karyawan_id" name="karyawan_id" class="form-control" onchange="syncWebKaryawanName(this)">
                                <option value="">-- Pilih dari Daftar Karyawan --</option>
                                @foreach ($karyawanList as $k)
                                    <option value="{{ $k->karyawan_id }}" data-nama="{{ $k->karyawan_nm }}" {{ old('karyawan_id', $selectedLaporan?->karyawan_id) == $k->karyawan_id ? 'selected' : '' }}>
                                        {{ $k->karyawan_nm }} ({{ $k->nik ?: 'Operator' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <input type="text" id="operator_nama" name="operator_nama" class="form-control" placeholder="Nama operator pengemasan..." value="{{ old('operator_nama', $selectedLaporan?->operator_nama ?? (Auth::user()->name ?? '')) }}" style="display: none; margin-top: 0.35rem;" required>
                    </div>

                    {{-- 5. Gudang Asal Pengambilan Bahan Kemasan --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="gudang_asal_id">Gudang Asal Bahan Kemasan <span class="req">*</span></label>
                        <select id="gudang_asal_id" name="gudang_asal_id" required class="form-control" onchange="onGudangAsalChange()">
                            <option value="">-- Pilih Gudang Asal Kemasan --</option>
                            @foreach($gudangList as $g)
                                @php
                                    $isDefaultAsal = (
                                        str_contains(strtoupper($g->gudang_nm), 'KEMASAN') ||
                                        str_contains(strtoupper($g->gudang_cd), 'BB') ||
                                        $g->tipe_gudang_cd === 'BB'
                                    );
                                    $isSelectedAsal = old('gudang_asal_id') ? (old('gudang_asal_id') == $g->gudang_id) : $isDefaultAsal;
                                @endphp
                                <option value="{{ $g->gudang_id }}" {{ $isSelectedAsal ? 'selected' : '' }}>
                                    {{ $g->gudang_nm }} ({{ $g->tipe_gudang_cd ?? 'Gudang' }})
                                </option>
                            @endforeach
                        </select>
                        <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.25rem; display: block;">Asal potong stok BPPB bahan kemasan.</small>
                    </div>

                    {{-- 6. Gudang Tujuan Simpan FG --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="gudang_id">Gudang Tujuan Simpan FG <span class="req">*</span></label>
                        <select id="gudang_id" name="gudang_id" required class="form-control">
                            <option value="">-- Pilih Lokasi Gudang FG --</option>
                            @foreach($gudangList as $g)
                                @php
                                    $isDefaultFg = ($g->gudang_cd === 'GDG-FG' || $g->tipe_gudang_cd === 'FG');
                                    $isSelected = old('gudang_id') ? (old('gudang_id') == $g->gudang_id) : $isDefaultFg;
                                @endphp
                                <option value="{{ $g->gudang_id }}" {{ $isSelected ? 'selected' : '' }}>
                                    {{ $g->gudang_nm }} ({{ $g->tipe_gudang_cd ?? 'Gudang' }})
                                </option>
                            @endforeach
                        </select>
                        <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.25rem; display: block;">Tujuan mutasi simpan produk jadi.</small>
                    </div>

                    {{-- Hidden: Perhitungan personil & upah tenaga kerja kemas dilakukan di modul Lembar HPP Packing --}}
                    <input type="hidden" id="jumlah_personil" name="jumlah_personil" value="{{ old('jumlah_personil', $selectedLaporan?->jumlah_personil ?? 0) }}">
                    <input type="hidden" id="total_biaya_tenaga_kerja" name="total_biaya_tenaga_kerja" value="{{ old('total_biaya_tenaga_kerja', 0) }}">
                </div>
            </div>
        </div>

        {{-- ═════════════════════════════════════════════════════════════════ --}}
                {{-- ═════════════════════════════════════════════════════════════════ --}}
        {{-- KARTU 2: PENGEMASAN DAN SEASONING                                  --}}
        {{-- ═════════════════════════════════════════════════════════════════ --}}
        <div class="card-web-form">
            <div class="card-web-header">
                <h2 class="card-web-title">
                    <span class="card-web-num">2</span>
                    <span>Pengemasan dan Seasoning</span>
                </h2>
                <span style="font-size: 0.775rem; color: #64748b; font-weight: 600;">Alur: Target FG &rarr; Alokasi Curah WIP &rarr; Seasoning</span>
            </div>
            <div class="card-web-body" style="display: flex; flex-direction: column; gap: 1.25rem;">
                {{-- ═════════════════════════════════════════════════════════════════ --}}
                {{-- SUB-CARD A: TARGET HASIL PENGEMASAN BARANG JADI (FG)               --}}
                {{-- ═════════════════════════════════════════════════════════════════ --}}
                <div style="border: 1px solid #cbd5e1; border-radius: 8px; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.03); overflow: hidden;">
                    <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.75rem 1.15rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="background: #059669; color: #fff; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800;">A</span>
                            <strong style="font-size: 0.925rem; color: #0f172a;">Target Hasil Pengemasan Barang Jadi (FG)</strong>
                            <span style="font-size: 0.75rem; color: #64748b;">(Tentukan spesifikasi produk jadi yang akan dihasilkan)</span>
                        </div>
                    </div>

                    <div style="padding: 1.15rem;">
                        {{-- Baris 1: Target Produk Jadi FG --}}
                        <div class="form-group" style="margin-bottom: 0.85rem;">
                            <label class="form-label" for="barang_jadi_id">
                                Target Produk Jadi (FG) <span class="req">*</span>
                            </label>
                            <select id="barang_jadi_id" name="barang_jadi_id" class="form-control" required style="font-weight: 600;" onchange="onTargetProductChange(this)">
                                <option value="">-- Pilih Produk Jadi (FG) --</option>
                                @foreach ($barangJadiList as $b)
                                    <option value="{{ $b->barang_id }}" data-nama="{{ $b->barang_nm }}" {{ (old('barang_jadi_id', $selectedLaporan?->barang_jadi_id) == $b->barang_id) ? 'selected' : '' }}>
                                        {{ $b->barang_nm }} ({{ $b->barang_cd }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Alert Banner: Buffer Sisa Eceran (Rollover Antar-Shift) --}}
                        <div id="alertBufferPack" style="display: none; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 0.85rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                                <div style="display: flex; align-items: center; gap: 0.6rem;">
                                    <span style="font-size: 1.35rem; line-height: 1;">📦</span>
                                    <div>
                                        <div style="font-weight: 700; color: #166534; font-size: 0.875rem;">
                                            Tersedia Sisa di Ruang Kemas: <span id="lblBufferQty" style="font-size: 1.05rem; color: #047857; font-weight: 800;">0</span> Pack
                                        </div>
                                        <div id="lblBufferSource" style="font-size: 0.725rem; color: #4b5563; margin-top: 2px;"></div>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <button type="button" id="btnGabungBuffer" class="btn btn-sm" style="background: #059669; color: #ffffff; font-weight: 700; font-size: 0.775rem; padding: 0.35rem 0.85rem; border-radius: 6px; border: none; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onclick="gabungkanSisaBuffer()">
                                        + Gabungkan ke Kemas Ini
                                    </button>
                                    <button type="button" id="btnBatalGabungBuffer" class="btn btn-sm" style="display: none; background: #e2e8f0; color: #475569; font-weight: 600; font-size: 0.775rem; padding: 0.35rem 0.75rem; border-radius: 6px; border: none; cursor: pointer;" onclick="batalkanGabungBuffer()">
                                        Batal Gabung
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Baris 2: Hasil Kemas Utama, Sisa Eceran, Batch FG & Expired Date --}}
                        <div class="grid-web-4" style="gap: 0.85rem; margin-bottom: 0;">
                            {{-- Hasil Kemas Utama --}}
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" for="hasil_karton_qty">
                                    Hasil Kemas Utama <span class="req">*</span>
                                </label>
                                <div class="input-group-web">
                                    <input type="number" step="any" min="0.0001" id="hasil_karton_qty" name="hasil_karton_qty" class="form-control" placeholder="0" value="{{ old('hasil_karton_qty', $selectedLaporan?->hasil_karton_qty) }}" required style="font-weight: 800; font-size: 0.95rem; color: #047857;">
                                    <select id="selectSatuanHasil" class="form-control" style="width: 85px; max-width: 90px; font-weight: 700; padding: 0.45rem 0.4rem;" onchange="setWebSatuanHasil(this.value)">
                                        <option value="KARTON" {{ old('satuan_hasil', $selectedLaporan?->satuan_hasil ?? 'KARTON') == 'KARTON' ? 'selected' : '' }}>Karton</option>
                                        <option value="BAL" {{ old('satuan_hasil', $selectedLaporan?->satuan_hasil) == 'BAL' ? 'selected' : '' }}>Bal</option>
                                        <option value="PACK" {{ old('satuan_hasil', $selectedLaporan?->satuan_hasil) == 'PACK' ? 'selected' : '' }}>Pack</option>
                                    </select>
                                </div>
                                <div class="isi-karton-tag" style="margin-top: 0.35rem;">
                                    <span>Isi:</span>
                                    <input type="number" step="1" min="1" id="isi_per_karton_pcs" name="isi_per_karton_pcs" value="{{ old('isi_per_karton_pcs', 20) }}" style="width: 44px; font-size: 0.75rem; padding: 1px 3px; border: 1px solid #cbd5e1; border-radius: 4px; text-align: center; font-weight: 700; background: #ffffff;" title="Isi bungkus (pcs) per karton/dus">
                                    <span id="lblSatuanIsi">Pcs / Karton</span>
                                </div>
                            </div>

                            {{-- Sisa Eceran & Rollover Buffer --}}
                            <div class="form-group" style="margin-bottom: 0;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <label class="form-label" for="hasil_pack_qty" style="margin-bottom: 0.2rem;">
                                        Sisa Eceran Baru (Pack)
                                    </label>
                                    <span id="badgeTarikBuffer" style="display: {{ old('tarik_pack_lalu_qty', $selectedLaporan?->tarik_pack_lalu_qty ?? 0) > 0 ? 'inline-block' : 'none' }}; font-size: 0.675rem; font-weight: 700; color: #047857; background: #dcfce7; padding: 1px 6px; border-radius: 4px;" title="Jumlah pack dari sesi sebelumnya yang digabungkan">
                                        +<span id="valTarikBufferText">{{ old('tarik_pack_lalu_qty', $selectedLaporan?->tarik_pack_lalu_qty ?? 0) }}</span> dari Lalu
                                    </span>
                                </div>
                                <div class="input-group-web">
                                    <input type="number" step="any" min="0" id="hasil_pack_qty" name="hasil_pack_qty" class="form-control" placeholder="0" value="{{ old('hasil_pack_qty', $selectedLaporan?->hasil_pack_qty) }}" style="font-weight: 600;">
                                    <span class="input-group-addon" style="font-size: 0.75rem; padding: 0 0.4rem; color: #475569; font-weight: 600;">Pack</span>
                                </div>
                                <input type="hidden" id="tarik_pack_lalu_qty" name="tarik_pack_lalu_qty" value="{{ old('tarik_pack_lalu_qty', $selectedLaporan?->tarik_pack_lalu_qty ?? 0) }}">
                                <small style="color: #64748b; font-size: 0.675rem; margin-top: 0.25rem; display: block;">Sisa di luar karton utuh &rarr; simpan ke buffer kemas.</small>
                            </div>

                            {{-- No. Batch FG (Display Read-only Panel) --}}
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="color: #334155; font-weight: 700;">
                                    Nomor Batch FG (Finished Goods) <span class="req">*</span>
                                </label>
                                @php
                                    $defaultPackDate = old('pack_tgl', $selectedLaporan?->tgl_kemas ? \Carbon\Carbon::parse($selectedLaporan->tgl_kemas)->format('Y-m-d') : date('Y-m-d'));
                                    $defaultBatchDmy = date('dmy', strtotime($defaultPackDate));
                                    $currBatchFg = old('batch_no', $defaultBatchDmy);
                                @endphp
                                <div id="display_batch_fg" style="min-height: 38px; display: flex; align-items: center; padding: 0.45rem 0.75rem; background: #f1f5f9; border: 1.5px solid #cbd5e1; border-radius: 6px; user-select: none;">
                                    <span id="text_batch_fg" style="font-family: ui-monospace, monospace; font-size: 0.95rem; font-weight: 700; color: #0f172a; letter-spacing: 0.05em;">
                                        {{ $currBatchFg }}
                                    </span>
                                </div>
                                <input type="hidden" id="batch_no" name="batch_no" value="{{ $currBatchFg }}">
                                <small style="color: #64748b; font-size: 0.675rem; margin-top: 0.25rem; display: block;">Format kode tanggal kemas: <strong>{{ $defaultBatchDmy }}</strong> (DDMMYY).</small>
                            </div>

                            {{-- Tgl Expired FG --}}
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" for="expired_tgl">
                                    Tanggal Kadaluarsa (Expired Date) <span class="req">*</span>
                                </label>
                                <input type="date" id="expired_tgl" name="expired_tgl" class="form-control" value="{{ old('expired_tgl', date('Y-m-d', strtotime('+6 months'))) }}" style="font-size: 0.825rem; font-weight: 700;" title="Tanggal Kadaluarsa FG">
                                <small style="color: #64748b; font-size: 0.675rem; margin-top: 0.25rem; display: block;">Default 6 bulan sejak tanggal pengerjaan.</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ═════════════════════════════════════════════════════════════════ --}}
                {{-- SUB-CARD B: SUMBER BAHAN CURAH KERIPIK (WIP)                       --}}
                {{-- ═════════════════════════════════════════════════════════════════ --}}
                <div style="border: 1px solid #cbd5e1; border-radius: 8px; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.03); overflow: hidden;">
                    <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.75rem 1.15rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="background: #059669; color: #fff; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800;">B</span>
                            <strong style="font-size: 0.925rem; color: #0f172a;">Sumber Bahan Curah Keripik (WIP)</strong>
                            <span style="font-size: 0.75rem; color: #64748b;">(Mendukung 1 varian atau campuran multi-batch Berko IFM &amp; Manual)</span>
                        </div>
                        <div>
                            <button type="button" class="btn-add-table-row" onclick="addWipCurahRow()" style="font-size: 0.775rem;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Tambah Baris Curah</span>
                            </button>
                        </div>
                    </div>

                    {{-- Tabel Baris Curah --}}
                    <div style="padding: 0;">
                        <div style="overflow-x: auto; border: 1px solid #cbd5e1; border-radius: 8px; background: #ffffff;">
                        <table class="table-web-form" style="margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th style="width: 35px; text-align: center;">No</th>
                                    <th style="width: 110px; text-align: center; white-space: nowrap;">Kategori</th>
                                    <th style="min-width: 240px;">Varian Produk Curah (WIP)</th>
                                    <th style="min-width: 280px;">Pilihan Batch Curah (FIFO)</th>
                                    <th style="width: 165px; text-align: center;">Sisa di Gudang</th>
                                    <th style="width: 160px;">Kuantitas Diambil</th>
                                    <th style="width: 45px; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="wipCurahTbody">
                                <tr id="wip_row_0" class="wip-curah-row">
                                    <td class="curah-row-no" style="text-align: center; font-weight: 700; color: #64748b; vertical-align: middle;">
                                        1
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <span class="cat-badge-web" style="background: #f0f9ff; color: #0369a1; border-color: #bae6fd;">Curah WIP</span>
                                    </td>
                                    <td>
                                        <select id="wip_barang_id_0" class="form-control select-wip-barang" required onchange="onCurahBarangChange(this, 'wip_row_0')">
                                            <option value="">-- Pilih Varian Keripik Curah --</option>
                                            @foreach ($wipBarangList as $wb)
                                                <option value="{{ $wb->barang_id }}" {{ (old('wip_barang_id', $selectedLaporan?->wip_barang_id) == $wb->barang_id) ? 'selected' : '' }}>
                                                    {{ $wb->barang?->barang_nm ?? 'Keripik Curah' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select id="output_id_0" name="wip_items[0][output_id]" class="form-control select-wip-batch" required onchange="onCurahBatchChange(this, 'wip_row_0')">
                                            <option value="">-- Pilih Produk Terlebih Dahulu --</option>
                                        </select>
                                        <input type="hidden" name="wip_items[0][barang_id]" id="wip_hidden_barang_id_0" class="wip-barang-id" value="{{ old('wip_barang_id', $selectedLaporan?->wip_barang_id) }}">
                                        <input type="hidden" name="wip_items[0][produksi_id]" id="wip_hidden_produksi_id_0" class="wip-prod-id" value="{{ old('produksi_id', $selectedLaporan?->produksi_id) }}">
                                        <input type="hidden" name="wip_items[0][batch_no]" id="wip_hidden_batch_no_0" class="wip-batch-no" value="{{ old('wip_batch_no', $selectedLaporan?->wip_batch_no) }}">
                                        <div class="curah-fifo-box" style="display: none; margin-top: 0.35rem;"></div>
                                        <div class="curah-warning-box" style="display: none; font-size: 0.72rem; color: #dc2626; font-weight: 600; margin-top: 0.25rem;"></div>
                                    </td>
                                    <td>
                                        <div class="box-sisa-gudang box-sisa-curah" style="display: none;">
                                            <div class="badge-stok-label">✓ STOK TERSEDIA</div>
                                            <div class="val-stok-qty"><span class="txt-val-sisa-curah">0</span> <small>Kg</small></div>
                                            <div class="sub-stok-label">Siap Dikemas</div>
                                            <button type="button" class="btn-ambil-semua" onclick="takeAllCurahStock(this)">
                                                Ambil Semua (<span class="txt-btn-sisa-curah">0</span>)
                                            </button>
                                        </div>
                                        <div class="empty-sisa-curah" style="text-align: center; color: #94a3b8; font-size: 0.75rem;">
                                            Pilih curah &amp; batch
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group-web">
                                            <input type="number" step="any" min="0.01" id="wip_qty_kg" name="wip_items[0][qty_kg]" class="form-control input-curah-qty" placeholder="0" value="{{ old('wip_qty_kg', $selectedLaporan?->wip_qty_kg) }}" required style="font-weight: 700; font-size: 0.95rem; text-align: right;" oninput="recalcCurahTotal()">
                                            <span class="input-group-addon" style="font-weight: 700; font-size: 0.75rem; padding: 0 0.5rem; color: #475569;">Kg</span>
                                        </div>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <button type="button" class="btn-remove-row" style="opacity: 0.35; cursor: not-allowed;" title="Baris utama tidak dapat dihapus" disabled>
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot style="background: #f8fafc; font-weight: 700; border-top: 1.5px solid #cbd5e1;">
                                <tr>
                                    <td colspan="4" style="text-align: right; padding: 0.65rem 0.85rem; font-size: 0.825rem; color: #334155;">
                                        TOTAL AKUMULASI CURAH TERPAKAI:
                                    </td>
                                    <td style="padding: 0.65rem 0.85rem;">
                                        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.35rem;">
                                            <strong id="lbl_total_curah_wip" style="font-size: 1rem; color: #0284c7; font-family: ui-monospace, monospace; font-weight: 800;">0,00</strong>
                                            <span style="font-size: 0.8rem; font-weight: 700; color: #475569;">Kg</span>
                                        </div>
                                        <input type="hidden" id="total_curah_wip_kg" value="{{ old('wip_qty_kg', $selectedLaporan?->wip_qty_kg ?? 0) }}">
                                    </td>
                                    <td colspan="2" style="padding: 0.65rem 0.85rem; font-size: 0.75rem; color: #64748b; font-weight: normal;">
                                        Total akumulasi curah yang dialokasikan.
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    </div>
                </div>

                {{-- ═════════════════════════════════════════════════════════════════ --}}
                {{-- ═════════════════════════════════════════════════════════════════ --}}
                {{-- SUB-CARD C: TAHAP PEMBUMBUAN (SEASONING TABUR)                     --}}
                {{-- ═════════════════════════════════════════════════════════════════ --}}
                <div style="border: 1px solid #cbd5e1; border-radius: 8px; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.03); overflow: hidden;">
                    <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0.75rem 1.15rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="background: #059669; color: #fff; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800;">C</span>
                            <strong style="font-size: 0.925rem; color: #0f172a;">Pembumbuan (Seasoning Tabur)</strong>
                            <span style="font-size: 0.75rem; color: #64748b;">(Opsional &bull; Kosongkan jika produk Original / Tawar)</span>
                        </div>
                    </div>

                    <div style="padding: 1.15rem;">
                        <div style="display: flex; gap: 1.5rem; align-items: flex-start; flex-wrap: wrap;">
                            {{-- 1. Pilihan Varian Bumbu Tabur --}}
                            <div class="form-group" style="flex: 1; max-width: 420px; min-width: 280px; margin-bottom: 0;">
                                <label class="form-label" for="bumbu_barang_id">
                                    Varian Bumbu Tabur <span style="font-size: 0.75rem; color: #94a3b8; font-weight: normal;">(Opsional)</span>
                                </label>
                                <select id="bumbu_barang_id" name="bumbu_barang_id" class="form-control" onchange="onSeasoningBumbuChange(this)">
                                    <option value="">-- Tanpa Bumbu (Original / Tawar) --</option>
                                    @foreach ($bumbuList as $bm)
                                        <option value="{{ $bm->barang_id }}" 
                                            data-batch="{{ $batchInfoMap[$bm->barang_id]['batch_no'] ?? '-' }}" 
                                            data-stok="{{ number_format($batchInfoMap[$bm->barang_id]['sisa_qty'] ?? 0, 0, ',', '.') }} {{ $bm->satuanDasar?->satuan_nm ?? 'Kg' }}"
                                            data-raw-stok="{{ (float)($batchInfoMap[$bm->barang_id]['sisa_qty'] ?? 0) }}"
                                            data-satuan-nm="{{ $bm->satuanDasar?->satuan_nm ?? 'Kg' }}"
                                            data-gudang-nm="{{ $batchInfoMap[$bm->barang_id]['gudang_nm'] ?? '' }}"
                                            {{ old('bumbu_barang_id', $selectedLaporan?->bumbu_barang_id) == $bm->barang_id ? 'selected' : '' }}>
                                            {{ $bm->barang_nm }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="info_batch_bumbu_seasoning" style="font-size: 0.725rem; color: #047857; margin-top: 0.35rem; display: none;">
                                    ✓ Stok: <strong id="txt_stok_bumbu_seasoning">-</strong> &bull; FIFO: <span style="font-family: monospace;" id="txt_batch_bumbu_seasoning">-</span>
                                </div>
                            </div>

                            {{-- 2. Kuantitas Bumbu Tabur --}}
                            <div class="form-group" style="width: 200px; min-width: 170px; margin-bottom: 0;">
                                <label class="form-label" for="bumbu_qty_kg">
                                    Kuantitas Pemakaian Bumbu
                                </label>
                                <div class="input-group-web">
                                    <input type="number" step="any" min="0" id="bumbu_qty_kg" name="bumbu_qty_kg" class="form-control" placeholder="0" value="{{ old('bumbu_qty_kg', $selectedLaporan?->bumbu_qty_kg) }}" style="font-weight: 700; font-size: 0.95rem; text-align: right;">
                                    @php
                                        $bumbuSatuanVal = old('bumbu_satuan', 'KG');
                                    @endphp
                                    <select id="bumbu_satuan" name="bumbu_satuan" class="form-control" style="max-width: 75px; font-weight: 700; background: #ffffff;">
                                        <option value="KG" {{ $bumbuSatuanVal === 'KG' ? 'selected' : '' }}>Kg</option>
                                        <option value="GRAM" {{ $bumbuSatuanVal === 'GRAM' ? 'selected' : '' }}>Gr</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        {{-- KARTU 3: TABEL PEMAKAIAN MATERIAL KEMASAN                        --}}
        {{-- ═════════════════════════════════════════════════════════════════ --}}
        <div class="card-web-form">
            <div class="card-web-header">
                <h2 class="card-web-title">
                    <span class="card-web-num">3</span>
                    <span>Pemakaian Material Kemasan</span>
                </h2>
                <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                    <span id="badgeBumbuStatus" style="display: none; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 0.7rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px;">
                        Curah Sudah Berbumbu Dapur
                    </span>
                    <button type="button" class="btn-add-table-row" onclick="addKemasanTambahanRow()">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah Baris Bahan</span>
                    </button>
                </div>
            </div>
            <div class="card-web-body" style="padding: 0;">
                <div style="overflow-x: auto;">
                    <table class="table-web-form">
                        <thead>
                            <tr>
                                <th style="width: 35px; text-align: center;">No</th>
                                <th style="width: 130px; white-space: nowrap;">Kategori Bahan</th>
                                <th style="min-width: 220px;">Nama Barang / Material Kemasan</th>
                                <th style="width: 175px; text-align: center;">Sisa di Gudang</th>
                                <th style="min-width: 215px; width: 225px;">Kuantitas Terpakai</th>
                                <th style="width: 130px;">Rusak / Afkir</th>
                                <th style="width: 240px;">Keterangan &amp; Kontrol Cepat</th>
                                <th style="width: 45px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- BARIS 1: PLASTIK KEMASAN UTAMA --}}
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">1</td>
                                <td>
                                    <span class="cat-badge-web cat-plastik-web">Plastik Kemas</span>
                                </td>
                                <td>
                                    <select id="plastik_barang_id" name="plastik_barang_id" class="form-control" onchange="updateRowBatchInfo('plastik_barang_id', 'info_batch_plastik', 'txt_batch_plastik', 'txt_stok_plastik', 'box_sisa_plastik', 'empty_sisa_plastik', 'txt_val_sisa_plastik', 'txt_satuan_sisa_plastik', 'txt_btn_sisa_plastik', 'loc_plastik')">
                                        <option value="">-- Pilih Plastik Kemasan Utama --</option>
                                        @foreach ($plastikList as $pls)
                                            <option value="{{ $pls->barang_id }}" 
                                                data-batch="{{ $batchInfoMap[$pls->barang_id]['batch_no'] ?? '-' }}" 
                                                data-stok="{{ number_format($batchInfoMap[$pls->barang_id]['sisa_qty'] ?? 0, 0, ',', '.') }} {{ $pls->satuanDasar?->satuan_nm ?? 'Pcs' }}"
                                                data-raw-stok="{{ (float)($batchInfoMap[$pls->barang_id]['sisa_qty'] ?? 0) }}"
                                                data-satuan-nm="{{ $pls->satuanDasar?->satuan_nm ?? 'Pcs' }}"
                                                data-gudang-nm="{{ $batchInfoMap[$pls->barang_id]['gudang_nm'] ?? '' }}"
                                                {{ old('plastik_barang_id', $selectedLaporan?->plastik_barang_id) == $pls->barang_id ? 'selected' : '' }}>
                                                {{ $pls->barang_nm }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="info_batch_plastik" class="batch-stock-card stock-available" style="display: none;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap;">
                                            <div style="display: flex; align-items: center; gap: 5px;">
                                                <span class="stock-badge-pill pill-green">✓ STOK TERSEDIA</span>
                                                <strong style="color: #047857; font-family: ui-monospace, monospace; font-size: 0.8rem;" id="txt_stok_plastik">-</strong>
                                            </div>
                                            <span class="batch-tag-pill">FIFO: <strong id="txt_batch_plastik">-</strong></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="box-sisa-gudang" id="box_sisa_plastik" style="display: none;">
                                        <div class="badge-stok-label">✓ STOK TERSEDIA</div>
                                        <div class="val-stok-qty"><span id="txt_val_sisa_plastik">0</span> <small id="txt_satuan_sisa_plastik">Roll</small></div>
                                        <div class="sub-stok-label">Siap Digunakan</div>
                                        <button type="button" class="btn-ambil-semua" id="btn_ambil_plastik" onclick="takeAllStock('plastik_qty', 'txt_val_sisa_plastik')">
                                            Ambil Semua (<span id="txt_btn_sisa_plastik">0</span>)
                                        </button>
                                        <span class="gudang-loc-pill" id="loc_plastik" style="display: none;"></span>
                                    </div>
                                    <div id="empty_sisa_plastik" style="text-align: center; color: #94a3b8; font-size: 0.75rem;">
                                        Pilih plastik
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group-web">
                                        <input type="number" step="any" min="0" id="plastik_qty" name="plastik_qty" class="form-control" value="{{ old('plastik_qty', $selectedLaporan?->plastik_qty) }}" placeholder="0" style="font-weight: 700;">
                                        <select id="plastik_satuan" name="plastik_satuan" class="form-control" style="max-width: 85px; font-weight: 700; background: #ffffff;">
                                            <option value="ROLL" {{ old('plastik_satuan', 'ROLL') == 'ROLL' ? 'selected' : '' }}>Roll</option>
                                            <option value="KG" {{ old('plastik_satuan') == 'KG' ? 'selected' : '' }}>Kg</option>
                                            <option value="PCS" {{ old('plastik_satuan') == 'PCS' ? 'selected' : '' }}>Pcs</option>
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group-web">
                                        <input type="number" step="any" min="0" id="plastik_rusak_pcs" name="plastik_rusak_pcs" class="form-control" placeholder="0" value="{{ old('plastik_rusak_pcs', $selectedLaporan?->plastik_rusak_pcs) }}" style="border-color: #fca5a5;">
                                        <span class="input-group-addon" style="color: #dc2626; font-size: 0.75rem; padding: 0 0.4rem;">Pcs</span>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size: 0.75rem; color: #64748b;">Plastik roll / sachet primer.</span>
                                </td>
                                <td style="text-align: center; color: #cbd5e1;">&bull;</td>
                            </tr>

                            {{-- BARIS 2: DUS KARTON PUTERAN --}}
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">2</td>
                                <td>
                                    <span class="cat-badge-web cat-karton-web">Dus Puteran</span>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #065f46; font-size: 0.85rem;">
                                        Karton Polos IFM (Puteran)
                                    </div>
                                    <div id="info_batch_puteran" class="batch-stock-card {{ $stokKartonPuteran > 0 ? 'stock-available' : 'stock-empty' }}">
                                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap;">
                                            <div style="display: flex; align-items: center; gap: 5px;">
                                                <span class="stock-badge-pill {{ $stokKartonPuteran > 0 ? 'pill-green' : 'pill-red' }}" id="pill_stok_puteran">{{ $stokKartonPuteran > 0 ? '✓ STOK TERSEDIA' : '⚠️ STOK HABIS' }}</span>
                                                <strong style="color: #047857; font-family: ui-monospace, monospace; font-size: 0.8rem;" id="txt_stok_karton_puteran">{{ number_format($stokKartonPuteran, 0, ',', '.') }} Lbr</strong>
                                            </div>
                                            <span class="batch-tag-pill">FIFO: <strong id="txt_batch_karton_puteran">{{ $batchInfoMap[$kartonPuteran?->barang_id]['batch_no'] ?? 'PUT-FIFO' }}</strong></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="box-sisa-gudang {{ $stokKartonPuteran > 0 ? 'stok-ada' : 'stok-habis' }}" id="box_sisa_puteran">
                                        <div class="badge-stok-label" id="badge_sisa_puteran">{{ $stokKartonPuteran > 0 ? '✓ STOK TERSEDIA' : '⚠️ STOK HABIS' }}</div>
                                        <div class="val-stok-qty"><span id="txt_val_sisa_puteran">{{ number_format($stokKartonPuteran, 0, ',', '.') }}</span> <small>Lbr</small></div>
                                        <div class="sub-stok-label" id="sub_sisa_puteran">{{ $stokKartonPuteran > 0 ? 'Siap Digunakan' : 'Stok Kosong di Gudang' }}</div>
                                        <button type="button" class="btn-ambil-semua" id="btn_ambil_puteran" style="{{ $stokKartonPuteran > 0 ? '' : 'display: none;' }}" onclick="takeAllStockDirect('karton_puteran_qty', window.stokKartonPuteranAvailable)">
                                            Ambil Semua (<span id="txt_btn_sisa_puteran">{{ number_format($stokKartonPuteran, 0, ',', '.') }}</span>)
                                        </button>
                                        <span class="gudang-loc-pill" id="loc_puteran" style="display: block;"></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group-web">
                                        <input type="number" step="any" min="0" id="karton_puteran_qty" name="karton_puteran_qty" class="form-control" placeholder="0" value="{{ old('karton_puteran_qty', $selectedLaporan?->karton_puteran_qty) }}" style="font-weight: 700; color: #065f46;" oninput="updateKartonTotal()">
                                        <select id="karton_puteran_satuan" name="karton_puteran_satuan" class="form-control" style="max-width: 85px; font-weight: 700; background: #ffffff;">
                                            <option value="LEMBAR" {{ old('karton_puteran_satuan', 'LEMBAR') == 'LEMBAR' ? 'selected' : '' }}>Lbr</option>
                                            <option value="PCS" {{ old('karton_puteran_satuan') == 'PCS' ? 'selected' : '' }}>Pcs</option>
                                            <option value="IKAT" {{ old('karton_puteran_satuan') == 'IKAT' ? 'selected' : '' }}>Ikat</option>
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group-web">
                                        <input type="number" step="any" min="0" id="karton_rusak_lembar" name="karton_rusak_lembar" class="form-control" placeholder="0" value="{{ old('karton_rusak_lembar', $selectedLaporan?->karton_rusak_lembar) }}" style="border-color: #fca5a5;">
                                        <span class="input-group-addon" style="color: #dc2626; font-size: 0.75rem; padding: 0 0.4rem;">Lbr</span>
                                    </div>
                                </td>
                                <td>
                                    <button type="button" class="btn-fifo-sm" onclick="autoAllocateKartonFifo()" title="Gunakan stok Puteran dulu sampai habis (FIFO)">
                                        <span>Gunakan Puteran Dulu (FIFO)</span>
                                    </button>
                                </td>
                                <td style="text-align: center; color: #cbd5e1;">&bull;</td>
                            </tr>

                            {{-- BARIS 3: DUS KARTON BARU --}}
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">3</td>
                                <td>
                                    <span class="cat-badge-web cat-karton-web">Dus Baru</span>
                                </td>
                                <td>
                                    <select id="karton_barang_id" name="karton_barang_id" class="form-control" onchange="updateRowBatchInfo('karton_barang_id', 'info_batch_karton_baru', 'txt_batch_karton_baru', 'txt_stok_karton_baru', 'box_sisa_karton_baru', 'empty_sisa_karton_baru', 'txt_val_sisa_karton_baru', 'txt_satuan_sisa_karton_baru', 'txt_btn_sisa_karton_baru', 'loc_karton_baru')">
                                        <option value="">-- Pilih Karton Dus Baru (Opsional) --</option>
                                        @foreach ($kartonList as $ktn)
                                            <option value="{{ $ktn->barang_id }}" 
                                                data-batch="{{ $batchInfoMap[$ktn->barang_id]['batch_no'] ?? '-' }}" 
                                                data-stok="{{ number_format($batchInfoMap[$ktn->barang_id]['sisa_qty'] ?? 0, 0, ',', '.') }} Lbr"
                                                data-raw-stok="{{ (float)($batchInfoMap[$ktn->barang_id]['sisa_qty'] ?? 0) }}"
                                                data-satuan-nm="Lbr"
                                                data-gudang-nm="{{ $batchInfoMap[$ktn->barang_id]['gudang_nm'] ?? '' }}"
                                                {{ old('karton_barang_id', $selectedLaporan?->karton_barang_id) == $ktn->barang_id ? 'selected' : '' }}>
                                                {{ $ktn->barang_nm }} ({{ $ktn->barang_cd }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="info_batch_karton_baru" class="batch-stock-card stock-available" style="display: none;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap;">
                                            <div style="display: flex; align-items: center; gap: 5px;">
                                                <span class="stock-badge-pill pill-green">✓ STOK TERSEDIA</span>
                                                <strong style="color: #047857; font-family: ui-monospace, monospace; font-size: 0.8rem;" id="txt_stok_karton_baru">{{ number_format($stokKartonBaru, 0, ',', '.') }} Lbr</strong>
                                            </div>
                                            <span class="batch-tag-pill">FIFO: <strong id="txt_batch_karton_baru">{{ $batchInfoMap[$kartonBaru?->barang_id]['batch_no'] ?? '-' }}</strong></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="box-sisa-gudang" id="box_sisa_karton_baru" style="display: none;">
                                        <div class="badge-stok-label">✓ STOK TERSEDIA</div>
                                        <div class="val-stok-qty"><span id="txt_val_sisa_karton_baru">0</span> <small id="txt_satuan_sisa_karton_baru">Lbr</small></div>
                                        <div class="sub-stok-label">Siap Digunakan</div>
                                        <button type="button" class="btn-ambil-semua" id="btn_ambil_karton_baru" onclick="takeAllStock('karton_baru_qty', 'txt_val_sisa_karton_baru')">
                                            Ambil Semua (<span id="txt_btn_sisa_karton_baru">0</span>)
                                        </button>
                                        <span class="gudang-loc-pill" id="loc_karton_baru" style="display: none;"></span>
                                    </div>
                                    <div id="empty_sisa_karton_baru" style="text-align: center; color: #94a3b8; font-size: 0.75rem;">
                                        Pilih dus baru
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group-web">
                                        <input type="number" step="any" min="0" id="karton_baru_qty" name="karton_baru_qty" class="form-control" placeholder="0" value="{{ old('karton_baru_qty', $selectedLaporan?->karton_baru_qty) }}" style="font-weight: 700; color: #0369a1;" oninput="updateKartonTotal()">
                                        <select id="karton_baru_satuan" name="karton_baru_satuan" class="form-control" style="max-width: 85px; font-weight: 700; background: #ffffff;">
                                            <option value="LEMBAR" {{ old('karton_baru_satuan', 'LEMBAR') == 'LEMBAR' ? 'selected' : '' }}>Lbr</option>
                                            <option value="PCS" {{ old('karton_baru_satuan') == 'PCS' ? 'selected' : '' }}>Pcs</option>
                                            <option value="IKAT" {{ old('karton_baru_satuan') == 'IKAT' ? 'selected' : '' }}>Ikat</option>
                                        </select>
                                    </div>
                                </td>
                                <td style="color: #94a3b8; font-size: 0.75rem; text-align: center;">
                                    -
                                </td>
                                <td>
                                    <span style="font-size: 0.75rem; color: #64748b;">
                                        Total Dus: <strong id="lbl_karton_total_dipakai">{{ old('karton_qty', $selectedLaporan?->karton_qty ?? 0) }}</strong> Lbr
                                    </span>
                                </td>
                                <td style="text-align: center; color: #cbd5e1;">&bull;</td>
                            </tr>

                            {{-- BARIS 4: LAKBAN SEGEL DUS --}}
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">4</td>
                                <td>
                                    <span class="cat-badge-web cat-lakban-web">Lakban Segel</span>
                                </td>
                                <td>
                                    <select id="lakban_barang_id" name="lakban_barang_id" class="form-control" onchange="updateRowBatchInfo('lakban_barang_id', 'info_batch_lakban', 'txt_batch_lakban', 'txt_stok_lakban', 'box_sisa_lakban', 'empty_sisa_lakban', 'txt_val_sisa_lakban', 'txt_satuan_sisa_lakban', 'txt_btn_sisa_lakban', 'loc_lakban')">
                                        <option value="">-- Pilih Lakban Segel --</option>
                                        @foreach ($lakbanList as $lkb)
                                            <option value="{{ $lkb->barang_id }}" 
                                                data-satuan="{{ $lkb->satuanDasar?->satuan_nm ?? 'ROLL' }}"
                                                data-batch="{{ $batchInfoMap[$lkb->barang_id]['batch_no'] ?? '-' }}" 
                                                data-stok="{{ number_format($batchInfoMap[$lkb->barang_id]['sisa_qty'] ?? 0, 0, ',', '.') }} {{ $lkb->satuanDasar?->satuan_nm ?? 'Roll' }}"
                                                data-raw-stok="{{ (float)($batchInfoMap[$lkb->barang_id]['sisa_qty'] ?? 0) }}"
                                                data-satuan-nm="{{ $lkb->satuanDasar?->satuan_nm ?? 'Roll' }}"
                                                data-gudang-nm="{{ $batchInfoMap[$lkb->barang_id]['gudang_nm'] ?? '' }}"
                                                {{ old('lakban_barang_id', $selectedLaporan?->lakban_barang_id) == $lkb->barang_id ? 'selected' : '' }}>
                                                {{ $lkb->barang_nm }} ({{ $lkb->satuanDasar?->satuan_nm ?? 'ROLL' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="info_batch_lakban" class="batch-stock-card stock-available" style="display: none;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap;">
                                            <div style="display: flex; align-items: center; gap: 5px;">
                                                <span class="stock-badge-pill pill-green">✓ STOK TERSEDIA</span>
                                                <strong style="color: #047857; font-family: ui-monospace, monospace; font-size: 0.8rem;" id="txt_stok_lakban">-</strong>
                                            </div>
                                            <span class="batch-tag-pill">FIFO: <strong id="txt_batch_lakban">-</strong></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="box-sisa-gudang" id="box_sisa_lakban" style="display: none;">
                                        <div class="badge-stok-label">✓ STOK TERSEDIA</div>
                                        <div class="val-stok-qty"><span id="txt_val_sisa_lakban">0</span> <small id="txt_satuan_sisa_lakban">Roll</small></div>
                                        <div class="sub-stok-label">Siap Digunakan</div>
                                        <button type="button" class="btn-ambil-semua" id="btn_ambil_lakban" onclick="takeAllStock('lakban_roll_qty', 'txt_val_sisa_lakban')">
                                            Ambil Semua (<span id="txt_btn_sisa_lakban">0</span>)
                                        </button>
                                        <span class="gudang-loc-pill" id="loc_lakban" style="display: none;"></span>
                                    </div>
                                    <div id="empty_sisa_lakban" style="text-align: center; color: #94a3b8; font-size: 0.75rem;">
                                        Pilih lakban
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $initLakbanSatuan = old('lakban_satuan', $selectedLaporan?->lakban_satuan ?? 'GRAM');
                                        $isLakbanRoll = strtoupper($initLakbanSatuan) === 'ROLL';
                                    @endphp
                                    <div style="display: flex; align-items: stretch; gap: 0.4rem;">
                                        <div style="flex: 1; display: flex; flex-direction: column; gap: 0.35rem;">
                                            {{-- Segel Atas --}}
                                            <div style="display: flex; align-items: center; gap: 0.35rem;">
                                                <span style="font-size: 0.72rem; font-weight: 700; color: #475569; width: 44px; flex-shrink: 0;">Atas:</span>
                                                <input type="number" step="any" min="0" id="input_lakban_atas_gr" name="input_segel_atas" class="form-control" placeholder="0" value="{{ $isLakbanRoll && old('lakban_atas_gr') ? round(old('lakban_atas_gr') / 1250, 4) : old('lakban_atas_gr', $selectedLaporan?->lakban_atas_gr) }}" style="font-weight: 700; text-align: right; font-size: 0.85rem;" oninput="onSegelAtasBawahChange()">
                                            </div>
                                            {{-- Segel Bawah --}}
                                            <div style="display: flex; align-items: center; gap: 0.35rem;">
                                                <span style="font-size: 0.72rem; font-weight: 700; color: #475569; width: 44px; flex-shrink: 0;">Bawah:</span>
                                                <input type="number" step="any" min="0" id="input_lakban_bawah_gr" name="input_segel_bawah" class="form-control" placeholder="0" value="{{ $isLakbanRoll && old('lakban_bawah_gr') ? round(old('lakban_bawah_gr') / 1250, 4) : old('lakban_bawah_gr', $selectedLaporan?->lakban_bawah_gr) }}" style="font-weight: 700; text-align: right; font-size: 0.85rem;" oninput="onSegelAtasBawahChange()">
                                            </div>
                                        </div>
                                        {{-- Dropdown Satuan Bersatu (Gr / Roll) --}}
                                        <div style="display: flex; flex-direction: column; justify-content: center; width: 68px; flex-shrink: 0;">
                                            <select id="select_lakban_satuan" class="form-control select-lakban-unit" style="height: 100%; min-height: 60px; font-weight: 800; font-size: 0.85rem; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 6px; text-align: center; color: #047857; cursor: pointer; padding: 0.2rem;" onchange="onLakbanSatuanSwitch(this.value)">
                                                <option value="GRAM" {{ !$isLakbanRoll ? 'selected' : '' }}>Gr</option>
                                                <option value="ROLL" {{ $isLakbanRoll ? 'selected' : '' }}>Roll</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Hidden inputs kompatibilitas database & HPP --}}
                                    <input type="hidden" id="lakban_standar_cm" name="lakban_standar_cm" value="{{ old('lakban_standar_cm', $selectedLaporan?->lakban_standar_cm ?? 120) }}">
                                    <input type="hidden" id="lakban_satuan" name="lakban_satuan" value="{{ old('lakban_satuan', 'GRAM') }}">
                                    <input type="hidden" id="lakban_qty" name="lakban_qty" value="{{ old('lakban_qty', $selectedLaporan?->lakban_qty) }}">
                                    <input type="hidden" id="lakban_jumlah_gr" name="lakban_jumlah_gr" value="{{ old('lakban_jumlah_gr', $selectedLaporan?->lakban_jumlah_gr) }}">
                                    <input type="hidden" id="lakban_roll_qty" name="lakban_roll_qty" value="{{ old('lakban_roll_qty', $selectedLaporan?->lakban_roll_qty) }}">
                                    <input type="hidden" id="input_lakban_total_gr" value="{{ old('lakban_qty', $selectedLaporan?->lakban_qty) }}">
                                </td>
                                <td>
                                    <div class="input-group-web">
                                        <input type="number" step="any" min="0" id="lakban_rusak_m" name="lakban_rusak_m" class="form-control" placeholder="0" value="{{ old('lakban_rusak_m', $selectedLaporan?->lakban_rusak_m) }}" style="border-color: #fca5a5; text-align: right;" oninput="onSegelAtasBawahChange()">
                                        <span id="lbl_rusak_lakban_unit" class="input-group-addon" style="color: #dc2626; font-size: 0.75rem; padding: 0 0.4rem; font-weight: 700;">Gr</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 0.725rem; color: #64748b; line-height: 1.35;">
                                        <div>Total: <strong id="lbl_lakban_total_gr" style="color: #be185d; font-size: 0.85rem; font-family: ui-monospace, monospace;">0</strong> <span id="lbl_lakban_satuan_text" style="font-weight: 700; color: #be185d;">Gr</span></div>
                                        <div id="lbl_lakban_roll_est" style="color: #94a3b8; font-size: 0.675rem;">(~0,00 Roll)</div>
                                        <button type="button" onclick="autoCalcLakbanFromDus()" style="font-size: 0.65rem; background: #fdf2f8; border: 1px solid #fbcfe8; color: #be185d; font-weight: 700; border-radius: 4px; padding: 1px 5px; cursor: pointer; margin-top: 3px; display: inline-block;" title="Hitung standar estimasi dari total karton">
                                            Standar Dus
                                        </button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: #cbd5e1;">&bull;</td>
                            </tr>

                            {{-- BARIS 5: LABEL EXPIRED (KADALUARSA) --}}
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">5</td>
                                <td>
                                    <span class="cat-badge-web cat-lain-web">Label Expired</span>
                                </td>
                                <td>
                                    <select id="expired_barang_id" name="expired_barang_id" class="form-control" onchange="updateRowBatchInfo('expired_barang_id', 'info_batch_expired', 'txt_batch_expired', 'txt_stok_expired', 'box_sisa_expired', 'empty_sisa_expired', 'txt_val_sisa_expired', 'txt_satuan_sisa_expired', 'txt_btn_sisa_expired', 'loc_expired')">
                                        <option value="">-- Pilih Kertas / Plastik Expired --</option>
                                        @foreach ($expiredList as $exp)
                                            <option value="{{ $exp->barang_id }}" 
                                                data-batch="{{ $batchInfoMap[$exp->barang_id]['batch_no'] ?? '-' }}" 
                                                data-stok="{{ number_format($batchInfoMap[$exp->barang_id]['sisa_qty'] ?? 0, 0, ',', '.') }} {{ $exp->satuanDasar?->satuan_nm ?? 'Lbr' }}"
                                                data-raw-stok="{{ (float)($batchInfoMap[$exp->barang_id]['sisa_qty'] ?? 0) }}"
                                                data-satuan-nm="{{ $exp->satuanDasar?->satuan_nm ?? 'Lbr' }}"
                                                data-gudang-nm="{{ $batchInfoMap[$exp->barang_id]['gudang_nm'] ?? '' }}"
                                                {{ old('expired_barang_id', $selectedLaporan?->expired_barang_id) == $exp->barang_id ? 'selected' : '' }}>
                                                {{ $exp->barang_nm }} ({{ $exp->satuanDasar?->satuan_nm ?? 'LEMBAR' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="info_batch_expired" class="batch-stock-card stock-available" style="display: none;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap;">
                                            <div style="display: flex; align-items: center; gap: 5px;">
                                                <span class="stock-badge-pill pill-green">✓ STOK TERSEDIA</span>
                                                <strong style="color: #047857; font-family: ui-monospace, monospace; font-size: 0.8rem;" id="txt_stok_expired">-</strong>
                                            </div>
                                            <span class="batch-tag-pill">FIFO: <strong id="txt_batch_expired">-</strong></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="box-sisa-gudang" id="box_sisa_expired" style="display: none;">
                                        <div class="badge-stok-label">✓ STOK TERSEDIA</div>
                                        <div class="val-stok-qty"><span id="txt_val_sisa_expired">0</span> <small id="txt_satuan_sisa_expired">Lbr</small></div>
                                        <div class="sub-stok-label">Siap Digunakan</div>
                                        <button type="button" class="btn-ambil-semua" id="btn_ambil_expired" onclick="takeAllStock('expired_qty', 'txt_val_sisa_expired')">
                                            Ambil Semua (<span id="txt_btn_sisa_expired">0</span>)
                                        </button>
                                        <span class="gudang-loc-pill" id="loc_expired" style="display: none;"></span>
                                    </div>
                                    <div id="empty_sisa_expired" style="text-align: center; color: #94a3b8; font-size: 0.75rem;">
                                        Pilih label expired
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group-web">
                                        <input type="number" step="any" min="0" id="expired_qty" name="expired_qty" class="form-control" placeholder="0" value="{{ old('expired_qty', $selectedLaporan?->expired_qty) }}" style="font-weight: 700;">
                                        <select id="expired_satuan" name="expired_satuan" class="form-control" style="max-width: 85px; font-weight: 700; background: #ffffff;">
                                            <option value="LEMBAR" {{ old('expired_satuan', 'LEMBAR') == 'LEMBAR' ? 'selected' : '' }}>Lbr</option>
                                            <option value="PCS" {{ old('expired_satuan') == 'PCS' ? 'selected' : '' }}>Pcs</option>
                                            <option value="ROLL" {{ old('expired_satuan') == 'ROLL' ? 'selected' : '' }}>Roll</option>
                                        </select>
                                    </div>
                                </td>
                                <td style="color: #94a3b8; font-size: 0.75rem; text-align: center;">
                                    -
                                </td>
                                <td>
                                    <span style="font-size: 0.75rem; color: #64748b;">Label stiker / cetak tanggal kedaluwarsa.</span>
                                </td>
                                <td style="text-align: center; color: #cbd5e1;">&bull;</td>
                            </tr>
                        </tbody>
                        {{-- WADAH BARIS DINAMIS KEMASAN TAMBAHAN (PLASTIK BAL, RAFIA, DLL) --}}
                        <tbody id="kemasanTambahanTbody">
                            {{-- Diisi via JavaScript saat klik tombol tambah --}}
                        </tbody>
                    </table>
                </div>

                {{-- Catatan Operasional & Kendala Lapangan di Footer Kartu 3 --}}
                <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 1.15rem 1.25rem;">
                    <label class="form-label" for="catatan_txt" style="font-weight: 700; color: #334155; margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.4rem;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>Catatan Operasional &amp; Kendala Lapangan</span>
                    </label>
                    <textarea id="catatan_txt" name="catatan_txt" class="form-control" rows="2" placeholder="Catatan tim shift, nomor meja kemas, kendala mesin/sealer, atau kondisi kemasan...">{{ old('catatan_txt', $selectedLaporan?->catatan) }}</textarea>
                    <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.35rem; display: block;">Keterangan tambahan shift mengenai jalannya proses pengemasan (opsional).</small>
                </div>
            </div>
        </div>


        {{-- TOMBOL AKSI SUBMIT FORMULIR                                        --}}
        {{-- ═════════════════════════════════════════════════════════════════ --}}
        <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem; margin-top: 1.5rem; padding-bottom: 2rem;">
            <a href="{{ route('produksi.packaging.index') }}" class="btn" style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #475569; font-weight: 700; font-size: 0.875rem; padding: 0.65rem 1.25rem; border-radius: 6px; text-decoration: none;">
                Batal
            </a>

            <button type="submit" class="btn btn-primary" style="background: #059669; border: 1.5px solid #059669; color: #ffffff; font-weight: 700; font-size: 0.925rem; padding: 0.65rem 1.5rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 2px 4px rgba(5, 150, 105, 0.2); cursor: pointer;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                <span>Simpan Dokumen Pengemasan</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script>
        window.stokKartonPuteranAvailable = {{ (float) $stokKartonPuteran }};
        window.stokKartonBaruAvailable = {{ (float) $stokKartonBaru }};
        window.stokKartonRejectAvailable = {{ (float) $stokKartonReject }};

        window.stokBatchByGudang = @json($stokBatchByGudang ?? []);
        window.stokKartonPuteranByGudang = @json($stokKartonPuteranByGudang ?? []);
        window.stokKartonBaruByGudang = @json($stokKartonBaruByGudang ?? []);
        window.stokKartonRejectByGudang = @json($stokKartonRejectByGudang ?? []);

        window.kemasanLainOptions = [
            @foreach ($kemasanLainList as $kml)
                {
                    barang_id: {{ $kml->barang_id }},
                    barang_nm: @json($kml->barang_nm),
                    satuan_nm: @json($kml->satuanDasar?->satuan_nm ?? 'PCS'),
                    batch_no: @json($batchInfoMap[$kml->barang_id]['batch_no'] ?? '-'),
                    sisa_qty: "{{ number_format($batchInfoMap[$kml->barang_id]['sisa_qty'] ?? 0, 0, ',', '.') }}",
                    raw_sisa_qty: {{ (float) ($batchInfoMap[$kml->barang_id]['sisa_qty'] ?? 0) }},
                    gudang_nm: @json($batchInfoMap[$kml->barang_id]['gudang_nm'] ?? '')
                },
            @endforeach
        ];

        window.wipBarangOptions = [
            @foreach ($wipBarangList as $wb)
                {
                    barang_id: {{ $wb->barang_id }},
                    barang_nm: @json($wb->barang?->barang_nm ?? 'Keripik Curah')
                },
            @endforeach
        ];

        window.wipListOptions = [
            @foreach ($wipList as $wip)
                @php
                    $prodNo = $wip->produksi?->produksi_no ?? 'PROD';
                    $batchStr = $wip->batch_no ?: ($wip->produksi?->batch_wip_no ?: ($wip->produksi?->produksi_tgl ? $wip->produksi->produksi_tgl->format('d m Y') : '-'));
                    $barangNm = $wip->barang?->barang_nm ?? 'Keripik Curah';
                    $qtyKg = (float) ($wip->sisa_qty_kg ?? $wip->qty_kg);
                    $hppKg = (float) ($wip->hpp_satuan > 0 ? $wip->hpp_satuan : ($wip->produksi?->hpp_per_kg ?? $wip->barang?->harga_beli_standar ?? 0));
                    $isBerbumbu = (float) ($wip->produksi?->bumbu_qty ?? 0) > 0 || str_contains(strtoupper($barangNm), 'BUMBU') || str_contains(strtoupper($barangNm), 'BALADO');
                    $isOldest = $wip->is_fifo_oldest ?? false;
                    $oldestQtyFormatted = number_format($wip->oldest_sisa_qty ?? 0, 1, ',', '.');
                @endphp
                {
                    output_id: {{ $wip->output_id }},
                    produksi_id: {{ $wip->produksi_id ?? 'null' }},
                    barang_id: {{ $wip->barang_id ?? 'null' }},
                    batch: @json($batchStr),
                    barang_nm: @json($barangNm),
                    prod_no: @json($prodNo),
                    qty: {{ $qtyKg }},
                    hpp: {{ $hppKg }},
                    is_berbumbu: {{ $isBerbumbu ? 1 : 0 }},
                    is_fifo: {{ $isOldest ? 1 : 0 }},
                    oldest_output_id: {{ $wip->oldest_output_id ?? 'null' }},
                    oldest_batch: @json($wip->oldest_batch_str ?? ''),
                    oldest_qty: @json($oldestQtyFormatted)
                },
            @endforeach
        ];

        window.preloadedOperatorLaporan = @json($selectedLaporan ?? null);

        // Fungsi Update Info Batch FIFO & Stok di Bawah Dropdown Barang serta Kolom Sisa di Gudang
        function updateRowBatchInfo(selectId, infoBoxId, txtBatchId, txtStokId, boxSisaId, emptySisaId, valSisaId, satuanSisaId, btnSisaId, locSisaId) {
            const sel = document.getElementById(selectId);
            const box = document.getElementById(infoBoxId);
            const txtB = document.getElementById(txtBatchId);
            const txtS = document.getElementById(txtStokId);
            
            const boxSisa = boxSisaId ? document.getElementById(boxSisaId) : null;
            const emptySisa = emptySisaId ? document.getElementById(emptySisaId) : null;
            const valSisa = valSisaId ? document.getElementById(valSisaId) : null;
            const satuanSisa = satuanSisaId ? document.getElementById(satuanSisaId) : null;
            const btnSisa = btnSisaId ? document.getElementById(btnSisaId) : null;
            const locSisa = locSisaId ? document.getElementById(locSisaId) : null;

            if (!sel) return;

            const opt = sel.options[sel.selectedIndex];
            if (opt && opt.value) {
                const b = opt.getAttribute('data-batch') || '-';
                const s = opt.getAttribute('data-stok') || '0';
                const rawQty = opt.getAttribute('data-raw-stok') || s;
                const satuan = opt.getAttribute('data-satuan-nm') || '';
                const gudangNm = opt.getAttribute('data-gudang-nm') || '';

                if (txtB) txtB.textContent = b;
                if (txtS) txtS.textContent = s;

                // Cek ketersediaan stok numerik
                const numericStok = parseFloat(String(rawQty).replace(/[^\d.,]/g, '').replace(',', '.')) || 0;
                const formattedStok = numericStok.toLocaleString('id-ID');

                if (box) {
                    const statusPill = box.querySelector('.stock-badge-pill');
                    if (statusPill) {
                        if (numericStok > 0) {
                            statusPill.className = 'stock-badge-pill pill-green';
                            statusPill.innerHTML = '✓ STOK TERSEDIA';
                            box.className = 'batch-stock-card stock-available';
                        } else {
                            statusPill.className = 'stock-badge-pill pill-red';
                            statusPill.innerHTML = '⚠️ STOK HABIS';
                            box.className = 'batch-stock-card stock-empty';
                        }
                    }
                    box.style.display = 'block';
                }

                if (boxSisa && emptySisa) {
                    emptySisa.style.display = 'none';
                    boxSisa.style.display = 'block';
                    if (valSisa) valSisa.textContent = formattedStok;
                    if (satuanSisa) satuanSisa.textContent = satuan;
                    if (btnSisa) btnSisa.textContent = formattedStok;

                    if (locSisa) {
                        if (gudangNm) {
                            locSisa.textContent = gudangNm;
                            locSisa.style.display = 'block';
                        } else {
                            locSisa.style.display = 'none';
                        }
                    }

                    const badgeLabel = boxSisa.querySelector('.badge-stok-label');
                    const subLabel = boxSisa.querySelector('.sub-stok-label');
                    const btnAmbil = boxSisa.querySelector('.btn-ambil-semua');

                    if (numericStok > 0) {
                        boxSisa.className = 'box-sisa-gudang stok-ada';
                        if (badgeLabel) badgeLabel.textContent = '✓ STOK TERSEDIA';
                        if (subLabel) subLabel.textContent = 'Siap Digunakan';
                        if (btnAmbil) btnAmbil.style.display = 'inline-block';
                    } else {
                        boxSisa.className = 'box-sisa-gudang stok-habis';
                        if (badgeLabel) badgeLabel.textContent = '⚠️ STOK HABIS';
                        if (subLabel) subLabel.textContent = 'Stok Kosong di Gudang';
                        if (btnAmbil) btnAmbil.style.display = 'none';
                    }
                }
            } else {
                if (box) box.style.display = 'none';
                if (boxSisa && emptySisa) {
                    boxSisa.style.display = 'none';
                    emptySisa.style.display = 'block';
                }
            }
        }
        window.updateRowBatchInfo = updateRowBatchInfo;

        function takeAllStock(inputId, valSpanId) {
            const span = document.getElementById(valSpanId);
            const input = document.getElementById(inputId);
            if (!span || !input) return;
            const cleanNum = parseFloat(span.textContent.replace(/\./g, '').replace(',', '.')) || 0;
            input.value = cleanNum;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
        window.takeAllStock = takeAllStock;

        function takeAllStockDirect(inputId, numVal) {
            const input = document.getElementById(inputId);
            if (!input) return;
            input.value = numVal;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
        window.takeAllStockDirect = takeAllStockDirect;

        function takeAllStockDynamic(btn) {
            const td = btn.closest('td');
            const tr = btn.closest('tr');
            if (!td || !tr) return;
            const span = td.querySelector('.txt-val-sisa-tambahan');
            const input = tr.querySelector('.input-qty-tambahan');
            if (!span || !input) return;
            const cleanNum = parseFloat(span.textContent.replace(/\./g, '').replace(',', '.')) || 0;
            input.value = cleanNum;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
        window.takeAllStockDynamic = takeAllStockDynamic;

        // Reaktif Filter Stok Bahan Kemasan Berdasarkan Gudang Asal Terpilih di Card 1
        function onGudangAsalChange() {
            const selGudang = document.getElementById('gudang_asal_id');
            if (!selGudang) return;
            const gudangId = parseInt(selGudang.value);
            const optGudang = selGudang.options[selGudang.selectedIndex];
            const gudangNm = (optGudang && optGudang.value) ? optGudang.text.replace(/\s*\([^)]*\)$/, '').trim() : '';

            const gudangBatches = (window.stokBatchByGudang && gudangId) ? (window.stokBatchByGudang[gudangId] || {}) : {};

            // 1. Update options pada dropdown bahan kemasan Card 3
            const selectIds = ['plastik_barang_id', 'karton_barang_id', 'lakban_barang_id', 'expired_barang_id', 'bumbu_barang_id'];
            selectIds.forEach(id => {
                const selectEl = document.getElementById(id);
                if (!selectEl) return;
                Array.from(selectEl.options).forEach(opt => {
                    if (!opt.value) return;
                    const barangId = parseInt(opt.value);
                    const bInfo = gudangBatches[barangId];
                    const satuanNm = opt.getAttribute('data-satuan-nm') || '';
                    if (bInfo && bInfo.sisa_qty > 0) {
                        opt.setAttribute('data-batch', bInfo.batch_no || '-');
                        opt.setAttribute('data-stok', (bInfo.sisa_qty).toLocaleString('id-ID') + (satuanNm ? ' ' + satuanNm : ''));
                        opt.setAttribute('data-raw-stok', bInfo.sisa_qty);
                        opt.setAttribute('data-gudang-nm', bInfo.gudang_nm || gudangNm);
                    } else {
                        opt.setAttribute('data-batch', '-');
                        opt.setAttribute('data-stok', '0' + (satuanNm ? ' ' + satuanNm : ''));
                        opt.setAttribute('data-raw-stok', 0);
                        opt.setAttribute('data-gudang-nm', gudangNm);
                    }
                });
            });

            // 2. Update info Karton Puteran
            const puteranQty = (window.stokKartonPuteranByGudang && gudangId) ? (window.stokKartonPuteranByGudang[gudangId] || 0) : 0;
            window.stokKartonPuteranAvailable = puteranQty;
            const puteranBatchInfo = gudangBatches[{{ $kartonPuteran?->barang_id ?? 0 }}];
            const puteranBatchStr = puteranBatchInfo ? puteranBatchInfo.batch_no : '-';

            const txtStokPuteran = document.getElementById('txt_stok_karton_puteran');
            const txtBatchPuteran = document.getElementById('txt_batch_karton_puteran');
            const pillPuteran = document.getElementById('pill_stok_puteran');
            const boxSisaPuteran = document.getElementById('box_sisa_puteran');
            const valSisaPuteran = document.getElementById('txt_val_sisa_puteran');
            const btnSisaPuteran = document.getElementById('txt_btn_sisa_puteran');
            const btnAmbilPuteran = document.getElementById('btn_ambil_puteran');
            const badgeSisaPuteran = document.getElementById('badge_sisa_puteran');
            const subSisaPuteran = document.getElementById('sub_sisa_puteran');
            const locPuteran = document.getElementById('loc_puteran');

            if (txtStokPuteran) txtStokPuteran.textContent = puteranQty.toLocaleString('id-ID') + ' Lbr';
            if (txtBatchPuteran) txtBatchPuteran.textContent = puteranBatchStr;
            if (valSisaPuteran) valSisaPuteran.textContent = puteranQty.toLocaleString('id-ID');
            if (btnSisaPuteran) btnSisaPuteran.textContent = puteranQty.toLocaleString('id-ID');
            if (locPuteran && gudangNm) locPuteran.textContent = gudangNm;

            if (boxSisaPuteran) {
                if (puteranQty > 0) {
                    boxSisaPuteran.className = 'box-sisa-gudang stok-ada';
                    if (badgeSisaPuteran) badgeSisaPuteran.textContent = '✓ STOK TERSEDIA';
                    if (subSisaPuteran) subSisaPuteran.textContent = 'Siap Digunakan';
                    if (btnAmbilPuteran) btnAmbilPuteran.style.display = 'inline-block';
                    if (pillPuteran) {
                        pillPuteran.className = 'stock-badge-pill pill-green';
                        pillPuteran.textContent = '✓ STOK TERSEDIA';
                    }
                } else {
                    boxSisaPuteran.className = 'box-sisa-gudang stok-habis';
                    if (badgeSisaPuteran) badgeSisaPuteran.textContent = '⚠️ STOK HABIS';
                    if (subSisaPuteran) subSisaPuteran.textContent = 'Stok Kosong di Gudang';
                    if (btnAmbilPuteran) btnAmbilPuteran.style.display = 'none';
                    if (pillPuteran) {
                        pillPuteran.className = 'stock-badge-pill pill-red';
                        pillPuteran.textContent = '⚠️ STOK HABIS';
                    }
                }
            }

            // 3. Re-render tampilan baris material Card 3 yang aktif
            updateRowBatchInfo('plastik_barang_id', 'info_batch_plastik', 'txt_batch_plastik', 'txt_stok_plastik', 'box_sisa_plastik', 'empty_sisa_plastik', 'txt_val_sisa_plastik', 'txt_satuan_sisa_plastik', 'txt_btn_sisa_plastik', 'loc_plastik');
            updateRowBatchInfo('karton_barang_id', 'info_batch_karton_baru', 'txt_batch_karton_baru', 'txt_stok_karton_baru', 'box_sisa_karton_baru', 'empty_sisa_karton_baru', 'txt_val_sisa_karton_baru', 'txt_satuan_sisa_karton_baru', 'txt_btn_sisa_karton_baru', 'loc_karton_baru');
            updateRowBatchInfo('lakban_barang_id', 'info_batch_lakban', 'txt_batch_lakban', 'txt_stok_lakban', 'box_sisa_lakban', 'empty_sisa_lakban', 'txt_val_sisa_lakban', 'txt_satuan_sisa_lakban', 'txt_btn_sisa_lakban', 'loc_lakban');
            updateRowBatchInfo('expired_barang_id', 'info_batch_expired', 'txt_batch_expired', 'txt_stok_expired', 'box_sisa_expired', 'empty_sisa_expired', 'txt_val_sisa_expired', 'txt_satuan_sisa_expired', 'txt_btn_sisa_expired', 'loc_expired');

            const bumbuSel = document.getElementById('bumbu_barang_id');
            if (bumbuSel && bumbuSel.value) onSeasoningBumbuChange(bumbuSel);

            // 4. Update baris kemasan tambahan dinamis jika ada
            document.querySelectorAll('#kemasanTambahanTbody tr').forEach(tr => {
                const sel = tr.querySelector('select');
                if (sel) {
                    Array.from(sel.options).forEach(opt => {
                        if (!opt.value) return;
                        const barangId = parseInt(opt.value);
                        const bInfo = gudangBatches[barangId];
                        const satuanNm = opt.getAttribute('data-satuan-nm') || '';
                        if (bInfo && bInfo.sisa_qty > 0) {
                            opt.setAttribute('data-batch', bInfo.batch_no || '-');
                            opt.setAttribute('data-stok', (bInfo.sisa_qty).toLocaleString('id-ID') + (satuanNm ? ' ' + satuanNm : ''));
                            opt.setAttribute('data-raw-stok', bInfo.sisa_qty);
                            opt.setAttribute('data-gudang-nm', bInfo.gudang_nm || gudangNm);
                        } else {
                            opt.setAttribute('data-batch', '-');
                            opt.setAttribute('data-stok', '0' + (satuanNm ? ' ' + satuanNm : ''));
                            opt.setAttribute('data-raw-stok', 0);
                            opt.setAttribute('data-gudang-nm', gudangNm);
                        }
                    });
                    onKemasanTambahanSelect(sel, tr.id);
                }
            });
        }
        window.onGudangAsalChange = onGudangAsalChange;
        window.syncGudangAsalDisplay = onGudangAsalChange;

        // Shortcut Halus dari Card 3 untuk Mengarahkan Fokus ke Pilihan Gudang Asal di Card 1
        function focusGudangAsal() {
            const sel = document.getElementById('gudang_asal_id');
            if (sel) {
                sel.scrollIntoView({ behavior: 'smooth', block: 'center' });
                sel.focus();
                sel.style.borderColor = '#0284c7';
                sel.style.boxShadow = '0 0 0 4px rgba(2, 132, 199, 0.25)';
                setTimeout(() => {
                    sel.style.borderColor = '';
                    sel.style.boxShadow = '';
                }, 1600);
            }
        }
        window.focusGudangAsal = focusGudangAsal;

        // Helper fungsi UI Web Desktop
        function selectWebShift(shift) {
            const input = document.getElementById('inputShift');
            if (input) input.value = shift;
            const btnA = document.getElementById('btnWebShiftA');
            const btnB = document.getElementById('btnWebShiftB');
            if (shift === 'A') {
                if (btnA) {
                    btnA.classList.add('shift-segmented-active', 'active-a');
                    btnA.classList.remove('shift-segmented-inactive');
                }
                if (btnB) {
                    btnB.classList.add('shift-segmented-inactive');
                    btnB.classList.remove('shift-segmented-active', 'active-b');
                }
            } else {
                if (btnB) {
                    btnB.classList.add('shift-segmented-active', 'active-b');
                    btnB.classList.remove('shift-segmented-inactive');
                }
                if (btnA) {
                    btnA.classList.add('shift-segmented-inactive');
                    btnA.classList.remove('shift-segmented-active', 'active-a');
                }
            }
        }
        window.selectWebShift = selectWebShift;

        function setWebWipSatuan(satuan) {
            document.getElementById('inputWipSatuan').value = satuan;
        }

        function setWebSatuanHasil(satuan) {
            document.getElementById('inputSatuanHasil').value = satuan;
        }

        let currentLakbanUnit = '{{ old('lakban_satuan', $selectedLaporan?->lakban_satuan ?? 'GRAM') }}';

        function onLakbanSatuanSwitch(satuan) {
            satuan = (satuan || 'GRAM').toUpperCase();
            if (satuan === currentLakbanUnit) return;

            const prevUnit = currentLakbanUnit;
            currentLakbanUnit = satuan;

            // Sinkronkan seluruh dropdown satuan lakban
            document.querySelectorAll('.select-lakban-unit').forEach(s => s.value = satuan);
            const hSatuan = document.getElementById('lakban_satuan');
            if (hSatuan) hSatuan.value = satuan;

            // Sinkronkan label satuan rusak / afkir & total
            const unitRusakLbl = document.getElementById('lbl_rusak_lakban_unit');
            if (unitRusakLbl) unitRusakLbl.textContent = (satuan === 'ROLL') ? 'Roll' : 'Gr';

            const unitTotalLbl = document.getElementById('lbl_lakban_satuan_text');
            if (unitTotalLbl) unitTotalLbl.textContent = (satuan === 'ROLL') ? 'Roll' : 'Gr';

            const atasInput = document.getElementById('input_lakban_atas_gr');
            const bawahInput = document.getElementById('input_lakban_bawah_gr');
            const rusakInput = document.getElementById('lakban_rusak_m');

            // Konversi nilai yang sudah ada: 1 Roll = 1.250 Gram
            if (satuan === 'ROLL' && prevUnit === 'GRAM') {
                if (atasInput && atasInput.value) atasInput.value = Math.round((parseFloat(atasInput.value) / 1250) * 1000) / 1000;
                if (bawahInput && bawahInput.value) bawahInput.value = Math.round((parseFloat(bawahInput.value) / 1250) * 1000) / 1000;
                if (rusakInput && rusakInput.value) rusakInput.value = Math.round((parseFloat(rusakInput.value) / 1250) * 1000) / 1000;
            } else if (satuan === 'GRAM' && prevUnit === 'ROLL') {
                if (atasInput && atasInput.value) atasInput.value = Math.round(parseFloat(atasInput.value) * 1250 * 10) / 10;
                if (bawahInput && bawahInput.value) bawahInput.value = Math.round(parseFloat(bawahInput.value) * 1250 * 10) / 10;
                if (rusakInput && rusakInput.value) rusakInput.value = Math.round(parseFloat(rusakInput.value) * 1250 * 10) / 10;
            }

            onSegelAtasBawahChange();
        }
        window.onLakbanSatuanSwitch = onLakbanSatuanSwitch;

        function onSegelAtasBawahChange() {
            const unit = currentLakbanUnit;
            const atasInput = document.getElementById('input_lakban_atas_gr');
            const bawahInput = document.getElementById('input_lakban_bawah_gr');
            const rusakInput = document.getElementById('lakban_rusak_m');
            
            const atasVal = parseFloat(atasInput?.value) || 0;
            const bawahVal = parseFloat(bawahInput?.value) || 0;
            const rusakVal = parseFloat(rusakInput?.value) || 0;

            const total = Math.round((atasVal + bawahVal + rusakVal) * 1000) / 1000;

            let totalGr = 0;
            let estRoll = 0;

            if (unit === 'ROLL') {
                estRoll = total;
                totalGr = Math.round(total * 1250 * 10) / 10;
            } else {
                totalGr = Math.round(total * 10) / 10;
                estRoll = Math.round((totalGr / 1250) * 100) / 100;
            }

            const lblTotal = document.getElementById('lbl_lakban_total_gr');
            if (lblTotal) {
                lblTotal.textContent = (unit === 'ROLL') 
                    ? total.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 3 })
                    : totalGr.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 1 });
            }
            const lblRoll = document.getElementById('lbl_lakban_roll_est');
            if (lblRoll) {
                if (unit === 'ROLL') {
                    lblRoll.textContent = `(~${totalGr.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 })} Gr)`;
                } else {
                    lblRoll.textContent = `(~${estRoll.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} Roll)`;
                }
            }

            // Sync hidden inputs for backend compatibility
            const hSatuan = document.getElementById('lakban_satuan');
            if (hSatuan) hSatuan.value = unit;
            const hQty = document.getElementById('lakban_qty');
            if (hQty) hQty.value = (unit === 'ROLL') ? total : totalGr;
            const hTotalGr = document.getElementById('input_lakban_total_gr');
            if (hTotalGr) hTotalGr.value = totalGr;
            const hAtas = document.getElementById('lakban_atas_gr');
            if (hAtas) hAtas.value = (unit === 'ROLL') ? Math.round(atasVal * 1250 * 10) / 10 : atasVal;
            const hBawah = document.getElementById('lakban_bawah_gr');
            if (hBawah) hBawah.value = (unit === 'ROLL') ? Math.round(bawahVal * 1250 * 10) / 10 : bawahVal;
            const hRoll = document.getElementById('lakban_roll_qty');
            if (hRoll) hRoll.value = estRoll;
            const hJumlahGr = document.getElementById('lakban_jumlah_gr');
            if (hJumlahGr) hJumlahGr.value = totalGr;
        }
        window.onSegelAtasBawahChange = onSegelAtasBawahChange;

        function autoCalcLakbanFromDus() {
            const hasilKarton = parseFloat(document.getElementById('hasil_karton_qty')?.value) || 0;
            const standarCm = parseFloat(document.getElementById('lakban_standar_cm')?.value) || 120;
            // Standar 120 cm per dus = 1.2 m = 15 gr total (7.5 gr atas, 7.5 gr bawah)
            const grPerKarton = (standarCm / 100) * 12.5;
            const halfGr = Math.round((hasilKarton * (grPerKarton / 2)) * 10) / 10;

            const atasInput = document.getElementById('input_lakban_atas_gr');
            const bawahInput = document.getElementById('input_lakban_bawah_gr');

            if (currentLakbanUnit === 'ROLL') {
                const halfRoll = Math.round((halfGr / 1250) * 1000) / 1000;
                if (atasInput) atasInput.value = halfRoll > 0 ? halfRoll : 0;
                if (bawahInput) bawahInput.value = halfRoll > 0 ? halfRoll : 0;
            } else {
                if (atasInput) atasInput.value = halfGr > 0 ? halfGr : 0;
                if (bawahInput) bawahInput.value = halfGr > 0 ? halfGr : 0;
            }

            onSegelAtasBawahChange();
        }
        window.autoCalcLakbanFromDus = autoCalcLakbanFromDus;

        function toggleManualOperatorInput() {
            const opInput = document.getElementById('operator_nama');
            const wrapSelect = document.getElementById('wrapperKaryawanSelect');
            const btnToggle = document.getElementById('btnToggleManualOp');
            if (opInput.style.display === 'none') {
                opInput.style.display = 'block';
                btnToggle.textContent = 'Pilih dari Daftar';
                opInput.focus();
            } else {
                opInput.style.display = 'none';
                btnToggle.textContent = 'Ketik Manual';
            }
        }

        function syncWebKaryawanName(sel) {
            const opt = sel.options[sel.selectedIndex];
            const opInput = document.getElementById('operator_nama');
            if (opt && opt.value && opt.dataset.nama) {
                opInput.value = opt.dataset.nama;
            }
        }

        // FUNGSI MULTI-CURAH WIP & SEASONING PINTAR
        
        // FUNGSI HELPER SEASONING LANGSUNG (TANPA TOGGLER)
        function onSeasoningBumbuChange(sel) {
            const opt = sel ? sel.options[sel.selectedIndex] : null;
            const box = document.getElementById('info_batch_bumbu_seasoning');
            const txtStok = document.getElementById('txt_stok_bumbu_seasoning');
            const txtBatch = document.getElementById('txt_batch_bumbu_seasoning');
            const bumbuQtyInput = document.getElementById('bumbu_qty_kg');

            if (opt && opt.value) {
                if (box) box.style.display = 'block';
                if (txtStok) txtStok.textContent = opt.dataset.stok || '-';
                if (txtBatch) txtBatch.textContent = opt.dataset.batch || '-';
            } else {
                if (box) box.style.display = 'none';
                if (bumbuQtyInput) bumbuQtyInput.value = '';
            }
        }
        window.onSeasoningBumbuChange = onSeasoningBumbuChange;

        function updateBumbuRatioDisplay() {
            // Function retained for compatibility if called by other handlers
        }
        window.updateBumbuRatioDisplay = updateBumbuRatioDisplay;

        let curahIndex = 1;

        function addWipCurahRow(defaultBarangId = '', defaultOutputId = '', defaultQty = '') {
            const tbody = document.getElementById('wipCurahTbody');
            if (!tbody) return;

            let barangOptionsHtml = '<option value="">-- Pilih Varian Keripik Curah --</option>';
            (window.wipBarangOptions || []).forEach(wb => {
                const isSelected = defaultBarangId && parseInt(defaultBarangId) === parseInt(wb.barang_id) ? 'selected' : '';
                barangOptionsHtml += `<option value="${wb.barang_id}" ${isSelected}>${wb.barang_nm}</option>`;
            });

            const rowId = `wip_row_${curahIndex}`;
            const tr = document.createElement('tr');
            tr.id = rowId;
            tr.className = 'wip-curah-row';
            tr.innerHTML = `
                <td class="curah-row-no" style="text-align: center; color: #0284c7; font-weight: 700; vertical-align: middle;">${curahIndex + 1}</td>
                <td style="text-align: center; vertical-align: middle;">
                    <span class="cat-badge-web" style="background: #f0f9ff; color: #0369a1; border-color: #bae6fd;">Curah WIP</span>
                </td>
                <td>
                    <select class="form-control select-wip-barang" required onchange="onCurahBarangChange(this, '${rowId}')">
                        ${barangOptionsHtml}
                    </select>
                </td>
                <td>
                    <select name="wip_items[${curahIndex}][output_id]" class="form-control select-wip-batch" required onchange="onCurahBatchChange(this, '${rowId}')">
                        <option value="">-- Pilih Produk Terlebih Dahulu --</option>
                    </select>
                    <input type="hidden" name="wip_items[${curahIndex}][barang_id]" class="wip-barang-id">
                    <input type="hidden" name="wip_items[${curahIndex}][produksi_id]" class="wip-prod-id">
                    <input type="hidden" name="wip_items[${curahIndex}][batch_no]" class="wip-batch-no">
                    <div class="curah-fifo-box" style="display: none; margin-top: 0.35rem;"></div>
                    <div class="curah-warning-box" style="display: none; font-size: 0.72rem; color: #dc2626; font-weight: 600; margin-top: 0.25rem;"></div>
                </td>
                <td>
                    <div class="box-sisa-gudang box-sisa-curah" style="display: none;">
                        <div class="badge-stok-label">✓ STOK TERSEDIA</div>
                        <div class="val-stok-qty"><span class="txt-val-sisa-curah">0</span> <small>Kg</small></div>
                        <div class="sub-stok-label">Siap Dikemas</div>
                        <button type="button" class="btn-ambil-semua" onclick="takeAllCurahStock(this)">
                            Ambil Semua (<span class="txt-btn-sisa-curah">0</span>)
                        </button>
                    </div>
                    <div class="empty-sisa-curah" style="text-align: center; color: #94a3b8; font-size: 0.75rem;">
                        Pilih curah &amp; batch
                    </div>
                </td>
                <td>
                    <div class="input-group-web">
                        <input type="number" step="any" min="0.01" name="wip_items[${curahIndex}][qty_kg]" class="form-control input-curah-qty" placeholder="0" value="${defaultQty}" required style="font-weight: 700; font-size: 0.95rem; text-align: right;" oninput="recalcCurahTotal()">
                        <span class="input-group-addon" style="font-size: 0.75rem; padding: 0 0.5rem; font-weight: 700; color: #475569;">Kg</span>
                    </div>
                </td>
                <td style="text-align: center; vertical-align: middle;">
                    <button type="button" class="btn-remove-row" onclick="removeCurahRow('${rowId}')" title="Hapus Baris Curah Ini">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            curahIndex++;

            const barangSel = tr.querySelector('.select-wip-barang');
            if (barangSel) {
                if (defaultBarangId) {
                    barangSel.value = defaultBarangId;
                }
                if (barangSel.value) {
                    onCurahBarangChange(barangSel, rowId, defaultOutputId);
                }
            }

            reindexCurahRows();
            recalcCurahTotal();
        }

        function removeCurahRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) row.remove();
            reindexCurahRows();
            recalcCurahTotal();
        }

        function reindexCurahRows() {
            const rows = document.querySelectorAll('#wipCurahTbody .wip-curah-row');
            rows.forEach((r, idx) => {
                const cellNo = r.querySelector('.curah-row-no');
                if (cellNo) cellNo.textContent = idx + 1;
            });
        }

        function takeAllCurahStock(btn) {
            const tr = btn.closest('tr');
            if (!tr) return;
            const span = tr.querySelector('.txt-val-sisa-curah');
            const input = tr.querySelector('.input-curah-qty');
            if (!span || !input) return;
            const cleanNum = parseFloat(span.textContent.replace(/\./g, '').replace(',', '.')) || 0;
            input.value = cleanNum;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
        window.takeAllCurahStock = takeAllCurahStock;

        function onCurahBarangChange(barangSel, rowId, defaultOutputId = null) {
            const tr = document.getElementById(rowId);
            if (!tr) return;
            const batchSel = tr.querySelector('.select-wip-batch');
            const barangIdInp = tr.querySelector('.wip-barang-id');
            const barangId = barangSel.value ? parseInt(barangSel.value) : null;
            
            if (barangIdInp) barangIdInp.value = barangId || '';
            if (!batchSel) return;

            if (!barangId) {
                batchSel.innerHTML = '<option value="">-- Pilih Produk Terlebih Dahulu --</option>';
                onCurahBatchChange(batchSel, rowId);
                return;
            }

            const batches = (window.wipListOptions || []).filter(w => parseInt(w.barang_id) === barangId);
            if (batches.length === 0) {
                batchSel.innerHTML = '<option value="">Stok batch curah kosong</option>';
                onCurahBatchChange(batchSel, rowId);
                return;
            }

            let html = '';
            batches.forEach((w, idx) => {
                const isSelected = defaultOutputId 
                    ? (parseInt(defaultOutputId) === parseInt(w.output_id) ? 'selected' : '')
                    : (idx === 0 ? 'selected' : '');
                const fifoPrefix = w.is_fifo ? '⭐ [FIFO Prioritas] ' : '';
                html += `<option value="${w.output_id}" 
                            data-produksi-id="${w.produksi_id}" 
                            data-barang-id="${w.barang_id}" 
                            data-batch="${w.batch}" 
                            data-barang-nama="${w.barang_nm}" 
                            data-qty="${w.qty}" 
                            data-hpp="${w.hpp}" 
                            data-is-berbumbu="${w.is_berbumbu}" 
                            data-is-fifo="${w.is_fifo}" 
                            data-oldest-output-id="${w.oldest_output_id}" 
                            data-oldest-batch="${w.oldest_batch}" 
                            data-oldest-qty="${w.oldest_qty}" 
                            ${isSelected}>
                            ${fifoPrefix}Batch: ${w.batch} • [${w.prod_no}] (Sisa: ${w.qty.toLocaleString('id-ID')} Kg)
                        </option>`;
            });
            batchSel.innerHTML = html;
            onCurahBatchChange(batchSel, rowId);
        }
        window.onCurahBarangChange = onCurahBarangChange;

        function onCurahBatchChange(batchSel, rowId) {
            const tr = document.getElementById(rowId);
            if (!tr) return;

            const opt = batchSel.options[batchSel.selectedIndex];
            const barangIdInp = tr.querySelector('.wip-barang-id');
            const prodIdInp = tr.querySelector('.wip-prod-id');
            const batchNoInp = tr.querySelector('.wip-batch-no');
            const boxSisa = tr.querySelector('.box-sisa-curah');
            const emptySisa = tr.querySelector('.empty-sisa-curah');
            const valSisa = tr.querySelector('.txt-val-sisa-curah');
            const btnSisa = tr.querySelector('.txt-btn-sisa-curah');

            // Cek Peringatan FIFO jika memilih batch yang bukan tertua untuk barang ini
            const fifoBox = tr.querySelector('.curah-fifo-box');
            if (fifoBox) {
                if (opt && opt.value && opt.dataset.isFifo === '0' && opt.dataset.oldestOutputId) {
                    fifoBox.innerHTML = `
                        <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 5px 8px; font-size: 0.72rem; color: #92400e; display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                            <span>⚠️ <strong>Perhatian FIFO:</strong> Tersedia Batch <strong>${opt.dataset.oldestBatch}</strong> yang lebih lama (Sisa: ${opt.dataset.oldestQty} Kg).</span>
                            <button type="button" onclick="selectFifoBatch('${rowId}', ${opt.dataset.oldestOutputId})" style="background: #059669; color: #ffffff; border: none; font-size: 0.675rem; font-weight: 700; padding: 3px 8px; border-radius: 4px; cursor: pointer; white-space: nowrap;">
                                Gunakan Batch Tertua
                            </button>
                        </div>
                    `;
                    fifoBox.style.display = 'block';
                } else {
                    fifoBox.innerHTML = '';
                    fifoBox.style.display = 'none';
                }
            }

            if (opt && opt.value) {
                const prodId = opt.dataset.produksiId || '';
                const barangId = opt.dataset.barangId || '';
                const batch = opt.dataset.batch || '';
                const qtyStok = parseFloat(opt.dataset.qty) || 0;
                const formattedStok = qtyStok.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

                if (barangIdInp) barangIdInp.value = barangId;
                if (prodIdInp) prodIdInp.value = prodId;
                if (batchNoInp) batchNoInp.value = batch;

                // Jika row 0, sinkronkan juga hidden utama untuk backwards compatibility
                if (rowId === 'wip_row_0') {
                    const hProd = document.getElementById('hidden_produksi_id');
                    const hWip = document.getElementById('hidden_wip_barang_id');
                    const hBatch = document.getElementById('hidden_wip_batch_no');
                    if (hProd) hProd.value = prodId;
                    if (hWip) hWip.value = barangId;
                    if (hBatch) hBatch.value = batch;
                }

                // Tampilkan sisa di gudang
                if (boxSisa && emptySisa) {
                    emptySisa.style.display = 'none';
                    boxSisa.style.display = 'block';
                    if (valSisa) valSisa.textContent = formattedStok;
                    if (btnSisa) btnSisa.textContent = formattedStok;

                    const badgeLabel = boxSisa.querySelector('.badge-stok-label');
                    const subLabel = boxSisa.querySelector('.sub-stok-label');
                    const btnAmbil = boxSisa.querySelector('.btn-ambil-semua');

                    if (qtyStok > 0) {
                        boxSisa.className = 'box-sisa-gudang box-sisa-curah stok-ada';
                        if (badgeLabel) badgeLabel.textContent = '✓ STOK TERSEDIA';
                        if (subLabel) subLabel.textContent = 'Siap Dikemas';
                        if (btnAmbil) btnAmbil.style.display = 'inline-block';
                    } else {
                        boxSisa.className = 'box-sisa-gudang box-sisa-curah stok-habis';
                        if (badgeLabel) badgeLabel.textContent = '⚠️ STOK HABIS';
                        if (subLabel) subLabel.textContent = 'Stok Kosong di Gudang';
                        if (btnAmbil) btnAmbil.style.display = 'none';
                    }
                }

                checkSingleRowStock(tr);
            } else {
                if (boxSisa && emptySisa) {
                    boxSisa.style.display = 'none';
                    emptySisa.style.display = 'block';
                }
                checkSingleRowStock(tr);
            }
            recalcCurahTotal();
        }
        window.onCurahBatchChange = onCurahBatchChange;

        function selectFifoBatch(rowId, oldestOutputId) {
            const tr = document.getElementById(rowId);
            if (!tr) return;
            const sel = tr.querySelector('.select-wip-batch');
            if (sel) {
                sel.value = oldestOutputId;
                onCurahBatchChange(sel, rowId);
            }
        }
        window.selectFifoBatch = selectFifoBatch;

        function checkSingleRowStock(tr) {
            const sel = tr.querySelector('.select-wip-batch') || tr.querySelector('.select-wip-curah');
            const inp = tr.querySelector('.input-curah-qty');
            const warnBox = tr.querySelector('.curah-warning-box');
            if (!sel || !inp || !warnBox) return;

            const opt = sel.options[sel.selectedIndex];
            if (!opt || !opt.value) {
                warnBox.style.display = 'none';
                inp.style.borderColor = '';
                inp.style.backgroundColor = '';
                return;
            }

            const stokQty = parseFloat(opt.dataset.qty) || 0;
            const inputVal = parseFloat(inp.value) || 0;

            if (inputVal > stokQty && stokQty > 0) {
                const selisih = (inputVal - stokQty).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
                warnBox.innerHTML = `Melebihi sisa stok (${stokQty.toLocaleString('id-ID')} Kg). Selisih +${selisih} Kg.`;
                warnBox.style.display = 'block';
                inp.style.borderColor = '#dc2626';
                inp.style.backgroundColor = '#fef2f2';
            } else {
                warnBox.style.display = 'none';
                inp.style.borderColor = '';
                inp.style.backgroundColor = '';
            }
        }

        function recalcCurahTotal() {
            let totalKg = 0;
            document.querySelectorAll('.input-curah-qty').forEach(inp => {
                const val = parseFloat(inp.value) || 0;
                totalKg += val;
                const tr = inp.closest('tr');
                if (tr) checkSingleRowStock(tr);
            });

            const lblTotal = document.getElementById('lbl_total_curah_wip');
            if (lblTotal) {
                lblTotal.textContent = totalKg.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            }

            const totalField = document.getElementById('total_curah_wip_kg');
            if (totalField) {
                totalField.value = totalKg;
            }

            const hiddenWipQty = document.getElementById('hidden_wip_qty_kg');
            if (hiddenWipQty) hiddenWipQty.value = totalKg;

            updateBumbuRatioDisplay();
        }

        // Penambahan baris dinamis di dalam tabel
        let kemasanTambahanIndex = 0;
        function addKemasanTambahanRow(selectedId = '', defaultQty = '') {
            const tbody = document.getElementById('kemasanTambahanTbody');
            if (!tbody) return;

            const optionsData = window.kemasanLainOptions || [];
            let optionsHtml = '<option value="">-- Pilih Kemasan Tambahan --</option>';

            optionsData.forEach(item => {
                const isSelected = selectedId && parseInt(selectedId) === parseInt(item.barang_id) ? 'selected' : '';
                const satuan = item.satuan_nm ? ` (${item.satuan_nm})` : '';
                optionsHtml += `<option value="${item.barang_id}" data-satuan="${item.satuan_nm || 'PCS'}" data-batch="${item.batch_no || '-'}" data-stok="${item.sisa_qty || '0'} ${item.satuan_nm || 'Pcs'}" data-raw-stok="${item.raw_sisa_qty || 0}" data-gudang-nm="${item.gudang_nm || ''}" ${isSelected}>${item.barang_nm}${satuan}</option>`;
            });

            const rowId = `kemasan_row_${kemasanTambahanIndex}`;
            const tr = document.createElement('tr');
            tr.id = rowId;

            tr.innerHTML = `
                <td style="text-align: center; color: #0284c7; font-weight: 700;">+</td>
                <td>
                    <span class="cat-badge-web cat-lain-web">Kemasan Lain</span>
                </td>
                <td>
                    <select name="kemasan_tambahan[${kemasanTambahanIndex}][barang_id]" class="form-control" onchange="onKemasanTambahanSelect(this, '${rowId}')">
                        ${optionsHtml}
                    </select>
                    <div class="info-batch-tambahan" style="font-size: 0.7rem; color: #64748b; margin-top: 3px; display: none;">
                        Batch FIFO: <strong style="color: #0284c7;" class="txt-batch-tambahan">-</strong> &bull; Sisa Stok: <strong class="txt-stok-tambahan">-</strong>
                    </div>
                </td>
                <td>
                    <div class="box-sisa-gudang box-sisa-tambahan" style="display: none;">
                        <div class="badge-stok-label">✓ STOK TERSEDIA</div>
                        <div class="val-stok-qty"><span class="txt-val-sisa-tambahan">0</span> <small class="txt-satuan-sisa-tambahan">Pcs</small></div>
                        <div class="sub-stok-label">Siap Digunakan</div>
                        <button type="button" class="btn-ambil-semua btn-ambil-tambahan" onclick="takeAllStockDynamic(this)">
                            Ambil Semua (<span class="txt-btn-sisa-tambahan">0</span>)
                        </button>
                        <span class="gudang-loc-pill loc-tambahan" style="display: none;"></span>
                    </div>
                    <div class="empty-sisa-tambahan" style="text-align: center; color: #94a3b8; font-size: 0.75rem;">
                        Pilih kemasan
                    </div>
                </td>
                <td>
                    <div class="input-group-web">
                        <input type="number" step="any" min="0" name="kemasan_tambahan[${kemasanTambahanIndex}][qty]" class="form-control input-qty-tambahan" placeholder="Qty" value="${defaultQty}" style="font-weight: 700;">
                        <select name="kemasan_tambahan[${kemasanTambahanIndex}][satuan]" class="form-control unit-select" style="max-width: 85px; font-weight: 700; background: #ffffff;">
                            <option value="PCS">Pcs</option>
                            <option value="PACK">Pack</option>
                            <option value="ROLL">Roll</option>
                            <option value="KG">Kg</option>
                            <option value="METER">Meter</option>
                            <option value="LEMBAR">Lbr</option>
                            <option value="IKAT">Ikat</option>
                        </select>
                    </div>
                </td>
                <td style="text-align: center; color: #94a3b8; font-size: 0.75rem;">-</td>
                <td>
                    <span style="font-size: 0.725rem; color: #64748b;">Kemasan tambahan / penolong.</span>
                </td>
                <td style="text-align: center;">
                    <button type="button" class="btn-remove-row" onclick="removeKemasanTambahanRow('${rowId}')" title="Hapus Baris">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
            kemasanTambahanIndex++;

            if (selectedId) {
                const sel = tr.querySelector('select');
                if (sel) onKemasanTambahanSelect(sel, rowId);
            }
        }

        function removeKemasanTambahanRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) row.remove();
        }

        function onKemasanTambahanSelect(selectElem, rowId) {
            const row = document.getElementById(rowId);
            if (!row) return;

            const selectedOpt = selectElem.options[selectElem.selectedIndex];
            const unitSelect = row.querySelector('.unit-select');
            const infoBatch = row.querySelector('.info-batch-tambahan');
            const txtBatch = row.querySelector('.txt-batch-tambahan');
            const txtStok = row.querySelector('.txt-stok-tambahan');

            const boxSisa = row.querySelector('.box-sisa-tambahan');
            const emptySisa = row.querySelector('.empty-sisa-tambahan');
            const valSisa = row.querySelector('.txt-val-sisa-tambahan');
            const satuanSisa = row.querySelector('.txt-satuan-sisa-tambahan');
            const btnSisa = row.querySelector('.txt-btn-sisa-tambahan');
            const locSisa = row.querySelector('.loc-tambahan');
            const btnAmbil = row.querySelector('.btn-ambil-tambahan');

            if (selectedOpt && selectedOpt.value) {
                const satuan = (selectedOpt.getAttribute('data-satuan') || 'PCS').toUpperCase();
                const batch = selectedOpt.getAttribute('data-batch') || '-';
                const stok = selectedOpt.getAttribute('data-stok') || '0';
                const rawStok = parseFloat(selectedOpt.getAttribute('data-raw-stok')) || 0;
                const gudangNm = selectedOpt.getAttribute('data-gudang-nm') || '';

                if (unitSelect) {
                    for (let i = 0; i < unitSelect.options.length; i++) {
                        if (unitSelect.options[i].value === satuan || unitSelect.options[i].text.toUpperCase() === satuan) {
                            unitSelect.selectedIndex = i;
                            break;
                        }
                    }
                }
                if (infoBatch && txtBatch && txtStok) {
                    txtBatch.textContent = batch;
                    txtStok.textContent = stok;
                    infoBatch.style.display = 'block';
                }

                if (boxSisa && emptySisa) {
                    emptySisa.style.display = 'none';
                    boxSisa.style.display = 'block';
                    const formatted = rawStok.toLocaleString('id-ID');
                    if (valSisa) valSisa.textContent = formatted;
                    if (satuanSisa) satuanSisa.textContent = satuan;
                    if (btnSisa) btnSisa.textContent = formatted;

                    if (locSisa) {
                        if (gudangNm) {
                            locSisa.textContent = gudangNm;
                            locSisa.style.display = 'block';
                        } else {
                            locSisa.style.display = 'none';
                        }
                    }

                    const badge = boxSisa.querySelector('.badge-stok-label');
                    const sub = boxSisa.querySelector('.sub-stok-label');

                    if (rawStok > 0) {
                        boxSisa.className = 'box-sisa-gudang box-sisa-tambahan stok-ada';
                        if (badge) badge.textContent = '✓ STOK TERSEDIA';
                        if (sub) sub.textContent = 'Siap Digunakan';
                        if (btnAmbil) btnAmbil.style.display = 'inline-block';
                    } else {
                        boxSisa.className = 'box-sisa-gudang box-sisa-tambahan stok-habis';
                        if (badge) badge.textContent = '⚠️ STOK HABIS';
                        if (sub) sub.textContent = 'Stok Kosong di Gudang';
                        if (btnAmbil) btnAmbil.style.display = 'none';
                    }
                }
            } else {
                if (infoBatch) infoBatch.style.display = 'none';
                if (boxSisa && emptySisa) {
                    boxSisa.style.display = 'none';
                    emptySisa.style.display = 'block';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            @if(old('kemasan_tambahan') && is_array(old('kemasan_tambahan')))
                @foreach(old('kemasan_tambahan') as $item)
                    addKemasanTambahanRow('{{ $item['barang_id'] ?? '' }}', '{{ $item['qty'] ?? '' }}');
                @endforeach
            @endif

            // Inisialisasi awal
            const selKaryawan = document.getElementById('karyawan_id');
            if (selKaryawan && selKaryawan.value) {
                syncWebKaryawanName(selKaryawan);
            }

            // Inisialisasi indikator gudang asal aktif Card 1 & Card 3 serta filter stok material Card 3
            onGudangAsalChange();

            // Inisialisasi baris pertama curah WIP (row 0) jika ada prefill/old data
            const selBarang0 = document.getElementById('wip_barang_id_0');
            if (selBarang0) {
                const initOutputId = '{{ old('output_id', $selectedLaporan?->output_id ?? '') }}';
                if (selBarang0.value) {
                    onCurahBarangChange(selBarang0, 'wip_row_0', initOutputId);
                }
            }

            // Hitung akumulasi awal curah
            recalcCurahTotal();

            // Inisialisasi kalkulasi lakban segel atas & bawah
            if (currentLakbanUnit === 'ROLL') {
                const unitRusakLbl = document.getElementById('lbl_rusak_lakban_unit');
                if (unitRusakLbl) unitRusakLbl.textContent = 'Roll';
                const unitTotalLbl = document.getElementById('lbl_lakban_satuan_text');
                if (unitTotalLbl) unitTotalLbl.textContent = 'Roll';
            }
            onSegelAtasBawahChange();
        });
    </script>
    <script src="{{ asset('js/produksi/packaging/packaging-create.js') }}?v={{ filemtime(public_path('js/produksi/packaging/packaging-create.js')) }}"></script>
@endpush
