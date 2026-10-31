@extends('layouts.executive')

@section('title', 'Persetujuan PO & Dashboard Eksekutif - PT Mirasa Food Industry')

@section('content')
<div class="macos-dashboard-container anim-fade-up">

    {{-- ==========================================================================
         EXECUTIVE INSIGHTS KPI CARDS (APPLE CARD MINIMALIST STYLE)
         ========================================================================== --}}
    <div class="macos-metrics-grid">
        
        {{-- Card 1: Menunggu Persetujuan --}}
        <a href="{{ route('gudang.po.approval_center', ['status' => 'PENDING_APPROVAL']) }}" class="macos-kpi-card">
            <div class="kpi-top-row">
                <span class="kpi-label">Menunggu Persetujuan</span>
                @if($pendingCount > 0)
                    <span class="kpi-chip red">Perlu Tindakan</span>
                @else
                    <span class="kpi-chip green">Semua Aman</span>
                @endif
            </div>
            <div class="kpi-value-big" style="{{ $pendingCount > 0 ? 'color: #dc2626;' : '' }}">
                {{ $pendingCount }} <span style="font-size: 0.95rem; font-weight: 600; color: #71717a;">Dokumen</span>
            </div>
            <div class="kpi-sub-text">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Estimasi nilai: <strong>Rp {{ number_format($pendingTotalRp, 0, ',', '.') }}</strong></span>
            </div>
        </a>

        {{-- Card 2: Total Nilai Anggaran Pending --}}
        <div class="macos-kpi-card">
            <div class="kpi-top-row">
                <span class="kpi-label">Anggaran Belum Cair</span>
                <span class="kpi-chip neutral">Budget Pending</span>
            </div>
            <div class="kpi-value-big" style="color: #0f172a;">
                Rp {{ number_format($pendingTotalRp, 0, ',', '.') }}
            </div>
            <div class="kpi-sub-text">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Akumulasi dari {{ $pendingCount }} pengadaan bahan baku</span>
            </div>
        </div>

        {{-- Card 3: Dokumen Perlu Revisi --}}
        <a href="{{ route('gudang.po.approval_center', ['status' => 'REVISION']) }}" class="macos-kpi-card">
            <div class="kpi-top-row">
                <span class="kpi-label">Perlu Revisi</span>
                <span class="kpi-chip {{ $revisionCount > 0 ? 'blue' : 'neutral' }}">{{ $revisionCount }} PO</span>
            </div>
            <div class="kpi-value-big" style="{{ $revisionCount > 0 ? 'color: #2563eb;' : '' }}">
                {{ $revisionCount }} <span style="font-size: 0.95rem; font-weight: 600; color: #71717a;">Dokumen</span>
            </div>
            <div class="kpi-sub-text">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Dikembalikan ke staf untuk koreksi harga/qty</span>
            </div>
        </a>

        {{-- Card 4: Disetujui Bulan Ini --}}
        <a href="{{ route('gudang.po.approval_center', ['status' => 'HISTORY']) }}" class="macos-kpi-card">
            <div class="kpi-top-row">
                <span class="kpi-label">Disetujui Bulan Ini</span>
                <span class="kpi-chip green">{{ now()->translatedFormat('F Y') }}</span>
            </div>
            <div class="kpi-value-big" style="color: #059669;">
                {{ $approvedThisMonthCount }} <span style="font-size: 0.95rem; font-weight: 600; color: #71717a;">PO Sah</span>
            </div>
            <div class="kpi-sub-text">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Realisasi belanja: <strong>Rp {{ number_format($approvedThisMonthRp, 0, ',', '.') }}</strong></span>
            </div>
        </a>

    </div>

    {{-- ==========================================================================
         3. MAIN DOCUMENT TABLE ("ANTREAN PERSETUJUAN PO")
         ========================================================================== --}}
    <div class="macos-content-section">
        
        {{-- Section Title & Filter Tabs Toolbar --}}
        <div class="your-file-toolbar">
            <div class="file-toolbar-left-tabs">
                <a href="{{ route('gudang.po.approval_center') }}" 
                   class="btn-tab-pill {{ $statusFilter === 'ALL' ? 'active' : '' }}">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="1.8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 7v5l3 3"/></svg>
                    <span>Semua Antrean</span>
                </a>

                <a href="{{ route('gudang.po.approval_center', ['status' => 'PENDING_APPROVAL']) }}" 
                   class="btn-tab-pill {{ $statusFilter === 'PENDING_APPROVAL' ? 'active' : '' }}">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke-width="1.8"/></svg>
                    <span>Menunggu Persetujuan</span>
                    @if($pendingCount > 0)
                        <span class="tab-badge-pill red">{{ $pendingCount }}</span>
                    @endif
                </a>

                <a href="{{ route('gudang.po.approval_center', ['status' => 'REVISION']) }}" 
                   class="btn-tab-pill {{ $statusFilter === 'REVISION' ? 'active' : '' }}">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Perlu Revisi</span>
                    @if($revisionCount > 0)
                        <span class="tab-badge-pill blue">{{ $revisionCount }}</span>
                    @endif
                </a>

                <a href="{{ route('gudang.po.approval_center', ['status' => 'HISTORY']) }}" 
                   class="btn-tab-pill {{ $statusFilter === 'HISTORY' ? 'active' : '' }}">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Riwayat Selesai</span>
                </a>
            </div>

            <div class="file-toolbar-right-actions">
                <div class="table-search-box">
                    <svg width="14" height="14" fill="none" stroke="#94a3b8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="tableFilterInput" placeholder="Cari nomor PO / barang..." onkeyup="filterFileTable(this.value)">
                </div>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="file-table-container">
            <table class="macos-file-table" id="macosFileTable">
                <thead>
                    <tr>
                        <th style="width: 44px; text-align: center;">
                            <input type="checkbox" class="macos-checkbox-head" id="selectAllCheckbox" onclick="toggleSelectAll(this)">
                        </th>
                        <th style="width: 32%;">Nomor PO &amp; Komoditas</th>
                        <th style="width: 20%;">Mitra Supplier</th>
                        <th style="width: 14%;">Diajukan Oleh</th>
                        <th style="width: 14%;">Total Anggaran</th>
                        <th style="width: 10%; text-align: center;">Status</th>
                        <th style="width: 10%; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($poList as $index => $po)
                        @php
                            $firstDetail = $po->details->first();
                            $commodityDesc = $firstDetail ? ($firstDetail->barang?->barang_nm ?? $firstDetail->barang_nm ?? 'Bahan Pokok') : 'Pengadaan Pabrik';
                            if ($po->details->count() > 1) {
                                $commodityDesc .= ' (+' . ($po->details->count() - 1) . ' item lain)';
                            }
                            $creatorName = $po->created_by ?? 'Tim Gudang';
                            $formattedDate = \Carbon\Carbon::parse($po->po_tgl ?? $po->created_at)->translatedFormat('d M Y');
                            $nominalStr = 'Rp ' . number_format($po->total_tagihan ?? 0, 0, ',', '.');
                            $supplierNm = $po->supplier?->supplier_nm ?? 'Supplier Terdaftar';
                            $menuId = 'actionMenu_' . $po->po_id;
                        @endphp
                        <tr class="file-row {{ $po->status_cd === 'PENDING_APPROVAL' ? 'pending-highlight' : '' }}">
                            {{-- Checkbox --}}
                            <td style="text-align: center;">
                                <input type="checkbox" class="macos-checkbox" value="{{ $po->po_id }}">
                            </td>

                            {{-- Nomor Dokumen & Komoditas --}}
                            <td>
                                <div class="file-name-cell">
                                    <div class="doc-icon-wrap {{ $po->status_cd === 'PENDING_APPROVAL' ? 'red' : ($po->status_cd === 'REVISION' ? 'blue' : 'gray') }}">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div style="min-width: 0;">
                                        <a href="{{ route('gudang.po.review', $po->po_id) }}" class="file-title-link">
                                            {{ $po->po_no }}
                                        </a>
                                        <div class="commodity-sub-label" title="{{ $commodityDesc }}">
                                            {{ Str::limit($commodityDesc, 38) }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Mitra Supplier --}}
                            <td>
                                <div class="supplier-cell-wrap">
                                    <span class="supplier-name-text">{{ Str::limit($supplierNm, 26) }}</span>
                                    <span class="supplier-date-sub">{{ $formattedDate }}</span>
                                </div>
                            </td>

                            {{-- Diajukan Oleh --}}
                            <td>
                                <div class="shared-by-cell">
                                    <div class="shared-avatar">
                                        {{ strtoupper(substr($creatorName, 0, 1)) }}
                                    </div>
                                    <span class="shared-name">{{ $creatorName }}</span>
                                </div>
                            </td>

                            {{-- Total Anggaran --}}
                            <td>
                                <span class="file-nominal-bold">{{ $nominalStr }}</span>
                                <div style="font-size: 0.7rem; color: #94a3b8;">{{ $po->details->count() }} item barang</div>
                            </td>

                            {{-- Status Badge --}}
                            <td style="text-align: center;">
                                @if($po->status_cd === 'PENDING_APPROVAL')
                                    <span class="status-chip-clean chip-pending">Pending</span>
                                @elseif($po->status_cd === 'REVISION')
                                    <span class="status-chip-clean chip-revision">Revisi</span>
                                @elseif(in_array($po->status_cd, ['APPROVED', 'COMPLETED', 'PARTIAL', 'ORDERED']))
                                    <span class="status-chip-clean chip-approved">Disetujui</span>
                                @elseif($po->status_cd === 'REJECTED')
                                    <span class="status-chip-clean chip-rejected">Ditolak</span>
                                @else
                                    <span class="status-chip-clean chip-default">{{ $po->status_cd }}</span>
                                @endif
                            </td>

                            {{-- Action Buttons --}}
                            <td style="text-align: right; position: relative;">
                                <div style="display: inline-flex; align-items: center; gap: 4px;">
                                    <a href="{{ route('gudang.po.review', $po->po_id) }}" 
                                       class="btn-quick-tinjau {{ $po->status_cd === 'PENDING_APPROVAL' ? 'btn-urgent' : '' }}" 
                                       title="Buka verifikasi & persetujuan">
                                        <span>Tinjau</span>
                                    </a>

                                    <button type="button" 
                                            class="btn-row-action-dots" 
                                            onclick="toggleSmartActionDropdown(this, event, '{{ $menuId }}')"
                                            title="Menu Pilihan">
                                        <svg width="15" height="15" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                                    </button>
                                </div>

                                {{-- Dropdown Floating Menu --}}
                                <div id="{{ $menuId }}" class="action-dropdown-menu macos-action-popup">
                                    <a href="{{ route('gudang.po.review', $po->po_id) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        <span>Tinjau &amp; Setujui</span>
                                    </a>
                                    <a href="{{ route('gudang.po.export-pdf', $po->po_id) }}" target="_blank" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                        <span>Download PDF</span>
                                    </a>
                                    <a href="{{ route('gudang.po.show', $po->po_id) }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                                        <span>Buka Dokumen PO</span>
                                    </a>
                                    <a href="{{ route('gudang.po.create') }}?copy={{ $po->po_id }}" class="action-dropdown-item">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                                        <span>Salin Pengajuan</span>
                                    </a>
                                    <div style="height: 1px; background: #f4f4f5; margin: 4px 0;"></div>
                                    <button type="button" class="action-dropdown-item danger-item" onclick="confirmDeletePo('{{ $po->po_id }}', '{{ $po->po_no }}')">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Batalkan PO</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 4.5rem 1rem;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 0.75rem;">
                                    <svg width="52" height="52" fill="none" stroke="#d4d4d8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <div style="font-weight: 700; color: #18181b; font-size: 0.95rem;">Tidak Ada Antrean Persetujuan</div>
                                    <div style="font-size: 0.8125rem; color: #71717a;">Semua dokumen PO pada filter ini sudah selesai ditindaklanjuti.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($poList->hasPages())
        <div class="macos-pagination-wrap">
            <span class="pagination-summary">
                Menampilkan {{ $poList->firstItem() ?? 0 }} - {{ $poList->lastItem() ?? 0 }} dari {{ $poList->total() }} dokumen PO
            </span>
            <div>
                {{ $poList->appends(request()->query())->links() }}
            </div>
        </div>
        @endif

    </div>

