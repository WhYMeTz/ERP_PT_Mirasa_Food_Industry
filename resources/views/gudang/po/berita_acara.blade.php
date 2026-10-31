@extends(request('popup') || request('iframe') ? 'layouts.blank' : 'layouts.app')

@section('title', 'Berita Acara Pembatalan Sisa Pesanan PO #' . $po->po_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/po/po-berita-acara.css') }}?v={{ file_exists(public_path('css/gudang/po/po-berita-acara.css')) ? filemtime(public_path('css/gudang/po/po-berita-acara.css')) : time() }}">
@endpush

@section('content')
@php
    \Carbon\Carbon::setLocale('id');
    $tglClose = $po->closed_at ? \Carbon\Carbon::parse($po->closed_at) : ($po->updated_at ? \Carbon\Carbon::parse($po->updated_at) : now());
    $hari = $tglClose->translatedFormat('l');
    $tglLengkap = $tglClose->translatedFormat('d F Y');

    $noUrut = str_pad($po->po_id, 3, '0', STR_PAD_LEFT);
    $bulanStr = $tglClose->format('m');
    $tahunStr = $tglClose->format('Y');
    $nomorSurat = "BA-PO/{$noUrut}/MFI/{$bulanStr}/{$tahunStr}";

    $totalPesan = (float) $po->details->sum('pesan_qty');
    $totalTerima = (float) $po->details->sum('terima_qty');
    $totalBatal = (float) $po->total_sisa_qty;
    $pctPemenuhan = $totalPesan > 0 ? round(($totalTerima / $totalPesan) * 100, 1) : 0;
@endphp

<div class="ba-container">
    {{-- TOOLBAR ATAS (HANYA DITAMPILKAN DI LAYAR) --}}
    <div class="ba-toolbar no-print">
        <div class="ba-toolbar-left">
            <a href="{{ route('gudang.po.show', $po->po_id) }}" class="btn btn-secondary btn-sm" style="border-radius: 6px; font-weight: 600;">
                &larr; Kembali ke Dokumen PO
            </a>
            <span style="color: #cbd5e1;">|</span>
            <span class="ba-toolbar-title">Berita Acara Pembatalan Sisa PO #{{ $po->po_no }}</span>
        </div>
        <div class="ba-toolbar-right">
            <button type="button" onclick="window.print()" class="btn btn-danger btn-sm" style="border-radius: 6px; font-weight: 700; background: #dc2626; border-color: #dc2626; display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Berita Acara (A4)</span>
            </button>
        </div>
    </div>

    {{-- LEMBAR DOKUMEN BERITA ACARA RESMI --}}
    <div class="ba-sheet">
        {{-- KOP TABEL STANDAR HACCP MIRASA --}}
        <table class="kop-table">
            <tr>
                <td class="kop-logo-cell" rowspan="2">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Cap Payung" class="kop-logo-img">
                    <div class="kop-slogan">ENAK &bull; GURIH &bull; LEZAT</div>
                </td>
                <td class="kop-title-cell" style="border-bottom: 1px solid #000000;">
                    <div class="kop-company-name">PT. MIRASA FOOD INDUSTRY</div>
                </td>
                <td style="width: 235px; padding: 0;" rowspan="2">
                    <table class="kop-meta-table">
                        <tr>
                            <td style="width: 95px; font-weight: 600; border-right: 1px solid #000000 !important;">Nomor dokumen</td>
                            <td style="width: 5px; text-align: center;">:</td>
                            <td style="font-size: 7.5pt;">MFI/HACCP-04/FRM-03/050/VIII/2021</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; border-right: 1px solid #000000 !important;">Terbitan/Tgl</td>
                            <td style="text-align: center;">:</td>
                            <td>19-08-2021</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; border-right: 1px solid #000000 !important;">Revisi</td>
                            <td style="text-align: center;">:</td>
                            <td>00</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; border-right: 1px solid #000000 !important;">Halaman</td>
                            <td style="text-align: center;">:</td>
                            <td>1</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="kop-title-cell">
                    <div class="kop-form-name">Form Berita Acara Pembatalan<br>Sisa Pesanan (Purchase Order)</div>
                </td>
            </tr>
        </table>

        {{-- JUDUL BERITA ACARA --}}
        <div class="ba-header-title">
            <div class="ba-main-title">BERITA ACARA PEMBATALAN SISA PESANAN</div>
            <div class="ba-doc-no">Nomor: {{ $nomorSurat }}</div>
        </div>

        {{-- PARAGRAF PEMBUKA --}}
        <p class="ba-paragraph">
            Pada hari ini <strong>{{ $hari }}</strong>, tanggal <strong>{{ $tglLengkap }}</strong>, bertempat di kantor <strong>PT. Mirasa Food Industry</strong> Magelang, telah disepakati penyelesaian transaksi dan pembatalan sisa kuota pesanan Purchase Order (PO) dengan identitas sebagai berikut:
        </p>

        {{-- IDENTITAS DOKUMEN PO --}}
        <table class="ba-info-table">
            <tr>
                <td style="width: 175px;">Nomor Purchase Order (PO)</td>
                <td style="width: 15px; text-align: center;">:</td>
                <td><strong style="font-family: monospace;">{{ $po->po_no }}</strong></td>
            </tr>
            <tr>
                <td>Tanggal Penerbitan PO</td>
                <td style="text-align: center;">:</td>
                <td>{{ \Carbon\Carbon::parse($po->po_tgl)->locale('id')->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td>Nama Supplier / Pemasok</td>
                <td style="text-align: center;">:</td>
                <td><strong>{{ strtoupper($po->supplier?->supplier_nm ?? '-') }}</strong></td>
            </tr>
            <tr>
                <td>Alamat / Kontak Rekanan</td>
                <td style="text-align: center;">:</td>
                <td>{{ $po->supplier?->alamat_txt ?? '-' }} {{ $po->supplier?->telepon_no ? '(Telp: ' . $po->supplier->telepon_no . ')' : '' }}</td>
            </tr>
            <tr>
                <td>Gudang Tujuan Penerimaan</td>
                <td style="text-align: center;">:</td>
                <td>{{ $po->gudang?->gudang_nm ?? 'Gudang Utama' }}</td>
            </tr>
        </table>

        <p class="ba-paragraph">
            Menerangkan bahwa dari total pesanan barang yang telah disepakati sebelumnya, telah dilakukan penerimaan fisik sebagian di gudang pabrik. Adapun rincian pemenuhan dan sisa kuota yang disepakati untuk <strong>dibatalkan</strong> adalah sebagai berikut:
        </p>

        {{-- TABEL PERBANDINGAN REALISASI VS PEMBATALAN SISA --}}
        <table class="ba-items-table">
            <thead>
                <tr>
                    <th style="width: 35px;">NO</th>
                    <th style="text-align: left; padding-left: 8px;">NAMA KOMODITAS / BAHAN</th>
                    <th style="width: 65px;">SATUAN</th>
                    <th style="width: 110px;">PESANAN AWAL</th>
                    <th style="width: 110px;">REALISASI MASUK</th>
                    <th style="width: 120px; color: #b91c1c;">SISA DIBATALKAN</th>
                    <th style="width: 85px;">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($po->details as $idx => $d)
                    @php
                        $pQty = (float) $d->pesan_qty;
                        $tQty = (float) $d->terima_qty;
                        $sQty = max(0, $pQty - $tQty);
                        $sat = $d->barang?->satuanDasar?->satuan_cd ?? 'KG';
                    @endphp
                    <tr>
                        <td style="text-align: center; font-weight: 700;">{{ $idx + 1 }}.</td>
                        <td style="padding-left: 8px;">
                            <strong>{{ $d->barang?->barang_nm ?? '-' }}</strong>
                        </td>
                        <td style="text-align: center;">{{ $sat }}</td>
                        <td style="text-align: right; padding-right: 8px;">{{ number_format($pQty, 2, ',', '.') }}</td>
                        <td style="text-align: right; padding-right: 8px; font-weight: 700; color: #047857;">{{ number_format($tQty, 2, ',', '.') }}</td>
                        <td style="text-align: right; padding-right: 8px; font-weight: 800; color: #dc2626;">{{ number_format($sQty, 2, ',', '.') }}</td>
                        <td style="text-align: center; font-size: 8.5pt;">
                            @if ($sQty > 0)
                                <span style="font-weight: 700; color: #b91c1c;">Batal Sisa</span>
                            @else
                                <span style="color: #047857;">Lengkap</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: 800; border-top: 2px solid #000000;">
                    <td colspan="3" style="text-align: right; padding: 6px 8px;">TOTAL KUANTITAS:</td>
                    <td style="text-align: right; padding: 6px 8px;">{{ number_format($totalPesan, 2, ',', '.') }}</td>
                    <td style="text-align: right; padding: 6px 8px; color: #047857;">{{ number_format($totalTerima, 2, ',', '.') }}</td>
                    <td style="text-align: right; padding: 6px 8px; color: #dc2626;">{{ number_format($totalBatal, 2, ',', '.') }}</td>
                    <td style="text-align: center; font-size: 8.5pt;">{{ $pctPemenuhan }}%</td>
                </tr>
            </tfoot>
        </table>

        {{-- BOX ALASAN PEMBATALAN SISA --}}
        <div class="ba-reason-box">
            <strong>Alasan Pembatalan Sisa Kuota Pesanan:</strong><br>
            <em>&ldquo;{{ $po->closed_reason ?: 'Supplier mengonfirmasi bahwa sisa komoditas/bahan baku tidak dapat dipenuhi lagi karena kendala ketersediaan stok/musim panen, dan kedua belah pihak sepakat untuk mengakhiri pesanan di angka realisasi penerimaan.' }}&rdquo;</em>
        </div>

        <p class="ba-paragraph">
            Dengan diterbitkannya Berita Acara ini, maka seluruh barang yang <strong>sudah diterima sah tercatat di inventori gudang</strong>, sedangkan sisa kuota yang belum dikirim resmi <strong>dibatalkan dan tidak dapat ditagihkan</strong>. Dokumen Purchase Order nomor <strong>{{ $po->po_no }}</strong> dinyatakan <strong>SELESAI (CLOSED)</strong>.
        </p>

        <p style="margin-bottom: 1.5rem;">
            Demikian Berita Acara ini dibuat dengan sebenarnya dalam rangkap yang cukup untuk dipergunakan sebagaimana mestinya oleh pihak Gudang, Purchasing, Keuangan, dan Rekanan Supplier.
        </p>

        {{-- TANGGAL SURAT --}}
        <div style="text-align: right; margin-bottom: 1.5rem; margin-right: 1.5rem;">
            Magelang, {{ $tglLengkap }}
        </div>

        {{-- KOLOM TANDA TANGAN (3 PIHAK RESMI) --}}
        <div class="ba-signature-grid">
            <div class="ba-sign-box">
                <div class="ba-sign-role">Pihak Pemasok / Supplier</div>
                <div style="height: 55px;"></div>
                <div class="ba-sign-name">{{ $po->supplier?->kontak_person ?? ($po->supplier?->supplier_nm ?? 'Pihak Supplier') }}</div>
            </div>

            <div class="ba-sign-box">
                <div class="ba-sign-role">Bagian Gudang / Purchasing</div>
                <div style="height: 55px;"></div>
                <div class="ba-sign-name">{{ $po->closed_by ?? (auth()->user()?->name ?? 'Staff Gudang/Purchasing') }}</div>
            </div>

            <div class="ba-sign-box">
                <div class="ba-sign-role">Mengetahui Direktur</div>
                <div style="height: 55px;"></div>
                <div class="ba-sign-name">Habieb Soleh Y.</div>
            </div>
        </div>
    </div>
</div>
@endsection

