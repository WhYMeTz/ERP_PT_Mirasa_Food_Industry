@extends('layouts.app')

@section('title', 'Pemeriksaan Dokumen ' . $pakai->pakai_no . ' - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/verifikasi_pemakaian/verifikasi-pemakaian.css') }}">
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
            .verifikasi-card {
                box-shadow: none !important;
                border: 1px solid #000000 !important;
            }
        }
    </style>
@endpush

@section('content')
<div class="no-print" style="margin-bottom: 1.5rem;">
    <a href="{{ route('keuangan.verifikasi_pemakaian.index', ['tab' => $pakai->status_st]) }}" style="color: #64748b; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.25rem;">
        &larr; Kembali ke Daftar Antrean Verifikasi
    </a>

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-top: 0.5rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <span>{{ $pakai->pakai_no }}</span>
                <span class="badge" style="background: #ffedd5; color: #c2410c; font-size: 0.85rem;">
                    {{ $pakai->tujuan_pemakaian }}
                </span>
                @if($pakai->status_st === 'DRAFT')
                    <span class="badge-status badge-draft" style="font-size: 0.8rem; padding: 0.25rem 0.65rem;">
                        ⏳ DRAFT (Menunggu Verifikasi)
                    </span>
                @else
                    <span class="badge-status badge-posted" style="font-size: 0.8rem; padding: 0.25rem 0.65rem;">
                        ✓ POSTED (Sudah Dijurnal)
                    </span>
                @endif
            </h1>
            <p style="font-size: 0.85rem; color: #64748b; margin-top: 0.25rem;">
                Gudang Asal: <strong>{{ $pakai->gudang?->gudang_nm }}</strong> &bull; Tanggal Catat: <strong>{{ \Carbon\Carbon::parse($pakai->pakai_tgl)->format('d F Y') }}</strong> &bull; Dicatat oleh: <strong>{{ $pakai->created_by ?? '-' }}</strong>
            </p>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            @if($pakai->status_st === 'DRAFT')
                <button type="button" onclick="openModalReject({{ $pakai->pakai_id }}, '{{ $pakai->pakai_no }}')" class="btn btn-secondary" style="color: #dc2626; border-color: #fecaca;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Tolak / Minta Revisi
                </button>
                <button type="button" onclick="openModalApprove({{ $pakai->pakai_id }}, '{{ $pakai->pakai_no }}', {{ $totalNominal }})" class="btn btn-primary" style="background: #059669; border-color: #059669; font-weight: 700; box-shadow: 0 2px 4px rgba(5,150,105,0.25);">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Setujui &amp; Terbitkan Jurnal HPP
                </button>
            @else
                @if($pakai->jurnal_id)
                    <button type="button" onclick="openModalDetailJurnal({{ $pakai->jurnal_id }})" class="btn btn-secondary" style="color: #065f46; border-color: #a7f3d0; background: #f0fdf4; font-weight: 700;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Lihat Jurnal Umum
                    </button>
                @endif
            @endif
            <button onclick="window.print()" class="btn btn-secondary">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Bukti
            </button>
        </div>
    </div>
</div>

