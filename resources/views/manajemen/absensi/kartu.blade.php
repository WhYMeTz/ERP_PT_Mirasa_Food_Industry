<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@php
    $logo      = asset('images/logo.png');
    $bln       = \Carbon\Carbon::parse($dari)->startOfMonth();
    $namaBulan = $bln->copy()->locale('id')->translatedFormat('F Y');
    $dicetak   = now()->locale('id')->translatedFormat('d F Y, H:i');
    $f2  = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',') ?: '0';
    $f4  = fn ($v) => rtrim(rtrim(number_format((float) $v, 4, ',', '.'), '0'), ',') ?: '0';
    $jam = fn ($d, $tgl) => $d ? $d->format('H:i') . ($d->toDateString() !== $tgl->toDateString() ? ' (+1)' : '') : '-';
@endphp
<title>Kartu Absensi {{ $namaBulan }}</title>
<style>
    :root { --ink:#0f172a; --muted:#64748b; --line:#cbd5e1; --soft:#f1f5f9; --brand:#b91c1c; --blue:#1d4ed8; }
    * { box-sizing:border-box; }
    body { font-family:"Segoe UI", Arial, sans-serif; font-size:10px; color:var(--ink); margin:0; background:#e2e8f0; }
    .bar { position:sticky; top:0; z-index:5; background:#1e293b; color:#fff; padding:10px 16px; display:flex; gap:14px; align-items:center; font-size:13px; }
    .bar button { padding:6px 16px; border:0; border-radius:6px; background:#0d9488; color:#fff; cursor:pointer; font-size:13px; }

    .kartu { background:#fff; width:190mm; margin:12px auto; padding:7mm 9mm 6mm; page-break-after:always; border-top:5px solid var(--brand); }
    .kartu:last-of-type { page-break-after:auto; }

    .head { display:flex; align-items:center; gap:12px; padding-bottom:7px; border-bottom:1px solid var(--line); }
    .head img { height:40px; width:auto; }
    .head .co { flex:1; }
    .co h1 { font-size:14px; margin:0; letter-spacing:.3px; }
    .co div { color:var(--muted); font-size:10px; margin-top:1px; }
    .doc { text-align:right; }
    .doc .t { font-size:15px; font-weight:700; letter-spacing:1.2px; color:var(--brand); }
    .doc .p { color:var(--muted); margin-top:1px; }

    .ident { display:grid; grid-template-columns:1fr 1fr; gap:0 20px; margin:8px 0; padding:6px 10px; background:var(--soft); border-radius:5px; }
    .ident div { display:flex; padding:2px 0; }
    .ident span { width:92px; color:var(--muted); flex:none; }
    .ident b { font-weight:600; }

    table { width:100%; border-collapse:collapse; table-layout:fixed; }
    .jam th { background:var(--blue); color:#fff; padding:4px 3px; font-size:9.5px; border:1px solid var(--blue); }
    .jam td { padding:0 5px; border:1px solid #d5dde6; height:6mm; font-size:9.5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .jam .c { text-align:center; font-variant-numeric:tabular-nums; }
    .jam .n { text-align:right; font-variant-numeric:tabular-nums; }
    .jam tr.minggu td { background:#fff7ed; }
    .jam tr.absen td  { background:#fef2f2; }
    .jam tr.cek td    { background:#fffbeb; }
    .jam tr.total td  { background:#dbeafe; font-weight:700; border-top:1.5px solid var(--blue); height:6.5mm; }
    .sh b { margin-right:4px; }
    .sh small { color:var(--muted); font-size:8.5px; }
    .rd { color:#b91c1c; font-weight:700; letter-spacing:1px; }

    .ringkas { display:grid; grid-template-columns:repeat(5,1fr); gap:6px; margin-top:8px; }
    .ringkas div { border:1px solid var(--line); border-radius:5px; padding:4px 6px; text-align:center; }
    .ringkas span { display:block; font-size:8.5px; color:var(--muted); text-transform:uppercase; letter-spacing:.3px; }
    .ringkas b { font-size:13px; }
    .peringatan { margin-top:6px; padding:3px 8px; background:#fffbeb; border:1px solid #fde68a; color:#92400e; border-radius:4px; font-size:9px; }

    .ttd { display:flex; justify-content:space-between; margin-top:14px; text-align:center; }
    .ttd > div { width:34%; }
    .ttd .garis { margin-top:46px; border-top:1px solid var(--ink); padding-top:2px; font-weight:600; }
    .foot { margin-top:10px; padding-top:4px; border-top:1px solid var(--line); color:var(--muted); font-size:8.5px; display:flex; justify-content:space-between; }

    @media print {
        body { background:#fff; }
        .bar { display:none; }
        .kartu { margin:0; width:auto; padding:3mm 5mm; }
        * { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        @page { size:A4 portrait; margin:8mm; }
    }
</style>
</head>
<body>
<div class="bar">
    <b>Kartu Absensi {{ $namaBulan }}</b>
    <span>{{ count($kartu) }} karyawan ({{ count($kartu) }} halaman)</span>
    <button onclick="window.print()">Cetak</button>
</div>

@foreach ($kartu as $k)
@php
    $s         = $k['ringkas'];
    $adaLembur = true;                                   // kolom Lembur selalu tampil, di sebelah Jam Kerja
    $lebar     = [12, 10, 21, 11, 11, 11, 11, 13];
@endphp
<div class="kartu">
    <div class="head">
        <img src="{{ $logo }}" alt="Mirasa" onerror="this.style.display='none'">
        <div class="co">
            <h1>PT MIRASA FOOD INDUSTRY</h1>
            <div>Human Resources &middot; Absensi Karyawan</div>
        </div>
        <div class="doc">
            <div class="t">KARTU ABSENSI</div>
            <div class="p">Periode {{ $namaBulan }}</div>
        </div>
    </div>

    <div class="ident">
        <div><span>Nama Karyawan</span><b>{{ $k['nama'] }}</b></div>
        <div><span>Departemen</span><b>{{ $k['departemen'] }}</b></div>
        <div><span>ID Karyawan</span><b>{{ $k['nik'] }}</b></div>
        <div><span>Divisi / Bagian</span><b>{{ $k['divisi'] }}</b></div>
        <div><span>ID Absensi</span><b>{{ $k['id_absen'] }}</b></div>
        <div><span>Bulan Periode</span><b>{{ $namaBulan }}</b></div>
    </div>

    <table class="jam">
        <colgroup>
            @foreach ($lebar as $p) <col style="width:{{ $p }}%"> @endforeach
        </colgroup>
        <thead>
            <tr>
                <th>Tanggal</th><th>Hari</th><th>Shift</th><th>Jam Masuk</th><th>Jam Pulang</th><th>Jam Kerja</th>
                @if ($adaLembur) <th>Lembur</th> @endif
                <th>Potongan</th>
            </tr>
        </thead>
        <tbody>
            {{-- hanya hari yang punya data --}}
            @foreach ($k['rows'] as $r)
                @php
                    $d      = $r->tanggal;
                    $sf     = $r->kode_shift ? ($shiftMap[$r->kode_shift] ?? null) : null;
                    $jenis  = $sf->jenis ?? 'jam';
                    $minggu = $jenis === 'minggu';
                    $driver = $jenis === 'driver' || $r->departemen_cd === 'DRIVER';
                    $ok     = $r->status === 'OK';
                    $absen  = $r->status === 'Tidak hadir';
                    $kelas  = $absen ? 'absen' : (!$ok ? 'cek' : ($d->isSunday() ? 'minggu' : ''));
                    $jadwal = ($r->jadwal_masuk && $r->jadwal_pulang)
                        ? $r->jadwal_masuk->format('H:i') . '-' . $r->jadwal_pulang->format('H:i') : '';
                @endphp
                <tr class="{{ $kelas }}">
                    <td class="c">{{ $d->format('d/m/Y') }}</td>
                    <td>{{ $d->copy()->locale('id')->translatedFormat('l') }}</td>
                    <td class="sh"><b>{{ $r->kode_shift ?? '-' }}</b>@if ($jadwal)<small>{{ $jadwal }}</small>@endif</td>
                    @if ($absen)
                        <td class="c rd" colspan="{{ $adaLembur ? 4 : 3 }}">ALPHA</td>
                        <td class="n">{{ $r->potongan_jam > 0 ? $f2($r->potongan_jam) : '-' }}</td>
                    @else
                        <td class="c">{{ $jam($r->masuk_finger, $r->tanggal) }}</td>
                        <td class="c">{{ $jam($r->pulang_finger, $r->tanggal) }}</td>
                        <td class="n">
                            @if (!$ok) ?
                            @elseif ($minggu || $driver) -
                            @else <b>{{ $f2($r->total_jam) }}</b>
                            @endif
                        </td>
                        @if ($adaLembur)
                            <td class="n">
                                @if (!$ok) ?
                                @else {{ $r->lembur_jam > 0 ? $f2($r->lembur_jam) : '-' }}
                                @endif
                            </td>
                        @endif
                        <td class="n">
                            @if (!$ok) ?
                            @else {{ $r->potongan_jam > 0 ? $f2($r->potongan_jam) : '-' }}
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="5" class="n">TOTAL</td>
                <td class="n">{{ $f2($s['total_jam']) }}</td>
                @if ($adaLembur) <td class="n">{{ $f2($s['lembur']) }}</td> @endif
                <td class="n">{{ $f2($s['potongan']) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="ringkas">
        <div><span>Hari Hadir</span><b>{{ $s['hari_hadir'] }}</b></div>
        <div><span>Total Kehadiran</span><b>{{ $f4($s['kehadiran']) }}</b></div>
        <div><span>Total Jam Kerja</span><b>{{ $f2($s['total_jam']) }}</b></div>
        <div><span>Total Lembur</span><b>{{ $f2($s['lembur']) }}</b></div>
        <div><span>Potongan (jam)</span><b>{{ $f2($s['potongan']) }}</b></div>
    </div>

    @if ($s['perlu'] > 0)
        <div class="peringatan">
            <b>{{ $s['perlu'] }} hari</b> berstatus Perlu dicek / Tanpa jadwal (ditandai "?") dan belum dijumlahkan.
            Perbaiki dulu di menu Absensi, lalu cetak ulang.
        </div>
    @endif

    <div class="ttd">
        <div>Karyawan<div class="garis">{{ $k['nama'] }}</div></div>
        <div>HRD PT. MIRASA FOOD INDUSTRY<div class="garis">&nbsp;</div></div>
    </div>

    <div class="foot">
        <span>Dokumen dihasilkan otomatis oleh sistem ERP PT Mirasa Food Industry.</span>
        <span>Dicetak {{ $dicetak }}</span>
    </div>
</div>
@endforeach
</body>
</html>