</div>

{{-- Scripts for search & smart action dropdown positioning --}}
@push('scripts')
<script>
    function filterFileTable(query) {
        query = query.toLowerCase();
        const rows = document.querySelectorAll('#macosFileTable tbody tr.file-row');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    }

    function toggleSelectAll(masterCheckbox) {
        const checkboxes = document.querySelectorAll('.macos-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = masterCheckbox.checked;
            const row = cb.closest('tr');
            if (row) {
                if (masterCheckbox.checked) {
                    row.classList.add('selected');
                } else {
                    row.classList.remove('selected');
                }
            }
        });
    }

    // Smart Action Dropdown Positioning (Fixed position to avoid table overflow)
    function toggleSmartActionDropdown(triggerBtn, event, menuId) {
        event.stopPropagation();
        const menu = document.getElementById(menuId);
        if (!menu) return;

        const isCurrentlyOpen = menu.classList.contains('show');

        // Close all other dropdowns
        document.querySelectorAll('.action-dropdown-menu.show').forEach(m => {
            m.classList.remove('show');
        });

        if (!isCurrentlyOpen) {
            const rect = triggerBtn.getBoundingClientRect();
            menu.style.position = 'fixed';
            menu.style.top = (rect.bottom + 6) + 'px';
            menu.style.left = (rect.right - 180) + 'px';
            menu.style.zIndex = '99999';
            menu.classList.add('show');
        }
    }

    // Close when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.action-dropdown-menu') && !e.target.closest('.btn-row-action-dots')) {
            document.querySelectorAll('.action-dropdown-menu.show').forEach(m => {
                m.classList.remove('show');
            });
        }
    });

    function confirmDeletePo(id, no) {
        if (confirm('Apakah Anda yakin ingin membatalkan dokumen PO #' + no + '?')) {
            // Can trigger post cancellation
        }
    }
</script>
@endpush
@endsection
