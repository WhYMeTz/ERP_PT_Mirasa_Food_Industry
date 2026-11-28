@extends('layouts.app')

@section('title', 'Detail Dokumen Hasil Produksi - ' . $produksi->produksi_no)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-show.css') }}?v={{ filemtime(public_path('css/produksi/produksi-show.css')) }}">
@endpush

@section('content')
@php
    $rKulit = $produksi->rendemen_kulit_persen;
    $rKupas = $produksi->rendemen_kupas_persen;
    $rKulitLolos = $rKulit >= 33.0;
    $rKulitWarning = $rKulit >= 30.0;
    $rKulitColor = $rKulitLolos ? '#059669' : ($rKulitWarning ? '#d97706' : '#dc2626');
    $rKulitBg = $rKulitLolos ? '#ecfdf5' : ($rKulitWarning ? '#fef3c7' : '#fee2e2');

    $rKupasLolos = $rKupas >= 44.0;
    $rKupasWarning = $rKupas >= 40.0;
    $rKupasColor = $rKupasLolos ? '#0284c7' : ($rKupasWarning ? '#d97706' : '#dc2626');

    $isIfm = str_contains(strtoupper($produksi->lini_produksi), 'IFM') || str_contains(strtoupper($produksi->lini_produksi), 'IFL');
    $expDate = !empty($produksi->exp_date)
        ? Carbon\Carbon::parse($produksi->exp_date)->format('d/m/Y')
        : ($isIfm
            ? Carbon\Carbon::parse($produksi->produksi_tgl)->addMonths(6)->format('d/m/Y')
            : Carbon\Carbon::parse($produksi->produksi_tgl)->addYear()->subDay()->format('d/m/Y'));

    $isBmbDoc = in_array(strtoupper($produksi->lokasi_pabrik ?? 'MFI'), ['BMB', 'CV-BMB', 'BAHTERA', 'TIMUR']);
    $creatorName = is_numeric($produksi->created_by) ? (\App\Models\User::find($produksi->created_by)?->name ?? 'User #' . $produksi->created_by) : ($produksi->created_by ?: 'Admin Gudang');
    $tkTotal = (int) ($produksi->tk_jumlah_org ?? ($produksi->tk_langsung_org + $produksi->tk_tidak_langsung_org + $produksi->tk_training_org));
    $totalWaste = $produksi->waste_total_qty ?: ($produksi->waste_lengket_qty + $produksi->total_berko_qty);

    $pakaiKulit = (float) ($produksi->kulitan_qty ?? 0) > 0;
    $pakaiKupas = (float) ($produksi->kupasan_qty ?? 0) > 0;
    if (!$pakaiKulit && !$pakaiKupas) {
        if ($produksi->singkong_tipe === 'KUPAS') {
            $pakaiKupas = true;
        } else {
            $pakaiKulit = true;
        }
    }
    $pakaiKeduanya = $pakaiKulit && $pakaiKupas;
@endphp

