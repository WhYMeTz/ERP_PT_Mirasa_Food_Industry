{{-- Modal Pratinjau Dokumen Fisik PO (macOS Quick Look Modal) --}}
<div id="modalDocumentPreview" class="macos-doc-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modalDocPreviewTitle">
    <div class="macos-doc-modal-window">
        
        {{-- macOS Window Titlebar --}}
        <div class="macos-doc-modal-header">
            {{-- Left: macOS Traffic Lights (Window Controls) --}}
            <div class="mac-traffic-lights" aria-label="Window Controls">
                <button type="button" class="mac-traffic-dot red" onclick="closeDocumentModal()" title="Tutup (Esc)">
                    <span class="mac-traffic-icon">&times;</span>
                </button>
                <button type="button" class="mac-traffic-dot yellow" onclick="closeDocumentModal()" title="Minimalkan">
                    <span class="mac-traffic-icon">&minus;</span>
                </button>
                <button type="button" class="mac-traffic-dot green" onclick="zoomModalDoc('reset')" title="Reset Skala 100%">
                    <span class="mac-traffic-icon">&plus;</span>
                </button>
            </div>

            {{-- Center: macOS Document Proxy & Title --}}
            <div class="mac-window-title-center" id="modalDocPreviewTitle">
                <svg class="mac-window-file-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="mac-window-filename">PO #{{ $po->po_no }} &mdash; Dokumen Resmi</span>
                @if($isApprovedState)
                    <span class="modal-status-chip chip-approved">✓ Sah &amp; Berstempel</span>
                @else
                    <span class="modal-status-chip chip-pending">● Draft Pengajuan</span>
                @endif
            </div>

            {{-- Right: macOS Toolbar Actions --}}
            <div class="modal-header-actions">
                <div class="mac-segmented-control">
                    <button type="button" class="btn-mac-seg" onclick="zoomModalDoc('out')" title="Perkecil (-)">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    </button>
                    <button type="button" class="btn-mac-seg btn-mac-seg-label" id="zoomLevelIndicator" onclick="zoomModalDoc('reset')" title="Reset Skala (100%)">
                        100%
                    </button>
                    <button type="button" class="btn-mac-seg" onclick="zoomModalDoc('in')" title="Perbesar (+)">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </button>
                </div>

                <div class="modal-tool-divider"></div>

                <button type="button" class="btn-modal-mac-action" onclick="printModalDocument()" title="Cetak Dokumen">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print</span>
                </button>

                <a href="{{ route('gudang.po.export-pdf', $po->po_id) }}" target="_blank" class="btn-modal-mac-action btn-mac-primary" title="Unduh PDF Resmi">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Unduh PDF</span>
                </a>
            </div>
        </div>

        {{-- Scrollable Canvas Desk --}}
        <div class="macos-doc-modal-body">
            
            {{-- Kertas Fisik A4 Asli (Format Gambar 2) --}}
            <div class="modal-a4-paper-sheet" id="modalA4Paper">
                
                {{-- 1. KOP RESMI HACCP PT MIRASA FOOD INDUSTRY --}}
                <table class="kop-table">
                    <tr>
                        <td class="kop-logo">
                            <img src="{{ asset('images/logo.png') }}" style="width: 54px; height: auto; display: block; margin: 0 auto;" alt="Logo Cap Payung">
                            <div style="font-size: 5.5pt; font-weight: 800; color: #dc2626; margin-top: 2px; letter-spacing: 0.02em; text-align: center;">
                                ENAK &bull; GURIH &bull; LEZAT
                            </div>
                        </td>
                        <td class="kop-title">
                            <div class="company-name">PT MIRASA FOOD INDUSTRY</div>
                            <div class="form-name">FORM PERMINTAAN BARANG</div>
                        </td>
                        <td class="kop-meta">
                            <table class="meta-table">
                                <tr>
                                    <td class="meta-label">Nomor dokumen</td>
                                    <td class="meta-sep">:</td>
                                    <td class="meta-val">MFI/HACCP-04/FRM-03/050/VIII/2021</td>
                                </tr>
                                <tr>
                                    <td class="meta-label">Terbitan/Tgl</td>
                                    <td class="meta-sep">:</td>
                                    <td class="meta-val">19-08-2021</td>
                                </tr>
                                <tr>
                                    <td class="meta-label">Revisi</td>
                                    <td class="meta-sep">:</td>
                                    <td class="meta-val">0</td>
                                </tr>
                                <tr>
                                    <td class="meta-label">Halaman</td>
                                    <td class="meta-sep">:</td>
                                    <td class="meta-val">1</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <hr class="kop-divider">

                {{-- 2. JUDUL DOKUMEN --}}
                <div class="doc-title">PURCHASE ORDER</div>

                {{-- 3. PARAMETER PO & KOTAK SUPPLIER --}}
                <table class="info-table">
                    <tr>
                        {{-- Kiri: Parameter PO --}}
                        <td style="width: 48%;">
                            <table class="po-params-table">
                                <tr>
                                    <td style="width: 65px; font-weight: 600;">Quot No</td>
                                    <td style="width: 10px; text-align: center;">:</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 700;">PO No.</td>
                                    <td style="text-align: center; font-weight: 700;">:</td>
                                    <td><span class="po-no-red">{{ $po->po_no }}</span></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 600;">Tanggal</td>
                                    <td style="text-align: center;">:</td>
                                    <td><strong>{{ $tglPo }}</strong></td>
                                </tr>
                            </table>
                        </td>

                        {{-- Jarak antar kolom --}}
                        <td style="width: 4%;"></td>

                        {{-- Kanan: Kotak Supplier --}}
                        <td style="width: 48%;">
                            <div class="supplier-box">
                                <strong style="font-size: 9.5pt; text-transform: uppercase;">
                                    {{ $po->supplier?->nama_supplier ?? $po->supplier?->supplier_nm ?? '-' }}
                                </strong>
                                <div>{{ $po->supplier?->alamat ?? $po->supplier?->alamat_txt ?? '-' }}</div>
                                <table style="width: 100%; border-collapse: collapse; margin-top: 3px; font-size: 8.5pt;">
                                    <tr>
                                        <td style="width: 45px; padding: 0;">Up</td>
                                        <td style="width: 8px; padding: 0;">:</td>
                                        <td style="padding: 0;">{{ $po->supplier?->kontak_person ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 0;">Telpon</td>
                                        <td style="padding: 0;">:</td>
                                        <td style="padding: 0;">{{ $po->supplier?->telepon ?? $po->supplier?->kontak_no ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>

                {{-- 4. KOTAK SHIP TO & INVOICE TO (SIDE BY SIDE) --}}
                <table class="address-row-table">
                    <tr>
                        <td class="address-box" style="width: 48%;">
                            <strong style="text-decoration: underline;">Ship To :</strong><br>
                            <strong>PT MIRASA FOOD INDUSTRY</strong><br>
                            JL. Munggur No. 2 Kalangan Wetan, Ambartawang<br>
                            Mungkid, Magelang, Jawa Tengah<br>
                            Telp : 0293 3280554, 3280556
                        </td>
                        <td style="width: 4%;"></td>
                        <td class="address-box" style="width: 48%;">
                            <strong style="text-decoration: underline;">Invoice To :</strong><br>
                            <strong>PT MIRASA FOOD INDUSTRY</strong><br>
                            JL. Munggur No. 2 Kalangan Wetan, Ambartawang<br>
                            Mungkid, Magelang, Jawa Tengah<br>
                            Telp : 0293 3280554, 3280556<br>
                            NPWP : 07.635.700.4-152.000
                        </td>
                    </tr>
                </table>

                {{-- 5. TABEL RINCIAN ITEM BARANG --}}
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 32px;">No</th>
                            <th style="text-align: left; padding-left: 8px;">Description</th>
                            <th style="width: 70px; text-align: right; padding-right: 6px;">Qty</th>
                            <th style="width: 48px;">Sat</th>
                            <th style="width: 105px; text-align: right; padding-right: 6px;">Unit Price</th>
                            <th style="width: 120px; text-align: right; padding-right: 8px;">Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($po->details as $idx => $d)
                            @php
                                $itemQty = (float) $d->pesan_qty;
                                $itemPrice = (float) ($d->harga_nominal ?? 0);
                                $itemSubtotal = (float) ($d->subtotal_nominal ?? ($itemQty * $itemPrice));
                            @endphp
                            <tr class="data-row">
                                <td style="text-align: center; font-weight: 700;">{{ $idx + 1 }}</td>
                                <td style="font-weight: 700; text-transform: uppercase;">
                                    {{ $d->barang?->barang_nm ?? ($d->barang_nm ?? 'Item Komoditas') }}
                                </td>
                                <td style="text-align: right; font-weight: 700;">
                                    {{ number_format($itemQty, 3, ',', '.') }}
                                </td>
                                <td style="text-align: center;">
                                    {{ $d->satuan_nm ?? ($d->barang?->satuanDasar?->satuan_nm ?? 'Unit') }}
                                </td>
                                <td>
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <tr>
                                            <td style="width: 25px; border: none; padding: 0;">Rp</td>
                                            <td style="text-align: right; border: none; padding: 0;">{{ number_format($itemPrice, 0, ',', '.') }}</td>
                                        </tr>
                                    </table>
                                </td>
                                <td>
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <tr>
                                            <td style="width: 25px; border: none; padding: 0; font-weight: 700;">Rp</td>
                                            <td style="text-align: right; border: none; padding: 0; font-weight: 700;">{{ number_format($itemSubtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        @endforeach

                        {{-- Blank filler rows --}}
                        @for($i = count($po->details); $i < 6; $i++)
                            <tr class="blank-row">
                                <td style="text-align: center; color: #cbd5e1;">{{ $i + 1 }}</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>

                {{-- 6. NOTE & KOTAK TOTAL / PPN / GRAND TOTAL --}}
                <table class="summary-table">
                    <tr>
                        <td class="note-cell">
                            <strong>NOTE :</strong>
                            @if ($po->catatan_txt)
                                <span style="font-weight: 700; color: #000000;">{{ $po->catatan_txt }}</span>
                            @elseif ($tglEta)
                                <span style="font-weight: 700; color: #000000;">*Mohon dikirim tanggal {{ $tglEta }}</span>
                            @else
                                <span>-</span>
                            @endif
                        </td>
                        <td style="width: 38%; padding: 4px 6px;">
                            <table class="total-box-table">
                                <tr>
                                    <td style="font-weight: 700; width: 85px;">Total</td>
                                    <td style="width: 15px;">Rp</td>
                                    <td style="text-align: right; font-weight: 700;">
                                        {{ number_format($subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 700;">PPN</td>
                                    <td>Rp</td>
                                    <td style="text-align: right; font-weight: 700;">
                                        {{ number_format($ppn, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr style="border-top: 1px solid #000000;">
                                    <td style="font-weight: 800; font-size: 9pt;">Grand Total</td>
                                    <td style="font-weight: 800; font-size: 9pt;">Rp</td>
                                    <td style="text-align: right; font-weight: 800; font-size: 9.5pt;">
                                        {{ number_format($grandTotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                {{-- 7. TERM OF PAYMENT, FRANCO, REMARK --}}
                <table class="terms-table">
                    <tr>
                        <td class="terms-label">Term of Payment</td>
                        <td class="terms-sep">:</td>
                        <td class="terms-val">
                            <strong>
                                @if (!empty($po->term_of_payment))
                                    {{ $po->term_of_payment }}
                                @elseif ($po->supplier?->top_hari && $po->supplier->top_hari > 0)
                                    Tempo {{ $po->supplier->top_hari }} Hari (Transfer)
                                @else
                                    Transfer
                                @endif
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <td class="terms-label">Franco</td>
                        <td class="terms-sep">:</td>
                        <td class="terms-val">Franco Gudang Magelang</td>
                    </tr>
                    <tr>
                        <td class="terms-label">Remark</td>
                        <td class="terms-sep">:</td>
                        <td class="terms-val">{{ $po->remark_txt ?: 'Aman' }}</td>
                    </tr>
                </table>

                {{-- 8. TANDA TANGAN & FOOTNOTE NB FAKTUR PAJAK --}}
                <table class="footer-table">
                    <tr>
                        {{-- Kiri: Footnote NPWP, Faktur Pajak & Rekening Pembayaran --}}
                        <td style="width: 60%; vertical-align: bottom;">
                            <div class="nb-tax">
                                <div style="font-weight: 800; font-size: 8.5pt; margin-bottom: 2px;">NB :</div>
                                <table style="width: 100%; border-collapse: collapse; font-size: 8pt;">
                                    <tr>
                                        <td style="width: 82px; vertical-align: top; font-weight: 700;">&bull; Faktur Pajak</td>
                                        <td style="width: 10px; vertical-align: top; text-align: center;">:</td>
                                        <td style="vertical-align: top;">
                                            a.n. <strong>PT MIRASA FOOD INDUSTRY</strong> (NPWP: 07.635.700.4-152.000)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="vertical-align: top; font-weight: 700;">&bull; Dari Rekening</td>
                                        <td style="width: 10px; vertical-align: top; text-align: center;">:</td>
                                        <td style="vertical-align: top;">
                                            BCA <strong>104-9173332</strong> a.n. PT MIRASA FOOD INDUSTRY
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>

                        {{-- Kanan: Tanda Tangan Hormat Kami & Stempel Resmi (Dinamis Sesuai Approver) --}}
                        <td style="width: 40%; text-align: center; vertical-align: top;">
                            <div style="font-size: 9pt; font-weight: 700; margin-bottom: 2px;">
                                Hormat Kami,
                            </div>

                            @if ($isApprovedState)
                                {{-- Jika sudah diapprove: Otomatis memunculkan tanda tangan & cap stempel basah approver --}}
                                <div style="margin: 2px auto; text-align: center; height: 80px; display: flex; align-items: center; justify-content: center;">
                                    <img src="{{ asset('images/signatures/' . $sigFilename) }}" style="height: 78px; max-width: 150px; object-fit: contain; display: inline-block;" alt="Tanda Tangan &amp; Cap Resmi">
                                </div>
                            @else
                                {{-- Jika belum diapprove: Ruang kosong 80px siap tanda tangan --}}
                                <div style="height: 80px;"></div>
                            @endif

                            <div style="font-size: 9.5pt; font-weight: 800; text-decoration: underline; color: #1e3a8a; margin-top: 2px;">
                                {{ $approverDisplayName }}
                            </div>
                            <div style="font-size: 7.5pt; color: #4b5563;">
                                PT Mirasa Food Industry
                            </div>
                        </td>
                    </tr>
                </table>

            </div>

        </div>

    </div>
</div>

