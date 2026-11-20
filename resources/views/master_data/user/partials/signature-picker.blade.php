<div class="signature-picker-container">
    <div class="signature-picker-header">
        <span class="signature-picker-title">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            Tanda Tangan & Stempel Resmi (Approval PO)
        </span>
        <span style="font-size: 0.725rem; color: #64748b; font-weight: 600;">Otomatis dicetak saat Approve PO</span>
    </div>

    <input type="hidden" name="signature_path" id="{{ $prefix }}_signature_path" value="">

    {{-- Grid Pilihan Template Cepat --}}
    <div class="signature-grid" id="{{ $prefix }}_signature_tiles">
        {{-- Template 1 --}}
        <div class="signature-card-tile" data-template="ttd_stempel_1.png" onclick="selectSignatureTemplate('{{ $prefix }}', 'ttd_stempel_1.png', '{{ asset('images/signatures/ttd_stempel_1.png') }}')">
            <span class="check-badge">✓</span>
            <img src="{{ asset('images/signatures/ttd_stempel_1.png') }}" alt="Template 1">
            <span class="tile-label">Cap Payung Merah</span>
        </div>

        {{-- Template 2 --}}
        <div class="signature-card-tile" data-template="ttd_stempel_2.png" onclick="selectSignatureTemplate('{{ $prefix }}', 'ttd_stempel_2.png', '{{ asset('images/signatures/ttd_stempel_2.png') }}')">
            <span class="check-badge">✓</span>
            <img src="{{ asset('images/signatures/ttd_stempel_2.png') }}" alt="Template 2">
            <span class="tile-label">Cap Payung Biru</span>
        </div>

        {{-- Template 3 --}}
        <div class="signature-card-tile" data-template="ttd_stempel_3.png" onclick="selectSignatureTemplate('{{ $prefix }}', 'ttd_stempel_3.png', '{{ asset('images/signatures/ttd_stempel_3.png') }}')">
            <span class="check-badge">✓</span>
            <img src="{{ asset('images/signatures/ttd_stempel_3.png') }}" alt="Template 3">
            <span class="tile-label">Cap Payung Bulat</span>
        </div>

        {{-- Template 4 (Polos) --}}
        <div class="signature-card-tile" data-template="ttd_polos_1.png" onclick="selectSignatureTemplate('{{ $prefix }}', 'ttd_polos_1.png', '{{ asset('images/signatures/ttd_polos_1.png') }}')">
            <span class="check-badge">✓</span>
            <img src="{{ asset('images/signatures/ttd_polos_1.png') }}" alt="TTD Polos">
            <span class="tile-label">TTD Saja (Polos)</span>
        </div>

        {{-- Opsi Tanpa TTD --}}
        <div class="signature-card-tile" data-template="none" onclick="clearSignatureSelection('{{ $prefix }}')">
            <span class="check-badge">✓</span>
            <div style="font-size: 1.5rem; color: #94a3b8; margin: 10px auto 4px;">🚫</div>
            <span class="tile-label" style="color: #64748b;">Tanpa TTD</span>
        </div>
    </div>

    {{-- Tombol Toggle Unggah File Sendiri --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
        <button type="button" class="btn btn-secondary" onclick="toggleCustomSignatureUpload('{{ $prefix }}')" style="font-size: 0.75rem; padding: 0.35rem 0.65rem; border: 1px dashed #94a3b8; background: #ffffff;">
            📁 Unggah Berkas Gambar Kustom (.PNG)
        </button>
        <span style="font-size: 0.7rem; color: #94a3b8;">Format PNG transparan disarankan</span>
    </div>

    {{-- Input File Upload (Hidden by default) --}}
    <div class="signature-upload-wrapper" id="{{ $prefix }}_upload_container">
        <input type="file" name="signature_file" id="{{ $prefix }}_signature_file" class="form-control" accept="image/png,image/jpeg,image/webp" onchange="handleSignatureFileUpload('{{ $prefix }}', this)" style="font-size: 0.8rem; padding: 0.35rem;">
    </div>

    {{-- Live Preview Stage --}}
    <div class="signature-preview-box">
        <div class="signature-preview-stage">
            <img id="{{ $prefix }}_preview_img" src="" alt="Pratinjau" style="display: none;">
            <span id="{{ $prefix }}_preview_empty" style="font-size: 0.725rem; color: #94a3b8; font-style: italic; text-align: center;">
                Belum ada spesimen dipilih
            </span>
        </div>
        <div class="signature-preview-info">
            <div style="font-size: 0.75rem; font-weight: 700; color: #1e293b;" id="{{ $prefix }}_preview_label">
                Belum memilih spesimen
            </div>
            <p style="font-size: 0.7rem; color: #64748b; margin: 0.25rem 0 0;">
                Spesimen ini akan disematkan pada lembar Purchase Order resmi bila akun ini bertindak sebagai Approver/Atasan.
            </p>
        </div>
    </div>
</div>

