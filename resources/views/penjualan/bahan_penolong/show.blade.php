@extends('layouts.app')

@section('title', 'Detail Penjualan ' . $order->so_no . ' - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/bahan_penolong/bp-show.css') }}?v={{ filemtime(public_path('css/penjualan/bahan_penolong/bp-show.css')) }}">
@endpush

@section('content')
<div class="bp-show-container">
    {{-- TOP COMMAND HEADER --}}
    <div class="bp-header-row">
        <div>
            <a href="{{ route('penjualan.bahan-penolong.index') }}" class="bp-back-btn">
                &larr; Kembali ke Daftar Penjualan Bahan Penolong
            </a>
            <div class="bp-title-group">
                <h1 class="bp-title">{{ $order->so_no }}</h1>
                <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-size: 0.775rem; font-weight: 700; padding: 4px 10px; border-radius: 4px;">
                    Lunas &amp; Selesai
                </span>
                @if ($order->faktur_no)
                    <span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-size: 0.775rem; font-weight: 700; padding: 4px 10px; border-radius: 4px; font-family: monospace;">
                        Faktur: {{ $order->faktur_no }}
                    </span>
                @endif
            </div>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">
                Diterbitkan pada {{ \Carbon\Carbon::parse($order->so_tgl)->translatedFormat('d F Y') }} oleh {{ $order->created_by ?: 'System' }} &bull; Pembeli: <strong>{{ $order->supir_nm ?: ($order->customer?->customer_nm ?? 'Pelanggan Umum') }}</strong>
            </p>
        </div>

        <div style="display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('penjualan.bahan-penolong.cetak-nota', $order->so_id) }}" target="_blank" class="btn btn-secondary" style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #1e293b; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; border-radius: 6px;">
                Cetak Nota / Struk
            </a>
            <a href="{{ route('penjualan.bahan-penolong.create') }}" class="btn btn-primary" style="background: #059669; border-color: #059669; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 1rem; border-radius: 6px;">
                + Transaksi Baru
            </a>
        </div>
    </div>

    @if (session('success'))
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.85rem 1.15rem; margin-bottom: 1.25rem; color: #166534; font-size: 0.85rem;">
            {{ session('success') }}
        </div>
    @endif

    {{-- 3-COLUMN SUMMARY CARDS --}}
    <div class="bp-info-grid">
        {{-- CARD 1: DOKUMEN & TRANSAKSI --}}
        <div class="bp-card">
            <div class="bp-card-header">Identitas Transaksi</div>
            <div class="bp-card-body">
                <div class="bp-kv-row">
                    <span class="bp-kv-label">No. Transaksi</span>
                    <span class="bp-kv-val font-mono">{{ $order->so_no }}</span>
                </div>
                <div class="bp-kv-row">
                    <span class="bp-kv-label">Tanggal Transaksi</span>
                    <span class="bp-kv-val">{{ \Carbon\Carbon::parse($order->so_tgl)->translatedFormat('d F Y') }}</span>
                </div>
                <div class="bp-kv-row">
                    <span class="bp-kv-label">No. Surat Jalan</span>
                    <span class="bp-kv-val font-mono" style="color: #059669;">{{ $order->surat_jalan_no ?: '-' }}</span>
                </div>
                <div class="bp-kv-row">
                    <span class="bp-kv-label">No. Faktur Penjualan</span>
                    <span class="bp-kv-val font-mono" style="color: #0284c7;">{{ $order->faktur_no ?: '-' }}</span>
                </div>
                <div class="bp-kv-row">
                    <span class="bp-kv-label">Status Pembayaran</span>
                    <span class="bp-kv-val" style="color: #166534;">LUNAS ({{ $order->top_txt ?: 'TUNAI' }})</span>
                </div>
            </div>
        </div>

        {{-- CARD 2: PIHAK PEMBELI --}}
        <div class="bp-card">
            <div class="bp-card-header">Pihak Pembeli / Customer</div>
            <div class="bp-card-body">
                <div class="bp-kv-row">
                    <span class="bp-kv-label">Nama Pembeli</span>
                    <span class="bp-kv-val" style="font-weight: 700; color: #0f172a;">
                        {{ $order->supir_nm ?: ($order->customer?->customer_nm ?? 'Pelanggan Umum') }}
                    </span>
                </div>
                <div class="bp-kv-row">
                    <span class="bp-kv-label">Klasifikasi Customer</span>
                    <span class="bp-kv-val">{{ $order->customer?->klasifikasi ?? 'Umum / Eceran' }}</span>
                </div>
                <div class="bp-kv-row">
                    <span class="bp-kv-label">Alamat / Lokasi</span>
                    <span class="bp-kv-val">{{ $order->customer?->alamat_txt ?: 'Ambil Langsung di Pabrik' }}</span>
                </div>
            </div>
        </div>

        {{-- CARD 3: LOGISTIK & GUDANG --}}
        <div class="bp-card">
            <div class="bp-card-header">Gudang &amp; Mutasi Fisik</div>
            <div class="bp-card-body">
                <div class="bp-kv-row">
                    <span class="bp-kv-label">Gudang Asal Barang</span>
                    <span class="bp-kv-val">{{ $order->pengeluaran->first()?->gudang?->gudang_nm ?? 'PT Mirasa Food Industry' }}</span>
                </div>
                <div class="bp-kv-row">
                    <span class="bp-kv-label">Status Mutasi Stok</span>
                    <span class="bp-kv-val" style="color: #166534;">
                        Stok Bahan/Kemasan Terpotong (FIFO)
                    </span>
                </div>
                <div class="bp-kv-row">
                    <span class="bp-kv-label">Petugas / Kasir</span>
                    <span class="bp-kv-val">{{ $order->created_by ?: 'Kasir' }}</span>
                </div>
                <div class="bp-kv-row">
                    <span class="bp-kv-label">Catatan</span>
                    <span class="bp-kv-val" style="font-size: 0.775rem; color: #475569;">{{ $order->catatan_txt ?: '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL RINCIAN BARANG --}}
    <div class="bp-card" style="margin-bottom: 1.5rem;">
        <div class="bp-card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span>Rincian Bahan Penolong &amp; Bumbu yang Dijual</span>
            <span style="font-size: 0.775rem; color: #64748b;">{{ $order->details->count() }} Macam Item</span>
        </div>
        <div style="overflow-x: auto;">
            <table class="bp-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="width: 130px;">Kode Barang</th>
                        <th style="min-width: 240px;">Nama Bahan / Bumbu / Kemasan</th>
                        <th style="width: 120px; text-align: center;">Kategori</th>
                        <th style="width: 100px; text-align: right;">Jumlah</th>
                        <th style="width: 80px; text-align: left;">Satuan</th>
                        <th style="width: 130px; text-align: right;">Harga Satuan</th>
                        <th style="width: 90px; text-align: right;">Diskon (%)</th>
                        <th style="width: 140px; text-align: right;">Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totQty = 0;
                    @endphp
                    @foreach ($order->details as $idx => $dtl)
                        @php
                            $totQty += (float) $dtl->pesan_qty;
                            $diskonPct = (float) ($dtl->diskon_persen ?? 0);
                            $jenisNm = $dtl->barang?->jenisBarang?->jenis_barang_nm ?? 'Bahan Penolong';
                        @endphp
                        <tr>
                            <td style="text-align: center; color: #64748b;">{{ $idx + 1 }}</td>
                            <td style="font-family: monospace; font-weight: 600; color: #0284c7;">
                                {{ $dtl->barang?->barang_cd ?? '-' }}
                            </td>
                            <td>
                                <strong style="color: #0f172a; display: block;">{{ $dtl->barang?->barang_nm ?? '-' }}</strong>
                                @if ($dtl->catatan_txt)
                                    <span style="font-size: 0.725rem; color: #64748b;">{{ $dtl->catatan_txt }}</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px;">
                                    {{ $jenisNm }}
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 700; font-family: monospace; color: #0f172a;">
                                {{ number_format((float) $dtl->pesan_qty, 2) }}
                            </td>
                            <td style="color: #475569;">
                                {{ $dtl->satuan }}
                            </td>
                            <td style="text-align: right; font-family: monospace; color: #475569;">
                                Rp {{ number_format((float) $dtl->harga_satuan, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-family: monospace; color: {{ $diskonPct > 0 ? '#d97706' : '#94a3b8' }};">
                                {{ $diskonPct > 0 ? number_format($diskonPct, 1) . '%' : '-' }}
                            </td>
                            <td style="text-align: right; font-family: monospace; font-weight: 800; color: #0f172a;">
                                Rp {{ number_format((float) $dtl->subtotal_tagihan, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot style="background: #f8fafc; border-top: 2px solid #cbd5e1;">
                    <tr>
                        <td colspan="4" style="text-align: right; padding: 0.75rem 1rem; font-weight: 700; color: #475569;">
                            Total Kuantitas:
                        </td>
                        <td style="text-align: right; padding: 0.75rem 1rem; font-family: monospace; font-weight: 800; color: #0f172a;">
                            {{ number_format($totQty, 2) }}
                        </td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- SUMMARY FOOTER & FINANCIAL CALCULATION BOX --}}
        <div class="bp-footer-summary">
            <div class="bp-summary-left">
                <div style="font-size: 0.75rem; color: #94a3b8; line-height: 1.6;">
                    <div>Dibuat oleh: <strong style="color: #64748b;">{{ $order->created_by ?: 'Kasir' }}</strong> pada {{ $order->created_at ? \Carbon\Carbon::parse($order->created_at)->translatedFormat('d F Y H:i') : '-' }}</div>
                    <div>Dokumen Resmi: <strong style="color: #059669;">PENJUALAN BAHAN PENOLONG &amp; BUMBU</strong></div>
                    <div>Status Transaksi: <strong style="color: #059669;">LUNAS &amp; SELESAI</strong> &bull; Versi: v{{ $order->version_no ?? 1 }}</div>
                </div>
            </div>

            <div class="bp-calc-box">
                <div class="bp-calc-row">
                    <span style="color: #64748b;">Subtotal Nilai Bruto:</span>
                    <strong style="color: #0f172a; font-family: monospace;">Rp {{ number_format((float) $order->subtotal_bruto, 0, ',', '.') }}</strong>
                </div>

                @if ($order->diskon_total > 0)
                    <div class="bp-calc-row">
                        <span style="color: #64748b;">Akumulasi Diskon:</span>
                        <strong style="color: #d97706; font-family: monospace;">- Rp {{ number_format((float) $order->diskon_total, 0, ',', '.') }}</strong>
                    </div>
                @endif

                @if ($order->potongan_nominal > 0)
                    <div class="bp-calc-row">
                        <span style="color: #64748b;">Potongan Tambahan:</span>
                        <strong style="color: #dc2626; font-family: monospace;">- Rp {{ number_format((float) $order->potongan_nominal, 0, ',', '.') }}</strong>
                    </div>
                @endif

                <div class="bp-calc-row" style="border-top: 1px dashed #cbd5e1; padding-top: 0.35rem; margin-top: 0.25rem;">
                    <span style="font-weight: 600; color: #334155;">Dasar Pengenaan Pajak (DPP):</span>
                    <strong style="color: #0f172a; font-family: monospace;">Rp {{ number_format((float) $order->dpp_nominal, 0, ',', '.') }}</strong>
                </div>

                <div class="bp-calc-row">
                    <span style="color: #64748b;">PPN ({{ $order->ppn_persen }}%):</span>
                    <strong style="color: #2563eb; font-family: monospace;">+ Rp {{ number_format((float) $order->ppn_nominal, 0, ',', '.') }}</strong>
                </div>

                <div class="bp-calc-row total-row">
                    <span>Total Pembayaran:</span>
                    <span style="color: #059669; font-size: 1.25rem; font-family: monospace; font-weight: 800;">
                        Rp {{ number_format((float) $order->total_tagihan, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/penjualan/bahan_penolong/bp-show.js') }}?v={{ filemtime(public_path('js/penjualan/bahan_penolong/bp-show.js')) }}"></script>
@endpush
