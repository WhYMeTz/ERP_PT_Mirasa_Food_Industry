@extends('layouts.packaging-mobile')

@section('title', 'Tiket Laporan - ' . $laporan->laporan_no)

@section('content')
<div style="margin-bottom: 1.25rem;">
    <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        
        {{-- Status Header --}}
        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px dashed #e2e8f0; padding-bottom: 1rem; margin-bottom: 1rem;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Laporan Pengemasan</span>
                <div style="font-size: 1.15rem; font-weight: 800; color: #0f172a; font-family: ui-monospace, SFMono-Regular, monospace; margin-top: 2px;">
                    {{ $laporan->laporan_no }}
                </div>
                <div style="font-size: 0.775rem; color: #64748b; margin-top: 2px;">
                    📅 {{ $laporan->tgl_kemas->format('d/m/Y') }} &bull; Shift {{ $laporan->shift }}
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
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; text-align: center;">
            <span style="font-size: 0.75rem; font-weight: 700; color: #047857; text-transform: uppercase;">Hasil Barang Jadi (FG)</span>
            <div style="font-size: 1.05rem; font-weight: 800; color: #065f46; margin: 4px 0;">
                {{ $laporan->barangJadi->barang_nm ?? '-' }}
            </div>
            <div style="font-size: 1.65rem; font-weight: 900; color: #059669; font-family: ui-monospace, SFMono-Regular, monospace;">
                {{ number_format($laporan->hasil_karton_qty, 0, ',', '.') }} {{ $laporan->satuan_hasil }}
                @if ($laporan->hasil_pack_qty > 0)
                    <span style="font-size: 1rem; color: #047857;">+ {{ number_format($laporan->hasil_pack_qty, 0, ',', '.') }} Pack</span>
                @endif
            </div>
        </div>

        {{-- Rincian Data Lapangan --}}
        <div style="font-size: 0.825rem; color: #334155;">
            <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f1f5f9;">
                <span style="color: #64748b;">Leader / Operator:</span>
                <strong style="color: #0f172a;">{{ $laporan->operator_nama }} ({{ $laporan->jumlah_personil }} Org &bull; {{ $laporan->jumlah_mesin }} Mesin)</strong>
            </div>

            @if ($laporan->wip_qty_kg > 0)
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f1f5f9;">
                    <span style="color: #64748b;">Curah WIP Dikemas:</span>
                    <strong style="color: #0f172a;">
                        @if ($laporan->produksi) [{{ $laporan->produksi->produksi_no }}] @endif
                        {{ number_format($laporan->wip_qty_kg, 2, ',', '.') }} {{ $laporan->wip_satuan ?: 'Kg' }}
                    </strong>
                </div>
            @endif

            @if ($laporan->bumbuBarang)
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f1f5f9;">
                    <span style="color: #64748b;">Bumbu Tabur:</span>
                    <strong style="color: #0f172a;">{{ $laporan->bumbuBarang->barang_nm }} ({{ number_format($laporan->bumbu_qty_kg, 1, ',', '.') }} Kg)</strong>
                </div>
            @endif

            @if ($laporan->plastikBarang)
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f1f5f9;">
                    <span style="color: #64748b;">Plastik Kemas:</span>
                    <strong style="color: #0f172a;">{{ $laporan->plastikBarang->barang_nm }} ({{ number_format($laporan->plastik_qty, 1, ',', '.') }})</strong>
                </div>
            @endif

            @if ($laporan->plastik_rusak_pcs > 0)
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f1f5f9; color: #dc2626;">
                    <span>Plastik Rusak (Afkir):</span>
                    <strong>{{ number_format($laporan->plastik_rusak_pcs, 0, ',', '.') }} Pcs</strong>
                </div>
            @endif

            @if ($laporan->kartonBarang)
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f1f5f9;">
                    <span style="color: #64748b;">Karton Dus:</span>
                    <strong style="color: #0f172a;">{{ $laporan->kartonBarang->barang_nm }} ({{ number_format($laporan->karton_qty, 0, ',', '.') }} Lbr)</strong>
                </div>
            @endif

            @if ($laporan->karton_rusak_lembar > 0)
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f1f5f9; color: #dc2626;">
                    <span>Karton Rusak (Afkir):</span>
                    <strong>{{ number_format($laporan->karton_rusak_lembar, 0, ',', '.') }} Lbr</strong>
                </div>
            @endif

            @if ($laporan->lakban_atas_gr > 0 || $laporan->lakban_bawah_gr > 0 || $laporan->lakban_qty > 0)
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f1f5f9;">
                    <span style="color: #64748b;">Lakban Dus:</span>
                    <strong style="color: #0f172a;">
                        @if ($laporan->lakban_atas_gr > 0 || $laporan->lakban_bawah_gr > 0)
                            Atas {{ number_format($laporan->lakban_atas_gr, 0, ',', '.') }}g | Bwh {{ number_format($laporan->lakban_bawah_gr, 0, ',', '.') }}g (Total {{ number_format($laporan->lakban_qty, 0, ',', '.') }}g)
                        @else
                            {{ number_format($laporan->lakban_qty, 0, ',', '.') }} Gram/Roll
                        @endif
                    </strong>
                </div>
            @endif

            @if ($laporan->catatan)
                <div style="margin-top: 0.75rem; background: #f8fafc; border-radius: 6px; padding: 0.65rem 0.75rem; font-size: 0.775rem; color: #64748b;">
                    <strong>Catatan:</strong> {{ $laporan->catatan }}
                </div>
            @endif
        </div>

        {{-- Action Buttons --}}
        <div style="display: flex; flex-direction: column; gap: 0.65rem; margin-top: 1.5rem;">
            <a href="{{ route('produksi.packaging.mobile') }}" class="pkg-btn-submit" style="text-decoration: none;">
                <span>+ Buat Laporan Kemas Baru</span>
            </a>
            <a href="{{ route('produksi.packaging.mobile.index') }}" class="pkg-btn-history" style="text-decoration: none; justify-content: center;">
                <span>📋 Lihat Riwayat Laporan Hari Ini</span>
            </a>
        </div>
    </div>
</div>
@endsection
