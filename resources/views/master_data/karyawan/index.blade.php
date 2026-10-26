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
    <div style="position: relative;">
        <button type="button" id="btnKelolaData" onclick="toggleKelolaMenu(event)" style="background-color: #007bff; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;">
            Kelola Data &#9662;
        </button>
        <ul id="kelolaMenu" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 4px; min-width: 220px; background: #fff; border: 1px solid #e2e8f0; border-radius: 0.375rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); padding: 0.5rem 0; z-index: 50; list-style: none;">
            <!-- KELOMPOK 1: INPUT DATA -->
            <li>
                <a href="#" onclick="closeKelolaMenu(); openModal('modalTambahKaryawan'); return false;" style="display: block; padding: 0.5rem 1rem; color: #067e06; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                    ➕ Tambah Karyawan
                </a>
            </li>
            <li>
                <a href="#" onclick="closeKelolaMenu(); openModal('modalImportExcel'); return false;" style="display: block; padding: 0.5rem 1rem; color: #067e06; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                    📥 Import dari Excel
                </a>
            </li>

            <!-- GARIS PEMBATAS TENGAH -->
            <li style="margin: 0.4rem 0; border-top: 1px solid #e2e8f0;"></li>

            <!-- KELOMPOK 2: OUTPUT DATA -->
            <li>
                <a href="{{ route('master.karyawan.export-excel', [
                    'search' => request('search'),
                    'departemen' => request('departemen'),
                    'kelompok' => request('kelompok'),
                    'shift' => request('shift')
                ]) }}" style="display: block; padding: 0.5rem 1rem; color: #0284c7; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                    📤 Export ke Excel
                </a>
            </li>
            <li>
                <a href="{{ route('master.karyawan.cetak-pdf', [
                    'search' => request('search'),
                    'departemen' => request('departemen'),
                    'kelompok' => request('kelompok'),
                    'shift' => request('shift')
                ]) }}" target="_blank" style="display: block; padding: 0.5rem 1rem; color: #0284c7; font-weight: 500; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#f0f9ff'" onmouseout="this.style.background='transparent'">
                    📄 Cetak Laporan PDF
                </a>
            </li>
        </ul>
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

<div class="card">
    <div style="overflow-x: auto;">
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 1px solid #e2e8f0; text-align: left; background-color: #f8fafc;">
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
                    <th style="padding: 1 rem;">Masa Kerja</th>
                    <th style="padding: 0.75rem;">Alamat</th>
                    <th style="padding: 0.75rem;">Akun Sistem &amp; Gudang</th>
                    <th style="padding: 0.75rem; width: 120px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($karyawanList as $index => $item)
                    <tr style="border-bottom: 1px solid #e2e8f0;">
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
                        <td colspan="18" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                            Belum ada data karyawan. Klik tombol <strong>"Kelola Data"</strong> di atas untuk menambah data.
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
<script>
    function toggleKelolaMenu(e) {
        e.stopPropagation();
        const menu = document.getElementById('kelolaMenu');
        if (menu) menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
    }
    function closeKelolaMenu() {
        const menu = document.getElementById('kelolaMenu');
        if (menu) menu.style.display = 'none';
    }
    document.addEventListener('click', closeKelolaMenu);
</script>
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
