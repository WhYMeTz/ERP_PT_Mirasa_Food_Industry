@extends('layouts.app')

@section('title', 'Jadwal Karyawan - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/manajemen/fingerprint/fingerprint-index.css') }}">
@endpush

@section('content')
<div class="fp-head">
    <div>
        <h1 class="fp-title">Jadwal Karyawan</h1>
        <p class="fp-sub">Kelompok kerja, kode shift, dan istirahat per karyawan per tanggal.</p>
    </div>
    <button type="button" class="fp-btn fp-btn-primary" onclick="fpOpenModal()">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M16 8l-4-4m0 0L8 8m4-4v12"/></svg>
        Impor Jadwal
    </button>
</div>

@if (session('jadwal_errors'))
    <div class="fp-alert fp-alert-err">
        <b>Baris yang dilewati (maks. 20 ditampilkan):</b>
        <ul style="margin:6px 0 0 18px">
            @foreach (session('jadwal_errors') as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
@endif

<div class="fp-stats">
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#0284c7">Baris Jadwal</div>
        <div class="fp-stat-value">{{ number_format($stat['baris']) }}</div>
        <div class="fp-stat-note">Karyawan per tanggal</div>
    </div>
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#0d9488">Karyawan Terjadwal</div>
        <div class="fp-stat-value">{{ number_format($stat['karyawan']) }}</div>
        <div class="fp-stat-note">Karyawan unik pada periode ini</div>
    </div>
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#7c3aed">Hari Terjadwal</div>
        <div class="fp-stat-value">{{ number_format($stat['hari']) }}</div>
        <div class="fp-stat-note">Tanggal yang punya jadwal</div>
    </div>
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#d97706">Istirahat Bawaan</div>
        <div class="fp-stat-value">{{ number_format($stat['tanpa_istirahat']) }}</div>
        <div class="fp-stat-note">Kolom istirahat kosong, pakai bawaan shift</div>
    </div>
</div>

<div class="fp-card">
    <form method="GET" class="fp-filter">
        <input type="text" name="q" value="{{ request('q') }}" class="fp-input fp-input-search" placeholder="Cari ID Absen / Nama">
        <select name="kelompok" class="fp-input">
            <option value="">-- Semua Kelompok --</option>
            @foreach ($kelompoks as $k) <option value="{{ $k }}" @selected(request('kelompok') === $k)>{{ $k }}</option> @endforeach
        </select>
        <select name="shift" class="fp-input">
            <option value="">-- Semua Shift --</option>
            @foreach ($shiftMap as $kode => $s) <option value="{{ $kode }}" @selected(request('shift') === $kode)>{{ $kode }}</option> @endforeach
        </select>
        <input type="date" name="dari" value="{{ $dari }}" class="fp-input">
        <span class="fp-muted">s/d</span>
        <input type="date" name="sampai" value="{{ $sampai }}" class="fp-input">
        <button type="submit" class="fp-btn fp-btn-primary">Filter</button>
        @if (request()->hasAny(['q', 'kelompok', 'shift', 'dari', 'sampai']))
            <a href="{{ route('manajemen.jadwal.index') }}" class="fp-btn fp-btn-soft">Reset</a>
        @endif
        <span class="fp-total">Total data: <b>{{ number_format($rows->total()) }}</b> baris</span>
    </form>
</div>

<div class="fp-card">
    <div class="fp-table-wrap">
        <table class="fp-table">
            <thead>
                <tr>
                    <th>No</th><th>Tanggal</th><th>ID Absen</th><th>Nama Karyawan</th>
                    <th>Kelompok Kerja</th><th>Shift</th><th style="text-align:center">Istirahat</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $r)
                    @php $s = $shiftMap[$r->kode_shift] ?? null; @endphp
                    <tr>
                        <td class="fp-muted">{{ $rows->firstItem() + $loop->index }}</td>
                        <td>{{ $r->tanggal->translatedFormat('D, d M Y') }}</td>
                        <td class="fp-mono fp-id">{{ $r->id_absen }}</td>
                        <td style="font-weight:600">{{ $r->nama }}</td>
                        <td>{{ $r->kelompok_kerja ?? '-' }}</td>
                        <td>
                            <span class="fp-badge fp-badge-info">{{ $r->kode_shift }}</span>
                            @if ($s && $s->jam_masuk)
                                <span class="fp-mono fp-muted">{{ substr($s->jam_masuk, 0, 5) }}-{{ substr($s->jam_pulang, 0, 5) }}</span>
                            @endif
                        </td>
                        <td style="text-align:center">
                            @if ($r->istirahat_jam !== null)
                                {{ rtrim(rtrim(number_format($r->istirahat_jam, 1, ',', ''), '0'), ',') }} jam
                            @else
                                <span class="fp-badge fp-badge-warn">Bawaan</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="fp-empty">Belum ada jadwal. Klik <b>Impor Jadwal</b> untuk memulai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($rows->hasPages()) <div class="fp-pager">{{ $rows->links() }}</div> @endif
