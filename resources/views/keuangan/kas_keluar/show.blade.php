@extends('layouts.app')

@section('title', 'Kas Keluar (Biaya Harian) - ' . $bkk->bkk_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/kas_keluar/kas-keluar-show.css') }}">
@endpush

@section('content')
<div class="bkk-detail-container">
    {{-- TOP ACTION BAR (NO PRINT) --}}
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <a href="{{ route('keuangan.kas_keluar.index') }}"
               aria-label="Kembali ke daftar kas keluar biaya harian"
               style="color: #475569; text-decoration: none; display: inline-flex; align-items: center;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h1 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">
                Voucher Kas Keluar: {{ $bkk->bkk_no }}
            </h1>
            @if ($bkk->status_cd === 'POSTED')
                <span style="background: #ecfdf5; color: #166534; font-size: 0.725rem; font-weight: 800; padding: 3px 9px; border-radius: 6px; border: 1px solid #a7f3d0;">
                    Sudah Terbit Jurnal (POSTED)
                </span>
            @elseif ($bkk->status_cd === 'DRAFT')
                <span style="background: #fffbeb; color: #92400e; font-size: 0.725rem; font-weight: 800; padding: 3px 9px; border-radius: 6px; border: 1px solid #fde68a;">
                    Menunggu Verifikasi Jurnal
                </span>
            @else
                <span style="background: #fef2f2; color: #b91c1c; font-size: 0.725rem; font-weight: 800; padding: 3px 9px; border-radius: 6px; border: 1px solid #fecaca;">
                    DIBATALKAN (VOID)
                </span>
            @endif
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('keuangan.kas_keluar.create') }}" class="btn btn-secondary" style="font-weight: 600; font-size: 0.8125rem;">
                + Catat Kas Keluar
            </a>
            <button type="button" onclick="window.print()" class="btn btn-primary" style="font-weight: 700; font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 6px; background: #0369a1; border-color: #0369a1; color: #ffffff;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Slip BKK</span>
            </button>
            @if (in_array($bkk->status_cd, ['DRAFT', 'POSTED'], true) && Auth::user()->canVoidBayarHutang())
                <button type="button" onclick="openVoidModal()" class="btn btn-danger" style="font-weight: 700; font-size: 0.8125rem; background: #dc2626; border-color: #dc2626; color: #ffffff;">
                    Batalkan (Void)
                </button>
            @endif
        </div>
    </div>

    {{-- INFO BANNER JIKA MASIH DRAFT (MENUNGGU VERIFIKASI AKUNTANSI) --}}
    @if ($bkk->status_cd === 'DRAFT')
        <div class="no-print" style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                <svg width="22" height="22" fill="none" stroke="#b45309" viewBox="0 0 24 24" aria-hidden="true" style="flex-shrink: 0; margin-top: 2px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <strong style="color: #92400e; font-size: 0.875rem;">Menunggu Verifikasi Akuntansi &amp; Penerbitan Jurnal Umum</strong>
                    <div style="font-size: 0.8125rem; color: #78350f; margin-top: 0.2rem;">
                        Saldo Kas/Bank sudah dipotong saat pencatatan Kasir. Bagian Akuntansi perlu memilih Pos Akun Beban (COA) di menu <strong>Verifikasi &amp; Jurnal Umum</strong> untuk menerbitkan jurnal resmi.
                    </div>
                </div>
            </div>
            @if (Auth::user()->canCreateJurnal())
                <a href="{{ route('keuangan.verifikasi_jurnal.index') }}"
                   style="background: #0f172a; color: #ffffff; text-decoration: none; font-size: 0.8rem; font-weight: 700; padding: 0.5rem 0.95rem; border-radius: 6px; white-space: nowrap;">
                    Verifikasi di Jurnal Akuntansi
                </a>
            @endif
        </div>
    @endif

    {{-- WARNING BANNER JIKA VOID --}}
    @if ($bkk->status_cd === 'VOID')
        <div class="no-print" style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.25rem;">
            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <svg width="24" height="24" fill="none" stroke="#dc2626" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                <div>
                    <strong style="color: #991b1b; font-size: 0.875rem;">Dokumen ini telah dibatalkan (VOID)</strong>
                    <div style="font-size: 0.8125rem; color: #b91c1c; margin-top: 0.2rem;">
                        Oleh: <strong>{{ $bkk->void_by ?? 'User' }}</strong> pada {{ $bkk->void_dt ? $bkk->void_dt->format('d/m/Y H:i') : '-' }} &bull; Alasan: <em>"{{ $bkk->alasan_void ?? '-' }}"</em>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- LEMBAR DOKUMEN CETAK BKK (VOUCHER SHEET) --}}
    <div class="voucher-sheet">
        {{-- KOP PERUSAHAAN --}}
        <div class="voucher-header">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <img src="{{ asset('images/logo.png') }}" alt="Logo Mirasa" style="height: 52px; object-fit: contain;" onerror="this.style.display='none'">
                <div>
                    <div style="font-size: 1.15rem; font-weight: 900; color: #0f172a; letter-spacing: -0.01em;">
                        PT MIRASA FOOD INDUSTRY
                    </div>
                    <div style="font-size: 0.75rem; color: #475569; margin-top: 0.15rem; line-height: 1.35;">
                        Produsen Makanan Ringan Olahan Tradisional Berkualitas<br>
                        Dusun Karangpandan, Kec. Rejoso, Kab. Pasuruan, Jawa Timur
                    </div>
                </div>
            </div>

            <div class="voucher-title">
                <h2>BUKTI KAS KELUAR</h2>
                <div style="font-family: ui-monospace, monospace; font-size: 1rem; font-weight: 800; color: #0369a1;">
                    {{ $bkk->bkk_no }}
                </div>
                <div style="font-size: 0.75rem; color: #475569; margin-top: 0.2rem;">
                    Tanggal: <strong>{{ $bkk->tgl_keluar->format('d F Y') }}</strong>
                </div>
            </div>
        </div>

        {{-- GRID METADATA TRANSAKSI --}}
        <div class="voucher-meta-grid">
            <div class="voucher-meta-item">
                <span class="voucher-meta-label">Dibayarkan Kepada (Penerima):</span>
                <span class="voucher-meta-value" style="font-size: 1rem; color: #0369a1;">
                    {{ $bkk->penerima_nama }}
                    @if ($bkk->tipe_penerima === 'KARYAWAN')
                        <span style="font-size: 0.725rem; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 2px 6px; border-radius: 4px; margin-left: 4px;">Karyawan</span>
                    @endif
                </span>
            </div>

            <div class="voucher-meta-item">
                <span class="voucher-meta-label">Pemohon Dana (PIC):</span>
                <span class="voucher-meta-value">
                    @if ($bkk->pemohon)
                        <strong style="color: #0f172a;">{{ $bkk->pemohon->karyawan_nm }}</strong>
                        <span style="font-size: 0.75rem; color: #475569; font-weight: 600;">({{ $bkk->departemen_pemohon ?: ($bkk->pemohon->departemen_cd ?: 'Staff') }})</span>
                    @elseif ($bkk->departemen_pemohon)
                        <span style="color: #0f172a; font-weight: 700;">{{ $bkk->departemen_pemohon }}</span>
                    @else
                        <span style="color: #94a3b8;">-</span>
                    @endif
                </span>
            </div>

            <div class="voucher-meta-item">
                <span class="voucher-meta-label">Dibayar Dari (Kas / Bank):</span>
                <span class="voucher-meta-value">
                    {{ $bkk->kasBank->nama_akun ?? '-' }} ({{ $bkk->kasBank->tipe_akun ?? 'KAS' }})
                </span>
            </div>

            <div class="voucher-meta-item">
                <span class="voucher-meta-label">Pos Akun Beban (COA):</span>
                <span class="voucher-meta-value">
                    @if ($bkk->coa)
                        <span style="font-family: monospace; background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px;">{{ $bkk->coa->kode_akun }}</span>
                        {{ $bkk->coa->nama_akun }}
                    @elseif ($bkk->status_cd === 'DRAFT')
                        <span style="color: #92400e; font-weight: 700;">Menunggu Verifikasi Akuntansi</span>
                    @else
                        -
                    @endif
                </span>
            </div>

            <div class="voucher-meta-item">
                <span class="voucher-meta-label">Metode Pembayaran:</span>
                <span class="voucher-meta-value">
                    {{ $bkk->metode_bayar }}
                    @if ($bkk->no_referensi_bank)
                        <span style="color: #475569; font-weight: 500;">(Ref: {{ $bkk->no_referensi_bank }})</span>
                    @endif
                </span>
            </div>

            @if ($bkk->catatan)
                <div class="voucher-meta-item" style="grid-column: span 2;">
                    <span class="voucher-meta-label">Catatan / Keterangan:</span>
                    <span class="voucher-meta-value" style="font-weight: 500; color: #334155;">
                        {{ $bkk->catatan }}
                    </span>
                </div>
            @endif

            @if ($bkk->jurnal)
                <div class="voucher-meta-item no-print" style="grid-column: span 2; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 0.6rem 0.75rem;">
                    <span class="voucher-meta-label" style="color: #166534;">Integrasi Buku Besar / Jurnal Umum:</span>
                    <span class="voucher-meta-value" style="font-size: 0.8125rem; color: #166534; font-weight: 600;">
                        Tercatat pada Jurnal <strong>{{ $bkk->jurnal->jurnal_no }}</strong> &bull; Total Debet/Kredit: Rp {{ number_format($bkk->jurnal->total_debet, 0, ',', '.') }}
                    </span>
                </div>
            @endif
        </div>

        {{-- TABEL RINCIAN PENGELUARAN --}}
        <table class="voucher-table">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">No</th>
                    <th>Uraian Keperluan / Pembayaran</th>
                    <th style="width: 80px; text-align: center;">Qty</th>
                    <th style="width: 80px;">Satuan</th>
                    <th style="width: 140px; text-align: right;">Harga Satuan</th>
                    <th style="width: 160px; text-align: right;">Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bkk->details as $index => $item)
                    <tr>
                        <td style="text-align: center; color: #475569;">{{ $index + 1 }}</td>
                        <td>
                            <strong style="color: #0f172a;">{{ $item->uraian }}</strong>
                            @if ($item->catatan)
                                <div style="font-size: 0.75rem; color: #475569;">{{ $item->catatan }}</div>
                            @endif
                        </td>
                        <td style="text-align: center;">{{ $item->qty > 0 ? (float) $item->qty : 1 }}</td>
                        <td style="color: #475569;">{{ $item->satuan ?? '-' }}</td>
                        <td class="cell-num">
                            {{ $item->harga_satuan > 0 ? 'Rp ' . number_format($item->harga_satuan, 0, ',', '.') : '-' }}
                        </td>
                        <td class="cell-num" style="font-weight: 800; color: #0f172a;">
                            Rp {{ number_format($item->nominal, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #475569; font-style: italic; padding: 1.5rem;">
                            Tidak ada rincian transaksi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-size: 0.95rem;">
                    <td colspan="5" style="text-align: right; font-weight: 800; color: #0f172a; padding: 0.85rem 0.75rem;">
                        TOTAL DIBAYARKAN:
                    </td>
                    <td class="cell-num" style="font-size: 1.1rem; font-weight: 900; color: #0369a1; padding: 0.85rem 0.75rem;">
                        Rp {{ number_format($bkk->total_keluar, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>

        {{-- TERBILANG --}}
        <div style="background: #f8fafc; border-radius: 6px; padding: 0.75rem 1rem; border: 1px dashed #cbd5e1; margin-bottom: 2rem;">
            <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #475569;">Terbilang:</span>
            <div style="font-size: 0.875rem; font-weight: 700; color: #0f172a; font-style: italic; margin-top: 0.15rem;">
                # {{ $bkk->terbilang }} Rupiah #
            </div>
        </div>

        {{-- KOLOM TANDA TANGAN --}}
        <div class="voucher-signatures">
            <div class="signature-box">
                <span class="signature-role">Dibuat Oleh (Kasir)</span>
                <span class="signature-name">{{ $bkk->created_by ?? 'Kasir Keuangan' }}</span>
            </div>
            @if ($bkk->pemohon || $bkk->departemen_pemohon)
                <div class="signature-box">
                    <span class="signature-role">Pemohon Dana (PIC)</span>
                    <span class="signature-name">{{ $bkk->pemohon?->karyawan_nm ?? $bkk->departemen_pemohon }}</span>
                </div>
            @endif
            <div class="signature-box">
                <span class="signature-role">Yang Menerima</span>
                <span class="signature-name">{{ $bkk->penerima_nama }}</span>
            </div>
            <div class="signature-box">
                <span class="signature-role">Diperiksa Oleh</span>
                <span class="signature-name">Staff Akuntansi</span>
            </div>
            <div class="signature-box">
                <span class="signature-role">Disetujui Oleh</span>
                <span class="signature-name">Manajer Keuangan</span>
            </div>
        </div>
    </div>
</div>

{{-- MODAL VOID BKK OPERASIONAL (UNTUK DRAFT MAUPUN POSTED) --}}
@if (in_array($bkk->status_cd, ['DRAFT', 'POSTED'], true) && Auth::user()->canVoidBayarHutang())
    <div id="modalVoidBkk" role="dialog" aria-modal="true" aria-labelledby="modalVoidBkkTitle" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
        <div style="background: #ffffff; border-radius: 12px; max-width: 480px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden;">
            <div style="background: #fef2f2; padding: 1.25rem 1.5rem; border-bottom: 1px solid #fee2e2; display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #dc2626; flex-shrink: 0;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 id="modalVoidBkkTitle" style="font-size: 1.05rem; font-weight: 800; color: #991b1b; margin: 0;">Konfirmasi Pembatalan Voucher</h3>
                    <div style="font-size: 0.75rem; color: #b91c1c; margin-top: 0.15rem;">BKK: {{ $bkk->bkk_no }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('keuangan.kas_keluar.void', $bkk->bkk_id) }}" style="padding: 1.5rem;">
                @csrf
                <p style="font-size: 0.8125rem; color: #334155; margin: 0 0 1rem 0; line-height: 1.45;">
                    Perhatian: Membatalkan voucher ini akan <strong>mengembalikan saldo sebesar Rp {{ number_format($bkk->total_keluar, 0, ',', '.') }}</strong> ke akun <strong>{{ $bkk->kasBank->nama_akun ?? 'Kas' }}</strong>
                    @if ($bkk->jurnal_id)
                        dan membatalkan pencatatan jurnal terkait.
                    @else
                        dan mengeluarkannya dari antrean verifikasi Jurnal Umum.
                    @endif
                </p>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="alasan_void" style="font-size: 0.8125rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                        Alasan Pembatalan <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea name="alasan_void" id="alasan_void" rows="3" minlength="5" maxlength="255" class="form-control" placeholder="Tuliskan alasan pembatalan transaksi..." required style="width: 100%; font-size: 0.8125rem; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.5rem;"></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <button type="button" onclick="closeVoidModal()" class="btn btn-secondary" style="font-weight: 600; font-size: 0.8125rem;">
                        Tutup
                    </button>
                    <button type="submit" class="btn btn-danger" style="font-weight: 700; font-size: 0.8125rem; background: #dc2626; border-color: #dc2626; color: #ffffff;">
                        Ya, Batalkan Voucher
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    function openVoidModal() {
        const modal = document.getElementById('modalVoidBkk');
        if (modal) {
            modal.style.display = 'flex';
            const input = document.getElementById('alasan_void');
            if (input) input.focus();
        }
    }

    function closeVoidModal() {
        const modal = document.getElementById('modalVoidBkk');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeVoidModal();
        }
    });
</script>
@endpush
