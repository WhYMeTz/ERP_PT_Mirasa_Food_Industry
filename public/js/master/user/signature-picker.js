/**
 * Signature & Stamp Picker Logic - ERP PT Mirasa Food Industry
 */

window.selectSignatureTemplate = function(prefix, templateName, imgUrl) {
    // Set hidden input value
    const inputVal = document.getElementById(prefix + '_signature_path');
    if (inputVal) inputVal.value = templateName;

    // Reset file upload input if any
    const fileInput = document.getElementById(prefix + '_signature_file');
    if (fileInput) fileInput.value = '';

    // Update tile active class
    const container = document.getElementById(prefix + '_signature_tiles');
    if (container) {
        container.querySelectorAll('.signature-card-tile').forEach(t => t.classList.remove('active'));
        const targetTile = container.querySelector('[data-template="' + templateName + '"]');
        if (targetTile) targetTile.classList.add('active');
    }

    // Hide upload container
    const uploadBox = document.getElementById(prefix + '_upload_container');
    if (uploadBox) uploadBox.classList.remove('show');

    // Update preview stage
    updateSignaturePreview(prefix, imgUrl, templateName ? 'Template: ' + templateName : 'Tanpa Tanda Tangan');
};

window.toggleCustomSignatureUpload = function(prefix) {
    const uploadBox = document.getElementById(prefix + '_upload_container');
    if (!uploadBox) return;

    uploadBox.classList.toggle('show');

    // De-activate template tiles
    const container = document.getElementById(prefix + '_signature_tiles');
    if (container) {
        container.querySelectorAll('.signature-card-tile').forEach(t => t.classList.remove('active'));
    }

    const inputVal = document.getElementById(prefix + '_signature_path');
    if (inputVal) inputVal.value = '';
};

window.handleSignatureFileUpload = function(prefix, input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();

        reader.onload = function(e) {
            updateSignaturePreview(prefix, e.target.result, 'Unggahan Baru: ' + file.name);
            
            // Set indicator
            const inputVal = document.getElementById(prefix + '_signature_path');
            if (inputVal) inputVal.value = ''; // Akan diisi oleh file upload pada backend

            // De-activate other tiles
            const container = document.getElementById(prefix + '_signature_tiles');
            if (container) {
                container.querySelectorAll('.signature-card-tile').forEach(t => t.classList.remove('active'));
            }
        };

        reader.readAsDataURL(file);
    }
};

window.clearSignatureSelection = function(prefix) {
    const inputVal = document.getElementById(prefix + '_signature_path');
    if (inputVal) inputVal.value = '__DELETE__';

    const fileInput = document.getElementById(prefix + '_signature_file');
    if (fileInput) fileInput.value = '';

    const container = document.getElementById(prefix + '_signature_tiles');
    if (container) {
        container.querySelectorAll('.signature-card-tile').forEach(t => t.classList.remove('active'));
        const noneTile = container.querySelector('[data-template="none"]');
        if (noneTile) noneTile.classList.add('active');
    }

    const uploadBox = document.getElementById(prefix + '_upload_container');
    if (uploadBox) uploadBox.classList.remove('show');

    updateSignaturePreview(prefix, '', 'Tanpa Tanda Tangan');
};

function updateSignaturePreview(prefix, imgUrl, labelText) {
    const imgEl = document.getElementById(prefix + '_preview_img');
    const emptyEl = document.getElementById(prefix + '_preview_empty');
    const labelEl = document.getElementById(prefix + '_preview_label');

    if (imgUrl) {
        if (imgEl) {
            imgEl.src = imgUrl;
            imgEl.style.display = 'block';
        }
        if (emptyEl) emptyEl.style.display = 'none';
    } else {
        if (imgEl) {
            imgEl.src = '';
            imgEl.style.display = 'none';
        }
        if (emptyEl) emptyEl.style.display = 'block';
    }

    if (labelEl) {
        labelEl.innerText = labelText;
    }
}

