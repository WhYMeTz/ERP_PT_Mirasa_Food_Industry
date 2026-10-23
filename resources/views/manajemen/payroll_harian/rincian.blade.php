@extends('layouts.app')

@section('title', 'Rincian Payroll Harian - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/manajemen/fingerprint/fingerprint-index.css') }}">
    <link rel="stylesheet" href="{{ asset('css/manajemen/payroll_harian/payroll-harian.css') }}">
@endpush

@section('content')
@php
    $rp = fn ($v) => number_format((float) $v, 0, ',', '.');
    $f2 = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',') ?: '0';
    $f4 = fn ($v) => rtrim(rtrim(number_format((float) $v, 4, ',', '.'), '0'), ',') ?: '0';
    $d  = \Carbon\Carbon::parse($tanggal)->locale('id');
    $pilihan = ['' => 'Semua', 'langsung' => 'Total Langsung', 'Langsung IFM' => 'IFM', 'Langsung Manual' => 'Manual',
                'Langsung Packing' => 'Packing', 'Tidak Langsung' => 'Tidak Langsung', 'Lainnya' => 'Lainnya'];
@endphp

<div class="fp-head">
    <div>
        <a href="{{ route('manajemen.payroll_harian.index', ['periode' => $periode]) }}" class="fp-muted" style="text-decoration:none; font-size:13px">&larr; Kembali ke Detail Payroll Harian</a>
        <h1 class="fp-title">Rincian {{ $d->translatedFormat('l, d F Y') }}</h1>
        <p class="fp-sub">{{ number_format($total['orang']) }} orang masuk &middot; biaya Rp {{ $rp($total['biaya']) }}</p>
    </div>
</div>

<div class="fp-card">
    <div class="fp-filter">
        @foreach ($pilihan as $nilai => $nama)
            <a class="fp-btn {{ ($kelompok ?? '') === (string) $nilai ? 'fp-btn-primary' : 'fp-btn-soft' }}"
               href="{{ route('manajemen.payroll_harian.rincian', ['tanggal' => $tanggal, 'kelompok' => $nilai ?: null]) }}">{{ $nama }}</a>
        @endforeach
    </div>
</div>

@if ($flag->isNotEmpty())
    <div class="fp-alert fp-alert-warn">
        <b>{{ $flag->count() }} absensi belum dihitung pada tanggal ini</b> (tidak ikut dijumlahkan sampai diperbaiki):
        <ul style="margin:6px 0 0 18px">
            @foreach ($flag as $f)
                <li>
                    {{ $f['nama'] }} ({{ $f['status'] }})@if ($f['catatan']): {{ $f['catatan'] }}@endif
                    &middot; <a href="{{ route('manajemen.absensi.index', ['q' => $f['id_absen'], 'dari' => $tanggal, 'sampai' => $tanggal, 'status' => 'perlu']) }}">buka di Absensi</a>
                </li>
            @endforeach
        </ul>
    </div>
@endif

<div class="fp-card">
    <div class="fp-table-wrap">
        <table class="fp-table fp-table-sm">
            <thead>
                <tr>
                    <th>No</th><th>Nama</th><th>ID Karyawan</th><th>Kelompok</th><th>Shift</th><th>Status</th>
                    <th class="ph-num">Kehadiran</th><th class="ph-num">Gaji Pokok</th><th class="ph-num">Tunj. Masa Kerja</th>
                    <th class="ph-num">Lembur I</th><th class="ph-num">Lembur II</th><th class="ph-num">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $r)
                    <tr>
                        <td class="fp-muted">{{ $loop->iteration }}</td>
                        <td style="font-weight:600">{{ $r['nama'] }}</td>
                        <td class="fp-mono">{{ $r['nik'] ?? $r['id_absen'] }}</td>
                        <td>{{ $r['kelompok_asli'] }}</td>
                        <td>@if ($r['kode_shift']) <span class="fp-badge fp-badge-info">{{ $r['kode_shift'] }}</span> @else - @endif</td>
                        <td><span class="fp-badge {{ $r['status'] === 'OK' ? 'fp-badge-ok' : 'fp-badge-err' }}">{{ $r['status'] }}</span></td>
                        <td class="ph-num">{{ $f4($r['k']) }}</td>
                        <td class="ph-num">{{ $rp($r['pokok']) }}</td>
                        <td class="ph-num">{{ $rp($r['tunj']) }}</td>
                        <td class="ph-num">{{ $rp($r['honor1']) }}<div class="ph-sub">{{ $f2($r['l1']) }} jam</div></td>
                        <td class="ph-num">{{ $rp($r['honor2']) }}<div class="ph-sub">{{ $f2($r['l2']) }} jam</div></td>
                        <td class="ph-num"><b>{{ $rp($r['total']) }}</b></td>
                    </tr>
                @empty
                    <tr><td colspan="12" class="fp-empty">Tidak ada karyawan yang terhitung pada tanggal dan kelompok ini.</td></tr>
                @endforelse
                @if ($rows->isNotEmpty())
                    <tr class="ph-total-row">
                        <td colspan="7">TOTAL</td>
                        <td class="ph-num">{{ $rp($rows->sum('pokok')) }}</td>
                        <td class="ph-num">{{ $rp($rows->sum('tunj')) }}</td>
                        <td class="ph-num">{{ $rp($rows->sum('honor1')) }}</td>
                        <td class="ph-num">{{ $rp($rows->sum('honor2')) }}</td>
                        <td class="ph-num">{{ $rp($total['biaya']) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection