@extends('layouts.packaging-mobile')

@section('title', 'Riwayat Laporan Pengemasan Lapangan - PT Mirasa')

@section('content')
<div class="pkg-page-header">
    <div class="pkg-page-title-row">
        <div>
            <h1 class="pkg-page-title">
                <span class="pkg-page-title-icon">📋</span>
                <span>Riwayat Laporan Kemas</span>
            </h1>
            <p class="pkg-page-subtitle">Tiket laporan lapangan yang telah terkirim</p>
        </div>
        <a href="{{ route('produksi.packaging.mobile') }}" class="pkg-btn-submit" style="width: auto; height: 38px; padding: 0 0.85rem; font-size: 0.8rem;">
            <span>+ Laporan Baru</span>
        </a>
    </div>
</div>

{{-- Filter Tanggal Sederhana --}}
<form method="GET" action="{{ route('produksi.packaging.mobile.index') }}" class="pkg-filter-bar">
    <input type="date" name="tgl_kemas" class="pkg-input" value="{{ $filters['tgl_kemas'] ?? date('Y-m-d') }}" style="height: 42px; font-size: 0.85rem;" onchange="this.form.submit()">
    <button type="submit" class="pkg-btn-history" style="height: 42px;">Filter</button>
</form>

{{-- Daftar Kartu Laporan --}}
<div style="display: flex; flex-direction: column; gap: 0.75rem;">
    @forelse ($laporanList as $lap)
        @php $badge = $lap->status_badge; @endphp
        <a href="{{ route('produksi.packaging.mobile.show', $lap->laporan_id) }}" class="pkg-laporan-card">
            <div class="pkg-laporan-card-header">
                <div>
                    <div class="pkg-laporan-card-no">
                        {{ $lap->laporan_no }}
                    </div>
                    <div class="pkg-laporan-card-meta">
                        📅 {{ $lap->tgl_kemas->format('d/m/Y') }} &bull; Shift {{ $lap->shift }} &bull; {{ $lap->operator_nama }}
                    </div>
                </div>
                <span style="font-size: 0.7rem; font-weight: 800; padding: 3px 8px; border-radius: 999px; background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; border: 1px solid {{ $badge['border'] }};">
                    {{ $badge['icon'] }} {{ $badge['label'] }}
                </span>
            </div>

            <div class="pkg-laporan-card-body">
                <span class="pkg-laporan-card-prod">
                    {{ $lap->barangJadi->barang_nm ?? '-' }}
                </span>
                <span class="pkg-laporan-card-qty">
                    {{ number_format($lap->hasil_karton_qty, 0, ',', '.') }} {{ $lap->satuan_hasil }}
                </span>
            </div>
        </a>
    @empty
        <div class="pkg-empty-state">
            <div class="pkg-empty-icon">📦</div>
            <strong class="pkg-empty-title">Belum Ada Laporan Pengemasan</strong>
            <p style="font-size: 0.8rem; margin: 0.25rem 0 1rem 0;">Belum ada laporan pengemasan yang terkirim pada tanggal ini.</p>
            <a href="{{ route('produksi.packaging.mobile') }}" class="pkg-btn-submit" style="display: inline-flex; width: auto; height: 42px; padding: 0 1.25rem; font-size: 0.875rem;">
                + Input Laporan Sekarang
            </a>
        </div>
    @endforelse
</div>

@if ($laporanList->hasPages())
    <div style="margin-top: 1.25rem;">
        {{ $laporanList->links() }}
    </div>
@endif
@endsection
