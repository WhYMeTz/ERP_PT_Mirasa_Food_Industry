@extends('layouts.app')

@section('title', 'Bukti Kas Keluar ' . $bkk->bayar_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/bayar/bayar-show.css') }}">
@endpush

@section('content')
{{-- ACTION TOOLBAR (HIDDEN ON PRINT) --}}
<div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
    <div style="display: flex; align-items: center; gap: 0.5rem;">
        <a href="{{ route('keuangan.bayar.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
            &larr; Riwayat Kas Keluar
        </a>
        <span style="color: #cbd5e1;">/</span>
        <h1 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0; font-family: monospace;">
            {{ $bkk->bayar_no }}
        </h1>
        @if ($bkk->status_cd === 'VOID')
            <span style="background: #fef2f2; color: #dc2626; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 6px; border: 1px solid #fecaca;">
                VOID (DIBATALKAN)
            </span>
        @else
            <span style="background: #f0fdf4; color: #166534; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 6px; border: 1px solid #bbf7d0;">
                POSTED (BERHASIL)
            </span>
        @endif
    </div>

    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <button type="button" class="btn btn-primary" onclick="window.print()" style="font-weight: 700; font-size: 0.8125rem; padding: 0.5rem 1.15rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>🖨️ Cetak Bukti Kas Keluar</span>
        </button>

        @if ($bkk->status_cd === 'POSTED')
            <form action="{{ route('keuangan.bayar.void', $bkk->bayar_id) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan (VOID) pembayaran {{ $bkk->bayar_no }}? Saldo kas/bank akan dikembalikan dan sisa hutang tagihan akan dipulihkan.');">
                @csrf
                <button type="submit" class="btn btn-danger" style="font-weight: 700; font-size: 0.8125rem; padding: 0.5rem 0.95rem; border-radius: 8px; background: #dc2626; border-color: #dc2626; color: #ffffff;">
                    Batalkan (VOID)
                </button>
            </form>
        @endif
    </div>
</div>

{{-- LEMBAR FORMAL BUKTI KAS/BANK KELUAR (SIAP CETAK) --}}
<div class="bkk-sheet-wrapper">
    {{-- KOP PERUSAHAAN & HEADER VOUCHER --}}
    <div class="bkk-header">
        <div>
            <h1 class="company-title">PT. MIRASA FOOD INDUSTRY</h1>
            <p class="company-sub">
                Produsen Makanan Ringan Olahan Keripik Singkong &amp; Tempe<br>
                Kawasan Industri Pabrik Mirasa &bull; Jawa Tengah, Indonesia<br>
                Telp: (0285) 441-234 &bull; Email: finance@mirasafood.co.id
            </p>
        </div>

        <div class="voucher-title">
            <h2>BUKTI KAS / BANK KELUAR</h2>
            <div class="voucher-no">{{ $bkk->bayar_no }}</div>
            <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                Tanggal: <strong>{{ date('d F Y', strtotime($bkk->tgl_bayar)) }}</strong>
            </div>
        </div>
    </div>

    {{-- INFORMASI REKANAN & REKENING --}}
    <div class="bkk-meta-grid">
        <div>
            <div style="font-size: 0.725rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 6px;">
                DIBAYARKAN KEPADA:
            </div>
            <div class="meta-row">
                <span>Nama Supplier</span>
                <span>{{ $bkk->supplier?->supplier_nm ?? ($bkk->details->first()?->hutang?->supplier?->supplier_nm ?? '-') }}</span>
            </div>
            <div class="meta-row">
                <span>Kode Rekanan</span>
                <span style="font-family: monospace;">{{ $bkk->supplier?->supplier_cd ?? ($bkk->details->first()?->hutang?->supplier?->supplier_cd ?? '-') }}</span>
            </div>
            <div class="meta-row">
                <span>Kontak Person</span>
                <span>{{ $bkk->penerima_nama ?: ($bkk->supplier?->kontak_no ?? ($bkk->details->first()?->hutang?->supplier?->kontak_no ?? '-')) }}</span>
            </div>
            <div class="meta-row">
                <span>Alamat</span>
                <span>{{ $bkk->supplier?->alamat_txt ?? ($bkk->details->first()?->hutang?->supplier?->alamat_txt ?? '-') }}</span>
            </div>
        </div>

        <div>
            <div style="font-size: 0.725rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 6px;">
                DIBAYAR MELALUI:
            </div>
            <div class="meta-row">
                <span>Rekening Sumber</span>
                <span>{{ $bkk->kasBank?->nama_akun ?? '-' }}</span>
            </div>
            <div class="meta-row">
                <span>No. Rekening</span>
                <span style="font-family: monospace;">{{ $bkk->kasBank?->nomor_rekening ?: ($bkk->kasBank?->no_rekening ?: 'KAS TUNAI') }}</span>
            </div>
            <div class="meta-row">
                <span>Metode Bayar</span>
                <span>{{ $bkk->metode_bayar }}</span>
            </div>
            <div class="meta-row">
                <span>No. Referensi / Trx</span>
                <span style="font-family: monospace;">{{ $bkk->no_referensi_bank ?: '-' }}</span>
            </div>
        </div>
    </div>

    {{-- TABEL ALOKASI RINCIAN PEMBAYARAN FAKTUR --}}
    <table style="width: 100%; border-collapse: collapse; font-size: 0.8125rem; margin-bottom: 1rem;">
        <thead>
            <tr style="background: #f1f5f9; border-top: 1.5px solid #cbd5e1; border-bottom: 1.5px solid #cbd5e1; font-size: 0.725rem; text-transform: uppercase; color: #334155;">
                <th style="padding: 0.65rem 0.75rem; text-align: center; width: 35px;">No.</th>
                <th style="padding: 0.65rem 0.75rem;">No. Tagihan Hutang (AP)</th>
                <th style="padding: 0.65rem 0.75rem;">Faktur Supplier</th>
                <th style="padding: 0.65rem 0.75rem; text-align: right; width: 130px;">Hutang Sebelum</th>
                <th style="padding: 0.65rem 0.75rem; text-align: right; width: 140px;">Nominal Bayar</th>
                <th style="padding: 0.65rem 0.75rem; text-align: right; width: 130px;">Sisa Hutang</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bkk->details as $idx => $dtl)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 0.65rem 0.75rem; text-align: center; color: #64748b;">
                        {{ $idx + 1 }}
                    </td>
                    <td style="padding: 0.65rem 0.75rem;">
                        <a href="{{ route('keuangan.hutang.show', $dtl->hutang_id) }}" style="font-weight: 700; color: #0284c7; text-decoration: none; font-family: monospace;">
                            {{ $dtl->hutang->hutang_no ?? '-' }}
                        </a>
                        <div style="font-size: 0.7rem; color: #64748b;">
                            Tgl: {{ date('d/m/Y', strtotime($dtl->hutang->tgl_tagihan ?? now())) }}
                        </div>
                    </td>
                    <td style="padding: 0.65rem 0.75rem; color: #334155;">
                        {{ $dtl->hutang->no_faktur_supplier ?: '-' }}
                    </td>
                    <td style="padding: 0.65rem 0.75rem; text-align: right; color: #64748b;">
                        Rp {{ number_format($dtl->sisa_hutang_sebelum, 0, ',', '.') }}
                    </td>
                    <td style="padding: 0.65rem 0.75rem; text-align: right; font-weight: 800; color: #16a34a;">
                        Rp {{ number_format($dtl->nominal_bayar, 0, ',', '.') }}
                    </td>
                    <td style="padding: 0.65rem 0.75rem; text-align: right; font-weight: 700; color: {{ $dtl->sisa_hutang_sesudah <= 0 ? '#16a34a' : '#dc2626' }};">
                        {{ $dtl->sisa_hutang_sesudah <= 0 ? 'LUNAS (Rp 0)' : 'Rp ' . number_format($dtl->sisa_hutang_sesudah, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="border-top: 2px solid #0f172a; font-size: 0.95rem; font-weight: 900;">
                <td colspan="4" style="padding: 0.75rem 0.75rem; text-align: right; text-transform: uppercase;">
                    Total Pengeluaran Kas/Bank:
                </td>
                <td style="padding: 0.75rem 0.75rem; text-align: right; color: #0284c7;">
                    Rp {{ number_format($bkk->total_bayar, 0, ',', '.') }}
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    {{-- TERBILANG RUPIAH --}}
    <div class="terbilang-box">
        <strong>Terbilang:</strong> {{ \App\Services\Keuangan\KasBankService::terbilang($bkk->total_bayar) }}
    </div>

    @if ($bkk->catatan)
        <div style="font-size: 0.775rem; color: #475569; margin-bottom: 1.5rem; background: #f8fafc; padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px solid #e2e8f0;">
            <strong>Catatan:</strong> {{ $bkk->catatan }}
        </div>
    @endif

    {{-- 4 KOLOM TANDA TANGAN FORMAL --}}
    <div class="signatures-grid">
        <div class="sign-box">
            <span class="sign-role">Dibuat Oleh,</span>
            <span class="sign-name">{{ $bkk->created_by ?: 'Kasir' }}</span>
        </div>

        <div class="sign-box">
            <span class="sign-role">Diperiksa Oleh,</span>
            <span class="sign-name">Accounting</span>
        </div>

        <div class="sign-box">
            <span class="sign-role">Disetujui Oleh,</span>
            <span class="sign-name">Direksi / Finance</span>
        </div>

        <div class="sign-box">
            <span class="sign-role">Diterima Oleh,</span>
            <span class="sign-name">{{ $bkk->penerima_nama ?: ($bkk->supplier?->supplier_nm ?? ($bkk->details->first()?->hutang?->supplier?->supplier_nm ?? 'Penerima')) }}</span>
        </div>
    </div>
</div>

@endsection
