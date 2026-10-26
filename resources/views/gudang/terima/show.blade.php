@extends('layouts.app')

@section('title', 'Detail Penerimaan Barang ' . $terima->terima_no . ' - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gudang/terima/terima-show.css') }}">
@endpush

@section('content')

{{-- PRINT-ONLY HEADER --}}
<div class="print-only" style="margin-bottom: 1.5rem; border-bottom: 2px solid #0f172a; padding-bottom: 0.75rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0;">PT. MIRASA FOOD INDUSTRY</h2>
            <p style="font-size: 0.75rem; color: #475569; margin: 0.2rem 0 0 0;">
                Pabrik Pengolahan F&B Singkong &bull; Jl. Munggur No. 2 Ambartawang, Magelang
            </p>
        </div>
        <div style="text-align: right;">
            <strong style="font-size: 1.1rem; color: #059669; display: block;">BUKTI PENERIMAAN BARANG & SLIP TIMBANG</strong>
            <span style="font-family: monospace; font-size: 0.9rem; font-weight: 700;">{{ $terima->terima_no }}</span>
        </div>
    </div>
</div>

<div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <a href="{{ route('gudang.terima.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
            &larr; Kembali ke Daftar Penerimaan
        </a>
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0;">{{ $terima->terima_no }}</h1>
            <span class="badge badge-success">Stok Masuk Gudang (GRN)</span>

            @php
                $firstHutangDtl = $terima->hutangDetails->first();
                $hutangHdr = $firstHutangDtl?->header;
            @endphp

            @if ($hutangHdr)
                <a href="{{ route('keuangan.hutang.show', $hutangHdr->hutang_id) }}" style="text-decoration: none;">
                    <span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                        💼 Ditagihkan: {{ $hutangHdr->hutang_no }}
                    </span>
                </a>
            @else
                <span class="badge" style="background: #f8fafc; color: #64748b; border: 1px solid #cbd5e1; font-weight: 600;">
                    ⏳ Belum Ditagihkan
                </span>
            @endif
        </div>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; position: relative;">
        @if ($hutangHdr)
            <a href="{{ route('keuangan.hutang.show', $hutangHdr->hutang_id) }}" class="btn btn-secondary" style="background: #eff6ff; border: 1.5px solid #0284c7; color: #0284c7; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem;" title="Buka Invoice Tagihan Hutang Pembelian">
                <span>💼 Invoice AP ({{ $hutangHdr->hutang_no }})</span>
            </a>
        @endif

        <a href="{{ route('gudang.terima.export-pdf', $terima->terima_id) }}" target="_blank" class="btn btn-secondary" style="background: #ffffff; border: 1.5px solid #dc2626; color: #dc2626; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#ffffff'" title="Buka & Cetak Bukti Penerimaan Barang Dokumen PDF Resmi">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <span>Cetak PDF (GRN)</span>
        </a>

        @php
            $totalRejectQty = (float) $terima->details->sum('reject_qty');
            $hasQcRejection = $terima->qc_id && ($totalRejectQty > 0 || ($terima->qc && ($terima->qc->status_qc === 'DITOLAK_TOTAL' || $terima->qc->details->sum('qty_reject') > 0)));
        @endphp

        @if ($hasQcRejection)
            <button type="button" 
                    onclick="openBeritaAcaraModal('{{ $terima->qc_id }}', '{{ $terima->qc?->qc_no ?? $terima->terima_no }}')" 
                    class="btn btn-secondary" 
                    style="background: #ffffff; border: 1.5px solid #dc2626; color: #dc2626; font-size: 0.85rem; font-weight: 700; padding: 0.55rem 0.95rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease-in-out; cursor: pointer;" 
                    onmouseover="this.style.background='#fef2f2'" 
                    onmouseout="this.style.background='#ffffff'" 
                    title="Buka & Cetak Berita Acara Penolakan Bahan Baku HACCP">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Berita Acara Penolakan</span>
            </button>
        @endif

        {{-- Dropdown Aksi Show --}}
        <button type="button" class="btn btn-secondary" onclick="toggleSmartActionDropdown(this, event, 'dropdown-show-terima')" style="background: #ffffff; border: 1.5px solid #cbd5e1; font-weight: 700; font-size: 0.85rem; padding: 0.55rem 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem;">
            <span>Aksi Dokumen</span>
            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <div id="dropdown-show-terima" class="action-dropdown-menu">
            <a href="{{ route('gudang.stok.index', ['gudang_id' => $terima->gudang_id]) }}" class="action-dropdown-item">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Lihat Stok di Gudang Ini</span>
            </a>
            @if ($terima->po_id)
                <a href="{{ route('gudang.po.show', $terima->po_id) }}" class="action-dropdown-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Buka Purchase Order Terkait</span>
                </a>
            @endif
            @if ($terima->qc_id)
                <a href="{{ route('qc.inbound.show', $terima->qc_id) }}" class="action-dropdown-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Buka Tiket QC Inbound Terkait</span>
                </a>
                @if ($hasQcRejection)
                    <button type="button" 
                            onclick="openBeritaAcaraModal('{{ $terima->qc_id }}', '{{ $terima->qc?->qc_no ?? $terima->terima_no }}')" 
                            class="action-dropdown-item warning-item" 
                            style="color: #dc2626; font-weight: 700;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Cetak Berita Acara Penolakan</span>
                    </button>
                @endif
            @endif
            @if ($hutangHdr)
                <a href="{{ route('keuangan.hutang.show', $hutangHdr->hutang_id) }}" class="action-dropdown-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Buka Invoice Keuangan ({{ $hutangHdr->hutang_no }})</span>
                </a>
            @endif
            @if (Auth::user()?->canEditTerima())
                <a href="{{ route('gudang.terima.edit', $terima->terima_id) }}" class="action-dropdown-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Penerimaan Ini</span>
                </a>
            @endif
            @if (Auth::user()?->canDeleteTerima())
                <div class="action-dropdown-divider"></div>
                <button type="button" class="action-dropdown-item danger-item" onclick="openDeleteTerimaModal({{ $terima->terima_id }}, '{{ $terima->terima_no }}', '{{ addslashes($terima->supplier?->supplier_nm ?? '') }}')">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Hapus Dokumen Ini</span>
                </button>
            @endif
        </div>
    </div>
