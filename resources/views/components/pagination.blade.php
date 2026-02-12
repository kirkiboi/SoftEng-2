@if ($paginator->hasPages())
    <div class="custom-pagination">
        <span class="pagination-info">
            Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </span>
        <nav class="pagination-nav">
            {{-- First Page --}}
            @if ($paginator->currentPage() > 1)
                <a href="{{ $paginator->url(1) }}" class="page-btn page-first" title="First page">
                    <i class="fa-solid fa-angles-left"></i>
                </a>
            @else
                <span class="page-btn page-first disabled">
                    <i class="fa-solid fa-angles-left"></i>
                </span>
            @endif

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="page-btn page-prev disabled">
                    <i class="fa-solid fa-chevron-left"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="page-btn page-prev" title="Previous">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            @endif

            {{-- Page Numbers (max 5 visible) --}}
            @php
                $current = $paginator->currentPage();
                $last = $paginator->lastPage();
                $start = max(1, $current - 2);
                $end = min($last, $start + 4);
                // Adjust start if we're near the end
                if ($end - $start < 4) {
                    $start = max(1, $end - 4);
                }
            @endphp

            @for ($i = $start; $i <= $end; $i++)
                @if ($i == $current)
                    <span class="page-btn page-num active">{{ $i }}</span>
                @else
                    <a href="{{ $paginator->url($i) }}" class="page-btn page-num">{{ $i }}</a>
                @endif
            @endfor

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="page-btn page-next" title="Next">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            @else
                <span class="page-btn page-next disabled">
                    <i class="fa-solid fa-chevron-right"></i>
                </span>
            @endif

            {{-- Last Page --}}
            @if ($paginator->currentPage() < $last)
                <a href="{{ $paginator->url($last) }}" class="page-btn page-last" title="Last page">
                    <i class="fa-solid fa-angles-right"></i>
                </a>
            @else
                <span class="page-btn page-last disabled">
                    <i class="fa-solid fa-angles-right"></i>
                </span>
            @endif
        </nav>
    </div>
@endif
