@extends('layouts.packaging-mobile')

@section('title', 'Riwayat Laporan Pengemasan Lapangan - PT Mirasa')

@section('content')
<div style="margin-bottom: 1.25rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <div>
            <h1 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0;">
                Riwayat Laporan Kemas
            </h1>
            <span style="font-size: 0.775rem; color: #64748b;">Tiket laporan lapangan yang terkirim</span>
        </div>
        <a href="{{ route('produksi.packaging.mobile') }}" class="pkg-btn-submit" style="height: 38px; padding: 0 0.85rem; font-size: 0.8rem; text-decoration: none;">
            <span>+ Laporan Baru</span>
        </a>
    </div>

    {{-- Filter Sederhana --}}
    <form method="GET" action="{{ route('produksi.packaging.mobile.index') }}" style="margin-bottom: 1rem; display: flex; gap: 0.5rem;">
        <input type="date" name="tgl_kemas" class="pkg-input" value="{{ $filters['tgl_kemas'] ?? date('Y-m-d') }}" style="height: 40px; font-size: 0.85rem;" onchange="this.form.submit()">
        <button type="submit" class="pkg-btn-history" style="height: 40px; padding: 0 1rem; font-size: 0.85rem;">Filter</button>
    </form>

    {{-- Daftar Kartu Laporan --}}
    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
        @forelse ($laporanList as $lap)
            @php $badge = $lap->status_badge; @endphp
            <a href="{{ route('produksi.packaging.mobile.show', $lap->laporan_id) }}" style="text-decoration: none; display: block; background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 1rem; transition: all 0.15s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.35rem;">
                    <div>
                        <strong style="color: #0f172a; font-size: 0.9rem; font-family: ui-monospace, SFMono-Regular, monospace;">
                            {{ $lap->laporan_no }}
                        </strong>
                        <div style="font-size: 0.725rem; color: #64748b; margin-top: 2px;">
                            📅 {{ $lap->tgl_kemas->format('d/m/Y') }} &bull; Shift {{ $lap->shift }} &bull; {{ $lap->operator_nama }}
                        </div>
                    </div>
                    <span style="font-size: 0.7rem; font-weight: 800; padding: 3px 8px; border-radius: 999px; background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; border: 1px solid {{ $badge['border'] }};">
                        {{ $badge['icon'] }} {{ $badge['label'] }}
                    </span>
                </div>

                <div style="background: #f8fafc; border-radius: 6px; padding: 0.5rem 0.75rem; margin-top: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.825rem; font-weight: 700; color: #1e293b;">
                        {{ $lap->barangJadi->barang_nm ?? '-' }}
                    </span>
                    <strong style="font-size: 0.95rem; font-weight: 900; color: #059669; font-family: ui-monospace, monospace;">
                        {{ number_format($lap->hasil_karton_qty, 0, ',', '.') }} {{ $lap->satuan_hasil }}
                    </strong>
                </div>
            </a>
        @empty
            <div style="text-align: center; padding: 3rem 1rem; background: #ffffff; border-radius: 10px; border: 1.5px dashed #cbd5e1; color: #64748b;">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📦</div>
                <strong style="color: #1e293b; display: block;">Belum Ada Laporan Pengemasan</strong>
                <p style="font-size: 0.8rem; margin: 0.25rem 0 0.85rem 0;">Belum ada laporan pengemasan yang terkirim pada tanggal ini.</p>
                <a href="{{ route('produksi.packaging.mobile') }}" class="pkg-btn-submit" style="display: inline-flex; height: 40px; padding: 0 1rem; font-size: 0.85rem; text-decoration: none;">
                    + Input Laporan Sekarang
                </a>
            </div>
        @endforelse
    </div>

    @if ($laporanList->hasPages())
        <div style="margin-top: 1rem;">
            {{ $laporanList->links() }}
        </div>
    @endif
</div>
@endsection