{{-- BANNER STATUS OPERASIONAL --}}
<div class="no-print">
    @if($pakai->status_st === 'DRAFT')
        <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.85rem;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; flex-shrink: 0;">
                    🔍
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #92400e;">Layar Pemeriksaan: Menunggu Validasi Pemeriksa</h4>
                    <p style="margin: 0.2rem 0 0; font-size: 0.825rem; color: #b45309;">
                        Silakan periksa ketersediaan stok fisik per-batch pada tabel di bawah. Jika semua item sudah benar dan stok mencukupi, klik tombol <strong>"Setujui &amp; Terbitkan Jurnal HPP"</strong> untuk memotong stok dan membukukan mutasi ke akun 1151 WIP &amp; FG.
                    </p>
                </div>
            </div>
            @if(!$canApprove)
                <span class="badge" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; font-weight: 700; padding: 0.35rem 0.65rem;">
                    ⚠️ Ada stok batch yang tidak mencukupi!
                </span>
            @endif
        </div>
    @else
        <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: #d1fae5; color: #065f46; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                    ✓
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 0.925rem; font-weight: 700; color: #065f46;">Dokumen Ini Telah Resmi Diverifikasi &amp; Terposting</h4>
                    <p style="margin: 0.15rem 0 0; font-size: 0.8rem; color: #047857;">
                        Diverifikasi pada <strong>{{ $pakai->posted_at ? $pakai->posted_at->format('d/m/Y H:i') : '-' }}</strong> oleh <strong>{{ $pakai->posted_by ?? 'SYSTEM' }}</strong>. Stok fisik gudang telah terpotong dan Jurnal Umum telah dibukukan.
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- 1. TABEL PEMERIKSAAN BATCH FISIK & STOK --}}
<div class="verifikasi-card">
    <div style="padding: 1.15rem 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <h2 style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin: 0;">
            1. Validasi Fisik Batch &amp; Ketersediaan Stok Gudang
        </h2>
        <span style="font-size: 0.85rem; color: #64748b;">
            Total: <strong>{{ count($itemsCheck) }} Item Bahan</strong>
        </span>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">No</th>
                    <th style="min-width: 190px;">Nama Bahan</th>
                    <th style="min-width: 130px;">Batch Dipilih</th>
                    <th style="min-width: 110px; text-align: right;">Qty Keluar</th>
                    <th style="min-width: 130px; text-align: right;">Sisa Stok Gudang</th>
                    <th style="min-width: 120px; text-align: center;">Cek Stok</th>
                    <th style="min-width: 150px;">Validasi FIFO</th>
                    <th style="min-width: 110px; text-align: right;">Harga Satuan</th>
                    <th style="min-width: 130px; text-align: right;">Total Nilai</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($itemsCheck as $index => $item)
                    @php $dtl = $item['detail']; @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="text-align: center; color: #64748b; font-size: 0.85rem;">{{ $index + 1 }}</td>
                        <td>
                            <strong style="color: #0f172a; font-size: 0.875rem;">
                                {{ $dtl->barang?->barang_nm }}
                            </strong>
                            <div style="font-size: 0.725rem; font-family: monospace; color: #64748b;">
                                {{ $dtl->barang?->barang_cd }} &bull; {{ $dtl->barang?->jenisBarang?->jenis_barang_nm ?? '-' }}
                            </div>
                        </td>
                        <td>
                            <span style="font-family: monospace; font-size: 0.825rem; background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; padding: 0.15rem 0.45rem; border-radius: 4px; font-weight: 700;">
                                {{ $dtl->batch_no }}
                            </span>
                            @if(!empty($dtl->grade_cd))
                                <span style="font-size: 0.7rem; font-weight: 700; color: #059669; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 0.1rem 0.3rem; border-radius: 4px; margin-left: 0.2rem;">
                                    {{ $dtl->grade_cd }}
                                </span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <span style="font-weight: 700; color: #dc2626; font-size: 0.925rem;">
                                {{ number_format((float) $dtl->qty_keluar, 2, ',', '.') }}
                            </span>
                            <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.15rem;">
                                {{ $dtl->barang?->satuanDasar?->satuan_nm }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <span style="font-weight: 700; color: {{ $item['current_stock'] > 0 ? '#0f172a' : '#dc2626' }};">
                                {{ number_format($item['current_stock'], 2, ',', '.') }}
                            </span>
                            <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.15rem;">
                                {{ $dtl->barang?->satuanDasar?->satuan_nm }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            @if($item['is_stock_ok'])
                                <span class="badge-status badge-stock-ok">
                                    ✓ Cukup
                                </span>
                            @else
                                <span class="badge-status badge-stock-danger">
                                    ✗ Kurang!
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($item['is_fifo_ok'])
                                <span style="font-size: 0.75rem; color: #059669; font-weight: 600; display: inline-flex; align-items: center; gap: 0.2rem;">
                                    ✓ Sesuai FIFO
                                </span>
                            @else
                                <span style="font-size: 0.725rem; color: #d97706; font-weight: 600;" title="{{ $item['fifo_note'] }}">
                                    ⚠️ {{ $item['fifo_note'] }}
                                </span>
                            @endif
                        </td>
                        <td style="text-align: right; color: #475569; font-size: 0.85rem;">
                            Rp {{ number_format($item['harga_satuan'], 0, ',', '.') }}
                        </td>
                        <td style="text-align: right;">
                            <strong style="color: #0f172a; font-family: monospace; font-size: 0.9rem;">
                                Rp {{ number_format($item['total_harga'], 0, ',', '.') }}
                            </strong>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: 700; border-top: 2px solid #cbd5e1;">
                    <td colspan="8" style="text-align: right; padding: 0.75rem 1rem; color: #0f172a;">
                        TOTAL ESTIMASI HPP PEMAKAIAN:
                    </td>
                    <td style="text-align: right; padding: 0.75rem 1rem; color: #059669; font-family: monospace; font-size: 1.05rem;">
                        Rp {{ number_format($totalNominal, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- 2. KOTAK SIMULASI JURNAL AKUNTANSI --}}
<div class="verifikasi-card">
    <div style="padding: 1.15rem 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
        <h2 style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
            <span>2. Jurnal Akuntansi Buku Besar</span>
            @if($pakai->status_st === 'POSTED')
                <span class="badge" style="background: #ecfdf5; color: #065f46; font-size: 0.75rem;">SUDAH DIBUKUKAN</span>
            @else
                <span class="badge" style="background: #eff6ff; color: #1e40af; font-size: 0.75rem;">SIMULASI OTOMATIS</span>
            @endif
        </h2>
        @if($pakai->jurnal)
            <span style="font-size: 0.8rem; font-family: monospace; color: #0284c7; font-weight: 700;">
                No Jurnal: {{ $pakai->jurnal->jurnal_no }}
            </span>
        @endif
    </div>

    <div style="padding: 1.25rem;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="background: #f8fafc; color: #475569; border-bottom: 1px solid #cbd5e1;">
                    <th style="padding: 0.6rem 0.85rem; text-align: left;">Kode Akun &amp; Nama Akun</th>
                    <th style="padding: 0.6rem 0.85rem; text-align: left;">Posisi / Fungsi</th>
                    <th style="padding: 0.6rem 0.85rem; text-align: right; min-width: 140px;">Debet</th>
                    <th style="padding: 0.6rem 0.85rem; text-align: right; min-width: 140px;">Kredit</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 0.65rem 0.85rem; color: #0f172a;">
                        <strong style="color: #059669;">1151</strong> &bull; Persediaan WIP &amp; FG
                    </td>
                    <td style="padding: 0.65rem 0.85rem; color: #64748b; font-size: 0.8rem;">
                        Penambahan Nilai Bahan Masuk Ruang Produksi Curah (WIP)
                    </td>
                    <td style="padding: 0.65rem 0.85rem; text-align: right; font-family: monospace; font-weight: 700; color: #059669;">
                        Rp {{ number_format($totalNominal, 0, ',', '.') }}
                    </td>
                    <td style="padding: 0.65rem 0.85rem; text-align: right; color: #94a3b8;">-</td>
                </tr>
                <tr>
                    <td style="padding: 0.65rem 0.85rem; color: #0f172a;">
                        <span style="padding-left: 1.5rem;"><strong style="color: #dc2626;">1150</strong> &bull; Persediaan Bahan Baku &amp; Penolong</span>
                    </td>
                    <td style="padding: 0.65rem 0.85rem; color: #64748b; font-size: 0.8rem;">
                        Pengurangan Nilai Stok Fisik Singkong, Minyak &amp; Bumbu Gudang
                    </td>
                    <td style="padding: 0.65rem 0.85rem; text-align: right; color: #94a3b8;">-</td>
                    <td style="padding: 0.65rem 0.85rem; text-align: right; font-family: monospace; font-weight: 700; color: #dc2626;">
                        Rp {{ number_format($totalNominal, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: 700; border-top: 1px solid #cbd5e1;">
                    <td colspan="2" style="padding: 0.65rem 0.85rem; text-align: right; color: #0f172a;">
                        TOTAL BALANCE:
                    </td>
                    <td style="padding: 0.65rem 0.85rem; text-align: right; font-family: monospace; color: #059669;">
                        Rp {{ number_format($totalNominal, 0, ',', '.') }}
                    </td>
                    <td style="padding: 0.65rem 0.85rem; text-align: right; font-family: monospace; color: #059669;">
                        Rp {{ number_format($totalNominal, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- MODALS PARTIALS --}}
@include('keuangan.verifikasi_pemakaian.partials.modal-approve')
@include('keuangan.verifikasi_pemakaian.partials.modal-reject')
@include('keuangan.jurnal.partials.modal-detail-jurnal')

@endsection

@push('scripts')
    <script src="{{ asset('js/keuangan/verifikasi_pemakaian/verifikasi-pemakaian.js') }}"></script>
@endpush
