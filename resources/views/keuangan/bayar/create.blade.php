@extends('layouts.app')

@section('title', 'Bayar Tagihan Hutang (Bukti Kas Keluar)')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/keuangan/bayar/bayar-create.css') }}">
@endpush

@section('content')
<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem; flex-wrap: wrap; gap: 0.5rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <a href="{{ route('keuangan.bayar.index') }}" style="color: #64748b; text-decoration: none; font-size: 0.825rem; font-weight: 600;">
                &larr; Riwayat Kas Keluar
            </a>
            <span style="color: #cbd5e1;">/</span>
            <h1 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0;">
                💳 Pembayaran Tagihan Hutang (Bukti Kas Keluar - BKK)
            </h1>
        </div>
    </div>

    <div>
        <a href="{{ route('keuangan.bayar.index') }}" class="btn btn-secondary" style="font-weight: 700; font-size: 0.8rem; padding: 0.35rem 0.75rem;">
            Batal
        </a>
    </div>
</div>

@php
    // Cari supplier aktif awal
    $initialSuppId = $supplierId;
    if (!$initialSuppId) {
        foreach ($suppliers as $s) {
            if (isset($unpaidCounts[$s->supplier_id]) && $unpaidCounts[$s->supplier_id]->count_tagihan > 0) {
                $initialSuppId = $s->supplier_id;
                break;
            }
        }
    }
    $initialSuppData = $initialSuppId && isset($unpaidCounts[$initialSuppId]) ? $unpaidCounts[$initialSuppId] : null;
    $initialSuppModel = $initialSuppId ? $suppliers->firstWhere('supplier_id', $initialSuppId) : null;
@endphp

