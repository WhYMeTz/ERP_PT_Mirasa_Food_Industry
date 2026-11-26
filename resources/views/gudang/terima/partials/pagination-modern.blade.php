@if ($dataList->total() > 0)
    <div class="terima-pagination-container">
        {{-- Sisi Kiri: Rangkuman Data & Per Page Dropdown --}}
        <div class="terima-pagination-info">
            <span>
                Menampilkan <strong>{{ $dataList->firstItem() ?? 0 }}</strong> &ndash; <strong>{{ $dataList->lastItem() ?? 0 }}</strong> dari <strong>{{ number_format($dataList->total(), 0, ',', '.') }}</strong> {{ ($viewType ?? 'header') === 'item' ? 'baris item' : 'dokumen' }}
            </span>

            <div class="terima-per-page-selector">
                <span>Baris:</span>
                <select class="terima-per-page-control" onchange="changeTerimaPerPage(this.value)">
                    @foreach ([10, 15, 20, 25, 50, 100] as $size)
                        <option value="{{ $size }}" {{ ($perPage ?? 20) == $size ? 'selected' : '' }}>
                            {{ $size }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Sisi Kanan: Navigasi Page Number & Quick Jump --}}
        <div class="terima-pagination-nav">
            {{-- Tombol Sebelumnya (Prev) --}}
            @if ($dataList->onFirstPage())
                <span class="terima-page-btn disabled" aria-disabled="true">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>Prev</span>
                </span>
            @else
                <a href="{{ $dataList->appends(request()->query())->previousPageUrl() }}" class="terima-page-btn" rel="prev">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>Prev</span>
                </a>
            @endif

            {{-- Nomor Halaman (Sliding Window) --}}
            <div class="terima-page-numbers">
                @php
                    $currentPage = $dataList->currentPage();
                    $lastPage = $dataList->lastPage();
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
                    <a href="{{ $dataList->appends(request()->query())->url(1) }}" class="terima-page-number">1</a>
                    @if ($start > 2)
                        <span class="terima-page-dots">&hellip;</span>
                    @endif
                @endif

                @for ($page = $start; $page <= $end; $page++)
                    @if ($page == $currentPage)
                        <span class="terima-page-number active">{{ $page }}</span>
                    @else
                        <a href="{{ $dataList->appends(request()->query())->url($page) }}" class="terima-page-number">{{ $page }}</a>
                    @endif
                @endfor

                @if ($end < $lastPage)
                    @if ($end < $lastPage - 1)
                        <span class="terima-page-dots">&hellip;</span>
                    @endif
                    <a href="{{ $dataList->appends(request()->query())->url($lastPage) }}" class="terima-page-number">{{ $lastPage }}</a>
                @endif
            </div>

            {{-- Tombol Berikutnya (Next) --}}
            @if ($dataList->hasMorePages())
                <a href="{{ $dataList->appends(request()->query())->nextPageUrl() }}" class="terima-page-btn" rel="next">
                    <span>Next</span>
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="terima-page-btn disabled" aria-disabled="true">
                    <span>Next</span>
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif

            {{-- Fitur Quick Jump / Lompat Halaman --}}
            @if ($dataList->lastPage() > 1)
                <div class="terima-page-jump">
                    <span class="terima-jump-label">Ke:</span>
                    <input type="number" 
                           id="terimaJumpPageInput" 
                           class="terima-jump-input" 
                           min="1" 
                           max="{{ $dataList->lastPage() }}" 
                           value="{{ $dataList->currentPage() }}" 
                           onchange="jumpToTerimaPage({{ $dataList->lastPage() }})"
                           onkeydown="if(event.key === 'Enter'){ event.preventDefault(); jumpToTerimaPage({{ $dataList->lastPage() }}); }"
                           title="Ketik nomor halaman lalu tekan Enter">
                    <span class="terima-jump-total">/ {{ $dataList->lastPage() }}</span>
                </div>
            @endif
        </div>
    </div>
@endif
