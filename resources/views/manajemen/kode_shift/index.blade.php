@extends('layouts.app')

@section('title', 'Kode Shift - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/manajemen/fingerprint/fingerprint-index.css') }}">
@endpush

@section('content')
@php $f = fn ($v) => rtrim(rtrim(number_format((float) $v, 1, ',', ''), '0'), ','); @endphp

<div class="fp-head">
    <div>
        <h1 class="fp-title">Kode Shift</h1>
        <p class="fp-sub">Aturan jam kerja tiap kode shift. Kode baru langsung bisa dipakai di jadwal dan absensi.</p>
    </div>
    <button type="button" class="fp-btn fp-btn-primary" onclick="ksNew()">+ Tambah Kode Shift</button>
</div>

@if ($errors->any())
    <div class="fp-alert fp-alert-err">
        @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
@endif

<div class="fp-info" style="margin-bottom:16px">
    Mengubah jam kode yang <b>sudah dipakai</b> akan mengubah hasil jika absensi lama dihitung ulang.
    Untuk aturan yang berlaku mulai tanggal tertentu, buat <b>kode baru</b> dan nonaktifkan kode lama.
</div>

<div class="fp-card">
    <div class="fp-table-wrap">
        <table class="fp-table">
            <thead>
                <tr>
                    <th>Kode</th><th>Hari</th><th>Masuk</th><th>Pulang</th>
                    <th style="text-align:center">Istirahat</th><th style="text-align:center">Standar</th>
                    <th>Jenis</th><th>Keterangan</th><th style="text-align:center">Dipakai</th><th>Status</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($shifts as $s)
                    <tr style="{{ $s->aktif ? '' : 'opacity:.55' }}">
                        <td><span class="fp-badge fp-badge-info">{{ $s->kode }}</span></td>
                        <td>{{ $s->hari }}</td>
                        <td class="fp-mono">{{ $s->jam_masuk ? substr($s->jam_masuk, 0, 5) : '-' }}</td>
                        <td class="fp-mono">
                            {{ $s->jam_pulang ? substr($s->jam_pulang, 0, 5) : '-' }}
                            @if ($s->lintas_hari) <span class="fp-badge fp-badge-warn">+1 hari</span> @endif
                        </td>
                        <td style="text-align:center">{{ $f($s->istirahat_default) }}</td>
                        <td style="text-align:center">{{ $s->standar_jam !== null ? $f($s->standar_jam) : '-' }}</td>
                        <td>{{ ucfirst($s->jenis) }}</td>
                        <td class="fp-muted">{{ $s->keterangan }}</td>
                        <td style="text-align:center">{{ number_format($pakai[$s->kode] ?? 0) }} baris</td>
                        <td>
                            <span class="fp-badge {{ $s->aktif ? 'fp-badge-ok' : 'fp-badge-err' }}">{{ $s->aktif ? 'Aktif' : 'Nonaktif' }}</span>
                        </td>
                        <td style="white-space:nowrap">
                            <button type="button" class="fp-btn fp-btn-soft" style="padding:5px 12px" onclick='ksEdit(@json($s), {{ $pakai[$s->kode] ?? 0 }})'>Edit</button>
                            @if (($pakai[$s->kode] ?? 0) === 0)
                                <form method="POST" action="{{ route('manajemen.kode-shift.destroy', $s) }}" style="display:inline"
                                      onsubmit="return confirm('Hapus kode {{ $s->kode }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="fp-btn fp-btn-danger" style="padding:5px 12px">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL --}}
