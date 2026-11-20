@extends('layouts.app')

@section('title', 'Payroll Karyawan - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/manajemen/fingerprint/fingerprint-index.css') }}">
    <style>
        .pr-grup { display:flex; align-items:center; gap:8px; }
        .pr-grup + .pr-grup { padding-left:10px; margin-left:2px; border-left:1px solid #e2e8f0; }
        .wm-box { max-width:1000px; width:94vw; }
        .wm-wrap { max-height:480px; overflow:auto; border:1px solid #e2e8f0; border-radius:10px; }
        .wm-wrap table { width:100%; border-collapse:collapse; font-size:13px; }
        .wm-wrap th { position:sticky; top:0; background:#f8fafc; text-align:left; padding:10px 12px; font-size:12px; color:#64748b; border-bottom:1px solid #e2e8f0; white-space:nowrap; }
        .wm-wrap td { padding:10px 12px; border-bottom:1px solid #f1f5f9; vertical-align:top; }
        .wm-wrap tr:last-child td { border-bottom:0; }
        .wm-nomor { font-family:ui-monospace,Consolas,monospace; white-space:nowrap; }
        .wm-pesan { max-width:260px; word-break:break-word; }
        .wm-kosong { text-align:center; padding:40px 12px; color:#64748b; }
        .wm-bar { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:10px; }
        .wm-bar .wm-cari { flex:1; min-width:220px; }
    </style>
@endpush

@section('content')
@php
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $f2 = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
    $badgeStatus = ['draft' => ['fp-badge-warn', 'Draft'], 'final' => ['fp-badge-info', 'Final'], 'dibayar' => ['fp-badge-ok', 'Dibayar']];
    [$bClass, $bText] = $badgeStatus[$status];
    $absensiUrl = route('manajemen.absensi.index', ['dari' => $awal, 'sampai' => $akhir]);
    $namaPeriode = \Carbon\Carbon::createFromFormat('!Y-m', $periode)->locale('id')->translatedFormat('F Y');
@endphp

<div class="fp-head">
    <div>
        <h1 class="fp-title">Payroll Karyawan</h1>
        <p class="fp-sub">Gaji bulanan dihitung dari absensi: gaji pokok menurut kehadiran, lembur, tunjangan, dan potongan.</p>
    </div>
    <form method="GET" class="fp-filter" style="margin:0">
        <b>Periode:</b>
        <input type="month" name="periode" value="{{ $periode }}" class="fp-input" onchange="this.form.submit()">
    </form>
</div>

@if (session('success')) <div class="fp-alert fp-alert-ok">{{ session('success') }}</div> @endif
@if (session('error'))   <div class="fp-alert fp-alert-err">{{ session('error') }}</div> @endif
@if ($errors->any())     <div class="fp-alert fp-alert-err">{{ $errors->first() }}</div> @endif
@if (session('import_errors'))
    <div class="fp-alert fp-alert-err">
        <b>Rincian kesalahan:</b>
        <ul style="margin:6px 0 0 18px">
            @foreach (session('import_errors') as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
@endif

<div class="fp-stats" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#0284c7">Jumlah Karyawan</div>
        <div class="fp-stat-value">{{ number_format($ringkasan['karyawan']) }}</div>
        <div class="fp-stat-note">Periode {{ $namaPeriode }}</div>
    </div>
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#0d9488">Total Pendapatan Karyawan</div>
        <div class="fp-stat-value" style="font-size:22px">{{ $rp($ringkasan['total_pendapatan']) }}</div>
        <div class="fp-stat-note">Semua karyawan</div>
    </div>
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#dc2626">Total Potongan Karyawan</div>
        <div class="fp-stat-value" style="font-size:22px">{{ $rp($ringkasan['total_potongan']) }}</div>
        <div class="fp-stat-note">BPJS dan Potongan Lainnya</div>
    </div>
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#7c3aed">Total Gaji Bersih Karyawan</div>
        <div class="fp-stat-value" style="font-size:22px">{{ $rp($ringkasan['total_bersih']) }}</div>
        <div class="fp-stat-note">Total Payment</div>
    </div>
</div>

{{-- STATUS PERIODE DAN AKSI --}}
<div class="fp-card">
    <div class="fp-filter" style="flex-wrap:wrap; row-gap:10px">
        <b>Status periode:</b>
        <span class="fp-badge {{ $bClass }}">{{ $bText }}</span>
        @if ($status === 'draft')
            <span class="fp-muted" style="font-size:13px">Dihitung langsung dari absensi. Finalkan untuk mengunci angka.</span>
        @elseif ($status === 'final')
            <span class="fp-muted" style="font-size:13px">Angka sudah dikunci dan tidak berubah.</span>
        @else
            <span class="fp-muted" style="font-size:13px">Sudah ditandai dibayar.</span>
        @endif

        <span style="flex:1"></span>

        @if ($rows->isNotEmpty())
            <div class="pr-grup">
                <a href="{{ route('manajemen.payroll.export', ['periode' => $periode]) }}" class="fp-btn fp-btn-soft">Export Excel</a>
                <a href="{{ route('manajemen.payroll.slip-semua', ['periode' => $periode]) }}" target="_blank" class="fp-btn fp-btn-soft">Cetak Semua Slip</a>
            </div>
        @endif

        @if ($status !== 'draft')
            <div class="pr-grup">
                <a href="{{ route('manajemen.payroll.multi.index', ['periode' => $periode]) }}" class="fp-btn fp-btn-soft">Multi Payroll BCA</a>
            </div>
        @endif

        @if ($status === 'draft' && $rows->isNotEmpty())
            <a href="{{ route('manajemen.payroll.template', ['periode' => $periode]) }}" class="fp-btn fp-btn-soft">Unduh Template</a>
            <button type="button" class="fp-btn fp-btn-soft" onclick="piOpen()">Import Excel</button>
        @endif

        @if ($status === 'draft')
            <form method="POST" action="{{ route('manajemen.payroll.finalkan') }}" style="margin:0"
                  onsubmit="return confirm('Finalkan payroll {{ $namaPeriode }}?\n\nAngka dikunci untuk semua karyawan dan slip tidak akan berubah lagi.')">
                @csrf <input type="hidden" name="periode" value="{{ $periode }}">
                <button type="submit" class="fp-btn fp-btn-primary" @disabled(!$ringkasan['bisa_final'])>Finalkan Periode</button>
            </form>
        @elseif ($status === 'final')
            <form method="POST" action="{{ route('manajemen.payroll.dibayar') }}" style="margin:0"
                  onsubmit="return confirm('Tandai payroll {{ $namaPeriode }} sudah dibayar?')">
                @csrf <input type="hidden" name="periode" value="{{ $periode }}">
                <button type="submit" class="fp-btn fp-btn-primary">Tandai Dibayar</button>
            </form>
            <form method="POST" action="{{ route('manajemen.payroll.buka-kunci') }}" style="margin:0"
                  onsubmit="return confirm('Buka kunci payroll {{ $namaPeriode }}?\n\nHasil yang dikunci dihapus dan periode kembali dihitung langsung dari absensi.')">
                @csrf <input type="hidden" name="periode" value="{{ $periode }}">
                <button type="submit" class="fp-btn fp-btn-danger">Buka Kunci</button>
            </form>
        @endif
    </div>
</div>

@if ($status === 'draft' && $rows->isNotEmpty() && !$ringkasan['bisa_final'])
    <div class="fp-alert fp-alert-err">
        <b>Belum bisa difinalkan:</b>
        <ul style="margin:6px 0 0 18px">
            @if ($ringkasan['perlu_dicek'] > 0)
                <li>{{ $ringkasan['perlu_dicek'] }} baris absensi berstatus "Perlu dicek". <a href="{{ $absensiUrl }}&status=Perlu dicek">Lihat di Absensi</a></li>
            @endif
            @if ($ringkasan['tanpa_jadwal'] > 0)
                <li>{{ $ringkasan['tanpa_jadwal'] }} baris absensi berstatus "Tanpa jadwal". <a href="{{ $absensiUrl }}&status=Tanpa jadwal">Lihat di Absensi</a></li>
            @endif
            @if ($ringkasan['tanpa_gaji'] > 0)
                <li>{{ $ringkasan['tanpa_gaji'] }} karyawan belum diisi gaji harian. Klik "Atur Komponen" pada baris yang bertanda "Belum diisi".</li>
            @endif
        </ul>
    </div>
@endif

{{-- FILTER --}}
<div class="fp-card">
    <form method="GET" class="fp-filter">
        <input type="hidden" name="periode" value="{{ $periode }}">
        <input type="text" name="q" value="{{ request('q') }}" class="fp-input fp-input-search" placeholder="Cari ID Absen / NIK / Nama">
        <button type="submit" class="fp-btn fp-btn-primary">Cari</button>
        @if (request()->filled('q'))
            <a href="{{ route('manajemen.payroll.index', ['periode' => $periode]) }}" class="fp-btn fp-btn-soft">Reset</a>
        @endif
        <span style="margin-left:auto; display:flex; align-items:center; gap:10px; flex-wrap:wrap">
            @if ($rows->isNotEmpty() && $status !== 'draft')
                <button type="button" class="fp-btn fp-btn-emerald" onclick="waBuka()">Kirim Slip via WhatsApp</button>
            @endif
            <button type="button" class="fp-btn fp-btn-soft" onclick="wmBuka()">
                Pesan WA Masuk @if ($waMasuk->where('periode', $periode)->isNotEmpty()) <span class="fp-badge fp-badge-info">{{ $waMasuk->where('periode', $periode)->count() }}</span> @endif
            </button>
            <span class="fp-total" style="margin-left:6px">Total data: <b>{{ number_format($rows->count()) }}</b> karyawan</span>
        </span>
    </form>
</div>

{{-- TABEL --}}
<div class="fp-card">
    <div class="fp-table-wrap">
        <table class="fp-table">
            <thead>
                <tr>
                    <th>No</th><th>ID Karyawan</th><th>Nama Karyawan</th>
                    <th class="fp-num">Kehadiran (hari)</th>
                    <th class="fp-num">Gaji Pokok</th>
                    <th class="fp-num">Lembur I</th>
                    <th class="fp-num">Lembur II</th>
                    <th class="fp-num">Tunj. Masa Kerja</th>
                    <th class="fp-num">Total Pendapatan</th><th class="fp-num">Total Potongan</th><th class="fp-num">Gaji Bersih</th>
                    <th style="text-align:center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $r)
                    @php $h = $r['hasil']; @endphp
                    <tr>
                        <td class="fp-muted">{{ $loop->iteration }}</td>
                        <td class="fp-mono fp-id">{{ $r['nik'] ?? $r['id_absen'] }}</td>
                        <td style="font-weight:600">
                            {{ $r['nama'] }}
                            <div class="fp-muted" style="font-size:12px;font-weight:400">{{ $r['kelompok'] ?? '-' }}</div>
                        </td>
                        <td class="fp-num">
                            <b>{{ $f2($h['total_kehadiran'] ?? 0) }}</b>
                            @if (($h['jam_kurang'] ?? 0) > 0)
                                <div class="fp-muted" style="font-size:12px">kurang {{ $f2($h['jam_kurang']) }} jam</div>
                            @endif
                            <div><a href="{{ route('manajemen.absensi.index', ['q' => $r['id_absen'], 'dari' => $awal, 'sampai' => $akhir]) }}" style="font-size:12px">Lihat absensi</a></div>
                            @if ($r['perlu_dicek'] > 0 || $r['tanpa_jadwal'] > 0)
                                <span class="fp-badge fp-badge-warn" title="Baris ini tidak ikut dihitung sampai dibereskan di Absensi">{{ $r['perlu_dicek'] + $r['tanpa_jadwal'] }} perlu dicek</span>
                            @endif
                        </td>
                        @if ($r['siap'])
                            <td class="fp-num">{{ $rp($h['gaji_pokok']) }}</td>
                            <td class="fp-num">
                                {{ $rp($h['honor_lembur_1']) }}
                                <div class="fp-muted" style="font-size:12px">{{ $f2($h['jam_lembur_1']) }} jam</div>
                            </td>
                            <td class="fp-num">
                                {{ $rp($h['honor_lembur_2']) }}
                                <div class="fp-muted" style="font-size:12px">{{ $f2($h['jam_lembur_2']) }} jam</div>
                            </td>
                            <td class="fp-num">
                                {{ $rp($h['tunjangan_masa_kerja']) }}
                                <div class="fp-muted" style="font-size:12px">{{ $h['masa_kerja_tahun'] }} thn</div>
                            </td>
                            <td class="fp-num">{{ $rp($h['total_pendapatan']) }}</td>
                            <td class="fp-num">{{ $rp($h['total_potongan']) }}</td>
                            <td class="fp-num"><b>{{ $rp($h['gaji_bersih']) }}</b></td>
                        @else
                            <td class="fp-num" colspan="7"><span class="fp-badge fp-badge-warn">Belum diisi</span></td>
                        @endif
                        <td style="text-align:center; white-space:nowrap">
                            @if (!$terkunci)
                                <button type="button" class="fp-btn fp-btn-primary" style="padding:6px 12px; font-size:13px"
                                    data-row="{{ json_encode(['id_absen' => $r['id_absen'], 'nik' => $r['nik'], 'nama' => $r['nama'], 'input' => $r['input'], 'catatan' => $r['catatan'], 'masa' => $h['masa_kerja_tahun'] ?? 0, 'tunj' => $h['tunjangan_masa_per_hari'] ?? 0, 'tersimpan' => $r['tersimpan']]) }}"
                                    onclick="prOpen(this)">Atur Komponen</button>
                            @endif
                            @if ($r['siap'])
                                <a href="{{ route('manajemen.payroll.slip', ['periode' => $periode, 'id_absen' => $r['id_absen']]) }}" target="_blank" class="fp-btn fp-btn-soft" style="padding:6px 12px; font-size:13px">Slip</a>
                                <a href="{{ route('manajemen.payroll.slip-pdf', ['periode' => $periode, 'id_absen' => $r['id_absen']]) }}" class="fp-btn fp-btn-soft" style="padding:6px 12px; font-size:13px" title="Unduh slip sebagai PDF">PDF</a>
                                @if ($status !== 'draft')
                                    <button type="button" class="fp-btn fp-btn-emerald" style="padding:6px 12px; font-size:13px" onclick="waKirimSatu('{{ $r['id_absen'] }}', this)">Kirim WA</button>
                                    @php
                                        $sw = $statusWa[$r['nik']] ?? null;
                                        $labelWa = ['terkirim_api' => ['fp-badge-info', 'Dikirim'], 'delivered' => ['fp-badge-ok', 'Sampai'], 'read' => ['fp-badge-ok', 'Dibaca'],
                                                    'failed' => ['fp-badge-err', 'Gagal'], 'dilewati' => ['fp-badge-warn', 'Dilewati']];
                                    @endphp
                                    @if ($sw && isset($labelWa[$sw->status]))
                                        <div><span class="fp-badge {{ $labelWa[$sw->status][0] }}" title="{{ $sw->detail }} ({{ $sw->updated_at->format('d/m H:i') }})">WA: {{ $labelWa[$sw->status][1] }}</span></div>
                                    @endif
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="12" class="fp-empty">Belum ada data absensi pada {{ $namaPeriode }}. Hitung absensi periode ini di menu <b>Absensi Karyawan</b> terlebih dahulu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL ATUR KOMPONEN --}}
<div id="prModal" class="fp-modal" style="display:none" onclick="if(event.target===this) prClose()">
    <div class="fp-modal-box fp-modal-lg">
        <form method="POST" action="{{ route('manajemen.payroll.simpan') }}">
            @csrf
            <input type="hidden" name="periode" value="{{ $periode }}">
            <input type="hidden" name="id_absen" id="pr_id_absen">
            <div class="fp-modal-head">
                <h3>Komponen Payroll <span id="pr_nama" style="font-weight:400"></span></h3>
                <button type="button" class="fp-modal-x" onclick="prClose()">&times;</button>
            </div>
            <div class="fp-modal-body">
                <div class="fp-info" id="pr_info"></div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px">
                    <div class="fp-field">
                        <label class="fp-label">Gaji Harian (Rp) <span>*</span></label>
                        <input type="text" inputmode="numeric" name="gaji_harian" id="pr_gaji_harian" class="fp-input pr-rupiah" style="width:100%" required>
                    </div>
                    <div class="fp-field">
                        <label class="fp-label">Tunjangan Jabatan (Rp)</label>
                        <input type="text" inputmode="numeric" name="tunjangan_jabatan" id="pr_tunjangan_jabatan" class="fp-input pr-rupiah" style="width:100%">
                    </div>
                    <div class="fp-field">
                        <label class="fp-label">Insentif Kerajinan (Rp)</label>
                        <input type="text" inputmode="numeric" name="insentif" id="pr_insentif" class="fp-input pr-rupiah" style="width:100%">
                    </div>
                    <div class="fp-field">
                        <label class="fp-label">Potongan Lainnya / Kasbon (Rp)</label>
                        <input type="text" inputmode="numeric" name="potongan_lainnya" id="pr_potongan_lainnya" class="fp-input pr-rupiah" style="width:100%">
                    </div>
                    <div class="fp-field">
                        <label class="fp-label">Potongan BPJS Kesehatan (Rp)</label>
                        <input type="text" inputmode="numeric" name="potongan_bpjs_kes" id="pr_potongan_bpjs_kes" class="fp-input pr-rupiah" style="width:100%">
                    </div>
                    <div class="fp-field">
                        <label class="fp-label">Potongan BPJS Ketenagakerjaan (Rp)</label>
                        <input type="text" inputmode="numeric" name="potongan_bpjs_tk" id="pr_potongan_bpjs_tk" class="fp-input pr-rupiah" style="width:100%">
                    </div>
                </div>
                <div class="fp-field">
                    <label class="fp-label">Catatan</label>
                    <input type="text" name="catatan" id="pr_catatan" maxlength="255" class="fp-input" style="width:100%">
                </div>

                <div class="fp-info" id="pr_tarif" style="margin-top:12px"></div>
            </div>
            <div class="fp-modal-foot">
                <button type="button" class="fp-btn fp-btn-soft" onclick="prClose()">Batal</button>
                <button type="submit" class="fp-btn fp-btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL IMPORT --}}
<div id="piModal" class="fp-modal" style="display:none" onclick="if(event.target===this) piClose()">
    <div class="fp-modal-box">
        <form method="POST" action="{{ route('manajemen.payroll.import') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="periode" value="{{ $periode }}">
            <div class="fp-modal-head">
                <h3>Import Komponen Payroll - {{ $namaPeriode }}</h3>
                <button type="button" class="fp-modal-x" onclick="piClose()">&times;</button>
            </div>
            <div class="fp-modal-body">
                <div class="fp-info">
                    Gunakan file dari tombol <b>Unduh Template</b>. Cocokkan berdasarkan <b>ID Karyawan</b>.
                    Kolom yang dikosongkan <b>tidak diubah</b>. Jika ada satu baris yang salah, seluruh import dibatalkan.
                </div>
                <div class="fp-field">
                    <label class="fp-label">File Excel (.xlsx / .xls / .csv) <span>*</span></label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" class="fp-input" style="width:100%" required>
                </div>
            </div>
            <div class="fp-modal-foot">
                <button type="button" class="fp-btn fp-btn-soft" onclick="piClose()">Batal</button>
                <button type="submit" class="fp-btn fp-btn-primary">Import</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL KIRIM WHATSAPP --}}
@php
    $waDaftar = $rows->where('siap', true)->map(function ($r) use ($statusWa) {
        $sw = $statusWa[$r['nik']] ?? null;

        return [
            'id_absen' => $r['id_absen'],
            'nama'     => $r['nama'],
            'terkirim' => $sw !== null && in_array($sw->status, \App\Models\Manajemen\TrxSlipWa::BERHASIL, true),
        ];
    })->values();
@endphp
@php
    $wmStatus = [
        'slip_terkirim'   => ['fp-badge-ok',   'Slip terkirim'],
        'bantuan'         => ['fp-badge-info', 'Dibalas petunjuk'],
        'tidak_ditemukan' => ['fp-badge-warn', 'Nomor tidak cocok'],
        'ambigu'          => ['fp-badge-warn', 'Nomor ganda'],
        'slip_belum_ada'  => ['fp-badge-warn', 'Slip belum ada'],
        'gagal'           => ['fp-badge-warn', 'Gagal'],
    ];
@endphp
<div id="wmModal" class="fp-modal" style="display:none" onclick="if(event.target===this) wmTutup()">
    <div class="fp-modal-box fp-modal-lg wm-box">
        <div class="fp-modal-head">
            <h3>Pesan WhatsApp Masuk</h3>
            <button type="button" class="fp-modal-x" onclick="wmTutup()">&times;</button>
        </div>
        <div class="fp-modal-body">
            @if ($waMasuk->isEmpty())
                <div class="wm-kosong">
                    <b>Belum ada pesan masuk</b><br>
                    <span style="font-size:13px">Pesan muncul di sini setelah karyawan mengirim "SLIP" ke nomor WhatsApp perusahaan.</span>
                </div>
            @else
                <div class="wm-bar">
                    <input type="search" id="wmCari" class="fp-input wm-cari" placeholder="Cari nomor, nama, atau NIK karyawan" oninput="wmSaring()" autocomplete="off">
                    <select id="wmFilter" class="fp-input" onchange="wmSaring()">
                        <option value="ini">Periode {{ $namaPeriode }}</option>
                        <option value="tanpa">Tanpa periode</option>
                        <option value="semua">Semua yang dimuat</option>
                    </select>
                </div>
                <div class="fp-muted" id="wmHitung" style="font-size:13px;margin-bottom:8px"></div>
                <div class="wm-wrap">
                    <table>
                        <thead>
                            <tr><th>Waktu</th><th>Nomor</th><th>Karyawan</th><th>Periode</th><th>Pesan</th><th>Hasil</th><th>Keterangan</th></tr>
                        </thead>
                        <tbody id="wmBody">
                            @foreach ($waMasuk as $m)
                                @php
                                    [$wmKelas, $wmTeks] = $wmStatus[$m->status_balasan] ?? ['fp-badge-info', $m->status_balasan ?: 'Diproses'];
                                    $wmNo    = preg_replace('/^62/', '0', (string) $m->nomor);
                                    $wmPer   = $m->periode ? \Carbon\Carbon::createFromFormat('!Y-m', $m->periode)->locale('id')->translatedFormat('M Y') : null;
                                    $wmCari  = mb_strtolower(trim(($m->nama ?? '') . ' ' . ($m->nik ?? '') . ' ' . $wmNo . ' ' . $m->nomor));
                                    $wmAngka = $wmNo . ' ' . $m->nomor;
                                @endphp
                                <tr data-per="{{ $m->periode ?? '' }}" data-cari="{{ $wmCari }}" data-angka="{{ $wmAngka }}">
                                    <td style="white-space:nowrap">
                                        {{ \Carbon\Carbon::parse($m->created_at)->format('d/m/Y') }}<br>
                                        <span class="fp-muted" style="font-size:12px">{{ \Carbon\Carbon::parse($m->created_at)->format('H:i') }}</span>
                                    </td>
                                    <td class="wm-nomor">{{ $wmNo }}</td>
                                    <td>
                                        @if ($m->nama)
                                            <b>{{ $m->nama }}</b><br><span class="fp-muted" style="font-size:12px">{{ $m->nik }}</span>
                                        @else
                                            <span class="fp-muted">-</span>
                                        @endif
                                    </td>
                                    <td style="white-space:nowrap">{{ $wmPer ?? '-' }}</td>
                                    <td class="wm-pesan">{{ $m->tipe === 'text' ? \Illuminate\Support\Str::limit($m->isi, 80) : '[' . ($m->tipe ?: 'lainnya') . ']' }}</td>
                                    <td style="white-space:nowrap"><span class="fp-badge {{ $wmKelas }}">{{ $wmTeks }}</span></td>
                                    <td class="fp-muted" style="font-size:12px">{{ $m->detail }}</td>
                                </tr>
                            @endforeach
                            <tr id="wmNihil" style="display:none"><td colspan="7" class="wm-kosong">Tidak ada pesan yang cocok dengan pencarian.</td></tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        <div class="fp-modal-foot">
            <button type="button" class="fp-btn fp-btn-soft" onclick="wmTutup()">Tutup</button>
        </div>
    </div>
</div>

<div id="waModal" class="fp-modal" style="display:none">
    <div class="fp-modal-box fp-modal-lg">
        <div class="fp-modal-head">
            <h3>Kirim Slip via WhatsApp - {{ $namaPeriode }}</h3>
            <button type="button" class="fp-modal-x" id="waTutup" onclick="waTutup()">&times;</button>
        </div>
        <div class="fp-modal-body">
            <div class="fp-info" id="waInfo"></div>
            <label style="display:flex;gap:8px;align-items:center;margin:10px 0">
                <input type="checkbox" id="waUlang"> Kirim ulang juga yang sudah terkirim
            </label>
            <div style="background:#e2e8f0;border-radius:6px;height:10px;overflow:hidden;margin:8px 0">
                <div id="waBar" style="height:10px;width:0;background:#10b981;transition:width .2s"></div>
            </div>
            <div id="waProgres" class="fp-muted" style="font-size:13px"></div>
            <div id="waLog" style="max-height:240px;overflow:auto;margin-top:8px;font-size:13px"></div>
        </div>
        <div class="fp-modal-foot">
            <button type="button" class="fp-btn fp-btn-soft" onclick="waTutup()" id="waBatal">Tutup</button>
            <button type="button" class="fp-btn fp-btn-emerald" id="waMulai" onclick="waMulai()">Mulai Kirim</button>
        </div>
    </div>
</div>

<script>
    const prFmt = n => String(n ?? '').replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    const prNum = id => parseFloat(String(document.getElementById(id).value).replace(/\D/g, '')) || 0;
    const prRp  = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
    const prFields = ['gaji_harian', 'tunjangan_jabatan', 'insentif', 'potongan_lainnya', 'potongan_bpjs_kes', 'potongan_bpjs_tk'];

    function prTarif() {
        const g = prNum('pr_gaji_harian'), t = g / 7;
        document.getElementById('pr_tarif').innerHTML = g > 0
            ? 'Tarif lembur per jam (gaji harian / 7): <b>' + prRp(t) + '</b> &middot; Lembur I per jam: <b>' + prRp(t * 1.5) + '</b> &middot; Lembur II per jam: <b>' + prRp(t * 2) + '</b>'
              + '<br><span class="fp-muted">Tarif lembur selalu memakai pembagi 7, termasuk hari Sabtu dan Security. Standar jam Kode Shift hanya dipakai untuk kehadiran dan potongan jam kerja.</span>'
            : 'Isi gaji harian untuk melihat tarif lembur otomatis.';
    }

    function prOpen(btn) {
        const d = JSON.parse(btn.dataset.row);
        document.getElementById('pr_id_absen').value = d.id_absen;
        document.getElementById('pr_nama').textContent = '- ' + d.nama + ' (' + (d.nik || d.id_absen) + ')';
        prFields.forEach(f => document.getElementById('pr_' + f).value = d.input[f] ? prFmt(Math.round(d.input[f])) : '');
        document.getElementById('pr_catatan').value = d.catatan || '';
        document.getElementById('pr_info').innerHTML =
            'Masa kerja sampai akhir periode: <b>' + d.masa + ' tahun</b> &middot; Tunjangan masa kerja: <b>' + prRp(d.tunj) + ' per hari hadir</b> (otomatis dari data master).'
            + (d.tersimpan ? '' : '<br>Isian awal disalin dari bulan sebelumnya (kasbon tidak disalin). Klik Simpan untuk menetapkannya.');
        prTarif();
        document.getElementById('prModal').style.display = 'flex';
    }
    function prClose() { document.getElementById('prModal').style.display = 'none'; }
    function piOpen() { document.getElementById('piModal').style.display = 'flex'; }
    function piClose() { document.getElementById('piModal').style.display = 'none'; }
    function wmBuka()  { document.getElementById('wmModal').style.display = 'flex'; wmSaring(); }
    function wmTutup() { document.getElementById('wmModal').style.display = 'none'; }

    // Saring daftar pesan masuk: periode + kata kunci (nama, NIK, atau nomor; nomor boleh 08xx atau 62xx)
    function wmSaring() {
        const body = document.getElementById('wmBody');
        if (!body) return;
        const kata  = (document.getElementById('wmCari').value || '').toLowerCase().trim().replace(/\s+/g, ' ');
        const angka = kata.replace(/\D/g, '');
        const f     = document.getElementById('wmFilter').value;
        let tampil = 0, total = 0;

        body.querySelectorAll('tr[data-per]').forEach(tr => {
            total++;
            const p = tr.dataset.per;
            let ok = f === 'semua' || (f === 'ini' && p === waPeriode) || (f === 'tanpa' && p === '');
            if (ok && kata) {
                ok = tr.dataset.cari.includes(kata) || (angka.length >= 3 && tr.dataset.angka.includes(angka));
            }
            tr.style.display = ok ? '' : 'none';
            if (ok) tampil++;
        });

        document.getElementById('wmNihil').style.display = tampil === 0 ? '' : 'none';
        document.getElementById('wmHitung').textContent = 'Menampilkan ' + tampil + ' dari ' + total + ' pesan yang dimuat.';
    }

    document.querySelectorAll('.pr-rupiah').forEach(i => i.addEventListener('input', () => { i.value = prFmt(i.value); prTarif(); }));
    document.addEventListener('keydown', e => { if (e.key === 'Escape') prClose(); });

    // ===== KIRIM SLIP VIA WHATSAPP =====
    const waUrl   = @json(route('manajemen.payroll.kirim-wa'));
    const waCsrf  = @json(csrf_token());
    const waPeriode = @json($periode);
    const waDaftar = @json($waDaftar);
    let waJalan = false;

    async function waPanggil(idAbsen, ulang) {
        const resp = await fetch(waUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': waCsrf },
            body: JSON.stringify({ periode: waPeriode, id_absen: idAbsen, ulang: ulang ? 1 : 0 }),
        });
        if (!resp.ok) {
            let pesan = 'HTTP ' + resp.status;
            try { const j = await resp.json(); pesan = j.message || pesan; } catch (e) {}
            return { ok: false, status: 'error', pesan };
        }
        return resp.json();
    }

    async function waKirimSatu(idAbsen, tombol) {
        const sudah = waDaftar.find(d => d.id_absen === idAbsen)?.terkirim;
        if (sudah && !confirm('Slip karyawan ini sudah pernah terkirim. Kirim ulang?')) return;
        tombol.disabled = true; const awal = tombol.textContent; tombol.textContent = 'Mengirim...';
        const h = await waPanggil(idAbsen, true);
        alert((h.ok ? 'Berhasil: ' : 'Tidak terkirim: ') + h.pesan);
        tombol.disabled = false; tombol.textContent = awal;
        if (h.ok) location.reload();
    }

    function waBuka() {
        const total = waDaftar.length, sudah = waDaftar.filter(d => d.terkirim).length;
        document.getElementById('waInfo').innerHTML =
            'Slip siap: <b>' + total + '</b> karyawan &middot; sudah terkirim: <b>' + sudah + '</b> &middot; belum: <b>' + (total - sudah) + '</b>.'
            + '<br>Setiap slip dikirim ke nomor di Master Karyawan memakai template WhatsApp yang disetujui Meta. Jangan tutup halaman ini selama proses berjalan.';
        document.getElementById('waBar').style.width = '0';
        document.getElementById('waProgres').textContent = '';
        document.getElementById('waLog').innerHTML = '';
        document.getElementById('waMulai').disabled = false;
        document.getElementById('waModal').style.display = 'flex';
    }
    function waTutup() {
        if (waJalan && !confirm('Pengiriman sedang berjalan. Hentikan dan tutup?')) return;
        document.getElementById('waModal').style.display = 'none';
        if (waJalan) { waJalan = false; }
        location.reload();
    }

    async function waMulai() {
        const ulang = document.getElementById('waUlang').checked;
        const antrean = waDaftar.filter(d => ulang || !d.terkirim);
        if (antrean.length === 0) { alert('Tidak ada slip yang perlu dikirim.'); return; }
        if (!confirm('Kirim ' + antrean.length + ' slip ke WhatsApp karyawan sekarang?')) return;

        waJalan = true;
        document.getElementById('waMulai').disabled = true;
        const log = document.getElementById('waLog');
        let ok = 0, gagal = 0, lewat = 0;

        for (let i = 0; i < antrean.length && waJalan; i++) {
            const d = antrean[i];
            document.getElementById('waProgres').textContent = 'Mengirim ' + (i + 1) + ' dari ' + antrean.length + ': ' + d.nama + '...';
            const h = await waPanggil(d.id_absen, ulang);
            if (h.ok) ok++; else if (h.status === 'dilewati') lewat++; else gagal++;
            const baris = document.createElement('div');
            baris.style.color = h.ok ? '#047857' : (h.status === 'dilewati' ? '#b45309' : '#b91c1c');
            baris.textContent = (h.ok ? 'OK  ' : (h.status === 'dilewati' ? 'LEWAT  ' : 'GAGAL  ')) + d.nama + ' - ' + h.pesan;
            log.appendChild(baris); log.scrollTop = log.scrollHeight;
            document.getElementById('waBar').style.width = Math.round(((i + 1) / antrean.length) * 100) + '%';
            await new Promise(r => setTimeout(r, 300));
        }

        waJalan = false;
        document.getElementById('waProgres').innerHTML = 'Selesai. Terkirim: <b>' + ok + '</b> &middot; dilewati: <b>' + lewat + '</b> &middot; gagal: <b>' + gagal + '</b>. Tutup jendela ini untuk melihat statusnya di tabel.';
    }
</script>
@endsection