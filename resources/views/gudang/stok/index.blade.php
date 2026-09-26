@extends('layouts.app')

@section('title', 'Monitoring Stok Gudang (Split-Screen) - ERP PT Mirasa')

@section('content')
{{-- Header Halaman & Tombol Aksi Cepat --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem;">
    <div>
        <h1 style="font-size: 1.4rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 0.5rem; margin: 0;">
            <span>Monitoring & Lacak Stok Gudang</span>
            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.75rem; font-weight: 700;">Anti-Scroll Viewport</span>
        </h1>
        <p style="color: #64748b; font-size: 0.825rem; margin: 0.2rem 0 0 0;">
            Pantau saldo fisik on-hand, ambang safety stock, dan prioritas batch FIFO dalam satu tampilan layar ringkas.
        </p>
    </div>
    
    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        {{-- View Switcher --}}
        <div style="display: inline-flex; background: #e2e8f0; padding: 0.2rem; border-radius: 8px; gap: 0.2rem;">
            <a href="{{ route('gudang.stok.index', array_merge(request()->except(['view', 'page']), ['view' => 'split'])) }}" 
               class="btn btn-sm" 
               style="background: {{ $viewType === 'split' ? '#ffffff' : 'transparent' }}; color: {{ $viewType === 'split' ? '#0f172a' : '#64748b' }}; box-shadow: {{ $viewType === 'split' ? '0 1px 3px rgba(0,0,0,0.1)' : 'none' }}; border-radius: 6px; font-weight: 700; padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                ⚡ Split-Screen (Master-Detail)
            </a>
            <a href="{{ route('gudang.stok.index', array_merge(request()->except(['view', 'page']), ['view' => 'summary'])) }}" 
               class="btn btn-sm" 
               style="background: {{ $viewType === 'summary' ? '#ffffff' : 'transparent' }}; color: {{ $viewType === 'summary' ? '#0f172a' : '#64748b' }}; box-shadow: {{ $viewType === 'summary' ? '0 1px 3px rgba(0,0,0,0.1)' : 'none' }}; border-radius: 6px; font-weight: 700; padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                📦 Tabel Ringkas (2-Level)
            </a>
            <a href="{{ route('gudang.stok.index', array_merge(request()->except(['view', 'page']), ['view' => 'batch'])) }}" 
               class="btn btn-sm" 
               style="background: {{ $viewType === 'batch' ? '#ffffff' : 'transparent' }}; color: {{ $viewType === 'batch' ? '#0f172a' : '#64748b' }}; box-shadow: {{ $viewType === 'batch' ? '0 1px 3px rgba(0,0,0,0.1)' : 'none' }}; border-radius: 6px; font-weight: 700; padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                📋 Sheet per-Batch
            </a>
        </div>

        <a href="{{ route('gudang.terima.create') }}" class="btn btn-primary btn-sm" style="background: #059669; border-color: #059669; padding: 0.4rem 0.85rem; font-weight: 600;">
            + Terima Masuk
        </a>
        <a href="{{ route('gudang.pemakaian.create') }}" class="btn btn-primary btn-sm" style="background: #dc2626; border-color: #dc2626; padding: 0.4rem 0.85rem; font-weight: 600;">
            - Catat Keluar
        </a>
    </div>
</div>

{{-- Mini KPI Strip (Ringkas 1-Baris, Tidak Memakan Tinggi Layar) --}}
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; margin-bottom: 0.75rem;">
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.6rem 0.85rem; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Valuasi Persediaan</div>
            <div style="font-size: 1.05rem; font-weight: 800; color: #0f172a;">Rp {{ number_format($kpiMetrics['total_nilai'], 0, ',', '.') }}</div>
        </div>
        <div style="font-size: 1.3rem;">💰</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.6rem 0.85rem; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Item (SKU)</div>
            <div style="font-size: 1.05rem; font-weight: 800; color: #0f172a;">{{ $kpiMetrics['total_sku'] }} <span style="font-size: 0.75rem; font-weight: 600; color: #2563eb;">({{ $kpiMetrics['sku_tersedia'] }} Ada Stok)</span></div>
        </div>
        <div style="font-size: 1.3rem;">📦</div>
    </div>

    <div style="background: {{ $kpiMetrics['sku_menipis'] > 0 ? '#fffbeb' : '#ffffff' }}; border: 1px solid {{ $kpiMetrics['sku_menipis'] > 0 ? '#fde68a' : '#e2e8f0' }}; border-radius: 8px; padding: 0.6rem 0.85rem; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <div style="font-size: 0.7rem; font-weight: 700; color: {{ $kpiMetrics['sku_menipis'] > 0 ? '#b45309' : '#64748b' }}; text-transform: uppercase;">Stok Menipis / Kritis</div>
            <div style="font-size: 1.05rem; font-weight: 800; color: {{ $kpiMetrics['sku_menipis'] > 0 ? '#b45309' : '#0f172a' }};">{{ $kpiMetrics['sku_menipis'] }} <span style="font-size: 0.75rem; font-weight: 500;">Item $\le$ Safety</span></div>
        </div>
        <div style="font-size: 1.3rem;">⚠️</div>
    </div>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.6rem 0.85rem; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Batch Aktif (FIFO)</div>
            <div style="font-size: 1.05rem; font-weight: 800; color: #0f172a;">{{ $kpiMetrics['batch_aktif'] }} <span style="font-size: 0.75rem; font-weight: 600; color: #7c3aed;">Batch Tersedia</span></div>
        </div>
        <div style="font-size: 1.3rem;">🏷️</div>
    </div>
</div>

{{-- ======================================================== --}}
{{-- KONSEP 1: SPLIT-SCREEN MASTER-DETAIL (ANTI-SCROLL)      --}}
{{-- ======================================================== --}}
@if ($viewType === 'split')
<div style="display: flex; gap: 0.85rem; height: calc(100vh - 215px); min-height: 540px; box-sizing: border-box;">
    
    {{-- PANEL KIRI: DAFTAR BARANG (MASTER LIST - LEBAR 38%) --}}
    <div style="flex: 0 0 38%; display: flex; flex-direction: column; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        {{-- Search & Filter Bar Kiri --}}
        <div style="padding: 0.75rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
            <div style="position: relative; margin-bottom: 0.5rem;">
                <input type="text" id="masterSearchInput" placeholder="Ketik nama atau kode barang..." 
                       style="width: 100%; padding: 0.45rem 2rem 0.45rem 0.75rem; font-size: 0.85rem; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; box-sizing: border-box;"
                       oninput="filterMasterList()">
                <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.85rem; pointer-events: none;">🔍</span>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.5rem;">
                {{-- Quick Status Filter Chips --}}
                <div style="display: flex; gap: 0.25rem;">
                    <button type="button" class="btn-chip active" data-filter="all" onclick="setChipFilter('all', this)" style="padding: 0.2rem 0.5rem; font-size: 0.725rem; border-radius: 4px; border: 1px solid #cbd5e1; background: #0f172a; color: #ffffff; cursor: pointer; font-weight: 600;">
                        Semua
                    </button>
                    <button type="button" class="btn-chip" data-filter="ada" onclick="setChipFilter('ada', this)" style="padding: 0.2rem 0.5rem; font-size: 0.725rem; border-radius: 4px; border: 1px solid #cbd5e1; background: #ffffff; color: #059669; cursor: pointer; font-weight: 600;">
                        🟢 Ada Stok
                    </button>
                    <button type="button" class="btn-chip" data-filter="menipis" onclick="setChipFilter('menipis', this)" style="padding: 0.2rem 0.5rem; font-size: 0.725rem; border-radius: 4px; border: 1px solid #cbd5e1; background: #ffffff; color: #d97706; cursor: pointer; font-weight: 600;">
                        ⚠️ Menipis
                    </button>
                    <button type="button" class="btn-chip" data-filter="habis" onclick="setChipFilter('habis', this)" style="padding: 0.2rem 0.5rem; font-size: 0.725rem; border-radius: 4px; border: 1px solid #cbd5e1; background: #ffffff; color: #dc2626; cursor: pointer; font-weight: 600;">
                        🔴 Habis
                    </button>
                </div>

                {{-- Gudang Selector --}}
                <form action="{{ route('gudang.stok.index') }}" method="GET" style="margin: 0;">
                    <input type="hidden" name="view" value="split">
                    <select name="gudang_id" class="form-control" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px; max-width: 220px;" onchange="this.form.submit()">
                        <option value="">Semua Lokasi / Entitas</option>
                        @foreach ($gudangList as $gdg)
                            <option value="{{ $gdg->gudang_id }}" {{ $gudangId == $gdg->gudang_id ? 'selected' : '' }}>
                                {{ $gdg->display_name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        {{-- Scrollable List of Items --}}
        <div id="masterListContainer" style="flex: 1; overflow-y: auto; padding: 0.4rem; display: flex; flex-direction: column; gap: 0.35rem;">
            @php $firstItemJson = null; @endphp
            @forelse ($summaryList as $index => $item)
                @php
                    $sisaQty = (float) $item->total_sisa_qty;
                    $minStok = (float) ($item->batas_minimum_qty ?? 0);
                    $isHabis = $sisaQty <= 0;
                    $isMenipis = !$isHabis && $minStok > 0 && $sisaQty <= $minStok;
                    $statusCat = $isHabis ? 'habis' : ($isMenipis ? 'menipis' : 'ada');

                    $itemData = [
                        'barang_id'          => $item->barang_id,
                        'barang_cd'          => $item->barang_cd,
                        'barang_nm'          => $item->barang_nm,
                        'jenis_nm'           => $item->jenisBarang?->jenis_barang_nm ?? 'Umum',
                        'satuan_nm'          => $item->satuanDasar?->satuan_nm ?? 'Unit',
                        'sisa_qty'           => $sisaQty,
                        'min_stok'           => $minStok,
                        'qty_awal'           => (float) $item->total_qty_awal,
                        'qty_keluar'         => max(0, (float) $item->total_qty_awal - $sisaQty),
                        'nilai_total'        => (float) $item->total_sisa_nilai,
                        'active_batch_count' => (int) $item->active_batch_count,
                        'status'             => $statusCat,
                        'batches'            => $item->stokBatches->map(function($b) {
                            return [
                                'batch_no'     => $b->batch_no,
                                'gudang_nm'    => $b->gudang?->gudang_nm ?? '-',
                                'sisa_qty'     => (float) $b->sisa_qty,
                                'qty_awal'     => (float) $b->qty_awal,
                                'harga_satuan' => (float) $b->harga_satuan,
                                'nilai'        => (float) $b->sisa_qty * (float) $b->harga_satuan,
                                'expired_tgl'  => $b->expired_tgl ? \Carbon\Carbon::parse($b->expired_tgl)->format('d/m/Y') : '-',
                                'tgl_terima'   => $b->created_at ? $b->created_at->format('d/m/Y') : '-',
                                'is_habis'     => (float) $b->sisa_qty <= 0,
                            ];
                        })->toArray()
                    ];

                    if ($index === 0) {
                        $firstItemJson = $itemData;
                    }
                @endphp

                <div class="master-item-card {{ $index === 0 ? 'selected' : '' }}" 
                     id="item-card-{{ $item->barang_id }}"
                     data-id="{{ $item->barang_id }}"
                     data-status="{{ $statusCat }}"
                     data-search="{{ strtolower($item->barang_cd . ' ' . $item->barang_nm . ' ' . ($item->jenisBarang?->jenis_barang_nm ?? '')) }}"
                     onclick="selectBarang({{ json_encode($itemData) }})"
                     style="padding: 0.55rem 0.75rem; border-radius: 8px; border: 1px solid {{ $index === 0 ? '#0284c7' : '#e2e8f0' }}; background: {{ $index === 0 ? '#f0f9ff' : '#ffffff' }}; cursor: pointer; transition: all 0.15s ease; border-left: 4px solid {{ $index === 0 ? '#0284c7' : ($isHabis ? '#cbd5e1' : ($isMenipis ? '#d97706' : '#059669')) }};">
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.15rem;">
                        <span style="font-family: monospace; font-size: 0.75rem; font-weight: 700; color: #475569;">
                            {{ $item->barang_cd }}
                        </span>
                        <div style="display: flex; gap: 0.25rem; align-items: center;">
                            @if ($isHabis)
                                <span style="background: #fee2e2; color: #991b1b; padding: 0.1rem 0.35rem; border-radius: 3px; font-size: 0.65rem; font-weight: 700;">HABIS</span>
                            @elseif ($isMenipis)
                                <span style="background: #fef3c7; color: #b45309; padding: 0.1rem 0.35rem; border-radius: 3px; font-size: 0.65rem; font-weight: 700;">⚠️ MENIPIS</span>
                            @else
                                <span style="background: #dcfce7; color: #166534; padding: 0.1rem 0.35rem; border-radius: 3px; font-size: 0.65rem; font-weight: 700;">🟢 AMAN</span>
                            @endif
                        </div>
                    </div>

                    <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $item->barang_nm }}
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.25rem; font-size: 0.75rem;">
                        <span style="color: #64748b;">
                            {{ $item->jenisBarang?->jenis_barang_nm ?? 'Umum' }}
                        </span>
                        <span style="font-weight: 800; color: {{ $isHabis ? '#94a3b8' : ($isMenipis ? '#d97706' : '#059669') }}; font-size: 0.85rem;">
                            {{ number_format($sisaQty, 2, ',', '.') }} {{ $item->satuanDasar?->satuan_nm }}
                        </span>
                    </div>
                </div>
            @empty
                <div style="padding: 2rem 1rem; text-align: center; color: #94a3b8; font-size: 0.85rem;">
                    Tidak ada barang yang cocok.
                </div>
            @endforelse
        </div>

        {{-- Footer Panel Kiri --}}
        <div style="padding: 0.4rem 0.75rem; background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 0.725rem; color: #64748b; display: flex; justify-content: space-between; align-items: center;">
            <span id="itemCountLabel">Menampilkan {{ count($summaryList) }} barang</span>
            <span>💡 Klik barang untuk melihat rincian batch</span>
        </div>
    </div>

    {{-- PANEL KANAN: DETAIL BARANG & BATCH FIFO (LEBAR 62%) --}}
    <div id="detailPanel" style="flex: 1; display: flex; flex-direction: column; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        {{-- Detail Header --}}
        <div style="padding: 0.85rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.4rem;">
                    <span id="dtlCd" style="font-family: monospace; font-size: 0.8rem; font-weight: 800; background: #e2e8f0; padding: 0.15rem 0.45rem; border-radius: 4px; color: #1e293b;">
                        -
                    </span>
                    <span id="dtlJenis" style="background: #eff6ff; color: #1d4ed8; padding: 0.15rem 0.45rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                        -
                    </span>
                    <span id="dtlStatusBadge"></span>
                </div>
                <h2 id="dtlNm" style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0.25rem 0 0 0;">
                    Pilih barang di sebelah kiri
                </h2>
            </div>

            <div style="display: flex; gap: 0.4rem;">
                <a id="dtlBtnKeluar" href="{{ route('gudang.pemakaian.create') }}" class="btn btn-sm" style="background: #dc2626; color: #ffffff; font-weight: 600; padding: 0.35rem 0.75rem; font-size: 0.775rem;">
                    - Catat Keluar
                </a>
                <a id="dtlBtnLedger" href="#" class="btn btn-sm btn-secondary" style="font-weight: 600; padding: 0.35rem 0.75rem; font-size: 0.775rem;">
                    📜 Kartu Stok &rarr;
                </a>
            </div>
        </div>

        {{-- Detail Body (Scrollable secara internal jika batch banyak) --}}
        <div style="flex: 1; overflow-y: auto; padding: 1rem 1.25rem;">
            {{-- 4 Kotak Ringkasan Cepat --}}
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.6rem; margin-bottom: 1rem;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.5rem 0.75rem;">
                    <div style="font-size: 0.7rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Sisa Fisik On-Hand</div>
                    <div id="dtlSisaQty" style="font-size: 1.1rem; font-weight: 800; color: #059669; margin-top: 0.1rem;">-</div>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.5rem 0.75rem;">
                    <div style="font-size: 0.7rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Safety Stock (Min)</div>
                    <div id="dtlMinStok" style="font-size: 1.1rem; font-weight: 700; color: #475569; margin-top: 0.1rem;">-</div>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.5rem 0.75rem;">
                    <div style="font-size: 0.7rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Riwayat Masuk / Keluar</div>
                    <div id="dtlAwalKeluar" style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-top: 0.25rem;">-</div>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.5rem 0.75rem;">
                    <div style="font-size: 0.7rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Estimasi Nilai Stok</div>
                    <div id="dtlNilaiTotal" style="font-size: 1rem; font-weight: 800; color: #0f172a; margin-top: 0.1rem;">-</div>
                </div>
            </div>

            {{-- Bagian Tabel Batch FIFO --}}
            <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                <div style="background: #0f394c; color: #ffffff; padding: 0.55rem 0.85rem; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 0.8rem; font-weight: 700; display: flex; align-items: center; gap: 0.35rem;">
                        <span>Rincian Batch FIFO & FEFO</span>
                        <span id="dtlBatchCountBadge" style="background: #0284c7; padding: 0.1rem 0.4rem; border-radius: 10px; font-size: 0.7rem;">0 Batch</span>
                    </div>
                    <div style="font-size: 0.7rem; color: #cbd5e1;">
                        ⭐ Urutan teratas wajib digunakan terlebih dahulu
                    </div>
                </div>

                <div style="overflow-x: auto; max-height: 280px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                        <thead style="position: sticky; top: 0; background: #f1f5f9; z-index: 5;">
                            <tr style="border-bottom: 1px solid #cbd5e1; color: #475569; font-weight: 700;">
                                <th style="padding: 0.5rem 0.65rem; text-align: center; width: 40px;">No</th>
                                <th style="padding: 0.5rem 0.65rem; text-align: left;">Nomor Batch</th>
                                <th style="padding: 0.5rem 0.65rem; text-align: left;">Gudang</th>
                                <th style="padding: 0.5rem 0.65rem; text-align: left;">Tgl Terima</th>
                                <th style="padding: 0.5rem 0.65rem; text-align: left;">Tgl Kadaluarsa</th>
                                <th style="padding: 0.5rem 0.65rem; text-align: right; font-weight: 800;">Sisa Fisik</th>
                                <th style="padding: 0.5rem 0.65rem; text-align: right;">Harga Satuan</th>
                                <th style="padding: 0.5rem 0.65rem; text-align: right;">Total Nilai</th>
                                <th style="padding: 0.5rem 0.65rem; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="dtlBatchTbody">
                            {{-- Diisi secara dinamis oleh JavaScript --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT INTERAKTIF MASTER-DETAIL --}}
<script>
    let activeFilter = 'all';

    function selectBarang(item) {
        if (!item) return;

        // 1. Highlight kartu terpilih di panel kiri
        document.querySelectorAll('.master-item-card').forEach(el => {
            el.style.border = '1px solid #e2e8f0';
            el.style.background = '#ffffff';
        });

        const activeCard = document.getElementById('item-card-' + item.barang_id);
        if (activeCard) {
            activeCard.style.border = '1px solid #0284c7';
            activeCard.style.background = '#f0f9ff';
        }

        // 2. Isi data header detail panel kanan
        document.getElementById('dtlCd').textContent = item.barang_cd;
        document.getElementById('dtlNm').textContent = item.barang_nm;
        document.getElementById('dtlJenis').textContent = item.jenis_nm;

        // Badge Status
        const badgeSpan = document.getElementById('dtlStatusBadge');
        if (item.status === 'habis') {
            badgeSpan.innerHTML = '<span style="background: #fee2e2; color: #991b1b; padding: 0.15rem 0.45rem; border-radius: 4px; font-size: 0.725rem; font-weight: 700;">🔴 STOK HABIS</span>';
        } else if (item.status === 'menipis') {
            badgeSpan.innerHTML = '<span style="background: #fef3c7; color: #b45309; padding: 0.15rem 0.45rem; border-radius: 4px; font-size: 0.725rem; font-weight: 700;">⚠️ STOK MENIPIS</span>';
        } else {
            badgeSpan.innerHTML = '<span style="background: #dcfce7; color: #166534; padding: 0.15rem 0.45rem; border-radius: 4px; font-size: 0.725rem; font-weight: 700;">🟢 STOK AMAN</span>';
        }

        // Link Tombol Aksi
        document.getElementById('dtlBtnLedger').href = `{{ route('gudang.stok.ledger') }}?barang_id=${item.barang_id}`;

        // 3. Isi 4 Quick Box
        const sisaColor = item.status === 'habis' ? '#94a3b8' : (item.status === 'menipis' ? '#d97706' : '#059669');
        document.getElementById('dtlSisaQty').innerHTML = `<span style="color: ${sisaColor}">${item.sisa_qty.toLocaleString('id-ID', {minimumFractionDigits: 2})}</span> <span style="font-size: 0.75rem; font-weight: 600; color: #64748b;">${item.satuan_nm}</span>`;
        
        document.getElementById('dtlMinStok').textContent = item.min_stok > 0 
            ? `${item.min_stok.toLocaleString('id-ID')} ${item.satuan_nm}` 
            : 'Belum diatur';

        document.getElementById('dtlAwalKeluar').innerHTML = `Masuk: ${item.qty_awal.toLocaleString('id-ID')} <br> Keluar: <span style="color: #dc2626">${item.qty_keluar.toLocaleString('id-ID')}</span>`;

        document.getElementById('dtlNilaiTotal').textContent = 'Rp ' + item.nilai_total.toLocaleString('id-ID');

        // 4. Render Tabel Batch FIFO
        const tbody = document.getElementById('dtlBatchTbody');
        const countBadge = document.getElementById('dtlBatchCountBadge');
        countBadge.textContent = `${item.batches ? item.batches.length : 0} Batch`;

        if (!item.batches || item.batches.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" style="text-align: center; padding: 2rem 1rem; color: #94a3b8;">Belum ada catatan batch penerimaan untuk barang ini.</td></tr>`;
            return;
        }

        let html = '';
        item.batches.forEach((b, idx) => {
            const isFirst = (idx === 0 && !b.is_habis);
            const rowBg = b.is_habis ? '#fafafa' : (isFirst ? '#f0fdf4' : '#ffffff');
            const textColor = b.is_habis ? '#94a3b8' : '#0f172a';

            html += `
                <tr style="border-bottom: 1px solid #f1f5f9; background: ${rowBg};">
                    <td style="padding: 0.45rem 0.65rem; text-align: center; color: #64748b;">
                        ${isFirst ? '<span title="Prioritas 1 FIFO">⭐</span>' : (idx + 1)}
                    </td>
                    <td style="padding: 0.45rem 0.65rem;">
                        <span style="font-family: monospace; font-weight: 700; font-size: 0.775rem; background: ${b.is_habis ? '#f1f5f9' : '#e0f2fe'}; color: ${b.is_habis ? '#94a3b8' : '#0369a1'}; padding: 0.15rem 0.4rem; border-radius: 4px; border: 1px solid ${b.is_habis ? '#e2e8f0' : '#bae6fd'};">
                            ${b.batch_no}
                        </span>
                    </td>
                    <td style="padding: 0.45rem 0.65rem; color: #334155;">${b.gudang_nm}</td>
                    <td style="padding: 0.45rem 0.65rem; color: #64748b;">${b.tgl_terima}</td>
                    <td style="padding: 0.45rem 0.65rem; color: #64748b;">${b.expired_tgl}</td>
                    <td style="padding: 0.45rem 0.65rem; text-align: right; font-weight: 800; color: ${b.is_habis ? '#94a3b8' : '#059669'};">
                        ${b.sisa_qty.toLocaleString('id-ID', {minimumFractionDigits: 2})} ${item.satuan_nm}
                    </td>
                    <td style="padding: 0.45rem 0.65rem; text-align: right; color: #475569;">
                        Rp ${b.harga_satuan.toLocaleString('id-ID')}
                    </td>
                    <td style="padding: 0.45rem 0.65rem; text-align: right; font-weight: 700; color: ${textColor};">
                        Rp ${b.nilai.toLocaleString('id-ID')}
                    </td>
                    <td style="padding: 0.45rem 0.65rem; text-align: center;">
                        ${b.is_habis 
                            ? '<span style="color: #94a3b8; font-size: 0.7rem; font-weight: 700;">HABIS</span>' 
                            : '<span style="background: #059669; color: #ffffff; padding: 0.1rem 0.4rem; border-radius: 3px; font-size: 0.675rem; font-weight: 700;">TERSEDIA</span>'}
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    // Filter Instant Panel Kiri (Pencarian & Status Chip)
    function filterMasterList() {
        const query = document.getElementById('masterSearchInput').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.master-item-card');
        let visibleCount = 0;
        let firstVisible = null;

        cards.forEach(card => {
            const searchData = card.getAttribute('data-search') || '';
            const statusData = card.getAttribute('data-status') || '';

            const matchesSearch = !query || searchData.includes(query);
            const matchesChip = (activeFilter === 'all') || (statusData === activeFilter);

            if (matchesSearch && matchesChip) {
                card.style.display = 'block';
                visibleCount++;
                if (!firstVisible) firstVisible = card;
            } else {
                card.style.display = 'none';
            }
        });

        document.getElementById('itemCountLabel').textContent = `Menampilkan ${visibleCount} barang`;
    }

    function setChipFilter(filterType, btn) {
        activeFilter = filterType;
        document.querySelectorAll('.btn-chip').forEach(b => {
            b.style.background = '#ffffff';
            b.style.color = '#475569';
        });

        btn.style.background = '#0f172a';
        btn.style.color = '#ffffff';

        filterMasterList();
    }

    // Auto-select item pertama saat halaman dimuat
    document.addEventListener('DOMContentLoaded', () => {
        const firstItem = @json($firstItemJson);
        if (firstItem) {
            selectBarang(firstItem);
        }
    });
</script>

{{-- ======================================================== --}}
{{-- KONTEN MODE 2: TABEL RINGKASAN PER BARANG (2-LEVEL)     --}}
{{-- ======================================================== --}}
@elseif ($viewType === 'summary')
<div class="card" style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
    <div class="card-header" style="background: #f8fafc; padding: 0.75rem 1.25rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0;">
        <span style="font-size: 0.85rem; color: #475569;">
            Menampilkan <strong>{{ $summaryList->total() }}</strong> barang. Klik baris untuk membuka rincian sub-batch.
        </span>
        <div style="display: flex; gap: 0.4rem;">
            <button type="button" class="btn btn-sm btn-secondary" onclick="toggleAllBatches(true)" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">
                Buka Semua Batch
            </button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="toggleAllBatches(false)" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">
                Tutup Semua Batch
            </button>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
            <thead>
                <tr style="background: #0f394c; color: #ffffff;">
                    <th style="padding: 0.65rem 0.5rem; width: 35px; text-align: center;"></th>
                    <th style="padding: 0.65rem 0.75rem; text-align: left; color: #e2e8f0;">Kode</th>
                    <th style="padding: 0.65rem 0.75rem; text-align: left; color: #e2e8f0;">Nama Barang & Kategori</th>
                    <th style="padding: 0.65rem 0.75rem; text-align: center; color: #e2e8f0;">Satuan</th>
                    <th style="padding: 0.65rem 0.75rem; text-align: right; color: #e2e8f0;">Min. Stok</th>
                    <th style="padding: 0.65rem 0.75rem; text-align: right; color: #e2e8f0;">Masuk</th>
                    <th style="padding: 0.65rem 0.75rem; text-align: right; color: #e2e8f0;">Keluar</th>
                    <th style="padding: 0.65rem 0.75rem; text-align: right; color: #e2e8f0; font-weight: 800;">Sisa Fisik</th>
                    <th style="padding: 0.65rem 0.75rem; text-align: right; color: #e2e8f0;">Nilai Persediaan</th>
                    <th style="padding: 0.65rem 0.75rem; text-align: center; color: #e2e8f0;">Status</th>
                    <th style="padding: 0.65rem 0.75rem; text-align: center; color: #e2e8f0;">Batch</th>
                    <th style="padding: 0.65rem 1rem; text-align: right; color: #e2e8f0;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($summaryList as $item)
                    @php
                        $sisaQty = (float) $item->total_sisa_qty;
                        $qtyAwal = (float) $item->total_qty_awal;
                        $qtyKeluar = max(0, $qtyAwal - $sisaQty);
                        $minStok = (float) ($item->batas_minimum_qty ?? 0);
                        $isHabis = $sisaQty <= 0;
                        $isMenipis = !$isHabis && $minStok > 0 && $sisaQty <= $minStok;
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9; cursor: pointer;" onclick="toggleBatchRow({{ $item->barang_id }})">
                        <td style="text-align: center; padding: 0.65rem 0.25rem;">
                            <span id="icon-chevron-{{ $item->barang_id }}" style="display: inline-block; font-size: 0.75rem; color: #64748b;">▶</span>
                        </td>
                        <td style="padding: 0.65rem 0.75rem; font-family: monospace; font-weight: 700;">{{ $item->barang_cd }}</td>
                        <td style="padding: 0.65rem 0.75rem; font-weight: 700; color: #0f172a;">{{ $item->barang_nm }}</td>
                        <td style="padding: 0.65rem 0.75rem; text-align: center;">{{ $item->satuanDasar?->satuan_nm }}</td>
                        <td style="padding: 0.65rem 0.75rem; text-align: right; color: #64748b;">{{ $minStok > 0 ? number_format($minStok, 0, ',', '.') : '-' }}</td>
                        <td style="padding: 0.65rem 0.75rem; text-align: right;">{{ number_format($qtyAwal, 2, ',', '.') }}</td>
                        <td style="padding: 0.65rem 0.75rem; text-align: right; color: #dc2626;">{{ number_format($qtyKeluar, 2, ',', '.') }}</td>
                        <td style="padding: 0.65rem 0.75rem; text-align: right; font-weight: 800; color: {{ $isHabis ? '#94a3b8' : ($isMenipis ? '#d97706' : '#059669') }};">
                            {{ number_format($sisaQty, 2, ',', '.') }}
                        </td>
                        <td style="padding: 0.65rem 0.75rem; text-align: right; font-weight: 700;">Rp {{ number_format((float) $item->total_sisa_nilai, 0, ',', '.') }}</td>
                        <td style="padding: 0.65rem 0.75rem; text-align: center;">
                            @if ($isHabis)
                                <span style="background: #fee2e2; color: #991b1b; padding: 0.15rem 0.4rem; border-radius: 3px; font-size: 0.675rem; font-weight: 700;">HABIS</span>
                            @elseif ($isMenipis)
                                <span style="background: #fef3c7; color: #b45309; padding: 0.15rem 0.4rem; border-radius: 3px; font-size: 0.675rem; font-weight: 700;">⚠️ MENIPIS</span>
                            @else
                                <span style="background: #dcfce7; color: #166534; padding: 0.15rem 0.4rem; border-radius: 3px; font-size: 0.675rem; font-weight: 700;">🟢 AMAN</span>
                            @endif
                        </td>
                        <td style="padding: 0.65rem 0.75rem; text-align: center;">
                            <span style="background: #e0e7ff; color: #3730a3; padding: 0.15rem 0.45rem; border-radius: 10px; font-size: 0.7rem; font-weight: 700;">
                                {{ $item->active_batch_count }} Batch
                            </span>
                        </td>
                        <td style="padding: 0.65rem 1rem; text-align: right;" onclick="event.stopPropagation();">
                            <a href="{{ route('gudang.stok.ledger', ['barang_id' => $item->barang_id, 'gudang_id' => $gudangId]) }}" class="btn btn-secondary btn-sm" style="font-size: 0.725rem; padding: 0.2rem 0.45rem;">
                                Kartu &rarr;
                            </a>
                        </td>
                    </tr>

                    {{-- Drawer Sub-tabel Batch --}}
                    <tr id="drawer-batch-{{ $item->barang_id }}" style="display: none; background: #f8fafc;">
                        <td colspan="12" style="padding: 0.75rem 1.25rem;">
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.775rem;">
                                    <thead>
                                        <tr style="background: #f1f5f9; color: #475569;">
                                            <th style="padding: 0.4rem 0.6rem; text-align: left;">No. Batch</th>
                                            <th style="padding: 0.4rem 0.6rem; text-align: left;">Gudang</th>
                                            <th style="padding: 0.4rem 0.6rem; text-align: left;">Tgl Terima</th>
                                            <th style="padding: 0.4rem 0.6rem; text-align: right; font-weight: 800;">Sisa Qty</th>
                                            <th style="padding: 0.4rem 0.6rem; text-align: right;">Harga Satuan</th>
                                            <th style="padding: 0.4rem 0.6rem; text-align: right;">Total Nilai</th>
                                            <th style="padding: 0.4rem 0.6rem; text-align: center;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($item->stokBatches as $b)
                                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                                <td style="padding: 0.4rem 0.6rem; font-family: monospace; font-weight: 700;">{{ $b->batch_no }}</td>
                                                <td style="padding: 0.4rem 0.6rem;">{{ $b->gudang?->gudang_nm }}</td>
                                                <td style="padding: 0.4rem 0.6rem; color: #64748b;">{{ $b->created_at ? $b->created_at->format('d/m/Y') : '-' }}</td>
                                                <td style="padding: 0.4rem 0.6rem; text-align: right; font-weight: 800; color: {{ (float)$b->sisa_qty <= 0 ? '#94a3b8' : '#059669' }};">
                                                    {{ number_format((float)$b->sisa_qty, 2, ',', '.') }}
                                                </td>
                                                <td style="padding: 0.4rem 0.6rem; text-align: right;">Rp {{ number_format((float)$b->harga_satuan, 0, ',', '.') }}</td>
                                                <td style="padding: 0.4rem 0.6rem; text-align: right; font-weight: 700;">Rp {{ number_format((float)$b->sisa_qty * (float)$b->harga_satuan, 0, ',', '.') }}</td>
                                                <td style="padding: 0.4rem 0.6rem; text-align: center;">
                                                    {{ (float)$b->sisa_qty <= 0 ? 'HABIS' : 'TERSEDIA' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="12" style="text-align: center; padding: 2rem;">Tidak ada barang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleBatchRow(id) {
    const d = document.getElementById('drawer-batch-' + id);
    const c = document.getElementById('icon-chevron-' + id);
    if (!d) return;
    const isHidden = d.style.display === 'none' || d.style.display === '';
    d.style.display = isHidden ? 'table-row' : 'none';
    if (c) c.style.transform = isHidden ? 'rotate(90deg)' : 'rotate(0deg)';
}
function toggleAllBatches(show) {
    document.querySelectorAll('[id^="drawer-batch-"]').forEach(el => el.style.display = show ? 'table-row' : 'none');
    document.querySelectorAll('[id^="icon-chevron-"]').forEach(el => el.style.transform = show ? 'rotate(90deg)' : 'rotate(0deg)');
}
</script>

{{-- ======================================================== --}}
{{-- KONTEN MODE 3: DETAIL SHEET PER-BATCH (FLAT FORMAT EXCEL)--}}
{{-- ======================================================== --}}
@else
<div class="card" style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
            <thead>
                <tr style="background: #0f394c; color: #ffffff;">
                    <th style="padding: 0.65rem 0.75rem; text-align: left;">Kode Batch</th>
                    <th style="padding: 0.65rem 0.75rem; text-align: left;">Kode Barang</th>
                    <th style="padding: 0.65rem 0.75rem; text-align: left;">Nama Barang & Gudang</th>
                    <th style="padding: 0.65rem 0.75rem; text-align: right;">Qty Awal</th>
                    <th style="padding: 0.65rem 0.75rem; text-align: right;">Harga Satuan</th>
                    <th style="padding: 0.65rem 0.75rem; text-align: right;">Qty Keluar</th>
                    <th style="padding: 0.65rem 0.75rem; text-align: right; font-weight: 800;">Sisa Qty</th>
                    <th style="padding: 0.65rem 0.75rem; text-align: right; font-weight: 700;">Sisa Nilai</th>
                    <th style="padding: 0.65rem 0.75rem; text-align: center;">Status</th>
                    <th style="padding: 0.65rem 1rem; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stokList as $b)
                    @php
                        $isHabis = (float) $b->sisa_qty <= 0;
                        $qtyAwal = (float) ($b->qty_awal > 0 ? $b->qty_awal : $b->sisa_qty);
                        $qtyKeluar = max(0, $qtyAwal - (float) $b->sisa_qty);
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9; background: {{ $isHabis ? '#fafafa' : '#ffffff' }};">
                        <td style="padding: 0.65rem 0.75rem; font-family: monospace; font-weight: 700;">{{ $b->batch_no }}</td>
                        <td style="padding: 0.65rem 0.75rem; font-family: monospace;">{{ $b->barang?->barang_cd }}</td>
                        <td style="padding: 0.65rem 0.75rem;">
                            <strong>{{ $b->barang?->barang_nm }}</strong>
                            <span style="display: block; font-size: 0.75rem; color: #64748b;">{{ $b->gudang?->gudang_nm }}</span>
                        </td>
                        <td style="padding: 0.65rem 0.75rem; text-align: right;">{{ number_format($qtyAwal, 2, ',', '.') }}</td>
                        <td style="padding: 0.65rem 0.75rem; text-align: right;">Rp {{ number_format((float)$b->harga_satuan, 0, ',', '.') }}</td>
                        <td style="padding: 0.65rem 0.75rem; text-align: right; color: #dc2626;">{{ number_format($qtyKeluar, 2, ',', '.') }}</td>
                        <td style="padding: 0.65rem 0.75rem; text-align: right; font-weight: 800; color: {{ $isHabis ? '#94a3b8' : '#059669' }};">
                            {{ number_format((float)$b->sisa_qty, 2, ',', '.') }}
                        </td>
                        <td style="padding: 0.65rem 0.75rem; text-align: right; font-weight: 700;">
                            Rp {{ number_format((float)$b->sisa_qty * (float)$b->harga_satuan, 0, ',', '.') }}
                        </td>
                        <td style="padding: 0.65rem 0.75rem; text-align: center;">
                            {{ $isHabis ? 'HABIS' : 'TERSEDIA' }}
                        </td>
                        <td style="padding: 0.65rem 1rem; text-align: right;">
                            <a href="{{ route('gudang.stok.ledger', ['barang_id' => $b->barang_id, 'gudang_id' => $b->gudang_id]) }}" class="btn btn-secondary btn-sm" style="font-size: 0.725rem; padding: 0.2rem 0.45rem;">
                                Kartu &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" style="text-align: center; padding: 2rem;">Tidak ada data batch.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($stokList->hasPages())
        <div style="padding: 0.75rem 1.25rem; border-top: 1px solid #e2e8f0;">
            {{ $stokList->withQueryString()->links() }}
        </div>
    @endif
</div>
@endif

@endsection
