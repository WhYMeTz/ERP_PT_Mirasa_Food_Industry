@extends('layouts.app')

@section('title', 'Bukti Penerimaan Pembayaran Customer (' . $bkm->bkm_no . ') - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/bayar_piutang/bayar-piutang-index.css') }}?v={{ file_exists(public_path('css/keuangan/bayar_piutang/bayar-piutang-index.css')) ? filemtime(public_path('css/keuangan/bayar_piutang/bayar-piutang-index.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('css/keuangan/bayar_piutang/bayar-piutang-form.css') }}?v={{ file_exists(public_path('css/keuangan/bayar_piutang/bayar-piutang-form.css')) ? filemtime(public_path('css/keuangan/bayar_piutang/bayar-piutang-form.css')) : time() }}">
@endpush

@php
if (!function_exists('penyebutRupiah')) {
    function penyebutRupiah($nilai) {
        $nilai = abs($nilai);
        $huruf = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
        $temp = "";
        if ($nilai < 12) {
            $temp = " " . $huruf[(int)$nilai];
        } else if ($nilai < 20) {
            $temp = penyebutRupiah($nilai - 10) . " Belas";
        } else if ($nilai < 100) {
            $temp = penyebutRupiah((int)($nilai / 10)) . " Puluh" . penyebutRupiah($nilai % 10);
        } else if ($nilai < 200) {
            $temp = " Seratus" . penyebutRupiah($nilai - 100);
        } else if ($nilai < 1000) {
            $temp = penyebutRupiah((int)($nilai / 100)) . " Ratus" . penyebutRupiah($nilai % 100);
        } else if ($nilai < 2000) {
            $temp = " Seribu" . penyebutRupiah($nilai - 1000);
        } else if ($nilai < 1000000) {
            $temp = penyebutRupiah((int)($nilai / 1000)) . " Ribu" . penyebutRupiah(fmod($nilai, 1000));
        } else if ($nilai < 1000000000) {
            $temp = penyebutRupiah((int)($nilai / 1000000)) . " Juta" . penyebutRupiah(fmod($nilai, 1000000));
        } else if ($nilai < 1000000000000) {
            $temp = penyebutRupiah((int)($nilai / 1000000000)) . " Milyar" . penyebutRupiah(fmod($nilai, 1000000000));
        }
        return $temp;
    }
}
if (!function_exists('terbilangRupiah')) {
    function terbilangRupiah($nilai) {
        if ($nilai < 0) {
            return "Minus " . trim(penyebutRupiah($nilai)) . " Rupiah";
        }
        if ($nilai == 0) {
            return "Nol Rupiah";
        }
        return trim(penyebutRupiah($nilai)) . " Rupiah";
    }
}

$isVoid = ($bkm->status_cd === 'VOID');
$hasRemaining = false;
if (!$isVoid) {
    $hasRemaining = $bkm->details->contains(fn($d) => (float)$d->sisa_piutang_sesudah > 0.0001);
}
@endphp

@section('content')
<div class="ar-page-wrapper">

    {{-- Action Bar (Non-Print) --}}
    <div class="no-print" style="max-width: 880px; margin: 0 auto 1.5rem auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('keuangan.bayar_piutang.index') }}"
               class="btn-header-secondary"
               style="padding: 0.5rem 0.95rem;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Riwayat</span>
            </a>

            @if ($isVoid)
                <span class="pill-status pill-status-void">
                    <span class="pill-dot"></span>
                    <span>Dibatalkan (VOID)</span>
                </span>
            @elseif ($hasRemaining)
                <span class="pill-status pill-status-sebagian">
                    <span class="pill-dot"></span>
                    <span>Cicilan (Sebagian)</span>
                </span>
            @else
                <span class="pill-status pill-status-lunas">
                    <span class="pill-dot"></span>
                    <span>Lunas Penuh</span>
                </span>
            @endif
        </div>

        <div style="display: flex; align-items: center; gap: 8px;">
            <button type="button"
                    onclick="window.print()"
                    class="btn-pill-dark"
                    style="border-radius: 8px; padding: 0.55rem 1.15rem;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak Bukti Pembayaran</span>
            </button>

            @if ($bkm->status_cd === 'POSTED' && Auth::user()->canVoidBayarPiutang())
                <button type="button"
                        class="btn-header-secondary"
                        onclick="openModalVoid('{{ $bkm->bayar_id }}', '{{ $bkm->bkm_no }}', 'Rp {{ number_format($bkm->total_bayar, 0, ',', '.') }}')"
                        style="border-color: #fee2e2; color: #dc2626; padding: 0.5rem 0.95rem;">
                    Batalkan (VOID)
                </button>
            @endif
        </div>
    </div>

    {{-- Kuitansi Bukti Penerimaan Pembayaran Customer Resmi --}}
    <div class="receipt-wrapper">
        {{-- Header Dokumen --}}
        <div class="receipt-header">
            <div>
                <h1 class="receipt-company-title">PT MIRASA FOOD INDUSTRY</h1>
                <p class="receipt-company-sub">
                    Pabrik &amp; Kantor: Jl. Industri Mirasa No. 1, Kab. Subang, Jawa Barat<br>
                    Divisi Keuangan &amp; Akuntansi Penjualan
                </p>
            </div>
            <div class="receipt-title-box">
                <div class="receipt-doc-name">BUKTI PENERIMAAN PEMBAYARAN CUSTOMER</div>
                <div class="receipt-doc-no">{{ $bkm->bkm_no }}</div>
                <div style="font-size: 0.775rem; color: #64748b; margin-top: 3px;">
                    Tanggal: {{ $bkm->tgl_bayar ? $bkm->tgl_bayar->format('d F Y') : '-' }}
                </div>
            </div>
        </div>

        {{-- Meta Data Penerimaan --}}
        <div class="receipt-meta-grid">
            <div class="receipt-meta-item">
                <div class="receipt-meta-label">Diterima Dari (Penyetor):</div>
                <div class="receipt-meta-value">{{ $bkm->diterima_dari }}</div>
                <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                    Pelanggan: <strong>{{ $bkm->customer?->customer_nm ?? '-' }}</strong> ({{ $bkm->customer?->customer_cd ?? 'CUST' }})
                </div>
            </div>

            <div class="receipt-meta-item">
                <div class="receipt-meta-label">Rekening Kas / Bank Penerima:</div>
                <div class="receipt-meta-value">{{ $bkm->kasBank?->nama_akun ?? '-' }}</div>
                <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                    Akun: {{ $bkm->kasBank?->kode_akun ?? '-' }} &bull; Metode: <strong>{{ $bkm->metode_bayar }}</strong>
                    @if ($bkm->no_referensi_bank)
                        &bull; Ref: {{ $bkm->no_referensi_bank }}
                    @endif
                </div>
            </div>
        </div>

        {{-- Terbilang Box --}}
        <div class="terbilang-box">
            <strong>Terbilang:</strong> {{ terbilangRupiah((float) $bkm->total_bayar) }}
        </div>

        {{-- Rincian Alokasi Faktur Penjualan --}}
        <div style="margin-bottom: 1.5rem;">
            <div style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">
                Rincian Pelunasan Faktur Penjualan:
            </div>
            <table style="width: 100%; border-collapse: collapse; font-size: 0.8125rem;">
                <thead>
                    <tr style="background: #f8fafc; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; color: #475569; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.04em;">
                        <th style="padding: 0.7rem 0.6rem; text-align: left; width: 35px;">No</th>
                        <th style="padding: 0.7rem 0.6rem; text-align: left;">No. Faktur</th>
                        <th style="padding: 0.7rem 0.6rem; text-align: left;">Tgl Faktur</th>
                        <th style="padding: 0.7rem 0.6rem; text-align: right;">Piutang Sebelum</th>
                        <th style="padding: 0.7rem 0.6rem; text-align: right;">Nominal Diterima</th>
                        <th style="padding: 0.7rem 0.6rem; text-align: right;">Sisa Piutang</th>
                        <th style="padding: 0.7rem 0.6rem; text-align: left;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bkm->details as $idx => $dtl)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.7rem 0.6rem; text-align: center; color: #64748b;">{{ $idx + 1 }}</td>
                            <td style="padding: 0.7rem 0.6rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-weight: 700; color: #0f172a;">
                                {{ $dtl->faktur?->faktur_no ?? '-' }}
                            </td>
                            <td style="padding: 0.7rem 0.6rem; color: #475569;">
                                {{ $dtl->faktur?->faktur_tgl ? $dtl->faktur->faktur_tgl->format('d/m/Y') : '-' }}
                            </td>
                            <td style="padding: 0.7rem 0.6rem; text-align: right; font-family: monospace; color: #64748b;">
                                Rp {{ number_format($dtl->sisa_piutang_sebelum, 0, ',', '.') }}
                            </td>
                            <td style="padding: 0.7rem 0.6rem; text-align: right; font-family: monospace; font-weight: 700; color: #0f172a;">
                                Rp {{ number_format($dtl->nominal_bayar, 0, ',', '.') }}
                            </td>
                            <td style="padding: 0.7rem 0.6rem; text-align: right; font-family: monospace; color: {{ $dtl->sisa_piutang_sesudah <= 0.0001 ? '#15803d' : '#b91c1c' }}; font-weight: 700;">
                                Rp {{ number_format($dtl->sisa_piutang_sesudah, 0, ',', '.') }}
                            </td>
                            <td style="padding: 0.7rem 0.6rem; color: #64748b; font-size: 0.75rem;">
                                {{ $dtl->catatan ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="border-top: 2px solid #0f172a; font-weight: 800; font-size: 0.875rem;">
                        <td colspan="4" style="padding: 0.85rem 0.6rem; text-align: right; text-transform: uppercase;">
                            Total Pembayaran Kas Diterima:
                        </td>
                        <td style="padding: 0.85rem 0.6rem; text-align: right; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; color: #0f172a; font-size: 1.05rem;">
                            Rp {{ number_format($bkm->total_bayar, 0, ',', '.') }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if ($bkm->catatan)
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.8rem; color: #475569; margin-bottom: 1.5rem;">
                <strong style="color: #0f172a;">Catatan Pembayaran:</strong>
                <p style="margin: 2px 0 0 0; white-space: pre-line;">{{ $bkm->catatan }}</p>
            </div>
        @endif

        {{-- Jurnal Otomatis Terkait (Non-Print) --}}
        @if ($bkm->jurnal)
            <div class="no-print" style="margin-top: 1.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem 1.15rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.04em;">
                        Entri Jurnal Umum Otomatis (No: {{ $bkm->jurnal->jurnal_no }})
                    </span>
                    <span style="font-size: 0.7rem; color: #15803d; font-weight: 700;">Double Entry Seimbang</span>
                </div>
                <table style="width: 100%; border-collapse: collapse; font-size: 0.775rem;">
                    <tbody>
                        @foreach ($bkm->jurnal->details as $jDtl)
                            <tr style="border-bottom: 1px dashed #e2e8f0;">
                                <td style="padding: 4px 0; font-family: monospace; width: 70px; color: #64748b;">{{ $jDtl->coa?->kode_akun ?? '-' }}</td>
                                <td style="padding: 4px 8px; color: #334155;">{{ $jDtl->coa?->nama_akun ?? '-' }}</td>
                                <td style="padding: 4px 8px; text-align: right; font-family: monospace; width: 130px;">
                                    {{ $jDtl->debet > 0 ? 'Rp ' . number_format($jDtl->debet, 0, ',', '.') : '-' }}
                                </td>
                                <td style="padding: 4px 8px; text-align: right; font-family: monospace; width: 130px;">
                                    {{ $jDtl->kredit > 0 ? 'Rp ' . number_format($jDtl->kredit, 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Kotak Tanda Tangan --}}
        <div class="signature-grid">
            <div class="signature-box">
                <div class="signature-title">Diterima Oleh (Kasir)</div>
                <div>
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $bkm->created_by ?? 'Kasir Keuangan' }}</div>
                </div>
            </div>

            <div class="signature-box">
                <div class="signature-title">Disetujui (Manajer Keuangan)</div>
                <div>
                    <div class="signature-line"></div>
                    <div class="signature-name">( ____________________ )</div>
                </div>
            </div>

            <div class="signature-box">
                <div class="signature-title">Penyetor / Pelanggan</div>
                <div>
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $bkm->diterima_dari }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Include Modal Konfirmasi VOID --}}
@include('keuangan.bayar_piutang.partials.modal-void')

@endsection

@push('scripts')
    <script>
        window.appConfig = {
            baseUrl: '{{ url('') }}'
        };
    </script>
    <script src="{{ asset('js/keuangan/bayar_piutang/bayar-piutang-index.js') }}?v={{ file_exists(public_path('js/keuangan/bayar_piutang/bayar-piutang-index.js')) ? filemtime(public_path('js/keuangan/bayar_piutang/bayar-piutang-index.js')) : time() }}"></script>
@endpush
