@extends('layouts.app')

@section('title', 'Bukti Kas Masuk - ' . $bkm->bkm_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/kas_masuk/kas-masuk-index.css') }}?v={{ file_exists(public_path('css/keuangan/kas_masuk/kas-masuk-index.css')) ? filemtime(public_path('css/keuangan/kas_masuk/kas-masuk-index.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('css/keuangan/kas_masuk/kas-masuk-show.css') }}?v={{ file_exists(public_path('css/keuangan/kas_masuk/kas-masuk-show.css')) ? filemtime(public_path('css/keuangan/kas_masuk/kas-masuk-show.css')) : time() }}">
@endpush

@section('content')
<div style="max-width: 960px; margin: 0 auto; padding-bottom: 3rem;">

    {{-- TOP ACTION TOOLBAR (NO-PRINT) --}}
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <a href="{{ route('keuangan.kas_masuk.index') }}" class="btn-pill-outline" style="font-size: 0.8125rem;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Daftar BKM</span>
            </a>
            <span style="font-size: 0.85rem; color: #64748b;">
                No. Transaksi: <strong style="color: #0f172a; font-family: ui-monospace, SFMono-Regular, monospace;">{{ $bkm->bkm_no }}</strong>
            </span>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            {{-- Tombol Cetak Slip Kuitansi / Voucher --}}
            <button type="button" onclick="window.print()" class="btn-pill-outline" style="font-size: 0.8125rem;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak Kuitansi / Voucher</span>
            </button>

            {{-- Tombol Verifikasi & Terbitkan Jurnal (Khusus DRAFT) --}}
            @if ($bkm->status_cd === 'DRAFT')
                @php
                    $formattedTotal = 'Rp ' . number_format($bkm->total_terima, 0, ',', '.');
                    $tglFmt = $bkm->tgl_terima ? \Carbon\Carbon::parse($bkm->tgl_terima)->format('d/m/Y') : '-';
                    $penyetorTxt = $bkm->penyetor_nama ?: '-';
                    $kasBankNm = $bkm->kasBank->nama_akun ?? 'Kas/Bank';
                @endphp
                <button type="button" 
                        class="btn-pill-emerald" 
                        onclick="openModalVerifikasiBkm({{ $bkm->bkm_id }}, '{{ $bkm->bkm_no }}', '{{ addslashes($penyetorTxt) }}', '{{ addslashes($kasBankNm) }}', '{{ $formattedTotal }}', '{{ $tglFmt }}')">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Verifikasi &amp; Terbitkan Jurnal</span>
                </button>
            @endif

            {{-- Tombol Void / Batal (Khusus DRAFT & POSTED) --}}
            @if ($bkm->status_cd !== 'VOID' && (Auth::user()?->canVoidKasMasuk() ?? true))
                <button type="button" 
                        onclick="openModalVoidBkm({{ $bkm->bkm_id }}, '{{ $bkm->bkm_no }}', 'Rp {{ number_format($bkm->total_terima, 0, ',', '.') }}')" 
                        class="btn-pill-rose" 
                        style="font-size: 0.8125rem;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span>Batalkan BKM (Void)</span>
                </button>
            @endif
        </div>
    </div>

    {{-- ALERTS --}}
    @if (session('success'))
        <div class="no-print" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 0.875rem 1.25rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- STATUS NOTIFICATION BANNERS --}}
    @if ($bkm->status_cd === 'DRAFT')
        <div class="no-print" style="background: #fffbeb; border: 1.5px solid #fde68a; color: #92400e; padding: 1rem 1.25rem; border-radius: 10px; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <div style="display: flex; align-items: flex-start; gap: 10px;">
                <svg width="22" height="22" fill="none" stroke="#d97706" viewBox="0 0 24 24" style="flex-shrink: 0; margin-top: 2px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <strong style="font-size: 0.95rem; color: #b45309;">STATUS: DRAFT (MENUNGGU VERIFIKASI AKUNTANSI)</strong>
                    <p style="margin: 0.25rem 0 0 0; font-size: 0.825rem; color: #92400e; line-height: 1.4;">
                        Uang kas/bank fisik telah bertambah dan mutasi kasir tercatat. Bagian Akuntansi perlu memverifikasi pos akun pendapatan/modal kredit untuk menerbitkan Jurnal Umum GL.
                    </p>
                </div>
            </div>
            <button type="button" 
                    class="btn-pill-emerald" 
                    onclick="openModalVerifikasiBkm({{ $bkm->bkm_id }}, '{{ $bkm->bkm_no }}', '{{ addslashes($penyetorTxt ?? '') }}', '{{ addslashes($kasBankNm ?? '') }}', '{{ $formattedTotal ?? '' }}', '{{ $tglFmt ?? '' }}')">
                <span>Verifikasi Sekarang</span>
            </button>
        </div>
    @elseif ($bkm->status_cd === 'POSTED')
        {{-- INFO JURNAL GL JIKA SUDAH TERVERIFIKASI --}}
        <div class="no-print" style="background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #065f46; padding: 1rem 1.25rem; border-radius: 10px; margin-bottom: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="badge-status badge-status-posted">POSTED &amp; TERVERIFIKASI</span>
                    <strong style="font-size: 0.95rem; color: #047857;">Jurnal Umum GL Telah Diterbitkan</strong>
                </div>
                @if ($bkm->jurnal)
                    <a href="{{ route('keuangan.jurnal.index', ['search' => $bkm->jurnal->jurnal_no]) }}" class="bkm-notice-link" style="font-weight: 700; color: #059669;">
                        Lihat di Buku Jurnal Umum &rarr;
                    </a>
                @endif
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem; font-size: 0.8125rem; margin-top: 0.5rem; background: #ffffff; padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid #d1fae5;">
                <div>
                    <span style="color: #64748b; display: block;">No. Referensi Jurnal GL:</span>
                    <strong style="color: #0f172a; font-family: ui-monospace, monospace;">{{ $bkm->jurnal->jurnal_no ?? 'JRN-TERBIT' }}</strong>
                </div>
                <div>
                    <span style="color: #64748b; display: block;">Pos Akun Lawan (Kredit):</span>
                    <strong style="color: #059669;">{{ $bkm->coa ? ($bkm->coa->kode_akun . ' - ' . $bkm->coa->nama_akun) : 'Pendapatan/Modal' }}</strong>
                </div>
                <div>
                    <span style="color: #64748b; display: block;">Rekening Penerima (Debet):</span>
                    <strong style="color: #0f172a;">{{ $bkm->kasBank->nama_akun ?? '-' }}</strong>
                </div>
                <div>
                    <span style="color: #64748b; display: block;">Status Keseimbangan:</span>
                    <strong style="color: #059669;">100% Balanced (Double-Entry)</strong>
                </div>
            </div>
        </div>
    @elseif ($bkm->status_cd === 'VOID')
        <div class="no-print" style="background: #fef2f2; border: 1.5px solid #fecaca; color: #991b1b; padding: 1rem 1.25rem; border-radius: 10px; margin-bottom: 1.25rem; font-size: 0.85rem;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 0.25rem;">
                <span class="badge-status badge-status-void">DIBATALKAN / VOID</span>
                <strong style="font-size: 0.95rem;">Dokumen Bukti Kas Masuk Ini Telah Dibatalkan</strong>
            </div>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.8125rem; color: #b91c1c;">
                Alasan: <em>"{{ $bkm->alasan_void ?? '-' }}"</em> oleh {{ $bkm->void_by ?? 'Sistem' }} pada {{ $bkm->void_dt ? \Carbon\Carbon::parse($bkm->void_dt)->format('d/m/Y H:i') : '-' }}. Saldo kas/bank dan Jurnal GL telah dibatalkan secara otomatis.
            </p>
        </div>
    @endif

    {{-- KERTAS VOUCHER BUKTI KAS MASUK (PRINTABLE VOUCHER) --}}
    <div id="printableVoucher" style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 2.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        
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
                    BUKTI KAS MASUK (BKM)
                </div>
                <div style="font-size: 0.875rem; font-weight: 800; color: #0f172a; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; margin-top: 2px;">
                    NO: {{ $bkm->bkm_no }}
                </div>
                <div style="margin-top: 6px;">
                    @if ($bkm->status_cd === 'DRAFT')
                        <span class="badge-status badge-status-draft">DRAFT (MENUNGGU VERIFIKASI)</span>
                    @elseif ($bkm->status_cd === 'POSTED')
                        <span class="badge-status badge-status-posted">POSTED / LUNAS</span>
                    @elseif ($bkm->status_cd === 'VOID')
                        <span class="badge-status badge-status-void">BATAL / VOID</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- INFORMASI PENERIMAAN --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; font-size: 0.85rem;">
            <div>
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 140px; color: #64748b; padding: 4px 0;">Tanggal Terima</td>
                        <td style="font-weight: 700; color: #0f172a;">: {{ \Carbon\Carbon::parse($bkm->tgl_terima)->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; padding: 4px 0;">Diterima Dari</td>
                        <td style="font-weight: 800; color: #0f172a;">: {{ $bkm->penyetor_nama ?: '-' }}</td>
                    </tr>
                    @if ($bkm->customer)
                        <tr>
                            <td style="color: #64748b; padding: 4px 0;">Mitra Customer</td>
                            <td style="font-weight: 700; color: #2563eb;">: {{ $bkm->customer->customer_nm }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td style="color: #64748b; padding: 4px 0;">Kategori</td>
                        <td style="font-weight: 700; color: #0f172a;">: {{ $bkm->kategori_badge['label'] }}</td>
                    </tr>
                </table>
            </div>

            <div>
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 140px; color: #64748b; padding: 4px 0;">Rekening Tujuan</td>
                        <td style="font-weight: 800; color: #059669;">: {{ $bkm->kasBank->nama_akun ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; padding: 4px 0;">No. Rekening</td>
                        <td style="font-weight: 600; color: #0f172a;">: {{ ($bkm->kasBank && $bkm->kasBank->nomor_rekening) ? $bkm->kasBank->nomor_rekening : 'Kas Fisik Pabrik' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; padding: 4px 0;">Metode Bayar</td>
                        <td style="font-weight: 700; color: #0f172a;">: {{ $bkm->metode_terima }}</td>
                    </tr>
                    @if ($bkm->no_referensi_bank)
                        <tr>
                            <td style="color: #64748b; padding: 4px 0;">No. Referensi / Trf</td>
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
                    <th style="width: 40px; text-align: center; padding: 0.65rem 0.5rem; font-weight: 800;">NO</th>
                    <th style="padding: 0.65rem 0.75rem; text-align: left; font-weight: 800;">RINCIAN PENERIMAAN</th>
                    <th style="width: 100px; text-align: right; padding: 0.65rem 0.5rem; font-weight: 800;">QTY</th>
                    <th style="width: 80px; text-align: center; padding: 0.65rem 0.5rem; font-weight: 800;">SATUAN</th>
                    <th style="width: 140px; text-align: right; padding: 0.65rem 0.75rem; font-weight: 800;">HARGA (RP)</th>
                    <th style="width: 160px; text-align: right; padding: 0.65rem 0.75rem; font-weight: 800;">JUMLAH (RP)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bkm->details as $idx => $dtl)
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="text-align: center; padding: 0.65rem 0.5rem; color: #64748b;">{{ $idx + 1 }}</td>
                        <td style="padding: 0.65rem 0.75rem; font-weight: 600; color: #0f172a;">{{ $dtl->uraian }}</td>
                        <td style="text-align: right; padding: 0.65rem 0.5rem; font-family: ui-monospace, monospace;">{{ number_format($dtl->qty, 2, ',', '.') }}</td>
                        <td style="text-align: center; padding: 0.65rem 0.5rem; color: #64748b;">{{ $dtl->satuan ?: '-' }}</td>
                        <td style="text-align: right; padding: 0.65rem 0.75rem; font-family: ui-monospace, monospace;">{{ number_format($dtl->harga_satuan, 0, ',', '.') }}</td>
                        <td style="text-align: right; padding: 0.65rem 0.75rem; font-weight: 700; color: #0f172a; font-family: ui-monospace, monospace;">{{ number_format($dtl->nominal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="border-top: 1.5px solid #0f172a; border-bottom: 2px solid #0f172a; background: #f8fafc;">
                    <td colspan="5" style="padding: 0.75rem 0.75rem; text-align: right; font-weight: 800; font-size: 0.9rem; text-transform: uppercase;">
                        TOTAL DITERIMA:
                    </td>
                    <td style="padding: 0.75rem 0.75rem; text-align: right; font-weight: 900; font-size: 1.1rem; color: #059669; font-family: ui-monospace, monospace;">
                        Rp {{ number_format($bkm->total_terima, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>

        {{-- BOX TERBILANG & CATATAN --}}
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.875rem 1rem; margin-bottom: 2rem; font-size: 0.825rem;">
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
                <div style="color: #64748b; margin-bottom: 3.5rem;">Diterima Oleh (Kasir),</div>
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

{{-- MODALS PARTIALS --}}
@include('keuangan.kas_masuk.partials.modal-verifikasi-bkm')
@include('keuangan.kas_masuk.partials.modal-void-bkm')

@endsection

@push('scripts')
    <script src="{{ asset('js/keuangan/kas_masuk/kas-masuk-index.js') }}?v={{ file_exists(public_path('js/keuangan/kas_masuk/kas-masuk-index.js')) ? filemtime(public_path('js/keuangan/kas_masuk/kas-masuk-index.js')) : time() }}"></script>
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
