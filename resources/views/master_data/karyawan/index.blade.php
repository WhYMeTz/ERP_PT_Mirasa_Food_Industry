@extends('layouts.app')

@section('title', 'Master Data Karyawan - ERP PT Mirasa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/master/karyawan/karyawan-index.css') }}">
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a;">Master Data Karyawan</h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">Kelola data profil staf operasional, produksi, gudang, purchasing, dan tim manajemen.</p>
    </div>
    <style>
        .kr-actions { display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; }
        .kr-btn { display: inline-flex; align-items: center; gap: 0.55rem; height: 40px; padding: 0 1rem 0 0.45rem; border-radius: 10px; font-size: 0.85rem; font-weight: 600; line-height: 1; text-decoration: none; cursor: pointer; white-space: nowrap; border: 1px solid transparent; transition: transform .15s, box-shadow .15s, background .15s; }
        .kr-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 14px -4px rgba(15,23,42,.25); }
        .kr-btn:active { transform: translateY(0); box-shadow: none; }
        .kr-ico { width: 28px; height: 28px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .kr-ico svg { width: 16px; height: 16px; }
        .kr-btn-green { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
        .kr-btn-green .kr-ico { background: #10b981; color: #fff; }
        .kr-btn-green:hover { background: #d1fae5; }
        .kr-btn-blue { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        .kr-btn-blue .kr-ico { background: #3b82f6; color: #fff; }
        .kr-btn-blue:hover { background: #dbeafe; }
        .kr-btn-rose { background: #fff1f2; color: #be123c; border-color: #fecdd3; }
        .kr-btn-rose .kr-ico { background: #f43f5e; color: #fff; }
        .kr-btn-rose:hover { background: #ffe4e6; }
        .kr-btn-primary { background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%); color: #fff; padding-right: 1.15rem; box-shadow: 0 4px 10px -2px rgba(3,105,161,.45); }
        .kr-btn-primary .kr-ico { background: rgba(255,255,255,.22); color: #fff; }
        .kr-btn-primary:hover { background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%); color: #fff; box-shadow: 0 8px 18px -4px rgba(3,105,161,.55); }
        .kr-sep { width: 1px; height: 26px; background: #e2e8f0; margin: 0 0.2rem; }
    </style>
    <div class="kr-actions">
        <button type="button" class="kr-btn kr-btn-green" onclick="openModal('modalImportExcel')"><span class="kr-ico"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/></svg></span> Import Excel</button>
        <a class="kr-btn kr-btn-blue" href="{{ route('master.karyawan.export-excel', [
            'search' => request('search'),
            'departemen' => request('departemen'),
            'kelompok' => request('kelompok'),
            'shift' => request('shift')
        ]) }}"><span class="kr-ico"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/></svg></span> Export Excel</a>
        <a class="kr-btn kr-btn-rose" href="{{ route('master.karyawan.cetak-pdf', [
            'search' => request('search'),
            'departemen' => request('departemen'),
            'kelompok' => request('kelompok'),
            'shift' => request('shift')
        ]) }}" target="_blank"><span class="kr-ico"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg></span> Cetak PDF</a>
        <span class="kr-sep"></span>
        <button type="button" class="kr-btn kr-btn-primary" onclick="openModal('modalTambahKaryawan')"><span class="kr-ico"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></span> Tambah Karyawan</button>
    </div>
</div>

<div class="card" style="background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1.5rem; margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 0;">
        <form action="{{ route('master.karyawan.index') }}" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; width: 100%;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari ID Karyawan, ID Absen, Nama..." class="form-control" style="padding: 0.5rem 0.75rem; max-width: 250px;">
            
            <select name="departemen" class="form-control" style="padding: 0.5rem 0.75rem; max-width: 180px;" onchange="this.form.submit()">
                <option value="">-- Semua Departemen --</option>
                @foreach ($departemenList as $key => $label)
                    <option value="{{ $key }}" {{ ($departemen ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select name="kelompok" class="form-control" style="padding: 0.5rem 0.75rem; max-width: 180px;" onchange="this.form.submit()">
                <option value="">-- Semua Kelompok --</option>
                @foreach (\App\Models\MasterData\MstKaryawan::KELOMPOK as $key => $label)
                    <option value="{{ $key }}" {{ ($kelompok ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select name="shift" class="form-control" style="padding: 0.5rem 0.75rem; max-width: 150px;" onchange="this.form.submit()">
                <option value="">-- Semua Shift --</option>
                <option value="SHIFT A" {{ ($shift ?? '') === 'SHIFT A' ? 'selected' : '' }}>Shift A</option>
                <option value="SHIFT B" {{ ($shift ?? '') === 'SHIFT B' ? 'selected' : '' }}>Shift B</option>
                <option value="NON SHIFT" {{ ($shift ?? '') === 'NON SHIFT' ? 'selected' : '' }}>Non Shift</option>
            </select>

            <button type="submit" class="btn btn-secondary">Filter</button>
            @if(!empty($search) || !empty($departemen) || !empty($kelompok) || !empty($shift))
                <a href="{{ route('master.karyawan.index') }}" class="btn btn-secondary" title="Reset Filter">&times;</a>
            @endif
            <div style="margin-left: auto; display: flex; align-items: center;">
                <span style="color: #64748b; font-size: 0.875rem;">Total Karyawan: <strong>{{ $karyawanList->total() }}</strong></span>
            </div>
        </form>
        
    </div>
</div>

@php $bisaEditMassal = Auth::user()->canEditMasterKaryawan(); @endphp

@if ($bisaEditMassal)
<style>
    #bulkBar { display: none; align-items: center; gap: .75rem; flex-wrap: wrap; margin-bottom: 1rem; padding: .7rem 1rem; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; color: #1e3a8a; font-size: .875rem; }
    #bulkBar.on { display: flex; }
    #bulkBar .bulk-count { font-weight: 700; }
    #bulkBar .bulk-spacer { flex: 1; }
    .kr-btn-danger-solid { background: #dc2626 !important; color: #ffffff !important; border-color: #b91c1c !important; box-shadow: 0 4px 10px -2px rgba(220,38,38,.45); }
    .kr-btn-danger-solid:hover { background: #b91c1c !important; color: #ffffff !important; }
    .row-check, #checkAll { width: 16px; height: 16px; cursor: pointer; accent-color: #0284c7; }
    tr.row-selected { background: #eff6ff; }
</style>
<div id="bulkBar">
    <span><span class="bulk-count" id="bulkCount">0</span> karyawan dipilih</span>
    <span class="bulk-spacer"></span>
    <button type="button" class="kr-btn kr-btn-primary" style="height:36px;" onclick="bukaEditMassal()">Edit Massal</button>
    <button type="button" class="kr-btn kr-btn-danger-solid" style="height:36px;padding-left:1rem;" onclick="hapusMassalKaryawan()">Hapus Massal</button>
    <button type="button" class="kr-btn kr-btn-blue" style="height:36px;padding-left:1rem;" onclick="batalPilih()">Batal pilih</button>
</div>
@endif

<div class="card">
    <div style="overflow-x: auto;">
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 1px solid #e2e8f0; text-align: left; background-color: #f8fafc;">
                    @if ($bisaEditMassal)
                        <th style="padding: 0.75rem; width: 36px;"><input type="checkbox" id="checkAll" title="Pilih semua di halaman ini"></th>
                    @endif
                    <th style="padding: 0.75rem; width: 50px;">No</th>
                    <th style="padding: 0.75rem;">ID Karyawan</th>
                    <th style="padding: 0.75rem;">ID Absensi</th>
                    <th style="padding: 0.75rem;">Nama Karyawan</th>
                    <th style="padding: 0.75rem;">Kelompok</th>
                    <th style="padding: 0.75rem;">Shift</th>
                    <th style="padding: 0.75rem;">Status Karyawan</th>
                    <th style="padding: 0.75rem;">Departemen</th>
                    <th style="padding: 0.75rem;">Divisi / Bagian</th> 
                    <th style="padding: 0.75rem;">Tanggal Masuk Kerja</th>
                    <th style="padding: 0.75rem;">Kontak / No Telp</th>
                    <th style="padding: 0.75rem;">Email</th>
                    <th style="padding: 0.75rem;">Bank</th>
                    <th style="padding: 0.75rem;">No. Rekening</th>
                    @if ($bisaEditMassal)
                        <th style="padding: 0.75rem;">NPWP/NIK &amp; PTKP</th>
                    @endif
                    <th style="padding: 1 rem;">Masa Kerja</th>
                    <th style="padding: 0.75rem;">Alamat</th>
                    <th style="padding: 0.75rem;">Akun Sistem &amp; Gudang</th>
                    <th style="padding: 0.75rem; width: 120px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($karyawanList as $index => $item)
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        @if ($bisaEditMassal)
                            <td style="padding: 0.75rem;"><input type="checkbox" class="row-check" value="{{ $item->karyawan_id }}" data-nama="{{ $item->karyawan_nm }}"></td>
                        @endif
                        <td style="padding: 0.75rem;">{{ $karyawanList->firstItem() + $index }}</td>
                        <td style="padding: 0.75rem;"><strong style="color: #0284c7; font-family: monospace; font-size: 0.9rem;">{{ $item->nik ?? '-' }}</strong></td>
                        <td style="padding: 0.75rem;">{{ $item->id_absensi ?? '-' }}</td>
                        <td style="padding: 0.75rem;">
                            <span style="font-weight: 600; color: #0f172a;">{{ $item->karyawan_nm }}</span>
                        </td>
                        <td style="padding: 0.75rem;">
                            @php $isLangsung = str_starts_with($item->kelompok ?? '', 'LANGSUNG'); @endphp
                            <span class="badge" style="background: {{ $isLangsung ? '#e0f2fe' : '#f1f5f9' }}; color: {{ $isLangsung ? '#0369a1' : '#475569' }}; font-weight: 600; font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 4px;">
                                {{ \App\Models\MasterData\MstKaryawan::KELOMPOK[$item->kelompok] ?? ($item->kelompok ?? '-') }}
                            </span>
                        </td>
                        <td style="padding: 0.75rem;">{{ $item->shift ?? '-' }}</td>
                        <td style="padding: 0.75rem;">{{ $item->status_kerja ?? 'Tetap' }}</td>
                        <td style="padding: 0.75rem;">
                            @php
                                $deptBg = '#f1f5f9';
                                $deptClr = '#475569';
                                $deptCd = $item->departemen_cd ?? '';
                                if ($deptCd === 'PRODUKSI') { $deptBg = '#eff6ff'; $deptClr = '#1e40af'; }
                                elseif ($deptCd === 'GUDANG') { $deptBg = '#fef3c7'; $deptClr = '#92400e'; }
                                elseif ($deptCd === 'PURCHASING') { $deptBg = '#ecfdf5'; $deptClr = '#065f46'; }
                                elseif ($deptCd === 'FINANCE') { $deptBg = '#fdf2f8'; $deptClr = '#9d174d'; }
                                elseif ($deptCd === 'QC') { $deptBg = '#f5f3ff'; $deptClr = '#5b21b6'; }
                                elseif ($deptCd === 'HRD') { $deptBg = '#fff1f2'; $deptClr = '#9f1239'; }
                                elseif ($deptCd === 'MANAJEMEN') { $deptBg = '#f0fdf4'; $deptClr = '#166534'; }
                            @endphp
                            <span class="badge" style="background: {{ $deptBg }}; color: {{ $deptClr }}; font-weight: 700; font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 4px;">
                                {{ $departemenList[$item->departemen_cd] ?? $item->departemen_cd }}
                            </span>
                        </td>
                        <td style="padding: 0.75rem;">{{ $item->jabatan_nm }}</td> 
                        <td style="padding: 0.75rem;">{{ $item->tgl_masuk ? \Carbon\Carbon::parse($item->tgl_masuk)->format('d/m/y') : '-' }}</td>
                        <td style="padding: 0.75rem;">{{ $item->telepon_no ?? '-' }}</td>
                        <td style="padding: 0.75rem; font-size: 0.85rem; color: #64748b;">{{ $item->email ?? '-' }}</td>
                        <td style="padding: 0.75rem;">{{ $item->bank_name ?? 'BCA' }}</td>
                        <td style="padding: 0.75rem; font-family: monospace;">{{ $item->no_rekening ?? '-' }}</td>
                        @if ($bisaEditMassal)
                            <td style="padding: 0.75rem; font-size: 0.85rem;">
                                @if ($item->npwp_nik && $item->status_ptkp)
                                    <span style="font-family: monospace;">{{ $item->npwp_nik }}</span>
                                    <span class="badge" style="background: #eef2ff; color: #3730a3; font-weight: 600; font-size: 0.75rem; padding: 0.15rem 0.45rem; border-radius: 4px;">{{ $item->status_ptkp }}</span>
                                @else
                                    <span class="badge" style="background: #fef3c7; color: #92400e; font-weight: 600; font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 4px;">
                                        Belum lengkap
                                    </span>
                                    <span style="display: block; font-size: 0.72rem; color: #92400e; margin-top: 0.15rem;">
                                        {{ !$item->npwp_nik ? 'NPWP/NIK kosong' : '' }}{{ !$item->npwp_nik && !$item->status_ptkp ? ' · ' : '' }}{{ !$item->status_ptkp ? 'PTKP kosong' : '' }}
                                    </span>
                                @endif
                            </td>
                        @endif
                        <td style="padding: 0.75rem; font-size: 0.85rem;">
                            @if($item->tgl_masuk)
                                {{ \Carbon\Carbon::parse($item->tgl_masuk)->diff(\Carbon\Carbon::now())->format('%y Tahun %m Bulan %d Hari') }}
                            @else
                                -
                            @endif
                        </td>
                        <td style="padding: 0.75rem; font-size: 0.85rem; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $item->alamat_txt }}">{{ $item->alamat_txt ?? '-' }}</td>
                        <td style="padding: 0.75rem;">
                            @if ($item->user)
                                <span class="badge" style="background: #ecfdf5; color: #065f46; font-weight: 600;">
                                    Akun: {{ $item->user->role_cd }}
                                </span>
                                @if ($item->user->gudang)
                                    <span style="display: block; font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                                        Gudang: {{ $item->user->gudang->gudang_nm }}
                                    </span>
                                @else
                                    <span style="display: block; font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                                        Seluruh Perusahaan
                                    </span>
                                @endif
                            @else
                                <span class="badge" style="background: #f1f5f9; color: #64748b;">Belum ada akun</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem; text-align: center; vertical-align: middle; white-space: nowrap;">
                            @php
                                $rowData = [
                                    'karyawan_id'   => $item->karyawan_id,
                                    'nik'           => $item->nik,
                                    'id_absensi'    => $item->id_absensi,
                                    'karyawan_nm'   => $item->karyawan_nm,
                                    'departemen_cd' => $item->departemen_cd,
                                    'departemen_nm' => $departemenList[$item->departemen_cd] ?? $item->departemen_cd,
                                    'jabatan_nm'    => $item->jabatan_nm,
                                    'kelompok'      => $item->kelompok,
                                    'shift'         => $item->shift,
                                    'status_kerja'  => $item->status_kerja,
                                    'tgl_masuk'     => $item->tgl_masuk ? \Carbon\Carbon::parse($item->tgl_masuk)->format('Y-m-d') : '',
                                    'telepon_no'    => $item->telepon_no,
                                    'email'         => $item->email,
                                    'bank_name'     => $item->bank_name,
                                    'no_rekening'   => $item->no_rekening,
                                    'npwp_nik'      => $item->npwp_nik,
                                    'status_ptkp'   => $item->status_ptkp,
                                    'alamat_txt'    => $item->alamat_txt,
                                ];
                            @endphp
                            @if (Auth::user()->canEditMasterKaryawan())
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editKaryawan({{ Js::from($rowData) }})" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-right: 0.25rem;">
                                    Edit
                                </button>
                            @endif
                            @if (Auth::user()->canDeleteMasterKaryawan())
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="openDeleteKaryawanModal({{ Js::from($rowData) }})" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                    Hapus
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $bisaEditMassal ? 20 : 18 }}" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            Belum ada data karyawan. Klik tombol <strong>"Tambah Karyawan"</strong> di atas untuk menambah data.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($karyawanList->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
        {{ $karyawanList->withQueryString()->links() }}
    </div>
@endif

{{-- MODALS --}}
@include('master_data.karyawan.partials.modal-create')
@include('master_data.karyawan.partials.modal-edit')
@include('master_data.karyawan.partials.modal-delete')
@include('master_data.karyawan.partials.modal-import') {{-- Tambahan modal import excel --}}
@if ($bisaEditMassal)
    @include('master_data.karyawan.partials.modal-bulk-edit')
@endif

{{-- Form tersembunyi untuk Hapus Massal --}}
<form id="formHapusMassal" method="POST" action="{{ route('master.karyawan.hapus-massal') }}" hidden>@csrf</form>
@endsection

@push('scripts')
    <script>
        window.appConfig = {
            karyawanBaseUrl: "{{ url('master-karyawan') }}",
            nextNIK: "{{ $nextNik }}"
        };
    </script>
    <script src="{{ asset('js/master/karyawan/karyawan-index.js') }}"></script>
@endpush