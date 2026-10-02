/**
 * JavaScript Cetak Label Stiker Fisik
 * Path: public/js/produksi/produksi-cetak-stiker.js
 * PT Mirasa Food Industry
 */

document.addEventListener('DOMContentLoaded', function () {
    const selectKarton = document.getElementById('selectKarton');
    const stickerWrapper = document.getElementById('stickerPageWrapper');
    const singleSticker = document.getElementById('singleSticker');
    const batchData = window.cetakStikerConfig || {};

    if (selectKarton) {
        selectKarton.addEventListener('change', function () {
            const val = this.value;
            if (val === 'ALL') {
                renderAllCartons();
            } else {
                renderSingleCarton(val);
            }
        });
    }

    function renderSingleCarton(kartonNo) {
        if (!singleSticker || !stickerWrapper) return;
        stickerWrapper.innerHTML = '';
        const cloned = singleSticker.cloneNode(true);
        cloned.style.display = 'block';
        const batchEl = cloned.querySelector('.st-batch-num');
        const paddedNo = String(kartonNo).padStart(4, '0');
        if (batchEl) {
            batchEl.textContent = (batchData.shift || 'A') + ' / ' + paddedNo;
        }
        stickerWrapper.appendChild(cloned);
    }

    function renderAllCartons() {
        if (!singleSticker || !stickerWrapper) return;
        stickerWrapper.innerHTML = '';
        const start = parseInt(batchData.noAwal) || 1;
        const total = parseInt(batchData.qtyKarton) || 1;
        const shift = batchData.shift || 'A';

        for (let i = 0; i < total; i++) {
            const currNo = start + i;
            const paddedNo = String(currNo).padStart(4, '0');
            const cloned = singleSticker.cloneNode(true);
            cloned.style.display = 'block';
            const batchEl = cloned.querySelector('.st-batch-num');
            if (batchEl) {
                batchEl.textContent = shift + ' / ' + paddedNo;
            }
            stickerWrapper.appendChild(cloned);
        }
    }
});
