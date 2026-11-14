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
        <input type="hidden" id="lakban_qty" name="lakban_qty" value="{{ old('lakban_qty', $selectedLaporan?->lakban_qty ?? 0) }}">
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

                    {{-- 3. Gudang Tujuan Simpan FG --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="gudang_id">Gudang Tujuan Simpan FG <span class="req">*</span></label>
                        <select id="gudang_id" name="gudang_id" required class="form-control">
                            <option value="">-- Pilih Lokasi Gudang FG --</option>
                            @foreach($gudangList as $g)
                                <option value="{{ $g->gudang_id }}" {{ old('gudang_id') == $g->gudang_id ? 'selected' : '' }}>
                                    {{ $g->gudang_nm }} ({{ $g->tipe_gudang_cd ?? 'Gudang' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

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

                    {{-- 5. Regu / Personil Kemas (Sinkron otomatis dari Modul Karyawan) --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                            <label class="form-label" for="jumlah_personil" style="margin-bottom: 0;">Personil Kemas</label>
                            <button type="button" class="btn-sync-tk-pack" onclick="syncLaborPackingFromKaryawan(true)" style="background: none; border: none; font-size: 0.725rem; color: #059669; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.25rem; padding: 0;" title="Sinkronkan presensi kelompok Langsung Packing">
                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Sync Presensi</span>
                            </button>
                        </div>
                        <div style="position: relative; display: flex; align-items: center;">
                            <input type="number" id="jumlah_personil" name="jumlah_personil" class="form-control" min="0" placeholder="0" value="{{ old('jumlah_personil', $selectedLaporan?->jumlah_personil ?? ($initialLabor['tk_jumlah_org'] ?? 0)) }}" style="padding-right: 3.5rem;">
                            <span style="position: absolute; right: 0.75rem; font-size: 0.75rem; font-weight: 700; color: #64748b; pointer-events: none;">Orang</span>
                        </div>
                        <input type="hidden" id="total_biaya_tenaga_kerja" name="total_biaya_tenaga_kerja" value="{{ old('total_biaya_tenaga_kerja', $initialLabor['tk_total_nilai'] ?? 0) }}">
                        <small id="helpLaborPacking" class="helper-text" style="font-size: 0.725rem; color: #059669; font-weight: 600; display: block; margin-top: 0.25rem;">
                            @if (!empty($initialLabor['is_synced']) && ($initialLabor['tk_jumlah_org'] > 0))
                                ✓ Tersinkron dari Modul Absensi: <strong>{{ $initialLabor['tk_jumlah_org'] }} Orang</strong>
                            @else
                                Otomatis sinkron dari presensi kelompok Langsung Packing.
                            @endif
                        </small>
                    </div>

                    {{-- 6. Mesin Aktif --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="jumlah_mesin">Mesin Aktif (Unit)</label>
                        <input type="number" id="jumlah_mesin" name="jumlah_mesin" class="form-control" min="1" placeholder="0" value="{{ old('jumlah_mesin', $selectedLaporan?->jumlah_mesin) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ═════════════════════════════════════════════════════════════════ --}}
        {{-- KARTU 2: TABEL KONVERSI PENGEMASAN (CURAH WIP KE BARANG JADI FG)   --}}
        {{-- ═════════════════════════════════════════════════════════════════ --}}
        <div class="card-web-form">
            <div class="card-web-header">
                <h2 class="card-web-title">
                    <span class="card-web-num">2</span>
                    <span>Tabel Konversi Pengemasan (Curah WIP &rarr; Barang Jadi FG)</span>
                </h2>
                <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Matriks Pengambilan Bahan Curah &amp; Target Hasil Kemas Jadi</span>
            </div>
            <div class="card-web-body" style="padding: 0;">
                <div style="overflow-x: auto;">
                    <table class="table-web-form">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th style="min-width: 260px;">Bahan Keripik Curah (WIP Dapur)</th>
                                <th style="width: 175px;">Qty Diambil</th>
                                <th style="min-width: 250px;">Target Produk Jadi (FG)</th>
                                <th style="width: 215px; white-space: nowrap;">Hasil Kemas Utama</th>
                                <th style="width: 130px;">Sisa Eceran</th>
                                <th style="width: 180px;">Batch &amp; Expired FG</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="text-align: center; font-weight: 700; color: #64748b; vertical-align: middle;">
                                    1
                                </td>
                                <td>
                                    <select id="output_id" name="output_id" class="form-control" required onchange="onWebWipChange(this)">
                                        <option value="">-- Pilih Varian Keripik Curah --</option>
                                        @foreach ($wipList as $wip)
                                            @php
                                                $prodNo = $wip->produksi?->produksi_no ?? 'PROD';
                                                $batchStr = $wip->batch_no ?: ($wip->produksi?->batch_wip_no ?: ($wip->produksi?->produksi_tgl ? $wip->produksi->produksi_tgl->format('d m Y') : '-'));
                                                $barangNm = $wip->barang?->barang_nm ?? 'Keripik Curah';
                                                $qtyKg = (float) $wip->qty_kg;
                                                $hppKg = (float) ($wip->hpp_satuan > 0 ? $wip->hpp_satuan : ($wip->produksi?->hpp_per_kg ?? $wip->barang?->harga_beli_standar ?? 0));
                                                $isBerbumbu = (float) ($wip->produksi?->bumbu_qty ?? 0) > 0 || str_contains(strtoupper($barangNm), 'BUMBU') || str_contains(strtoupper($barangNm), 'BALADO');
                                            @endphp
                                            <option value="{{ $wip->output_id }}"
                                                    data-produksi-id="{{ $wip->produksi_id }}"
                                                    data-barang-id="{{ $wip->barang_id }}"
                                                    data-batch="{{ $batchStr }}"
                                                    data-barang-nama="{{ $barangNm }}"
                                                    data-qty="{{ $qtyKg }}"
                                                    data-hpp="{{ $hppKg }}"
                                                    data-is-berbumbu="{{ $isBerbumbu ? '1' : '0' }}"
                                                    {{ (old('output_id', $selectedLaporan?->output_id) == $wip->output_id) ? 'selected' : '' }}>
                                                [{{ $prodNo }}] {{ $barangNm }} &bull; Batch: {{ $batchStr }} (Stok: {{ number_format($qtyKg, 0, ',', '.') }} Kg)
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="wipInfoBox" style="display: none; font-size: 0.725rem; color: #0284c7; font-weight: 600; margin-top: 0.35rem;">
                                        <span id="wipInfoText"></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group-web">
                                        <input type="number" step="any" min="0.01" id="wip_qty_kg" name="wip_qty_kg" class="form-control" placeholder="0" value="{{ old('wip_qty_kg', $selectedLaporan?->wip_qty_kg) }}" required style="font-weight: 700; font-size: 0.95rem;">
                                        <select id="selectWipSatuan" class="form-control" style="max-width: 75px; font-weight: 700; background: #ffffff;" onchange="setWebWipSatuan(this.value)">
                                            <option value="KG" {{ old('wip_satuan', $selectedLaporan?->wip_satuan ?? 'KG') == 'KG' ? 'selected' : '' }}>Kg</option>
                                            <option value="KARTON" {{ old('wip_satuan', $selectedLaporan?->wip_satuan) == 'KARTON' ? 'selected' : '' }}>Dus</option>
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <select id="barang_jadi_id" name="barang_jadi_id" class="form-control" required style="background: #ffffff;">
                                        <option value="">-- Pilih Produk Jadi (FG) --</option>
                                        @foreach ($barangJadiList as $b)
                                            <option value="{{ $b->barang_id }}" {{ (old('barang_jadi_id', $selectedLaporan?->barang_jadi_id) == $b->barang_id) ? 'selected' : '' }}>
                                                {{ $b->barang_nm }} ({{ $b->barang_cd }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div class="input-group-web">
                                        <input type="number" step="any" min="0.0001" id="hasil_karton_qty" name="hasil_karton_qty" class="form-control" placeholder="0" value="{{ old('hasil_karton_qty', $selectedLaporan?->hasil_karton_qty) }}" required style="font-weight: 700; font-size: 0.95rem; color: #059669; background: #ffffff;">
                                        <select id="selectSatuanHasil" class="form-control" style="width: 105px; max-width: 110px; font-weight: 700; background: #ffffff; padding: 0.45rem 0.5rem;" onchange="setWebSatuanHasil(this.value)">
                                            <option value="KARTON" {{ old('satuan_hasil', $selectedLaporan?->satuan_hasil ?? 'KARTON') == 'KARTON' ? 'selected' : '' }}>Karton</option>
                                            <option value="BAL" {{ old('satuan_hasil', $selectedLaporan?->satuan_hasil) == 'BAL' ? 'selected' : '' }}>Bal</option>
                                            <option value="PACK" {{ old('satuan_hasil', $selectedLaporan?->satuan_hasil) == 'PACK' ? 'selected' : '' }}>Pack</option>
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group-web">
                                        <input type="number" step="any" min="0" id="hasil_pack_qty" name="hasil_pack_qty" class="form-control" placeholder="0" value="{{ old('hasil_pack_qty', $selectedLaporan?->hasil_pack_qty) }}" style="background: #ffffff;">
                                        <span class="input-group-addon" style="font-size: 0.75rem; padding: 0 0.4rem;">Pack</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <input type="text" id="batch_no" name="batch_no" class="form-control" placeholder="No. Batch (15 10 2026)" value="{{ old('batch_no', $selectedLaporan?->wip_batch_no) }}" style="background: #ffffff; font-size: 0.8rem; padding: 0.35rem 0.5rem;" title="Nomor Batch FG">
                                        <input type="date" id="expired_tgl" name="expired_tgl" class="form-control" value="{{ old('expired_tgl', date('Y-m-d', strtotime('+6 months'))) }}" style="background: #ffffff; font-size: 0.8rem; padding: 0.35rem 0.5rem;" title="Tanggal Kadaluarsa FG">
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ═════════════════════════════════════════════════════════════════ --}}
        {{-- KARTU 3: TABEL PEMAKAIAN BAHAN KEMASAN & PENOLONG (RINGKAS)       --}}
        {{-- ═════════════════════════════════════════════════════════════════ --}}
        <div class="card-web-form">
            <div class="card-web-header">
                <h2 class="card-web-title">
                    <span class="card-web-num">3</span>
                    <span>Tabel Pemakaian Bahan Kemasan, Dus, Lakban &amp; Afkir</span>
                </h2>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
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
                                <th style="width: 150px; white-space: nowrap;">Kategori Bahan</th>
                                <th>Nama Barang / Material Kemasan</th>
                                <th style="width: 180px;">Kuantitas Terpakai</th>
                                <th style="width: 150px;">Rusak / Afkir</th>
                                <th style="width: 280px;">Keterangan &amp; Kontrol Cepat</th>
                                <th style="width: 45px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- BARIS 1: BUMBU TABUR --}}
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">1</td>
                                <td>
                                    <span class="cat-badge-web cat-bumbu-web">Bumbu Tabur</span>
                                </td>
                                <td>
                                    <select id="bumbu_barang_id" name="bumbu_barang_id" class="form-control" onchange="updateRowBatchInfo('bumbu_barang_id', 'info_batch_bumbu', 'txt_batch_bumbu', 'txt_stok_bumbu')">
                                        <option value="">-- Tanpa Bumbu (Original / Tidak Pakai) --</option>
                                        @foreach ($bumbuList as $bm)
                                            <option value="{{ $bm->barang_id }}" 
                                                data-batch="{{ $batchInfoMap[$bm->barang_id]['batch_no'] ?? '-' }}" 
                                                data-stok="{{ number_format($batchInfoMap[$bm->barang_id]['sisa_qty'] ?? 0, 0, ',', '.') }} Kg"
                                                {{ old('bumbu_barang_id', $selectedLaporan?->bumbu_barang_id) == $bm->barang_id ? 'selected' : '' }}>
                                                {{ $bm->barang_nm }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="info_batch_bumbu" style="font-size: 0.7rem; color: #64748b; margin-top: 3px; display: none;">
                                        Batch FIFO: <strong style="color: #0284c7;" id="txt_batch_bumbu">-</strong> &bull; Sisa Stok: <strong id="txt_stok_bumbu">-</strong>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group-web">
                                        <input type="number" step="any" min="0" id="bumbu_qty_kg" name="bumbu_qty_kg" class="form-control" placeholder="0" value="{{ old('bumbu_qty_kg', $selectedLaporan?->bumbu_qty_kg) }}" style="font-weight: 700;">
                                        <select id="bumbu_satuan" name="bumbu_satuan" class="form-control" style="max-width: 85px; font-weight: 700; background: #ffffff;">
                                            <option value="KG" {{ old('bumbu_satuan', 'KG') == 'KG' ? 'selected' : '' }}>Kg</option>
                                            <option value="GRAM" {{ old('bumbu_satuan') == 'GRAM' ? 'selected' : '' }}>Gram</option>
                                        </select>
                                    </div>
                                </td>
                                <td style="color: #94a3b8; font-size: 0.75rem; text-align: center;">
                                    -
                                </td>
                                <td>
                                    <div id="alertBumbuDapur" style="display: none; font-size: 0.725rem; color: #15803d; background: #f0fdf4; padding: 4px 8px; border-radius: 4px; border: 1px solid #86efac;">
                                        Curah sudah dibumbui di dapur.
                                        <button type="button" onclick="toggleManualBumbuOverride()" style="background: none; border: none; padding: 0; color: #0284c7; font-size: 0.725rem; font-weight: 600; text-decoration: underline; cursor: pointer;">
                                            Tambah manual?
                                        </button>
                                    </div>
                                    <span id="txtBumbuNormal" style="font-size: 0.75rem; color: #64748b;">Standar bumbu tabur perasa.</span>
                                </td>
                                <td style="text-align: center; color: #cbd5e1;">&bull;</td>
                            </tr>

                            {{-- BARIS 2: PLASTIK KEMASAN UTAMA --}}
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">2</td>
                                <td>
                                    <span class="cat-badge-web cat-plastik-web">Plastik Kemas</span>
                                </td>
                                <td>
                                    <select id="plastik_barang_id" name="plastik_barang_id" class="form-control" onchange="updateRowBatchInfo('plastik_barang_id', 'info_batch_plastik', 'txt_batch_plastik', 'txt_stok_plastik')">
                                        <option value="">-- Pilih Plastik Kemasan Utama --</option>
                                        @foreach ($plastikList as $pls)
                                            <option value="{{ $pls->barang_id }}" 
                                                data-batch="{{ $batchInfoMap[$pls->barang_id]['batch_no'] ?? '-' }}" 
                                                data-stok="{{ number_format($batchInfoMap[$pls->barang_id]['sisa_qty'] ?? 0, 0, ',', '.') }} {{ $pls->satuanDasar?->satuan_nm ?? 'Pcs' }}"
                                                {{ old('plastik_barang_id', $selectedLaporan?->plastik_barang_id) == $pls->barang_id ? 'selected' : '' }}>
                                                {{ $pls->barang_nm }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="info_batch_plastik" style="font-size: 0.7rem; color: #64748b; margin-top: 3px; display: none;">
                                        Batch FIFO: <strong style="color: #0284c7;" id="txt_batch_plastik">-</strong> &bull; Sisa Stok: <strong id="txt_stok_plastik">-</strong>
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

                            {{-- BARIS 3: DUS KARTON PUTERAN --}}
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">3</td>
                                <td>
                                    <span class="cat-badge-web cat-karton-web">Dus Puteran</span>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #065f46; font-size: 0.85rem;">
                                        Karton Polos IFM (Puteran)
                                    </div>
                                    <div style="font-size: 0.7rem; color: #64748b; margin-top: 2px;">
                                        Batch FIFO: <strong style="color: #0284c7;">{{ $batchInfoMap[$kartonPuteran?->barang_id]['batch_no'] ?? 'PUT-FIFO' }}</strong> &bull; Sisa Stok: <strong style="color: #059669;">{{ number_format($stokKartonPuteran, 0, ',', '.') }} Lbr</strong>
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

                            {{-- BARIS 4: DUS KARTON BARU --}}
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">4</td>
                                <td>
                                    <span class="cat-badge-web cat-karton-web">Dus Baru</span>
                                </td>
                                <td>
                                    <select id="karton_barang_id" name="karton_barang_id" class="form-control" onchange="updateRowBatchInfo('karton_barang_id', 'info_batch_karton_baru', 'txt_batch_karton_baru', 'txt_stok_karton_baru')">
                                        <option value="">-- Pilih Karton Dus Baru (Opsional) --</option>
                                        @foreach ($kartonList as $ktn)
                                            <option value="{{ $ktn->barang_id }}" 
                                                data-batch="{{ $batchInfoMap[$ktn->barang_id]['batch_no'] ?? '-' }}" 
                                                data-stok="{{ number_format($batchInfoMap[$ktn->barang_id]['sisa_qty'] ?? 0, 0, ',', '.') }} Lbr"
                                                {{ old('karton_barang_id', $selectedLaporan?->karton_barang_id) == $ktn->barang_id ? 'selected' : '' }}>
                                                {{ $ktn->barang_nm }} ({{ $ktn->barang_cd }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="info_batch_karton_baru" style="font-size: 0.7rem; color: #64748b; margin-top: 2px; display: none;">
                                        Batch FIFO: <strong style="color: #0284c7;" id="txt_batch_karton_baru">{{ $batchInfoMap[$kartonBaru?->barang_id]['batch_no'] ?? '-' }}</strong> &bull; Sisa Stok: <strong style="color: #0369a1;" id="txt_stok_karton_baru">{{ number_format($stokKartonBaru, 0, ',', '.') }} Lbr</strong>
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

                            {{-- BARIS 5: LAKBAN SEGEL DUS --}}
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">5</td>
                                <td>
                                    <span class="cat-badge-web cat-lakban-web">Lakban Segel</span>
                                </td>
                                <td>
                                    <select id="lakban_barang_id" name="lakban_barang_id" class="form-control" onchange="updateRowBatchInfo('lakban_barang_id', 'info_batch_lakban', 'txt_batch_lakban', 'txt_stok_lakban')">
                                        <option value="">-- Pilih Lakban Segel --</option>
                                        @foreach ($lakbanList as $lkb)
                                            <option value="{{ $lkb->barang_id }}" 
                                                data-satuan="{{ $lkb->satuanDasar?->satuan_nm ?? 'ROLL' }}"
                                                data-batch="{{ $batchInfoMap[$lkb->barang_id]['batch_no'] ?? '-' }}" 
                                                data-stok="{{ number_format($batchInfoMap[$lkb->barang_id]['sisa_qty'] ?? 0, 0, ',', '.') }} {{ $lkb->satuanDasar?->satuan_nm ?? 'Roll' }}"
                                                {{ old('lakban_barang_id', $selectedLaporan?->lakban_barang_id) == $lkb->barang_id ? 'selected' : '' }}>
                                                {{ $lkb->barang_nm }} ({{ $lkb->satuanDasar?->satuan_nm ?? 'ROLL' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="info_batch_lakban" style="font-size: 0.7rem; color: #64748b; margin-top: 3px; display: none;">
                                        Batch FIFO: <strong style="color: #0284c7;" id="txt_batch_lakban">-</strong> &bull; Sisa Stok: <strong id="txt_stok_lakban">-</strong>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group-web">
                                        <input type="number" step="any" min="0" id="lakban_roll_qty" name="lakban_roll_qty" class="form-control" placeholder="0" value="{{ old('lakban_roll_qty', $selectedLaporan?->lakban_roll_qty) }}" style="font-weight: 700; color: #be185d;" oninput="onManualRollInput()">
                                        <select id="lakban_satuan" name="lakban_satuan" class="form-control" style="max-width: 85px; font-weight: 700; background: #ffffff;" onchange="onLakbanSatuanChange(this.value)">
                                            <option value="ROLL" {{ old('lakban_satuan', 'ROLL') == 'ROLL' ? 'selected' : '' }}>Roll</option>
                                            <option value="METER" {{ old('lakban_satuan') == 'METER' ? 'selected' : '' }}>Meter</option>
                                            <option value="GRAM" {{ old('lakban_satuan') == 'GRAM' ? 'selected' : '' }}>Gram</option>
                                        </select>
                                    </div>
                                    <input type="hidden" id="lakban_standar_cm" name="lakban_standar_cm" value="{{ old('lakban_standar_cm', $selectedLaporan?->lakban_standar_cm ?? 120) }}">
                                    <input type="hidden" id="input_lakban_total_gr" value="{{ old('lakban_qty', $selectedLaporan?->lakban_qty) }}">
                                    <input type="hidden" id="input_lakban_atas_gr" value="{{ old('lakban_atas_gr', $selectedLaporan?->lakban_atas_gr) }}">
                                    <input type="hidden" id="input_lakban_bawah_gr" value="{{ old('lakban_bawah_gr', $selectedLaporan?->lakban_bawah_gr) }}">
                                </td>
                                <td>
                                    <div class="input-group-web">
                                        <input type="number" step="any" min="0" id="lakban_rusak_m" name="lakban_rusak_m" class="form-control" placeholder="0" value="{{ old('lakban_rusak_m', $selectedLaporan?->lakban_rusak_m) }}" style="border-color: #fca5a5;" oninput="recalculateLakbanFromKarton()">
                                        <span class="input-group-addon" style="color: #dc2626; font-size: 0.75rem; padding: 0 0.4rem;">Meter</span>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size: 0.725rem; color: #64748b;" id="lbl_lakban_summary">
                                        Standar 120cm / karton dus.
                                    </span>
                                </td>
                                <td style="text-align: center; color: #cbd5e1;">&bull;</td>
                            </tr>

                            {{-- BARIS 6: LABEL EXPIRED (KADALUARSA) --}}
                            <tr>
                                <td style="text-align: center; color: #94a3b8; font-weight: 700;">6</td>
                                <td>
                                    <span class="cat-badge-web cat-lain-web">Label Expired</span>
                                </td>
                                <td>
                                    <select id="expired_barang_id" name="expired_barang_id" class="form-control" onchange="updateRowBatchInfo('expired_barang_id', 'info_batch_expired', 'txt_batch_expired', 'txt_stok_expired')">
                                        <option value="">-- Pilih Kertas / Plastik Expired --</option>
                                        @foreach ($expiredList as $exp)
                                            <option value="{{ $exp->barang_id }}" 
                                                data-batch="{{ $batchInfoMap[$exp->barang_id]['batch_no'] ?? '-' }}" 
                                                data-stok="{{ number_format($batchInfoMap[$exp->barang_id]['sisa_qty'] ?? 0, 0, ',', '.') }} {{ $exp->satuanDasar?->satuan_nm ?? 'Lbr' }}"
                                                {{ old('expired_barang_id', $selectedLaporan?->expired_barang_id) == $exp->barang_id ? 'selected' : '' }}>
                                                {{ $exp->barang_nm }} ({{ $exp->satuanDasar?->satuan_nm ?? 'LEMBAR' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="info_batch_expired" style="font-size: 0.7rem; color: #64748b; margin-top: 3px; display: none;">
                                        Batch FIFO: <strong style="color: #0284c7;" id="txt_batch_expired">-</strong> &bull; Sisa Stok: <strong id="txt_stok_expired">-</strong>
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
            </div>
        </div>

        {{-- ═════════════════════════════════════════════════════════════════ --}}
        {{-- KARTU 4: CATATAN OPERASIONAL PENGEMASAN                            --}}
        {{-- ═════════════════════════════════════════════════════════════════ --}}
        <div class="card-web-form">
            <div class="card-web-header">
                <h2 class="card-web-title">
                    <span class="card-web-num">4</span>
                    <span>Catatan Operasional &amp; Kendala Lapangan</span>
                </h2>
                <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Keterangan Tambahan Shift</span>
            </div>
            <div class="card-web-body">
                <div class="form-group" style="margin-bottom: 0;">
                    <textarea id="catatan_txt" name="catatan_txt" class="form-control" rows="2" placeholder="Catatan tim shift, nomor meja kemas, kendala mesin/sealer, atau kondisi kemasan...">{{ old('catatan_txt', $selectedLaporan?->catatan) }}</textarea>
                </div>
            </div>
        </div>

        {{-- ═════════════════════════════════════════════════════════════════ --}}
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

        window.kemasanLainOptions = [
            @foreach ($kemasanLainList as $kml)
                {
                    barang_id: {{ $kml->barang_id }},
                    barang_nm: @json($kml->barang_nm),
                    satuan_nm: @json($kml->satuanDasar?->satuan_nm ?? 'PCS'),
                    batch_no: @json($batchInfoMap[$kml->barang_id]['batch_no'] ?? '-'),
                    sisa_qty: "{{ number_format($batchInfoMap[$kml->barang_id]['sisa_qty'] ?? 0, 0, ',', '.') }}"
                },
            @endforeach
        ];

        window.preloadedOperatorLaporan = @json($selectedLaporan ?? null);

        // Fungsi Update Info Batch FIFO & Stok di Bawah Dropdown Barang
        function updateRowBatchInfo(selectId, infoBoxId, txtBatchId, txtStokId) {
            const sel = document.getElementById(selectId);
            const box = document.getElementById(infoBoxId);
            const txtB = document.getElementById(txtBatchId);
            const txtS = document.getElementById(txtStokId);
            if (!sel || !box || !txtB || !txtS) return;

            const opt = sel.options[sel.selectedIndex];
            if (opt && opt.value) {
                const b = opt.getAttribute('data-batch') || '-';
                const s = opt.getAttribute('data-stok') || '0';
                txtB.textContent = b;
                txtS.textContent = s;
                box.style.display = 'block';
            } else {
                box.style.display = 'none';
            }
        }
        window.updateRowBatchInfo = updateRowBatchInfo;

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

        function onLakbanSatuanChange(satuan) {
            const modeInput = document.getElementById('lakban_satuan_mode');
            if (modeInput) {
                modeInput.value = (satuan === 'GRAM') ? 'GRAM' : 'CM';
            }
            if (typeof onManualRollInput === 'function') {
                onManualRollInput();
            }
        }
        window.onLakbanSatuanChange = onLakbanSatuanChange;

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

        function onWebWipChange(sel) {
            const opt = sel.options[sel.selectedIndex];
            const infoBox = document.getElementById('wipInfoBox');
            const infoText = document.getElementById('wipInfoText');
            if (opt && opt.value) {
                const prodId = opt.dataset.produksiId || '';
                const barangId = opt.dataset.barangId || '';
                const batch = opt.dataset.batch || '';
                const isBerbumbu = opt.dataset.isBerbumbu === '1';

                document.getElementById('hidden_produksi_id').value = prodId;
                document.getElementById('hidden_wip_barang_id').value = barangId;
                document.getElementById('hidden_wip_batch_no').value = batch;

                if (batch) {
                    const batchFgInput = document.getElementById('batch_no');
                    if (batchFgInput && !batchFgInput.value) {
                        batchFgInput.value = batch;
                    }
                }

                // Alert jika curah sudah berbumbu dari dapur
                const alertBumbu = document.getElementById('alertBumbuDapur');
                const badgeBumbu = document.getElementById('badgeBumbuStatus');
                const txtBumbuNormal = document.getElementById('txtBumbuNormal');
                const bumbuSelect = document.getElementById('bumbu_barang_id');
                const bumbuQty = document.getElementById('bumbu_qty_kg');

                if (isBerbumbu) {
                    if (alertBumbu) alertBumbu.style.display = 'block';
                    if (badgeBumbu) badgeBumbu.style.display = 'inline-block';
                    if (txtBumbuNormal) txtBumbuNormal.style.display = 'none';
                    if (bumbuSelect) bumbuSelect.value = '';
                    if (bumbuQty) bumbuQty.value = '0';
                } else {
                    if (alertBumbu) alertBumbu.style.display = 'none';
                    if (badgeBumbu) badgeBumbu.style.display = 'none';
                    if (txtBumbuNormal) txtBumbuNormal.style.display = 'inline';
                }

                if (infoBox && infoText) {
                    const hppVal = parseFloat(opt.dataset.hpp) || 0;
                    const hppTxt = hppVal > 0 ? ` &bull; HPP Curah: <strong>Rp ${hppVal.toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 2})}/Kg</strong>` : '';
                    infoText.innerHTML = `Batch Curah: <strong>${batch}</strong> &bull; Sisa Stok: <strong>${Number(opt.dataset.qty || 0).toLocaleString('id-ID')} Kg</strong>${hppTxt}`;
                    infoBox.style.display = 'block';
                }
            } else {
                if (infoBox) infoBox.style.display = 'none';
            }
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
                optionsHtml += `<option value="${item.barang_id}" data-satuan="${item.satuan_nm || 'PCS'}" data-batch="${item.batch_no || '-'}" data-stok="${item.sisa_qty || '0'} ${item.satuan_nm || 'Pcs'}" ${isSelected}>${item.barang_nm}${satuan}</option>`;
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
                    <div class="input-group-web">
                        <input type="number" step="any" min="0" name="kemasan_tambahan[${kemasanTambahanIndex}][qty]" class="form-control" placeholder="Qty" value="${defaultQty}" style="font-weight: 700;">
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

            if (selectedOpt && selectedOpt.value) {
                const satuan = (selectedOpt.getAttribute('data-satuan') || 'PCS').toUpperCase();
                if (unitSelect) {
                    for (let i = 0; i < unitSelect.options.length; i++) {
                        if (unitSelect.options[i].value === satuan || unitSelect.options[i].text.toUpperCase() === satuan) {
                            unitSelect.selectedIndex = i;
                            break;
                        }
                    }
                }
                if (infoBatch && txtBatch && txtStok) {
                    txtBatch.textContent = selectedOpt.getAttribute('data-batch') || '-';
                    txtStok.textContent = selectedOpt.getAttribute('data-stok') || '0';
                    infoBatch.style.display = 'block';
                }
            } else {
                if (infoBatch) infoBatch.style.display = 'none';
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

            // Inisialisasi tampilan nomor batch aktif FIFO bahan kemasan
            updateRowBatchInfo('bumbu_barang_id', 'info_batch_bumbu', 'txt_batch_bumbu', 'txt_stok_bumbu');
            updateRowBatchInfo('plastik_barang_id', 'info_batch_plastik', 'txt_batch_plastik', 'txt_stok_plastik');
            updateRowBatchInfo('karton_barang_id', 'info_batch_karton_baru', 'txt_batch_karton_baru', 'txt_stok_karton_baru');
            updateRowBatchInfo('lakban_barang_id', 'info_batch_lakban', 'txt_batch_lakban', 'txt_stok_lakban');
            updateRowBatchInfo('expired_barang_id', 'info_batch_expired', 'txt_batch_expired', 'txt_stok_expired');
        });
    </script>
    <script src="{{ asset('js/produksi/packaging/packaging-create.js') }}?v={{ filemtime(public_path('js/produksi/packaging/packaging-create.js')) }}"></script>
@endpush
