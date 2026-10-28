@extends('layouts.app')

@section('title', 'Absensi Karyawan - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/manajemen/fingerprint/fingerprint-index.css') }}">
@endpush

@section('content')
@php
    $f2 = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ''), '0'), ',');
    $f4 = fn ($v) => rtrim(rtrim(number_format((float) $v, 4, ',', ''), '0'), ',');
    $jam = fn ($d, $tgl, $detik = false) => $d
        ? $d->format($detik ? 'H:i:s' : 'H:i') . ($d->toDateString() !== $tgl->toDateString() ? ' (+1)' : '')
        : '-';
    $badge = ['OK' => 'fp-badge-ok', 'Perlu dicek' => 'fp-badge-warn', 'Tidak hadir' => 'fp-badge-err', 'Tanpa jadwal' => 'fp-badge-info'];
@endphp

<div class="fp-head">
    <div>
        <h1 class="fp-title">Absensi Karyawan</h1>
        <p class="fp-sub">Hasil perhitungan dari scan finger print, jadwal, dan Master Karyawan: jam bulat, potongan, lembur, dan kehadiran.</p>
    </div>
    @php $qs = array_merge(request()->query(), ['dari' => $dari, 'sampai' => $sampai]); @endphp
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-start">
        <a href="{{ route('manajemen.absensi.export', $qs) }}" class="fp-btn fp-btn-emerald">Export Excel</a>
        <a href="{{ route('manajemen.absensi.cetak-pdf', $qs) }}" target="_blank" class="fp-btn fp-btn-primary">Cetak PDF</a>
    </div>
</div>

<div class="fp-stats">
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#0284c7">Data Absensi</div>
        <div class="fp-stat-value">{{ number_format($stat['baris']) }}</div>
        <div class="fp-stat-note">Karyawan per tanggal</div>
    </div>
    @php
        $linkPerlu = route('manajemen.absensi.index', array_merge(
            request()->only(['dari', 'sampai', 'q', 'kelompok', 'shift']),
            ['status' => 'perlu']
        ));
        $perluAktif = request('status') === 'perlu';
    @endphp
    <a href="{{ $perluAktif ? route('manajemen.absensi.index', request()->except('status')) : $linkPerlu }}"
    class="fp-stat fp-stat-link {{ $perluAktif ? 'fp-stat-active' : '' }}">
        <div class="fp-stat-label" style="color:#d97706">Perlu Dicek</div>
        <div class="fp-stat-value">{{ number_format($stat['perlu']) }}</div>
        <div class="fp-stat-note">
            {{ $perluAktif ? 'Klik lagi untuk menampilkan semua' : ($stat['perlu'] > 0 ? 'Klik untuk melihat barisnya' : 'Tidak ada yang perlu dicek') }}
        </div>
    </a>
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#dc2626">Total Potongan</div>
        <div class="fp-stat-value">{{ $f2($stat['potongan']) }} jam</div>
        <div class="fp-stat-note">Terlambat, pulang cepat, tidak hadir</div>
    </div>
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#7c3aed">Total Lembur</div>
        <div class="fp-stat-value">{{ $f2($stat['lembur']) }} jam</div>
        <div class="fp-stat-note">Akumulasi pada periode ini</div>
    </div>
</div>

{{-- HITUNG --}}
<div class="fp-card">
    <form method="POST" action="{{ route('manajemen.absensi.hitung') }}" class="fp-filter"
          onsubmit="return confirm('Hitung ulang absensi untuk periode ini? Hasil hitungan lama pada periode tersebut akan diganti.')">
        @csrf
        <b>Hitung absensi periode:</b>
        <input type="date" name="dari" value="{{ $dari }}" class="fp-input" required>
        <span class="fp-muted">s/d</span>
        <input type="date" name="sampai" value="{{ $sampai }}" class="fp-input" required>
        <button type="submit" class="fp-btn fp-btn-primary">Hitung / Hitung Ulang</button>
        <span class="fp-muted" style="font-size:13px">Pastikan scan finger print dan jadwal periode ini sudah diimpor.</span>
    </form>
</div>

{{-- FILTER --}}
<div class="fp-card">
    <form method="GET" class="fp-filter">
        <input type="text" name="q" value="{{ request('q') }}" class="fp-input fp-input-search" placeholder="Cari ID Absen / ID Karyawan / Nama">
        <select name="status" class="fp-input">
            <option value="">-- Semua Status --</option>
            <option value="perlu" @selected(request('status') === 'perlu')>Perlu dicek + Tanpa jadwal</option>
            @foreach (array_keys($badge) as $s) <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option> @endforeach
        </select>
        <select name="kelompok" class="fp-input">
            <option value="">-- Semua Kelompok --</option>
            @foreach ($kelompoks as $k) <option value="{{ $k }}" @selected(request('kelompok') === $k)>{{ $k }}</option> @endforeach
        </select>
        <select name="shift" class="fp-input">
            <option value="">-- Semua Shift --</option>
            @foreach ($shifts as $s) <option value="{{ $s }}" @selected(request('shift') === $s)>{{ $s }}</option> @endforeach
        </select>
        <input type="date" name="dari" value="{{ $dari }}" class="fp-input">
        <span class="fp-muted">s/d</span>
        <input type="date" name="sampai" value="{{ $sampai }}" class="fp-input">
        <button type="submit" class="fp-btn fp-btn-primary">Filter</button>
        @if (request()->hasAny(['q', 'status', 'kelompok', 'shift', 'dari', 'sampai']))
            <a href="{{ route('manajemen.absensi.index') }}" class="fp-btn fp-btn-soft">Reset</a>
        @endif
        <span class="fp-total">Total data: <b>{{ number_format($rows->total()) }}</b> baris</span>
    </form>
