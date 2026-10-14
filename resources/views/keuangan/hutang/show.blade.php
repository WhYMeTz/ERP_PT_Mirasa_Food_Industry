@extends('layouts.app')

@section('title', 'Detail Tagihan Hutang #' . $hutang->hutang_no)

@section('content')
<div class="content-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <a href="{{ route('keuangan.hutang.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                &larr; Riwayat Hutang
            </a>
            <span style="color: #cbd5e1;">/</span>
            <h1 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0; font-family: monospace;">
                {{ $hutang->hutang_no }}
            </h1>
            @if ($hutang->status_pembayaran === 'LUNAS')
                <span style="background: #f0fdf4; color: #166534; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 6px; border: 1px solid #bbf7d0;">
                    ✓ LUNAS
                </span>
            @elseif ($hutang->isOverdue())
                <span style="background: #fee2e2; color: #b91c1c; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 6px; border: 1px solid #fecaca;">
                    ⚠️ OVERDUE
                </span>
            @elseif ($hutang->status_pembayaran === 'SEBAGIAN')
                <span style="background: #fffbeb; color: #b45309; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 6px; border: 1px solid #fde68a;">
                    ⏳ PARSIAL
                </span>
            @else
                <span style="background: #fef2f2; color: #b91c1c; font-size: 0.75rem; font-weight: 800; padding: 2px 8px; border-radius: 6px; border: 1px solid #fecaca;">
                    🔴 BELUM DIBAYAR
                </span>
            @endif
        </div>
        <p style="font-size: 0.8125rem; color: #64748b; margin: 0.2rem 0 0 0;">
            Tagihan kepada {{ $hutang->supplier?->supplier_nm ?: 'Operasional Pabrik' }} &bull; Diterbitkan: {{ \Carbon\Carbon::parse($hutang->tgl_tagihan)->format('d F Y') }}
        </p>
    </div>

    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <button type="button" onclick="window.print()" class="btn btn-secondary" style="font-size: 0.8125rem; font-weight: 700;">
            🖨️ Cetak Faktur
        </button>

        @if ($hutang->sisa_hutang > 0 && Auth::user()->canCreateBayarHutang())
            <a href="{{ route('keuangan.bayar.create', ['hutang_id' => $hutang->hutang_id, 'supplier_id' => $hutang->supplier_id]) }}" class="btn btn-primary" style="font-size: 0.8125rem; font-weight: 700; background: #059669; border-color: #059669; box-shadow: 0 2px 4px rgba(5,150,105,0.3);">
                💳 Bayar Tagihan Ini (BKK)
            </a>
        @endif
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 320px; gap: 1.25rem; align-items: flex-start;">
    <div>
        {{-- KARTU 1: INFORMASI DETAIL INVOICE --}}
        <div class="card" style="padding: 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 1.25rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div>
                    <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Rekanan Supplier</span>
                    <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a; margin-top: 2px;">
                        {{ $hutang->supplier?->supplier_nm ?: 'Operasional Pabrik' }}
                    </div>
                    <div style="font-size: 0.75rem; color: #64748b;">
                        {{ $hutang->supplier?->alamat_txt ?: 'Supplier Pabrik PT Mirasa' }}
                    </div>
                </div>

                <div>
                    <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700;">No. Faktur Fisik Supplier</span>
                    <div style="font-weight: 800; font-size: 0.95rem; color: #0f172a; margin-top: 2px; font-family: monospace;">
                        {{ $hutang->no_faktur_supplier ?: '— (Tanpa No Faktur)' }}
                    </div>
                </div>

                <div>
                    <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Jatuh Tempo Pembayaran</span>
                    <div style="font-weight: 800; font-size: 0.95rem; color: {{ $hutang->isOverdue() ? '#dc2626' : '#0f172a' }}; margin-top: 2px;">
                        {{ \Carbon\Carbon::parse($hutang->tgl_jatuh_tempo)->format('d F Y') }}
                    </div>
                    @if ($hutang->isOverdue())
                        <div style="font-size: 0.72rem; color: #dc2626; font-weight: 700;">
                            ⚠️ Telat {{ now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($hutang->tgl_jatuh_tempo)->startOfDay()) }} hari dari batas tempo!
                        </div>
                    @endif
                </div>

                <div>
                    <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Tipe Dokumen</span>
                    <div style="font-weight: 800; font-size: 0.95rem; color: #0284c7; margin-top: 2px;">
                        {{ $hutang->tipe_hutang === 'GAS_UTILITAS' ? '🔥 Konsinyasi Gas Utilitas' : '🌾 Pembelian Bahan Baku (GRN)' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 2: TABEL RINCIAN ITEM BARANG --}}
        <div class="card" style="padding: 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 1.25rem;">
            <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0 0 0.85rem 0;">
                Rincian Barang yang Ditagihkan
            </h3>

            <div style="overflow-x: auto;">
                <table class="table-compact" style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-size: 0.75rem; color: #475569; text-transform: uppercase;">
                            <th style="padding: 0.75rem 0.85rem;">Deskripsi / Surat Jalan</th>
                            <th style="padding: 0.75rem 0.5rem; text-align: right;">Kuantitas</th>
                            <th style="padding: 0.75rem 0.5rem; text-align: center;">Satuan</th>
                            <th style="padding: 0.75rem 0.5rem; text-align: right;">Harga Satuan</th>
                            <th style="padding: 0.75rem 0.5rem; text-align: right;">Diskon</th>
                            <th style="padding: 0.75rem 0.5rem; text-align: right;">PPN</th>
                            <th style="padding: 0.75rem 0.85rem; text-align: right;">Netto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hutang->details as $dtl)
                            <tr style="border-bottom: 1px solid #f1f5f9; font-size: 0.8125rem;">
                                <td style="padding: 0.65rem 0.85rem;">
                                    <div style="font-weight: 700; color: #0f172a;">
                                        {{ $dtl->deskripsi }}
                                    </div>
                                    @if ($dtl->terima)
                                        <div style="font-size: 0.72rem; color: #64748b;">
                                            Ref GRN: <a href="{{ route('gudang.terima.show', $dtl->terima_id) }}" target="_blank" style="color: #0284c7; text-decoration: underline;">{{ $dtl->terima->terima_no }}</a> (SJ: {{ $dtl->terima->surat_jalan_no ?: '-' }})
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 0.65rem 0.5rem; text-align: right; font-weight: 700;">
                                    {{ number_format($dtl->qty, 2, ',', '.') }}
                                </td>
                                <td style="padding: 0.65rem 0.5rem; text-align: center; color: #64748b;">
                                    {{ $dtl->satuan }}
                                </td>
                                <td style="padding: 0.65rem 0.5rem; text-align: right;">
                                    Rp {{ number_format($dtl->harga_satuan, 0, ',', '.') }}
                                </td>
                                <td style="padding: 0.65rem 0.5rem; text-align: right; color: #16a34a;">
                                    {{ $dtl->diskon_nominal > 0 ? 'Rp ' . number_format($dtl->diskon_nominal, 0, ',', '.') : '-' }}
                                </td>
                                <td style="padding: 0.65rem 0.5rem; text-align: right; color: #0284c7;">
                                    {{ $dtl->ppn_nominal > 0 ? 'Rp ' . number_format($dtl->ppn_nominal, 0, ',', '.') : '-' }}
                                </td>
                                <td style="padding: 0.65rem 0.85rem; text-align: right; font-weight: 800; color: #0f172a;">
                                    Rp {{ number_format($dtl->total_netto, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- KARTU 3: RIWAYAT PEMBAYARAN KAS KELUAR (BKK) --}}
        <div class="card" style="padding: 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem;">
                <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0;">
                    Riwayat Pembayaran (Bukti Kas Keluar - BKK)
                </h3>
                <span style="font-size: 0.75rem; color: #64748b;">
                    Total Terbayar: <strong style="color: #16a34a;">Rp {{ number_format($hutang->total_dibayar, 0, ',', '.') }}</strong>
                </span>
            </div>

            @if ($hutang->bayarDetails->count() > 0)
                <table class="table-compact" style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-size: 0.75rem; color: #475569; text-transform: uppercase;">
                            <th style="padding: 0.65rem 0.75rem;">No. BKK &amp; Tgl</th>
                            <th style="padding: 0.65rem 0.75rem;">Sumber Dana</th>
                            <th style="padding: 0.65rem 0.75rem;">Metode &amp; Ref</th>
                            <th style="padding: 0.65rem 0.75rem; text-align: right;">Nominal Bayar</th>
                            <th style="padding: 0.65rem 0.75rem; text-align: right;">Sisa Setelahnya</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hutang->bayarDetails as $bdtl)
                            @php $bkk = $bdtl->header; @endphp
                            <tr style="border-bottom: 1px solid #f1f5f9; font-size: 0.8125rem;">
                                <td style="padding: 0.65rem 0.75rem;">
                                    <a href="{{ route('keuangan.bayar.show', $bkk->bayar_id) }}" style="font-weight: 800; color: #0284c7; text-decoration: none; font-family: monospace;">
                                        {{ $bkk->bayar_no }}
                                    </a>
                                    <div style="font-size: 0.7rem; color: #64748b;">
                                        {{ \Carbon\Carbon::parse($bkk->tgl_bayar)->format('d/m/Y') }}
                                    </div>
                                </td>
                                <td style="padding: 0.65rem 0.75rem;">
                                    <span style="font-weight: 700; color: #0f172a;">{{ $bkk->kasBank?->nama_akun }}</span>
                                </td>
                                <td style="padding: 0.65rem 0.75rem;">
                                    <span style="font-weight: 700;">{{ $bkk->metode_bayar }}</span>
                                    @if ($bkk->no_referensi_bank)
                                        <div style="font-size: 0.7rem; color: #64748b; font-family: monospace;">Ref: {{ $bkk->no_referensi_bank }}</div>
                                    @endif
                                </td>
                                <td style="padding: 0.65rem 0.75rem; text-align: right; font-weight: 800; color: #16a34a;">
                                    Rp {{ number_format($bdtl->nominal_bayar, 0, ',', '.') }}
                                </td>
                                <td style="padding: 0.65rem 0.75rem; text-align: right; font-weight: 600; color: #64748b;">
                                    Rp {{ number_format($bdtl->sisa_hutang_sesudah, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div style="padding: 2rem; text-align: center; color: #94a3b8; font-size: 0.85rem; background: #f8fafc; border-radius: 8px;">
                    Belum ada riwayat pembayaran untuk tagihan ini.
                </div>
            @endif
        </div>
    </div>

    {{-- SISI KANAN: STATUS KEUANGAN & SISA HUTANG --}}
    <div>
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); position: sticky; top: 80px;">
            <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0 0 1rem 0; padding-bottom: 0.5rem; border-bottom: 1px solid #f1f5f9;">
                Status Saldo Tagihan
            </h3>

            <div style="display: flex; justify-content: space-between; padding: 0.45rem 0; font-size: 0.825rem; color: #475569; border-bottom: 1px dashed #f1f5f9;">
                <span>Total Bruto</span>
                <strong>Rp {{ number_format($hutang->total_bruto, 0, ',', '.') }}</strong>
            </div>

            @if ($hutang->total_diskon > 0)
                <div style="display: flex; justify-content: space-between; padding: 0.45rem 0; font-size: 0.825rem; color: #16a34a; border-bottom: 1px dashed #f1f5f9;">
                    <span>Diskon</span>
                    <strong>- Rp {{ number_format($hutang->total_diskon, 0, ',', '.') }}</strong>
                </div>
            @endif

            @if ($hutang->total_potongan_retur > 0)
                <div style="display: flex; justify-content: space-between; padding: 0.45rem 0; font-size: 0.825rem; color: #dc2626; border-bottom: 1px dashed #f1f5f9;">
                    <span>Potongan Retur</span>
                    <strong>- Rp {{ number_format($hutang->total_potongan_retur, 0, ',', '.') }}</strong>
                </div>
            @endif

            @if ($hutang->total_ppn > 0)
                <div style="display: flex; justify-content: space-between; padding: 0.45rem 0; font-size: 0.825rem; color: #0284c7; border-bottom: 1px dashed #f1f5f9;">
                    <span>PPN</span>
                    <strong>+ Rp {{ number_format($hutang->total_ppn, 0, ',', '.') }}</strong>
                </div>
            @endif

            <div style="display: flex; justify-content: space-between; padding: 0.55rem 0; font-size: 0.9rem; font-weight: 800; color: #0f172a; border-bottom: 2px solid #e2e8f0;">
                <span>Total Tagihan Netto</span>
                <span>Rp {{ number_format($hutang->total_tagihan, 0, ',', '.') }}</span>
            </div>

            <div style="display: flex; justify-content: space-between; padding: 0.45rem 0; font-size: 0.85rem; color: #16a34a; font-weight: 700; border-bottom: 1px dashed #f1f5f9;">
                <span>Sudah Dibayar</span>
                <span>Rp {{ number_format($hutang->total_dibayar, 0, ',', '.') }}</span>
            </div>

            <div style="margin-top: 0.75rem; padding: 0.85rem; border-radius: 8px; background: {{ $hutang->sisa_hutang > 0 ? '#fef2f2' : '#f0fdf4' }}; border: 1.5px solid {{ $hutang->sisa_hutang > 0 ? '#fecaca' : '#bbf7d0' }}; text-align: center;">
                <span style="font-size: 0.75rem; font-weight: 800; color: {{ $hutang->sisa_hutang > 0 ? '#991b1b' : '#166534' }}; text-transform: uppercase;">
                    Sisa Kewajiban Hutang
                </span>
                <div style="font-size: 1.35rem; font-weight: 800; color: {{ $hutang->sisa_hutang > 0 ? '#dc2626' : '#15803d' }}; margin-top: 2px;">
                    Rp {{ number_format($hutang->sisa_hutang, 0, ',', '.') }}
                </div>
            </div>

            @if ($hutang->sisa_hutang > 0 && Auth::user()->canCreateBayarHutang())
                <div style="margin-top: 1rem;">
                    <a href="{{ route('keuangan.bayar.create', ['hutang_id' => $hutang->hutang_id, 'supplier_id' => $hutang->supplier_id]) }}" class="btn btn-primary" style="width: 100%; padding: 0.7rem; font-weight: 800; font-size: 0.875rem; background: #059669; border-color: #059669; text-align: center; border-radius: 8px;">
                        💳 Bayar Tagihan Sekarang
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
