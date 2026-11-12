<div class="sheet-header">
    <div style="font-size: 0.725rem; font-weight: 800; color: #64748b; letter-spacing: 0.08em; text-transform: uppercase;">
        SISTEM INFORMASI ENTERPRISE RESOURCE PLANNING
    </div>
    <h1 class="sheet-company-title">
        PT MIRASA FOOD INDUSTRY
    </h1>
    <div style="font-size: 0.75rem; color: #475569; margin-top: 2px;">
        Jl. Magelang - Purworejo, Magelang, Jawa Tengah &bull; Departemen Keuangan &amp; Akuntansi
    </div>
    <h2 class="sheet-report-title">
        {{ $reportTitle ?? 'LAPORAN KEUANGAN' }}
    </h2>
    <div class="sheet-period-subtitle">
        {{ $periodSubtitle ?? ('Periode: ' . \Carbon\Carbon::parse($dateInfo['start_date'])->translatedFormat('d F Y') . ' s/d ' . \Carbon\Carbon::parse($dateInfo['end_date'])->translatedFormat('d F Y')) }}
    </div>
</div>

