@extends('layouts.app')

@section('title', 'Matriks Hak Akses Peran - ERP PT Mirasa')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
            <span>Matriks Hak Akses & Wewenang Peran</span>
            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.75rem;">Role Permissions Matrix</span>
        </h1>
        <p style="color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">
            Atur menu apa saja yang dapat dibuka dan aksi operasional apa saja yang diizinkan untuk Admin Gudang, Staff Produksi, Purchasing, dll.
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            &larr; Kelola Akun Pengguna
        </a>
    </div>
</div>

{{-- TAB SWITCHER --}}
<div style="display: flex; gap: 0.5rem; margin-bottom: 1.25rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">
    <a href="{{ route('admin.users.index') }}" 
       style="padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.875rem; font-weight: 500; color: #64748b;">
        👥 Daftar Akun Pengguna
    </a>
    <a href="{{ route('admin.users.permissions') }}" 
       style="padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.875rem; font-weight: 700; background: #0284c7; color: #ffffff;">
        🛡️ Pengaturan Izin Peran (Permissions)
    </a>
</div>

<form action="{{ route('admin.users.permissions.update') }}" method="POST">
    @csrf

    <div class="card" style="margin-bottom: 1.5rem;">
        <div style="padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 0.875rem; color: #334155;">
                Centang kotak pada peran yang diberikan izin untuk mengakses menu atau melakukan aksi. <strong>Super Administrator</strong> selalu memiliki izin penuh.
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.25rem;">
                💾 Simpan Perubahan Hak Akses
            </button>
        </div>

        <div style="overflow-x: auto;">
            <table class="table-compact" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
                        <th style="padding: 0.875rem 1rem; text-align: left; min-width: 280px;">Menu & Hak Akses Fitur</th>
                        <th style="padding: 0.875rem 0.5rem; text-align: center; width: 130px;">
                            <div style="font-weight: 700; color: #dc2626;">SUPERADMIN</div>
                            <div style="font-size: 0.7rem; color: #64748b; font-weight: normal;">Direksi / IT</div>
                        </th>
                        @foreach ($matrix as $roleCd => $data)
                            @php
                                $badgeColor = '#0284c7';
                                if ($roleCd === 'STAFF_PRODUKSI') $badgeColor = '#ea580c';
                                elseif ($roleCd === 'PURCHASING') $badgeColor = '#d97706';
                                elseif ($roleCd === 'FINANCE') $badgeColor = '#059669';
                                elseif ($roleCd === 'QC') $badgeColor = '#7c3aed';
                            @endphp
                            <th style="padding: 0.875rem 0.5rem; text-align: center; width: 140px;">
                                <div style="font-weight: 700; color: {{ $badgeColor }}; font-size: 0.8125rem;">
                                    {{ $roleCd }}
                                </div>
                                <div style="font-size: 0.6875rem; color: #64748b; font-weight: normal; margin-top: 0.15rem;">
                                    {{ $data['name'] }}
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($modules as $moduleKey => $module)
                        {{-- MODULE SECTION HEADER --}}
                        <tr style="background: #f8fafc; border-top: 1.5px solid #e2e8f0; border-bottom: 1.5px solid #e2e8f0;">
                            <td colspan="{{ count($matrix) + 2 }}" style="padding: 0.65rem 1rem; font-weight: 700; color: #1e293b; font-size: 0.875rem;">
                                {{ $module['title'] }}
                            </td>
                        </tr>

                        {{-- PERMISSION ITEMS --}}
                        @foreach ($module['items'] as $permKey => $perm)
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;">
                                <td style="padding: 0.65rem 1rem;">
                                    <div style="font-weight: 600; color: #0f172a; font-size: 0.85rem;">
                                        {{ $perm['label'] }}
                                    </div>
                                    <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.15rem;">
                                        {{ $perm['desc'] }} &bull; <code style="font-size: 0.7rem; color: #0284c7;">{{ $permKey }}</code>
                                    </div>
                                </td>

                                {{-- SUPERADMIN (ALWAYS LOCKED AS CHECKED) --}}
                                <td style="text-align: center; background: #fff5f5; vertical-align: middle;">
                                    <span style="font-size: 0.8rem; font-weight: 700; color: #dc2626;" title="Superadmin selalu memiliki akses penuh">
                                        🔒 Penuh
                                    </span>
                                </td>

                                {{-- EDITABLE ROLES --}}
                                @foreach ($matrix as $roleCd => $data)
                                    @php
                                        $isChecked = !empty($data['permissions'][$permKey]);
                                    @endphp
                                    <td style="text-align: center; vertical-align: middle;">
                                        <label style="display: flex; align-items: center; justify-content: center; cursor: pointer; width: 100%; height: 100%; min-height: 28px;">
                                            <input type="checkbox" 
                                                   name="permissions[{{ $roleCd }}][]" 
                                                   value="{{ $permKey }}"
                                                   {{ $isChecked ? 'checked' : '' }}
                                                   style="width: 17px; height: 17px; accent-color: #0284c7; cursor: pointer;">
                                        </label>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="padding: 1rem 1.25rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.75rem;">
            <a href="{{ route('admin.users.permissions') }}" class="btn btn-secondary">
                Batal / Muat Ulang
            </a>
            <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.5rem; font-weight: 700;">
                💾 Simpan Perubahan Hak Akses
            </button>
        </div>
    </div>
</form>
@endsection