</div>

{{-- MODAL IMPOR --}}
<div id="fpModal" class="fp-modal" onclick="if(event.target===this) fpCloseModal()">
    <div class="fp-modal-box fp-modal-lg">
        <form id="fpImportForm" method="POST" action="{{ route('manajemen.jadwal.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="fp-modal-head">
                <h3>Impor Jadwal Karyawan</h3>
                <button type="button" class="fp-modal-x" onclick="fpCloseModal()">&times;</button>
            </div>
            <div class="fp-modal-body">
                <div class="fp-info">
                    <b>Belum punya format file?</b> Unduh template yang sudah berisi contoh dan pilihan dropdown.
                    <div style="margin-top:8px">
                        <a href="{{ asset('templates/template_import_jadwal.xlsx') }}" download class="fp-btn fp-btn-soft" style="padding:6px 12px; font-size:13px">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M8 12l4 4m0 0l4-4m-4 4V4"/></svg>
                            Download Template Import
                        </a>
                    </div>
                </div>

                <label class="fp-label">File Excel <span>*</span></label>
                <div class="fp-file-box"><input type="file" id="fpFile" name="file" accept=".xlsx,.xls,.csv" required></div>
                <p class="fp-hint">Format: .xlsx, .xls, atau .csv. Maksimal 10 MB.</p>

                <div id="fpFileInfo" class="fp-info fp-info-ok" style="display:none;">
                    File dipilih: <b id="fpFileName"></b> (<span id="fpFileSize"></span>)
                </div>

                <details class="fp-guide">
                    <summary>Petunjuk pengisian</summary>
                    <div class="fp-guide-body">
                        <ol>
                            <li>Satu baris = satu karyawan pada satu tanggal. Isi hanya karyawan yang dijadwalkan pada tanggal itu.</li>
                            <li>Kelompok kerja, kode shift, dan istirahat ditentukan <b>per hari</b>, jadi boleh berbeda tiap hari untuk orang yang sama.</li>
                            <li><b>Istirahat_Jam</b>: 0, 1, atau 1,5. Kosong = memakai bawaan shift (A-F = 1 jam, G-M = 0).</li>
                            <li><b>ID_Absen</b> harus sama dengan ID Absensi di Master Karyawan (9 digit, format teks).</li>
                            <li><b>Driver</b>: kelompok tetap "Tidak Langsung". Aturan driver (1x finger = 1 hari hadir) dikenali dari Bagian/Divisi = Driver di Master Karyawan.</li>
                            <li>Hapus baris contoh di template sebelum diimpor.</li>
                            <li>Impor ulang pada Tanggal + ID_Absen yang sama akan menimpa baris lama.</li>
                        </ol>

                        <table class="fp-guide-table">
                            <thead><tr><th>Kode</th><th>Hari</th><th>Masuk</th><th>Pulang</th><th>Standar</th><th>Keterangan</th></tr></thead>
                            <tbody>
                                @foreach ($shiftMap as $kode => $s)
                                    <tr>
                                        <td><b>{{ $kode }}</b></td>
                                        <td>{{ $s->hari }}</td>
                                        <td>{{ $s->jam_masuk ? substr($s->jam_masuk, 0, 5) : '-' }}</td>
                                        <td>{{ $s->jam_pulang ? substr($s->jam_pulang, 0, 5) : '-' }}{{ $s->lintas_hari ? ' (+1 hari)' : '' }}</td>
                                        <td>{{ $s->standar_jam !== null ? rtrim(rtrim(number_format($s->standar_jam, 1, ',', ''), '0'), ',') . ' jam' : '-' }}</td>
                                        <td>{{ $s->keterangan }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            </div>
            <div class="fp-modal-foot">
                <button type="button" class="fp-btn fp-btn-soft" onclick="fpCloseModal()">Batal</button>
                <button type="submit" id="fpSubmit" class="fp-btn fp-btn-primary" disabled>Import</button>
            </div>
        </form>
    </div>
</div>

<script>
    const fpModal = document.getElementById('fpModal'), fpFile = document.getElementById('fpFile'), fpSubmit = document.getElementById('fpSubmit');
    function fpOpenModal()  { fpModal.classList.add('open'); }
    function fpCloseModal() { fpModal.classList.remove('open'); }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') fpCloseModal(); });
    fpFile.addEventListener('change', function () {
        const f = this.files[0], info = document.getElementById('fpFileInfo');
        if (!f) { info.style.display = 'none'; fpSubmit.disabled = true; return; }
        document.getElementById('fpFileName').textContent = f.name;
        document.getElementById('fpFileSize').textContent = f.size > 1048576 ? (f.size / 1048576).toFixed(1) + ' MB' : Math.ceil(f.size / 1024) + ' KB';
        info.style.display = 'block'; fpSubmit.disabled = false;
    });
    document.getElementById('fpImportForm').addEventListener('submit', () => { fpSubmit.disabled = true; fpSubmit.textContent = 'Memproses...'; });
</script>
@endsection