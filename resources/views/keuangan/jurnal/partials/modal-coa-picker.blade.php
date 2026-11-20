{{-- MODAL SEARCH & PICKER AKUN COA CEPAT (MACOS NATIVE WINDOW) --}}
<div id="modalCoaPicker" 
     class="modal-backdrop-custom" 
     style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 1070; align-items: center; justify-content: center; padding: 1.25rem; backdrop-filter: blur(4px);"
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
                <span class="macos-title-heading">Pilih Bagan Akun (Chart of Accounts)</span>
            </div>

            <div class="macos-title-esc">
                <span>esc</span>
            </div>
        </div>

        {{-- SEARCH INPUT & FILTER PILLS --}}
        <div style="padding: 1rem 1.25rem 0.5rem 1.25rem; border-bottom: 1px solid #f1f5f9; background: #ffffff;">
            <div style="position: relative; margin-bottom: 0.65rem;">
                <input type="text" 
                       id="modalCoaSearchInput" 
                       class="jurnal-input" 
                       placeholder="🔍 Ketik kode akun (6101) atau nama (Gaji, Listrik, Kas, dll)..." 
                       style="height: 38px; font-size: 0.85rem; padding-left: 2.25rem; border-radius: 6px;"
                       oninput="filterCoaPickerModal()">
                <span style="position: absolute; left: 10px; top: 10px; color: #94a3b8; font-size: 0.9rem; pointer-events: none;">🔍</span>
            </div>

            {{-- KATEGORI PILLS --}}
            <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; margin-bottom: 0.5rem;">
                <button type="button" class="coa-filter-pill active" onclick="filterCoaCategory('', this)">Semua ({{ $coaList->count() }})</button>
                <button type="button" class="coa-filter-pill" style="background: #ecfdf5; color: #047857; border-color: #86efac; font-weight: 800;" onclick="filterCoaCategory('PASANGAN', this)">⚡ Pasangan Cepat (Auto Baris 2)</button>
                <button type="button" class="coa-filter-pill" onclick="filterCoaCategory('BEBAN', this)">Beban &amp; Biaya</button>
                <button type="button" class="coa-filter-pill" onclick="filterCoaCategory('AKTIVA', this)">Kas, Bank &amp; Aset</button>
                <button type="button" class="coa-filter-pill" onclick="filterCoaCategory('PASIVA', this)">Hutang &amp; Kewajiban</button>
                <button type="button" class="coa-filter-pill" onclick="filterCoaCategory('MODAL', this)">Modal &amp; Ekuitas</button>
                <button type="button" class="coa-filter-pill" onclick="filterCoaCategory('PENDAPATAN', this)">Pendapatan</button>
            </div>
        </div>

        {{-- LIST CONTAINER (SCROLLABLE) --}}
        <div id="modalCoaContainer" style="padding: 0.75rem 1.25rem; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 0.45rem; max-height: 380px; background: #fafbfc;">
            @php
                $coaMap = $coaList->keyBy('kode_akun');
                $pairedTemplates = [
                    [
                        'title' => '⚡ Listrik & Air PLN Pabrik',
                        'd_code' => '6102', 'd_name' => 'Beban air & Listrik',
                        'k_code' => '1104', 'k_name' => 'Kas Operasional',
                        'desc' => 'Pembayaran Tagihan Listrik PLN & Air Pabrik',
                        'search' => 'pasangan listrik pln air utilitas beban kas operasional 6102 1104'
                    ],
                    [
                        'title' => '🔧 Pemeliharaan / Servis Mesin Fryer',
                        'd_code' => '6106', 'd_name' => 'Beban Pemeliharaan',
                        'k_code' => '1104', 'k_name' => 'Kas Operasional',
                        'desc' => 'Biaya Servis, Sparepart & Pemeliharaan Mesin Pabrik',
                        'search' => 'pasangan servis pemeliharaan mesin sparepart las kas operasional 6106 1104'
                    ],
                    [
                        'title' => '👷 Upah & Gaji Karyawan',
                        'd_code' => '6101', 'd_name' => 'Beban Gaji',
                        'k_code' => '1104', 'k_name' => 'Kas Operasional',
                        'desc' => 'Pembayaran Upah & Gaji Karyawan Pabrik',
                        'search' => 'pasangan gaji upah buruh karyawan kas operasional 6101 1104'
                    ],
                    [
                        'title' => '⛽ BBM & Solar Boiler Mesin',
                        'd_code' => '6107', 'd_name' => 'Beban BBM',
                        'k_code' => '1104', 'k_name' => 'Kas Operasional',
                        'desc' => 'Pembelian Solar Mesin Boiler / Bensin Operasional',
                        'search' => 'pasangan bbm solar bensin spbu boiler kas operasional 6107 1104'
                    ],
                    [
                        'title' => '📦 Perlengkapan Pabrik & ATK',
                        'd_code' => '6100', 'd_name' => 'Beban Perlengkapan',
                        'k_code' => '1104', 'k_name' => 'Kas Operasional',
                        'desc' => 'Pembelian Lakban, Karung, Spidol & ATK Pabrik',
                        'search' => 'pasangan perlengkapan atk lakban plastik karung kas operasional 6100 1104'
                    ],
                    [
                        'title' => '🛒 Pembelian Bahan / Kemasan (Hutang)',
                        'd_code' => '1150', 'd_name' => 'Persediaan Bahan Baku & Penolong',
                        'k_code' => '2100', 'k_name' => 'Hutang usaha',
                        'desc' => 'Pencatatan Pembelian Bahan Baku / Karton Kemasan Supplier',
                        'search' => 'pasangan pembelian persediaan karton kemasan bahan baku hutang usaha 1150 2100'
                    ],
                    [
                        'title' => '🏦 Tarik Tunai Bank BCA ke Kas Operasional',
                        'd_code' => '1104', 'd_name' => 'Kas Operasional',
                        'k_code' => '1103', 'k_name' => 'Bank BCA',
                        'desc' => 'Tarik Tunai dari Rekening Bank BCA untuk Kas Operasional',
                        'search' => 'pasangan tarik tunai bank bca kas operasional mutasi kas 1104 1103'
                    ],
                    [
                        'title' => '💳 Pelunasan Hutang Supplier via Bank BCA',
                        'd_code' => '2100', 'd_name' => 'Hutang usaha',
                        'k_code' => '1103', 'k_name' => 'Bank BCA',
                        'desc' => 'Pelunasan Hutang Usaha Supplier via Transfer Bank BCA',
                        'search' => 'pasangan pelunasan bayar hutang usaha supplier bank bca transfer 2100 1103'
                    ],
                    [
                        'title' => '🍽️ Konsumsi & Kebutuhan Dapur Pabrik',
                        'd_code' => '6105', 'd_name' => 'Beban rumah tangga',
                        'k_code' => '1104', 'k_name' => 'Kas Operasional',
                        'desc' => 'Biaya Konsumsi Lembur, Galon Air Minum & Dapur Pabrik',
                        'search' => 'pasangan konsumsi makan lembur dapur galon air beban rumah tangga kas operasional 6105 1104'
                    ],
                    [
                        'title' => '🚚 Ongkos Angkut & Pengiriman',
                        'd_code' => '6112', 'd_name' => 'Beban Angkut',
                        'k_code' => '1104', 'k_name' => 'Kas Operasional',
                        'desc' => 'Ongkos Angkut Bahan & Ekspedisi Pengiriman Barang',
                        'search' => 'pasangan ongkos angkut pengiriman ekspedisi kurir beban angkut kas operasional 6112 1104'
                    ],
                ];
            @endphp

            {{-- DAFTAR TEMPLATE PASANGAN CEPAT --}}
            @foreach ($pairedTemplates as $pt)
                @php
                    $dCoa = $coaMap->get($pt['d_code']);
                    $kCoa = $coaMap->get($pt['k_code']);
                    $dId = $dCoa?->coa_id ?? 0;
                    $kId = $kCoa?->coa_id ?? 0;
                    $dNm = $dCoa?->nama_akun ?? $pt['d_name'];
                    $kNm = $kCoa?->nama_akun ?? $pt['k_name'];
                @endphp
                <div class="coa-pair-item"
                     data-kategori="PASANGAN"
                     data-did="{{ $dId }}"
                     data-dcode="{{ $pt['d_code'] }}"
                     data-dname="{{ $dNm }}"
                     data-kid="{{ $kId }}"
                     data-kcode="{{ $pt['k_code'] }}"
                     data-kname="{{ $kNm }}"
                     data-desc="{{ $pt['desc'] }}"
                     data-search="{{ strtolower($pt['search'] . ' ' . $pt['title'] . ' ' . $pt['desc'] . ' ' . $dNm . ' ' . $kNm) }}"
                     onclick="selectCoaPairItem(this)">
                    <div class="coa-pair-title">
                        <span>{{ $pt['title'] }}</span>
                        <span style="font-size: 0.675rem; color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 1px 6px; border-radius: 4px; margin-left: auto;">⚡ 2 Baris Otomatis</span>
                    </div>
                    <div class="coa-pair-flow">
                        <span class="coa-pair-tag-d">Debet: {{ $pt['d_code'] }} ({{ $dNm }})</span>
                        <span class="coa-pair-arrow">&rarr;</span>
                        <span class="coa-pair-tag-k">Kredit: {{ $pt['k_code'] }} ({{ $kNm }})</span>
                    </div>
                </div>
            @endforeach
            @foreach ($coaList as $c)
                <div class="coa-picker-item" 
                     data-id="{{ $c->coa_id }}" 
                     data-kode="{{ $c->kode_akun }}" 
                     data-nama="{{ $c->nama_akun }}" 
                     data-kategori="{{ $c->kategori_akun }}" 
                     data-posisi="{{ $c->posisi_keuangan }}" 
                     data-search="{{ strtolower($c->kode_akun . ' ' . $c->nama_akun . ' ' . $c->kategori_akun) }}"
                     onclick="selectCoaFromModal(this)">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.65rem;">
                            <span class="coa-code-pill" style="font-size: 0.8rem; padding: 2px 7px;">{{ $c->kode_akun }}</span>
                            <strong style="color: #0f172a; font-size: 0.825rem;">{{ $c->nama_akun }}</strong>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.45rem;">
                            <span class="badge-coa-cat badge-cat-{{ strtolower($c->kategori_akun) }}">{{ $c->kategori_akun }}</span>
                            <span style="font-size: 0.7rem; color: #64748b; font-family: monospace;">Saldo: {{ $c->saldo_normal }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
            <div id="coaEmptySearch" style="display: none; text-align: center; padding: 2.5rem 1rem; color: #94a3b8; font-size: 0.85rem;">
                Tidak ada akun yang sesuai dengan pencarian Anda.
            </div>
        </div>

        {{-- MACOS FOOTER --}}
        <div class="macos-footer-bar">
            <span style="font-size: 0.75rem; color: #64748b;">Klik pada baris akun untuk langsung memilih</span>
            <button type="button" class="btn btn-secondary" onclick="closeCoaPickerModal()" style="font-size: 0.775rem; padding: 0.35rem 0.95rem; font-weight: 700;">Tutup</button>
        </div>
    </div>
</div>