</div>

    {{-- ALERT BANNER PENOLAKAN BAHAN BAKU --}}
    @if ($totalRejectQty > 0 || ($terima->qc && $terima->qc->details->sum('qty_reject') > 0))
        @php
            $displayReject = $totalRejectQty > 0 ? $totalRejectQty : (float) $terima->qc->details->sum('qty_reject');
        @endphp
        <div class="no-print" style="margin-bottom: 1.5rem; background: #fef2f2; border: 1.5px solid #fca5a5; border-radius: 8px; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; box-shadow: 0 1px 3px rgba(220,38,38,0.08);">
            <div style="display: flex; align-items: center; gap: 0.85rem;">
                <div style="width: 38px; height: 38px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #dc2626; font-size: 1.25rem;">
                    ⚠️
                </div>
                <div>
                    <strong style="color: #991b1b; font-size: 0.95rem;">Terdapat Penolakan Muatan Bahan Baku ({{ number_format($displayReject, 2, ',', '.') }} KG Ditolak)</strong>
                    <p style="color: #b91c1c; font-size: 0.825rem; margin: 0.2rem 0 0 0; line-height: 1.4;">
                        Kuantitas yang ditolak (Grade B / cacat mutu) tidak masuk stok gudang dan tidak memotong tagihan PO. Berita Acara Penolakan HACCP resmi telah disiapkan untuk sopir/supplier.
                    </p>
                </div>
            </div>
            @if ($terima->qc_id)
                <button type="button" 
                        onclick="openBeritaAcaraModal('{{ $terima->qc_id }}', '{{ $terima->qc?->qc_no ?? $terima->terima_no }}')" 
                        class="btn btn-sm" 
                        style="background: #dc2626; color: #ffffff; font-weight: 800; border-radius: 6px; padding: 0.5rem 1rem; border: none; white-space: nowrap; box-shadow: 0 2px 4px rgba(220,38,38,0.25); cursor: pointer;">
                    📄 Buka Berita Acara Penolakan &rarr;
                </button>
            @endif
        </div>
    @endif

    @if ($hutangHdr)
        @php
            // Hitung total diskon dan potongan di GRN
            $grnDiskonTotal = (float) $terima->details->sum(function($d) {
                $sub = (float)$d->terima_qty * (float)$d->harga_nominal;
                if ((float)$d->diskon_persen > 0) return round($sub * ((float)$d->diskon_persen / 100), 2);
                return (float)$d->terima_qty * (float)($d->diskon_nominal ?? 0);
            });
            $grnPotonganTotal = (float) $terima->details->sum('potongan_nominal');

            // Hitung total diskon dan potongan di Keuangan
            $apDiskonTotal = (float) $terima->hutangDetails->sum('diskon_nominal');
            $apPotonganTotal = (float) $terima->hutangDetails->sum('potongan_nominal');

            $selisihDiskon = round($apDiskonTotal - $grnDiskonTotal, 2);
            $selisihPotongan = round($apPotonganTotal - $grnPotonganTotal, 2);
            $adaPenyesuaian = (abs($selisihDiskon) > 0.01 || abs($selisihPotongan) > 0.01);
        @endphp

        <div class="card no-print" style="margin-bottom: 1.5rem; background: #ffffff; border: 1.5px solid {{ $adaPenyesuaian ? '#f59e0b' : '#38bdf8' }}; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">
            <div style="background: {{ $adaPenyesuaian ? '#fffbeb' : '#f0f9ff' }}; padding: 0.85rem 1.25rem; border-bottom: 1px solid {{ $adaPenyesuaian ? '#fef3c7' : '#e0f2fe' }}; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <span style="font-size: 1.25rem;">{{ $adaPenyesuaian ? '📝' : '💼' }}</span>
                    <div>
                        <strong style="font-size: 0.925rem; color: {{ $adaPenyesuaian ? '#92400e' : '#0369a1' }};">
                            {{ $adaPenyesuaian ? 'Penyesuaian & Rekonsiliasi Tagihan Keuangan' : 'Dokumen Tagihan Hutang Pembelian (AP Invoice)' }}
                        </strong>
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                            Terkait Invoice Keuangan: <a href="{{ route('keuangan.hutang.show', $hutangHdr->hutang_id) }}" style="font-weight: 800; font-family: monospace; color: #0284c7; text-decoration: underline;">{{ $hutangHdr->hutang_no }}</a>
                            @if ($hutangHdr->no_faktur_supplier)
                                &bull; Faktur Supplier: <strong>{{ $hutangHdr->no_faktur_supplier }}</strong>
                            @endif
                            &bull; Tgl Tagihan: {{ \Carbon\Carbon::parse($hutangHdr->tgl_tagihan)->format('d/m/Y') }}
                        </div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span class="badge" style="background: {{ $hutangHdr->status_pembayaran === 'LUNAS' ? '#dcfce7' : ($hutangHdr->status_pembayaran === 'SEBAGIAN' ? '#fef3c7' : '#fee2e2') }}; color: {{ $hutangHdr->status_pembayaran === 'LUNAS' ? '#15803d' : ($hutangHdr->status_pembayaran === 'SEBAGIAN' ? '#b45309' : '#b91c1c') }}; font-weight: 700;">
                        {{ str_replace('_', ' ', $hutangHdr->status_pembayaran) }}
                    </span>
                    <a href="{{ route('keuangan.hutang.show', $hutangHdr->hutang_id) }}" class="btn btn-secondary btn-sm" style="font-size: 0.775rem; font-weight: 700; background: #ffffff;">
                        Buka Invoice Keuangan &rarr;
                    </a>
                </div>
            </div>

            <div style="padding: 1rem 1.25rem;">
                @if ($adaPenyesuaian)
                    <div style="background: #fffdf5; border-left: 4px solid #f59e0b; padding: 0.75rem 1rem; border-radius: 4px; margin-bottom: 0.5rem;">
                        <div style="font-size: 0.825rem; font-weight: 700; color: #b45309; margin-bottom: 0.35rem;">
                            ⚠️ Perbedaan / Penyesuaian Nilai oleh Bagian Keuangan:
                        </div>
                        <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.8rem; color: #475569; line-height: 1.5;">
                            @if (abs($selisihDiskon) > 0.01)
                                <li>
                                    <strong>Diskon Faktur:</strong> 
                                    Di Invoice Keuangan dicatat <strong>Rp {{ number_format($apDiskonTotal, 0, ',', '.') }}</strong> 
                                    (Catatan awal GRN: Rp {{ number_format($grnDiskonTotal, 0, ',', '.') }} &bull; 
                                    <span style="color: {{ $selisihDiskon > 0 ? '#16a34a' : '#dc2626' }}; font-weight: 700;">
                                        {{ $selisihDiskon > 0 ? '+ Tambahan Diskon Rp ' . number_format($selisihDiskon, 0, ',', '.') : '- Pengurangan Diskon Rp ' . number_format(abs($selisihDiskon), 0, ',', '.') }}
                                    </span>)
                                </li>
                            @endif
                            @if (abs($selisihPotongan) > 0.01)
                                <li>
                                    <strong>Potongan Mutu:</strong> 
                                    Di Invoice Keuangan dicatat <strong>Rp {{ number_format($apPotonganTotal, 0, ',', '.') }}</strong> 
                                    (Catatan awal GRN: Rp {{ number_format($grnPotonganTotal, 0, ',', '.') }} &bull; 
                                    <span style="color: {{ $selisihPotongan > 0 ? '#dc2626' : '#16a34a' }}; font-weight: 700;">
                                        {{ $selisihPotongan > 0 ? '+ Penyesuaian Potongan Rp ' . number_format($selisihPotongan, 0, ',', '.') : '- Pengurangan Potongan Rp ' . number_format(abs($selisihPotongan), 0, ',', '.') }}
                                    </span>)
                                </li>
                            @endif
                        </ul>
                    </div>
                @else
                    <div style="font-size: 0.8rem; color: #166534; display: flex; align-items: center; gap: 0.4rem;">
                        <span style="font-weight: 800;">✓</span>
                        <span>Nilai kuantitas, harga satuan, dan diskon pada invoice Keuangan <strong>100% klop (cocok)</strong> dengan data fisik penerimaan gudang.</span>
                    </div>
                @endif

                @if ($hutangHdr->catatan)
                    <div style="font-size: 0.775rem; color: #64748b; margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed #e2e8f0;">
                        💬 <strong>Catatan dari Bagian Keuangan:</strong> <em>"{{ $hutangHdr->catatan }}"</em>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    {{-- KARTU INFO PENGIRIM & GUDANG --}}
    <div class="card">
        <div class="card-header" style="background: #f8fafc;">
            <strong style="color: #0f172a;">Informasi Dokumen & Pengirim</strong>
        </div>
        <div style="padding: 1.25rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
                <span style="color: #64748b; font-size: 0.8rem; display: block;">Supplier Pengirim:</span>
                <strong style="font-size: 1rem; color: #0f172a;">{{ $terima->supplier?->supplier_nm ?? '-' }}</strong>
                <p style="color: #64748b; font-size: 0.85rem; margin-top: 0.25rem;">
                    {{ $terima->supplier?->alamat_txt ?? '-' }}<br>
                    Kontak: {{ $terima->supplier?->kontak_no ?? '-' }}
                </p>
            </div>
            <div>
                <span style="color: #64748b; font-size: 0.8rem; display: block;">Gudang Penyimpanan Fisik:</span>
                <strong style="font-size: 1rem; color: #0f172a;">{{ $terima->gudang?->gudang_nm ?? '-' }}</strong>
                <p style="color: #64748b; font-size: 0.85rem; margin-top: 0.25rem;">
                    Kode Gudang: {{ $terima->gudang?->gudang_cd ?? '-' }}<br>
                    Lokasi: {{ $terima->gudang?->alamat_txt ?? '-' }}
                </p>
            </div>
            @if ($terima->catatan_txt)
                <div style="grid-column: 1 / -1; padding-top: 0.75rem; border-top: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 0.8rem; display: block;">Catatan Fisik Penerimaan:</span>
                    <p style="color: #334155; font-size: 0.875rem; margin-top: 0.25rem;">{{ $terima->catatan_txt }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- KARTU INFO REFERENSI --}}
    <div class="card">
        <div class="card-header" style="background: #f8fafc;">
            <strong style="color: #0f172a;">Referensi Dokumen</strong>
        </div>
        <div style="padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                <span style="color: #64748b; font-size: 0.875rem;">Tanggal Terima:</span>
                <strong style="color: #0f172a;">{{ \Carbon\Carbon::parse($terima->terima_tgl)->format('d F Y') }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; padding-top: 0.75rem; border-top: 1px solid #f1f5f9;">
                <span style="color: #64748b; font-size: 0.875rem;">Referensi PO:</span>
                @if ($terima->po)
                    <a href="{{ route('gudang.po.show', $terima->po_id) }}" style="font-weight: 700; color: #0284c7; text-decoration: none;">
                        {{ $terima->po->po_no }} &rarr;
                    </a>
                @else
                    <span style="color: #94a3b8; font-style: italic;">Non-PO</span>
                @endif
            </div>
            @if ($terima->qc_id)
                <div style="display: flex; justify-content: space-between; padding-top: 0.75rem; border-top: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-size: 0.875rem;">Tiket QC Inbound:</span>
                    <a href="{{ route('qc.inbound.show', $terima->qc_id) }}" style="font-weight: 700; color: #7e22ce; text-decoration: none;">
                        {{ $terima->qc?->qc_no ?? ('QC #' . $terima->qc_id) }} &rarr;
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- TABEL BARANG DITERIMA DENGAN NOMOR BATCH & NILAI --}}
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
        <strong style="color: #0f172a;">Rincian Barang, Alokasi Nomor Batch, Harga & Nilai Masuk</strong>
        <span style="font-size: 0.8rem; color: #64748b;">{{ $terima->details->count() }} Jenis Komoditas</span>
    </div>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">No</th>
                    <th>Nama Barang / Komoditas</th>
                    <th>Nomor Batch / Lot</th>
                    <th style="text-align: center;">Expired</th>
                    <th style="text-align: center;">Grade</th>
                    <th style="text-align: right;">Qty Netto (Masuk)</th>
                    <th style="text-align: center;">Satuan</th>
                    <th style="text-align: right;">@Harga Satuan</th>
                    <th style="text-align: center;" class="no-print">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($terima->details as $index => $item)
                    @php
                        $hargaAwal = (float) ($item->harga_nominal ?? 0);
                        $itemNetto = max(0, (float) $item->terima_qty - (float) ($item->reject_qty ?? 0));
                    @endphp
                    <tr>
                        <td style="text-align: center; color: #64748b; font-weight: 600;">{{ $index + 1 }}</td>
                        <td>
                            <strong style="color: #0f172a;">{{ $item->barang?->barang_nm }}</strong>
                            <span style="display: block; font-size: 0.75rem; color: #64748b;">Kode: {{ $item->barang?->barang_cd }}</span>
                        </td>
                        <td>
                            <span style="font-family: monospace; font-size: 0.85rem; background: #ecfdf5; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 700; color: #065f46; border: 1px solid #a7f3d0;">
                                {{ $item->batch_no }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            @if ($item->expired_tgl)
                                <span style="color: #0f172a; font-weight: 600; font-size: 0.85rem;">{{ \Carbon\Carbon::parse($item->expired_tgl)->format('d/m/Y') }}</span>
                            @else
                                <span style="color: #94a3b8;">-</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 0.75rem;">
                                {{ $item->grade_cd ?? 'A' }}
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: 700; font-size: 0.95rem; color: #047857;">
                            + {{ number_format($itemNetto, 2, ',', '.') }}
                            @if ((float) $item->reject_qty > 0)
                                <div style="font-size: 0.725rem; color: #dc2626; font-weight: 700; margin-top: 2px;">
                                    Afkir: {{ number_format((float) $item->reject_qty, 2, ',', '.') }} (Bruto: {{ number_format((float) $item->terima_qty, 2, ',', '.') }})
                                </div>
                            @endif
                        </td>
                        <td style="text-align: center; color: #475569; font-size: 0.85rem;">{{ $item->barang?->satuanDasar?->satuan_nm ?? '-' }}</td>
                        <td style="text-align: right; font-family: monospace; font-size: 0.875rem; font-weight: 700; color: #0f172a;">
                            Rp {{ number_format($hargaAwal, 0, ',', '.') }}
                            <small style="color: #64748b; font-size: 0.725rem; font-weight: normal; display: block;">/ {{ $item->barang?->satuanDasar?->satuan_nm ?? 'Satuan' }}</small>
                        </td>
                        <td style="text-align: center;" class="no-print">
                            <a href="{{ route('gudang.stok.ledger', ['barang_id' => $item->barang_id, 'gudang_id' => $terima->gudang_id]) }}" class="btn btn-secondary btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" title="Lihat Kartu Stok Barang Ini">
                                Kartu Stok &rarr;
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- SUMMARY REKAPITULASI FISIK GUDANG --}}
    @php
        $sumBruto = (float) $terima->details->sum('terima_qty');
        $sumAfkir = (float) $terima->details->sum('reject_qty');
        $sumNetto = max(0, $sumBruto - $sumAfkir);
    @endphp

    <div style="border-top: 2px solid #e2e8f0; background: #f8fafc; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div style="color: #64748b; font-size: 0.85rem;">
            Catatan: Perhitungan faktur tagihan hutang, PPN, dan rekonsiliasi pembayaran dikelola oleh modul <strong>Keuangan &amp; Akuntansi</strong>.
        </div>
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.5rem 1rem; text-align: right;">
                <span style="font-size: 0.725rem; color: #64748b; text-transform: uppercase; font-weight: 700; display: block;">Total Qty Bruto</span>
                <strong style="color: #0f172a; font-size: 1rem; font-family: monospace;">{{ number_format($sumBruto, 2, ',', '.') }}</strong>
            </div>
            @if ($sumAfkir > 0)
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 0.5rem 1rem; text-align: right;">
                    <span style="font-size: 0.725rem; color: #dc2626; text-transform: uppercase; font-weight: 700; display: block;">Total Afkir / Reject</span>
                    <strong style="color: #b91c1c; font-size: 1rem; font-family: monospace;">- {{ number_format($sumAfkir, 2, ',', '.') }}</strong>
                </div>
            @endif
            <div style="background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 6px; padding: 0.5rem 1.25rem; text-align: right;">
                <span style="font-size: 0.725rem; color: #047857; text-transform: uppercase; font-weight: 800; display: block;">Total Netto Masuk Gudang</span>
                <strong style="color: #047857; font-size: 1.2rem; font-family: monospace;">+ {{ number_format($sumNetto, 2, ',', '.') }}</strong>
            </div>
        </div>
    </div>
