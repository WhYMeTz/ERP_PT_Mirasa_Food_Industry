@extends('layouts.app')

@section('title', 'Form Retur Pembelian ke Supplier - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/retur/retur-create.css') }}?v={{ file_exists(public_path('css/gudang/retur/retur-create.css')) ? filemtime(public_path('css/gudang/retur/retur-create.css')) : time() }}">
@endpush

@section('content')
    {{-- 1. CHEVRON BREADCRUMB NAVIGATION (GAYA AKUNTANSI ENTERPRISE) --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
        <nav class="erp-chevron-bar" aria-label="Breadcrumb">
            <a href="{{ route('gudang.po.index') }}" class="erp-chevron-item">
                <span>Pembelian</span>
            </a>
            <span class="erp-chevron-sep">
                <svg width="8" height="20" viewBox="0 0 8 20" fill="none">
                    <path d="M1 2L6 10L1 18" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" />
                </svg>
            </span>
            <a href="{{ route('gudang.retur.index') }}" class="erp-chevron-item">
                <span>Retur Pembelian</span>
            </a>
            <span class="erp-chevron-sep">
                <svg width="8" height="20" viewBox="0 0 8 20" fill="none">
                    <path d="M1 2L6 10L1 18" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" />
                </svg>
            </span>
            <span class="erp-chevron-item active">
                <span>+ Form Retur Pembelian ke Supplier</span>
            </span>
            <span class="erp-chevron-end">
                <svg width="10" height="32" viewBox="0 0 10 32" fill="none">
                    <path d="M1 1L8 16L1 31" stroke="#dce4ec" stroke-width="1.5" />
                </svg>
            </span>
        </nav>

        {{-- Live KPI Widget Ramping di Siku Kanan Atas (Identik PO & Catat Tagihan) --}}
        <div class="po-live-kpi-widget">
            <div>
                <div class="po-live-kpi-title">ESTIMASI NILAI RETUR:</div>
                <div class="po-live-kpi-val" id="topLiveGrandTotal">Rp 0</div>
            </div>
            <div style="height: 28px; width: 1.5px; background: #e2e8f0;"></div>
            <div style="font-size: 0.775rem; line-height: 1.35; color: #475569;">
                <div>Item: <strong id="topLiveItemCount" style="color: #0f172a; font-family: monospace;">0 Item</strong></div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 0.7rem; color: #dc2626; font-weight: 700;">🔴 Mode Outbound Retur</span>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div style="background: #fef2f2; border: 1.5px solid #fca5a5; padding: 0.85rem 1.15rem; border-radius: 8px; margin-bottom: 0.85rem; color: #991b1b; font-size: 0.825rem;">
            <div style="font-weight: 700; margin-bottom: 0.25rem;">Gagal memproses dokumen retur pembelian:</div>
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('gudang.retur.store') }}" id="returForm">
        @csrf

        {{-- 2. TOOLBAR CARD RAMPING: 4 KOLOM PRESISI & SWITCHER RETUR --}}
        <div class="po-toolbar-card">
            {{-- Mode Switcher & Tindakan Retur di Header Bar --}}
            <div class="po-mode-toolbar-wrap">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <span style="font-size: 0.85rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 0.4rem;">
                        <span>🚚</span> <span>Dokumen Retur Outbound &amp; Rekanan</span>
                    </span>
                    <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; font-size: 0.725rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;">
                        No: {{ $nextReturNo }}
                    </span>
                    <input type="hidden" name="retur_no" id="retur_no" value="{{ $nextReturNo }}">
                </div>

                {{-- Radio Pills: Sumber Asal & Tindakan Retur --}}
                <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    {{-- Sumber Asal Barang: Dari PO vs Non-PO --}}
                    <div class="retur-pill-group" title="Sumber Dokumen Asal">
                        <label class="retur-pill-label {{ old('source_type', $selectedPo ? 'PO' : 'PO') === 'PO' ? 'active' : '' }}" id="pillSourcePo">
                            <input type="radio" name="source_type" value="PO" {{ old('source_type', $selectedPo ? 'PO' : 'PO') === 'PO' ? 'checked' : '' }} onchange="onSourceTypeChanged()">
                            <span>📋 Dari PO Terkait</span>
                        </label>
                        <label class="retur-pill-label {{ old('source_type') === 'NON_PO' ? 'active' : '' }}" id="pillSourceNonPo">
                            <input type="radio" name="source_type" value="NON_PO" {{ old('source_type') === 'NON_PO' ? 'checked' : '' }} onchange="onSourceTypeChanged()">
                            <span>📦 Non-PO (Bebas)</span>
                        </label>
                    </div>

                    {{-- Tindakan Retur: REPLACE vs CREDIT_NOTE --}}
                    <div class="retur-pill-group" title="Kesepakatan Penanganan Retur">
                        <label class="retur-pill-label {{ old('tindakan_cd', 'REPLACE') === 'REPLACE' ? 'active-success' : '' }}" id="pillTindakanReplace">
                            <input type="radio" name="tindakan_cd" value="REPLACE" {{ old('tindakan_cd', 'REPLACE') === 'REPLACE' ? 'checked' : '' }} onchange="onTindakanChanged()">
                            <span>🔄 Kirim Ulang (Replace)</span>
                        </label>
                        <label class="retur-pill-label {{ old('tindakan_cd') === 'CREDIT_NOTE' ? 'active-amber' : '' }}" id="pillTindakanCredit">
                            <input type="radio" name="tindakan_cd" value="CREDIT_NOTE" {{ old('tindakan_cd') === 'CREDIT_NOTE' ? 'checked' : '' }} onchange="onTindakanChanged()">
                            <span>💰 Potong Tagihan (Credit Note)</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Banner Penjelasan Tindakan Retur Terpilih --}}
            <div id="tindakanExplanation" style="margin-bottom: 0.75rem; padding: 0.45rem 0.85rem; border-radius: 6px; font-size: 0.75rem; border: 1px solid #bbf7d0; background: #f0fdf4; color: #065f46; line-height: 1.4;">
                <strong>🔄 Minta Kirim Ulang (Pengganti):</strong> Kuota penerimaan PO akan dibuka kembali ke status <strong>PARTIAL</strong> agar kiriman barang baru dapat diterima di sistem nanti.
            </div>

            {{-- GRID UNIFIED 4 KOLOM (SIMETRIS, PRESISI, IDENTIK WORKSTATION LAIN) --}}
            <div class="po-toolbar-grid">
                {{-- [C1] Mitra Supplier --}}
                <div>
                    <label class="po-field-label">
                        Mitra Supplier <span style="color:#ef4444;">*</span>
                    </label>
                    <select name="supplier_id" id="supplierSelect" class="form-control po-field-select" required>
                        <option value="">-- Pilih Rekanan Supplier --</option>
                        @foreach ($supplierList as $sup)
                            <option value="{{ $sup->supplier_id }}" {{ old('supplier_id', $selectedPo?->supplier_id) == $sup->supplier_id ? 'selected' : '' }}>
                                {{ $sup->supplier_nm }} ({{ $sup->supplier_cd }})
                            </option>
                        @endforeach
                    </select>
                    <small style="color: #64748b; font-size: 0.7rem; display: block; margin-top: 2px;">
                        Pihak vendor/petani penerima barang retur.
                    </small>
                </div>

                {{-- [C2] Dokumen PO Terkait --}}
                <div id="poSelectGroup">
                    <label class="po-field-label">
                        Dokumen Purchase Order (PO) <span style="color:#ef4444;">*</span>
                    </label>
                    <select name="po_id" id="poSelect" class="form-control po-field-select" onchange="onPoSelected()">
                        <option value="">-- Pilih Dokumen PO Asal --</option>
                        @foreach ($poList as $po)
                            <option value="{{ $po->po_id }}" 
                                    data-supplier-id="{{ $po->supplier_id }}"
                                    data-gudang-id="{{ $po->gudang_id }}"
                                    {{ old('po_id', $selectedPo?->po_id) == $po->po_id ? 'selected' : '' }}>
                                {{ $po->po_no }} - {{ $po->supplier?->supplier_nm }} ({{ $po->po_tgl?->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>
                    <small style="color: #0284c7; font-size: 0.7rem; display: block; margin-top: 2px;">
                        ⚡ Pilih PO untuk auto-load barang &amp; surat jalan.
                    </small>
                </div>

                {{-- [C3] Gudang / Cabang Asal Retur --}}
                <div>
                    <label class="po-field-label">
                        Gudang / Cabang Asal <span style="color:#ef4444;">*</span>
                    </label>
                    @if (!empty($userGudangId))
                        @php $lockedGdg = $gudangList->firstWhere('gudang_id', $userGudangId); @endphp
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="text" class="form-control po-field-input" value="{{ $lockedGdg?->gudang_nm }} ({{ $lockedGdg?->gudang_cd }})" disabled style="background: #f1f5f9; font-weight: 600;">
                            <input type="hidden" name="gudang_id" id="gudang_id" value="{{ $userGudangId }}">
                        </div>
                    @else
                        <select name="gudang_id" id="gudang_id" class="form-control po-field-select" required onchange="onGudangChanged()">
                            @foreach ($gudangList as $gdg)
                                <option value="{{ $gdg->gudang_id }}" {{ old('gudang_id', $selectedPo?->gudang_id) == $gdg->gudang_id ? 'selected' : '' }}>
                                    {{ $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                    <small style="color: #64748b; font-size: 0.7rem; display: block; margin-top: 2px;">
                        Lokasi fisik tempat stok batch dikeluarkan.
                    </small>
                </div>

                {{-- [C4] Tanggal Retur & Surat Jalan Asal --}}
                <div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                        <div>
                            <label class="po-field-label">
                                Tgl Retur <span style="color:#ef4444;">*</span>
                            </label>
                            <input type="date" id="retur_tgl" name="retur_tgl" value="{{ old('retur_tgl', date('Y-m-d')) }}" class="form-control po-field-input" required>
                        </div>
                        <div>
                            <label class="po-field-label" title="No Surat Jalan Pengiriman Asal saat barang tiba">
                                Surat Jalan Asal
                            </label>
                            <input type="text" name="suratjalan_supplier_no" id="suratjalan_supplier_no" value="{{ old('suratjalan_supplier_no', $defaultSj ?? '') }}" placeholder="No SJ GRN" class="form-control po-field-input">
                        </div>
                    </div>
                    <small style="color: #64748b; font-size: 0.7rem; display: block; margin-top: 2px;">
                        Tanggal fisik armada &amp; nomor referensi surat jalan.
                    </small>
                </div>
            </div>
        </div>

        {{-- 3. QUICK CATALOG ADD BAR: PEMILIHAN BARANG CEPAT --}}
        <div class="retur-catalog-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.45rem; flex-wrap: wrap; gap: 0.5rem;">
                <span style="font-size: 0.8rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.35rem;">
                    <span>🔍</span> <span>Katalog Barang &amp; Filter Cepat</span>
                </span>
                {{-- Category Chips --}}
                <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                    <button type="button" class="btn-chip active" id="chipSemua" onclick="filterBarangCatalog('ALL')">Semua</button>
                    <button type="button" class="btn-chip" id="chipBB" onclick="filterBarangCatalog('BAHAN_BAKU')">Bahan Baku</button>
                    <button type="button" class="btn-chip" id="chipBP" onclick="filterBarangCatalog('BAHAN_PENOLONG')">Bahan Penolong</button>
                    <button type="button" class="btn-chip" id="chipPACK" onclick="filterBarangCatalog('KEMASAN')">Kemasan</button>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 0.65rem; align-items: center;">
                <div>
                    <select id="quickBarangSelect" class="form-control" style="height: 34px; font-size: 0.8rem; font-weight: 600;">
                        <option value="">-- Pilih Barang dari Katalog --</option>
                        @foreach ($barangList as $b)
                            <option value="{{ $b->barang_id }}" 
                                    data-kategori="{{ $b->kategori_kelompok }}"
                                    data-satuan="{{ $b->satuanDasar?->satuan_nm }}"
                                    data-harga="{{ $b->harga_beli_standar || 0 }}">
                                {{ $b->barang_nm }} ({{ $b->barang_cd }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="text" id="catalogSearchInput" placeholder="Ketik nama / kode barang..." class="form-control" style="height: 34px; font-size: 0.8rem;" oninput="searchCatalog()">
                </div>
                <div>
                    <button type="button" class="btn btn-primary" onclick="addSelectedBarangToTable()" style="background: #0f172a; height: 34px; font-weight: 700; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.35rem; padding: 0 0.85rem; border-radius: 6px;">
                        <span>+ Tambah ke Tabel</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- 4. FULL-WIDTH WORKSTATION TABLE CARD --}}
        <div class="po-workstation-table-card">
            <div class="po-workstation-table-header">
                <div>
                    <strong style="font-size: 0.875rem; color: #0f172a;">Rincian Item Fisik yang Diretur &amp; Alokasi Batch</strong>
                    <span style="font-size: 0.725rem; color: #64748b; margin-left: 0.5rem;">
                        💡 Kuantitas retur dibatasi oleh sisa saldo batch aktual di gudang
                    </span>
                </div>
                <div style="display: flex; gap: 0.35rem; align-items: center;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addRow()" style="background: #ffffff; border: 1px solid #cbd5e1; font-weight: 700; font-size: 0.75rem; border-radius: 4px; padding: 0.25rem 0.65rem;">
                        + Baris Kosong
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="resetItemsTable()" style="background: #ffffff; border: 1px solid #cbd5e1; color: #64748b; font-size: 0.75rem; border-radius: 4px; padding: 0.25rem 0.65rem;" title="Kosongkan Tabel">
                        Reset
                    </button>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="po-workstation-table" id="itemTable">
                    <thead>
                        <tr>
                            <th style="width: 35px; text-align: center;">No</th>
                            <th style="min-width: 220px;">Barang Baku / Penolong</th>
                            <th style="min-width: 190px;">Nomor Batch &amp; Sisa Fisik</th>
                            <th style="width: 110px; text-align: right;">Qty Retur</th>
                            <th style="width: 70px; text-align: center;">Satuan</th>
                            <th style="width: 120px; text-align: right;">Harga (Rp)</th>
                            <th style="width: 130px; text-align: right;">Subtotal (Rp)</th>
                            <th style="min-width: 170px;">Alasan Cacat / Reject</th>
                            <th style="width: 45px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemTableBody">
                        {{-- Baris item dirender secara dinamis oleh JS --}}
                    </tbody>
                    <tfoot>
                        <tr style="background: #f8fafc; font-weight: 700; border-top: 1.5px solid #cbd5e1;">
                            <td colspan="3" style="padding: 0.65rem 0.85rem; font-size: 0.8rem; color: #475569;">
                                Total Baris: <span id="totalItemCount" style="color: #0f172a; font-family: monospace;">0</span> Item
                            </td>
                            <td colspan="3" style="text-align: right; padding: 0.65rem 0.85rem; font-size: 0.8rem; color: #64748b;">
                                Total Nilai Retur:
                            </td>
                            <td style="text-align: right; padding: 0.65rem 0.85rem; font-size: 0.95rem; color: #dc2626; font-family: monospace;">
                                <span id="totalNominalText">Rp 0</span>
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- 5. BOTTOM SECTION: MEMO LAPANGAN KIRI & FINANCIAL STACK KANAN --}}
        <div class="po-bottom-grid">
            {{-- Sisi Kiri: Memo Lapangan & Standar HACCP --}}
            <div class="po-notes-card">
                <div style="margin-bottom: 0.75rem;">
                    <label for="alasan_txt" class="po-field-label">
                        Alasan Umum Retur / Catatan Memo Lapangan
                    </label>
                    <textarea name="alasan_txt" id="alasan_txt" rows="2" class="form-control" placeholder="Contoh: Ditemukan singkong busuk & berserat saat pembersihan di lini produksi..." style="font-size: 0.8rem; border-radius: 5px;">{{ old('alasan_txt') }}</textarea>
                </div>

                {{-- SOP Retur & HACCP Banner --}}
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.65rem 0.85rem; font-size: 0.75rem; color: #475569; line-height: 1.45;">
                    <strong style="color: #0f172a; display: block; margin-bottom: 0.25rem;">📋 Standar Operasional Retur PT Mirasa Food Industry:</strong>
                    <div style="margin-bottom: 0.35rem;">
                        &bull; <strong>Replace:</strong> Kuota PO kembali terbuka status <code>PARTIAL</code> agar pengiriman pengganti dapat diproses di penerimaan gudang.
                    </div>
                    <div style="margin-bottom: 0.35rem;">
                        &bull; <strong>Credit Note:</strong> Nilai retur otomatis menjadi nota kredit pemotong hutang faktur pada modul <code>Catat Tagihan</code>.
                    </div>
                    <div>
                        &bull; <strong>Berita Acara:</strong> Dokumen HACCP resmi penolakan bahan baku dapat dicetak langsung setelah retur berhasil disimpan.
                    </div>
                </div>
            </div>

            {{-- Sisi Kanan: Financial Stack Ringkasan & Tombol Eksekusi --}}
            <div class="po-financial-card">
                <div style="font-size: 0.775rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; color: #475569; margin-bottom: 0.65rem;">
                    Ringkasan Dokumen Retur
                </div>

                <div class="po-financial-row">
                    <span style="color: #64748b; font-weight: 600;">Total Item Diretur:</span>
                    <strong id="summaryTotalItem" style="font-family: monospace; font-size: 0.95rem; color: #1e293b;">0 Item</strong>
                </div>

                <div class="po-financial-row">
                    <span style="color: #64748b; font-weight: 600;">Total Fisik Dikembalikan:</span>
                    <strong id="summaryTotalQty" style="font-family: monospace; font-size: 0.95rem; color: #dc2626;">0,00</strong>
                </div>

                {{-- Grand Total Box (Merah Mencolok Khas Outbound Retur) --}}
                <div class="retur-grand-total-box">
                    <div>
                        <div style="font-size: 0.7rem; font-weight: 800; color: #991b1b; text-transform: uppercase;">
                            TOTAL ESTIMASI NILAI RETUR
                        </div>
                        <div style="font-size: 0.65rem; color: #b91c1c;">
                            Saldo stok batch dipotong otomatis &bull; Mutasi terbit
                        </div>
                    </div>
                    <div class="retur-grand-total-val" id="summaryGrandTotal">Rp 0</div>
                </div>

                {{-- Action Bar --}}
                <div class="po-action-bar">
                    <a href="{{ route('gudang.retur.index') }}" class="btn btn-secondary" style="font-size: 0.8rem; font-weight: 600; padding: 0.45rem 0.85rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; color: #475569; text-decoration: none;">
                        Batal
                    </a>

                    <button type="submit" id="btnSubmitRetur" class="btn btn-primary" style="background: #dc2626; border-color: #b91c1c; font-size: 0.85rem; font-weight: 700; padding: 0.5rem 1.25rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.45rem; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.25);">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Terbitkan Dokumen Retur</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        window.returConfig = {
            masterBarang: @json($barangList),
            batchesUrl: "{{ route('gudang.retur.batches') }}",
            poDataUrl: "{{ url('/gudang/retur/po-data') }}"
        };
    </script>
    <script src="{{ asset('js/gudang/retur/retur-create.js') }}?v={{ file_exists(public_path('js/gudang/retur/retur-create.js')) ? filemtime(public_path('js/gudang/retur/retur-create.js')) : time() }}"></script>
@endpush