</div>

{{-- TABEL --}}
<div class="fp-card">
    <div class="fp-table-wrap">
        <table class="fp-table fp-table-sm">
            <thead>
                <tr>
                    <th>No</th><th>Hari / Tanggal</th><th>ID Absen</th><th>ID Karyawan</th><th>Nama</th>
                    <th>Bagian / Divisi</th><th>Kelompok</th><th>Shift</th>
                    <th>Masuk Finger</th><th>Masuk Bulat</th><th>Pulang Finger</th><th>Pulang Bulat</th>
                    <th class="fp-num">Istirahat</th><th class="fp-num">Potongan</th><th class="fp-num">Total Jam</th>
                    <th class="fp-num">Lembur</th><th class="fp-num">Kehadiran</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $r)
                    <tr title="{{ $r->catatan }}">
                        <td class="fp-muted">{{ $rows->firstItem() + $loop->index }}</td>
                        <td>{{ $r->tanggal->locale('id')->translatedFormat('l, d M Y') }}</td>
                        <td class="fp-mono fp-id">{{ $r->id_absen }}</td>
                        <td class="fp-mono">{{ $r->nik ?? '-' }}</td>
                        <td style="font-weight:600">{{ $r->nama }}</td>
                        <td>{{ $departemen[$r->departemen_cd] ?? ($r->departemen_cd ?? '-') }}</td>
                        <td>{{ $r->kelompok_kerja ?? '-' }}</td>
                        <td>
                            @if ($r->kode_shift)
                                @php
                                    $sf  = $shiftMap[$r->kode_shift] ?? null;
                                    $ket = $r->jadwal_masuk && $r->jadwal_pulang
                                        ? $r->jadwal_masuk->format('H:i') . ' - ' . $r->jadwal_pulang->format('H:i')
                                        : ($sf && in_array($sf->jenis, ['minggu', 'driver']) ? ucfirst($sf->jenis) : null);
                                @endphp
                                <div class="fp-shift-cell">
                                    <span class="fp-badge fp-badge-info">{{ $r->kode_shift }}</span>
                                    @if ($ket) <span class="fp-shift-jam">{{ $ket }}</span> @endif
                                </div>
                            @else - @endif
                        </td>
                        <td class="fp-mono fp-muted">{{ $jam($r->masuk_finger, $r->tanggal, true) }}</td>
                        <td class="fp-mono fp-time-final">{{ $jam($r->masuk_bulat, $r->tanggal) }}</td>
                        <td class="fp-mono fp-muted">{{ $jam($r->pulang_finger, $r->tanggal, true) }}</td>
                        <td class="fp-mono fp-time-final">{{ $jam($r->pulang_bulat, $r->tanggal) }}</td>
                        <td class="fp-num">
                            {{ $f2($r->istirahat) }}
                            @if ($r->istirahat_bawaan && $r->kode_shift) <span class="fp-badge fp-badge-warn" title="Istirahat memakai bawaan shift">B</span> @endif
                        </td>
                        <td class="fp-num">{{ $f2($r->potongan_jam) }}</td>
                        <td class="fp-num"><b>{{ $f2($r->total_jam) }}</b></td>
                        <td class="fp-num">{{ $f2($r->lembur_jam) }}</td>
                        <td class="fp-num"><b>{{ $f4($r->kehadiran) }}</b></td>
                        <td>
                            @php
                                $sumber = $r->sumber_masalah;
                                $tgl    = $r->tanggal->toDateString();
                                $urlFp  = route('manajemen.fingerprint.index', [
                                    'q' => $r->id_absen, 'dari' => $tgl,
                                    'sampai' => $r->tanggal->copy()->addDay()->toDateString(), 'from' => 'absensi',
                                ]);
                                $urlJd  = route('manajemen.jadwal.index', [
                                    'q' => $r->id_absen, 'dari' => $tgl, 'sampai' => $tgl, 'from' => 'absensi',
                                ]);
                            @endphp

                            @if ($sumber)
                                <a href="{{ $sumber === 'jadwal' ? $urlJd : $urlFp }}"
                                class="fp-badge fp-badge-link {{ $badge[$r->status] ?? 'fp-badge-info' }}"
                                title="Buka {{ $sumber === 'jadwal' ? 'Jadwal' : 'Finger Print' }} untuk memeriksa">
                                    {{ $r->status }} &rarr;
                                </a>
                                @if ($r->catatan) <div class="fp-alasan">{{ $r->catatan }}</div> @endif
                                <div class="fp-alasan-link">
                                    Periksa:
                                    <a href="{{ $sumber === 'jadwal' ? $urlJd : $urlFp }}"><b>{{ $sumber === 'jadwal' ? 'Jadwal' : 'Finger Print' }}</b></a>
                                    &middot;
                                    <a href="{{ $sumber === 'jadwal' ? $urlFp : $urlJd }}">{{ $sumber === 'jadwal' ? 'Finger Print' : 'Jadwal' }}</a>
                                </div>
                            @else
                                <span class="fp-badge {{ $badge[$r->status] ?? 'fp-badge-ok' }}">{{ $r->status }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="18" class="fp-empty">Belum ada data. Pilih periode lalu klik <b>Hitung / Hitung Ulang</b>.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($rows->hasPages()) <div class="fp-pager">{{ $rows->links() }}</div> @endif
</div>
@endsection