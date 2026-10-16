/**
 * Buku Mutasi Kas & Rekening Bank - Interaction Scripts
 * ERP PT Mirasa Food Industry
 */

document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filterMutasiForm');
    const autoSubmitElements = document.querySelectorAll('.auto-submit-filter');

    autoSubmitElements.forEach(function (el) {
        el.addEventListener('change', function () {
            if (filterForm) {
                filterForm.submit();
            }
        });
    });
});