</div>

{{-- TANDA TANGAN SLIP TIMBANG & PENERIMAAN (GRN) --}}
<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-top: 2rem; text-align: center; font-size: 0.875rem;">
    <div style="border: 1px dashed #cbd5e1; border-radius: 8px; padding: 1rem; background: #ffffff;">
        <span style="color: #64748b; font-size: 0.8rem; display: block;">Diserahkan Oleh:</span>
        <strong style="color: #0f172a; display: block; margin-top: 0.2rem;">Sopir / Petani Pemasok</strong>
        <div style="height: 55px;"></div>
        <div style="border-bottom: 1px solid #94a3b8; width: 80%; margin: 0 auto;"></div>
        <span style="color: #64748b; font-size: 0.75rem; margin-top: 0.25rem; display: block;">Nama & Tanggal</span>
    </div>

    <div style="border: 1px dashed #cbd5e1; border-radius: 8px; padding: 1rem; background: #ffffff;">
        <span style="color: #64748b; font-size: 0.8rem; display: block;">Diterima & Ditimbang Oleh:</span>
        <strong style="color: #0f172a; display: block; margin-top: 0.2rem;">Petugas Timbang Gudang</strong>
        <div style="height: 55px;"></div>
        <div style="border-bottom: 1px solid #94a3b8; width: 80%; margin: 0 auto;"></div>
        <span style="color: #64748b; font-size: 0.75rem; margin-top: 0.25rem; display: block;">Nama & Tanggal</span>
    </div>

    <div style="border: 1px dashed #cbd5e1; border-radius: 8px; padding: 1rem; background: #ffffff;">
        <span style="color: #64748b; font-size: 0.8rem; display: block;">Diperiksa & Disetujui:</span>
        <strong style="color: #0f172a; display: block; margin-top: 0.2rem;">QC / Kepala Gudang</strong>
        <div style="height: 55px;"></div>
        <div style="border-bottom: 1px solid #94a3b8; width: 80%; margin: 0 auto;"></div>
        <span style="color: #64748b; font-size: 0.75rem; margin-top: 0.25rem; display: block;">Nama & Tanda Tangan</span>
    </div>
</div>

@include('gudang.terima.partials.modal-delete-confirm')
@include('gudang.qc.partials.common.modal-preview-berita-acara')

@push('scripts')
<script src="{{ asset('js/gudang/terima/terima-show.js') }}"></script>
@endpush
@endsection