<div id="ksModal" class="fp-modal" onclick="if(event.target===this) ksClose()">
    <div class="fp-modal-box fp-modal-lg">
        <form id="ksForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="ksMethod" value="POST">
            <div class="fp-modal-head">
                <h3 id="ksTitle">Tambah Kode Shift</h3>
                <button type="button" class="fp-modal-x" onclick="ksClose()">&times;</button>
            </div>
            <div class="fp-modal-body">
                <div id="ksWarn" class="fp-info" style="display:none">
                    Kode ini sudah dipakai di <b id="ksN">0</b> baris jadwal. Nama kode tidak bisa diganti, dan perubahan jam
                    berlaku untuk semua periode saat dihitung ulang.
                </div>

                <div class="fp-field">
                    <label class="fp-label">Kode <span>*</span></label>
                    <input type="text" name="kode" id="ksKode" maxlength="5" class="fp-input" style="width:100%; text-transform:uppercase" required>
                </div>
                <div class="fp-field">
                    <label class="fp-label">Hari berlaku <span>*</span></label>
                    <input type="text" name="hari" id="ksHari" maxlength="30" class="fp-input" style="width:100%" placeholder="Contoh: Senin-Jumat" required>
                </div>
                <div class="fp-field">
                    <label class="fp-label">Jenis <span>*</span></label>
                    <select name="jenis" id="ksJenis" class="fp-input" style="width:100%" onchange="ksToggle()">
                        <option value="normal">Normal (dihitung dari jam masuk dan pulang)</option>
                        <option value="security">Security (jam panjang, dihitung seperti normal)</option>
                        <option value="minggu">Minggu (seluruh jam kerja = lembur)</option>
                        <option value="driver">Driver (1x finger = 1 hari hadir)</option>
                        <option value="libur">Libur (tidak dihitung)</option>
                    </select>
                </div>
                <div id="ksJam">
                    <div style="display:flex; gap:12px">
                        <div class="fp-field" style="flex:1">
                            <label class="fp-label">Jam masuk</label>
                            <input type="time" name="jam_masuk" id="ksMasuk" class="fp-input" style="width:100%">
                        </div>
                        <div class="fp-field" style="flex:1">
                            <label class="fp-label">Jam pulang</label>
                            <input type="time" name="jam_pulang" id="ksPulang" class="fp-input" style="width:100%">
                        </div>
                    </div>
                    <label class="fp-check-line" style="margin-bottom:14px">
                        <input type="checkbox" name="lintas_hari" id="ksLintas" value="1"> Pulang di hari berikutnya (shift malam)
                    </label>
                </div>
                <div class="fp-field">
                    <label class="fp-label">Istirahat bawaan Senin-Kamis (jam)</label>
                    <input type="number" name="istirahat_default" id="ksIst" step="0.5" min="0" max="3" value="0" class="fp-input" style="width:100%">
                </div>
                <div style="display:flex; gap:12px">
                    <div class="fp-field" style="flex:1">
                        <label class="fp-label">Istirahat Jumat (jam)</label>
                        <input type="number" name="istirahat_jumat" id="ksJumat" step="0.5" min="0" max="3" class="fp-input" style="width:100%" placeholder="Kosong = sama">
                    </div>
                    <div class="fp-field" style="flex:1">
                        <label class="fp-label">Istirahat Sabtu (jam)</label>
                        <input type="number" name="istirahat_sabtu" id="ksSabtu" step="0.5" min="0" max="3" class="fp-input" style="width:100%" placeholder="Kosong = sama">
                    </div>
                </div>
                <div class="fp-field">
                    <label class="fp-label">Keterangan</label>
                    <input type="text" name="keterangan" id="ksKet" maxlength="255" class="fp-input" style="width:100%">
                </div>
                <label class="fp-check-line">
                    <input type="checkbox" name="aktif" id="ksAktif" value="1" checked> Aktif (bisa dipilih di jadwal baru)
                </label>
            </div>
            <div class="fp-modal-foot">
                <button type="button" class="fp-btn fp-btn-soft" onclick="ksClose()">Batal</button>
                <button type="submit" class="fp-btn fp-btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    const ksModal = document.getElementById('ksModal'), ksForm = document.getElementById('ksForm');
    const $ = id => document.getElementById(id);

    function ksToggle() {
        const berjam = ['normal', 'security'].includes($('ksJenis').value);
        $('ksJam').style.display = berjam ? 'block' : 'none';
    }
    function ksClose() { ksModal.classList.remove('open'); }

    function ksNew() {
        ksForm.action = @json(route('manajemen.kode-shift.store'));
        $('ksMethod').value = 'POST';
        $('ksTitle').textContent = 'Tambah Kode Shift';
        $('ksWarn').style.display = 'none';
        ksForm.reset();
        $('ksKode').readOnly = false; $('ksAktif').checked = true;
        ksToggle(); ksModal.classList.add('open');
    }

    function ksEdit(s, dipakai) {
        ksForm.action = @json(url('manajemen/kode-shift')) + '/' + s.id;
        $('ksMethod').value = 'PUT';
        $('ksTitle').textContent = 'Edit Kode Shift ' + s.kode;
        $('ksKode').value = s.kode; $('ksKode').readOnly = dipakai > 0;
        $('ksHari').value = s.hari; $('ksJenis').value = s.jenis;
        $('ksMasuk').value = s.jam_masuk ? s.jam_masuk.substring(0, 5) : '';
        $('ksPulang').value = s.jam_pulang ? s.jam_pulang.substring(0, 5) : '';
        $('ksLintas').checked = !!s.lintas_hari;
        $('ksIst').value = s.istirahat_default; $('ksKet').value = s.keterangan || '';
        $('ksJumat').value = s.istirahat_jumat ?? '';
        $('ksSabtu').value = s.istirahat_sabtu ?? '';
        $('ksAktif').checked = !!s.aktif;
        $('ksN').textContent = dipakai; $('ksWarn').style.display = dipakai > 0 ? 'block' : 'none';
        ksToggle(); ksModal.classList.add('open');
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') ksClose(); });
</script>
@endsection