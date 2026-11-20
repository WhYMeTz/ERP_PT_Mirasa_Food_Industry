{{-- MODAL SEARCH & PICKER AKUN COA CEPAT (MACOS NATIVE WINDOW) --}}
<div id="modalCoaPicker" 
     class="modal-backdrop-custom" 
     style="display: none;"
     onclick="if(event.target.id === 'modalCoaPicker') closeCoaPickerModal()">
    <div onclick="event.stopPropagation()" class="macos-window-frame">
        
        {{-- MACOS WINDOW TITLE BAR --}}
        <div class="macos-title-bar">
            <div class="macos-traffic-lights">
                <span class="traffic-dot dot-close" onclick="closeCoaPickerModal()" title="Tutup (Esc)"></span>
                <span class="traffic-dot dot-minimize" onclick="closeCoaPickerModal()" title="Tutup"></span>
                <span class="traffic-dot dot-expand" title="Pencarian Bagan Akun"></span>
            </div>

            <div class="macos-title-center">
                <span class="macos-title-icon">🔍</span>
                <span class="macos-title-heading">Pilih Rekening Akun Buku Besar (COA)</span>
            </div>

            <div class="macos-title-esc">
                <span>esc</span>
            </div>
        </div>

        {{-- INFO BARIS YANG SEDANG DIATUR --}}
        <div class="modal-target-info">
            <svg width="14" height="14" fill="none" stroke="#dc2626" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Mengatur Akun untuk: <strong id="modalTargetLabel" style="color: #991b1b;">-</strong></span>
        </div>

        {{-- SEARCH INPUT & FILTER PILLS --}}
        <div class="modal-search-section">
            <div class="modal-search-input-wrap">
                <span class="modal-search-icon">🔍</span>
                <input type="text" 
                       id="modalCoaSearchInput" 
                       class="modal-search-field" 
                       placeholder="🔍 Ketik kode (1150) atau nama akun (Bahan Baku, Penolong, Hutang, Bank, Kas, Beban)..." 
                       oninput="filterCoaPickerModal()">
            </div>

            {{-- KATEGORI PILLS --}}
            <div class="modal-category-pills">
                <button type="button" class="coa-filter-pill active" onclick="filterCoaCategory('', this)">Semua ({{ $coaList->count() }})</button>
                <button type="button" class="coa-filter-pill" onclick="filterCoaCategory('AKTIVA', this)">Kas, Bank &amp; Aktiva</button>
                <button type="button" class="coa-filter-pill" onclick="filterCoaCategory('PASIVA', this)">Hutang &amp; Pasiva</button>
                <button type="button" class="coa-filter-pill" onclick="filterCoaCategory('HPP', this)">HPP Produksi</button>
                <button type="button" class="coa-filter-pill" onclick="filterCoaCategory('BEBAN', this)">Beban &amp; Biaya</button>
                <button type="button" class="coa-filter-pill" onclick="filterCoaCategory('PENDAPATAN', this)">Pendapatan</button>
                <button type="button" class="coa-filter-pill" onclick="filterCoaCategory('MODAL', this)">Modal</button>
            </div>
        </div>

        {{-- LIST CONTAINER (SCROLLABLE) --}}
        <div id="modalCoaContainer" class="modal-coa-scroll-list">
            @foreach ($coaList as $c)
                <div class="coa-picker-item" 
                     data-id="{{ $c->coa_id }}" 
                     data-kode="{{ $c->kode_akun }}" 
                     data-nama="{{ $c->nama_akun }}" 
                     data-kategori="{{ $c->kategori_akun }}" 
                     data-search="{{ strtolower($c->kode_akun . ' ' . $c->nama_akun . ' ' . $c->kategori_akun) }}"
                     onclick="selectCoaFromModal(this)">
                    <div class="coa-picker-left">
                        <span class="coa-code-pill">{{ $c->kode_akun }}</span>
                        <strong class="coa-item-title">{{ $c->nama_akun }}</strong>
                    </div>
                    <div class="coa-picker-right">
                        <span class="badge-coa-cat badge-cat-{{ strtolower($c->kategori_akun) }}">{{ $c->kategori_akun }}</span>
                        <span class="coa-item-balance">Saldo: {{ $c->saldo_normal }}</span>
                    </div>
                </div>
            @endforeach
            <div id="coaEmptySearch" style="display: none;" class="coa-empty-state">
                Tidak ada rekening akun yang cocok dengan kata kunci pencarian.
            </div>
        </div>

        {{-- MACOS FOOTER --}}
        <div class="macos-footer-bar">
            <span style="font-size: 0.75rem; color: #64748b;">💡 Klik pada salah satu baris akun untuk langsung memilih</span>
            <button type="button" class="btn-modal-close" onclick="closeCoaPickerModal()">Tutup</button>
        </div>
    </div>
</div>
