@extends('layouts.app')

@section('title', 'Finger Print Karyawan - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/manajemen/fingerprint/fingerprint-index.css') }}">
@endpush

@section('content')

{{-- HEADER --}}
<div class="fp-head">
    <div>
        <h1 class="fp-title">Finger Print Karyawan</h1>
        <p class="fp-sub">Impor data mesin absensi. ...</p>
    </div>

    <button type="button" class="fp-btn fp-btn-primary" onclick="fpOpenModal()">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Impor Data Finger Print
    </button>
</div>

{{-- PESAN --}}
@if (session('success')) <div class="fp-alert fp-alert-ok">{{ session('success') }}</div> @endif
@if (session('error'))   <div class="fp-alert fp-alert-err">{{ session('error') }}</div> @endif
@error('file')           <div class="fp-alert fp-alert-err">{{ $message }}</div> @enderror

{{-- RINGKASAN --}}
<div class="fp-stats">
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#0284c7">Karyawan Terdata</div>
        <div class="fp-stat-value">{{ number_format($stat['karyawan']) }}</div>
        <div class="fp-stat-note">Karyawan unik pada periode ini</div>
    </div>
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#0d9488">Data Absensi Harian</div>
        <div class="fp-stat-value">{{ number_format($stat['hari_data']) }}</div>
        <div class="fp-stat-note">Baris karyawan per tanggal</div>
    </div>
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#d97706">Belum Finger Pulang</div>
        <div class="fp-stat-value">{{ number_format($stat['belum_pulang']) }}</div>
        <div class="fp-stat-note">Perlu dicek ke karyawan terkait</div>
    </div>
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#7c3aed">Jam Operasional</div>
        <div class="fp-stat-value">{{ number_format($stat['jam_operasional'], 1, ',', '.') }} Jam</div>
        <div class="fp-stat-note">
            @if ($stat['hari_operasional'] > 1)
                Rata-rata {{ number_format($stat['rata_operasional'], 1, ',', '.') }} jam/hari dari {{ $stat['hari_operasional'] }} hari
            @else
                Dari karyawan masuk pertama sampai pulang terakhir
            @endif
        </div>
    </div>
</div>

<div id="fpBulkBar" class="fp-bulkbar">
    <span><b id="fpSelCount">0</b> baris dipilih</span>
    <button type="button" class="fp-btn fp-btn-primary" onclick="fpOpenEdit()">Edit</button>
    <button type="button" class="fp-btn fp-btn-danger" onclick="fpBulkDelete()">Hapus</button>
    <button type="button" class="fp-btn fp-btn-soft" onclick="fpClearSel()">Batal pilih</button>
</div>

{{-- FILTER --}}
<div class="fp-card">
    <form method="GET" class="fp-filter">
        <input type="text" name="q" value="{{ request('q') }}" class="fp-input fp-input-search" placeholder="Cari ID Absen / Nama Karyawan">
        <input type="date" name="dari" value="{{ $dari }}" class="fp-input">
        <span class="fp-muted">s/d</span>
        <input type="date" name="sampai" value="{{ $sampai }}" class="fp-input">
        <button type="submit" class="fp-btn fp-btn-primary">Filter</button>
            @if (request()->hasAny(['q', 'dari', 'sampai']))
                <a href="{{ route('manajemen.fingerprint.index') }}" class="fp-btn fp-btn-soft">Reset</a>
            @endif
        <span class="fp-total">Total data: <b>{{ number_format($rows->total()) }}</b> baris</span>
    </form>
</div>

