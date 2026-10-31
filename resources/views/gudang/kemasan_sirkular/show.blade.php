@extends('layouts.app')

@section('title', 'Detail Penerimaan Kemasan Sirkular - ' . $kemasan->sirkular_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/kemasan_sirkular/sirkular-form.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/gudang/kemasan_sirkular/sirkular-index.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="sirkular-form-container">
    {{-- BREADCRUMBS & TOP BAR --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <a href="{{ route('gudang.kemasan-sirkular.index') }}" style="color: #64748b; font-size: 0.825rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.35rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Riwayat Kemasan Sirkular
            </a>
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <h1 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0; font-family: monospace;">
                    {{ $kemasan->sirkular_no }}
                </h1>
                @if($kemasan->status_cd === 'VOID')
                    <span class="badge-status-void">DIBATALKAN (VOID)</span>
                @else
                    <span class="badge-status-completed">SELESAI DITERIMA</span>
                @endif
            </div>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <a href="{{ route('gudang.kemasan-sirkular.index') }}" class="btn btn-secondary" style="font-size: 0.825rem; padding: 0.45rem 1rem;">Kembali</a>
            @if($kemasan->status_cd !== 'VOID')
                <button type="button" class="btn btn-outline-danger" style="font-size: 0.825rem; padding: 0.45rem 1rem;" onclick="openModalVoid()">
                    Batalkan Dokumen (Void)
                </button>
            @endif
        </div>
    </div>

    {{-- KARTU 1: INFO DOKUMEN & PENGIRIMAN --}}
    <div class="sirkular-card">
        <div class="sirkular-card-header">
            <div class="sirkular-card-title">
                <span>1. Dokumen Pengiriman &amp; Penerimaan Fisik</span>
            </div>
            <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">
                Dicatat: {{ $kemasan->created_at?->format('d/m/Y H:i') }} oleh {{ $kemasan->created_by }}
            </span>
        </div>

        <div class="sirkular-card-body">
            <div class="form-grid-3" style="margin-bottom: 1.25rem;">
                <div>
                    <span class="summary-label">Tanggal Masuk Fisik</span>
                    <strong style="font-size: 0.95rem; color: #0f172a;">{{ \Carbon\Carbon::parse($kemasan->sirkular_tgl)->format('d F Y') }}</strong>
                </div>
                <div>
                    <span class="summary-label">Customer / Pengirim</span>
                    <strong style="font-size: 0.95rem; color: #0f172a;">{{ $kemasan->customer?->customer_nm ?? 'Mitra Umum' }}</strong>
                </div>
                <div>
                    <span class="summary-label">Gudang Penyimpanan</span>
                    <strong style="font-size: 0.95rem; color: #0f172a;">{{ $kemasan->gudang?->gudang_nm }} ({{ $kemasan->gudang?->gudang_cd }})</strong>
                </div>
            </div>

            <div class="form-grid-3" style="margin-bottom: 1.25rem;">
                <div>
                    <span class="summary-label">Ref. Surat Jalan Pengiriman Mirasa (DO)</span>
                    @if($kemasan->pengeluaran)
                        <strong style="font-size: 0.95rem; color: #0284c7; font-family: monospace;">{{ $kemasan->pengeluaran->pengeluaran_no }}</strong>
                        <div style="font-size: 0.75rem; color: #64748b;">(Terkirim: {{ number_format($kemasan->pengeluaran->total_qty, 0, ',', '.') }} Box)</div>
                    @else
                        <span style="color: #94a3b8; font-size: 0.85rem;">- Tanpa Referensi DO -</span>
                    @endif
                </div>
                <div>
                    <span class="summary-label">No. Dokumen Eksternal / Bukti Serah Terima</span>
                    <strong style="font-size: 0.95rem; color: #0f172a;">{{ $kemasan->surat_jalan_customer_no ?: '-' }}</strong>
                </div>
                <div>
                    <span class="summary-label">Status Posting Stok</span>
                    <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 700; font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 4px;">
                        Tercatat di Kartu Stok
                    </span>
                </div>
            </div>

            <div class="form-grid-3" style="padding-top: 1rem; border-top: 1px dashed #e2e8f0;">
                <div>
                    <span class="summary-label">Nama Sopir</span>
                    <strong style="font-size: 0.95rem; color: #0f172a;">{{ $kemasan->supir_nm ?: '-' }}</strong>
                </div>
                <div>
                    <span class="summary-label">No. Polisi Kendaraan</span>
                    <strong style="font-size: 0.95rem; color: #0f172a; font-family: monospace;">{{ $kemasan->nopol_kendaraan ?: '-' }}</strong>
                </div>
                <div>
                    <span class="summary-label">Nama Ekspedisi / Armada</span>
                    <strong style="font-size: 0.95rem; color: #0f172a;">{{ $kemasan->ekspedisi_nm ?: 'Internal Mirasa' }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- KARTU 2: RINCIAN PENERIMAAN & HASIL SORTIR KEMASAN --}}
    <div class="sirkular-card">
        <div class="sirkular-card-header">
            <div class="sirkular-card-title">
                <span>2. Rincian Penerimaan &amp; Hasil Sortir Kemasan</span>
            </div>
        </div>

        <div class="sirkular-card-body">
            {{-- RINGKASAN REKONSILIASI KORPORAT --}}
            <div class="sirkular-summary-bar">
                <div class="sirkular-summary-col">
                    <span class="summary-label">Total Kirim (DO)</span>
                    <span class="summary-val">{{ number_format($kemasan->total_kirim_qty, 0, ',', '.') }} Box</span>
                </div>
                <div class="sirkular-summary-col">
                    <span class="summary-label">Fisik Kembali</span>
                    <span class="summary-val" style="color: #0284c7;">{{ number_format($kemasan->total_kembali_qty, 0, ',', '.') }} Box</span>
                </div>
                <div class="sirkular-summary-col">
                    <span class="summary-label">Layak (Puteran)</span>
                    <span class="summary-val" style="color: #059669;">{{ number_format($kemasan->total_mulus_qty, 0, ',', '.') }} Box</span>
                </div>
                <div class="sirkular-summary-col">
                    <span class="summary-label">Karton BMG</span>
                    <span class="summary-val" style="color: #6366f1;">{{ number_format($kemasan->total_bmg_qty, 0, ',', '.') }} Box</span>
                </div>
                <div class="sirkular-summary-col">
                    <span class="summary-label">Rusak (Reject)</span>
                    <span class="summary-val" style="color: #d97706;">{{ number_format($kemasan->total_rusak_qty, 0, ',', '.') }} Box</span>
                </div>
                <div class="sirkular-summary-col">
                    <span class="summary-label">Selisih Pengiriman</span>
                    <span class="summary-val" style="color: {{ $kemasan->total_selisih_qty > 0 ? '#dc2626' : '#059669' }};">
                        {{ number_format($kemasan->total_selisih_qty, 0, ',', '.') }} Box
                    </span>
                </div>
            </div>

            {{-- TABEL RINCIAN --}}
            <div style="overflow-x: auto;">
                <table class="table-sortir-input">
                    <thead>
                        <tr>
                            <th style="text-align: left; min-width: 240px;">Jenis Kemasan</th>
                            <th style="text-align: right; width: 120px;">Qty Kirim (Box)</th>
                            <th style="text-align: right; width: 130px;">Layak / Puteran (Pcs)</th>
                            <th style="text-align: right; width: 120px;">Karton BMG (Pcs)</th>
                            <th style="text-align: right; width: 130px;">Rusak / Reject (Pcs)</th>
                            <th style="text-align: right; width: 120px;">Total Kembali</th>
                            <th style="text-align: right; width: 100px;">Selisih</th>
                            <th style="min-width: 200px; text-align: left;">Keterangan Fisik</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kemasan->details as $dtl)
                            <tr>
                                <td>
                                    <div style="font-weight: 700; color: #0f172a;">{{ $dtl->barangAsal?->barang_nm ?? 'Karton Kemasan' }}</div>
                                    <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                                        @if($dtl->batch_no_mulus)
                                            Batch Mulus: <span style="font-family: monospace; color: #059669; font-weight: 600;">{{ $dtl->batch_no_mulus }}</span> &bull;
                                        @endif
                                        @if($dtl->batch_no_bmg)
                                            Batch BMG: <span style="font-family: monospace; color: #6366f1; font-weight: 600;">{{ $dtl->batch_no_bmg }}</span> &bull;
                                        @endif
                                        @if($dtl->batch_no_rusak)
                                            Batch Reject: <span style="font-family: monospace; color: #d97706; font-weight: 600;">{{ $dtl->batch_no_rusak }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td style="text-align: right; font-variant-numeric: tabular-nums; font-family: monospace;">
                                    {{ number_format($dtl->kirim_qty, 0, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-weight: 700; font-family: monospace;">
                                    {{ number_format($dtl->mulus_qty, 0, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-weight: 700; font-family: monospace; color: #6366f1;">
                                    {{ number_format($dtl->bmg_qty, 0, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-weight: 700; font-family: monospace;">
                                    {{ number_format($dtl->rusak_qty, 0, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-weight: 700; font-family: monospace; background: #f8fafc;">
                                    {{ number_format($dtl->kembali_qty, 0, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-weight: 700; font-family: monospace; color: {{ $dtl->selisih_qty > 0 ? '#dc2626' : '#059669' }};">
                                    {{ number_format($dtl->selisih_qty, 0, ',', '.') }}
                                </td>
                                <td>
                                    {{ $dtl->catatan_txt ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($kemasan->catatan_txt)
                <div style="margin-top: 1.25rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.75rem 1rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Catatan Dokumen:</div>
                    <div style="font-size: 0.85rem; color: #334155; margin-top: 0.25rem;">{{ $kemasan->catatan_txt }}</div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- MODAL VOID DIALOG --}}
@if($kemasan->status_cd !== 'VOID')
<div id="modalVoid" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(2px); align-items: center; justify-content: center;">
    <div style="background: #ffffff; width: 100%; max-width: 480px; border-radius: 8px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; margin: 1rem;">
        <div style="background: #fef2f2; border-bottom: 1px solid #fecaca; padding: 1rem 1.25rem; display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; flex-shrink: 0;">
                !
            </div>
            <div>
                <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #991b1b;">Konfirmasi Batalkan Dokumen (Void)</h4>
                <div style="font-size: 0.75rem; color: #b91c1c;">Tindakan ini akan membatalkan mutasi stok gudang.</div>
            </div>
        </div>

        <form action="{{ route('gudang.kemasan-sirkular.void', $kemasan->kemasan_sirkular_id) }}" method="POST">
            @csrf
            <div style="padding: 1.25rem;">
                <p style="font-size: 0.85rem; color: #334155; line-height: 1.5; margin-top: 0;">
                    Apakah Anda yakin ingin membatalkan dokumen <strong style="font-family: monospace;">{{ $kemasan->sirkular_no }}</strong>? Saldo stok kemasan yang telah masuk akan ditarik kembali secara otomatis.
                </p>

                <label for="alasan_batal" class="form-label" style="font-weight: 600; font-size: 0.825rem;">
                    Alasan Pembatalan <span style="color: #ef4444;">*</span>
                </label>
                <textarea name="alasan_batal" id="alasan_batal" rows="3" class="form-control" required placeholder="Tuliskan alasan pembatalan dokumen ini secara lengkap..." style="font-size: 0.85rem;"></textarea>
            </div>

            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.75rem 1.25rem; display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" onclick="closeModalVoid()" class="btn btn-secondary" style="font-size: 0.825rem; padding: 0.45rem 1rem;">Tutup</button>
                <button type="submit" class="btn btn-danger" style="font-size: 0.825rem; padding: 0.45rem 1rem; font-weight: 700;">Ya, Batalkan Dokumen</button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
    <script src="{{ asset('js/gudang/kemasan_sirkular/sirkular-show.js') }}?v={{ time() }}"></script>
@endpush

@endsection
