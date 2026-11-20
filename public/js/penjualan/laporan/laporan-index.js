/**
 * ══════════════════════════════════════════════════════════════════════════
 * REKAPITULASI & LAPORAN PENJUALAN - JAVASCRIPT
 * PT Mirasa Food Industry
 * ══════════════════════════════════════════════════════════════════════════
 */

document.addEventListener('DOMContentLoaded', function () {
    const presetSelect = document.getElementById('filterPreset');
    const customDateWrap = document.getElementById('customDateWrap');

    if (presetSelect && customDateWrap) {
        presetSelect.addEventListener('change', function () {
            if (this.value === 'custom') {
                customDateWrap.style.display = 'flex';
            } else {
                customDateWrap.style.display = 'none';
                // Auto submit form on preset change for snappy UX
                this.form.submit();
            }
        });
    }

    const channelSelect = document.getElementById('filterChannel');
    if (channelSelect) {
        channelSelect.addEventListener('change', function () {
            this.form.submit();
        });
    }
});

function switchLaporanTab(tabName) {
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tabName);
    window.location.href = url.toString();
}
