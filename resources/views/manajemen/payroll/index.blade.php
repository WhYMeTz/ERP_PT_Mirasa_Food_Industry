@extends('layouts.app')

@section('title', 'Payroll Karyawan - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/manajemen/fingerprint/fingerprint-index.css') }}">
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
        <div class="fp-stat-note">BPJS dan kasbon</div>
    </div>
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#7c3aed">Total Gaji Bersih Karyawan</div>
        <div class="fp-stat-value" style="font-size:22px">{{ $rp($ringkasan['total_bersih']) }}</div>
        <div class="fp-stat-note">Yang dibayarkan</div>
    </div>
</div>

{{-- STATUS PERIODE DAN AKSI --}}
<div class="fp-card">
    <div class="fp-filter">
        <b>Status periode:</b>
        <span class="fp-badge {{ $bClass }}">{{ $bText }}</span>
        @if ($status === 'draft')
            <span class="fp-muted" style="font-size:13px">Dihitung langsung dari absensi. Finalkan untuk mengunci angka.</span>
        @elseif ($status === 'final')
            <span class="fp-muted" style="font-size:13px">Angka sudah dikunci dan tidak berubah walau absensi atau tarif berubah.</span>
        @else
            <span class="fp-muted" style="font-size:13px">Sudah ditandai dibayar.</span>
        @endif

        <span style="flex:1"></span>

        @if ($rows->isNotEmpty())
            <a href="{{ route('manajemen.payroll.export', ['periode' => $periode]) }}" class="fp-btn fp-btn-soft">Export Excel</a>
            <a href="{{ route('manajemen.payroll.slip-semua', ['periode' => $periode]) }}" target="_blank" class="fp-btn fp-btn-soft">Cetak Semua Slip</a>
        @endif
        
        @if ($status !== 'draft')
            <a href="{{ route('manajemen.payroll.multi.index', ['periode' => $periode]) }}" class="fp-btn fp-btn-soft">Multi Payroll BCA</a>
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
        <span class="fp-total">Total data: <b>{{ number_format($rows->count()) }}</b> karyawan</span>
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

<script>
    const prFmt = n => String(n ?? '').replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    const prNum = id => parseFloat(String(document.getElementById(id).value).replace(/\D/g, '')) || 0;
    const prRp  = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
    const prFields = ['gaji_harian', 'tunjangan_jabatan', 'insentif', 'potongan_lainnya', 'potongan_bpjs_kes', 'potongan_bpjs_tk'];

    function prTarif() {
        const g = prNum('pr_gaji_harian'), t = g / 7;
        document.getElementById('pr_tarif').innerHTML = g > 0
            ? 'Tarif per jam (standar 7 jam): <b>' + prRp(t) + '</b> &middot; Lembur I per jam: <b>' + prRp(t * 1.5) + '</b> &middot; Lembur II per jam: <b>' + prRp(t * 2) + '</b>'
              + '<br><span class="fp-muted">Shift dengan standar lain (Sabtu 5,5 jam, Security 12 jam) memakai standar Kode Shift-nya.</span>'
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

    document.querySelectorAll('.pr-rupiah').forEach(i => i.addEventListener('input', () => { i.value = prFmt(i.value); prTarif(); }));
    document.addEventListener('keydown', e => { if (e.key === 'Escape') prClose(); });
</script>
@endsection