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
    @php $qs = array_merge(request()->query(), ['dari' => $dari, 'sampai' => $sampai]);
        $kartuOk = \Carbon\Carbon::parse($dari)->diffInDays(\Carbon\Carbon::parse($sampai)) <= 30;
        $kd = $kartuOk ? $dari : \Carbon\Carbon::parse($dari)->startOfMonth()->toDateString();
        $ks = $kartuOk ? $sampai : \Carbon\Carbon::parse($dari)->endOfMonth()->toDateString();
        $qsKartu = array_merge(request()->only(['q', 'kelompok', 'shift']), ['dari' => $kd, 'sampai' => $ks]); 
    @endphp
    
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
    @php
        $raguAktif = request('lembur') === 'belum';
        $linkRagu  = route('manajemen.absensi.index', array_merge(request()->only(['dari', 'sampai', 'q', 'kelompok', 'shift']), ['lembur' => 'belum']));
    @endphp
    <a href="{{ $raguAktif ? route('manajemen.absensi.index', request()->except('lembur')) : $linkRagu }}"
       class="fp-stat fp-stat-link {{ $raguAktif ? 'fp-stat-active' : '' }}">
        <div class="fp-stat-label" style="color:#ea580c">Lembur Perlu Konfirmasi</div>
        <div class="fp-stat-value">{{ number_format($stat['ragu']) }}</div>
        <div class="fp-stat-note">Pulang lewat jadwal &lt; 30 menit, dihitung atau tidak?</div>
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
        <input type="text" name="q" value="{{ request('q') }}" class="fp-input fp-input-search" placeholder="Cari nama (awalan) / ID Absen / ID Karyawan">
        <select name="status" class="fp-input">
            <option value="">-- Semua Status --</option>
            <option value="perlu" @selected(request('status') === 'perlu')>Perlu dicek + Tanpa jadwal</option>
            @foreach (array_keys($badge) as $s) <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option> @endforeach
        </select>
        <select name="lembur" class="fp-input" title="Filter lembur yang pulangnya lewat jadwal kurang dari 30 menit">
            <option value="">-- Semua Konfirmasi Lembur --</option>
            <option value="belum" @selected(request('lembur') === 'belum')>Belum dikonfirmasi</option>
            <option value="dihitung" @selected(request('lembur') === 'dihitung')>Sudah: dihitung lembur</option>
            <option value="tidak" @selected(request('lembur') === 'tidak')>Sudah: tidak dihitung</option>
            <option value="ragu" @selected(request('lembur') === 'ragu')>Semua kasus lembur 30 menit</option>
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
        @if (request()->hasAny(['q', 'status', 'lembur', 'kelompok', 'shift', 'dari', 'sampai']))
            <a href="{{ route('manajemen.absensi.index') }}" class="fp-btn fp-btn-soft">Reset</a>
        @endif
        <span class="fp-total">Total data: <b>{{ number_format($rows->total()) }}</b> baris</span>
    </form>
</div>

<div id="kBar" class="fp-bulkbar">
    <span><b id="kCount">0</b> karyawan dipilih</span>
    <button type="button" class="fp-btn fp-btn-primary" onclick="kCetak()">Cetak Kartu Amano Terpilih</button>
    @if ($stat['ragu'] > 0)
        <button type="button" class="fp-btn fp-btn-emerald" onclick="kLembur(1)" title="Hitung lembur 30 menit yang belum dikonfirmasi milik karyawan terpilih">Hitung lembur terpilih</button>
        <button type="button" class="fp-btn fp-btn-soft" onclick="kLembur(0)" title="Tidak hitung lembur 30 menit yang belum dikonfirmasi milik karyawan terpilih">Tidak dihitung terpilih</button>
    @endif
    <button type="button" class="fp-btn fp-btn-soft" onclick="kClear()">Batal pilih</button>
</div>

