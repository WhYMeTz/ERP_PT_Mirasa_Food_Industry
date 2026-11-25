@if ($poList->total() > 0)
    <div class="po-pagination-container">
        {{-- Sisi Kiri: Rangkuman Data & Per Page Dropdown --}}
        <div class="po-pagination-info">
            <span>
                Menampilkan <strong>{{ $poList->firstItem() ?? 0 }}</strong> &ndash; <strong>{{ $poList->lastItem() ?? 0 }}</strong> dari <strong>{{ number_format($poList->total(), 0, ',', '.') }}</strong> dokumen
            </span>

            <div class="po-per-page-selector">
                <span>Baris:</span>
                <select class="po-per-page-control" onchange="changePoPerPage(this.value)">
                    @foreach ([10, 15, 25, 50, 100] as $size)
                        <option value="{{ $size }}" {{ ($perPage ?? 15) == $size ? 'selected' : '' }}>
                            {{ $size }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Sisi Kanan: Navigasi Page Number & Quick Jump --}}
        <div class="po-pagination-nav">
            {{-- Tombol Sebelumnya (Prev) --}}
            @if ($poList->onFirstPage())
                <span class="po-page-btn disabled" aria-disabled="true">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>Prev</span>
                </span>
            @else
                <a href="{{ $poList->appends(request()->query())->previousPageUrl() }}" class="po-page-btn" rel="prev">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>Prev</span>
                </a>
            @endif

            {{-- Nomor Halaman (Sliding Window) --}}
            <div class="po-page-numbers">
                @php
                    $currentPage = $poList->currentPage();
                    $lastPage = $poList->lastPage();
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
                    <a href="{{ $poList->appends(request()->query())->url(1) }}" class="po-page-number">1</a>
                    @if ($start > 2)
                        <span class="po-page-dots">&hellip;</span>
                    @endif
                @endif

                @for ($page = $start; $page <= $end; $page++)
                    @if ($page == $currentPage)
                        <span class="po-page-number active">{{ $page }}</span>
                    @else
                        <a href="{{ $poList->appends(request()->query())->url($page) }}" class="po-page-number">{{ $page }}</a>
                    @endif
                @endfor

                @if ($end < $lastPage)
                    @if ($end < $lastPage - 1)
                        <span class="po-page-dots">&hellip;</span>
                    @endif
                    <a href="{{ $poList->appends(request()->query())->url($lastPage) }}" class="po-page-number">{{ $lastPage }}</a>
                @endif
            </div>

            {{-- Tombol Berikutnya (Next) --}}
            @if ($poList->hasMorePages())
                <a href="{{ $poList->appends(request()->query())->nextPageUrl() }}" class="po-page-btn" rel="next">
                    <span>Next</span>
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="po-page-btn disabled" aria-disabled="true">
                    <span>Next</span>
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif

            {{-- Fitur Quick Jump / Lompat Halaman --}}
            @if ($poList->lastPage() > 1)
                <div class="po-page-jump">
                    <span class="po-jump-label">Ke:</span>
                    <input type="number" 
                           id="poJumpPageInput" 
                           class="po-jump-input" 
                           min="1" 
                           max="{{ $poList->lastPage() }}" 
                           value="{{ $poList->currentPage() }}" 
                           onchange="jumpToPoPage({{ $poList->lastPage() }})"
                           onkeydown="if(event.key === 'Enter'){ event.preventDefault(); jumpToPoPage({{ $poList->lastPage() }}); }"
                           title="Ketik nomor halaman atau gunakan panah atas/bawah">
                    <span class="po-jump-total">/ {{ $poList->lastPage() }}</span>
                </div>
            @endif
        </div>
    </div>
@endif
