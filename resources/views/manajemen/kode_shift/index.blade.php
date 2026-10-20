@extends('layouts.app')

@section('title', 'Kode Shift - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/manajemen/fingerprint/fingerprint-index.css') }}">
@endpush

@section('content')
<div class="fp-head">
    <div>
        <h1 class="fp-title">Kode Shift</h1>
        <p class="fp-sub">Aturan jam kerja tiap kode shift. Dipakai untuk menghitung potongan, lembur, dan kehadiran.</p>
    </div>
</div>

<div class="fp-card">
    <div class="fp-table-wrap">
        <table class="fp-table">
            <thead>
                <tr>
                    <th>Kode</th><th>Hari</th><th>Masuk</th><th>Pulang</th>
                    <th style="text-align:center">Istirahat (jam)</th>
                    <th style="text-align:center">Standar Jam Kerja</th>
                    <th>Jenis</th><th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($shifts as $s)
                    <tr>
                        <td><span class="fp-badge fp-badge-info">{{ $s->kode }}</span></td>
                        <td>{{ $s->hari }}</td>
                        <td class="fp-mono">{{ $s->jam_masuk ? substr($s->jam_masuk, 0, 5) : '-' }}</td>
                        <td class="fp-mono">
                            {{ $s->jam_pulang ? substr($s->jam_pulang, 0, 5) : '-' }}
                            @if ($s->lintas_hari) <span class="fp-badge fp-badge-warn">+1 hari</span> @endif
                        </td>
                        <td style="text-align:center">{{ rtrim(rtrim(number_format($s->istirahat_default, 1, ',', ''), '0'), ',') }}</td>
                        <td style="text-align:center">{{ $s->standar_jam !== null ? rtrim(rtrim(number_format($s->standar_jam, 1, ',', ''), '0'), ',') : '-' }}</td>
                        <td>{{ ucfirst($s->jenis) }}</td>
                        <td class="fp-muted">{{ $s->keterangan }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection