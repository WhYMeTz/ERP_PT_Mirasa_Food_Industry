@if ($inspeksiList->total() > 0)
    <div class="qc-pagination-container">
        {{-- Sisi Kiri: Rangkuman Data & Per Page Dropdown --}}
        <div class="qc-pagination-info">
            <span>
                Menampilkan <strong>{{ $inspeksiList->firstItem() ?? 0 }}</strong> &ndash; <strong>{{ $inspeksiList->lastItem() ?? 0 }}</strong> dari <strong>{{ number_format($inspeksiList->total(), 0, ',', '.') }}</strong> dokumen
            </span>

            <div class="qc-per-page-selector">
                <span>Baris:</span>
                <select class="qc-per-page-control" onchange="changeQcPerPage(this.value)">
                    @foreach ([10, 15, 25, 50, 100] as $size)
                        <option value="{{ $size }}" {{ ($perPage ?? 15) == $size ? 'selected' : '' }}>
                            {{ $size }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Sisi Kanan: Navigasi Page Number & Quick Jump --}}
        <div class="qc-pagination-nav">
            {{-- Tombol Sebelumnya (Prev) --}}
            @if ($inspeksiList->onFirstPage())
                <span class="qc-page-btn disabled" aria-disabled="true">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>Prev</span>
                </span>
            @else
                <a href="{{ $inspeksiList->appends(request()->query())->previousPageUrl() }}" class="qc-page-btn" rel="prev">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>Prev</span>
                </a>
            @endif

            {{-- Nomor Halaman (Sliding Window) --}}
            <div class="qc-page-numbers">
                @php
                    $currentPage = $inspeksiList->currentPage();
                    $lastPage = $inspeksiList->lastPage();
                    $start = max(1, $currentPage - 1);
                    $end = min($lastPage, $currentPage + 1);

                    if ($currentPage <= 2) {
                        $end = min($lastPage, 4);
                    }
                    if ($currentPage >= $lastPage - 1) {
                        $start = max(1, $lastPage - 3);
                    }
                @endphp

                @if ($start > 1)
                    <a href="{{ $inspeksiList->appends(request()->query())->url(1) }}" class="qc-page-number">1</a>
                    @if ($start > 2)
                        <span class="qc-page-dots">&hellip;</span>
                    @endif
                @endif

                @for ($page = $start; $page <= $end; $page++)
                    @if ($page == $currentPage)
                        <span class="qc-page-number active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $inspeksiList->appends(request()->query())->url($page) }}" class="qc-page-number">{{ $page }}</a>
                    @endif
                @endfor

                @if ($end < $lastPage)
                    @if ($end < $lastPage - 1)
                        <span class="qc-page-dots">&hellip;</span>
                    @endif
                    <a href="{{ $inspeksiList->appends(request()->query())->url($lastPage) }}" class="qc-page-number">{{ $lastPage }}</a>
                @endif
            </div>

            {{-- Tombol Berikutnya (Next) --}}
            @if ($inspeksiList->hasMorePages())
                <a href="{{ $inspeksiList->appends(request()->query())->nextPageUrl() }}" class="qc-page-btn" rel="next">
                    <span>Next</span>
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="qc-page-btn disabled" aria-disabled="true">
                    <span>Next</span>
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif

            {{-- Quick Jump Input Form (Tanpa Tombol Samping) --}}
            @if ($inspeksiList->lastPage() > 1)
                <div class="qc-page-jump">
                    <span class="qc-jump-label">Ke:</span>
                    <input type="number" 
                           min="1" 
                           max="{{ $inspeksiList->lastPage() }}" 
                           class="qc-jump-input" 
                           value="{{ $inspeksiList->currentPage() }}" 
                           title="Ketik nomor halaman lalu tekan Enter"
                           onchange="jumpToQcPage({{ $inspeksiList->lastPage() }})" 
                           onkeydown="if(event.key === 'Enter'){ event.preventDefault(); jumpToQcPage({{ $inspeksiList->lastPage() }}); }">
                    <span class="qc-jump-total">/ {{ $inspeksiList->lastPage() }}</span>
                </div>
            @endif
        </div>
    </div>
@endif
