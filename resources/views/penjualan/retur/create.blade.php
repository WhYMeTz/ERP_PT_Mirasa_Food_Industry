@extends('layouts.app')

@section('title', 'Pencatatan Retur Penjualan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/retur/retur-create.css') }}">
@endpush

@section('content')
<div class="retur-create-container">
    {{-- HEADER & BREADCRUMB --}}
    <div class="retur-header-row">
        <div>
            <a href="{{ $selectedSo ? route('penjualan.so.show', $selectedSo->so_id) : route('penjualan.retur.index') }}" class="retur-back-btn">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali</span>
            </a>
            <h1 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0;">Pencatatan Retur Penjualan</h1>
            <p style="font-size: 0.8rem; color: #64748b; margin: 0.2rem 0 0 0;">
                Catat pengembalian barang jadi dari customer, tentukan perlakuan fisik (stok kembali atau pemusnahan), dan penyesuaian komersial.
            </p>
        </div>
    </div>

    @if (session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.75rem 1rem; border-radius: 6px; font-size: 0.85rem; margin-bottom: 1.25rem;">
            <strong>Peringatan:</strong> {{ session('error') }}
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.75rem 1rem; border-radius: 6px; font-size: 0.85rem; margin-bottom: 1.25rem;">
            <strong>Terdapat kesalahan pada isian form:</strong>
            <ul style="margin: 0.25rem 0 0 1.25rem; padding: 0;">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('penjualan.retur.store') }}" method="POST" id="returCreateForm">
        @csrf

        {{-- KARTU 1: DOKUMEN PESANAN SUMBER (SO) --}}
        <div class="retur-card">
            <div class="retur-card-header">
                <h3 class="retur-card-title">
                    <svg width="18" height="18" fill="none" stroke="#0284c7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>1. Dokumen Pesanan Penjualan (SO) Sumber</span>
                </h3>
            </div>
            <div class="retur-card-body">
                <div class="retur-form-grid" style="grid-template-columns: 2fr 1fr 1fr;">
                    <div class="retur-form-group">
                        <label class="retur-label" for="so_id">Pilih Dokumen Pesanan (SO Terkirim) <span class="required">*</span></label>
                        <select name="so_id" id="so_id" class="retur-ctrl" required>
                            <option value="">-- Pilih Dokumen SO --</option>
                            @foreach ($eligibleSos as $so)
                                <option value="{{ $so->so_id }}" {{ ($selectedSo && $selectedSo->so_id == $so->so_id) || old('so_id') == $so->so_id ? 'selected' : '' }}>
                                    {{ $so->so_no }} &mdash; {{ $so->customer?->customer_nm ?: 'Pelanggan Umum' }} ({{ \Carbon\Carbon::parse($so->so_tgl)->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                        <span class="retur-hint">Menampilkan seluruh SO berstatus Terkirim (Partial/Completed) yang memenuhi syarat retur.</span>
                    </div>

                    <div class="retur-form-group">
                        <label class="retur-label">Surat Jalan Terkait</label>
                        <input type="text" class="retur-ctrl" readonly id="infoSjNo" value="{{ $selectedSo?->surat_jalan_no ?: ($selectedSo?->pengeluaran?->first()?->pengeluaran_no ?: '-') }}" style="background: #f8fafc; font-family: monospace;">
                    </div>

                    <div class="retur-form-group">
                        <label class="retur-label">Faktur Penjualan Terkait</label>
                        <input type="text" class="retur-ctrl" readonly id="infoFakturNo" value="{{ $selectedSo?->faktur_no ?: ($selectedSo?->faktur?->first()?->faktur_no ?: '-') }}" style="background: #f8fafc; font-family: monospace;">
                    </div>
                </div>

                {{-- INFO BOX DETAIL PELANGGAN & PESANAN --}}
                <div id="soInfoBox" class="so-info-box" style="{{ $selectedSo ? 'display: grid;' : 'display: none;' }}">
                    <div class="so-info-item">
                        <span class="so-info-lbl">Nomor SO:</span>
                        <span class="so-info-val" id="infoSoNo">{{ $selectedSo?->so_no ?: '-' }}</span>
                    </div>
                    <div class="so-info-item">
                        <span class="so-info-lbl">Mitra Customer:</span>
                        <span class="so-info-val" id="infoCustNm">{{ $selectedSo?->customer?->customer_nm ?: '-' }}</span>
                    </div>
                    <div class="so-info-item">
                        <span class="so-info-lbl">Tanggal Terbit SO:</span>
                        <span class="so-info-val" id="infoSoTgl">{{ $selectedSo?->so_tgl ? \Carbon\Carbon::parse($selectedSo->so_tgl)->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div class="so-info-item">
                        <span class="so-info-lbl">Status Tagihan:</span>
                        <span class="so-info-val" style="color: #059669;">{{ $selectedSo?->status_cd ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 2: PARAMETER & KETENTUAN RETUR --}}
        <div class="retur-card">
            <div class="retur-card-header">
                <h3 class="retur-card-title">
                    <svg width="18" height="18" fill="none" stroke="#059669" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>2. Parameter & Ketentuan Retur</span>
                </h3>
            </div>
            <div class="retur-card-body">
                <div class="retur-form-grid">
                    <div class="retur-form-group">
                        <label class="retur-label" for="retur_tgl">Tanggal Retur <span class="required">*</span></label>
                        <input type="date" name="retur_tgl" id="retur_tgl" class="retur-ctrl" value="{{ old('retur_tgl', date('Y-m-d')) }}" required>
                    </div>

                    <div class="retur-form-group">
                        <label class="retur-label" for="gudang_id">Gudang Penerima Masuk <span class="required">*</span></label>
                        <select name="gudang_id" id="gudang_id" class="retur-ctrl" required>
                            @foreach ($gudangs as $gdg)
                                <option value="{{ $gdg->gudang_id }}" {{ old('gudang_id', $defaultGudangId) == $gdg->gudang_id ? 'selected' : '' }}>
                                    {{ $gdg->gudang_nm }} ({{ $gdg->gudang_cd }})
                                </option>
                            @endforeach
                        </select>
                        <span class="retur-hint">Lokasi fisik barang retur yang layak simpan diterima kembali.</span>
                    </div>

                    <div class="retur-form-group">
                        <label class="retur-label" for="tindakan_cd">Tindakan Komersial Tagihan <span class="required">*</span></label>
                        <select name="tindakan_cd" id="tindakan_cd" class="retur-ctrl" required>
                            <option value="POTONG_TAGIHAN" {{ old('tindakan_cd', 'POTONG_TAGIHAN') == 'POTONG_TAGIHAN' ? 'selected' : '' }}>
                                Potong Tagihan Faktur (Pengurangan Piutang)
                            </option>
                            <option value="TUKAR_GULING" {{ old('tindakan_cd') == 'TUKAR_GULING' ? 'selected' : '' }}>
                                Tukar Guling / Ganti Barang Fisik Baru
                            </option>
                        </select>
                        <span class="retur-hint">Kebijakan finansial terhadap nilai barang yang dikembalikan.</span>
                    </div>

                    <div class="retur-form-group">
                        <label class="retur-label" for="alasan_txt">Alasan / Catatan Pengembalian</label>
                        <input type="text" name="alasan_txt" id="alasan_txt" class="retur-ctrl" placeholder="Contoh: Kemasan penyok saat pengiriman ekspedisi" value="{{ old('alasan_txt') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 3: RINCIAN BARANG YANG DIRETUR & PERLAKUAN FISIK --}}
        <div class="retur-card">
            <div class="retur-card-header">
                <h3 class="retur-card-title">
                    <svg width="18" height="18" fill="none" stroke="#d97706" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>3. Rincian Barang & Disposisi Fisik</span>
                </h3>
                <span style="font-size: 0.775rem; color: #64748b;">Pilih perlakuan apakah barang <strong>Disimpan Kembali</strong> ke stok atau <strong>Dimusnahkan Resmi</strong>.</span>
            </div>
            <div style="overflow-x: auto;">
                <table class="retur-table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">No</th>
                            <th style="text-align: left; min-width: 180px;">Produk Jadi (FG)</th>
                            <th style="text-align: right; width: 85px;">Dikirim</th>
                            <th style="text-align: right; width: 95px;">Bisa Diretur</th>
                            <th style="text-align: center; width: 115px;">Qty Retur</th>
                            <th style="text-align: center; width: 70px;">Satuan</th>
                            <th style="text-align: right; width: 110px;">Harga Satuan</th>
                            <th style="text-align: left; width: 230px;">Perlakuan Fisik (Disposisi)</th>
                            <th style="text-align: left; width: 150px;">Kondisi Barang</th>
                            <th style="text-align: right; width: 125px;">Subtotal Nilai</th>
                        </tr>
                    </thead>
                    <tbody id="returItemsTbody">
                        @if ($selectedSo && count($itemsData) > 0)
                            @foreach ($itemsData as $idx => $item)
                                <tr class="retur-item-row" data-sodtl-id="{{ $item['sodtl_id'] }}" data-price="{{ $item['effective_price'] }}" data-max="{{ $item['max_returnable'] }}">
                                    <td style="text-align: center; color: #64748b; font-weight: 700;">{{ $idx + 1 }}</td>
                                    <td>
                                        <strong style="color: #0f172a; display: block;">{{ $item['barang_nm'] }}</strong>
                                        <span style="font-size: 0.725rem; color: #0284c7; font-family: monospace;">{{ $item['barang_cd'] }}</span>
                                        <input type="hidden" name="items[{{ $idx }}][sodtl_id]" value="{{ $item['sodtl_id'] }}">
                                    </td>
                                    <td style="text-align: right; font-family: monospace; font-weight: 700; color: #475569;">
                                        {{ number_format($item['kirim_qty'], 0) }}
                                    </td>
                                    <td style="text-align: right; font-family: monospace; font-weight: 700; color: #059669;">
                                        {{ number_format($item['max_returnable'], 0) }} {{ $item['satuan_nm'] }}
                                    </td>
                                    <td style="width: 115px;">
                                        <input type="number" step="any" min="0" max="{{ $item['max_returnable'] }}"
                                            name="items[{{ $idx }}][qty_retur]"
                                            class="retur-ctrl qty-retur-input"
                                            value="{{ old("items.{$idx}.qty_retur", 0) }}"
                                            style="text-align: right; font-weight: 800; font-family: monospace;"
                                            data-idx="{{ $idx }}">
                                    </td>
                                    <td style="text-align: center; color: #475569; font-weight: 600;">
                                        {{ $item['satuan_nm'] }}
                                    </td>
                                    <td style="text-align: right; font-family: monospace; color: #475569;">
                                        {{ $item['formatted_price'] }}
                                    </td>
                                    <td style="width: 230px;">
                                        <div class="disposisi-card-group">
                                            <label class="disposisi-option opt-simpan">
                                                <input type="radio" name="items[{{ $idx }}][perlakuan_cd]" value="MASUK_PERSEDIAAN" {{ old("items.{$idx}.perlakuan_cd", 'MASUK_PERSEDIAAN') === 'MASUK_PERSEDIAAN' ? 'checked' : '' }} class="disposisi-radio" data-idx="{{ $idx }}">
                                                <span>Gunakan Kembali (Masuk Stok)</span>
                                            </label>
                                            <label class="disposisi-option opt-musnah">
                                                <input type="radio" name="items[{{ $idx }}][perlakuan_cd]" value="DIMUSNAHKAN" {{ old("items.{$idx}.perlakuan_cd") === 'DIMUSNAHKAN' ? 'checked' : '' }} class="disposisi-radio" data-idx="{{ $idx }}">
                                                <span>Dimusnahkan (Scrap / Afkir)</span>
                                            </label>
                                        </div>
                                    </td>
                                    <td style="width: 150px;">
                                        <select name="items[{{ $idx }}][kondisi_cd]" class="retur-ctrl" style="font-size: 0.775rem;">
                                            <option value="RUSAK_KEMASAN" {{ old("items.{$idx}.kondisi_cd") == 'RUSAK_KEMASAN' ? 'selected' : '' }}>Kemasan Rusak / Cacat</option>
                                            <option value="REMUK_MELEMPEM" {{ old("items.{$idx}.kondisi_cd") == 'REMUK_MELEMPEM' ? 'selected' : '' }}>Remuk / Melempem</option>
                                            <option value="KADALUARSA" {{ old("items.{$idx}.kondisi_cd") == 'KADALUARSA' ? 'selected' : '' }}>Kadaluarsa (Expired)</option>
                                            <option value="SALAH_KIRIM" {{ old("items.{$idx}.kondisi_cd") == 'SALAH_KIRIM' ? 'selected' : '' }}>Salah Varian / Lebih</option>
                                            <option value="REJECT_CUSTOMER" {{ old("items.{$idx}.kondisi_cd") == 'REJECT_CUSTOMER' ? 'selected' : '' }}>Reject Mitra / Customer</option>
                                        </select>
                                    </td>
                                    <td style="text-align: right; font-family: monospace; font-weight: 800; color: #0f172a;" class="row-subtotal">
                                        Rp 0
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                                    Silakan pilih Dokumen Pesanan (SO) di atas untuk memuat rincian barang yang dapat diretur.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- FOOTER KALKULASI & TOTALS --}}
            <div class="retur-summary-footer">
                <div class="summary-left">
                    {{-- WARNING BANNER PEMUSNAHAN --}}
                    <div id="musnahWarningBanner" class="musnah-warning-banner" style="display: none;">
                        <div style="display: flex; gap: 0.5rem; align-items: flex-start;">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0; margin-top: 1px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <div>
                                <strong style="display: block; font-size: 0.85rem;">Peringatan Pemusnahan Barang (<span id="countMusnahText">0</span> unit):</strong>
                                <span style="font-size: 0.775rem;">Barang yang ditandai <strong>Dimusnahkan</strong> tidak akan dimasukkan kembali ke persediaan gudang. Sistem akan otomatis menerbitkan <strong>Berita Acara Pemusnahan Barang Retur (Scrap Report)</strong> resmi untuk ditandatangani QC & Pimpinan Pabrik.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="summary-right">
                    <div class="summary-row">
                        <span>Total Kuantitas Retur:</span>
                        <strong id="dispTotQtyRetur">0 Karton / Satuan</strong>
                    </div>
                    <div class="summary-row" style="color: #059669;">
                        <span>&bull; Disimpan Kembali ke Gudang:</span>
                        <strong id="dispTotQtyMasuk">0 Karton</strong>
                    </div>
                    <div class="summary-row" style="color: #dc2626;">
                        <span>&bull; Dimusnahkan (Scrap):</span>
                        <strong id="dispTotQtyMusnah">0 Karton</strong>
                    </div>
                    <div class="summary-row total-nilai-row">
                        <span>Total Nilai Retur:</span>
                        <span id="dispTotNilai">Rp 0</span>
                    </div>
                </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 1rem 1.25rem; display: flex; justify-content: flex-end; gap: 0.75rem; align-items: center;">
                <a href="{{ $selectedSo ? route('penjualan.so.show', $selectedSo->so_id) : route('penjualan.retur.index') }}" class="btn btn-secondary" style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #475569; font-weight: 700; font-size: 0.85rem; padding: 0.55rem 1.15rem; border-radius: 6px; text-decoration: none;">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary" style="background: #059669; border-color: #047857; color: #ffffff; font-weight: 700; font-size: 0.875rem; padding: 0.6rem 1.5rem; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.45rem; box-shadow: 0 1px 3px rgba(5, 150, 105, 0.3);">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan & Proses Retur</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/penjualan/retur/retur-create.js') }}"></script>
@endpush
