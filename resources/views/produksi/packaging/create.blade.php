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

            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <button type="button" onclick="openPullOperatorModal()" class="btn" style="background: #ecfdf5; color: #047857; border: 1.5px solid #a7f3d0; font-weight: 700; font-size: 0.8125rem; padding: 0.55rem 1rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); cursor: pointer;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>📥 Tarik Laporan Operator (HP)</span>
                </button>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600;">
            {{ session('error') }}
        </div>
    @endif

    {{-- Banner Notifikasi Jika Data Ditarik dari Laporan Operator --}}
    <div id="operatorSyncBadge" style="display: none; background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 0.75rem 1.25rem; margin-bottom: 1.25rem; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 1.25rem;">📱</span>
            <div>
                <strong style="color: #065f46; font-size: 0.875rem; display: block;" id="operatorSyncTitle">
                    Data Terhubung dengan Laporan Operator Lapangan
                </strong>
                <span style="color: #047857; font-size: 0.775rem;" id="operatorSyncSubtitle">
                    Bahan riil (bumbu, plastik, karton, lakban) terisi otomatis dari input operator HP.
                </span>
            </div>
        </div>
        <button type="button" onclick="clearOperatorSync()" style="background: none; border: none; color: #047857; text-decoration: underline; font-size: 0.75rem; font-weight: 700; cursor: pointer;">Lepas Tautan</button>
    </div>

    <form method="POST" action="{{ route('produksi.packaging.store') }}" id="formPackagingCreate">
        @csrf
        <input type="hidden" name="bom_id" id="bom_id_hidden" value="">
        <input type="hidden" name="laporan_operator_id" id="laporan_operator_id" value="">

        {{-- BAGIAN 1: INFORMASI DOKUMEN & IDENTITAS PRODUK KEMASAN --}}
        <div class="card" style="margin-bottom: 1.25rem;">
            <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem;">
                <strong style="color: #0f172a; font-size: 0.95rem;">
                    1. Informasi Dokumen &amp; Identitas Produk Kemasan
                </strong>
            </div>

            <div style="padding: 1.25rem;">
                {{-- Baris 1: Tanggal, Gudang FG, dan Target Produk Jadi Kemasan --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">
                            Tanggal Pengemasan <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" id="pack_tgl" name="pack_tgl" required
                               class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;" 
                               value="{{ old('pack_tgl', date('Y-m-d')) }}">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">
                            Gudang Tujuan Simpan FG <span style="color: #ef4444;">*</span>
                        </label>
                        <select id="gudang_id" name="gudang_id" required class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;">
                            <option value="">-- Pilih Lokasi Gudang FG --</option>
                            @foreach($gudangList as $g)
                                <option value="{{ $g->gudang_id }}" {{ (old('gudang_id') == $g->gudang_id) ? 'selected' : '' }}>
                                    {{ $g->gudang_nm }} ({{ $g->tipe_gudang_cd ?? 'Gudang' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">
                            Target Produk Jadi Kemasan <span style="color: #ef4444;">*</span>
                        </label>
                        <select id="barang_jadi_id" name="barang_jadi_id" required class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;">
                            <option value="">-- Pilih Produk Jadi yang Dikemas --</option>
                            @foreach($barangJadiList as $b)
                                <option value="{{ $b->barang_id }}" {{ (old('barang_jadi_id', request('barang_jadi_id')) == $b->barang_id) ? 'selected' : '' }}>
                                    {{ $b->barang_nm }} ({{ $b->barang_cd }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Baris 2: Tarik Lembar HPP Produksi (WIP), No. Batch, dan Tanggal Expired --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
                    <div>
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">
                            Sumber HPP Produksi (WIP) <span style="font-size: 0.75rem; color: #15803d; font-weight: 600;">(Prioritas FIFO)</span>
                        </label>
                        <select id="produksi_id" name="produksi_id" class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;" onchange="onProduksiSelect(this)">
                            <option value="">-- Pilih Lembar HPP Produksi / Curah --</option>
                            @foreach($produksiList as $p)
                                @php
                                    $tglStr = $p->produksi_tgl ? $p->produksi_tgl->format('d/m/Y') : '-';
                                    $expDateStr = $p->exp_date 
                                        ? $p->exp_date->format('Y-m-d') 
                                        : ($p->produksi_tgl ? $p->produksi_tgl->copy()->addMonths(6)->format('Y-m-d') : date('Y-m-d', strtotime('+6 months')));
                                    $validDetails = $p->details->filter(fn($d) => (float) $d->qty_kg > 0);
                                @endphp
                                @if($validDetails->isNotEmpty())
                                    @foreach($validDetails as $dtl)
                                        @php
                                            $qtyKg = (float) $dtl->qty_kg;
                                            $barangNm = $dtl->barang?->barang_nm ?? 'Keripik Curah';
                                            $batchStr = $dtl->batch_no ?: ($p->batch_wip_no ?: ($p->produksi_tgl ? $p->produksi_tgl->format('d m Y') : '-'));
                                            $hppVal = (float) ($dtl->hpp_satuan > 0 ? $dtl->hpp_satuan : $p->hpp_per_kg);
                                        @endphp
                                        <option value="{{ $p->produksi_id }}" 
                                                data-hpp="{{ $hppVal }}" 
                                                data-batch="{{ $batchStr }}" 
                                                data-exp="{{ $expDateStr }}"
                                                data-qty="{{ $qtyKg }}"
                                                data-no="{{ $p->produksi_no }}"
                                                data-barang-id="{{ $dtl->barang_id }}"
                                                data-barang-nm="{{ $barangNm }}"
                                                {{ (old('produksi_id') == $p->produksi_id) ? 'selected' : '' }}>
                                            [{{ $p->produksi_no }}] {{ $tglStr }} • {{ $barangNm }} ({{ number_format($qtyKg, 0, ',', '.') }} Kg) • Batch: {{ $batchStr }} (HPP: Rp {{ number_format($hppVal, 0, ',', '.') }}/Kg)
                                        </option>
                                    @endforeach
                                @else
                                    @php
                                        $qtyBerko = (float) ($p->total_berko_qty > 0 ? $p->total_berko_qty : $p->berko_qty);
                                        $batchStr = $p->batch_wip_no ?: ($p->produksi_tgl ? $p->produksi_tgl->format('d m Y') : '-');
                                    @endphp
                                    @if($qtyBerko > 0)
                                        <option value="{{ $p->produksi_id }}" 
                                                data-hpp="{{ (float) $p->hpp_per_kg }}" 
                                                data-batch="{{ $batchStr }}" 
                                                data-exp="{{ $expDateStr }}"
                                                data-qty="{{ $qtyBerko }}"
                                                data-no="{{ $p->produksi_no }}"
                                                data-barang-nm="KS BERKO"
                                                {{ (old('produksi_id') == $p->produksi_id) ? 'selected' : '' }}>
                                            [{{ $p->produksi_no }}] {{ $tglStr }} • KS BERKO ({{ number_format($qtyBerko, 0, ',', '.') }} Kg) • Batch: {{ $batchStr }} (HPP: Rp {{ number_format($p->hpp_per_kg, 0, ',', '.') }}/Kg)
                                        </option>
                                    @endif
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">
                            Nomor Lot / Batch FG
                        </label>
                        <input type="text" id="batch_no" name="batch_no"
                               class="form-control" placeholder="Contoh: 15 10 2026" value="{{ old('batch_no') }}"
                               style="height: 38px; border-radius: 6px; font-size: 0.85rem;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">
                            Tanggal Expired
                        </label>
                        <input type="date" id="expired_tgl" name="expired_tgl"
                               class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.85rem;"
                               value="{{ old('expired_tgl', date('Y-m-d', strtotime('+6 months'))) }}">
                    </div>
                </div>

                {{-- Status Sinkronisasi HPP WIP --}}
                <div id="wipSyncStatusBox" style="display: none; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 0.5rem 0.85rem; font-size: 0.8rem; color: #166534; align-items: center; justify-content: space-between; margin-top: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #16a34a; flex-shrink: 0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span id="wipSyncStatusText"></span>
                    </div>
                    <button type="button" onclick="clearProduksiSync()" style="background: none; border: none; font-size: 0.75rem; color: #15803d; text-decoration: underline; cursor: pointer; font-weight: 600;">Lepas Tautan</button>
                </div>
            </div>
        </div>

        {{-- BAGIAN 2: RINCIAN BAHAN KEMASAN & KERIPIK CURAH (WIP) TERPAKAI --}}
        <div class="card" style="margin-bottom: 1.25rem;">
            <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div>
                    <strong style="color: #0f172a; font-size: 0.95rem;">
                        2. Bahan Baku Kemasan &amp; Keripik Curah (WIP) Terpakai
                    </strong>
                </div>
                <div>
                    <button type="button" onclick="addNewMaterialRow()" class="btn btn-secondary btn-sm" style="font-size: 0.8rem; padding: 0.35rem 0.75rem; font-weight: 600; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; color: #334155; display: inline-flex; align-items: center; gap: 0.35rem;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah Baris Bahan</span>
                    </button>
                </div>
            </div>

            <div style="padding: 1.25rem;">
                {{-- Container Dinamis Info Resep BOM (jika dipanggil) --}}
                <div id="bomBannerContainer"></div>

                {{-- Tabel Item Konsumsi Bahan --}}
                <div style="overflow-x: auto; margin-bottom: 0.5rem;">
                    <table class="table" style="width: 100%; border-collapse: collapse; margin: 0; font-size: 0.825rem;">
                        <thead style="background: #f8fafc; color: #475569; font-weight: 600; border-bottom: 1px solid #e2e8f0;">
                            <tr>
                                <th style="padding: 0.65rem 0.75rem; width: 100px; text-align: left;">KATEGORI</th>
                                <th style="padding: 0.65rem 0.75rem; text-align: left;">KOMPONEN BAHAN</th>
                                <th style="padding: 0.65rem 0.75rem; text-align: right; width: 140px;">QTY AKTUAL <span style="color: #ef4444;">*</span></th>
                                <th style="padding: 0.65rem 0.75rem; text-align: right; width: 140px;">HPP SATUAN (RP)</th>
                                <th style="padding: 0.65rem 0.75rem; text-align: right; width: 150px;">SUBTOTAL</th>
                                <th style="padding: 0.65rem 0.75rem; text-align: center; width: 45px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="materialItemsTbody">
                            <tr id="emptyMaterialRow">
                                <td colspan="6" style="text-align: center; padding: 2rem 1rem; color: #94a3b8;">
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 0.35rem;">
                                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #cbd5e1;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        <span style="font-weight: 600; color: #475569; font-size: 0.85rem;">Belum ada komponen bahan</span>
                                        <span style="font-size: 0.75rem; color: #64748b;">Pilih produk jadi kemasan di atas atau klik <strong>"+ Tambah Baris Bahan"</strong>.</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- SUB-PANEL: TIMBANGAN KHUSUS LAKBAN SEGEL DUS (ATAS & BAWAH) --}}
                <div style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 8px; padding: 1rem 1.25rem; margin-top: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.85rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.15rem;">🏷️</span>
                            <div>
                                <strong style="color: #065f46; font-size: 0.875rem;">Timbangan Lakban Segel Karton (Atas, Bawah &amp; Rupiah)</strong>
                                <span style="display: block; font-size: 0.725rem; color: #047857;">
                                    Sesuai buku log timbangan packing: catat timbangan gram Atas dan Bawah secara presisi.
                                </span>
                            </div>
                        </div>
                        <div style="font-size: 0.75rem; background: #ffffff; padding: 0.25rem 0.65rem; border-radius: 6px; border: 1px solid #86efac; color: #166534; font-weight: 700;">
                            Tarif Acuan: Rp <span id="tarifLakbanInfo">35</span> / gram
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; align-items: flex-end;">
                        <div>
                            <label style="display: block; font-size: 0.775rem; font-weight: 700; color: #166534; margin-bottom: 0.35rem;">
                                Lakban Bagian Atas (Gram)
                            </label>
                            <input type="number" step="any" min="0" id="lakban_atas_gr" name="lakban_atas_gr"
                                   class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.9rem; font-weight: 700; text-align: right;" 
                                   value="{{ old('lakban_atas_gr', 0) }}" placeholder="Contoh: 1250" oninput="onLakbanInputsChanged()">
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.775rem; font-weight: 700; color: #166534; margin-bottom: 0.35rem;">
                                Lakban Bagian Bawah (Gram)
                            </label>
                            <input type="number" step="any" min="0" id="lakban_bawah_gr" name="lakban_bawah_gr"
                                   class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.9rem; font-weight: 700; text-align: right;" 
                                   value="{{ old('lakban_bawah_gr', 0) }}" placeholder="Contoh: 800" oninput="onLakbanInputsChanged()">
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.775rem; font-weight: 700; color: #166534; margin-bottom: 0.35rem;">
                                Total Timbangan (Gram)
                            </label>
                            <input type="number" step="any" min="0" id="lakban_jumlah_gr" name="lakban_jumlah_gr" readonly
                                   class="form-control" style="height: 38px; border-radius: 6px; font-size: 0.95rem; font-weight: 800; text-align: right; background: #e2e8f0; color: #0f172a;" 
                                   value="{{ old('lakban_jumlah_gr', 0) }}">
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.775rem; font-weight: 700; color: #166534; margin-bottom: 0.35rem;">
                                Total Biaya Lakban (Rupiah)
                            </label>
                            <input type="hidden" id="lakban_nilai" name="lakban_nilai" value="{{ old('lakban_nilai', 0) }}">
                            <div id="lakbanNilaiDisplay" style="height: 38px; border-radius: 6px; font-size: 1rem; font-weight: 800; color: #047857; background: #ffffff; border: 1.5px solid #10b981; display: flex; align-items: center; justify-content: flex-end; padding: 0 0.75rem;">
                                Rp 0
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BAGIAN 3: BIAYA TENAGA KERJA KEMAS & OPERASIONAL LAIN --}}
        <div class="card" style="margin-bottom: 1.25rem;">
            <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem;">
                <strong style="color: #0f172a; font-size: 0.95rem;">
                    3. Biaya Tenaga Kerja Kemas &amp; Catatan Khusus
                </strong>
            </div>

            <div style="padding: 1.25rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">
                            Upah Tenaga Kerja Kemas (Rp)
                        </label>
                        <input type="number" step="1" min="0" id="total_biaya_tenaga_kerja" name="total_biaya_tenaga_kerja"
                               class="form-control" style="text-align: right; height: 38px; border-radius: 6px; font-size: 0.85rem;" 
                               value="{{ old('total_biaya_tenaga_kerja', 0) }}" placeholder="0">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">
                            Biaya Lain-Lain / Overhead (Rp)
                        </label>
                        <input type="number" step="1" min="0" id="total_biaya_lain" name="total_biaya_lain"
                               class="form-control" style="text-align: right; height: 38px; border-radius: 6px; font-size: 0.85rem;" 
                               value="{{ old('total_biaya_lain', 0) }}" placeholder="0">
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">
                        Catatan Operasional Pengemasan
                    </label>
                    <textarea id="catatan_txt" name="catatan_txt" rows="2" class="form-control"
                              style="border-radius: 6px; font-size: 0.85rem;"
                              placeholder="Catatan tim shift, meja kemas, atau kondisi kemasan...">{{ old('catatan_txt') }}</textarea>
                </div>
            </div>
        </div>

        {{-- BAGIAN 4: REALISASI HASIL KEMASAN (OUTPUT FINISHED GOODS) --}}
        <div class="card" style="margin-bottom: 1.25rem;">
            <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 0.875rem 1.25rem;">
                <strong style="color: #0f172a; font-size: 0.95rem;">
                    4. Realisasi Hasil Kemasan (Output Finished Goods)
                </strong>
            </div>

            <div style="padding: 1.25rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; align-items: flex-start;">
                    <div>
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">
                            Jumlah Hasil Kemas (Karton) <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="number" step="0.01" min="0.01" id="hasil_karton_qty" name="hasil_karton_qty" required
                               class="form-control" style="text-align: right; font-weight: 700; font-size: 1.15rem; color: #047857; height: 40px; border: 1.5px solid #10b981; border-radius: 6px;" 
                               value="{{ old('hasil_karton_qty') }}" placeholder="0">
                        <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.25rem; display: block;">Kuantitas riil fisik karton jadi.</small>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">
                            Isi per Karton (Pcs)
                        </label>
                        <input type="number" step="1" min="1" id="isi_per_karton_pcs" name="isi_per_karton_pcs"
                               class="form-control" style="text-align: right; height: 40px; font-weight: 600; font-size: 0.95rem; border-radius: 6px;" 
                               value="{{ old('isi_per_karton_pcs', 20) }}" placeholder="20">
                        <small id="livePcsOutputBadge" style="color: #64748b; font-size: 0.725rem; margin-top: 0.25rem; display: block;">Total setara: 0 Pcs kemasan.</small>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem;">
                            Gramatur per Bungkus (Gram)
                        </label>
                        <input type="number" step="0.001" min="0" id="gramatur_gr" name="gramatur_gr"
                               class="form-control" style="text-align: right; height: 40px; font-weight: 600; font-size: 0.95rem; border-radius: 6px;" 
                               value="{{ old('gramatur_gr', 16) }}" placeholder="16">
                        <small style="color: #64748b; font-size: 0.725rem; margin-top: 0.25rem; display: block;">Berat timbangan netto per kemasan.</small>
                    </div>
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
                        0 Karton
                    </div>
                    <span id="sumTotalPcsInfo" style="font-size: 0.725rem; font-weight: 600; color: #64748b;">
                        Setara 0 Pcs Siap Edar
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

{{-- MODAL TARIK LAPORAN OPERATOR LAPANGAN --}}
@include('produksi.packaging.partials.modal-pull-operator')
@endsection

@push('scripts')
    <script>
        window.masterKemasanList = @json($kemasanBarangList ?? []);
        window.preloadedOperatorLaporan = @json($selectedLaporan ?? null);
    </script>
    <script src="{{ asset('js/produksi/packaging/packaging-create.js') }}"></script>
@endpush
