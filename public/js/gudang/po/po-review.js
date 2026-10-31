/**
 * JavaScript Logika Tinjauan PO (macOS Monochrome & Quick Look Modal)
 * PT Mirasa Food Industry - ERP Executive Suite
 */

document.addEventListener('DOMContentLoaded', function () {
    // Esc key closes any open modal
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeDocumentModal();
            document.querySelectorAll('.modal-review-backdrop.show').forEach(function (modal) {
                closeReviewModal(modal.id);
            });
        }
    });

    // Close on click backdrop
    const docModal = document.getElementById('modalDocumentPreview');
    if (docModal) {
        docModal.addEventListener('click', function (e) {
            if (e.target === docModal) {
                closeDocumentModal();
            }
        });
    }

    document.querySelectorAll('.modal-review-backdrop').forEach(function (backdrop) {
        backdrop.addEventListener('click', function (e) {
            if (e.target === backdrop) {
                closeReviewModal(backdrop.id);
            }
        });
    });

    // Form submit states (prevent double submit)
    setupFormSubmitHandling('formApprovePo', 'btnSubmitApprove', 'Memproses Persetujuan...');
    setupFormSubmitHandling('formRevisionPo', 'btnSubmitRevision', 'Mengirim Instruksi...');
    setupFormSubmitHandling('formRejectPo', 'btnSubmitReject', 'Menolak Pengadaan...');
});

// ========================================================================
// 1. MACOS QUICK LOOK DOCUMENT MODAL
// ========================================================================
let modalZoomLevel = 1.0;
let isDocModalAnimating = false;

function openDocumentModal() {
    const modal = document.getElementById('modalDocumentPreview');
    if (!modal || isDocModalAnimating) return;

    modal.classList.remove('closing');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeDocumentModal() {
    const modal = document.getElementById('modalDocumentPreview');
    if (!modal || !modal.classList.contains('show') || isDocModalAnimating) return;

    isDocModalAnimating = true;
    modal.classList.add('closing');

    setTimeout(function () {
        modal.classList.remove('show', 'closing');
        document.body.style.overflow = '';
        isDocModalAnimating = false;
    }, 200);
}

function zoomModalDoc(direction) {
    const paper = document.getElementById('modalA4Paper');
    const indicator = document.getElementById('zoomLevelIndicator');
    if (!paper) return;

    if (direction === 'in') {
        if (modalZoomLevel < 1.35) modalZoomLevel += 0.1;
    } else if (direction === 'out') {
        if (modalZoomLevel > 0.65) modalZoomLevel -= 0.1;
    } else {
        modalZoomLevel = 1.0;
    }

    paper.style.transform = `scale(${modalZoomLevel})`;
    paper.style.transformOrigin = 'top center';

    if (indicator) {
        indicator.textContent = Math.round(modalZoomLevel * 100) + '%';
    }
}

function printModalDocument() {
    window.print();
}

// ========================================================================
// 2. DECISION CONFIRMATION MODALS (APPROVE, REVISION, REJECT)
// ========================================================================
function openApproveModal() {
    openReviewModal('modalApprovePo');
    const textarea = document.getElementById('approval_notes');
    if (textarea) setTimeout(() => textarea.focus(), 150);
}

function openRevisionModal() {
    openReviewModal('modalRevisionPo');
    const textarea = document.getElementById('revision_notes');
    if (textarea) setTimeout(() => textarea.focus(), 150);
}

function openRejectModal() {
    openReviewModal('modalRejectPo');
    const textarea = document.getElementById('reject_reason');
    if (textarea) setTimeout(() => textarea.focus(), 150);
}

function openReviewModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.classList.remove('closing');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeReviewModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal || !modal.classList.contains('show')) return;

    modal.classList.add('closing');

    setTimeout(function () {
        modal.classList.remove('show', 'closing');
        // If no other modals are open, restore body scroll
        if (!document.querySelector('.macos-doc-modal-backdrop.show, .modal-review-backdrop.show')) {
            document.body.style.overflow = '';
        }
    }, 200);
}

function setupFormSubmitHandling(formId, buttonId, loadingText) {
    const form = document.getElementById(formId);
    const btn = document.getElementById(buttonId);
    if (!form || !btn) return;

    form.addEventListener('submit', function (e) {
        if (!form.checkValidity()) {
            return;
        }

        btn.disabled = true;
        btn.dataset.originalHtml = btn.innerHTML;
        btn.innerHTML = `
            <svg class="animate-spin" width="16" height="16" fill="none" viewBox="0 0 24 24" style="animation: spin 1s linear infinite; display: inline-block;">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            ${loadingText}
        `;
    });
}
