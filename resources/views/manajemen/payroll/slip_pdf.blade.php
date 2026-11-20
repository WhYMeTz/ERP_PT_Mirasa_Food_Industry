<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Slip Gaji</title>
@php
    // DomPDF tidak memuat gambar dari URL lokal dengan andal; pakai file di folder public, disisipkan langsung.
    $logoFile = public_path('images/logo.png');
    $logo = is_file($logoFile) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile)) : null;

    $namaPeriode = \Carbon\Carbon::createFromFormat('!Y-m', $periode)->locale('id')->translatedFormat('F Y');
    $dicetak = now()->locale('id')->translatedFormat('d F Y, H:i');
    $rp  = fn ($v) => number_format((float) $v, 0, ',', '.');
    $f2  = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
    $label = ['draft' => 'DRAFT', 'final' => 'FINAL', 'dibayar' => 'SUDAH DIBAYAR'];
    $h = $r['hasil'];
    $adaL1 = ($h['jam_lembur_1'] ?? 0) > 0;
    $adaL2 = ($h['jam_lembur_2'] ?? 0) > 0;
@endphp
<style>
    @page { margin: 12mm 12mm 12mm 12mm; }
    body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9.5pt; color: #0f172a; }
    table { border-collapse: collapse; width: 100%; }
    .brand-line { border-top: 4px solid #b91c1c; padding-top: 12px; }
    .muted { color: #64748b; }
    .small { color: #64748b; font-size: 8pt; }
    .judul { font-size: 15pt; font-weight: bold; color: #b91c1c; letter-spacing: 1px; text-align: right; }
    .stamp { font-size: 8pt; font-weight: bold; color: #047857; border: 1px solid #047857; padding: 1px 6px; }
    .ident { background: #f1f5f9; margin: 10px 0 12px; }
    .ident td { padding: 3px 8px; font-size: 9pt; }
    .box th { background: #1d4ed8; color: #fff; text-align: left; padding: 5px 7px; font-size: 8.5pt; }
    .box th.n { text-align: right; }
    .box td { padding: 4px 7px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
    .box td.n { text-align: right; white-space: nowrap; }
    .box tr.tot td { background: #eff6ff; color: #1d4ed8; font-weight: bold; border-top: 1px solid #1d4ed8; }
    .bersih { background: #15803d; color: #fff; margin-top: 12px; }
    .bersih td { padding: 9px 12px; }
    .terbilang { margin-top: 6px; font-style: italic; color: #334155; }
    .ttd td { text-align: center; width: 38%; vertical-align: top; }
    .garis { margin-top: 48px; border-top: 1px solid #0f172a; padding-top: 3px; font-weight: bold; }
    .foot { margin-top: 16px; border-top: 1px solid #cbd5e1; padding-top: 6px; font-size: 7.5pt; color: #64748b; }
</style>
</head>
<body>
<div class="brand-line">
    <table>
        <tr>
            <td style="width: 12%; vertical-align: middle;">
                @if ($logo) <img src="{{ $logo }}" style="height: 48px;"> @endif
            </td>
            <td style="vertical-align: middle;">
                <div style="font-size: 14pt; font-weight: bold;">PT MIRASA FOOD INDUSTRY</div>
                <div class="small">Human Resources &middot; Penggajian Karyawan</div>
            </td>
            <td style="width: 32%; vertical-align: middle; text-align: right;">
                <div class="judul">SLIP GAJI</div>
                <div class="muted">Periode {{ $namaPeriode }}</div>
                @if (($r['status'] ?? 'draft') !== 'draft')
                    <span class="stamp">{{ $label[$r['status']] ?? strtoupper($r['status']) }}</span>
                @else
                    <span class="stamp" style="color:#64748b;border-color:#64748b">DRAFT</span>
                @endif
            </td>
        </tr>
    </table>
</div>

<table class="ident">
    <tr>
        <td class="muted" style="width: 14%;">Nama</td><td style="width: 36%;"><b>{{ $r['nama'] }}</b></td>
        <td class="muted" style="width: 16%;">ID Karyawan</td><td><b>{{ $r['nik'] ?? $r['id_absen'] }}</b></td>
    </tr>
    <tr>
        <td class="muted">Devisi</td><td><b>{{ $r['jabatan'] ?? '-' }}</b></td>
        <td class="muted">Kelompok</td><td><b>{{ $r['kelompok'] ?? '-' }}</b></td>
    </tr>
    <tr>
        <td class="muted">Masa kerja</td><td><b>{{ $h['masa_kerja_tahun'] ?? 0 }} tahun</b></td>
        <td class="muted">Kehadiran</td><td><b>{{ $f2($h['total_kehadiran'] ?? 0) }} hari</b></td>
    </tr>
</table>

<table>
    <tr>
        <td style="width: 52%; vertical-align: top; padding-right: 8px;">
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
        </td>
        <td style="width: 48%; vertical-align: top; padding-left: 8px;">
            <table class="box">
                <tr><th>POTONGAN</th><th class="n">Rp</th></tr>
                <tr><td>BPJS Kesehatan</td><td class="n">{{ $rp($h['potongan_bpjs_kes'] ?? 0) }}</td></tr>
                <tr><td>BPJS Ketenagakerjaan</td><td class="n">{{ $rp($h['potongan_bpjs_tk'] ?? 0) }}</td></tr>
                <tr><td>Kasbon / lainnya</td><td class="n">{{ $rp($h['potongan_lainnya'] ?? 0) }}</td></tr>
                <tr class="tot"><td>Total potongan</td><td class="n">{{ $rp($h['total_potongan'] ?? 0) }}</td></tr>
            </table>
            @if (!empty($r['catatan']))
                <p class="small" style="margin-top: 8px;">Catatan: {{ $r['catatan'] }}</p>
            @endif
        </td>
    </tr>
</table>

<table class="bersih">
    <tr>
        <td style="font-size: 10pt;">GAJI BERSIH DITERIMA</td>
        <td style="text-align: right; font-size: 15pt; font-weight: bold;">Rp {{ $rp($h['gaji_bersih'] ?? 0) }}</td>
    </tr>
</table>
<div class="terbilang">Terbilang: {{ \App\Support\Terbilang::rupiah((int) ($h['gaji_bersih'] ?? 0)) }}</div>

<table class="ttd" style="margin-top: 20px;">
    <tr>
        <td>Penerima<div class="garis">{{ $r['nama'] }}</div></td>
        <td style="width: 24%;"></td>
        <td>Keuangan<div class="garis">Admin Keuangan</div></td>
    </tr>
</table>

<div class="foot">
    <table>
        <tr>
            <td>Dokumen ini dihasilkan otomatis oleh sistem ERP PT Mirasa Food Industry dan bersifat rahasia.</td>
            <td style="text-align: right;">Dicetak {{ $dicetak }}</td>
        </tr>
    </table>
</div>
</body>
</html>