{{-- TABEL --}}
<div class="fp-card">
    <div class="fp-table-wrap">
        <table class="fp-table">
            <thead>
                <tr>
                    <th style="width:40px"><input type="checkbox" class="fp-chk" id="fpCheckAll"></th>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>ID Absen</th>
                    <th>Nama Karyawan</th>
                    <th>Scan Pertama</th>
                    <th>Jam Masuk</th>
                    <th>Scan Terakhir</th>
                    <th>Jam Pulang</th>
                    <th style="text-align:center">Total Jam Kerja</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $r)
                    <tr data-row>
                        <td><input type="checkbox" class="fp-chk fp-row-chk" value="{{ $r->id }}"></td>
                        <td class="fp-muted">{{ $rows->firstItem() + $loop->index }}</td>
                        <td>{{ $r->tanggal->translatedFormat('d M Y') }}</td>
                        <td class="fp-mono fp-id">{{ $r->id_absen }}</td>
                        <td style="font-weight:600">
                            {{ $r->nama }}
                            @if ($r->is_manual)
                                <span class="fp-badge fp-badge-manual" title="{{ $r->catatan }}">Manual</span>
                            @endif
                        </td>
                        <td class="fp-mono fp-muted">{{ $r->masuk_asli?->format('H:i:s') }}</td>
                        <td class="fp-mono fp-time-final">{{ $r->jam_masuk?->format('H:i') }}</td>
                        <td class="fp-mono fp-muted">{{ $r->pulang_asli?->format('H:i:s') ?? '-' }}</td>
                        <td class="fp-mono fp-time-final">
                            @if ($r->jam_pulang)
                                {{ $r->jam_pulang->format('H:i') }}
                            @else
                                <span class="fp-badge fp-badge-warn">Belum finger pulang</span>
                            @endif
                        </td>
                        <td style="text-align:center">
                            @if ($r->total_jam_label)
                                <span class="fp-badge fp-badge-info">{{ $r->total_jam_label }}</span>
                            @else
                                <span class="fp-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="fp-empty">Belum ada data. Klik <b>Impor Data Finger Print</b> untuk memulai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($rows->hasPages())
        <div class="fp-pager">{{ $rows->links() }}</div>
    @endif
</div>

{{-- MODAL IMPOR --}}
<div id="fpModal" class="fp-modal" onclick="if(event.target===this) fpCloseModal()">
    <div class="fp-modal-box">
        <form id="fpImportForm" method="POST" action="{{ route('manajemen.fingerprint.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="fp-modal-head">
                <h3>Impor Data Finger Print</h3>
                <button type="button" class="fp-modal-x" onclick="fpCloseModal()">&times;</button>
            </div>

            <div class="fp-modal-body">
                <div class="fp-info">
                    Pastikan file hanya berisi <b>satu sheet</b> data scan dengan header di baris 2:
                    <b>ID_absen, ID_mesin, Nama, Tanggal_Jam</b>.
                </div>

                <label class="fp-label">File Excel <span>*</span></label>
                <div class="fp-file-box">
                    <input type="file" id="fpFile" name="file" accept=".xlsx,.xls,.csv" required>
                </div>
                <p class="fp-hint">Format: .xlsx, .xls, atau .csv. Maksimal 10 MB.</p>

                <div id="fpFileInfo" class="fp-info fp-info-ok" style="display:none;">
                    File dipilih: <b id="fpFileName"></b> (<span id="fpFileSize"></span>)
                </div>

                <ul class="fp-list">
                    <li>Scan yang sama (ID dan waktu sama) tidak akan dobel saat diimpor ulang.</li>
                    <li>Baris tanpa ID Absen atau waktu akan dilewati.</li>
                </ul>
            </div>

            <div class="fp-modal-foot">
                <button type="button" class="fp-btn fp-btn-soft" onclick="fpCloseModal()">Batal</button>
                <button type="submit" id="fpSubmit" class="fp-btn fp-btn-primary" disabled>Import</button>
            </div>
        </form>
    </div>
</div>

