<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@php
    // GANTI path logo di bawah ini sesuai lokasi file logo Mirasa di folder public.
    $logo = asset('images/logo.png');

    $namaPeriode = \Carbon\Carbon::createFromFormat('!Y-m', $periode)->locale('id')->translatedFormat('F Y');
    $dicetak = now()->locale('id')->translatedFormat('d F Y, H:i');
    $rp  = fn ($v) => number_format((float) $v, 0, ',', '.');
    $f2  = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
    $label = ['draft' => 'DRAFT', 'final' => 'FINAL', 'dibayar' => 'SUDAH DIBAYAR'];
@endphp
<title>Slip Gaji {{ $namaPeriode }}</title>
<style>
    :root { --ink: #0f172a; --muted: #64748b; --line: #cbd5e1; --soft: #f1f5f9; --brand: #b91c1c;
        --blue: #1d4ed8; --blue-soft: #eff6ff; --green: #15803d; }
    * { box-sizing: border-box; }
    body { font-family: "Segoe UI", Arial, Helvetica, sans-serif; font-size: 12px; color: var(--ink); margin: 0; background: #e2e8f0; }
    .bar { position: sticky; top: 0; z-index: 5; background: #1e293b; color: #fff; padding: 10px 16px; display: flex; gap: 12px; align-items: center; }
    .bar button { padding: 6px 16px; border: 0; border-radius: 6px; background: #0d9488; color: #fff; cursor: pointer; font-size: 13px; }

    .slip { background: #fff; width: 190mm; margin: 14px auto; padding: 11mm 12mm 9mm; page-break-after: always; border-top: 5px solid var(--brand); }
    .slip:last-of-type { page-break-after: auto; }

    .head { display: flex; align-items: center; gap: 14px; padding-bottom: 12px; border-bottom: 1px solid var(--line); }
    .head img { height: 54px; width: auto; }
    .head .co { flex: 1; }
    .co h1 { font-size: 17px; margin: 0; letter-spacing: .3px; }
    .co div { color: var(--muted); font-size: 11px; margin-top: 2px; }
    .head .doc { text-align: right; }
    .doc .t { font-size: 18px; font-weight: 700; letter-spacing: 1.5px; color: var(--brand); }
    .doc .p { color: var(--muted); margin-top: 2px; }
    .stamp { display: inline-block; margin-top: 5px; font-weight: 700; font-size: 10px; padding: 2px 8px; border: 1.5px solid var(--muted); border-radius: 3px; color: var(--muted); letter-spacing: .5px; }
    .stamp.ok { color: #047857; border-color: #047857; }

    .ident { display: grid; grid-template-columns: 1fr 1fr; gap: 0 24px; margin: 12px 0 14px; padding: 9px 12px; background: var(--soft); border-radius: 6px; }
    .ident div { display: flex; padding: 3px 0; }
    .ident span { width: 90px; color: var(--muted); }
    .ident b { font-weight: 600; }

    .cols { display: flex; gap: 16px; align-items: flex-start; }
    .cols > div { flex: 1; }
    table { width: 100%; border-collapse: collapse; }
    .box th { text-align: left; padding: 6px 8px; background: var(--blue); color: #ffffff; font-size: 11px; letter-spacing: .6px; }
    .box th.n { text-align: right; }
    .box td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
    .box td.n { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .box tr:nth-child(even) td { background: #f8fafc; }
    .box .tot td { border-top: 1.5px solid var(--blue); border-bottom: 0; background: var(--blue-soft); color: var(--blue); font-weight: 700; }
    .small { color: var(--muted); font-size: 10.5px; }

    .bersih { margin-top: 14px; display: flex; justify-content: space-between; align-items: center; padding: 11px 14px; background: var(--green); color: #fff; border-radius: 6px; }
    .bersih .l { font-size: 12px; letter-spacing: 1px; }
    .bersih .v { font-size: 19px; font-weight: 700; font-variant-numeric: tabular-nums; }
    .terbilang { margin-top: 7px; font-style: italic; color: #334155; }
    .note { margin-top: 8px; color: var(--muted); font-size: 11px; }

    .ttd { display: flex; justify-content: space-between; margin-top: 24px; text-align: center; }
    .ttd > div { width: 38%; }
    .ttd .garis { margin-top: 52px; border-top: 1px solid var(--ink); padding-top: 3px; font-weight: 600; }
    .foot { margin-top: 18px; padding-top: 7px; border-top: 1px solid var(--line); color: var(--muted); font-size: 10px; display: flex; justify-content: space-between; }

    @media print {
        body { background: #fff; }
        .bar { display: none; }
        .slip { margin: 0; width: auto; padding: 8mm 10mm; }
        * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        @page { size: A4; margin: 8mm; }
    }
</style>
</head>
<body>
<div class="bar">
    <b>Slip Gaji {{ $namaPeriode }}</b> <span>{{ $rows->count() }} slip</span>
    <button onclick="window.print()">Cetak</button>
</div>

@foreach ($rows as $r)
@php
    $h = $r['hasil'];
    $adaL1 = ($h['jam_lembur_1'] ?? 0) > 0;
    $adaL2 = ($h['jam_lembur_2'] ?? 0) > 0;
@endphp
<div class="slip">
    <div class="head">
        <img src="{{ $logo }}" alt="Mirasa" onerror="this.style.display='none'">
        <div class="co">
            <h1>PT MIRASA FOOD INDUSTRY</h1>
            <div>Human Resources &middot; Penggajian Karyawan</div>
        </div>
        <div class="doc">
            <div class="t">SLIP GAJI</div>
            <div class="p">Periode {{ $namaPeriode }}</div>
            <span class="stamp {{ $r['status'] !== 'draft' ? 'ok' : '' }}">{{ $label[$r['status']] ?? strtoupper($r['status']) }}</span>
        </div>
    </div>

    <div class="ident">
        <div><span>Nama</span><b>{{ $r['nama'] }}</b></div>
        <div><span>ID Karyawan</span><b>{{ $r['nik'] ?? $r['id_absen'] }}</b></div>
        <div><span>Devisi</span><b>{{ $r['jabatan'] ?? '-' }}</b></div>
        <div><span>Kelompok</span><b>{{ $r['kelompok'] ?? '-' }}</b></div>
        <div><span>Masa kerja</span><b>{{ $h['masa_kerja_tahun'] ?? 0 }} tahun</b></div>
        <div><span>Kehadiran</span><b>{{ $f2($h['total_kehadiran'] ?? 0) }} hari</b></div>
    </div>

    <div class="cols">
        <div>
            <table class="box">
                <tr><th>PENDAPATAN</th><th class="n">Rp</th></tr>
                <tr>
                    <td>Gaji pokok<div class="small">{{ $rp($r['input']['gaji_harian']) }} &times; {{ $f2($h['total_kehadiran'] ?? 0) }} hari</div></td>
                    <td class="n">{{ $rp($h['gaji_pokok'] ?? 0) }}</td>
                </tr>
                @if ($adaL1)
                <tr>
                    <td>Lembur I (&times;1,5)<div class="small">{{ $f2($h['jam_lembur_1']) }} jam</div></td>
                    <td class="n">{{ $rp($h['honor_lembur_1'] ?? 0) }}</td>
                </tr>
                @endif
                @if ($adaL2)
                <tr>
                    <td>Lembur II (&times;2)<div class="small">{{ $f2($h['jam_lembur_2']) }} jam</div></td>
                    <td class="n">{{ $rp($h['honor_lembur_2'] ?? 0) }}</td>
                </tr>
                @endif
                <tr>
                    <td>Tunjangan masa kerja<div class="small">{{ $rp($h['tunjangan_masa_per_hari'] ?? 0) }} &times; {{ $f2($h['total_kehadiran'] ?? 0) }} hari</div></td>
                    <td class="n">{{ $rp($h['tunjangan_masa_kerja'] ?? 0) }}</td>
                </tr>
                <tr><td>Tunjangan jabatan</td><td class="n">{{ $rp($h['tunjangan_jabatan'] ?? 0) }}</td></tr>
                <tr><td>Insentif kerajinan</td><td class="n">{{ $rp($h['insentif'] ?? 0) }}</td></tr>
                <tr class="tot"><td>Total pendapatan</td><td class="n">{{ $rp($h['total_pendapatan'] ?? 0) }}</td></tr>
            </table>
        </div>
        <div>
            <table class="box">
                <tr><th>POTONGAN</th><th class="n">Rp</th></tr>
                <tr><td>BPJS Kesehatan</td><td class="n">{{ $rp($h['potongan_bpjs_kes'] ?? 0) }}</td></tr>
                <tr><td>BPJS Ketenagakerjaan</td><td class="n">{{ $rp($h['potongan_bpjs_tk'] ?? 0) }}</td></tr>
                <tr><td>Kasbon / lainnya</td><td class="n">{{ $rp($h['potongan_lainnya'] ?? 0) }}</td></tr>
                <tr class="tot"><td>Total potongan</td><td class="n">{{ $rp($h['total_potongan'] ?? 0) }}</td></tr>
            </table>
            @if (!empty($r['catatan']))
                <p class="note">Catatan: {{ $r['catatan'] }}</p>
            @endif
        </div>
    </div>

    <div class="bersih"><span class="l">GAJI BERSIH DITERIMA</span><span class="v">Rp {{ $rp($h['gaji_bersih'] ?? 0) }}</span></div>
    <div class="terbilang">Terbilang: {{ \App\Support\Terbilang::rupiah((int) ($h['gaji_bersih'] ?? 0)) }}</div>

    <div class="ttd">
        <div>Penerima<div class="garis">{{ $r['nama'] }}</div></div>
        <div>Keuangan<div class="garis">&nbsp;Admin Keuangan</div></div>
    </div>

    <div class="foot">
        <span>Dokumen ini dihasilkan otomatis oleh sistem ERP PT Mirasa Food Industry.</span>
        <span>Dicetak {{ $dicetak }}</span>
    </div>
</div>
@endforeach
</body>
</html>