@if ($stat['ragu'] > 0)
<div class="fp-card">
    <form method="POST" action="{{ route('manajemen.absensi.konfirmasi-lembur-massal') }}" class="fp-filter">
        @csrf
        <input type="hidden" name="dari" value="{{ $dari }}">
        <input type="hidden" name="sampai" value="{{ $sampai }}">
        @foreach (request()->only(['q', 'kelompok', 'shift']) as $k => $v) <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endforeach
        <b>{{ number_format($stat['ragu']) }} lembur belum dikonfirmasi</b>
        <span class="fp-muted" style="font-size:13px">Semua yang belum dikonfirmasi pada filter ini (atau centang nama di tabel, lalu pilih di bar biru):</span>
        <button type="submit" name="hitung" value="1" class="fp-btn fp-btn-emerald" onclick="return confirm('Hitung SEMUA lembur yang belum dikonfirmasi pada filter ini?')">Hitung semua</button>
        <button type="submit" name="hitung" value="0" class="fp-btn fp-btn-soft" onclick="return confirm('Tidak hitung SEMUA lembur yang belum dikonfirmasi pada filter ini?')">Tidak dihitung semua</button>
    </form>
</div>
@endif

{{-- TABEL --}}
<div class="fp-card">
    <div class="fp-table-wrap">
        <table class="fp-table fp-table-sm">
            <thead>
                <tr>
                    <th style="width:96px"><input type="checkbox" class="fp-chk" id="kCheckAll"></th>
                    <th>No</th><th>Hari / Tanggal</th><th>ID Absen</th><th>ID Karyawan</th><th>Nama</th>
                    <th>Bagian / Divisi</th><th>Kelompok</th><th>Shift</th>
                    <th>Masuk Finger</th><th>Masuk Bulat</th><th>Pulang Finger</th><th>Pulang Bulat</th>
                    <th class="fp-num">Istirahat</th><th class="fp-num">Potongan</th><th class="fp-num">Total Jam</th>
                    <th class="fp-num">Lembur</th><th>Status Lembur</th><th class="fp-num">Kehadiran</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $r)
                    <tr title="{{ $r->catatan }}">
                        <td>
                            @php
                                $rk = $kartuOk ? [$dari, $sampai]
                                    : [$r->tanggal->copy()->startOfMonth()->toDateString(), $r->tanggal->copy()->endOfMonth()->toDateString()];
                            @endphp
                            <div style="display:flex;align-items:center;gap:8px">
                                <input type="checkbox" class="fp-chk k-chk" value="{{ $r->id_absen }}">
                                <a href="{{ route('manajemen.absensi.kartu', ['id_absen' => $r->id_absen, 'dari' => $rk[0], 'sampai' => $rk[1]]) }}"
                                   target="_blank" class="fp-kartu-link" title="Cetak kartu absensi {{ $r->nama }}">Kartu</a>
                            </div>
                        </td>
                        <td class="fp-muted">{{ $rows->firstItem() + $loop->index }}</td>
                        <td>{{ $r->tanggal->locale('id')->translatedFormat('l, d M Y') }}</td>
                        <td class="fp-mono fp-id">{{ $r->id_absen }}</td>
                        <td class="fp-mono">{{ $r->nik ?? '-' }}</td>
                        <td style="font-weight:600">
                            {{ $r->nama }}
                        </td>
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
                        <td class="fp-num">
                            {{ $f2($r->lembur_jam) }}
                            @if ($r->lembur_ragu > 0)
                                @php $kf = is_null($r->lembur_konfirmasi) ? null : (int) $r->lembur_konfirmasi; @endphp
                                <form method="POST" action="{{ route('manajemen.absensi.konfirmasi-lembur') }}" style="display:flex;gap:4px;justify-content:flex-end;align-items:center;margin-top:3px">
                                    @csrf
                                    <input type="hidden" name="id_absen" value="{{ $r->id_absen }}">
                                    <input type="hidden" name="tanggal" value="{{ $r->tanggal->toDateString() }}">
                                    @if ($kf === null)
                                        <span class="fp-badge fp-badge-warn" title="Scan pulang kurang dari 30 menit setelah jadwal, tetapi jam bulat naik. Konfirmasi apakah dihitung lembur.">? {{ $f2($r->lembur_ragu) }}</span>
                                    @endif
                                    <button type="submit" name="hitung" value="1" class="fp-badge {{ $kf === 1 ? 'fp-badge-ok' : 'fp-badge-info' }}" style="border:0;cursor:pointer">Hitung</button>
                                    <button type="submit" name="hitung" value="0" class="fp-badge {{ $kf === 0 ? 'fp-badge-err' : 'fp-badge-info' }}" style="border:0;cursor:pointer">Tidak</button>
                                </form>
                            @endif
                        </td>
                        <td>
                            @if ($r->lembur_ragu > 0)
                                @php $stLb = is_null($r->lembur_konfirmasi) ? null : (int) $r->lembur_konfirmasi; @endphp
                                @if ($stLb === 1) <span class="fp-badge fp-badge-ok">Dihitung</span>
                                @elseif ($stLb === 0) <span class="fp-badge fp-badge-err">Tidak dihitung</span>
                                @else <span class="fp-badge fp-badge-warn">Belum dikonfirmasi</span>
                                @endif
                            @else
                                <span class="fp-muted">-</span>
                            @endif
                        </td>
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
                                @if ($r->dikonfirmasi)
                                    <span class="fp-badge fp-badge-ok" title="Jam dikonfirmasi benar (pulang cepat / izin)">&#10003; Dikonfirmasi</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="20" class="fp-empty">Belum ada data. Pilih periode lalu klik <b>Hitung / Hitung Ulang</b>.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($rows->hasPages()) <div class="fp-pager">{{ $rows->links() }}</div> @endif
