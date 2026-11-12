<div class="laporan-filter-card no-print">
    {{-- Grup Pill Preset Periode --}}
    <div class="filter-presets-group">
        @php
            $currentPreset = $dateInfo['preset'] ?? 'this_month';
        @endphp
        <span style="font-size: 0.725rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-right: 0.25rem;">
            Periode:
        </span>
        <a href="?preset=this_month" class="filter-preset-pill {{ $currentPreset === 'this_month' ? 'active' : '' }}" data-preset="this_month">
            Bulan Ini
        </a>
        <a href="?preset=last_month" class="filter-preset-pill {{ $currentPreset === 'last_month' ? 'active' : '' }}" data-preset="last_month">
            Bulan Lalu
        </a>
        <a href="?preset=this_quarter" class="filter-preset-pill {{ $currentPreset === 'this_quarter' ? 'active' : '' }}" data-preset="this_quarter">
            Kuartal Ini
        </a>
        <a href="?preset=this_year" class="filter-preset-pill {{ $currentPreset === 'this_year' ? 'active' : '' }}" data-preset="this_year">
            Tahun Ini
        </a>
    </div>

    {{-- Form Filter Kustom & Tombol Aksi --}}
    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
        <form method="GET" action="{{ url()->current() }}" class="filter-custom-form">
            <input type="hidden" name="preset" value="custom">
            
            <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Dari:</span>
            <input type="date" name="start_date" value="{{ $dateInfo['start_date'] }}" class="filter-input-date" required>
            
            <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">S/D:</span>
            <input type="date" name="end_date" value="{{ $dateInfo['end_date'] }}" class="filter-input-date" required>
            
            <button type="submit" class="btn-filter-action btn-filter-primary">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                <span>Terapkan</span>
            </button>
        </form>

        @if(!empty($tableExportId))
            <button type="button" onclick="exportTableToCsv('{{ $tableExportId }}', '{{ $exportFilename ?? 'laporan-keuangan.csv' }}')" class="btn-filter-action btn-filter-secondary" title="Ekspor ke spreadsheet CSV">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>CSV</span>
            </button>
        @endif

        <button type="button" onclick="printLaporan()" class="btn-filter-action btn-filter-secondary" title="Cetak format akuntansi resmi A4">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Cetak A4</span>
        </button>
    </div>
</div>