<div id="fpEditModal" class="fp-modal" onclick="if(event.target===this) fpCloseEdit()">
    <div class="fp-modal-box">
        <form id="fpEditForm" method="POST" action="{{ route('manajemen.fingerprint.bulk-update') }}">
            @csrf
            <div class="fp-modal-head">
                <h3>Edit Absensi (<span id="fpEditCount">0</span> baris)</h3>
                <button type="button" class="fp-modal-x" onclick="fpCloseEdit()">&times;</button>
            </div>
            <div class="fp-modal-body">
                <div class="fp-info">Kolom yang dikosongkan <b>tidak diubah</b>. Jam yang diisi berlaku untuk semua baris terpilih.</div>

                <div class="fp-field">
                    <label class="fp-label">Jam Masuk</label>
                    <input type="time" name="jam_masuk" class="fp-input" style="width:100%">
                </div>
                <div class="fp-field">
                    <label class="fp-label">Jam Pulang</label>
                    <input type="time" name="jam_pulang" class="fp-input" style="width:100%">
                    <label class="fp-check-line" style="margin-top:8px">
                        <input type="checkbox" name="pulang_besok" value="1"> Pulang di hari berikutnya (shift malam)
                    </label>
                </div>
                <div class="fp-field">
                    <label class="fp-label">Catatan</label>
                    <input type="text" name="catatan" maxlength="255" class="fp-input" style="width:100%" placeholder="Contoh: lupa finger pulang, sesuai konfirmasi HRD">
                </div>
            </div>
            <div class="fp-modal-foot">
                <button type="button" class="fp-btn fp-btn-soft" onclick="fpCloseEdit()">Batal</button>
                <button type="submit" class="fp-btn fp-btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<form id="fpDeleteForm" method="POST" action="{{ route('manajemen.fingerprint.bulk-delete') }}" hidden>@csrf</form>

<script>
    const fpModal  = document.getElementById('fpModal');
    const fpFile   = document.getElementById('fpFile');
    const fpSubmit = document.getElementById('fpSubmit');

    function fpOpenModal()  { fpModal.classList.add('open'); }
    function fpCloseModal() { fpModal.classList.remove('open'); }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') fpCloseModal(); });

    fpFile.addEventListener('change', function () {
        const f = this.files[0];
        const info = document.getElementById('fpFileInfo');
        if (!f) { info.style.display = 'none'; fpSubmit.disabled = true; return; }

        document.getElementById('fpFileName').textContent = f.name;
        document.getElementById('fpFileSize').textContent =
            f.size > 1048576 ? (f.size / 1048576).toFixed(1) + ' MB' : Math.ceil(f.size / 1024) + ' KB';
        info.style.display = 'block';
        fpSubmit.disabled = false;
    });

    document.getElementById('fpImportForm').addEventListener('submit', function () {
        fpSubmit.disabled = true;
        fpSubmit.textContent = 'Memproses...';
    });

    const fpAll = document.getElementById('fpCheckAll');
    const fpBar = document.getElementById('fpBulkBar');
    const fpRowChecks = () => [...document.querySelectorAll('.fp-row-chk')];
    const fpSelected  = () => fpRowChecks().filter(c => c.checked);

    function fpRefreshSel() {
        const sel = fpSelected();
        document.getElementById('fpSelCount').textContent = sel.length;
        fpBar.classList.toggle('show', sel.length > 0);
        fpRowChecks().forEach(c => c.closest('tr').classList.toggle('fp-row-sel', c.checked));
        if (fpAll) fpAll.checked = sel.length > 0 && sel.length === fpRowChecks().length;
    }

    fpAll?.addEventListener('change', () => { fpRowChecks().forEach(c => c.checked = fpAll.checked); fpRefreshSel(); });
    document.addEventListener('change', e => { if (e.target.classList.contains('fp-row-chk')) fpRefreshSel(); });
    function fpClearSel() { fpRowChecks().forEach(c => c.checked = false); fpRefreshSel(); }

    function fpFillIds(form) {
        form.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());
        fpSelected().forEach(c => {
            const i = document.createElement('input');
            i.type = 'hidden'; i.name = 'ids[]'; i.value = c.value;
            form.appendChild(i);
        });
    }

    function fpOpenEdit() {
        document.getElementById('fpEditCount').textContent = fpSelected().length;
        document.getElementById('fpEditModal').classList.add('open');
    }
    function fpCloseEdit() { document.getElementById('fpEditModal').classList.remove('open'); }

    document.getElementById('fpEditForm').addEventListener('submit', function () { fpFillIds(this); });

    function fpBulkDelete() {
        const n = fpSelected().length;
        if (!n) return;
        if (!confirm('Hapus ' + n + ' data absensi terpilih?\n\nScan mentah karyawan pada tanggal tersebut ikut dihapus. Tindakan ini tidak bisa dibatalkan.')) return;
        const f = document.getElementById('fpDeleteForm');
        fpFillIds(f);
        f.submit();
    }
</script>

@endsection