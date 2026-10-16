/**
 * JavaScript Detail Produksi Harian (Show)
 * Path: public/js/produksi/produksi-show.js
 * PT Mirasa Food Industry
 */

/**
 * Tab Navigation Controls
 */
function switchProdTab(tabName, event) {
    if (event) {
        event.preventDefault();
    }
    
    // Update tab buttons
    const buttons = document.querySelectorAll('.prod-tab-btn');
    buttons.forEach(btn => {
        if (btn.getAttribute('data-tab') === tabName) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    // Update tab panes
    const panes = document.querySelectorAll('.prod-tab-pane');
    panes.forEach(pane => {
        if (pane.id === 'pane-' + tabName) {
            pane.classList.add('active');
        } else {
            pane.classList.remove('active');
        }
    });

    // Update URL hash without jumping
    if (history.replaceState) {
        history.replaceState(null, null, '#' + tabName);
    }
}
window.switchProdTab = switchProdTab;

document.addEventListener('DOMContentLoaded', function() {
    // Add click listeners to buttons
    document.querySelectorAll('.prod-tab-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const tab = this.getAttribute('data-tab');
            if (tab) {
                switchProdTab(tab, e);
            }
        });
    });

    // Check hash on load
    const hash = window.location.hash.replace('#', '');
    if (hash && ['output', 'biaya', 'audit'].includes(hash)) {
        switchProdTab(hash);
    }
});

function updateLiveStickerCard(kartonNum) {
    const el = document.getElementById('previewStickerKartonNo');
    if (el && kartonNum) {
        el.textContent = kartonNum;
    }
}