<form action="{{ route('keuangan.bayar.store') }}" method="POST" id="formBayarHutang">
    @csrf

    {{-- Hidden field untuk supplier terpilih --}}
    <input type="hidden" name="supplier_id" id="selectedSupplierId" value="{{ $initialSuppId }}">

    <div class="split-screen-bayar-container">
        {{-- ===============================================================
             PANEL KIRI: DAFTAR SUPPLIER BERHUTANG (MASTER DIRECTORY)
             =============================================================== --}}
        <div class="supp-directory-panel">
            <div class="supp-directory-header">
                <div class="supp-header-title">
                    <h3>
                        <span>🏢</span> <span>Rekanan Berhutang</span>
                    </h3>
                    <span class="supp-count-badge" id="badgeSuppTotal">
                        {{ count($unpaidCounts) }} Rekanan
                    </span>
                </div>
                <input type="text" 
                       id="searchSupplierInput" 
                       class="supp-search-input" 
                       placeholder="Cari nama supplier...">
            </div>

            <div class="supp-items-scroll" id="supplierListContainer">
                @forelse ($suppliers as $supp)
                    @php
                        $uData = $unpaidCounts[$supp->supplier_id] ?? null;
                        if (!$uData || $uData->count_tagihan <= 0) continue;

                        $countTagihan = $uData->count_tagihan;
                        $totalSisa = (float) $uData->total_sisa;
                        $minTempo = $uData->min_tempo ?? null;

                        $tempoText = '-';
                        if ($minTempo) {
                            $days = round(now()->diffInDays(\Carbon\Carbon::parse($minTempo), false));
                            if ($days < 0) {
                                $tempoText = '⚠️ Lewat ' . abs($days) . ' hr';
                            } elseif ($days <= 7) {
                                $tempoText = '⏳ ' . $days . ' hr lagi';
                            } else {
                                $tempoText = '📅 ' . $days . ' hr';
                            }
                        }
                        $isActive = ($initialSuppId == $supp->supplier_id);
                    @endphp
                    <div class="supplier-master-item {{ $isActive ? 'active' : '' }}"
                         data-supplier-id="{{ $supp->supplier_id }}"
                         data-name="{{ $supp->supplier_nm }}"
                         data-count="{{ $countTagihan }}"
                         data-total="{{ $totalSisa }}">
                        <div class="supp-item-top">
                            <span class="supp-item-name" title="{{ $supp->supplier_nm }}">
                                {{ $supp->supplier_nm }}
                            </span>
                            <span class="supp-tagihan-pill">
                                {{ $countTagihan }} Faktur
                            </span>
                        </div>
                        <div class="supp-item-bottom">
                            <span class="supp-item-nominal">
                                Rp {{ number_format($totalSisa, 0, ',', '.') }}
                            </span>
                            <span class="supp-item-tempo">
                                {{ $tempoText }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div style="padding: 2rem 1rem; text-align: center; color: #94a3b8; font-size: 0.8rem;">
                        Tidak ada supplier yang memiliki tagihan belum lunas.
                    </div>
                @endforelse

                <div id="suppEmptySearch" style="display: none; padding: 2rem 1rem; text-align: center; color: #94a3b8; font-size: 0.8rem;">
                    Supplier tidak ditemukan.
                </div>
            </div>
        </div>

        {{-- ===============================================================
             PANEL KANAN: DETAIL FAKTUR & EKSEKUSI BKK
             =============================================================== --}}
        <div class="bayar-detail-panel">
            {{-- KARTU 1: HEADER BANNER REKANAN & FORM MINI KAS/BANK --}}
            <div class="detail-header-card">
                <div class="detail-supplier-banner">
                    <div class="banner-left">
                        <span style="font-size: 1.25rem;">🏢</span>
                        <div>
                            <div class="banner-title" id="bannerSupplierName">
                                {{ $initialSuppModel->supplier_nm ?? 'Pilih Rekanan Supplier' }}
                            </div>
                            <div class="banner-sub" id="bannerTagihanSub">
                                {{ $initialSuppData ? $initialSuppData->count_tagihan . ' Faktur Belum Lunas' : '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="banner-right">
                        <span class="banner-debt-label">Total Kewajiban:</span>
                        <span class="banner-debt-val" id="bannerTotalDebt">
                            Rp {{ number_format($initialSuppData->total_sisa ?? 0, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- Mini Form Kas Keluar --}}
                <div class="mini-form-grid">
                    <div class="fast-control-group">
                        <label class="fast-control-label">
                            <span>Rekening Kas / Bank <span style="color: #dc2626;">*</span></span>
                            <span id="fastSaldoBadge" style="color: #059669; font-weight: 800;">-</span>
                        </label>
                        <select name="kas_bank_id" id="kasBankSelect" class="fast-control-select" required>
                            @foreach ($kasBanks as $kb)
                                <option value="{{ $kb->kas_bank_id }}" 
                                        data-saldo="{{ $kb->saldo_berjalan }}"
                                        data-nama="{{ $kb->nama_akun }}"
                                        {{ old('kas_bank_id') == $kb->kas_bank_id ? 'selected' : '' }}>
                                    {{ $kb->nama_akun }} (Rp {{ number_format($kb->saldo_berjalan, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="fast-control-group">
                        <label class="fast-control-label">
                            <span>Tgl Bayar <span style="color: #dc2626;">*</span></span>
                        </label>
                        <input type="date" 
                               name="tgl_bayar" 
                               value="{{ old('tgl_bayar', date('Y-m-d')) }}" 
                               class="fast-control-input" 
                               required>
                    </div>

                    <div class="fast-control-group">
                        <label class="fast-control-label">
                            <span>Metode <span style="color: #dc2626;">*</span></span>
                        </label>
                        <select name="metode_bayar" class="fast-control-select" required>
                            <option value="TRANSFER" {{ old('metode_bayar') == 'TRANSFER' ? 'selected' : '' }}>📲 Transfer Bank</option>
                            <option value="CASH" {{ old('metode_bayar') == 'CASH' ? 'selected' : '' }}>💵 Tunai (Kas Pabrik)</option>
                            <option value="GIRO" {{ old('metode_bayar') == 'GIRO' ? 'selected' : '' }}>📜 Giro</option>
                            <option value="CEK" {{ old('metode_bayar') == 'CEK' ? 'selected' : '' }}>📝 Cek Bank</option>
                        </select>
                    </div>

                    <div class="fast-control-group">
                        <label class="fast-control-label">
                            <span>No. Ref / Catatan BKK</span>
                        </label>
                        <input type="text" 
                               name="no_referensi_bank" 
                               value="{{ old('no_referensi_bank') }}" 
                               class="fast-control-input" 
                               placeholder="Ref transfer / memo...">
                    </div>
                </div>
            </div>

            {{-- KARTU 2: TABEL FAKTUR SUPPLIER TERPILIH --}}
            <div class="detail-table-card">
                <div class="detail-table-toolbar">
                    <div class="table-toolbar-left">
                        <span style="font-size: 0.75rem; font-weight: 800; color: #475569; text-transform: uppercase;">
                            📋 Faktur Siap Bayar
                        </span>
                        <input type="text" 
                               id="searchInvoiceInput" 
                               class="table-search-box" 
                               placeholder="Cari No. Faktur / AP...">
                    </div>

                    <div class="table-toolbar-right">
                        <button type="button" class="btn-fast-tool" onclick="selectAllVisibleInvoices()">
                            ☑️ Bayar Penuh Semua
                        </button>
                        <button type="button" class="btn-fast-tool" onclick="clearAllInvoices()">
                            ⬜ Batal Pilih
                        </button>
                    </div>
                </div>

                <div class="detail-table-scroll">
                    <table class="fast-table" id="tableInvoices">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">
                                    <input type="checkbox" id="checkAllHead" class="fast-row-chk" title="Pilih Semua Faktur">
                                </th>
                                <th style="width: 150px;">No. AP &amp; Tipe</th>
                                <th style="width: 140px;">No. Faktur Fisik</th>
                                <th style="width: 110px;">Tgl Faktur</th>
                                <th style="width: 120px;">Jatuh Tempo</th>
                                <th style="width: 140px; text-align: right;">Sisa Tagihan</th>
                                <th style="width: 235px; text-align: right;">Nominal Bayar (BKK)</th>
                            </tr>
                        </thead>
                        <tbody id="invoiceTableBody">
                            @forelse ($unpaidHutang as $idx => $inv)
                                @php
                                    $isPreSelected = ($selectedHutang && $selectedHutang->hutang_id == $inv->hutang_id);
                                    $sisa = (float) $inv->sisa_hutang;

                                    $daysRemaining = round(now()->diffInDays(\Carbon\Carbon::parse($inv->tgl_jatuh_tempo), false));
                                    if ($daysRemaining < 0) {
                                        $tempoClass = 'overdue';
                                        $tempoText = '⚠️ Lewat ' . abs($daysRemaining) . ' hr!';
                                    } elseif ($daysRemaining <= 7) {
                                        $tempoClass = 'urgent';
                                        $tempoText = '⏳ ' . $daysRemaining . ' hr lagi';
                                    } else {
                                        $tempoClass = 'safe';
                                        $tempoText = '📅 ' . $daysRemaining . ' hr';
                                    }

                                    $badgeTipe = 'Beban Ops';
                                    $badgeStyle = 'background: #f1f5f9; color: #475569;';
                                    if ($inv->tipe_hutang === 'PEMBELIAN_GRN') {
                                        $badgeTipe = '🌾 Bahan Baku';
                                        $badgeStyle = 'background: #eff6ff; color: #1d4ed8;';
                                    } elseif ($inv->tipe_hutang === 'GAS_UTILITAS') {
                                        $badgeTipe = '🔥 Gas Utilitas';
                                        $badgeStyle = 'background: #fff7ed; color: #c2410c;';
                                    }
                                @endphp
                                <tr class="invoice-table-row {{ $isPreSelected ? 'row-selected' : '' }}"
                                    data-hutang-id="{{ $inv->hutang_id }}"
                                    data-supplier-id="{{ $inv->supplier_id }}"
                                    data-sisa="{{ $sisa }}">
                                    <td style="text-align: center;">
                                        <input type="checkbox" 
                                               id="chk_hutang_{{ $inv->hutang_id }}" 
                                               class="fast-row-chk invoice-chk"
                                               {{ $isPreSelected ? 'checked' : '' }}
                                               onclick="toggleInvoiceRow({{ $inv->hutang_id }})">
                                    </td>
                                    <td>
                                        <div style="font-weight: 800; font-family: monospace; font-size: 0.8rem; color: #0f172a;">
                                            {{ $inv->hutang_no }}
                                        </div>
                                        <span style="font-size: 0.6875rem; font-weight: 700; padding: 1px 5px; border-radius: 3px; {{ $badgeStyle }}">
                                            {{ $badgeTipe }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($inv->no_faktur_supplier)
                                            <span style="font-weight: 700; font-family: monospace; color: #334155;">{{ $inv->no_faktur_supplier }}</span>
                                        @else
                                            <span style="color: #94a3b8; font-style: italic;">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span style="font-size: 0.775rem; color: #334155;">{{ date('d/m/Y', strtotime($inv->tgl_tagihan)) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge-fast-tempo {{ $tempoClass }}">{{ $tempoText }}</span>
                                        <div style="font-size: 0.6875rem; color: #64748b; margin-top: 1px;">{{ date('d/m/Y', strtotime($inv->tgl_jatuh_tempo)) }}</div>
                                    </td>
                                    <td style="text-align: right; font-weight: 800; font-family: monospace; font-size: 0.85rem; color: #334155;">
                                        Rp {{ number_format($sisa, 0, ',', '.') }}
                                    </td>
                                    <td style="text-align: right;">
                                        <input type="hidden" name="allocations[{{ $idx }}][hutang_id]" value="{{ $inv->hutang_id }}">
                                        <div class="fast-input-bayar-group">
                                            <span style="font-size: 0.75rem; font-weight: 700; color: #64748b;">Rp</span>
                                            <input type="number" 
                                                   step="1" 
                                                   min="0" 
                                                   max="{{ $sisa }}"
                                                   name="allocations[{{ $idx }}][nominal_bayar]" 
                                                   id="nominal_bayar_{{ $inv->hutang_id }}"
                                                   value="{{ $isPreSelected ? $sisa : 0 }}" 
                                                   class="fast-nominal-input" 
                                                   placeholder="0">
                                            <button type="button" class="btn-fast-100" onclick="payFullInvoice({{ $inv->hutang_id }})" title="Bayar 100% Lunas">
                                                100%
                                            </button>
                                            <button type="button" class="btn-fast-clear" onclick="resetInvoiceRow({{ $inv->hutang_id }})" title="Kosongkan">
                                                ✕
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 2.5rem 1rem; color: #64748b;">
                                        Tidak ada faktur tagihan hutang yang belum lunas.
                                    </td>
                                </tr>
                            @endforelse

                            <tr id="invoicesEmptyState" style="display: none;">
                                <td colspan="7" style="text-align: center; padding: 2rem 1rem; color: #64748b; font-weight: 600;">
                                    Tidak ada faktur tagihan untuk rekanan ini atau tidak cocok dengan pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- KARTU 3: BOTTOM SUMMARY & ACTION BAR --}}
            <div class="detail-bottom-bar">
                <div class="fast-summary-strip">
                    <div class="fast-sum-item">
                        <span class="fast-sum-label">Faktur Dipilih</span>
                        <span class="fast-sum-val" id="summaryCountInvoices">0 Faktur</span>
                    </div>

                    <div class="fast-sum-item">
                        <span class="fast-sum-label">Total BKK Kas Keluar</span>
                        <span class="fast-sum-val highlight-green" id="summaryTotalBayar">Rp 0</span>
                    </div>

                    <div class="fast-sum-item">
                        <span class="fast-sum-label">Saldo Kas (Awal &rarr; Sisa)</span>
                        <div style="display: flex; align-items: baseline; gap: 0.4rem;">
                            <span style="font-size: 0.85rem; color: #64748b; font-family: monospace;" id="previewSaldoTersedia">Rp 0</span>
                            <span style="color: #cbd5e1;">&rarr;</span>
                            <span class="fast-sum-val" id="previewSaldoSesudah" style="font-size: 0.95rem;">Rp 0</span>
                        </div>
                    </div>

                    <div id="boxSaldoWarning" style="display: none; padding: 4px 10px; border-radius: 6px; background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; font-size: 0.75rem; font-weight: 800;">
                        ⚠️ Saldo Kas Defisit / Kurang!
                    </div>
                </div>

                <div class="fast-actions-group">
                    <a href="{{ route('keuangan.bayar.index') }}" class="btn btn-secondary" style="font-weight: 700; font-size: 0.85rem; height: 38px; display: inline-flex; align-items: center; padding: 0 1rem;">
                        Batal
                    </a>
                    <button type="submit" id="btnSubmitBayar" class="btn-fast-submit" disabled>
                        <span>⚡ SIMPAN &amp; TERBITKAN BKK</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/keuangan/bayar/bayar-create.js') }}"></script>
@endpush
