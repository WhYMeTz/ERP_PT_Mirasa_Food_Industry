@extends('layouts.app')

@section('title', 'Detail PO Penjualan ' . $order->so_no . ' - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/penjualan/so/so-show.css') }}">
@endpush

@section('content')
<div class="so-show-container">

    @php
        $custNmUpper = strtoupper($order->customer?->customer_nm ?? '');
        $custCdUpper = strtoupper($order->customer?->customer_cd ?? '');
        $klasifikasi = $order->customer?->klasifikasi;
        $isOrderIfm = $klasifikasi === 'IFM' || str_contains($custNmUpper, 'INDOFOOD') || str_contains($custNmUpper, 'IFM') || str_contains($custNmUpper, 'FORTUNA') || str_contains($custCdUpper, 'IFM') || str_contains($custCdUpper, 'INDOFOOD');
        $isOrderIdm = $klasifikasi === 'IDM' || str_contains($custCdUpper, 'IDM') || str_contains($custCdUpper, 'DC-YOGYA') || str_contains($custNmUpper, 'INDOMARET');
        $isOrderAlfa = $klasifikasi === 'MIDI_ALFA' || $klasifikasi === 'IDM_ALFA' || str_contains($custNmUpper, 'ALFA') || str_contains($custNmUpper, 'SUMBER ALFARIA') || str_contains($custNmUpper, 'MIDI') || str_contains($custCdUpper, 'MIDI') || str_contains($custCdUpper, 'ALFA');
        $isOrderPpn = $order->ppn_nominal > 0 || $order->ppn_tipe === 'PPN_11';

        $validSjs = $order->pengeluaran ? $order->pengeluaran->where('status_cd', '!=', 'BATAL')->values() : collect();
        $activeSj = $validSjs->first();
        $validFakturs = \App\Models\Penjualan\DatFakturHdr::where('so_id', $order->so_id)->where('deleted_st', false)->where('status_pembayaran', '!=', 'BATAL')->orderBy('faktur_id')->get();
        $activeFaktur = $validFakturs->first();
        $namaGdg = $activeSj?->gudang?->gudang_nm ?? 'PT Mirasa Food Industry';

        $totalItem = $order->details->count();
        $totalQty = (float) ($order->total_pesan_qty ?: $order->details->sum('pesan_qty'));
        $totalPesanQty = $totalQty;
        $totalKirimQty = (float) $order->total_kirim_qty;
        $pctKirim = $order->persentase_kirim;
        $statusCd = $order->status_cd;

        $klas = $order->customer?->klasifikasi;
        $klasLabel = match($klas) {
            'IFM' => 'Indofood (IFM)',
            'IDM' => 'Modern Trade (IDM)',
            'MIDI_ALFA', 'IDM_ALFA' => 'Modern Trade (Midi/Alfa)',
            'MANUAL' => 'Manual (Sales/Toko)',
            default => 'Customer'
        };

        $hasKurangStok = false;
        foreach ($order->details as $d) {
            $s = max(0, (float)$d->pesan_qty - (float)$d->kirim_qty);
            $stk = (float)($stockMap[$d->barang_id] ?? 0);
            if ($s > $stk) {
                $hasKurangStok = true;
                break;
            }
        }
    @endphp

    {{-- PRINT-ONLY HEADER --}}
    <div class="print-only" style="margin-bottom: 1.5rem; border-bottom: 2px solid #0f172a; padding-bottom: 0.75rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">PT. MIRASA FOOD INDUSTRY</h2>
                <p style="font-size: 0.75rem; color: #475569; margin: 0.2rem 0 0 0;">
                    Pabrik Pengolahan F&B Singkong &bull; Jl. Munggur No. 2 Ambartawang, Magelang
                </p>
            </div>
            <div style="text-align: right;">
                <strong style="font-size: 1.1rem; color: #059669; display: block;">PURCHASE ORDER PENJUALAN (SO)</strong>
                <span style="font-family: monospace; font-size: 0.9rem; font-weight: 700;">{{ $order->so_no }}</span>
            </div>
        </div>
    </div>

    {{-- TOP BAR --}}
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <a href="{{ route('penjualan.so.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
                &larr; Kembali ke Daftar PO Penjualan
            </a>
            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">{{ $order->so_no }}</h1>
                @if ($order->status_cd == 'COMPLETED')
                    <span class="badge badge-success">Selesai (100%)</span>
                @elseif ($order->status_cd == 'PARTIAL')
                    <span class="badge badge-info">Sebagian Kirim</span>
                @elseif ($order->status_cd == 'PROCESSING')
                    <span class="badge" style="background:#e0f2fe; color:#0369a1;">Diproses</span>
                @elseif ($order->status_cd == 'APPROVED')
                    <span class="badge" style="background:#dbeafe; color:#1d4ed8;">Disetujui</span>
                @elseif ($order->status_cd == 'DRAFT')
                    <span class="badge" style="background:#f1f5f9; color:#475569;">Draft</span>
                @else
                    <span class="badge" style="background:#fee2e2; color:#b91c1c;">Dibatalkan</span>
                @endif

                @if ($validFakturs->count() > 1)
                    @foreach ($validFakturs as $vfIdx => $vf)
                        <a href="{{ route('penjualan.faktur.show', $vf->faktur_id) }}" style="text-decoration: none;">
                            <span class="badge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-weight: 700;">
                                Faktur #{{ $vfIdx + 1 }}: {{ $vf->faktur_no }} ({{ $vf->status_pembayaran === 'LUNAS' ? 'Lunas' : 'Belum Lunas' }})
                            </span>
                        </a>
                    @endforeach
                @elseif ($activeFaktur)
                    <a href="{{ route('penjualan.faktur.show', $activeFaktur->faktur_id) }}" style="text-decoration: none;">
                        <span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-weight: 700;">
                            Ditagihkan: {{ $activeFaktur->faktur_no }} ({{ $activeFaktur->status_pembayaran === 'LUNAS' ? 'Lunas' : 'Belum Lunas' }})
                        </span>
                    </a>
                @else
                    <span class="badge" style="background: #f8fafc; color: #64748b; border: 1px solid #cbd5e1; font-weight: 600;">
                        Belum Ditagihkan
                    </span>
                @endif
            </div>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">
                Diterbitkan pada {{ \Carbon\Carbon::parse($order->so_tgl)->translatedFormat('d F Y') }} oleh {{ $order->created_by ?: 'System' }} &bull; Mitra: <strong>{{ $order->customer?->customer_nm ?: 'Pelanggan Umum' }}</strong>
            </p>
        </div>

        <div style="display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap;">
            @if ($validSjs->count() > 1)
                {{-- Multi-Trip: Navigasi Cepat ke Riwayat Pengiriman & Cetak Dokumen Lengkap --}}
                <a href="#riwayat-pengiriman" class="btn btn-secondary" style="background: #f0fdf4; border: 1.5px solid #86efac; color: #166534; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; border-radius: 6px;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: inline-block; vertical-align: -2px; margin-right: 4px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Lihat Dokumen Pengiriman ({{ $validSjs->count() }}x SJ &amp; Faktur)
                </a>

                @if ($lastSj = $validSjs->last())
                    <a href="{{ route('penjualan.pengeluaran.export-pdf', $lastSj->pengeluaran_id) }}" target="_blank" class="btn btn-secondary" style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #1e293b; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; border-radius: 6px;" title="Cetak Surat Jalan Trip Terakhir">
                        Cetak SJ Terakhir (PDF)
                    </a>
                @endif

                @if ($lastFk = $validFakturs->last())
                    <a href="{{ route('penjualan.faktur.export-pdf', $lastFk->faktur_id) }}" target="_blank" class="btn btn-primary" style="background: #059669; border-color: #047857; color: #ffffff; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; border-radius: 6px;" title="Cetak Faktur Penjualan Trip Terakhir">
                        Cetak Faktur Terakhir (PDF)
                    </a>
                @endif

                {{-- Catat Retur Penjualan (Dedicated Page) --}}
                <a href="{{ route('penjualan.retur.create', ['so_id' => $order->so_id]) }}" class="btn btn-warning" style="background: #fffbeb; border: 1.5px solid #fde68a; color: #b45309; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; border-radius: 6px; text-decoration: none;" title="Catat pengembalian barang dari pesanan ini">
                    + Catat Retur
                </a>
            @elseif ($activeSj)
                {{-- Cetak Lembar Surat Jalan Resmi --}}
                <a href="{{ route('penjualan.pengeluaran.export-pdf', $activeSj->pengeluaran_id) }}" target="_blank" class="btn btn-secondary" style="background: #ffffff; border: 1.5px solid #cbd5e1; color: #1e293b; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); border-radius: 6px;" title="Cetak Surat Jalan Resmi Dokumen PDF">
                    Cetak Surat Jalan (PDF)
                </a>

                @if ($activeFaktur)
                    {{-- Cetak Faktur Penjualan Resmi --}}
                    <a href="{{ route('penjualan.faktur.export-pdf', $activeFaktur->faktur_id) }}" target="_blank" class="btn btn-primary" style="background: #059669; border-color: #047857; color: #ffffff; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; border-radius: 6px;" title="Cetak Faktur Penjualan Resmi PDF">
                        Cetak Faktur (PDF)
                    </a>
                @else
                    {{-- Terbitkan Faktur Penjualan --}}
                    <a href="{{ route('penjualan.faktur.create', ['pengeluaran_id' => $activeSj->pengeluaran_id]) }}" class="btn btn-primary" style="background: #059669; border-color: #047857; color: #ffffff; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; border-radius: 6px;" title="Terbitkan Faktur Penjualan dari Surat Jalan Ini">
                        + Terbitkan Faktur
                    </a>
                @endif

                {{-- Catat Retur Penjualan (Dedicated Page) --}}
                <a href="{{ route('penjualan.retur.create', ['so_id' => $order->so_id]) }}" class="btn btn-warning" style="background: #fffbeb; border: 1.5px solid #fde68a; color: #b45309; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; border-radius: 6px; text-decoration: none;" title="Catat pengembalian barang dari pesanan ini">
                    + Catat Retur
                </a>

                {{-- Batalkan Pengiriman (Void Delivery) --}}
                <button type="button" class="btn btn-secondary" onclick="openVoidDeliveryModal({{ $order->so_id }}, '{{ $order->so_no }}')" style="border-color: #fca5a5; color: #dc2626; background: #fff5f5; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; border-radius: 6px;" title="Batalkan pengiriman dan kembalikan stok fisik ke Gudang FG">
                    Batalkan Pengiriman
                </button>
            @elseif(!in_array($order->status_cd, ['COMPLETED', 'CANCELLED']))
                <a href="{{ route('penjualan.so.edit', $order->so_id) }}" class="btn btn-secondary" style="background: #ffffff; border: 1.5px solid #cbd5e1; font-weight: 700; font-size: 0.85rem; padding: 0.55rem 0.95rem; border-radius: 6px;">
                    Edit PO
                </a>

                <button type="button" class="btn btn-danger" onclick="openCancelSoModal({{ $order->so_id }}, '{{ $order->so_no }}')" style="font-weight: 700; font-size: 0.85rem; padding: 0.55rem 0.95rem; border-radius: 6px;">
                    Batalkan PO
                </button>
            @endif
        </div>
    </div>

    {{-- TIGA KARTU INFORMASI DOKUMEN, PELANGGAN & LOGISTIK (STANDAR ERP) --}}
    <div class="so-grid-3-col">
        {{-- KARTU 1: DATA PESANAN (PO) --}}
        <div class="card">
            <div class="card-header">
                <strong style="color: #0f172a;">Data Pesanan (PO)</strong>
            </div>
            <div class="card-body" style="padding: 1rem 1.25rem;">
                <div class="so-kv-row">
                    <span class="so-kv-label">Nomor PO</span>
                    <span class="so-kv-value font-mono" style="color: #059669; font-weight: 700;">{{ $order->so_no }}</span>
                </div>
                <div class="so-kv-row">
                    <span class="so-kv-label">Tanggal Pesan</span>
                    <span class="so-kv-value">{{ \Carbon\Carbon::parse($order->so_tgl)->translatedFormat('d F Y') }}</span>
                </div>
                <div class="so-kv-row">
                    <span class="so-kv-label">No. PO Customer</span>
                    <span class="so-kv-value font-mono">{{ $order->customer_po_no ?: '-' }}</span>
                </div>
                <div class="so-kv-row">
                    <span class="so-kv-label">Ketentuan Pajak</span>
                    <span class="so-kv-value">{{ $isOrderPpn ? 'Ber-PPN (11%)' : 'Non-PPN' }}</span>
                </div>
                <div class="so-kv-row">
                    <span class="so-kv-label">Dibuat Oleh</span>
                    <span class="so-kv-value">{{ $order->created_by ?: 'System' }}</span>
                </div>
                <div class="so-kv-row">
                    <span class="so-kv-label">Gudang Asal</span>
                    <span class="so-kv-value">{{ $namaGdg }}</span>
                </div>
            </div>
        </div>

        {{-- KARTU 2: MITRA CUSTOMER --}}
        <div class="card">
            <div class="card-header">
                <strong style="color: #0f172a;">Mitra Customer</strong>
            </div>
            <div class="card-body" style="padding: 1rem 1.25rem;">
                <div style="margin-bottom: 0.65rem;">
                    <div style="font-weight: 700; font-size: 0.95rem; color: #0f172a;">{{ $order->customer?->customer_nm ?: 'Pelanggan Umum' }}</div>
                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                        Kode: <span class="font-mono">{{ $order->customer?->customer_cd ?: '-' }}</span> &bull; 
                        <span class="badge" style="background: #f0fdf4; color: #15803d; font-weight: 700; font-size: 0.725rem;">{{ $klasLabel }}</span>
                    </div>
                </div>
                <div class="so-kv-row">
                    <span class="so-kv-label">Alamat</span>
                    <span class="so-kv-value" style="font-size: 0.8rem; font-weight: 500; color: #475569;">{{ $order->customer?->alamat_txt ?: ($order->customer?->wilayah ?: '-') }}</span>
                </div>
                <div class="so-kv-row">
                    <span class="so-kv-label">Kontak / Telp</span>
                    <span class="so-kv-value">{{ $order->customer?->kontak_no ?: '-' }}</span>
                </div>
                <div class="so-kv-row">
                    <span class="so-kv-label">Syarat Bayar (TOP)</span>
                    <span class="so-kv-value" style="color: #0284c7; font-weight: 700;">{{ $order->top_txt ?: ($order->customer?->top_txt ?: '-') }}</span>
                </div>
            </div>
        </div>

        {{-- KARTU 3: LOGISTIK & PENGIRIMAN --}}
        <div class="card">
            <div class="card-header">
                <strong style="color: #0f172a;">Pengiriman &amp; Faktur</strong>
            </div>
            <div class="card-body" style="padding: 1rem 1.25rem;">
                <div class="so-kv-row">
                    <span class="so-kv-label">Realisasi Kirim</span>
                    <span class="so-kv-value">
                        {{ number_format($totalKirimQty, 0) }} / {{ number_format($totalPesanQty, 0) }} Karton
                        <span style="font-size: 0.75rem; color: {{ $pctKirim >= 100 ? '#15803d' : '#d97706' }};">({{ $pctKirim }}%)</span>
                    </span>
                </div>
                <div class="so-kv-row">
                    <span class="so-kv-label">Status Mutasi</span>
                    <span class="so-kv-value">
                        @if($order->isCompleted())
                            <span class="badge badge-success" style="font-weight: 700;">Terpotong FEFO</span>
                        @elseif($hasKurangStok)
                            <span class="badge" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; font-weight: 700;">
                                Menunggu Produksi (Stok Kurang)
                            </span>
                        @else
                            <span class="badge badge-warning">Menunggu Kirim</span>
                        @endif
                    </span>
                </div>
                <div class="so-kv-row" style="{{ $validSjs->count() > 1 ? 'align-items: flex-start;' : '' }}">
                    <span class="so-kv-label" style="{{ $validSjs->count() > 1 ? 'padding-top: 2px;' : '' }}">No. Surat Jalan</span>
                    <span class="so-kv-value">
                        @if($validSjs->count() > 1)
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                @foreach($validSjs as $sIdx => $sj)
                                    <div style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem;">
                                        <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.675rem; font-weight: 700; padding: 1px 5px; border-radius: 3px;">Trip {{ $sIdx + 1 }}</span>
                                        <a href="{{ route('penjualan.pengeluaran.show', $sj->pengeluaran_id) }}" style="color: #0284c7; font-weight: 700; font-family: monospace; text-decoration: underline;">
                                            {{ $sj->pengeluaran_no }}
                                        </a>
                                        <span style="color: #64748b; font-size: 0.725rem;">({{ number_format($sj->total_qty, 0) }} ctn)</span>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($activeSj)
                            <a href="{{ route('penjualan.pengeluaran.show', $activeSj->pengeluaran_id) }}" style="color: #0284c7; font-weight: 700; font-family: monospace; text-decoration: underline;">
                                {{ $activeSj->pengeluaran_no }}
                            </a>
                        @else
                            <span style="color: #94a3b8;">Belum Terbit</span>
                        @endif
                    </span>
                </div>
                <div class="so-kv-row">
                    <span class="so-kv-label">Tanggal Kirim</span>
                    <span class="so-kv-value">
                        {{ $order->tgl_kirim_aktual ? $order->tgl_kirim_aktual->translatedFormat('d F Y') : ($order->tgl_kirim_estimasi ? 'Est: ' . $order->tgl_kirim_estimasi->translatedFormat('d F Y') : '-') }}
                    </span>
                </div>
                <div class="so-kv-row">
                    <span class="so-kv-label">Armada / Sopir</span>
                    <span class="so-kv-value" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                        <span>{{ $order->supir_nm ?: '-' }} {{ $order->nopol_kendaraan ? "({$order->nopol_kendaraan})" : '' }}</span>
                        <button type="button" onclick="openModalArmada()" class="btn-xs-armada" title="Atur Sopir / Armada">Atur</button>
                    </span>
                </div>
                <div class="so-kv-row" style="{{ $validFakturs->count() > 1 ? 'align-items: flex-start;' : '' }}">
                    <span class="so-kv-label" style="{{ $validFakturs->count() > 1 ? 'padding-top: 2px;' : '' }}">Faktur Penjualan</span>
                    <span class="so-kv-value">
                        @if($validFakturs->count() > 1)
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                @foreach($validFakturs as $fIdx => $vf)
                                    <div style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem;">
                                        <span class="badge" style="background: #d1fae5; color: #047857; font-size: 0.675rem; font-weight: 700; padding: 1px 5px; border-radius: 3px;">Trip {{ $fIdx + 1 }}</span>
                                        <a href="{{ route('penjualan.faktur.show', $vf->faktur_id) }}" style="color: #059669; font-weight: 700; font-family: monospace; text-decoration: underline;">
                                            {{ $vf->faktur_no }}
                                        </a>
                                        <span style="color: #0f172a; font-weight: 700; font-family: monospace; font-size: 0.75rem;">Rp {{ number_format($vf->total_tagihan, 0, ',', '.') }}</span>
                                        <span class="badge {{ $vf->status_pembayaran === 'LUNAS' ? 'badge-success' : 'badge-warning' }}" style="font-size: 0.65rem; padding: 1px 4px;">
                                            {{ $vf->status_pembayaran === 'LUNAS' ? 'Lunas' : 'Belum' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($activeFaktur)
                            <a href="{{ route('penjualan.faktur.show', $activeFaktur->faktur_id) }}" style="color: #0284c7; font-weight: 700; font-family: monospace; text-decoration: underline;">
                                {{ $activeFaktur->faktur_no }}
                            </a>
                            <span class="badge {{ $activeFaktur->status_pembayaran === 'LUNAS' ? 'badge-success' : 'badge-warning' }}" style="font-size: 0.675rem; margin-left: 2px;">
                                {{ $activeFaktur->status_pembayaran === 'LUNAS' ? 'Lunas' : 'Belum Lunas' }}
                            </span>
                        @else
                            <span style="color: #94a3b8;">Belum Terbit</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- KARTU TABEL RINCIAN BARANG --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
            <strong style="color: #0f172a;">Rincian Barang yang Dipesan</strong>
            <span style="font-size: 0.85rem; color: #64748b;">{{ $totalItem }} Item Produk Jadi</span>
        </div>

        <div style="overflow-x: auto;">
            <table class="so-sheet-table">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="width: 110px; text-align: center;">Kode Barang</th>
                        <th style="min-width: 230px; text-align: left;">Nama Produk / Barang Jadi</th>
                        <th style="width: 140px; text-align: center;">Ketersediaan Stok</th>
                        <th style="width: 85px; text-align: right;">Jumlah</th>
                        <th style="width: 80px; text-align: left;">Satuan</th>
                        <th style="width: 120px; text-align: right;">Harga Satuan</th>
                        <th style="width: 85px; text-align: right;">Diskon (%)</th>
                        <th style="width: 100px; text-align: right;">Potongan</th>
                        <th style="width: 90px; text-align: center;">PPN</th>
                        <th style="width: 130px; text-align: right;">Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->details as $index => $item)
                        @php
                            $diskonPct = (float) ($item->diskon_persen ?? 0);
                            $potNom = (float) ($item->potongan_nominal ?? 0);
                            $isPpn = ($item->ppn_tipe ?? '') === 'PPN_11';
                            $subtotalRow = (float) ($item->subtotal_tagihan ?: $item->subtotal_nominal);
                            $stokFg = (float) ($stockMap[$item->barang_id] ?? 0);
                            $sisaItem = max(0, (float)$item->pesan_qty - (float)$item->kirim_qty);
                            $isCukup = ($stokFg >= $sisaItem && $stokFg > 0);
                        @endphp
                        <tr>
                            <td style="text-align: center; color: #64748b;">{{ $index + 1 }}</td>
                            <td style="text-align: center;">
                                <span class="font-mono" style="font-size: 0.775rem; color: #0284c7; font-weight: 700; background: #f0f9ff; border: 1px solid #bae6fd; padding: 2px 7px; border-radius: 4px;">
                                    {{ $item->barang?->barang_cd ?: '-' }}
                                </span>
                            </td>
                            <td>
                                <strong style="color: #0f172a; font-size: 0.875rem;">{{ $item->barang?->barang_nm }}</strong>
                            </td>

                            {{-- Kolom Ketersediaan Stok: Badge Enterprise Informatif --}}
                            <td style="vertical-align: middle;">
                                @if($order->isCompleted())
                                    <div style="display: flex; flex-direction: column; gap: 2px;">
                                        <span style="display: inline-block; width: fit-content; padding: 2px 7px; font-size: 0.725rem; font-weight: 700; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 4px;">
                                            TERKIRIM
                                        </span>
                                        <span style="font-size: 0.725rem; color: #64748b;">100% Terpenuhi</span>
                                    </div>
                                @elseif($isCukup)
                                    <div style="display: flex; flex-direction: column; gap: 2px;">
                                        <span style="display: inline-block; width: fit-content; padding: 2px 7px; font-size: 0.725rem; font-weight: 700; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; border-radius: 4px;">
                                            SIAP KIRIM
                                        </span>
                                        <span style="font-size: 0.725rem; color: #166534; font-weight: 600;">
                                            Stok: {{ number_format($stokFg, 0) }} ctn
                                        </span>
                                    </div>
                                @elseif($stokFg > 0)
                                    <div style="display: flex; flex-direction: column; gap: 2px;">
                                        <span style="display: inline-block; width: fit-content; padding: 2px 7px; font-size: 0.725rem; font-weight: 700; background: #fffbeb; color: #b45309; border: 1px solid #fde68a; border-radius: 4px;">
                                            STOK SEBAGIAN
                                        </span>
                                        <span style="font-size: 0.725rem; color: #92400e; font-weight: 600;">
                                            Ada {{ number_format($stokFg, 0) }} &bull; <strong style="color: #dc2626;">Kurang {{ number_format($sisaItem - $stokFg, 0) }}</strong>
                                        </span>
                                    </div>
                                @else
                                    <div style="display: flex; flex-direction: column; gap: 2px;">
                                        <span style="display: inline-block; width: fit-content; padding: 2px 7px; font-size: 0.725rem; font-weight: 700; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 4px;">
                                            MENUNGGU PRODUKSI
                                        </span>
                                        <span style="font-size: 0.725rem; color: #64748b;">
                                            Stok: 0 ctn (Kurang {{ number_format($sisaItem, 0) }})
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <td style="text-align: right; font-weight: 600; font-size: 0.875rem; white-space: nowrap;">
                                {{ number_format((float) $item->pesan_qty, 0) }}
                            </td>
                            <td style="color: #475569; font-size: 0.85rem; white-space: nowrap;">
                                {{ $item->satuan ?: ($item->barang?->satuanDasar?->satuan_nm ?? ($item->barang?->satuanDasar?->satuan_cd ?? '-')) }}
                            </td>
                            <td style="text-align: right; font-size: 0.85rem; color: #475569; font-family: monospace; white-space: nowrap;">
                                Rp {{ number_format((float) $item->harga_satuan, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-family: monospace; font-size: 0.85rem; color: {{ $diskonPct > 0 ? '#d97706' : '#94a3b8' }}; white-space: nowrap;">
                                {{ $diskonPct > 0 ? number_format($diskonPct, 1, ',', '.') . '%' : '-' }}
                            </td>
                            <td style="text-align: right; font-family: monospace; font-size: 0.85rem; color: {{ $potNom > 0 ? '#dc2626' : '#94a3b8' }}; white-space: nowrap;">
                                {{ $potNom > 0 ? 'Rp ' . number_format($potNom, 0, ',', '.') : '-' }}
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                @if ($isPpn)
                                    <span class="badge" style="background:#e0f2fe; color:#0369a1; font-size:0.7rem; font-weight:700;">PPN 11%</span>
                                @else
                                    <span class="badge" style="background:#f1f5f9; color:#64748b; font-size:0.7rem;">Non PPN</span>
                                @endif
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a; font-size: 0.875rem; font-family: monospace; white-space: nowrap;">
                                Rp {{ number_format($subtotalRow, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot style="background: #f8fafc; border-top: 2px solid #cbd5e1; font-weight: 700;">
                    <tr>
                        <td colspan="4" style="text-align: right; padding: 0.75rem 1rem; color: #475569; font-size: 0.85rem;">
                            Total Kuantitas:
                        </td>
                        <td style="text-align: right; padding: 0.75rem 0.75rem; color: #0f172a; font-size: 0.95rem; font-weight: 800; font-family: monospace; white-space: nowrap;">
                            {{ number_format((float) $totalQty, 0, ',', '.') }}
                        </td>
                        <td colspan="6"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- BAGIAN BAWAH: CATATAN & KOTAK RINGKASAN FINANSIAL --}}
        <div class="so-footer-summary">
            {{-- SISI KIRI: CATATAN & AUDIT DOKUMEN --}}
            <div class="so-summary-left">
                @if ($order->catatan_txt)
                    <div class="so-summary-notes">
                        <div class="so-summary-notes-title">Catatan Pesanan:</div>
                        <div class="so-summary-notes-content">{!! nl2br(e($order->catatan_txt)) !!}</div>
                    </div>
                @endif

                <div class="so-audit-trail">
                    <div>Dibuat oleh: <strong>{{ $order->created_by ?: 'System' }}</strong> pada {{ $order->created_dt ? \Carbon\Carbon::parse($order->created_dt)->translatedFormat('d F Y H:i') : ($order->so_tgl ? \Carbon\Carbon::parse($order->so_tgl)->translatedFormat('d F Y') : '-') }}</div>
                    @if ($order->updated_by)
                        <div>Terakhir diubah oleh: <strong>{{ $order->updated_by }}</strong> pada {{ $order->updated_dt ? \Carbon\Carbon::parse($order->updated_dt)->translatedFormat('d F Y H:i') : '-' }}</div>
                    @endif
                    <div>Versi Dokumen: v{{ $order->version_no ?? 1 }}</div>
                </div>
            </div>

            {{-- SISI KANAN: KOTAK PERHITUNGAN TOTAL FINANSIAL --}}
            <div class="so-calc-box">
                <div class="so-calc-row">
                    <span class="so-calc-label">Subtotal Nilai Bruto:</span>
                    <span class="so-calc-value">Rp {{ number_format((float) $order->subtotal_bruto, 0, ',', '.') }}</span>
                </div>

                @if ($order->diskon_total > 0)
                    <div class="so-calc-row">
                        <span class="so-calc-label">Akumulasi Diskon:</span>
                        <span class="so-calc-value text-amber">- Rp {{ number_format((float) $order->diskon_total, 0, ',', '.') }}</span>
                    </div>
                @endif

                @if ($order->potongan_nominal > 0)
                    <div class="so-calc-row">
                        <span class="so-calc-label">Potongan Tambahan:</span>
                        <span class="so-calc-value text-danger">- Rp {{ number_format((float) $order->potongan_nominal, 0, ',', '.') }}</span>
                    </div>
                @endif

                <div class="so-calc-row so-calc-divider">
                    <span class="so-calc-label font-semibold">Dasar Pengenaan Pajak (DPP):</span>
                    <span class="so-calc-value font-semibold">Rp {{ number_format((float) $order->dpp_nominal, 0, ',', '.') }}</span>
                </div>

                <div class="so-calc-row">
                    <span class="so-calc-label">PPN (11%):</span>
                    <span class="so-calc-value text-blue">+ Rp {{ number_format((float) $order->ppn_nominal, 0, ',', '.') }}</span>
                </div>

                <div class="so-calc-row total-row">
                    <span>Total Tagihan SO:</span>
                    <span class="so-calc-total-val">Rp {{ number_format((float) $order->total_tagihan, 0, ',', '.') }}</span>
                </div>

                @if ($order->total_retur_nominal > 0)
                    <div class="so-calc-row text-danger" style="margin-top: 0.35rem;">
                        <span>Retur Penjualan (Potong):</span>
                        <span>- Rp {{ number_format((float) $order->total_retur_nominal, 0, ',', '.') }}</span>
                    </div>
                    <div class="so-calc-row" style="border-top: 1px dashed #cbd5e1; padding-top: 0.35rem; margin-top: 0.25rem;">
                        <span style="font-weight: 700; color: #0f172a;">Sisa Tagihan Bersih (Net):</span>
                        <span style="font-family: monospace; font-weight: 800; color: #059669; font-size: 1.05rem;">Rp {{ number_format((float) $order->net_tagihan, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- KARTU RIWAYAT PENGIRIMAN FISIK (SURAT JALAN) & DOKUMEN FAKTUR --}}
    @if($validSjs->count() > 0)
        <div class="card" id="riwayat-pengiriman" style="margin-bottom: 1.5rem;">
            <div class="card-header" style="background: #f8fafc; display: flex; justify-content: space-between; align-items: center; border-bottom: 1.5px solid #e2e8f0; padding: 0.85rem 1.25rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="20" height="20" fill="none" stroke="#0284c7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                    <div>
                        <strong style="color: #0f172a; font-size: 0.95rem;">Riwayat Pengiriman Fisik (Surat Jalan) &amp; Dokumen Faktur</strong>
                        <div style="font-size: 0.75rem; color: #64748b; font-weight: 400; margin-top: 1px;">
                            Setiap keberangkatan truk memiliki Surat Jalan fisik &amp; Faktur Penjualan resmi terpisah
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 0.775rem; border: 1px solid #bae6fd; padding: 4px 10px;">
                    {{ $validSjs->count() }} Kali Pengiriman Terpisah
                </span>
            </div>

            <div style="overflow-x: auto;">
                <table class="so-sheet-table">
                    <thead>
                        <tr>
                            <th style="padding: 0.65rem 0.85rem; text-align: center; width: 60px;">Trip</th>
                            <th style="padding: 0.65rem 0.85rem; text-align: left; min-width: 150px;">No. Surat Jalan</th>
                            <th style="padding: 0.65rem 0.85rem; text-align: left; min-width: 115px;">Tanggal Kirim</th>
                            <th style="padding: 0.65rem 0.85rem; text-align: left; min-width: 150px;">Supir &amp; Armada</th>
                            <th style="padding: 0.65rem 0.85rem; text-align: left; min-width: 260px;">Rincian Barang &amp; Batch FEFO</th>
                            <th style="padding: 0.65rem 0.85rem; text-align: left; min-width: 150px;">Dokumen Faktur</th>
                            <th style="padding: 0.65rem 0.85rem; text-align: right; min-width: 125px;">Nilai Tagihan</th>
                            <th style="padding: 0.65rem 0.85rem; text-align: center; min-width: 160px;">Aksi Dokumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($validSjs as $sIdx => $sj)
                            @php
                                $fk = $sj->faktur;
                            @endphp
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="text-align: center; vertical-align: middle;">
                                    <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 800; font-size: 0.8rem; padding: 3px 8px; border-radius: 4px;">
                                        Trip {{ $sIdx + 1 }}
                                    </span>
                                </td>
                                <td style="vertical-align: middle;">
                                    <a href="{{ route('penjualan.pengeluaran.show', $sj->pengeluaran_id) }}" style="font-family: monospace; font-weight: 700; color: #0284c7; text-decoration: underline; font-size: 0.875rem;">
                                        {{ $sj->pengeluaran_no }}
                                    </a>
                                    <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                        Total: <strong style="color: #0f172a;">{{ number_format($sj->total_qty, 0) }} Karton</strong>
                                    </div>
                                </td>
                                <td style="vertical-align: middle;">
                                    <div style="font-weight: 600; color: #334155; font-size: 0.825rem;">
                                        {{ $sj->pengeluaran_tgl ? \Carbon\Carbon::parse($sj->pengeluaran_tgl)->translatedFormat('d F Y') : '-' }}
                                    </div>
                                </td>
                                <td style="vertical-align: middle;">
                                    <div style="font-weight: 600; color: #0f172a; font-size: 0.825rem;">
                                        {{ $sj->supir_nm ?: '-' }}
                                    </div>
                                    <div style="font-size: 0.725rem; color: #64748b;">
                                        {{ $sj->nopol_kendaraan ?: ($sj->ekspedisi_nm ?: 'Armada Internal') }}
                                    </div>
                                </td>
                                <td style="vertical-align: middle;">
                                    @foreach ($sj->details as $sjd)
                                        <div style="font-size: 0.8rem; padding: 3px 0; border-bottom: 1px dashed #f1f5f9; display: flex; align-items: baseline; justify-content: space-between; gap: 8px;">
                                            <div>
                                                <strong style="color: #1e293b;">{{ $sjd->barang?->barang_nm ?? 'Produk' }}</strong>
                                                @if($sjd->batch_no)
                                                    <span style="color: #64748b; font-size: 0.725rem; font-family: monospace;">({{ $sjd->batch_no }})</span>
                                                @endif
                                            </div>
                                            <div style="font-weight: 700; color: #059669; font-family: monospace; white-space: nowrap;">
                                                {{ number_format($sjd->kirim_qty, 0) }} ctn
                                            </div>
                                        </div>
                                    @endforeach
                                </td>
                                <td style="vertical-align: middle;">
                                    @if ($fk)
                                        <a href="{{ route('penjualan.faktur.show', $fk->faktur_id) }}" style="font-family: monospace; font-weight: 700; color: #059669; text-decoration: underline; font-size: 0.85rem;">
                                            {{ $fk->faktur_no }}
                                        </a>
                                        <div style="margin-top: 3px;">
                                            <span class="badge {{ $fk->status_pembayaran === 'LUNAS' ? 'badge-success' : 'badge-warning' }}" style="font-size: 0.675rem; padding: 2px 6px; border-radius: 3px;">
                                                {{ $fk->status_pembayaran === 'LUNAS' ? 'Lunas' : 'Belum Lunas' }}
                                            </span>
                                        </div>
                                    @else
                                        <span style="color: #94a3b8; font-style: italic; font-size: 0.775rem;">Belum Terbit</span>
                                    @endif
                                </td>
                                <td style="text-align: right; vertical-align: middle; font-weight: 700; font-family: monospace; color: #0f172a; font-size: 0.875rem;">
                                    @if ($fk)
                                        Rp {{ number_format($fk->total_tagihan, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="text-align: center; vertical-align: middle; white-space: nowrap;">
                                    <div style="display: inline-flex; gap: 4px;">
                                        <a href="{{ route('penjualan.pengeluaran.export-pdf', $sj->pengeluaran_id) }}" target="_blank" class="btn btn-xs" style="background: #ffffff; border: 1px solid #cbd5e1; color: #0284c7; font-weight: 700; font-size: 0.725rem; padding: 4px 8px; border-radius: 4px;" title="Cetak Dokumen Surat Jalan Resmi PDF">
                                            Cetak SJ
                                        </a>
                                        @if ($fk)
                                            <a href="{{ route('penjualan.faktur.export-pdf', $fk->faktur_id) }}" target="_blank" class="btn btn-xs" style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; font-weight: 700; font-size: 0.725rem; padding: 4px 8px; border-radius: 4px;" title="Cetak Dokumen Faktur Penjualan PDF">
                                                Cetak Faktur
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- KARTU RETUR PENJUALAN (JIKA ADA RETUR) --}}
    @if($order->returns && $order->returns->count() > 0)
        <div class="card" id="retur-penjualan" style="margin-bottom: 1.5rem;">
            <div class="card-header" style="background: #f8fafc;">
                <strong style="color: #0f172a; font-size: 0.95rem;">Riwayat Retur Penjualan</strong>
                <span style="font-size: 0.775rem; color: #64748b; margin-left: 0.35rem;">(Barang fisik kembali ke gudang)</span>
            </div>

            <div style="overflow-x: auto;">
                <table class="so-sheet-table">
                    <thead>
                        <tr>
                            <th style="padding: 0.6rem 0.85rem; text-align: center; width: 40px;">No</th>
                            <th style="padding: 0.6rem 0.85rem; text-align: left;">No. Retur</th>
                            <th style="padding: 0.6rem 0.85rem; text-align: left;">Tanggal</th>
                            <th style="padding: 0.6rem 0.85rem; text-align: left;">Gudang Masuk</th>
                            <th style="padding: 0.6rem 0.85rem; text-align: left;">Tindakan</th>
                            <th style="padding: 0.6rem 0.85rem; text-align: left;">Rincian Barang</th>
                            <th style="padding: 0.6rem 0.85rem; text-align: right; width: 80px;">Total Qty</th>
                            <th style="padding: 0.6rem 0.85rem; text-align: right; width: 120px;">Total Nilai</th>
                            <th style="padding: 0.6rem 0.85rem; text-align: center; width: 100px;">Status</th>
                            <th style="padding: 0.6rem 0.85rem; text-align: center; width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->returns as $rIdx => $retur)
                            <tr>
                                <td style="text-align: center; color: #64748b;">{{ $rIdx + 1 }}</td>
                                <td style="font-family: monospace; font-weight: 700; color: #dc2626;">
                                    {{ $retur->retur_no }}
                                </td>
                                <td>
                                    {{ $retur->retur_tgl ? \Carbon\Carbon::parse($retur->retur_tgl)->format('d/m/Y') : '-' }}
                                </td>
                                <td>
                                    {{ $retur->gudang?->gudang_nm ?? '-' }}
                                </td>
                                <td>
                                    <span class="badge" style="background: #fef2f2; color: #991b1b; font-size: 0.7rem; font-weight: 700;">
                                        {{ $retur->tindakan_cd === 'POTONG_TAGIHAN' ? 'Potong Tagihan' : 'Ganti Barang' }}
                                    </span>
                                </td>
                                <td>
                                    @foreach($retur->details as $rdtl)
                                        <div style="font-size: 0.775rem; color: #334155; margin-bottom: 2px;">
                                            &bull; <strong>{{ $rdtl->barang?->barang_nm }}</strong>: {{ number_format((float)$rdtl->qty, 0) }} {{ $rdtl->satuan ?: 'karton' }}
                                        </div>
                                    @endforeach
                                </td>
                                <td style="text-align: right; font-family: monospace; font-weight: 700;">
                                    {{ number_format((float)$retur->total_qty, 0) }}
                                </td>
                                <td style="text-align: right; font-family: monospace; font-weight: 700; color: #0f172a;">
                                    Rp {{ number_format((float)$retur->total_nilai, 0, ',', '.') }}
                                </td>
                                <td style="text-align: center;">
                                    {!! $retur->status_badge !!}
                                </td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <div style="display: flex; gap: 0.35rem; justify-content: center; align-items: center;">
                                        <a href="{{ route('penjualan.retur.export-pdf', $retur->retur_id) }}" 
                                           target="_blank" 
                                           class="btn btn-secondary btn-sm" 
                                           style="font-size: 0.725rem; padding: 0.25rem 0.5rem;"
                                           title="Cetak Berita Acara Retur PDF">
                                            PDF
                                        </a>
                                        @if ((float)$retur->total_qty_dimusnahkan > 0)
                                            <a href="{{ route('penjualan.retur.berita-acara-pemusnahan', $retur->retur_id) }}" 
                                               class="btn btn-danger btn-sm" 
                                               style="font-size: 0.725rem; padding: 0.25rem 0.5rem; background: #fee2e2; border-color: #fca5a5; color: #dc2626; text-decoration: none;"
                                               title="Lihat & Cetak Berita Acara Pemusnahan (Scrap Report)">
                                                BAP Musnah
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: #f8fafc; border-top: 2px solid #cbd5e1; font-weight: 700;">
                            <td colspan="6" style="padding: 0.65rem 0.85rem; text-align: right; color: #334155;">
                                Total Retur Terkonfirmasi:
                            </td>
                            <td style="padding: 0.65rem 0.85rem; text-align: right; font-family: monospace; color: #0f172a;">
                                {{ number_format((float)$order->total_retur_qty, 0) }}
                            </td>
                            <td style="padding: 0.65rem 0.85rem; text-align: right; font-family: monospace; color: #0f172a; font-size: 0.9rem;">
                                Rp {{ number_format((float)$order->total_retur_nominal, 0, ',', '.') }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

</div>

{{-- MODAL DIALOGS LENGKAP --}}
@include('penjualan.so.partials.modal-void-delivery')
@include('penjualan.so.partials.modal-cancel-so')
@include('penjualan.so.partials.modal-update-armada')

@endsection

@push('scripts')
    <script>
        window.appConfig = {
            autoOpenPdf: @json(session('auto_open_pdf'))
        };
    </script>
    <script src="{{ asset('js/penjualan/so/so-show.js') }}"></script>
@endpush
