@extends('layouts.packaging-mobile')

@section('title', 'Tiket Laporan - ' . $laporan->laporan_no)

@section('content')
<div class="pkg-card" style="margin-bottom: 1.25rem;">
    {{-- Status Header --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1.5px dashed var(--pkg-border); padding-bottom: 1rem; margin-bottom: 1rem;">
        <div>
            <span style="font-size: 0.72rem; font-weight: 800; color: var(--pkg-red-600); text-transform: uppercase; letter-spacing: 0.06em;">Tiket Laporan Pengemasan</span>
            <div style="font-size: 1.25rem; font-weight: 900; color: var(--pkg-slate-900); font-family: var(--pkg-font-mono); margin-top: 2px;">
                {{ $laporan->laporan_no }}
            </div>
            <div style="font-size: 0.775rem; color: var(--pkg-slate-500); margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                <span>📅 {{ $laporan->tgl_kemas->format('d/m/Y') }}</span>
                <span>&bull;</span>
                <span>Shift {{ $laporan->shift }}</span>
            </div>
        </div>

        <div>
            @php $badge = $laporan->status_badge; @endphp
            <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 800; background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; border: 1px solid {{ $badge['border'] }};">
                <span>{{ $badge['icon'] }}</span>
                <span>{{ $badge['label'] }}</span>
            </span>
        </div>
    </div>

    {{-- Ringkasan Output FG --}}
    <div class="pkg-summary-card">
        <span class="pkg-summary-label">Hasil Barang Jadi (FG)</span>
        <div class="pkg-summary-prod">
            {{ $laporan->barangJadi->barang_nm ?? '-' }}
        </div>
        <div class="pkg-summary-qty">
            {{ number_format($laporan->hasil_karton_qty, 0, ',', '.') }} {{ $laporan->satuan_hasil }}
            @if ($laporan->hasil_pack_qty > 0)
                <span style="font-size: 1rem; color: var(--pkg-red-700); font-weight: 700;">+ {{ number_format($laporan->hasil_pack_qty, 0, ',', '.') }} Pack</span>
            @endif
        </div>
    </div>

    {{-- Rincian Data Lapangan --}}
    <div style="font-size: 0.825rem; color: var(--pkg-slate-700);">
        <div style="display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid var(--pkg-slate-100);">
            <span style="color: var(--pkg-slate-500);">Leader / Operator:</span>
            <strong style="color: var(--pkg-slate-900);">{{ $laporan->operator_nama }} ({{ $laporan->jumlah_personil }} Org &bull; {{ $laporan->jumlah_mesin }} Mesin)</strong>
        </div>

        @if ($laporan->wip_qty_kg > 0)
            <div style="display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid var(--pkg-slate-100);">
                <span style="color: var(--pkg-slate-500);">Curah WIP Dikemas:</span>
                <div style="text-align: right;">
                    <strong style="color: var(--pkg-slate-900); display: block;">
                        {{ $laporan->wipBarang?->barang_nm ?? ($laporan->produksi ? 'Keripik Curah' : '-') }}
                    </strong>
                    <div style="font-size: 0.725rem; color: var(--pkg-slate-400); margin: 2px 0;">
                        @if ($laporan->produksi) [{{ $laporan->produksi->produksi_no }}] @endif
                        @if ($laporan->wip_batch_no) &bull; Batch: {{ $laporan->wip_batch_no }} @endif
                    </div>
                    <span style="font-weight: 800; color: var(--pkg-red-600); font-size: 0.875rem; font-family: var(--pkg-font-mono);">
                        {{ number_format($laporan->wip_qty_kg, 2, ',', '.') }} {{ $laporan->wip_satuan ?: 'Kg' }}
                    </span>
                </div>
            </div>
        @endif

        @if ($laporan->bumbuBarang)
            <div style="display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid var(--pkg-slate-100);">
                <span style="color: var(--pkg-slate-500);">Bumbu Tabur:</span>
                <strong style="color: var(--pkg-slate-900);">{{ $laporan->bumbuBarang->barang_nm }} ({{ number_format($laporan->bumbu_qty_kg, 1, ',', '.') }} Kg)</strong>
            </div>
        @endif

        @if ($laporan->plastikBarang)
            <div style="display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid var(--pkg-slate-100);">
                <span style="color: var(--pkg-slate-500);">Plastik Kemas:</span>
                <strong style="color: var(--pkg-slate-900);">{{ $laporan->plastikBarang->barang_nm }} ({{ number_format($laporan->plastik_qty, 1, ',', '.') }})</strong>
            </div>
        @endif

        @if ($laporan->plastik_rusak_pcs > 0)
            <div style="display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid var(--pkg-slate-100); color: #dc2626;">
                <span>Plastik Rusak (Afkir):</span>
                <strong style="font-family: var(--pkg-font-mono);">{{ number_format($laporan->plastik_rusak_pcs, 0, ',', '.') }} Pcs</strong>
            </div>
        @endif

        @if ($laporan->kartonBarang)
            <div style="display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid var(--pkg-slate-100);">
                <span style="color: var(--pkg-slate-500);">Karton Dus:</span>
                <strong style="color: var(--pkg-slate-900);">{{ $laporan->kartonBarang->barang_nm }} ({{ number_format($laporan->karton_qty, 0, ',', '.') }} Lbr)</strong>
            </div>
        @endif

        @if ($laporan->karton_rusak_lembar > 0)
            <div style="display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid var(--pkg-slate-100); color: #dc2626;">
                <span>Karton Rusak (Afkir):</span>
                <strong style="font-family: var(--pkg-font-mono);">{{ number_format($laporan->karton_rusak_lembar, 0, ',', '.') }} Lbr</strong>
            </div>
        @endif

        @if ($laporan->lakban_atas_gr > 0 || $laporan->lakban_bawah_gr > 0 || $laporan->lakban_qty > 0 || $laporan->lakbanBarang)
            <div style="display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid var(--pkg-slate-100);">
                <span style="color: var(--pkg-slate-500);">Lakban Dus:</span>
                <div style="text-align: right;">
                    <strong style="color: var(--pkg-slate-900); display: block;">
                        {{ $laporan->lakbanBarang?->barang_nm ?? 'Lakban' }}
                    </strong>
                    @if ($laporan->lakban_atas_gr > 0 || $laporan->lakban_bawah_gr > 0)
                        <div style="font-size: 0.725rem; color: var(--pkg-slate-400); margin: 2px 0;">
                            Atas {{ number_format($laporan->lakban_atas_gr, 0, ',', '.') }}g &bull; Bwh {{ number_format($laporan->lakban_bawah_gr, 0, ',', '.') }}g
                        </div>
                        <span style="font-weight: 800; color: var(--pkg-red-600); font-size: 0.85rem; font-family: var(--pkg-font-mono);">
                            Total: {{ number_format($laporan->lakban_qty, 0, ',', '.') }} Gram
                        </span>
                    @elseif ($laporan->lakban_qty > 0)
                        <span style="font-weight: 800; color: var(--pkg-red-600); font-size: 0.85rem; font-family: var(--pkg-font-mono);">
                            {{ number_format($laporan->lakban_qty, 1, ',', '.') }} {{ $laporan->lakbanBarang?->satuanDasar?->satuan_nm ?? 'Gram/Roll' }}
                        </span>
                    @endif
                </div>
            </div>
        @endif

        @if ($laporan->expired_qty > 0 || $laporan->expiredBarang)
            <div style="display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid var(--pkg-slate-100);">
                <span style="color: var(--pkg-slate-500);">Kertas / Plastik Expired:</span>
                <strong style="color: var(--pkg-slate-900);">
                    {{ $laporan->expiredBarang?->barang_nm ?? 'Label Expired' }} ({{ number_format($laporan->expired_qty, 0, ',', '.') }} {{ $laporan->expiredBarang?->satuanDasar?->satuan_nm ?? 'Lbr/Pcs' }})
                </strong>
            </div>
        @endif

        @if (!empty($laporan->kemasan_tambahan_items) && count($laporan->kemasan_tambahan_items) > 0)
            <div style="padding: 0.6rem 0; border-bottom: 1px solid var(--pkg-slate-100);">
                <span style="color: var(--pkg-slate-500); display: block; margin-bottom: 4px;">Kemasan Tambahan:</span>
                <div style="display: flex; flex-direction: column; gap: 4px; padding-left: 0.5rem;">
                    @foreach ($laporan->kemasan_tambahan_items as $item)
                        <div style="display: flex; justify-content: space-between; font-size: 0.8rem;">
                            <span style="color: var(--pkg-slate-800);">&bull; {{ $item['barang_nm'] ?? 'Kemasan Tambahan' }}</span>
                            <strong style="color: var(--pkg-slate-900); font-family: var(--pkg-font-mono);">
                                {{ number_format($item['qty'] ?? 0, 1, ',', '.') }} {{ $item['satuan_nm'] ?? 'Pcs' }}
                            </strong>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif ($laporan->kemasan_lain_qty > 0 || $laporan->kemasanLainBarang)
            <div style="display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid var(--pkg-slate-100);">
                <span style="color: var(--pkg-slate-500);">Kemasan Tambahan:</span>
                <strong style="color: var(--pkg-slate-900);">
                    @if ($laporan->kemasanLainBarang)
                        {{ $laporan->kemasanLainBarang->barang_nm }} ({{ number_format($laporan->kemasan_lain_qty, 1, ',', '.') }} {{ $laporan->kemasanLainBarang->satuanDasar?->satuan_nm ?? 'Pcs' }})
                    @else
                        {{ number_format($laporan->kemasan_lain_qty, 1, ',', '.') }} Pcs
                    @endif
                </strong>
            </div>
        @endif

        @if ($laporan->catatan)
            <div style="margin-top: 0.85rem; background: var(--pkg-slate-50); border: 1px solid var(--pkg-slate-200); border-radius: var(--pkg-radius-sm); padding: 0.75rem; font-size: 0.775rem; color: var(--pkg-slate-600);">
                <strong style="color: var(--pkg-slate-800);">Catatan:</strong> {{ $laporan->catatan }}
            </div>
        @endif
    </div>

    {{-- Action Buttons --}}
    <div style="display: flex; flex-direction: column; gap: 0.65rem; margin-top: 1.5rem;">
        <a href="{{ route('produksi.packaging.mobile') }}" class="pkg-btn-submit" style="text-decoration: none;">
            <span>+ Buat Laporan Kemas Baru</span>
        </a>
        <a href="{{ route('produksi.packaging.mobile.index') }}" class="pkg-btn-history" style="text-decoration: none; justify-content: center;">
            <span>📋 Lihat Riwayat Laporan</span>
        </a>
    </div>
</div>
@endsection
