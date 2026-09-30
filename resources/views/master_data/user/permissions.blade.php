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
        <div style="padding: 1rem 1.25rem; background: #ffffff; border-bottom: 1.5px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <div style="font-size: 0.875rem; color: #334155; font-weight: 600;">
                    Centang hak akses untuk masing-masing peran. Hak akses <strong>Edit</strong> dan <strong>Hapus</strong> dipisahkan tersendiri. <strong>Super Administrator</strong> otomatis memiliki akses penuh.
                </div>
                {{-- QUICK CATEGORY FILTER BUTTONS --}}
                <div style="display: flex; gap: 0.35rem; margin-top: 0.5rem; flex-wrap: wrap;" id="matrixFilterNav">
                    <button type="button" onclick="filterMatrixCategory('ALL', this)" class="matrix-filter-btn" style="font-size: 0.75rem; font-weight: 800; padding: 0.25rem 0.65rem; border-radius: 6px; border: 1.5px solid #0284c7; background: #0284c7; color: #ffffff; cursor: pointer;">
                        ✨ Semua (21)
                    </button>
                    <button type="button" onclick="filterMatrixCategory('edit', this)" class="matrix-filter-btn" style="font-size: 0.75rem; font-weight: 800; padding: 0.25rem 0.65rem; border-radius: 6px; border: 1.5px solid #fcd34d; background: #fffbeb; color: #b45309; cursor: pointer;">
                        ✏️ Edit &amp; Koreksi
                    </button>
                    <button type="button" onclick="filterMatrixCategory('delete', this)" class="matrix-filter-btn" style="font-size: 0.75rem; font-weight: 800; padding: 0.25rem 0.65rem; border-radius: 6px; border: 1.5px solid #fca5a5; background: #fef2f2; color: #dc2626; cursor: pointer;">
                        🗑️ Hapus &amp; Batal
                    </button>
                    <button type="button" onclick="filterMatrixCategory('create', this)" class="matrix-filter-btn" style="font-size: 0.75rem; font-weight: 800; padding: 0.25rem 0.65rem; border-radius: 6px; border: 1.5px solid #86efac; background: #f0fdf4; color: #15803d; cursor: pointer;">
                        ➕ Input &amp; Transaksi
                    </button>
                    <button type="button" onclick="filterMatrixCategory('view', this)" class="matrix-filter-btn" style="font-size: 0.75rem; font-weight: 800; padding: 0.25rem 0.65rem; border-radius: 6px; border: 1.5px solid #7dd3fc; background: #f0f9ff; color: #0284c7; cursor: pointer;">
                        👁️ Lihat / Read-Only
                    </button>
                    <button type="button" onclick="filterMatrixCategory('master', this)" class="matrix-filter-btn" style="font-size: 0.75rem; font-weight: 800; padding: 0.25rem 0.65rem; border-radius: 6px; border: 1.5px solid #d8b4fe; background: #faf5ff; color: #7c3aed; cursor: pointer;">
                        📁 Master Data
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.4rem; font-weight: 800; border-radius: 8px;">
                💾 Simpan Perubahan Hak Akses
            </button>
        </div>

        <div style="overflow-x: auto;">
            <table class="table-compact" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
                        <th style="padding: 0.875rem 1rem; text-align: left; min-width: 300px;">Menu &amp; Hak Akses Fitur</th>
                        <th style="padding: 0.875rem 0.5rem; text-align: center; width: 130px;">
                            <div style="font-weight: 800; color: #dc2626;">SUPERADMIN</div>
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
                                <div style="font-weight: 800; color: {{ $badgeColor }}; font-size: 0.8125rem;">
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
                        @php
                            $catKey = $module['category_key'] ?? 'other';
                        @endphp
                        {{-- MODULE SECTION HEADER --}}
                        <tr class="matrix-cat-row" data-cat="{{ $catKey }}" style="background: {{ $module['bg_color'] ?? '#f8fafc' }}; border-top: 2px solid {{ $module['border_color'] ?? '#e2e8f0' }}; border-bottom: 1.5px solid {{ $module['border_color'] ?? '#e2e8f0' }};">
                            <td colspan="{{ count($matrix) + 2 }}" style="padding: 0.75rem 1rem;">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-weight: 800; color: {{ $module['theme_color'] ?? '#1e293b' }}; font-size: 0.9rem;">
                                        {{ $module['title'] }}
                                    </span>
                                    <span style="font-size: 0.7rem; font-weight: 800; background: {{ $module['badge_bg'] ?? '#e2e8f0' }}; color: {{ $module['badge_text'] ?? '#334155' }}; padding: 0.15rem 0.5rem; border-radius: 6px; border: 1px solid {{ $module['border_color'] ?? '#cbd5e1' }};">
                                        {{ count($module['items']) }} Izin Fitur
                                    </span>
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                                    {{ $module['desc'] ?? '' }}
                                </div>
                            </td>
                        </tr>

                        {{-- PERMISSION ITEMS --}}
                        @foreach ($module['items'] as $permKey => $perm)
                            @php
                                $itemAction = $perm['action'] ?? 'OTHER';
                                $badgeStyle = 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;';
                                if ($itemAction === 'EDIT') $badgeStyle = 'background: #fffbeb; color: #b45309; border: 1px solid #fde68a;';
                                elseif ($itemAction === 'DELETE') $badgeStyle = 'background: #fef2f2; color: #dc2626; border: 1px solid #fca5a5;';
                                elseif ($itemAction === 'CREATE') $badgeStyle = 'background: #f0fdf4; color: #15803d; border: 1px solid #86efac;';
                                elseif ($itemAction === 'VIEW') $badgeStyle = 'background: #f0f9ff; color: #0284c7; border: 1px solid #7dd3fc;';
                                elseif ($itemAction === 'MANAGE') $badgeStyle = 'background: #faf5ff; color: #7c3aed; border: 1px solid #d8b4fe;';
                            @endphp
                            <tr class="matrix-item-row" data-cat="{{ $catKey }}" style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;">
                                <td style="padding: 0.65rem 1rem;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                                        <span style="font-weight: 700; color: #0f172a; font-size: 0.85rem;">
                                            {{ $perm['label'] }}
                                        </span>
                                        <span style="font-size: 0.65rem; font-weight: 800; padding: 0.1rem 0.45rem; border-radius: 4px; {{ $badgeStyle }} flex-shrink: 0;">
                                            {{ $itemAction }}
                                        </span>
                                    </div>
                                    <div style="font-size: 0.725rem; color: #64748b; margin-top: 0.15rem;">
                                        {{ $perm['desc'] }} &bull; <code style="font-size: 0.7rem; color: #0284c7;">{{ $permKey }}</code>
                                    </div>
                                </td>

                                {{-- SUPERADMIN (ALWAYS LOCKED AS CHECKED) --}}
                                <td style="text-align: center; background: #fff5f5; vertical-align: middle;">
                                    <span style="font-size: 0.8rem; font-weight: 800; color: #dc2626;" title="Superadmin selalu memiliki akses penuh">
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

<script>
    function filterMatrixCategory(catKey, btn) {
        document.querySelectorAll('.matrix-filter-btn').forEach(b => {
            b.style.background = '#ffffff';
            b.style.borderColor = '#cbd5e1';
            b.style.color = '#475569';
        });

        if (btn) {
            btn.style.background = '#0284c7';
            btn.style.borderColor = '#0284c7';
            btn.style.color = '#ffffff';
        }

        document.querySelectorAll('.matrix-cat-row, .matrix-item-row').forEach(row => {
            if (catKey === 'ALL' || row.getAttribute('data-cat') === catKey) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
@endsection