</div>
<form id="kLemburForm" method="POST" action="{{ route('manajemen.absensi.konfirmasi-lembur-massal') }}" style="display:none">
    @csrf
    <input type="hidden" name="dari" value="{{ $dari }}">
    <input type="hidden" name="sampai" value="{{ $sampai }}">
    @foreach (request()->only(['q', 'kelompok', 'shift']) as $k => $v) <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endforeach
    <input type="hidden" name="ids" id="kLemburIds">
    <input type="hidden" name="hitung" id="kLemburHitung">
</form>
<script>
(() => {
    const all  = document.getElementById('kCheckAll');
    const bar  = document.getElementById('kBar');
    const baris = () => [...document.querySelectorAll('.k-chk')];
    const ids   = () => [...new Set(baris().filter(c => c.checked).map(c => c.value))];

    function refresh() {
        const n = ids().length;
        document.getElementById('kCount').textContent = n;
        bar.classList.toggle('show', n > 0);
        baris().forEach(c => c.closest('tr').classList.toggle('fp-row-sel', c.checked));
        if (all) all.checked = baris().length > 0 && baris().every(c => c.checked);
    }

    document.addEventListener('change', e => {
        if (!e.target.classList.contains('k-chk')) return;
        // karyawan yang sama muncul di banyak baris: semua barisnya ikut tercentang
        baris().filter(c => c.value === e.target.value).forEach(c => c.checked = e.target.checked);
        refresh();
    });
    all?.addEventListener('change', () => { baris().forEach(c => c.checked = all.checked); refresh(); });

    window.kClear = () => { baris().forEach(c => c.checked = false); refresh(); };
    window.kLembur = (hitung) => {
        const pilih = ids();
        if (!pilih.length) return;
        const aksi = hitung ? 'HITUNG' : 'TIDAK hitung';
        if (!confirm(aksi + ' semua lembur 30 menit yang belum dikonfirmasi untuk ' + pilih.length + ' karyawan terpilih (pada periode dan filter ini)?')) return;
        document.getElementById('kLemburIds').value = pilih.join(',');
        document.getElementById('kLemburHitung').value = hitung ? 1 : 0;
        document.getElementById('kLemburForm').submit();
    };
    window.kCetak = () => {
        const pilih = ids();
        if (!pilih.length) return;
        const q = new URLSearchParams({ dari: @json($dari), ids: pilih.join(',') });
        window.open(@json(route('manajemen.absensi.kartu-semua')) + '?' + q.toString(), '_blank');
    };
})();
</script>
@endsection