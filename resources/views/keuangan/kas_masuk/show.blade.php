@extends('layouts.app')

@section('title', 'Bukti Kas Masuk - ' . $bkm->bkm_no)

@push('styles')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #printableVoucher, #printableVoucher * {
            visibility: visible;
        }
        #printableVoucher {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            border: none !important;
            box-shadow: none !important;
        }
        .no-print {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')
<div style="max-width: 900px; margin: 0 auto;">

    {{-- TOP ACTION TOOLBAR (NO-PRINT) --}}
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <a href="{{ route('keuangan.kas_masuk.index') }}" class="btn btn-secondary" style="font-weight: 600; font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Daftar BKM</span>
            </a>
            <span style="font-size: 0.85rem; color: #64748b;">
                No. Transaksi: <strong style="color: #0f172a; font-family: ui-monospace, SFMono-Regular, monospace;">{{ $bkm->bkm_no }}</strong>
            </span>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <button type="button" onclick="window.print()" class="btn btn-secondary" style="font-weight: 700; font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Voucher</span>
            </button>

            @if ($bkm->status_cd !== 'VOID' && Auth::user()->canVoidKasMasuk())
                <button type="button" onclick="openModalVoidBkm({{ $bkm->bkm_id }}, '{{ $bkm->bkm_no }}', 'Rp {{ number_format($bkm->total_terima, 0, ',', '.') }}')" class="btn" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; font-weight: 700; font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Batalkan BKM (Void)</span>
                </button>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="no-print" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 0.875rem 1.25rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.85rem; font-weight: 600;">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if ($bkm->status_cd === 'VOID')
        <div class="no-print" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.875rem 1.25rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.85rem;">
            <strong>⚠️ VOUCHER TELAH DIBATALKAN (VOID)</strong>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.8rem;">
                Alasan: <em>"{{ $bkm->alasan_void ?? '-' }}"</em> oleh {{ $bkm->void_by ?? 'Sistem' }} pada {{ $bkm->void_dt ? \Carbon\Carbon::parse($bkm->void_dt)->format('d/m/Y H:i') : '-' }}. Saldo kas/bank telah disesuaikan kembali.
            </p>
        </div>
    @endif

    {{-- KERTAS VOUCHER BUKTI KAS MASUK (SIAP CETAK) --}}
    <div id="printableVoucher" class="card" style="padding: 2.5rem; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        
        {{-- KOP SURAT --}}
        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0f172a; padding-bottom: 1.25rem; margin-bottom: 1.5rem;">
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 900; color: #0f172a; margin: 0; text-transform: uppercase; letter-spacing: -0.01em;">
                    PT MIRASA FOOD INDUSTRY
                </h2>
                <div style="font-size: 0.775rem; color: #475569; margin-top: 0.25rem; line-height: 1.4;">
                    Produsen Keripik Singkong &amp; Makanan Ringan Berkualitas<br>
                    Jl. Magelang - Purworejo, Jawa Tengah, Indonesia<br>
                    Telp: (0275) 321-xxx | Email: finance@mirasafood.com
                </div>
            </div>

            <div style="text-align: right;">
                <div style="font-size: 1.35rem; font-weight: 900; color: #059669; letter-spacing: 0.03em; text-transform: uppercase;">
                    BUKTI KAS MASUK
                </div>
                <div style="font-size: 0.825rem; font-weight: 800; color: #0f172a; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; margin-top: 2px;">
                    NO: {{ $bkm->bkm_no }}
                </div>
                <div style="margin-top: 6px;">
                    @if ($bkm->status_cd === 'VOID')
                        <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 800; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">
                            BATAL / VOID
                        </span>
                    @else
                        <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 800; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;">
                            LUNAS / POSTED
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- INFORMASI PENERIMAAN --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; font-size: 0.85rem;">
            <div>
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 130px; color: #64748b; padding: 3px 0;">Tanggal Terima</td>
                        <td style="font-weight: 700; color: #0f172a;">: {{ \Carbon\Carbon::parse($bkm->tgl_terima)->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; padding: 3px 0;">Diterima Dari</td>
                        <td style="font-weight: 800; color: #0f172a;">: {{ $bkm->penyetor_nama ?: '-' }}</td>
                    </tr>
                    @if ($bkm->customer)
                        <tr>
                            <td style="color: #64748b; padding: 3px 0;">Mitra Customer</td>
                            <td style="font-weight: 700; color: #2563eb;">: {{ $bkm->customer->customer_nm }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td style="color: #64748b; padding: 3px 0;">Kategori</td>
                        <td style="font-weight: 700; color: #0f172a;">: {{ $bkm->kategori_badge['label'] }}</td>
                    </tr>
                </table>
            </div>

            <div>
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 130px; color: #64748b; padding: 3px 0;">Rekening Tujuan</td>
                        <td style="font-weight: 800; color: #059669;">: {{ $bkm->kasBank->kas_bank_nm ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; padding: 3px 0;">No. Rekening</td>
                        <td style="font-weight: 600; color: #0f172a;">: {{ ($bkm->kasBank && $bkm->kasBank->nomor_rekening) ? $bkm->kasBank->nomor_rekening : 'Kas Fisik Pabrik' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; padding: 3px 0;">Metode Bayar</td>
                        <td style="font-weight: 700; color: #0f172a;">: {{ $bkm->metode_terima }}</td>
                    </tr>
                    @if ($bkm->no_referensi_bank)
                        <tr>
                            <td style="color: #64748b; padding: 3px 0;">No. Referensi / Trf</td>
                            <td style="font-weight: 700; font-family: ui-monospace, monospace; color: #0f172a;">: {{ $bkm->no_referensi_bank }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- TABEL RINCIAN ITEM PENERIMAAN --}}
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; font-size: 0.85rem;">
            <thead>
                <tr style="background: #f8fafc; border-top: 1.5px solid #0f172a; border-bottom: 1.5px solid #0f172a;">
                    <th style="width: 40px; text-align: center; padding: 0.6rem 0.5rem; font-weight: 800;">NO</th>
                    <th style="padding: 0.6rem 0.75rem; text-align: left; font-weight: 800;">RINCIAN PENERIMAAN</th>
                    <th style="width: 100px; text-align: right; padding: 0.6rem 0.5rem; font-weight: 800;">QTY</th>
                    <th style="width: 80px; text-align: center; padding: 0.6rem 0.5rem; font-weight: 800;">SATUAN</th>
                    <th style="width: 140px; text-align: right; padding: 0.6rem 0.75rem; font-weight: 800;">HARGA (RP)</th>
                    <th style="width: 160px; text-align: right; padding: 0.6rem 0.75rem; font-weight: 800;">JUMLAH (RP)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bkm->details as $idx => $dtl)
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="text-align: center; padding: 0.6rem 0.5rem; color: #64748b;">{{ $idx + 1 }}</td>
                        <td style="padding: 0.6rem 0.75rem; font-weight: 600; color: #0f172a;">{{ $dtl->uraian }}</td>
                        <td style="text-align: right; padding: 0.6rem 0.5rem; font-family: ui-monospace, monospace;">{{ number_format($dtl->qty, 2, ',', '.') }}</td>
                        <td style="text-align: center; padding: 0.6rem 0.5rem; color: #64748b;">{{ $dtl->satuan ?: '-' }}</td>
                        <td style="text-align: right; padding: 0.6rem 0.75rem; font-family: ui-monospace, monospace;">{{ number_format($dtl->harga_satuan, 0, ',', '.') }}</td>
                        <td style="text-align: right; padding: 0.6rem 0.75rem; font-weight: 700; color: #0f172a; font-family: ui-monospace, monospace;">{{ number_format($dtl->nominal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="border-top: 1.5px solid #0f172a; border-bottom: 2px solid #0f172a; background: #f8fafc;">
                    <td colspan="5" style="padding: 0.75rem 0.75rem; text-align: right; font-weight: 800; font-size: 0.9rem; text-transform: uppercase;">
                        TOTAL DITERIMA:
                    </td>
                    <td style="padding: 0.75rem 0.75rem; text-align: right; font-weight: 900; font-size: 1.05rem; color: #059669; font-family: ui-monospace, monospace;">
                        Rp {{ number_format($bkm->total_terima, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>

        {{-- BOX TERBILANG & CATATAN --}}
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.875rem 1rem; margin-bottom: 2rem; font-size: 0.8125rem;">
            <div style="margin-bottom: 0.35rem;">
                <span style="color: #64748b;">Terbilang:</span>
                <strong style="color: #0f172a; font-style: italic;">
                    # {{ ucwords($bkm->terbilang) }} Rupiah #
                </strong>
            </div>
            @if ($bkm->catatan)
                <div style="color: #475569;">
                    <span style="color: #64748b;">Catatan:</span> {{ $bkm->catatan }}
                </div>
            @endif
        </div>

        {{-- LEMBAR TANDA TANGAN 3 KOLOM --}}
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; text-align: center; font-size: 0.8125rem;">
            <div>
                <div style="color: #64748b; margin-bottom: 3.5rem;">Penyetor / Pihak Pembayar,</div>
                <div style="font-weight: 700; color: #0f172a; text-decoration: underline;">
                    ( {{ $bkm->penyetor_nama ?: 'Penyetor' }} )
                </div>
                <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">Tanda Tangan &amp; Nama Terang</div>
            </div>

            <div>
                <div style="color: #64748b; margin-bottom: 3.5rem;">Diterima Oleh (Finance / Kasir),</div>
                <div style="font-weight: 700; color: #0f172a; text-decoration: underline;">
                    ( {{ $bkm->created_by ?: 'Kasir Mirasa' }} )
                </div>
                <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">Bagian Keuangan</div>
            </div>

            <div>
                <div style="color: #64748b; margin-bottom: 3.5rem;">Mengetahui / Disetujui,</div>
                <div style="font-weight: 700; color: #0f172a; text-decoration: underline;">
                    ( Manajer / Direksi )
                </div>
                <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">Pimpinan Perusahaan</div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL VOID BKM PARTIAL --}}
@include('keuangan.kas_masuk.partials.modal-void-bkm')

@endsection

@push('scripts')
    <script src="{{ asset('js/keuangan/kas_masuk/kas-masuk-index.js') }}"></script>
    @if (request()->has('print'))
        <script>
            window.addEventListener('load', function() {
                setTimeout(function() {
                    window.print();
                }, 300);
            });
        </script>
    @endif
@endpush
