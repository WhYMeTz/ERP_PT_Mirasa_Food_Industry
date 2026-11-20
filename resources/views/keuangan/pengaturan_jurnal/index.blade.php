@extends('layouts.app')

@section('title', 'Pemetaan Jurnal Otomatis')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/pengaturan-jurnal/pengaturan-jurnal.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="settings-container">
    {{-- ALERT NOTIFIKASI --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-bottom: 1.25rem; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 8px; padding: 0.85rem 1.35rem;">
            <strong>Berhasil:</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-bottom: 1.25rem; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 8px; padding: 0.85rem 1.35rem;">
            <strong>Perhatian:</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('keuangan.pengaturan_jurnal.update') }}" method="POST" id="formPengaturanJurnal">
        @csrf

        {{-- HEADER UTAMA --}}
        <div class="settings-header">
            <div class="settings-header-left">
                <div class="header-title-badge">
                    <span class="header-accent-dot"></span>
                    <h1>Pemetaan Akun Jurnal Otomatis</h1>
                </div>
                <div class="settings-header-desc">
                    Tentukan rekening akun COA, susunan urutan posting voucher pembukuan, dan narasi keterangan jurnal umum otomatis.
                </div>
            </div>

            <div>
                <button type="submit" class="header-action-btn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan Pengaturan</span>
                </button>
            </div>
        </div>

        {{-- TAB SWITCHER FULL WIDTH --}}
        <div class="settings-tabs">
            <button type="button" class="tab-btn active" data-tab="tab-hpp">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <span>1. Biaya Produksi &amp; HPP WIP</span>
            </button>
            <button type="button" class="tab-btn" data-tab="tab-hpp-fg">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
                <span>2. HPP Pengemasan &amp; Barang Jadi (FG)</span>
            </button>
            <button type="button" class="tab-btn" data-tab="tab-pembelian">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>3. Pembelian &amp; Hutang Usaha (AP)</span>
            </button>
        </div>

        {{-- ==================================================================
             TAB 1: HPP PRODUKSI WIP
             ================================================================== --}}
        @php
            $hppSettings = $settingsGrouped->get('HPP_WIP', collect())->keyBy('kunci');
            $hppTemplateVal = old('settings.HPP_WIP_TEMPLATE_KET', $hppSettings['HPP_WIP_TEMPLATE_KET']->nilai_text ?? 'Biaya Produksi & HPP WIP ({tgl}) - Shift {shift} ({total_kg} kg WIP)');
            $hppPresetStandard = 'Biaya Produksi & HPP WIP ({tgl}) - Shift {shift} ({total_kg} kg WIP)';
            $hppPresetSimple = 'Biaya Produksi & HPP WIP ({tgl}) - Shift {shift}';
            $isHppCustom = ($hppTemplateVal !== $hppPresetStandard && $hppTemplateVal !== $hppPresetSimple);

            $hppDefs = [
                'HPP_WIP_AKUN_WIP' => [
                    'kunci' => 'HPP_WIP_AKUN_WIP',
                    'nama' => 'Persediaan Hasil WIP / Curah',
                    'desc' => 'Akumulasi biaya produksi yang dikapitalisasi ke stok',
                    'posisi' => 'DEBET',
                    'picker_title' => 'Persediaan Hasil WIP / Keripik Curah',
                    'fallback_code' => '1151',
                    'fallback_nama' => 'Persediaan WIP / Curah',
                    'default_urutan' => 1,
                ],
                'HPP_WIP_AKUN_BAHAN' => [
                    'kunci' => 'HPP_WIP_AKUN_BAHAN',
                    'nama' => 'Persediaan Bahan Baku Terpakai',
                    'desc' => 'Pengeluaran singkong mentah dari gudang bahan baku',
                    'posisi' => 'KREDIT',
                    'picker_title' => 'Persediaan Bahan Baku Terpakai',
                    'fallback_code' => '1150',
                    'fallback_nama' => 'Persediaan Bahan Baku',
                    'default_urutan' => 2,
                ],
                'HPP_WIP_AKUN_PENOLONG' => [
                    'kunci' => 'HPP_WIP_AKUN_PENOLONG',
                    'nama' => 'Persediaan Bahan Penolong',
                    'desc' => 'Pemakaian minyak goreng, bumbu rendam & perenyah',
                    'posisi' => 'KREDIT',
                    'picker_title' => 'Persediaan Bahan Penolong Terpakai',
                    'fallback_code' => '1153',
                    'fallback_nama' => 'Persediaan Bahan Penolong',
                    'default_urutan' => 3,
                ],
                'HPP_WIP_AKUN_GAS' => [
                    'kunci' => 'HPP_WIP_AKUN_GAS',
                    'nama' => 'Pemakaian Gas Alam CNG',
                    'desc' => 'Akrual kewajiban harian gas sebelum tagihan terbit',
                    'posisi' => 'KREDIT',
                    'picker_title' => 'Pemakaian Gas Alam CNG Pabrik',
                    'fallback_code' => '2100',
                    'fallback_nama' => 'Pemakaian Gas Alam CNG',
                    'default_urutan' => 4,
                ],
                'HPP_WIP_AKUN_TENAGA_KERJA' => [
                    'kunci' => 'HPP_WIP_AKUN_TENAGA_KERJA',
                    'nama' => 'Upah Tenaga Kerja (BTKL)',
                    'desc' => 'Alokasi gaji dan upah borongan tim penggorengan',
                    'posisi' => 'KREDIT',
                    'picker_title' => 'Upah Tenaga Kerja Langsung (BTKL)',
                    'fallback_code' => '2102',
                    'fallback_nama' => 'Upah Tenaga Kerja (BTKL)',
                    'default_urutan' => 5,
                ],
            ];

            $hppRows = collect($hppDefs)->map(function ($def, $kunci) use ($hppSettings, $coaList) {
                $setting = $hppSettings[$kunci] ?? null;
                $coa = $setting ? $coaList->firstWhere('coa_id', $setting->nilai_coa_id) : null;
                $def['setting'] = $setting;
                $def['coa_id'] = $setting?->nilai_coa_id;
                $def['kode_akun'] = $coa->kode_akun ?? $def['fallback_code'];
                $def['nama_akun'] = $coa->nama_akun ?? $def['fallback_nama'];
                $def['urutan'] = (int) ($setting->urutan ?? $def['default_urutan']);
                return $def;
            })->sortBy('urutan')->values();
        @endphp
        <div class="tab-content active" id="tab-hpp">
            <div class="workbench-dual-container">
                {{-- SISI KIRI: ATURAN AKUN BUKU BESAR DENGAN REORDERING POSISI --}}
                <div class="rules-card-container">
                    <div class="rules-card-header">
                        <div>
                            <div class="rules-header-title">
                                <h2>Aturan Akun Jurnal HPP WIP (Keripik Curah)</h2>
                            </div>
                            <div class="rules-header-desc">Gunakan tombol ▲ / ▼ untuk mengatur urutan posting jurnal pembukuan. Rekening akun dipilih via tombol COA.</div>
                        </div>
                        <span class="rules-counter-badge">5 Komponen Terpetakan</span>
                    </div>

                    <div class="rules-rows-wrap" id="wrap_rules_HPP_WIP">
                        @foreach($hppRows as $idx => $r)
                        <div class="rule-row-box" data-kunci="{{ $r['kunci'] }}" draggable="true">
                            {{-- Reorder Gripper & Up/Down Buttons --}}
                            <div class="rule-reorder-col">
                                <span class="row-seq-badge">#{{ $idx + 1 }}</span>
                                <div class="row-reorder-nav">
                                    <button type="button" class="btn-reorder-nav btn-up" onclick="moveRuleRow(this, -1)" title="Geser ke Atas" {{ $loop->first ? 'disabled' : '' }}>
                                        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg>
                                    </button>
                                    <button type="button" class="btn-reorder-nav btn-down" onclick="moveRuleRow(this, 1)" title="Geser ke Bawah" {{ $loop->last ? 'disabled' : '' }}>
                                        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </div>
                                <input type="hidden" name="urutan[{{ $r['kunci'] }}]" class="input-row-urutan" value="{{ $idx + 1 }}">
                            </div>

                            {{-- Info Komponen --}}
                            <div class="rule-meta-info">
                                <span class="rule-comp-title">{{ $r['nama'] }}</span>
                                <span class="rule-comp-desc">{{ $r['desc'] }}</span>
                            </div>

                            {{-- Posisi Solid Badge (Debet / Kredit) --}}
                            <div class="rule-pos-col">
                                <span class="rule-side-badge {{ strtolower($r['posisi']) }}">{{ $r['posisi'] }}</span>
                            </div>

                            {{-- Tombol Pemilihan COA --}}
                            <div class="rule-coa-action">
                                <input type="hidden" name="settings[{{ $r['kunci'] }}]" id="input_{{ $r['kunci'] }}" value="{{ $r['coa_id'] }}">
                                <button type="button" class="btn-pick-coa" onclick="openCoaModalPicker('{{ $r['kunci'] }}', '{{ $r['picker_title'] }}')">
                                    <div class="coa-pick-main">
                                        <span class="coa-badge-code" id="badge_{{ $r['kunci'] }}">{{ $r['kode_akun'] }}</span>
                                        <span class="coa-name-text" id="name_{{ $r['kunci'] }}">{{ $r['nama_akun'] }}</span>
                                    </div>
                                    <span class="coa-pick-tag">Pilih ▾</span>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- SISI KANAN: WORKBENCH FORMAT & LIVE VOUCHER SLIP DENGAN URUTAN REAL-TIME --}}
                <div class="simulator-card-container">
                    {{-- SECTION A: PILIHAN FORMAT KETERANGAN --}}
                    <div class="format-box-wrap">
                        <div class="format-box-header">
                            <span class="format-box-title">Susunan Narasi Jurnal</span>
                            <span class="format-box-sub">Template Otomatis</span>
                        </div>

                        <div class="preset-options-list">
                            <label class="preset-card-item {{ (!$isHppCustom && $hppTemplateVal === $hppPresetStandard) ? 'selected' : '' }}">
                                <input type="radio" name="preset_hpp" value="{{ $hppPresetStandard }}" {{ (!$isHppCustom && $hppTemplateVal === $hppPresetStandard) ? 'checked' : '' }}>
                                <div>
                                    <div class="preset-item-title">Format Lengkap (Rekomendasi)</div>
                                    <div class="preset-item-desc">Tanggal, shift kerja, dan total kg hasil WIP.</div>
                                </div>
                            </label>

                            <label class="preset-card-item {{ (!$isHppCustom && $hppTemplateVal === $hppPresetSimple) ? 'selected' : '' }}">
                                <input type="radio" name="preset_hpp" value="{{ $hppPresetSimple }}" {{ (!$isHppCustom && $hppTemplateVal === $hppPresetSimple) ? 'checked' : '' }}>
                                <div>
                                    <div class="preset-item-title">Format Ringkas</div>
                                    <div class="preset-item-desc">Hanya tanggal dan shift kerja.</div>
                                </div>
                            </label>

                            <label class="preset-card-item {{ $isHppCustom ? 'selected' : '' }}">
                                <input type="radio" name="preset_hpp" value="CUSTOM" {{ $isHppCustom ? 'checked' : '' }}>
                                <div>
                                    <div class="preset-item-title">Kustom Manual</div>
                                    <div class="preset-item-desc">Tulis kalimat bebas dengan chip variabel cepat.</div>
                                </div>
                            </label>
                        </div>

                        {{-- KOTAK KUSTOM MANUAL --}}
                        <div class="custom-input-wrap" id="customBox_HPP_WIP" style="{{ $isHppCustom ? '' : 'display: none;' }}">
                            <div class="custom-input-top">
                                <span class="custom-input-heading">Variabel Cepat:</span>
                                <div class="quick-chips-group">
                                    <button type="button" class="quick-chip-btn" data-target="setting_HPP_WIP_TEMPLATE_KET" data-tag="{tgl}">+ Tanggal</button>
                                    <button type="button" class="quick-chip-btn" data-target="setting_HPP_WIP_TEMPLATE_KET" data-tag="{shift}">+ Shift</button>
                                    <button type="button" class="quick-chip-btn" data-target="setting_HPP_WIP_TEMPLATE_KET" data-tag="{total_kg}">+ Total Kg</button>
                                    <button type="button" class="quick-chip-btn" data-target="setting_HPP_WIP_TEMPLATE_KET" data-tag="{lini}">+ Lini</button>
                                </div>
                            </div>
                            <input type="text" name="settings[HPP_WIP_TEMPLATE_KET]" id="setting_HPP_WIP_TEMPLATE_KET" class="template-text-field" value="{{ $hppTemplateVal }}" placeholder="Ketik format keterangan...">
                        </div>
                    </div>

                    {{-- SECTION B: VOUCHER SLIP DENGAN TABEL URUTAN JURNAL UMUM --}}
                    <div class="voucher-slip-card">
                        <div class="voucher-card-top">
                            <div class="voucher-top-left">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span class="voucher-top-heading">Simulasi Posisi Jurnal Umum (Urutan Pembukuan)</span>
                            </div>
                            <span class="voucher-live-dot">● LIVE UPDATE</span>
                        </div>

                        <div class="voucher-card-inner">
                            <div class="voucher-narasi-container">
                                <div class="voucher-narasi-caption">Keterangan Narasi Jurnal:</div>
                                <div class="voucher-narasi-value" id="preview_HPP_WIP_TEMPLATE_KET">-</div>
                            </div>

                            <table class="voucher-ledger-table" id="slip_table_HPP_WIP">
                                <thead>
                                    <tr>
                                        <th style="width: 22%;">Posisi</th>
                                        <th style="width: 78%;">Rekening Akun Buku Besar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($hppRows as $r)
                                    <tr data-slip-kunci="{{ $r['kunci'] }}">
                                        <td><span class="slip-pos-badge {{ strtolower($r['posisi']) }}">{{ $r['posisi'] }}</span></td>
                                        <td class="slip-coa-text"><span class="slip-coa-code" id="slip_badge_{{ $r['kunci'] }}">{{ $r['kode_akun'] }}</span> &middot; <span id="slip_name_{{ $r['kunci'] }}">{{ $r['nama_akun'] }}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="voucher-card-footer">
                            <span>Otomatisasi: Posting HPP WIP Selesai</span>
                            <span>Shift A &middot; 300,00 kg WIP</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================================================================
             TAB 2: HPP PENGEMASAN & BARANG JADI (FG)
             ================================================================== --}}
        @php
            $fgSettings = $settingsGrouped->get('HPP_FG', collect())->keyBy('kunci');
            $fgTemplateVal = old('settings.HPP_FG_TEMPLATE_KET', $fgSettings['HPP_FG_TEMPLATE_KET']->nilai_text ?? 'Biaya Pengemasan & HPP FG ({tgl}) - {barang_jadi} ({hasil_karton} karton / {hasil_pcs} pcs)');
            $fgPresetStandard = 'Biaya Pengemasan & HPP FG ({tgl}) - {barang_jadi} ({hasil_karton} karton / {hasil_pcs} pcs)';
            $fgPresetSimple = 'Biaya Pengemasan & HPP FG ({tgl}) - {barang_jadi} [Batch: {batch_no}]';
            $isFgCustom = ($fgTemplateVal !== $fgPresetStandard && $fgTemplateVal !== $fgPresetSimple);

            $fgDefs = [
                'HPP_FG_AKUN_FG' => [
                    'kunci' => 'HPP_FG_AKUN_FG',
                    'nama' => 'Persediaan Barang Jadi (FG)',
                    'desc' => 'Akumulasi HPP Pengemasan dikapitalisasi ke stok FG',
                    'posisi' => 'DEBET',
                    'picker_title' => 'Persediaan Barang Jadi (FG) Siap Jual',
                    'fallback_code' => '1152',
                    'fallback_nama' => 'Persediaan Barang Jadi (FG)',
                    'default_urutan' => 1,
                ],
                'HPP_FG_AKUN_WIP' => [
                    'kunci' => 'HPP_FG_AKUN_WIP',
                    'nama' => 'Persediaan WIP / Keripik Curah',
                    'desc' => 'Pengeluaran keripik singkong curah dari gudang WIP',
                    'posisi' => 'KREDIT',
                    'picker_title' => 'Persediaan WIP / Keripik Curah Terpakai',
                    'fallback_code' => '1151',
                    'fallback_nama' => 'Persediaan WIP / Curah',
                    'default_urutan' => 2,
                ],
                'HPP_FG_AKUN_KEMASAN' => [
                    'kunci' => 'HPP_FG_AKUN_KEMASAN',
                    'nama' => 'Persediaan Bahan Kemasan & Dus',
                    'desc' => 'Pengeluaran kemasan primer, karton dus & lakban',
                    'posisi' => 'KREDIT',
                    'picker_title' => 'Persediaan Bahan Kemasan, Karton & Lakban',
                    'fallback_code' => '1154',
                    'fallback_nama' => 'Persediaan Bahan Kemasan & Dus',
                    'default_urutan' => 3,
                ],
                'HPP_FG_AKUN_TENAGA_KERJA' => [
                    'kunci' => 'HPP_FG_AKUN_TENAGA_KERJA',
                    'nama' => 'Upah Tenaga Kerja Kemas (BTKL)',
                    'desc' => 'Kewajiban upah borongan / harian regu pengemasan',
                    'posisi' => 'KREDIT',
                    'picker_title' => 'Upah Tenaga Kerja Kemas (BTKL Kemas)',
                    'fallback_code' => '2102',
                    'fallback_nama' => 'Upah Tenaga Kerja (BTKL)',
                    'default_urutan' => 4,
                ],
                'HPP_FG_AKUN_OVERHEAD' => [
                    'kunci' => 'HPP_FG_AKUN_OVERHEAD',
                    'nama' => 'Biaya Overhead Pengemasan',
                    'desc' => 'Biaya penunjang operasional pengemasan barang jadi',
                    'posisi' => 'KREDIT',
                    'picker_title' => 'Biaya Lain-Lain & Overhead Pengemasan',
                    'fallback_code' => '5103',
                    'fallback_nama' => 'Biaya Overhead Pengemasan',
                    'default_urutan' => 5,
                ],
            ];

            $fgRows = collect($fgDefs)->map(function ($def, $kunci) use ($fgSettings, $coaList) {
                $setting = $fgSettings[$kunci] ?? null;
                $coa = $setting ? $coaList->firstWhere('coa_id', $setting->nilai_coa_id) : null;
                $def['setting'] = $setting;
                $def['coa_id'] = $setting?->nilai_coa_id;
                $def['kode_akun'] = $coa->kode_akun ?? $def['fallback_code'];
                $def['nama_akun'] = $coa->nama_akun ?? $def['fallback_nama'];
                $def['urutan'] = (int) ($setting->urutan ?? $def['default_urutan']);
                return $def;
            })->sortBy('urutan')->values();
        @endphp
        <div class="tab-content" id="tab-hpp-fg">
            <div class="workbench-dual-container">
                {{-- SISI KIRI: ATURAN AKUN BUKU BESAR DENGAN REORDERING POSISI --}}
                <div class="rules-card-container">
                    <div class="rules-card-header">
                        <div>
                            <div class="rules-header-title">
                                <h2>Aturan Akun Jurnal HPP Barang Jadi (FG)</h2>
                            </div>
                            <div class="rules-header-desc">Gunakan tombol ▲ / ▼ untuk mengatur urutan posting jurnal pembukuan. Rekening akun dipilih via tombol COA.</div>
                        </div>
                        <span class="rules-counter-badge">5 Komponen Terpetakan</span>
                    </div>

                    <div class="rules-rows-wrap" id="wrap_rules_HPP_FG">
                        @foreach($fgRows as $idx => $r)
                        <div class="rule-row-box" data-kunci="{{ $r['kunci'] }}" draggable="true">
                            {{-- Reorder Gripper & Up/Down Buttons --}}
                            <div class="rule-reorder-col">
                                <span class="row-seq-badge">#{{ $idx + 1 }}</span>
                                <div class="row-reorder-nav">
                                    <button type="button" class="btn-reorder-nav btn-up" onclick="moveRuleRow(this, -1)" title="Geser ke Atas" {{ $loop->first ? 'disabled' : '' }}>
                                        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg>
                                    </button>
                                    <button type="button" class="btn-reorder-nav btn-down" onclick="moveRuleRow(this, 1)" title="Geser ke Bawah" {{ $loop->last ? 'disabled' : '' }}>
                                        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </div>
                                <input type="hidden" name="urutan[{{ $r['kunci'] }}]" class="input-row-urutan" value="{{ $idx + 1 }}">
                            </div>

                            {{-- Info Komponen --}}
                            <div class="rule-meta-info">
                                <span class="rule-comp-title">{{ $r['nama'] }}</span>
                                <span class="rule-comp-desc">{{ $r['desc'] }}</span>
                            </div>

                            {{-- Posisi Solid Badge (Debet / Kredit) --}}
                            <div class="rule-pos-col">
                                <span class="rule-side-badge {{ strtolower($r['posisi']) }}">{{ $r['posisi'] }}</span>
                            </div>

                            {{-- Tombol Pemilihan COA --}}
                            <div class="rule-coa-action">
                                <input type="hidden" name="settings[{{ $r['kunci'] }}]" id="input_{{ $r['kunci'] }}" value="{{ $r['coa_id'] }}">
                                <button type="button" class="btn-pick-coa" onclick="openCoaModalPicker('{{ $r['kunci'] }}', '{{ $r['picker_title'] }}')">
                                    <div class="coa-pick-main">
                                        <span class="coa-badge-code" id="badge_{{ $r['kunci'] }}">{{ $r['kode_akun'] }}</span>
                                        <span class="coa-name-text" id="name_{{ $r['kunci'] }}">{{ $r['nama_akun'] }}</span>
                                    </div>
                                    <span class="coa-pick-tag">Pilih ▾</span>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- SISI KANAN: WORKBENCH FORMAT & LIVE VOUCHER SLIP DENGAN URUTAN REAL-TIME --}}
                <div class="simulator-card-container">
                    {{-- SECTION A: PILIHAN FORMAT KETERANGAN --}}
                    <div class="format-box-wrap">
                        <div class="format-box-header">
                            <span class="format-box-title">Susunan Narasi Jurnal</span>
                            <span class="format-box-sub">Template Otomatis</span>
                        </div>

                        <div class="preset-options-list">
                            <label class="preset-card-item {{ (!$isFgCustom && $fgTemplateVal === $fgPresetStandard) ? 'selected' : '' }}">
                                <input type="radio" name="preset_fg" value="{{ $fgPresetStandard }}" {{ (!$isFgCustom && $fgTemplateVal === $fgPresetStandard) ? 'checked' : '' }}>
                                <div>
                                    <div class="preset-item-title">Format Lengkap (Rekomendasi)</div>
                                    <div class="preset-item-desc">Tanggal kemas, nama barang jadi, hasil karton &amp; total pcs.</div>
                                </div>
                            </label>

                            <label class="preset-card-item {{ (!$isFgCustom && $fgTemplateVal === $fgPresetSimple) ? 'selected' : '' }}">
                                <input type="radio" name="preset_fg" value="{{ $fgPresetSimple }}" {{ (!$isFgCustom && $fgTemplateVal === $fgPresetSimple) ? 'checked' : '' }}>
                                <div>
                                    <div class="preset-item-title">Format Nomor Batch</div>
                                    <div class="preset-item-desc">Tanggal kemas, nama barang jadi, dan nomor batch produksi.</div>
                                </div>
                            </label>

                            <label class="preset-card-item {{ $isFgCustom ? 'selected' : '' }}">
                                <input type="radio" name="preset_fg" value="CUSTOM" {{ $isFgCustom ? 'checked' : '' }}>
                                <div>
                                    <div class="preset-item-title">Kustom Manual</div>
                                    <div class="preset-item-desc">Tulis kalimat bebas dengan chip variabel cepat.</div>
                                </div>
                            </label>
                        </div>

                        {{-- KOTAK KUSTOM MANUAL --}}
                        <div class="custom-input-wrap" id="customBox_HPP_FG" style="{{ $isFgCustom ? '' : 'display: none;' }}">
                            <div class="custom-input-top">
                                <span class="custom-input-heading">Variabel Cepat:</span>
                                <div class="quick-chips-group">
                                    <button type="button" class="quick-chip-btn" data-target="setting_HPP_FG_TEMPLATE_KET" data-tag="{tgl}">+ Tanggal</button>
                                    <button type="button" class="quick-chip-btn" data-target="setting_HPP_FG_TEMPLATE_KET" data-tag="{barang_jadi}">+ Nama FG</button>
                                    <button type="button" class="quick-chip-btn" data-target="setting_HPP_FG_TEMPLATE_KET" data-tag="{hasil_karton}">+ Karton</button>
                                    <button type="button" class="quick-chip-btn" data-target="setting_HPP_FG_TEMPLATE_KET" data-tag="{hasil_pcs}">+ Pcs</button>
                                    <button type="button" class="quick-chip-btn" data-target="setting_HPP_FG_TEMPLATE_KET" data-tag="{batch_no}">+ Batch</button>
                                </div>
                            </div>
                            <input type="text" name="settings[HPP_FG_TEMPLATE_KET]" id="setting_HPP_FG_TEMPLATE_KET" class="template-text-field" value="{{ $fgTemplateVal }}" placeholder="Ketik format keterangan...">
                        </div>
                    </div>

                    {{-- SECTION B: VOUCHER SLIP DENGAN TABEL URUTAN JURNAL UMUM --}}
                    <div class="voucher-slip-card">
                        <div class="voucher-card-top">
                            <div class="voucher-top-left">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span class="voucher-top-heading">Simulasi Posisi Jurnal Umum (Urutan Pembukuan)</span>
                            </div>
                            <span class="voucher-live-dot">● LIVE UPDATE</span>
                        </div>

                        <div class="voucher-card-inner">
                            <div class="voucher-narasi-container">
                                <div class="voucher-narasi-caption">Keterangan Narasi Jurnal:</div>
                                <div class="voucher-narasi-value" id="preview_HPP_FG_TEMPLATE_KET">-</div>
                            </div>

                            <table class="voucher-ledger-table" id="slip_table_HPP_FG">
                                <thead>
                                    <tr>
                                        <th style="width: 22%;">Posisi</th>
                                        <th style="width: 78%;">Rekening Akun Buku Besar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fgRows as $r)
                                    <tr data-slip-kunci="{{ $r['kunci'] }}">
                                        <td><span class="slip-pos-badge {{ strtolower($r['posisi']) }}">{{ $r['posisi'] }}</span></td>
                                        <td class="slip-coa-text"><span class="slip-coa-code" id="slip_badge_{{ $r['kunci'] }}">{{ $r['kode_akun'] }}</span> &middot; <span id="slip_name_{{ $r['kunci'] }}">{{ $r['nama_akun'] }}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="voucher-card-footer">
                            <span>Otomatisasi: HPP Pengemasan</span>
                            <span>FG Balado &middot; 150 Karton (6.000 Pcs)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================================================================
             TAB 3: PEMBELIAN & HUTANG USAHA (AP)
             ================================================================== --}}
        @php
            $apSettings = $settingsGrouped->get('PEMBELIAN', collect())->keyBy('kunci');
            $apTemplateVal = old('settings.PEMBELIAN_TEMPLATE_KET', $apSettings['PEMBELIAN_TEMPLATE_KET']->nilai_text ?? '{supplier} - {qty} {satuan} {nama_barang} @{harga}');
            $apPresetStandard = '{supplier} - {qty} {satuan} {nama_barang} @{harga}';
            $apPresetSimple = '{supplier} - Faktur {no_faktur}';
            $isApCustom = ($apTemplateVal !== $apPresetStandard && $apTemplateVal !== $apPresetSimple);

            $apDefs = [
                'PEMBELIAN_AKUN_PERSEDIAAN' => [
                    'kunci' => 'PEMBELIAN_AKUN_PERSEDIAAN',
                    'nama' => 'Persediaan Bahan Baku Masuk',
                    'desc' => 'Singkong Segar / Bahan Mentah (DPP sebelum pajak)',
                    'posisi' => 'DEBET',
                    'picker_title' => 'Persediaan Bahan Baku Masuk',
                    'fallback_code' => '1150',
                    'fallback_nama' => 'Persediaan Bahan Baku',
                    'default_urutan' => 1,
                ],
                'PEMBELIAN_AKUN_PENOLONG' => [
                    'kunci' => 'PEMBELIAN_AKUN_PENOLONG',
                    'nama' => 'Persediaan Bahan Penolong & Kemasan',
                    'desc' => 'Minyak, Bumbu, Dus Karton, Lakban (DPP sebelum pajak)',
                    'posisi' => 'DEBET',
                    'picker_title' => 'Persediaan Bahan Penolong & Kemasan Masuk',
                    'fallback_code' => '1153',
                    'fallback_nama' => 'Persediaan Bahan Penolong & Kemasan',
                    'default_urutan' => 2,
                ],
                'PEMBELIAN_AKUN_ONGKIR' => [
                    'kunci' => 'PEMBELIAN_AKUN_ONGKIR',
                    'nama' => 'Ongkos Angkut / Biaya Kirim',
                    'desc' => 'Beban ekspedisi pengiriman yang ditagihkan supplier',
                    'posisi' => 'DEBET',
                    'picker_title' => 'Ongkos Angkut / Biaya Kirim Pembelian',
                    'fallback_code' => '6112',
                    'fallback_nama' => 'Ongkos Angkut Pembelian',
                    'default_urutan' => 3,
                ],
                'PEMBELIAN_AKUN_HUTANG' => [
                    'kunci' => 'PEMBELIAN_AKUN_HUTANG',
                    'nama' => 'Hutang Usaha ke Supplier',
                    'desc' => 'Kewajiban tagihan faktur yang jatuh tempo',
                    'posisi' => 'KREDIT',
                    'picker_title' => 'Hutang Usaha ke Supplier',
                    'fallback_code' => '2100',
                    'fallback_nama' => 'Hutang Usaha Supplier',
                    'default_urutan' => 4,
                ],
                'PEMBELIAN_AKUN_UANG_MUKA' => [
                    'kunci' => 'PEMBELIAN_AKUN_UANG_MUKA',
                    'nama' => 'Penyelesaian Uang Muka (DP)',
                    'desc' => 'Pengurang hutang dari saldo uang muka yang telah disetor',
                    'posisi' => 'KREDIT',
                    'picker_title' => 'Penyelesaian Uang Muka Pembelian (DP)',
                    'fallback_code' => '1122',
                    'fallback_nama' => 'Penyelesaian Uang Muka (DP)',
                    'default_urutan' => 5,
                ],
            ];

            $apRows = collect($apDefs)->map(function ($def, $kunci) use ($apSettings, $coaList) {
                $setting = $apSettings[$kunci] ?? null;
                $coa = $setting ? $coaList->firstWhere('coa_id', $setting->nilai_coa_id) : null;
                $def['setting'] = $setting;
                $def['coa_id'] = $setting?->nilai_coa_id;
                $def['kode_akun'] = $coa->kode_akun ?? $def['fallback_code'];
                $def['nama_akun'] = $coa->nama_akun ?? $def['fallback_nama'];
                $def['urutan'] = (int) ($setting->urutan ?? $def['default_urutan']);
                return $def;
            })->sortBy('urutan')->values();
        @endphp
        <div class="tab-content" id="tab-pembelian">
            <div class="workbench-dual-container">
                {{-- SISI KIRI: ATURAN AKUN BUKU BESAR DENGAN REORDERING POSISI --}}
                <div class="rules-card-container">
                    <div class="rules-card-header">
                        <div>
                            <div class="rules-header-title">
                                <h2>Aturan Akun Pembelian &amp; Hutang (AP)</h2>
                            </div>
                            <div class="rules-header-desc">Gunakan tombol ▲ / ▼ untuk mengatur urutan posting jurnal pembukuan. Rekening akun dipilih via tombol COA.</div>
                        </div>
                        <span class="rules-counter-badge">5 Komponen Terpetakan</span>
                    </div>

                    <div class="rules-rows-wrap" id="wrap_rules_PEMBELIAN">
                        @foreach($apRows as $idx => $r)
                        <div class="rule-row-box" data-kunci="{{ $r['kunci'] }}" draggable="true">
                            {{-- Reorder Gripper & Up/Down Buttons --}}
                            <div class="rule-reorder-col">
                                <span class="row-seq-badge">#{{ $idx + 1 }}</span>
                                <div class="row-reorder-nav">
                                    <button type="button" class="btn-reorder-nav btn-up" onclick="moveRuleRow(this, -1)" title="Geser ke Atas" {{ $loop->first ? 'disabled' : '' }}>
                                        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg>
                                    </button>
                                    <button type="button" class="btn-reorder-nav btn-down" onclick="moveRuleRow(this, 1)" title="Geser ke Bawah" {{ $loop->last ? 'disabled' : '' }}>
                                        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </div>
                                <input type="hidden" name="urutan[{{ $r['kunci'] }}]" class="input-row-urutan" value="{{ $idx + 1 }}">
                            </div>

                            {{-- Info Komponen --}}
                            <div class="rule-meta-info">
                                <span class="rule-comp-title">{{ $r['nama'] }}</span>
                                <span class="rule-comp-desc">{{ $r['desc'] }}</span>
                            </div>

                            {{-- Posisi Solid Badge (Debet / Kredit) --}}
                            <div class="rule-pos-col">
                                <span class="rule-side-badge {{ strtolower($r['posisi']) }}">{{ $r['posisi'] }}</span>
                            </div>

                            {{-- Tombol Pemilihan COA --}}
                            <div class="rule-coa-action">
                                <input type="hidden" name="settings[{{ $r['kunci'] }}]" id="input_{{ $r['kunci'] }}" value="{{ $r['coa_id'] }}">
                                <button type="button" class="btn-pick-coa" onclick="openCoaModalPicker('{{ $r['kunci'] }}', '{{ $r['picker_title'] }}')">
                                    <div class="coa-pick-main">
                                        <span class="coa-badge-code" id="badge_{{ $r['kunci'] }}">{{ $r['kode_akun'] }}</span>
                                        <span class="coa-name-text" id="name_{{ $r['kunci'] }}">{{ $r['nama_akun'] }}</span>
                                    </div>
                                    <span class="coa-pick-tag">Pilih ▾</span>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- INFO AKUN PAJAK DI MASTER PAJAK --}}
                    <div class="rules-tax-banner">
                        <div class="tax-banner-left">
                            <span style="font-size: 1.15rem; color: var(--pj-crimson);">📌</span>
                            <div>
                                <div class="tax-banner-title">Pengaturan Akun Pajak (PPN &amp; Potongan PPh)</div>
                                <div class="tax-banner-desc">Setiap jenis pajak (PPN Masukan, PPh 22, PPh 23, PPh 4(2)) rekening COA-nya dikelola langsung di menu Master Data &gt; Master Pajak &amp; Tarif.</div>
                            </div>
                        </div>
                        <a href="{{ route('master.pajak.index') }}" target="_blank" class="btn-tax-link">
                            <span>Buka Master Pajak</span>
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                </div>

                {{-- SISI KANAN: WORKBENCH FORMAT & LIVE VOUCHER SLIP DENGAN URUTAN REAL-TIME --}}
                <div class="simulator-card-container">
                    {{-- SECTION A: PILIHAN FORMAT KETERANGAN --}}
                    <div class="format-box-wrap">
                        <div class="format-box-header">
                            <span class="format-box-title">Susunan Narasi Jurnal</span>
                            <span class="format-box-sub">Template Otomatis</span>
                        </div>

                        <div class="preset-options-list">
                            <label class="preset-card-item {{ (!$isApCustom && $apTemplateVal === $apPresetStandard) ? 'selected' : '' }}">
                                <input type="radio" name="preset_ap" value="{{ $apPresetStandard }}" {{ (!$isApCustom && $apTemplateVal === $apPresetStandard) ? 'checked' : '' }}>
                                <div>
                                    <div class="preset-item-title">Format Lengkap (Rekomendasi)</div>
                                    <div class="preset-item-desc">Supplier, kuantitas barang, dan harga satuan.</div>
                                </div>
                            </label>

                            <label class="preset-card-item {{ (!$isApCustom && $apTemplateVal === $apPresetSimple) ? 'selected' : '' }}">
                                <input type="radio" name="preset_ap" value="{{ $apPresetSimple }}" {{ (!$isApCustom && $apTemplateVal === $apPresetSimple) ? 'checked' : '' }}>
                                <div>
                                    <div class="preset-item-title">Format Ringkas Faktur</div>
                                    <div class="preset-item-desc">Hanya nama supplier dan nomor faktur tagihan.</div>
                                </div>
                            </label>

                            <label class="preset-card-item {{ $isApCustom ? 'selected' : '' }}">
                                <input type="radio" name="preset_ap" value="CUSTOM" {{ $isApCustom ? 'checked' : '' }}>
                                <div>
                                    <div class="preset-item-title">Kustom Manual</div>
                                    <div class="preset-item-desc">Tulis susunan kalimat sendiri dengan tombol cepat tag variabel.</div>
                                </div>
                            </label>
                        </div>

                        {{-- KOTAK KUSTOM MANUAL --}}
                        <div class="custom-input-wrap" id="customBox_PEMBELIAN" style="{{ $isApCustom ? '' : 'display: none;' }}">
                            <div class="custom-input-top">
                                <span class="custom-input-heading">Variabel Cepat:</span>
                                <div class="quick-chips-group">
                                    <button type="button" class="quick-chip-btn" data-target="setting_PEMBELIAN_TEMPLATE_KET" data-tag="{supplier}">+ Supplier</button>
                                    <button type="button" class="quick-chip-btn" data-target="setting_PEMBELIAN_TEMPLATE_KET" data-tag="{nama_barang}">+ Barang</button>
                                    <button type="button" class="quick-chip-btn" data-target="setting_PEMBELIAN_TEMPLATE_KET" data-tag="{qty}">+ Qty</button>
                                    <button type="button" class="quick-chip-btn" data-target="setting_PEMBELIAN_TEMPLATE_KET" data-tag="{satuan}">+ Satuan</button>
                                    <button type="button" class="quick-chip-btn" data-target="setting_PEMBELIAN_TEMPLATE_KET" data-tag="{harga}">+ Harga</button>
                                    <button type="button" class="quick-chip-btn" data-target="setting_PEMBELIAN_TEMPLATE_KET" data-tag="{no_faktur}">+ No Faktur</button>
                                </div>
                            </div>
                            <input type="text" name="settings[PEMBELIAN_TEMPLATE_KET]" id="setting_PEMBELIAN_TEMPLATE_KET" class="template-text-field" value="{{ $apTemplateVal }}" placeholder="Ketik format keterangan...">
                        </div>
                    </div>

                    {{-- SECTION B: VOUCHER SLIP DENGAN TABEL URUTAN JURNAL UMUM --}}
                    <div class="voucher-slip-card">
                        <div class="voucher-card-top">
                            <div class="voucher-top-left">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span class="voucher-top-heading">Simulasi Posisi Jurnal Umum (Urutan Pembukuan)</span>
                            </div>
                            <span class="voucher-live-dot">● LIVE UPDATE</span>
                        </div>

                        <div class="voucher-card-inner">
                            <div class="voucher-narasi-container">
                                <div class="voucher-narasi-caption">Keterangan Narasi Jurnal:</div>
                                <div class="voucher-narasi-value" id="preview_PEMBELIAN_TEMPLATE_KET">-</div>
                            </div>

                            <table class="voucher-ledger-table" id="slip_table_PEMBELIAN">
                                <thead>
                                    <tr>
                                        <th style="width: 22%;">Posisi</th>
                                        <th style="width: 78%;">Rekening Akun Buku Besar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($apRows as $r)
                                    <tr data-slip-kunci="{{ $r['kunci'] }}">
                                        <td><span class="slip-pos-badge {{ strtolower($r['posisi']) }}">{{ $r['posisi'] }}</span></td>
                                        <td class="slip-coa-text"><span class="slip-coa-code" id="slip_badge_{{ $r['kunci'] }}">{{ $r['kode_akun'] }}</span> &middot; <span id="slip_name_{{ $r['kunci'] }}">{{ $r['nama_akun'] }}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="voucher-card-footer">
                            <span>Otomatisasi: Verifikasi Tagihan AP</span>
                            <span>Singkong &middot; 5.000 kg @2.400</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>

    {{-- MODAL SEARCH & PICKER COA (MACOS WINDOW) --}}
    @include('keuangan.pengaturan_jurnal.partials.modal-coa-picker')
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/keuangan/pengaturan-jurnal/pengaturan-jurnal.js') }}?v={{ time() }}"></script>
@endpush
