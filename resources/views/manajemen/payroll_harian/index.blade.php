@extends('layouts.app')

@section('title', 'Detail Payroll Harian - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/manajemen/fingerprint/fingerprint-index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/manajemen/payroll_harian/payroll-harian.css') }}">
@endpush

@section('content')
@php
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $namaPeriode = \Carbon\Carbon::createFromFormat('!Y-m', $periode)->locale('id')->translatedFormat('F Y');
    $labelStatus = ['draft' => 'Draft', 'final' => 'Final', 'dibayar' => 'Dibayar'];

    // satu sel: jumlah orang + biaya. Bila ada $tgl, sel menjadi tautan ke rincian.
    $sel = function (array $s, ?string $tgl = null, ?string $kel = null) use ($rp) {
        $isi = '<span class="ph-orang">' . number_format($s['orang']) . ' orang masuk</span><span class="ph-rp">' . $rp($s['biaya']) . '</span>';
        if ($s['orang'] === 0) {
            return '<span class="ph-kosong">' . $isi . '</span>';
        }
        if ($tgl === null) {
            return $isi;
        }
        $url = route('manajemen.payroll_harian.rincian', ['tanggal' => $tgl, 'kelompok' => $kel]);

        return '<a class="ph-link" href="' . e($url) . '">' . $isi . '</a>';
    };
@endphp

<div class="fp-head">
    <div>
        <h1 class="fp-title">Detail Payroll Harian</h1>
        <p class="fp-sub">Biaya gaji per tanggal dan kelompok kerja. Selalu dihitung dari absensi terbaru.</p>
    </div>
    <div class="ph-tools no-print">
        <form method="GET" class="ph-tools">
            <label class="fp-label" style="margin:0">Periode:</label>
            <input type="month" name="periode" value="{{ $periode }}" class="fp-input" onchange="this.form.submit()">
        </form>
        <button type="button" class="fp-btn fp-btn-soft" onclick="window.print()">Cetak Laporan</button>
        <a href="{{ route('manajemen.payroll_harian.export', ['periode' => $periode]) }}" class="fp-btn fp-btn-primary">Export Excel</a>
    </div>
</div>

@if ($selisih !== null && abs($selisih) > 100)
    <div class="fp-alert fp-alert-warn">
        <b>Payroll {{ $namaPeriode }} sudah {{ $labelStatus[$status] }}, tetapi absensi berubah sesudahnya.</b>
        Biaya harian terkini {{ $selisih > 0 ? 'lebih besar' : 'lebih kecil' }} {{ $rp(abs($selisih)) }} dari angka final Payroll
        (gaji pokok, lembur, dan tunjangan masa kerja). Halaman ini mengikuti absensi terbaru, sedangkan Payroll tetap memakai angka final.
        Buka kunci Payroll dan finalkan ulang bila perubahan absensi itu memang benar.
    </div>
@endif

<div class="fp-stats">
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#0284c7">Total Biaya Gaji Harian</div>
        <div class="fp-stat-value" style="font-size:24px">{{ $rp($ringkasan['biaya']) }}</div>
        <div class="fp-stat-note">{{ $namaPeriode }} &middot; Payroll: {{ $labelStatus[$status] }}</div>
    </div>
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#0d9488">Kelompok Langsung</div>
        <div class="fp-stat-value" style="font-size:24px">{{ $rp($ringkasan['langsung']) }}</div>
        <div class="fp-stat-note">IFM, Manual, Packing</div>
    </div>
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#7c3aed">Tidak Langsung (Overhead)</div>
        <div class="fp-stat-value" style="font-size:24px">{{ $rp($ringkasan['tidak']) }}</div>
        <div class="fp-stat-note">{{ number_format($ringkasan['orang_hari']) }} orang-hari &middot; {{ $ringkasan['hari_kerja'] }} hari ada kehadiran</div>
    </div>
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#d97706">Belum Dihitung</div>
        <div class="fp-stat-value" style="font-size:24px">{{ number_format($ringkasan['perlu']) }}</div>
        <div class="fp-stat-note">Absensi Perlu dicek / Tanpa jadwal</div>
    </div>
</div>

<div class="fp-card">
    <div class="fp-table-wrap">
        <table class="fp-table ph-table">
            <thead>
                <tr>
                    <th>Tanggal Operasional</th>
                    <th class="ph-h-ifm">Langsung - IFM</th>
                    <th class="ph-h-manual">Langsung - Manual</th>
                    <th class="ph-h-packing">Langsung - Packing</th>
                    <th class="ph-h-total">Total Kelompok Langsung</th>
                    <th class="ph-h-tidak">Tidak Langsung (Overhead)</th>
                    <th>Total Semua</th>
                    <th class="no-print">Perlu Dicek</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($hari as $h)
                    @php $d = \Carbon\Carbon::parse($h['tanggal'])->locale('id'); @endphp
                    <tr class="{{ $d->isSunday() ? 'ph-minggu' : '' }}">
                        <td>
                            <b>{{ $d->format('d-m-Y') }}</b>
                            <div class="ph-sub">{{ $d->translatedFormat('l') }}</div>
                        </td>
                        @foreach (['Langsung IFM', 'Langsung Manual', 'Langsung Packing'] as $k)
                            <td>{!! $sel($h['kel'][$k], $h['tanggal'], $k) !!}</td>
                        @endforeach
                        <td class="ph-total-col">{!! $sel($h['langsung'], $h['tanggal'], 'langsung') !!}</td>
                        <td>{!! $sel($h['tidak'], $h['tanggal'], 'Tidak Langsung') !!}</td>
                        <td class="ph-total-col">{!! $sel($h['total'], $h['tanggal']) !!}</td>
                        <td class="no-print">
                            @if ($h['perlu'] > 0)
                                <a class="fp-badge fp-badge-warn" style="text-decoration:none"
                                   href="{{ route('manajemen.absensi.index', ['dari' => $h['tanggal'], 'sampai' => $h['tanggal'], 'status' => 'perlu']) }}">
                                    {{ $h['perlu'] }} perlu dicek &rarr;
                                </a>
                            @else
                                <span class="fp-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="fp-empty">Belum ada data absensi bergaji pada {{ $namaPeriode }}.</td>
                    </tr>
                @endforelse
                @if (count($hari) > 0)
                    <tr class="ph-total-row">
                        <td>TOTAL {{ strtoupper($namaPeriode) }}</td>
                        @foreach (['Langsung IFM', 'Langsung Manual', 'Langsung Packing'] as $k)
                            <td>{!! $sel($total['kel'][$k]) !!}</td>
                        @endforeach
                        <td>{!! $sel($total['langsung']) !!}</td>
                        <td>{!! $sel($total['tidak']) !!}</td>
                        <td>{!! $sel($total['total']) !!}</td>
                        <td class="no-print">{{ $ringkasan['perlu'] ?: '-' }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<div class="fp-info">
    <b>Cara membaca:</b> biaya = gaji harian &times; kehadiran + tunjangan masa kerja + lembur (Lembur I &times;1,5, Lembur II &times;2).
    Tunjangan jabatan, insentif, BPJS, dan kasbon bersifat bulanan sehingga tidak ikut. Karyawan yang gaji hariannya belum diisi di Payroll tidak ditampilkan.
    Absensi berstatus <i>Perlu dicek</i> atau <i>Tanpa jadwal</i> belum dijumlahkan, perbaiki dulu lewat kolom Perlu Dicek.
    Klik sel untuk melihat rinciannya. Selisih beberapa rupiah dengan Payroll bulanan berasal dari pembulatan.
</div>
@endsection