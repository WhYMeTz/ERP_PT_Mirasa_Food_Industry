@extends('layouts.app')

@section('title', 'Detail Penerimaan Barang ' . $terima->terima_no . ' - ERP PT Mirasa')

@section('content')
<style>
    @media print {
        header, nav, .btn, .no-print, .alert, footer {
            display: none !important;
        }
        body, main {
            padding: 0 !important;
            margin: 0 !important;
            background: #ffffff !important;
        }
        .card {
            box-shadow: none !important;
            border: 1px solid #000000 !important;
            break-inside: avoid;
        }
        .print-only {
            display: block !important;
        }
        table th, table td {
            border: 1px solid #cbd5e1 !important;
            color: #000000 !important;
        }
    }
    .print-only {
        display: none;
    }
</style>

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
            <strong style="font-size: 1.1rem; color: #059669; display: block;">BUKTI PENERIMAAN BARANG & SLIP TIMBANG</strong>
            <span style="font-family: monospace; font-size: 0.9rem; font-weight: 700;">{{ $terima->terima_no }}</span>
        </div>
    </div>
</div>

<div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <a href="{{ route('gudang.terima.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
            &larr; Kembali ke Daftar Penerimaan
        </a>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">{{ $terima->terima_no }}</h1>
            <span class="badge badge-success">Stok Masuk Gudang (GRN)</span>
        </div>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <button onclick="window.print()" class="btn btn-secondary">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak Bukti Timbang
        </button>
        <a href="{{ route('gudang.stok.index', ['gudang_id' => $terima->gudang_id]) }}" class="btn btn-secondary">
            Lihat Stok di Gudang Ini &rarr;
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    {{-- KARTU INFO PENGIRIM & GUDANG --}}
    <div class="card">
        <div class="card-header" style="background: #f8fafc;">
            <strong style="color: #0f172a;">Informasi Dokumen & Pengirim</strong>
        </div>
        <div style="padding: 1.25rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
                <span style="color: #64748b; font-size: 0.8rem; display: block;">Supplier Pengirim:</span>
                <strong style="font-size: 1rem; color: #0f172a;">{{ $terima->supplier?->supplier_nm ?? '-' }}</strong>
                <p style="color: #64748b; font-size: 0.85rem; margin-top: 0.25rem;">
                    {{ $terima->supplier?->alamat_txt ?? '-' }}<br>
                    Kontak: {{ $terima->supplier?->kontak_no ?? '-' }}
                </p>
            </div>
            <div>
                <span style="color: #64748b; font-size: 0.8rem; display: block;">Gudang Penyimpanan Fisik:</span>
                <strong style="font-size: 1rem; color: #0f172a;">{{ $terima->gudang?->gudang_nm ?? '-' }}</strong>
                <p style="color: #64748b; font-size: 0.85rem; margin-top: 0.25rem;">
                    Kode Gudang: {{ $terima->gudang?->gudang_cd ?? '-' }}<br>
                    Lokasi: {{ $terima->gudang?->alamat_txt ?? '-' }}
                </p>
            </div>
            @if ($terima->catatan_txt)
                <div style="grid-column: 1 / -1; padding-top: 0.75rem; border-top: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 0.8rem; display: block;">Catatan Fisik Penerimaan:</span>
                    <p style="color: #334155; font-size: 0.875rem; margin-top: 0.25rem;">{{ $terima->catatan_txt }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- KARTU INFO REFERENSI --}}
    <div class="card">
        <div class="card-header" style="background: #f8fafc;">
            <strong style="color: #0f172a;">Referensi Dokumen</strong>
        </div>
        <div style="padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                <span style="color: #64748b; font-size: 0.875rem;">Tanggal Terima:</span>
                <strong style="color: #0f172a;">{{ \Carbon\Carbon::parse($terima->terima_tgl)->format('d F Y') }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding-top: 0.75rem; border-top: 1px solid #f1f5f9;">
                <span style="color: #64748b; font-size: 0.875rem;">Referensi PO:</span>
                @if ($terima->po)
                    <a href="{{ route('gudang.po.show', $terima->po_id) }}" style="font-weight: 700; color: #0284c7; text-decoration: none;">
                        {{ $terima->po->po_no }} &rarr;
                    </a>
                @else
                    <span style="color: #94a3b8; font-style: italic;">Non-PO</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- TABEL BARANG DITERIMA DENGAN NOMOR BATCH & NILAI --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
        <strong style="color: #0f172a;">Rincian Barang, Alokasi Nomor Batch, Harga & Nilai Masuk</strong>
        <span style="font-size: 0.8rem; color: #64748b;">{{ $terima->details->count() }} Jenis Komoditas</span>
    </div>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">No</th>
                    <th>Nama Barang / Komoditas</th>
                    <th>Nomor Batch / Lot</th>
                    <th style="text-align: center;">Expired</th>
                    <th style="text-align: center;">Grade</th>
                    <th style="text-align: right;">Qty Netto</th>
                    <th style="text-align: center;">Satuan</th>
                    <th style="text-align: right;">Harga Satuan</th>
                    <th style="text-align: right;">Diskon</th>
                    <th style="text-align: right;">Potongan</th>
                    <th style="text-align: right;">HPP Masuk</th>
                    <th style="text-align: center;">Pajak</th>
                    <th style="text-align: right;">Subtotal Tagihan</th>
                    <th style="text-align: center;" class="no-print">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($terima->details as $index => $item)
                    @php
                        $hargaAwal = (float) ($item->harga_nominal ?? 0);
                        $diskonPct = (float) ($item->diskon_persen ?? 0);
                        $potonganItem = (float) ($item->potongan_nominal ?? 0);
                        $hargaNet = (float) ($item->harga_netto ?: ($hargaAwal * (1 - ($diskonPct / 100))));
                        $subNet = (float) ($item->subtotal_netto ?: max(0, ((float) $item->terima_qty * $hargaNet) - $potonganItem));
                        $isItemPpn = ($item->ppn_tipe === 'PPN_11');
                        $tagihanItem = (float) ($item->subtotal_tagihan ?: ($subNet + ($isItemPpn ? round($subNet * 0.11) : 0)));
                    @endphp
                    <tr>
                        <td style="text-align: center; color: #64748b; font-weight: 600;">{{ $index + 1 }}</td>
                        <td>
                            <strong style="color: #0f172a;">{{ $item->barang?->barang_nm }}</strong>
                            <span style="display: block; font-size: 0.75rem; color: #64748b;">Kode: {{ $item->barang?->barang_cd }}</span>
                        </td>
                        <td>
                            <span style="font-family: monospace; font-size: 0.85rem; background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 700; color: #0284c7;">
                                {{ $item->batch_no }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            @if ($item->expired_tgl)
                                <span style="color: #0f172a; font-weight: 600; font-size: 0.85rem;">{{ \Carbon\Carbon::parse($item->expired_tgl)->format('d/m/Y') }}</span>
                            @else
                                <span style="color: #94a3b8;">-</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 0.75rem;">
                                {{ $item->grade_cd ?? 'A' }}
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: 700; font-size: 0.95rem; color: #047857;">
                            + {{ number_format((float) $item->terima_qty, 2, ',', '.') }}
                        </td>
                        <td style="text-align: center; color: #475569; font-size: 0.85rem;">{{ $item->barang?->satuanDasar?->satuan_nm ?? '-' }}</td>
                        <td style="text-align: right; font-family: monospace; font-size: 0.85rem;">
                            Rp {{ number_format($hargaAwal, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-family: monospace; font-size: 0.85rem; color: {{ $diskonPct > 0 ? '#d97706' : '#94a3b8' }};">
                            {{ $diskonPct > 0 ? number_format($diskonPct, 1, ',', '.') . '%' : '-' }}
                        </td>
                        <td style="text-align: right; font-family: monospace; font-size: 0.85rem; color: {{ $potonganItem > 0 ? '#dc2626' : '#94a3b8' }}; font-weight: {{ $potonganItem > 0 ? '600' : 'normal' }};">
                            {{ $potonganItem > 0 ? '- Rp ' . number_format($potonganItem, 0, ',', '.') : '-' }}
                        </td>
                        <td style="text-align: right; font-family: monospace; font-size: 0.85rem; font-weight: 600; color: #047857;">
                            Rp {{ number_format($subNet, 0, ',', '.') }}
                        </td>
                        <td style="text-align: center;">
                            @if ($isItemPpn)
                                <span class="badge" style="background: #e0f2fe; color: #0284c7; font-size: 0.725rem; font-weight: 700;">PPN 11%</span>
                            @else
                                <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 0.725rem; font-weight: 600;">Non-Pajak</span>
                            @endif
                        </td>
                        <td style="text-align: right; font-weight: 700; font-family: monospace; color: #0f172a;">
                            Rp {{ number_format($tagihanItem, 0, ',', '.') }}
                        </td>
                        <td style="text-align: center;" class="no-print">
                            <a href="{{ route('gudang.stok.ledger', ['barang_id' => $item->barang_id, 'gudang_id' => $terima->gudang_id]) }}" class="btn btn-secondary btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" title="Lihat Kartu Stok Barang Ini">
                                Kartu Stok &rarr;
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- SUMMARY BREAKDOWN KEUANGAN: DISKON, POTONGAN HARGA, DPP & PPN --}}
    @php
        $calcSubtotalBruto = (float) ($terima->subtotal_nominal ?? 0);
        if ($calcSubtotalBruto <= 0) {
            $calcSubtotalBruto = (float) $terima->details->sum(fn($d) => (float)$d->terima_qty * (float)$d->harga_nominal);
        }
        $calcDiskonItem = (float) $terima->details->sum(fn($d) => (float)$d->terima_qty * (float)($d->diskon_nominal ?? 0));
        $calcPotonganItem = (float) $terima->details->sum(fn($d) => (float)($d->potongan_nominal ?? 0));
        $totalSemuaPotongan = (float) ($terima->potongan_nominal ?? 0);
        $calcPotonganFaktur = max(0, $totalSemuaPotongan - $calcPotonganItem);

        $totalDppPpn = (float) $terima->details->where('ppn_tipe', 'PPN_11')->sum('subtotal_netto');
        $totalDppNonPpn = (float) $terima->details->where('ppn_tipe', '!=', 'PPN_11')->sum('subtotal_netto');
        if ($totalDppPpn == 0 && $totalDppNonPpn == 0) {
            if ($terima->ppn_tipe === 'PPN_11') {
                $totalDppPpn = (float) ($terima->dpp_nominal ?: ($calcSubtotalBruto - $calcDiskonItem - $totalSemuaPotongan));
            } else {
                $totalDppNonPpn = (float) ($calcSubtotalBruto - $calcDiskonItem - $totalSemuaPotongan);
            }
        }
        $calcPpn = (float) ($terima->ppn_nominal ?? $terima->details->sum('ppn_nominal'));
        $calcTagihan = (float) ($terima->total_tagihan ?? ($totalDppPpn + $totalDppNonPpn + $calcPpn - $calcPotonganFaktur));
        $calcHpp = (float) $terima->details->sum(fn($d) => (float)($d->subtotal_netto ?: ((float)$d->terima_qty * (float)($d->harga_netto ?: $d->harga_nominal))));
        if ($calcHpp <= 0) $calcHpp = (float) ($terima->total_nominal ?? 0);
    @endphp

    <div style="border-top: 2px solid #e2e8f0; background: #f8fafc; padding: 1.25rem; display: flex; justify-content: flex-end;">
        <div style="width: 100%; max-width: 440px; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.85rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; color: #64748b;">
                <span>Subtotal Nilai Kotor:</span>
                <strong style="color: #0f172a; font-family: monospace;">Rp {{ number_format($calcSubtotalBruto, 0, ',', '.') }}</strong>
            </div>

            @if ($calcDiskonItem > 0)
                <div style="display: flex; justify-content: space-between; align-items: center; color: #d97706;">
                    <span>Akumulasi Diskon Item:</span>
                    <strong style="font-family: monospace;">- Rp {{ number_format($calcDiskonItem, 0, ',', '.') }}</strong>
                </div>
            @endif

            @if ($calcPotonganItem > 0)
                <div style="display: flex; justify-content: space-between; align-items: center; color: #dc2626;">
                    <span>Akumulasi Potongan Item:</span>
                    <strong style="font-family: monospace;">- Rp {{ number_format($calcPotonganItem, 0, ',', '.') }}</strong>
                </div>
            @endif

            @if ($calcPotonganFaktur > 0)
                <div style="display: flex; justify-content: space-between; align-items: center; color: #dc2626;">
                    <span>Potongan Tambahan Nota Faktur:</span>
                    <strong style="font-family: monospace;">- Rp {{ number_format($calcPotonganFaktur, 0, ',', '.') }}</strong>
                </div>
            @endif

            @if ($totalDppPpn > 0)
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed #cbd5e1; padding-top: 0.4rem; color: #334155;">
                    <span style="font-weight: 600;">DPP Barang Kena PPN (11%):</span>
                    <strong style="font-family: monospace; font-weight: 700; color: #0f172a;">Rp {{ number_format($totalDppPpn, 0, ',', '.') }}</strong>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; color: #0284c7;">
                    <span>Nominal PPN (11%):</span>
                    <strong style="font-family: monospace;">+ Rp {{ number_format($calcPpn, 0, ',', '.') }}</strong>
                </div>
            @endif

            @if ($totalDppNonPpn > 0)
                <div style="display: flex; justify-content: space-between; align-items: center; color: #047857; {{ $totalDppPpn > 0 ? '' : 'border-top: 1px dashed #cbd5e1; padding-top: 0.4rem;' }}">
                    <span>Subtotal Barang Non-Pajak (0%):</span>
                    <strong style="font-family: monospace;">Rp {{ number_format($totalDppNonPpn, 0, ',', '.') }}</strong>
                </div>
            @endif

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 2px solid #0f172a; padding-top: 0.65rem; margin-top: 0.25rem;">
                <span style="font-size: 1rem; font-weight: 800; color: #0f172a;">Total Tagihan Akhir Supplier:</span>
                <strong style="font-size: 1.25rem; font-weight: 800; color: #0f172a; font-family: monospace;">
                    Rp {{ number_format($calcTagihan, 0, ',', '.') }}
                </strong>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; background: #dcfce7; border: 1px solid #86efac; border-radius: 6px; padding: 0.45rem 0.75rem; margin-top: 0.35rem;">
                <span style="color: #166534; font-size: 0.775rem; font-weight: 700; text-transform: uppercase;">Total Nilai Masuk Stok (HPP):</span>
                <strong style="color: #15803d; font-family: monospace; font-size: 0.95rem;">
                    Rp {{ number_format($calcHpp, 0, ',', '.') }}
                </strong>
            </div>
        </div>
    </div>
</div>

{{-- TANDA TANGAN SLIP TIMBANG & PENERIMAAN (GRN) --}}
<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-top: 2rem; text-align: center; font-size: 0.875rem;">
    <div style="border: 1px dashed #cbd5e1; border-radius: 8px; padding: 1rem; background: #ffffff;">
        <span style="color: #64748b; font-size: 0.8rem; display: block;">Diserahkan Oleh:</span>
        <strong style="color: #0f172a; display: block; margin-top: 0.2rem;">Sopir / Petani Pemasok</strong>
        <div style="height: 55px;"></div>
        <div style="border-bottom: 1px solid #94a3b8; width: 80%; margin: 0 auto;"></div>
        <span style="color: #64748b; font-size: 0.75rem; margin-top: 0.25rem; display: block;">Nama & Tanggal</span>
    </div>

    <div style="border: 1px dashed #cbd5e1; border-radius: 8px; padding: 1rem; background: #ffffff;">
        <span style="color: #64748b; font-size: 0.8rem; display: block;">Diterima & Ditimbang Oleh:</span>
        <strong style="color: #0f172a; display: block; margin-top: 0.2rem;">Petugas Timbang Gudang</strong>
        <div style="height: 55px;"></div>
        <div style="border-bottom: 1px solid #94a3b8; width: 80%; margin: 0 auto;"></div>
        <span style="color: #64748b; font-size: 0.75rem; margin-top: 0.25rem; display: block;">Nama & Tanggal</span>
    </div>

    <div style="border: 1px dashed #cbd5e1; border-radius: 8px; padding: 1rem; background: #ffffff;">
        <span style="color: #64748b; font-size: 0.8rem; display: block;">Diperiksa & Disetujui:</span>
        <strong style="color: #0f172a; display: block; margin-top: 0.2rem;">QC / Kepala Gudang</strong>
        <div style="height: 55px;"></div>
        <div style="border-bottom: 1px solid #94a3b8; width: 80%; margin: 0 auto;"></div>
        <span style="color: #64748b; font-size: 0.75rem; margin-top: 0.25rem; display: block;">Nama & Tanda Tangan</span>
    </div>
</div>
@endsection