<div class="show-container">

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- BAGIAN DOKUMEN CETAK RESMI (STANDAR TEMPLATE SISTEM ERP MIRASA)   --}}
    {{-- 100% Mengikuti Standar Format BPPB / LPB (pdf_dokumen.blade.php)  --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="print-only">
        {{-- KOP RESMI HACCP / ISO PT MIRASA FOOD INDUSTRY --}}
        <table class="kop-table" style="width: 100%; border-collapse: collapse; margin-bottom: 4px;">
            <tr>
                <td class="kop-logo" style="width: 75px; vertical-align: middle; text-align: center; padding-right: 12px;">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Cap Payung" style="width: 48px; height: auto; display: block; margin: 0 auto 3px auto;">
                    <div class="kop-slogan" style="font-family: Arial, Helvetica, sans-serif; font-size: 5pt; font-weight: 800; color: #dc2626; letter-spacing: 0.3px; text-align: center; white-space: nowrap;">ENAK &bull; GURIH &bull; LEZAT</div>
                </td>
                <td class="kop-text" style="vertical-align: middle; text-align: left; padding: 0;">
                    <div class="kop-company" style="font-size: 13pt; font-weight: 800; color: #000000; letter-spacing: 0.5px; font-family: 'Arial', sans-serif; line-height: 1.25; margin-bottom: 2px;">PT. MIRASA FOOD INDUSTRY</div>
                    <div class="kop-sub-industry" style="font-size: 7.5pt; font-weight: 700; color: #0f172a; line-height: 1.25; margin-bottom: 1px;">Pabrik Pengolahan Aneka Keripik Singkong &amp; Makanan Ringan</div>
                    <div class="kop-address" style="font-size: 7pt; color: #334155; line-height: 1.25; margin-bottom: 1px;">Jl. Menur No. 2, Ambartawang, Mungkid, Magelang, Jawa Tengah 56512</div>
                    <div class="kop-contact" style="font-size: 6.8pt; color: #475569; line-height: 1.25;">Telp: (0293) 3288554, 3288556 &bull; Email: mirasa_food@yahoo.co.id</div>
                </td>
                <td class="kop-doc-control" style="width: 250px; text-align: right; vertical-align: middle;">
                    <table class="doc-control-box" style="width: 100%; border: 1.5px solid #000000; border-collapse: collapse; font-size: 7pt; background: #ffffff;">
                        <tr>
                            <td class="dcb-label" style="border: 1px solid #000000; padding: 2.5px 5px; width: 70px; font-weight: 700; color: #000000; background: #f8fafc; font-size: 6.8pt; white-space: nowrap;">No. Dokumen</td>
                            <td class="dcb-sep" style="border: 1px solid #000000; padding: 2.5px 0; width: 8px; text-align: center; font-weight: bold; background: #f8fafc; font-size: 6.8pt;">:</td>
                            <td class="dcb-val" style="border: 1px solid #000000; padding: 2.5px 6px; color: #000000; font-size: 6.8pt; font-family: monospace; font-weight: bold; white-space: nowrap;">{{ $produksi->produksi_no }}</td>
                        </tr>
                        <tr>
                            <td class="dcb-label" style="border: 1px solid #000000; padding: 2.5px 5px; font-weight: 700; color: #000000; background: #f8fafc; font-size: 6.8pt; white-space: nowrap;">Tgl. Produksi</td>
                            <td class="dcb-sep" style="border: 1px solid #000000; padding: 2.5px 0; text-align: center; font-weight: bold; background: #f8fafc; font-size: 6.8pt;">:</td>
                            <td class="dcb-val" style="border: 1px solid #000000; padding: 2.5px 6px; color: #000000; font-size: 6.8pt; white-space: nowrap;">{{ \Carbon\Carbon::parse($produksi->produksi_tgl)->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="dcb-label" style="border: 1px solid #000000; padding: 2.5px 5px; font-weight: 700; color: #000000; background: #f8fafc; font-size: 6.8pt; white-space: nowrap;">Lini Produksi</td>
                            <td class="dcb-sep" style="border: 1px solid #000000; padding: 2.5px 0; text-align: center; font-weight: bold; background: #f8fafc; font-size: 6.8pt;">:</td>
                            <td class="dcb-val" style="border: 1px solid #000000; padding: 2.5px 6px; color: #000000; font-size: 6.8pt; white-space: nowrap;">{{ $produksi->lini_produksi }}</td>
                        </tr>
                        <tr>
                            <td class="dcb-label" style="border: 1px solid #000000; padding: 2.5px 5px; font-weight: 700; color: #000000; background: #f8fafc; font-size: 6.8pt; white-space: nowrap;">Shift Kerja</td>
                            <td class="dcb-sep" style="border: 1px solid #000000; padding: 2.5px 0; text-align: center; font-weight: bold; background: #f8fafc; font-size: 6.8pt;">:</td>
                            <td class="dcb-val" style="border: 1px solid #000000; padding: 2.5px 6px; color: #000000; font-size: 6.8pt; white-space: nowrap;">Shift {{ $produksi->shift_cd ?: '-' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="kop-divider-double"></div>

        <div class="doc-header-banner">
            <div class="doc-main-title">BUKTI HASIL PRODUKSI</div>
        </div>

        {{-- INFORMASI DOKUMEN PARAMETER (RINGKAS, TEPAT GUNA, TANPA DUPLIKASI KOP) --}}
        <table class="meta-table">
            <tr>
                <td class="meta-label">Lokasi &amp; Gudang Simpan</td>
                <td class="meta-val">
                    <strong>{{ $isBmbDoc ? 'CV Bahtera Mandiri Bersama' : 'PT Mirasa Food Industry' }}</strong>
                    &bull; {{ $produksi->gudang?->gudang_nm ?? 'Gudang Utama Pabrik' }}
                </td>
                <td class="meta-label">Dokumen Bahan (BPPB)</td>
                <td class="meta-val">
                    @if($produksi->pemakaianBahan)
                        <strong style="font-family: monospace;">{{ $produksi->pemakaianBahan->pakai_no }}</strong>
                        @if($produksi->pemakaianBahan->tujuan_pemakaian)
                            <span style="font-size: 7.5pt; color: #64748b;">({{ $produksi->pemakaianBahan->tujuan_pemakaian }})</span>
                        @endif
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <td class="meta-label">Batch &amp; Estimasi Exp</td>
                <td class="meta-val">
                    <strong style="font-family: monospace;">{{ in_array($produksi->batch_wip_no, ['BELUM TERBIT', 'PENDING']) ? 'Buffer Stock IFM' : ($produksi->batch_wip_no ?: '-') }}</strong>
                    <span style="font-size: 7.5pt; color: #475569; margin-left: 4px;">(Exp: {{ $expDate }})</span>
                </td>
                <td class="meta-label">Kemasan Karton Box</td>
                <td class="meta-val">
                    @if ($produksi->no_karton_awal && $produksi->no_karton_akhir)
                        <span style="font-family: monospace;">No. {{ str_pad($produksi->no_karton_awal, 4, '0', STR_PAD_LEFT) }} s/d {{ str_pad($produksi->no_karton_akhir, 4, '0', STR_PAD_LEFT) }}</span>
                        <strong>({{ $produksi->qty_karton }} Box)</strong>
                    @else
                        WIP Curah (Timbangan Fisik)
                    @endif
                </td>
            </tr>
            <tr>
                <td class="meta-label">Bahan Baku Singkong</td>
                <td class="meta-val">
                    <strong>{{ number_format($produksi->singkong_qty, 0, ',', '.') }} kg</strong>
                    @if($pakaiKeduanya)
                        <span style="font-size: 7.5pt; color: #475569;">(Kulit: {{ number_format($produksi->kulitan_qty, 0, ',', '.') }} kg + Kupas: {{ number_format($produksi->kupasan_qty, 0, ',', '.') }} kg)</span>
                    @elseif($pakaiKupas)
                        <span style="font-size: 7.5pt; color: #475569;">(Kupas)</span>
                    @else
                        <span style="font-size: 7.5pt; color: #475569;">(Kulit)</span>
                    @endif
                </td>
                <td class="meta-label">Total Hasil Output WIP</td>
                <td class="meta-val">
                    <strong>{{ number_format($produksi->total_wip_qty, 2, ',', '.') }} kg</strong>
                    @if ($produksi->qty_karton)
                        <span style="font-size: 7.5pt; color: #475569;">({{ number_format($produksi->qty_karton, 0, ',', '.') }} Karton)</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="meta-label">Rendemen Singkong</td>
                <td class="meta-val">
                    @if($pakaiKeduanya)
                        <strong>Kulit: {{ number_format($rKulit, 2, ',', '.') }}%</strong> &bull; 
                        <strong>Kupas: {{ number_format($rKupas, 2, ',', '.') }}%</strong>
                    @elseif($pakaiKupas)
                        <strong>{{ number_format($rKupas, 2, ',', '.') }}%</strong> <span style="font-size: 7.5pt; color: #475569;">(Kupas)</span>
                    @else
                        <strong>{{ number_format($rKulit, 2, ',', '.') }}%</strong> <span style="font-size: 7.5pt; color: #475569;">(Kulit)</span>
                    @endif
                </td>
                <td class="meta-label">Limbah &amp; Waste</td>
                <td class="meta-val">
                    <strong>{{ number_format($totalWaste, 2, ',', '.') }} kg</strong>
                    @if($produksi->waste_lengket_qty > 0)
                        <span style="font-size: 7pt; color: #475569;">(Lengket: {{ number_format($produksi->waste_lengket_qty, 1, ',', '.') }} kg | Berko: {{ number_format($produksi->total_berko_qty, 1, ',', '.') }} kg)</span>
                    @endif
                </td>
            </tr>
            @if(!empty($produksi->catatan))
            <tr>
                <td class="meta-label">Catatan Operasional</td>
                <td class="meta-val" colspan="3">{{ $produksi->catatan }}</td>
            </tr>
            @endif
        </table>

        {{-- TABEL RINCIAN HASIL BARANG JADI (STANDAR DATA-TABLE ERP) --}}
        @php
            $sortedOutputs = $produksi->outputs ? $produksi->outputs->sortBy(function($out) {
                $bNm = strtoupper($out->barang?->barang_nm ?? '');
                $bCd = strtoupper($out->barang?->barang_cd ?? '');
                $kat = strtoupper($out->kategori_output ?? '');
                
                $isLengket = str_contains($bNm, 'LENGKET') || str_contains($bCd, 'LGT') || str_contains($kat, 'LGT') || str_contains($kat, 'LENGKET');
                $isBerko = !$isLengket && (str_contains($bNm, 'BERKO') || str_contains($bCd, 'BRK') || str_contains($kat, 'BERKO') || str_contains($kat, 'BRK') || str_contains($kat, 'KW2'));
                $isAfval = str_contains($bNm, 'AFVAL') || str_contains($bNm, 'REMAHAN') || str_contains($kat, 'AFVAL') || str_contains($bCd, 'AFV') || str_contains($kat, 'RMH');
                $isUtama = !$isLengket && !$isBerko && !$isAfval;

                $tier = 1;
                if ($isBerko) $tier = 2;
                elseif ($isLengket) $tier = 3;
                elseif ($isAfval) $tier = 4;

                return sprintf('%d_%010.2f', $tier, 1000000 - (float)$out->qty_kg);
            })->values() : collect();
        @endphp
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 28px; text-align: center;">No</th>
                    <th style="width: 95px;">Kode Barang</th>
                    <th>Nama Barang WIP / Olahan</th>
                    <th style="width: 110px;">Kategori Output</th>
                    <th style="width: 90px; text-align: center;">Batch Varian</th>
                    <th style="width: 90px; text-align: right;">Qty Kemasan</th>
                    <th style="width: 95px; text-align: right;">Berat (Kg)</th>
                    <th style="width: 60px; text-align: center;">Porsi (%)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sortedOutputs as $idx => $out)
                    @php
                        $totalWip = (float) $produksi->total_wip_qty > 0 ? (float) $produksi->total_wip_qty : 1;
                        $porsiPersen = round(((float)$out->qty_kg / $totalWip) * 100, 1);
                        $satuanTampil = $out->satuan_cd ?: ($out->barang?->satuan?->satuan_nm ?? 'KG');
                    @endphp
                    <tr>
                        <td style="text-align: center; color: #64748b;">{{ $idx + 1 }}</td>
                        <td style="font-family: monospace; font-weight: bold; color: #0f172a;">{{ $out->barang?->barang_cd ?? '-' }}</td>
                        <td><strong>{{ $out->barang?->barang_nm ?? '-' }}</strong></td>
                        <td style="color: #475569;">{{ $out->kategori_output ?: 'Produk Utama' }}</td>
                        <td style="font-family: monospace; text-align: center;">{{ $out->batch_no }}</td>
                        <td style="text-align: right;">
                            @if((float) $out->qty_hasil > 0)
                                {{ number_format((float) $out->qty_hasil, 0, ',', '.') }} {{ $satuanTampil }}
                            @else
                                -
                            @endif
                        </td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($out->qty_kg, 2, ',', '.') }} kg</td>
                        <td style="text-align: center;">{{ number_format($porsiPersen, 1, ',', '.') }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 10px; color: #94a3b8;">
                            Belum ada rincian output timbangan tersimpan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" style="text-align: right; font-weight: bold;">TOTAL HASIL PRODUKSI (WIP):</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($produksi->total_wip_qty, 2, ',', '.') }} kg</td>
                    <td style="text-align: center; font-weight: bold;">100.0%</td>
                </tr>
            </tfoot>
        </table>

        {{-- KOLOM TANDA TANGAN (KEPALA PRODUKSI) --}}
        <table class="ttd-table" style="width: 100%; margin-top: 25px; margin-bottom: 25px; border-collapse: collapse; page-break-inside: avoid;">
            <tr>
                <td style="width: 65%; border: none;"></td>
                <td style="width: 35%; text-align: center; vertical-align: top; padding: 0; border: none;">
                    <div class="ttd-box" style="border: 1px dashed #94a3b8; border-radius: 6px; padding: 8px 12px; background: #ffffff;">
                        <div class="ttd-role">Mengetahui &amp; Menyetujui:</div>
                        <div class="ttd-name-title" style="font-size: 9pt; font-weight: bold; color: #0f172a; margin-top: 2px;">Kepala Produksi</div>
                        <div class="ttd-space" style="height: 50px;"></div>
                        <div class="ttd-line" style="border-bottom: 1px solid #475569; width: 85%; margin: 0 auto;"></div>
                        <div class="ttd-date" style="font-size: 7.5pt; color: #64748b; margin-top: 4px;">( ..................................................... )</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- FOOTER INFO STANDAR --}}
        <div class="footer-note">
            <span>Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WIB | Oleh: {{ Auth::user()->name ?? 'Operator' }}</span>
            <span>Dokumen Resmi PT Mirasa Food Industry - Bukti Hasil Produksi</span>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- BAGIAN TAMPILAN WEB DASHBOARD INTERAKTIF (NO-PRINT)               --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="no-print">

        {{-- BARIS HEADER NAVIGASI & AKSI (NO-PRINT) --}}
        <div class="show-header">
        <div>
            <a href="{{ route('produksi.index') }}" class="btn-back-link">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Hasil Barang Produksi</span>
            </a>
            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">{{ $produksi->produksi_no }}</h1>
                @if ($produksi->shift_cd)
                    <span class="badge-shift-pill {{ $produksi->shift_cd === 'A' ? 'badge-shift-a' : 'badge-shift-b' }}">
                        Shift {{ $produksi->shift_cd }}
                    </span>
                @endif
                <span style="font-size: 0.75rem; font-weight: 700; color: #0369a1; background: #e0f2fe; border: 1px solid #bae6fd; padding: 0.2rem 0.6rem; border-radius: 4px;">
                    {{ $produksi->lini_produksi }}
                </span>
            </div>
        </div>

        <div class="show-actions">
            @if(Auth::user()->canCreateProduksi() && (in_array($produksi->batch_wip_no, ['BELUM TERBIT', 'PENDING']) || empty($produksi->batch_wip_no)))
                <button type="button" onclick="openModalAssignBatch()" class="btn-print-doc" style="background: #f59e0b; color: #fff; text-decoration: none; border-color: #d97706; font-weight: 700;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>⚡ Beri Nomor Batch</span>
                </button>
            @endif
            <button type="button" onclick="window.print()" class="btn-print-doc">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Dokumen</span>
            </button>
        </div>
    </div>

    {{-- BANNER KHUSUS BUFFER STOCK IFM (BATCH BELUM TERBIT) --}}
    @if(in_array($produksi->batch_wip_no, ['BELUM TERBIT', 'PENDING']) || empty($produksi->batch_wip_no))
        <div class="no-print" style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 8px; padding: 0.85rem 1.25rem; color: #92400e; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <span style="font-size: 1.35rem;">📦</span>
                <div>
                    <strong style="display: block; font-size: 0.875rem;">Status Batch: Belum Diterbitkan (Buffer Stock IFM)</strong>
                    <span style="font-size: 0.775rem; color: #b45309;">Produksi ini tersimpan di gudang sebagai persediaan buffer tanpa nomor batch pengiriman. Terbitkan nomor batch resmi saat jadwal keberangkatan truk Indofood tiba.</span>
                </div>
            </div>
            @if(Auth::user()->canCreateProduksi())
                <button type="button" onclick="openModalAssignBatch()" style="background: #d97706; color: #ffffff; border: none; padding: 0.45rem 1rem; border-radius: 6px; font-weight: 700; font-size: 0.825rem; cursor: pointer; display: flex; align-items: center; gap: 0.35rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Beri Nomor Batch Sekarang</span>
                </button>
            @endif
        </div>
    @endif

    {{-- ALERT NOTIFIKASI SUKSES / ERROR --}}
    @if(session('success'))
        <div class="no-print" style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 0.85rem 1.25rem; color: #065f46; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="no-print" style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 0.85rem 1.25rem; color: #991b1b; margin-bottom: 1.25rem; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- BARIS 1: 4 KARTU METRIK OPERASIONAL FISIK (EXECUTIVE STAT GRID)    --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="executive-stat-grid no-print">
        {{-- METRIK 1: INPUT SINGKONG MENTAH --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label">Input Singkong Masuk</span>
                    <div class="executive-stat-value">
                        {{ number_format($produksi->singkong_qty, 0, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">kg</span>
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #e0f2fe; color: #0284c7;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                @if($produksi->singkong_tipe === 'KUPAS' || (float)$produksi->kupasan_qty > 0)
                    Singkong Kupas &bull; Setara Kulitan: <strong style="color: #0369a1;">{{ number_format($produksi->kulitan_qty ?: ($produksi->singkong_qty / 0.711), 0, ',', '.') }} kg</strong> (÷ 71,1%)
                @else
                    Singkong Kulit &bull; Setara Kupas: <strong style="color: #0369a1;">{{ number_format($produksi->kupasan_qty ?: ($produksi->singkong_qty * 0.711), 0, ',', '.') }} kg</strong> (× 71,1%)
                @endif
            </div>
        </div>

        {{-- METRIK 2: TOTAL OUTPUT WIP JADI --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label">Total Output Barang Jadi (WIP)</span>
                    <div class="executive-stat-value">
                        {{ number_format($produksi->total_wip_qty, 2, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">kg</span>
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #f1f5f9; color: #475569;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                @if ($produksi->total_berko_qty > 0)
                    Termasuk Berko: <strong style="color: #b45309;">{{ number_format($produksi->total_berko_qty, 1, ',', '.') }} kg</strong> ({{ number_format($produksi->berko_persen, 1) }}%)
                @elseif ($produksi->qty_karton)
                    {{ number_format($produksi->qty_karton, 0, ',', '.') }} Karton / Box Jadi Tersimpan
                @else
                    Hasil penimbangan fisik tersimpan
                @endif
            </div>
        </div>

        {{-- METRIK 3: RENDEMEN SINGKONG (SESUAI BAHAN YANG DIGUNAKAN) --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: {{ $pakaiKupas && !$pakaiKulit ? $rKupasColor : $rKulitColor }};">Rendemen Singkong</span>
                    <div class="executive-stat-value" style="color: {{ $pakaiKupas && !$pakaiKulit ? $rKupasColor : $rKulitColor }};">
                        @if($pakaiKeduanya)
                            {{ number_format($rKulit, 2, ',', '.') }}%
                            <span style="font-size: 0.725rem; font-weight: 600; color: #64748b;">(Kulit) &bull; {{ number_format($rKupas, 2, ',', '.') }}% (Kupas)</span>
                        @elseif($pakaiKupas)
                            {{ number_format($rKupas, 2, ',', '.') }}%
                            <span style="font-size: 0.725rem; font-weight: 600; color: #64748b;">(Kupas)</span>
                        @else
                            {{ number_format($rKulit, 2, ',', '.') }}%
                            <span style="font-size: 0.725rem; font-weight: 600; color: #64748b;">(Kulit)</span>
                        @endif
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: {{ $pakaiKupas && !$pakaiKulit ? '#f0f9ff' : $rKulitBg }}; color: {{ $pakaiKupas && !$pakaiKulit ? $rKupasColor : $rKulitColor }};">
                    @if(($pakaiKupas && !$pakaiKulit ? $rKupasLolos : $rKulitLolos))
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @elseif(($pakaiKupas && !$pakaiKulit ? $rKupasWarning : $rKulitWarning))
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    @else
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                    @endif
                </div>
            </div>
            <div class="executive-stat-subtext" style="display: flex; align-items: center; justify-content: space-between; gap: 0.35rem; flex-wrap: wrap;">
                @if($pakaiKeduanya)
                    <div>
                        <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: {{ $rKulitColor }};"></span>
                        <span>Kulit: <strong>{{ number_format($rKulit, 2, ',', '.') }}%</strong> (&ge;33%)</span>
                    </div>
                    <div style="border-left: 1px solid #e2e8f0; padding-left: 0.4rem;">
                        <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: {{ $rKupasColor }};"></span>
                        <span>Kupas: <strong style="color: {{ $rKupasColor }};">{{ number_format($rKupas, 2, ',', '.') }}%</strong> (44-45%)</span>
                    </div>
                @elseif($pakaiKupas)
                    <div>
                        <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: {{ $rKupasColor }};"></span>
                        <span>Standar Kupas: <strong>{{ number_format($rKupas, 2, ',', '.') }}%</strong> (Std: 44-45%)</span>
                    </div>
                @else
                    <div>
                        <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: {{ $rKulitColor }};"></span>
                        <span>Standar Kulit: <strong>{{ number_format($rKulit, 2, ',', '.') }}%</strong> (Std: &ge;33%)</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- METRIK 4: TOTAL LIMBAH & WASTE (HACCP) --}}
        <div class="executive-stat-card">
            <div class="executive-stat-card-header">
                <div>
                    <span class="executive-stat-label" style="color: #dc2626;">Limbah &amp; Waste (HACCP)</span>
                    <div class="executive-stat-value" style="color: #dc2626;">
                        {{ number_format($totalWaste, 2, ',', '.') }} <span style="font-size: 0.85rem; font-weight: 600; color: #dc2626;">kg</span>
                    </div>
                </div>
                <div class="executive-stat-icon-box" style="background: #fef2f2; color: #dc2626;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
            </div>
            <div class="executive-stat-subtext">
                @if($produksi->waste_lengket_qty > 0)
                    Lengket: {{ number_format($produksi->waste_lengket_qty, 1, ',', '.') }} kg ({{ number_format($produksi->waste_lengket_persen, 1) }}%)
                @else
                    BS, lengket &amp; reject pemrosesan
                @endif
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- BARIS 2: 2 PANEL SEJAJAR (PARAMETER OPERASIONAL & BATCH GUDANG)    --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="meta-cards-grid-2 no-print">

        {{-- PANEL 1: PARAMETER OPERASIONAL & SHIFT --}}
        <div class="card-enterprise" style="margin-bottom: 0;">
            <div class="card-enterprise-header">
                <div class="card-enterprise-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0284c7;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Parameter Operasional Pabrik</span>
                </div>
                @if ($produksi->shift_cd)
                    <span class="badge-shift-pill {{ $produksi->shift_cd === 'A' ? 'badge-shift-a' : 'badge-shift-b' }}">
                        Shift {{ $produksi->shift_cd }}
                    </span>
                @endif
            </div>
            <div style="padding: 1rem 1.25rem;">
                <table class="spec-table">
                    <tr>
                        <td class="label-col">Tanggal Produksi</td>
                        <td class="value-col">{{ Carbon\Carbon::parse($produksi->produksi_tgl)->locale('id')->isoFormat('dddd, D MMMM Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Lokasi Produksi</td>
                        <td class="value-col">
                            <span style="display: inline-flex; align-items: center; gap: 0.35rem; background: {{ $isBmbDoc ? '#ede9fe; color: #6d28d9; border: 1px solid #c4b5fd' : '#e0f2fe; color: #0369a1; border: 1px solid #7dd3fc' }}; padding: 0.2rem 0.6rem; border-radius: 4px; font-weight: 700; font-size: 0.775rem;">
                                <span>{{ $isBmbDoc ? 'CV Bahtera Mandiri Bersama' : 'PT Mirasa Food Industry' }}</span>
                            </span>
                            @if(!empty($produksi->bf_aktif_count))
                                <span style="font-size: 0.775rem; color: #64748b; margin-left: 0.4rem; font-weight: 600;">
                                    ({{ $produksi->bf_aktif_count }} BF Aktif)
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Lini Produksi Pabrik</td>
                        <td class="value-col">{{ $produksi->lini_produksi }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Varietas Singkong (RM)</td>
                        <td class="value-col" style="color: #0284c7; font-weight: 600;">
                            {{ !empty($produksi->varietas_singkong) ? $produksi->varietas_singkong : 'Standar Pabrik (STP / MGU)' }}
                        </td>
                    </tr>
                    @php
                        $singkongDetails = ($produksi->pemakaianBahan && $produksi->pemakaianBahan->details) ? $produksi->pemakaianBahan->details->filter(function($d) {
                            $nm = strtoupper($d->barang?->barang_nm ?? '');
                            $cd = strtoupper($d->barang?->barang_cd ?? '');
                            return str_contains($nm, 'SINGKONG') || str_contains($cd, 'SK00') || str_contains($cd, 'BB-SK');
                        }) : collect([]);

                        $gradeGroups = [];
                        foreach ($singkongDetails as $sk) {
                            $gRaw = trim((string) ($sk->grade_cd ?: ($sk->barang?->grade_cd ?: 'A')));
                            $gName = strtoupper($gRaw);
                            if (!str_starts_with($gName, 'GRADE')) {
                                $gName = 'Grade ' . $gName;
                            }
                            $qtyVal = (float) ($sk->qty_keluar ?: ($sk->qty_satuan ?? 0));
                            $gradeGroups[$gName] = ($gradeGroups[$gName] ?? 0) + $qtyVal;
                        }
                        $isCampuran = count($gradeGroups) > 1;
                    @endphp
                    <tr>
                        <td class="label-col">Input Singkong Masuk</td>
                        <td class="value-col">
                            <span style="font-weight: 700;">{{ number_format($produksi->singkong_qty, 0, ',', '.') }} kg</span>
                            @if($produksi->singkong_tipe === 'KUPAS' || (float)$produksi->kupasan_qty > 0)
                                <span style="font-size: 0.75rem; font-weight: 600; color: #0369a1; margin-left: 0.35rem;">
                                    (Singkong Kupas &rarr; Setara Kulitan: {{ number_format($produksi->kulitan_qty ?: ($produksi->singkong_qty / 0.711), 0, ',', '.') }} kg / faktor 71,1%)
                                </span>
                            @else
                                <span style="font-size: 0.75rem; font-weight: 600; color: #64748b; margin-left: 0.35rem;">
                                    (Singkong Kulit &rarr; Setara Kupas: {{ number_format($produksi->kupasan_qty ?: ($produksi->singkong_qty * 0.711), 0, ',', '.') }} kg / faktor 71,1%)
                                </span>
                            @endif
                        </td>
                    </tr>
                    @if($produksi->pemakaianBahan)
                    <tr>
                        <td class="label-col">Dokumen Bahan Baku (BPPB)</td>
                        <td class="value-col">
                            <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                <a href="{{ route('gudang.pemakaian.show', $produksi->pakai_id) }}" style="color: #0284c7; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;" title="Lihat Dokumen Pengeluaran Gudang (BPPB)">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    <span>{{ $produksi->pemakaianBahan->pakai_no }}</span>
                                </a>
                                @if($produksi->pemakaianBahan->tujuan_pemakaian)
                                    <span style="font-size: 0.75rem; color: #64748b;">({{ $produksi->pemakaianBahan->tujuan_pemakaian }})</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td class="label-col">Jumlah Tenaga Kerja</td>
                        <td class="value-col">
                            {{ $tkTotal > 0 ? $tkTotal . ' Orang Hadir' : 'Standar Alokasi Shift' }}
                        </td>
                    </tr>
                    @if(($produksi->waste_total_qty > 0) || ($produksi->waste_lengket_qty > 0) || ($produksi->total_berko_qty > 0) || ($produksi->berko_ifl_qty > 0) || ($produksi->berko_non_ifl_qty > 0))
                    <tr>
                        <td class="label-col">Limbah &amp; Waste (HACCP)</td>
                        <td class="value-col">
                            <span style="font-weight: 700; color: #dc2626;">{{ number_format($totalWaste, 2, ',', '.') }} kg</span>
                            @if($produksi->waste_lengket_qty > 0)
                                <span style="font-size: 0.75rem; color: #b91c1c; margin-left: 0.25rem; font-weight: 600;">(Lengket: {{ number_format($produksi->waste_lengket_qty, 2, ',', '.') }} kg / {{ number_format($produksi->waste_lengket_persen, 2) }}%)</span>
                            @endif
                            @if($produksi->total_berko_qty > 0 || $produksi->berko_ifl_qty > 0 || $produksi->berko_non_ifl_qty > 0)
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                    Total Berko: {{ number_format($produksi->total_berko_qty ?: ($produksi->berko_ifl_qty + $produksi->berko_non_ifl_qty), 2, ',', '.') }} kg ({{ number_format($produksi->berko_persen, 2) }}%)
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- PANEL 2: MANAJEMEN BATCH & GUDANG SIMPAN --}}
        <div class="card-enterprise" style="margin-bottom: 0;">
            <div class="card-enterprise-header">
                <div class="card-enterprise-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Identitas Batch &amp; Gudang Simpan</span>
                </div>
                <span class="badge-batch-pill">
                    {{ $produksi->batch_wip_no ?: $produksi->produksi_no }}
                </span>
            </div>
            <div style="padding: 1rem 1.25rem;">
                <table class="spec-table">
                    <tr>
                        <td class="label-col">No. Batch Persediaan</td>
                        <td class="value-col" style="font-family: monospace; font-weight: 700;">
                            @if(in_array($produksi->batch_wip_no, ['BELUM TERBIT', 'PENDING']))
                                <span style="background: #fffbeb; color: #b45309; border: 1.5px solid #f59e0b; padding: 0.2rem 0.55rem; border-radius: 4px; font-size: 0.825rem; font-weight: 700;">BELUM TERBIT (Buffer Stock IFM)</span>
                            @else
                                <span style="color: #0284c7;">{{ $produksi->batch_wip_no ?: '-' }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Rentang Seri Karton Box</td>
                        <td class="value-col">
                            @if ($produksi->no_karton_awal && $produksi->no_karton_akhir)
                                <span style="font-family: monospace; font-weight: 700;">No. {{ str_pad($produksi->no_karton_awal, 4, '0', STR_PAD_LEFT) }} s/d {{ str_pad($produksi->no_karton_akhir, 4, '0', STR_PAD_LEFT) }} ({{ $produksi->qty_karton }} Box)</span>
                            @else
                                <span style="color: #64748b;">WIP Curah (Timbangan Fisik)</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Estimasi Exp Date</td>
                        <td class="value-col" style="color: #059669; font-weight: 700;">
                            {{ $expDate }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Gudang Penyimpanan</td>
                        <td class="value-col">{{ $produksi->gudang?->gudang_nm ?? 'Gudang Utama Pabrik' }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Alur Pengeluaran Stok</td>
                        <td class="value-col" style="color: #64748b; font-weight: 600;">
                            FIFO Persediaan (Gudang WIP &rarr; Kemasan)
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">Operator Pencatat</td>
                        <td class="value-col" style="font-weight: 600;">{{ $creatorName }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════ --}}
    {{-- BARIS 3: RINCIAN HASIL TIMBANGAN BARANG JADI (WIP OUTPUT) FISIK   --}}
    {{-- ═════════════════════════════════════════════════════════════════ --}}
    <div class="print-only print-section-title" style="margin-top: 10px;">III. RINCIAN HASIL TIMBANGAN BARANG JADI (WIP OUTPUT)</div>
    <div class="card-enterprise print-card-flush" style="margin-top: 1.5rem;">
        <div class="card-enterprise-header no-print" style="padding: 0.85rem 1.25rem;">
            <div class="card-enterprise-title">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #059669;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                <span>Rincian Hasil Timbangan Barang Jadi (WIP Output)</span>
                <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.75rem; font-weight: 700; border: 1px solid #cbd5e1; padding: 0.15rem 0.5rem; border-radius: 4px; margin-left: 0.5rem;">
                    {{ $produksi->outputs->count() }} Varian Output
                </span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span style="font-size: 0.825rem; font-weight: 700; color: #0f172a; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 0.25rem 0.65rem; border-radius: 4px;">
                    Total Berat: {{ number_format($produksi->total_wip_qty, 2, ',', '.') }} kg
                </span>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="table-enterprise print-table-compact">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">No</th>
                        <th style="width: 160px;">Kategori Output</th>
                        <th>Kode &amp; Nama Barang WIP / Olahan</th>
                        <th style="width: 140px; text-align: center;">Batch Varian</th>
                        <th style="width: 130px; text-align: right;">Qty Kemasan</th>
                        <th style="width: 140px; text-align: right;">Berat Bersih (Kg)</th>
                        <th style="width: 90px; text-align: center;">Porsi (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sortedOutputs = $produksi->outputs ? $produksi->outputs->sortBy(function($out) {
                            $bNm = strtoupper($out->barang?->barang_nm ?? '');
                            $bCd = strtoupper($out->barang?->barang_cd ?? '');
                            $kat = strtoupper($out->kategori_output ?? '');
                            
                            $isLengket = str_contains($bNm, 'LENGKET') || str_contains($bCd, 'LGT') || str_contains($kat, 'LGT') || str_contains($kat, 'LENGKET');
                            $isBerko = !$isLengket && (str_contains($bNm, 'BERKO') || str_contains($bCd, 'BRK') || str_contains($kat, 'BERKO') || str_contains($kat, 'BRK') || str_contains($kat, 'KW2'));
                            $isAfval = str_contains($bNm, 'AFVAL') || str_contains($bNm, 'REMAHAN') || str_contains($kat, 'AFVAL') || str_contains($bCd, 'AFV') || str_contains($kat, 'RMH');
                            $isUtama = !$isLengket && !$isBerko && !$isAfval;

                            $tier = 1;
                            if ($isBerko) $tier = 2;
                            elseif ($isLengket) $tier = 3;
                            elseif ($isAfval) $tier = 4;

                            return sprintf('%d_%010.2f', $tier, 1000000 - (float)$out->qty_kg);
                        })->values() : collect();
                    @endphp
                    @forelse ($sortedOutputs as $idx => $out)
                        @php
                            $bNm = strtoupper($out->barang?->barang_nm ?? '');
                            $bCd = strtoupper($out->barang?->barang_cd ?? '');
                            $kat = strtoupper($out->kategori_output ?? '');
                            
                            $isLengket = str_contains($bNm, 'LENGKET') || str_contains($bCd, 'LGT') || str_contains($kat, 'LGT') || str_contains($kat, 'LENGKET');
                            $isBerko = !$isLengket && (str_contains($bNm, 'BERKO') || str_contains($bCd, 'BRK') || str_contains($kat, 'BERKO') || str_contains($kat, 'BRK'));
                            $isAfval = str_contains($bNm, 'AFVAL') || str_contains($bNm, 'REMAHAN') || str_contains($kat, 'AFVAL') || str_contains($bCd, 'AFV');
                            $isUtama = !$isLengket && !$isBerko && !$isAfval;

                            $totalWip = (float) $produksi->total_wip_qty > 0 ? (float) $produksi->total_wip_qty : 1;
                            $porsiPersen = round(((float)$out->qty_kg / $totalWip) * 100, 1);
                            $satuanTampil = $out->satuan_cd ?: ($out->barang?->satuan?->satuan_nm ?? 'KG');
                            $hasQtyKemasan = (float) $out->qty_hasil > 0;
                        @endphp
                        <tr>
                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $idx + 1 }}</td>
                            <td>
                                @if($isUtama)
                                    <span style="display: inline-block; padding: 0.2rem 0.5rem; background: #f1f5f9; color: #1e293b; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.725rem; font-weight: 600;">
                                        {{ $out->kategori_output ?: 'Produk Utama' }}
                                    </span>
                                @elseif($isBerko)
                                    <span style="display: inline-block; padding: 0.2rem 0.5rem; background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.725rem; font-weight: 600;">
                                        KS Berko
                                    </span>
                                @elseif($isLengket)
                                    <span style="display: inline-block; padding: 0.2rem 0.5rem; background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.725rem; font-weight: 600;">
                                        KS Lengket
                                    </span>
                                @else
                                    <span style="display: inline-block; padding: 0.2rem 0.5rem; background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 0.725rem; font-weight: 600;">
                                        Remahan / BS
                                    </span>
                                @endif
                            </td>
                            <td>
                                <strong style="color: #0f172a; font-family: monospace;">{{ $out->barang?->barang_cd ?? '-' }}</strong> &ndash; 
                                <span style="font-weight: 600; color: #1e293b;">{{ $out->barang?->barang_nm ?? '-' }}</span>
                            </td>
                            <td style="font-family: monospace; font-weight: 600; color: #475569; text-align: center;">
                                {{ $out->batch_no }}
                            </td>
                            <td style="text-align: right; font-weight: 600; color: #334155;">
                                @if($hasQtyKemasan)
                                    {{ number_format((float) $out->qty_hasil, 0, ',', '.') }} {{ $satuanTampil }}
                                @else
                                    <span style="color: #94a3b8; font-size: 0.75rem;">Curah (Kg)</span>
                                @endif
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a; font-variant-numeric: tabular-nums;">
                                {{ number_format($out->qty_kg, 2, ',', '.') }} kg
                            </td>
                            <td style="text-align: center; font-weight: 600; color: #475569; font-variant-numeric: tabular-nums;">
                                {{ number_format($porsiPersen, 1, ',', '.') }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: #94a3b8;">
                                Belum ada rincian output timbangan tersimpan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot style="background: #f8fafc; font-weight: 700; border-top: 2px solid #cbd5e1;">
                    <tr>
                        <td colspan="5" style="padding: 0.75rem 1rem; text-align: right; font-size: 0.78rem; color: #475569; text-transform: uppercase; letter-spacing: 0.03em;">
                            TOTAL AKUMULASI OUTPUT:
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right; color: #0f172a; font-size: 0.95rem; font-variant-numeric: tabular-nums;">
                            {{ number_format($produksi->total_wip_qty, 2, ',', '.') }} kg
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: center; color: #475569; font-size: 0.85rem; font-variant-numeric: tabular-nums;">
                            100.0%
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- CATATAN OPERASIONAL PABRIK (JIKA ADA) --}}
    @if(!empty($produksi->catatan))
        <div class="card-enterprise" style="margin-top: 1.25rem;">
            <div class="card-enterprise-header">
                <div class="card-enterprise-title">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748b;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    <span>Catatan Operasional Shift</span>
                </div>
            </div>
            <div style="padding: 0.85rem 1.25rem; font-size: 0.85rem; color: #334155; line-height: 1.6;">
                {{ $produksi->catatan }}
            </div>
        </div>
    @endif

    </div> {{-- /no-print web dashboard --}}

</div> {{-- /show-container --}}

@include('produksi.partials.modal-assign-batch')
@endsection

@push('scripts')
    <script>
        window.produksiData = {
            id: {{ $produksi->produksi_id }},
            no: "{{ $produksi->produksi_no }}",
            qtyKarton: {{ (int) $produksi->qty_karton }},
            assignBatchUrl: "{{ route('produksi.assign-batch', $produksi->produksi_id) }}"
        };
    </script>
    <script src="{{ asset('js/produksi/produksi-show.js') }}?v={{ filemtime(public_path('js/produksi/produksi-show.js')) }}"></script>
@endpush
