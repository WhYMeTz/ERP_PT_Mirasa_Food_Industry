{{--
    =====================================================================
    BAR INSPEKTUR BARIS AKTIF
    Menampilkan ringkasan baris yang sedang diisi secara real-time,
    supaya operator selalu tahu data apa yang sedang dia inputkan.
    Tidak mengubah struktur kolom tabel sama sekali.
    =====================================================================
--}}
<div class="po-row-inspector" id="poRowInspector" aria-live="polite">
    <span class="ux-inspector-hint">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>
            Klik kolom mana pun pada tabel untuk melihat ringkasan baris yang sedang diisi.
            <span class="ux-inspector-kbd">
                <kbd>Alt</kbd>+<kbd>A</kbd> tambah baris &bull;
                <kbd>Ctrl</kbd>+<kbd>Enter</kbd> simpan
            </span>
        </span>
    </span>
</div>
