<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Stiker Fisik - {{ $produksi->batch_wip_no }}</title>
    <link rel="stylesheet" href="{{ asset('css/produksi/produksi-cetak-stiker.css') }}">
</head>
<body>
    @php
        $isIfm = str_contains(strtoupper($produksi->lini_produksi), 'IFM');
        $title = $isIfm ? 'WIP-FCC' : strtoupper(str_replace('PRODUKSI ', '', $produksi->lini_produksi ?: 'FINISHED GOODS'));
        $expDateObj = !empty($produksi->exp_date)
            ? Carbon\Carbon::parse($produksi->exp_date)
            : ($isIfm
                ? Carbon\Carbon::parse($produksi->produksi_tgl)->addMonths(6)
                : Carbon\Carbon::parse($produksi->produksi_tgl)->addYear()->subDay());
        
        $tglProduksiStr = strtoupper(Carbon\Carbon::parse($produksi->produksi_tgl)->format('d M Y'));
        $tglKadaluarsaStr = strtoupper($expDateObj->format('d M Y'));
        
        $jamStr = $produksi->jam_produksi ? str_replace(':', '.', $produksi->jam_produksi) : '14.03';
        $activeShift = $produksi->shift_cd ?: 'A';
        $rawKarton = (int) ($noKarton ?: ($produksi->no_karton_awal ?: 1));
        $kartonPadded = str_pad((string) $rawKarton, 4, '0', STR_PAD_LEFT);
        $batchNoDisplay = $isIfm ? ($activeShift . ' / ' . $kartonPadded) : $produksi->batch_wip_no;

        $noAwalPadded = str_pad((string) ($produksi->no_karton_awal ?: 1), 4, '0', STR_PAD_LEFT);
        $noAkhirPadded = str_pad((string) ($produksi->no_karton_akhir ?: 1), 4, '0', STR_PAD_LEFT);
    @endphp

    {{-- TOOLBAR KONTROL CETAK (Hanya Muncul di Layar, Tersembunyi Saat Dicetak) --}}
    <div class="toolbar-container">
        <div class="toolbar-title">
            <span>🏷️ Cetak Label Stiker Fisik Kemasan (1:1 Standar Pabrik)</span>
            <span style="font-size: 11px; color: #64748b; font-weight: normal;">Batch: {{ $produksi->batch_wip_no }}</span>
        </div>

        <div class="toolbar-actions">
            <button onclick="window.print()" class="btn-print-primary">
                🖨️ Cetak Label Sekarang (Ctrl + P)
            </button>
            <button onclick="window.close()" class="btn-toolbar-secondary">
                ✕ Tutup
            </button>
        </div>

        @if ($isIfm && ($produksi->qty_karton > 1))
            <div class="toolbar-filter">
                <label for="selectKarton">Pilihan Karton Box:</label>
                <select id="selectKarton">
                    <option value="{{ $rawKarton }}">Cetak Karton Ini Saja (No. {{ $kartonPadded }})</option>
                    <option value="ALL">🗂️ Cetak Semua Sekaligus ({{ $produksi->qty_karton }} Karton: No. {{ $noAwalPadded }} - {{ $noAkhirPadded }})</option>
                    @for ($k = $produksi->no_karton_awal; $k <= $produksi->no_karton_akhir; $k++)
                        @if ($k != $rawKarton)
                            <option value="{{ $k }}">Cetak Khusus Karton No. {{ str_pad((string)$k, 4, '0', STR_PAD_LEFT) }}</option>
                        @endif
                    @endfor
                </select>
            </div>
        @endif
    </div>

    {{-- CONTAINER HALAMAN CETAK STIKER --}}
    <div class="sticker-page-wrapper" id="stickerPageWrapper">
        {{-- TEMPLATE STIKER FISIK ASLI PABRIK (1:1 DENGAN FOTO FISIK) --}}
        <div class="carton-physical-sticker" id="singleSticker">
            {{-- Header: WIP-FCC & Logo Halal Majelis Ulama --}}
            <div class="sticker-header">
                <div class="sticker-title">{{ $title }}</div>
                <div class="sticker-halal-box">
                    <svg viewBox="0 0 100 100" width="34" height="34" style="display: block; margin: 0 auto;">
                        <circle cx="50" cy="50" r="46" fill="none" stroke="#000000" stroke-width="4"/>
                        <circle cx="50" cy="50" r="39" fill="none" stroke="#000000" stroke-width="1.5"/>
                        <text x="50" y="30" font-size="8.5" font-weight="900" text-anchor="middle" font-family="Arial, sans-serif">MAJELIS ULAMA</text>
                        <text x="50" y="58" font-size="18" font-weight="900" text-anchor="middle" font-family="'Times New Roman', serif">حلال</text>
                        <text x="50" y="73" font-size="8" font-weight="900" text-anchor="middle" font-family="Arial, sans-serif">INDONESIA</text>
                    </svg>
                    <div class="halal-cert-id">ID3321000001931219</div>
                    <div class="halal-cert-date">6 Februari 2024</div>
                </div>
            </div>

            {{-- Body: 2 Kolom Alami Tanpa Gap Canggung --}}
            <div class="sticker-cols-container">
                {{-- Kolom Kiri --}}
                <div class="st-left-col">
                    <div class="st-row">
                        <span class="st-lbl">No Batch :</span>
                        <span class="st-val st-batch-num">{{ $batchNoDisplay }}</span>
                    </div>
                    <div class="st-row">
                        <span class="st-lbl">Gross :</span>
                        <span class="st-val">{{ $isIfm ? '7.08 kg' : 'STANDAR' }}</span>
                    </div>
                    <div class="st-row">
                        <span class="st-lbl">Netto :</span>
                        <span class="st-val">{{ $isIfm ? '6 kg' : 'BAL/RETAIL' }}</span>
                    </div>
                    <div class="st-row">
                        <span class="st-lbl">Jam :</span>
                        <span class="st-val">{{ $jamStr }}</span>
                    </div>
                </div>

                {{-- Kolom Kanan --}}
                <div class="st-right-col">
                    <div class="st-row">
                        <span class="st-lbl">Tgl. Produksi :</span>
                        <span class="st-val">{{ $tglProduksiStr }}</span>
                    </div>
                    <div class="st-row">
                        <span class="st-lbl">Tgl. Kadaluarsa :</span>
                        <span class="st-val">{{ $tglKadaluarsaStr }}</span>
                    </div>
                    <div class="st-row">
                        <span class="st-lbl">Varietas RM :</span>
                        <span class="st-val">{{ $isIfm ? ($produksi->varietas_singkong ?: 'STP / MGU') : 'STANDAR' }}</span>
                    </div>
                    <div class="st-row" style="justify-content: flex-end;">
                        <div class="plant-code-box">{{ $isIfm ? 'M029 / - / ISA' : 'MIRASA / FG' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Script Variabel Konfigurasi & Event Handler --}}
    <script>
        window.cetakStikerConfig = {
            isIfm: {{ $isIfm ? 'true' : 'false' }},
            shift: "{{ $activeShift }}",
            noAwal: {{ (int) ($produksi->no_karton_awal ?: 1) }},
            noAkhir: {{ (int) ($produksi->no_karton_akhir ?: 1) }},
            qtyKarton: {{ (int) ($produksi->qty_karton ?: 1) }},
            batchNo: "{{ $produksi->batch_wip_no }}"
        };
    </script>
    <script src="{{ asset('js/produksi/produksi-cetak-stiker.js') }}"></script>
</body>
</html>
