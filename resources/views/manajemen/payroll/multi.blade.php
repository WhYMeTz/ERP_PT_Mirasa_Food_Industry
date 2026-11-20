@extends('layouts.app')

@section('title', 'Multi Payroll BCA - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/manajemen/fingerprint/fingerprint-index.css') }}">
    <style>
        /* Kartu bernomor (1, 2, 3), meniru tampilan form Penerimaan Gudang */
        .mp-sec { padding:0; margin-bottom:18px; }
        .mp-sec-head { padding:20px 24px 4px; }
        .mp-sec-judul { margin:0; font-size:18px; font-weight:700; color:#0f172a; }
        .mp-sec-sub { margin:4px 0 0; font-size:13px; color:#64748b; }
        .mp-sec-body { padding:16px 24px 24px; }
        .mp-sec-aksi { display:flex; gap:10px; align-items:center; flex-wrap:wrap; padding:16px 24px; border-top:1px solid #e2e8f0; background:#f8fafc; }
        .mp-sec-aksi:last-child { border-radius:0 0 14px 14px; }
        .mp-sec-sekat { border-top:1px solid #e2e8f0; }
        .mp-sub-judul { padding:16px 24px 8px; font-weight:600; }
        .mp-bagian { display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
        .mp-dua { display:grid; grid-template-columns:repeat(auto-fit,minmax(360px,1fr)); gap:18px; }
        .mp-kolom { border:1px solid #e2e8f0; border-radius:12px; padding:18px 20px; background:#fff; }
        .mp-kolom-baca { background:#f8fafc; }
        .mp-kolom-judul { margin:0 0 14px; font-size:14px; font-weight:700; color:#0f172a; }
        .mp-kolom-judul small { display:block; margin-top:2px; font-size:12px; font-weight:400; color:#64748b; }
        .mp-grid2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .mp-penuh { grid-column:1 / -1; }
        @media (max-width:560px) { .mp-grid2 { grid-template-columns:1fr; } }
    </style>
@endpush

@section('content')
@php
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $layak    = $data['layak'];
    $dilewati = $data['dilewati'];
    $tanpaBagian = $ringkasan['_tanpa_bagian'] ?? 0;
@endphp

<div class="fp-head">
    <div>
        <h1 class="fp-title">Multi Payroll BCA</h1>
        <p class="fp-sub">Buat file TXT transfer gaji untuk KlikBCA Bisnis, satu file per bagian. Hanya untuk payroll yang sudah Final.</p>
    </div>
    <form method="GET" class="fp-filter" style="margin:0">
        <b>Periode:</b>
        <input type="month" name="periode" value="{{ $periode }}" class="fp-input" onchange="this.form.submit()">
        <input type="hidden" name="bagian" value="{{ $bagian }}">
    </form>
</div>

@if (session('success')) <div class="fp-alert fp-alert-ok">{{ session('success') }}</div> @endif
@if (session('error'))   <div class="fp-alert fp-alert-err">{{ session('error') }}</div> @endif
@if ($errors->any())     <div class="fp-alert fp-alert-err">{{ $errors->first() }}</div> @endif

@if (! $siap)
    <div class="fp-alert fp-alert-err">
        Payroll <b>{{ $namaPeriode }}</b> belum Final atau belum ada datanya. Finalkan dulu di
        <a href="{{ route('manajemen.payroll.index', ['periode' => $periode]) }}">halaman Payroll</a>.
    </div>
@else

{{-- RINGKASAN (di atas) --}}
<div class="fp-stats" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#0284c7">Siap Ditransfer</div>
        <div class="fp-stat-value" id="mp_n">{{ number_format($layak->count()) }}</div>
        <div class="fp-stat-note">{{ $bagian }} &middot; {{ $namaPeriode }}</div>
    </div>
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#0d9488">Total Transfer</div>
        <div class="fp-stat-value" style="font-size:22px" id="mp_total">{{ $rp($data['total']) }}</div>
        <div class="fp-stat-note">Jumlah semua baris di file</div>
    </div>
    <div class="fp-stat">
        <div class="fp-stat-label" style="color:#d97706">Dilewati</div>
        <div class="fp-stat-value">{{ number_format($dilewati->count()) }}</div>
        <div class="fp-stat-note">Tidak masuk file, lihat alasan di bawah</div>
    </div>
</div>

{{-- 1. PILIH BAGIAN & CEK DATA --}}
<div class="fp-card mp-sec">
    <div class="mp-sec-head">
        <h2 class="mp-sec-judul">1. Pilih Bagian &amp; Cek Data</h2>
        <p class="mp-sec-sub">Satu file transfer dibuat untuk satu bagian. Ringkasan di atas ikut berubah sesuai bagian yang dipilih.</p>
    </div>
    <div class="mp-sec-body">
        <div class="mp-bagian">
            <b>Bagian:</b>
            @foreach ($daftarBagian as $b)
                @php $s = $ringkasan[$b] ?? ['layak' => 0, 'total' => 0, 'dilewati' => 0]; @endphp
                <a href="{{ route('manajemen.payroll.multi.index', ['periode' => $periode, 'bagian' => $b]) }}"
                   class="fp-btn {{ $b === $bagian ? 'fp-btn-primary' : 'fp-btn-soft' }}">
                    {{ $b }} &middot; {{ $s['layak'] }} orang
                    @if ($s['dilewati'] > 0) <span class="fp-badge fp-badge-warn">{{ $s['dilewati'] }} dilewati</span> @endif
                </a>
            @endforeach
        </div>
        @if ($tanpaBagian > 0)
            <div class="fp-alert fp-alert-err" style="margin:14px 0 0">
                <b>{{ $tanpaBagian }} karyawan</b> tidak masuk bagian mana pun karena isian Jabatan di Master Karyawan tidak dikenali
                (yang dikenali: {{ implode(', ', $daftarBagian) }}). Karyawan itu tidak ikut file transfer sampai Jabatannya diperbaiki.
            </div>
        @endif
    </div>
</div>

{{-- 2. PENGATURAN FILE TRANSFER --}}
<div class="fp-card mp-sec">
    <div class="mp-sec-head">
        <h2 class="mp-sec-judul">2. Pengaturan File Transfer</h2>
        <p class="mp-sec-sub">Kiri: data rekening dari sistem. Kanan: isian yang perlu Anda periksa sebelum mengunduh.</p>
    </div>
    <div class="mp-sec-body">
        <form method="POST" id="mp_form" action="{{ route('manajemen.payroll.multi.unduh') }}" onsubmit="return mpKonfirm()">
            @csrf
            <input type="hidden" name="periode" value="{{ $periode }}">
            <input type="hidden" name="bagian" value="{{ $bagian }}">

            <div class="mp-dua">
                {{-- KIRI: data dari pengaturan sistem (hanya dibaca) --}}
                <div class="mp-kolom mp-kolom-baca">
                    <h3 class="mp-kolom-judul">Rekening &amp; Perusahaan <small>Diambil dari pengaturan sistem, tidak bisa diubah di sini.</small></h3>
                    <div class="mp-grid2">
                        <div class="fp-field mp-penuh">
                            <label class="fp-label">Rekening Sumber (Debited Account)</label>
                            <input type="text" class="fp-input fp-mono" style="width:100%" value="{{ $config['rekening_sumber'] }}" readonly>
                        </div>
                        <div class="fp-field">
                            <label class="fp-label">Corporate ID</label>
                            <input type="text" class="fp-input fp-mono" style="width:100%" value="{{ $config['corporate_id'] }}" readonly>
                        </div>
                        <div class="fp-field">
                            <label class="fp-label">Company Code</label>
                            <input type="text" class="fp-input fp-mono" style="width:100%" value="{{ $config['company_code'] }}" readonly>
                        </div>
                    </div>
                </div>

                {{-- KANAN: isian transfer --}}
                <div class="mp-kolom">
                    <h3 class="mp-kolom-judul">Data Transfer <small>Isi atau periksa kolom bertanda <span style="color:#dc2626">*</span>.</small></h3>
                    <div class="mp-grid2">
                        <div class="fp-field">
                            <label class="fp-label">Header ID <span>*</span></label>
                            <input type="number" name="header_id" class="fp-input" style="width:100%" min="1" max="99999999" value="{{ old('header_id', $isian['header_id']) }}" required>
                        </div>
                        <div class="fp-field">
                            <label class="fp-label">Tanggal Transfer (Effective Date) <span>*</span></label>
                            <input type="date" name="tanggal_transfer" class="fp-input" style="width:100%" value="{{ old('tanggal_transfer', $isian['tanggal_transfer']) }}" required>
                        </div>
                        <div class="fp-field">
                            <label class="fp-label">Remarks 1 (nama kelompok) <span>*</span></label>
                            <input type="text" name="remarks1" class="fp-input" style="width:100%" maxlength="50" value="{{ old('remarks1', $isian['remarks1']) }}" required>
                        </div>
                        <div class="fp-field">
                            <label class="fp-label">Remarks 2 (bulan gaji) <span>*</span></label>
                            <input type="text" name="remarks2" class="fp-input" style="width:100%" maxlength="50" value="{{ old('remarks2', $isian['remarks2']) }}" required>
                        </div>
                    </div>
                </div>
            </div>
            <p class="fp-muted" style="font-size:13px; margin:14px 0 0">
                Nomor header terisi otomatis berikutnya. Setiap unduhan tercatat di riwayat, jadi nomor tidak bisa dobel.
            </p>
        </form>
    </div>
</div>

{{-- 3. PRATINJAU & UNDUH --}}
<div class="fp-card mp-sec">
    <div class="mp-sec-head">
        <h2 class="mp-sec-judul">3. Pratinjau Isi File &amp; Unduh</h2>
        <p class="mp-sec-sub">{{ $layak->count() }} baris untuk bagian {{ $bagian }}. Hilangkan centang untuk menahan seseorang dari file ini.</p>
    </div>
    <div class="fp-table-wrap" style="margin-top:12px">
        <table class="fp-table">
            <thead>
                <tr>
                    <th style="width:40px"><input type="checkbox" id="mp_all" checked title="Pilih / lepas semua"></th><th>No</th><th>Transaction ID</th><th>Transfer Type</th><th>Credited Account</th>
                    <th>Beneficiary</th><th class="fp-num">Amount</th><th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($layak as $r)
                    <tr class="mp-row">
                        <td><input type="checkbox" class="mp-ikut" name="ikut[]" value="{{ $r['id_absen'] }}" form="mp_form" data-jumlah="{{ $r['jumlah'] }}" checked></td>
                        <td class="fp-muted mp-no">{{ $loop->iteration }}</td>
                        <td class="fp-mono mp-id" data-awal="{{ substr($r['id_transaksi'], 0, 16) }}">{{ $r['id_transaksi'] }}</td>
                        <td>{{ $config['kode_bank'] }}</td>
                        <td class="fp-mono">{{ $r['rekening'] }}</td>
                        <td style="font-weight:600">{{ $r['nama_file'] }}</td>
                        <td class="fp-num">{{ number_format($r['jumlah'], 2, '.', '') }}</td>
                        <td>{{ $isian['remarks2'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="fp-muted" style="text-align:center; padding:24px">Tidak ada karyawan yang layak ditransfer di bagian ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mp-sec-aksi">
        <button type="submit" form="mp_form" class="fp-btn fp-btn-primary" @disabled($layak->isEmpty())>Unduh TXT {{ $bagian }}</button>
        <a href="{{ route('manajemen.payroll.multi.excel', ['periode' => $periode]) }}" class="fp-btn fp-btn-soft">Unduh Excel (semua bagian)</a>
    </div>
        
    

    @if ($dilewati->isNotEmpty())
        <div class="mp-sec-sekat">
            <div class="mp-sub-judul">Dilewati &mdash; tidak masuk file ({{ $dilewati->count() }})</div>
            <div class="fp-table-wrap">
                <table class="fp-table">
                    <thead><tr><th>No</th><th>NIK</th><th>Nama</th><th class="fp-num">Gaji Bersih</th><th>Alasan</th></tr></thead>
                    <tbody>
                        @foreach ($dilewati as $r)
                            <tr>
                                <td class="fp-muted">{{ $loop->iteration }}</td>
                                <td class="fp-mono">{{ $r['nik'] ?? '-' }}</td>
                                <td style="font-weight:600">{{ $r['nama'] }}</td>
                                <td class="fp-num">{{ $rp($r['jumlah']) }}</td>
                                <td><span class="fp-badge fp-badge-warn">{{ $r['alasan'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

@endif

@include('manajemen.payroll.partials.xml-bp21', ['periode' => $periode, 'bagian' => $bagian, 'remarks1' => $isian['remarks1']])

{{-- RIWAYAT --}}
<div class="fp-card mp-sec">
    <div class="mp-sec-head">
        <h2 class="mp-sec-judul">4. Riwayat Unduhan {{ $namaPeriode }}</h2>
        <p class="mp-sec-sub">Nomor Header ID yang sudah terpakai tidak bisa dipakai lagi.</p>
    </div>
    <div class="fp-table-wrap" style="margin-top:12px">
        <table class="fp-table">
            <thead>
                <tr><th>Header ID</th><th>Bagian</th><th>Nama File</th><th class="fp-num">Baris</th><th class="fp-num">Total</th><th>Tgl Transfer</th><th>Diunduh Oleh</th><th>Waktu</th></tr>
            </thead>
            <tbody>
                @forelse ($riwayat as $h)
                    <tr>
                        <td class="fp-mono"><b>{{ str_pad($h->header_id, 8, '0', STR_PAD_LEFT) }}</b></td>
                        <td>{{ $h->bagian }}</td>
                        <td class="fp-mono">{{ $h->nama_file }}</td>
                        <td class="fp-num">{{ $h->jumlah_baris }}</td>
                        <td class="fp-num">{{ $rp($h->total) }}</td>
                        <td>{{ $h->tanggal_transfer->format('d-m-Y') }}</td>
                        <td>{{ $h->dibuat_oleh ?? '-' }}</td>
                        <td class="fp-muted">{{ $h->created_at->format('d-m-Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="fp-muted" style="text-align:center; padding:20px">Belum ada file yang diunduh untuk periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
(function () {
    if (!document.getElementById('mp_n')) return; // periode belum siap: tidak ada tabel pratinjau
    const rp  = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
    const all = document.getElementById('mp_all');
    const box = () => Array.from(document.querySelectorAll('.mp-ikut'));

    function hitung() {
        let n = 0, total = 0;
        box().forEach(cb => {
            const tr = cb.closest('tr');
            const idCell = tr.querySelector('.mp-id');
            const noCell = tr.querySelector('.mp-no');
            if (cb.checked) {
                n++; total += parseFloat(cb.dataset.jumlah);
                idCell.textContent = idCell.dataset.awal + String(n).padStart(2, '0');
                noCell.textContent = n;
                tr.style.opacity = '';
            } else {
                idCell.textContent = '(ditahan)';
                noCell.textContent = '-';
                tr.style.opacity = '.45';
            }
        });
        document.getElementById('mp_n').textContent = n.toLocaleString('id-ID');
        document.getElementById('mp_total').textContent = rp(total);
        if (all) all.checked = n === box().length;
        window.mpN = n; window.mpTotal = total;
    }

    window.mpKonfirm = function () {
        if (!window.mpN) { alert('Pilih minimal satu karyawan.'); return false; }
        const h = document.getElementById('mp_form').header_id.value;
        const tahan = box().length - window.mpN;
        return confirm('Unduh file Multi Payroll {{ $bagian }}?\n\n' + window.mpN + ' orang, ' + rp(window.mpTotal)
            + (tahan ? '\n' + tahan + ' orang ditahan (tidak ikut file)' : '')
            + '\n\nNomor header ' + h + ' akan tercatat terpakai dan tidak bisa dipakai lagi.');
    };

    box().forEach(cb => cb.addEventListener('change', hitung));
    if (all) all.addEventListener('change', () => { box().forEach(cb => cb.checked = all.checked); hitung(); });
    hitung();
})();
</script>
@endsection
