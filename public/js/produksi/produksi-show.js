/**
 * JavaScript Detail Produksi Harian (Show)
 * Path: public/js/produksi/produksi-show.js
 * PT Mirasa Food Industry
 */

function updateLiveStickerCard(kartonNum) {
    const el = document.getElementById('previewStickerKartonNo');
    if (el && kartonNum) {
        el.textContent = kartonNum;
    }
}
