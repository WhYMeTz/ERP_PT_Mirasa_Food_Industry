<div class="laporan-nav-tabs no-print">
    <a href="{{ route('keuangan.laporan.index', ['preset' => $dateInfo['preset'] ?? 'this_month']) }}" 
       class="laporan-tab-btn {{ request()->routeIs('keuangan.laporan.index') ? 'active' : '' }}">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
        <span>Ringkasan Eksekutif</span>
    </a>
    <a href="{{ route('keuangan.laporan.laba_rugi', ['preset' => $dateInfo['preset'] ?? 'this_month']) }}" 
       class="laporan-tab-btn {{ request()->routeIs('keuangan.laporan.laba_rugi') ? 'active' : '' }}">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
        <span>Laba Rugi</span>
    </a>
    <a href="{{ route('keuangan.laporan.neraca', ['preset' => $dateInfo['preset'] ?? 'this_month']) }}" 
       class="laporan-tab-btn {{ request()->routeIs('keuangan.laporan.neraca') ? 'active' : '' }}">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
        <span>Neraca Keuangan</span>
    </a>
    <a href="{{ route('keuangan.laporan.neraca_saldo', ['preset' => $dateInfo['preset'] ?? 'this_month']) }}" 
       class="laporan-tab-btn {{ request()->routeIs('keuangan.laporan.neraca_saldo') ? 'active' : '' }}">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <span>Neraca Saldo (Trial Balance)</span>
    </a>
    <a href="{{ route('keuangan.laporan.arus_kas', ['preset' => $dateInfo['preset'] ?? 'this_month']) }}" 
       class="laporan-tab-btn {{ request()->routeIs('keuangan.laporan.arus_kas') ? 'active' : '' }}">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>Arus Kas (Cash Flow)</span>
    </a>
</div>

