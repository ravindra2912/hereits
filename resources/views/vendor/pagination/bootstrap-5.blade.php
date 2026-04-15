@if ($paginator->hasPages())
<nav>
    <div class="d-flex flex-fill align-items-center justify-content-center justify-content-sm-between">
        <div class="d-none d-sm-block">
            <p class="text-secondary mb-0 d-none">
                {!! __('Showing') !!}
                <span class="fw-bold text-dark">{{ $paginator->firstItem() }}</span>
                {!! __('to') !!}
                <span class="fw-bold text-dark">{{ $paginator->lastItem() }}</span>
                {!! __('of') !!}
                <span class="fw-bold text-dark">{{ $paginator->total() }}</span>
                {!! __('results') !!}
            </p>
        </div>

        <div>
            <ul class="pagination pagination-sm gap-1 mb-0 border-0 flex-wrap justify-content-center">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link rounded-circle border-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fas fa-chevron-left small"></i>
                    </span>
                </li>
                @else
                <li class="page-item">
                    <a class="page-link rounded-circle border-0 d-flex align-items-center justify-content-center bg-white shadow-sm text-dark hover-primary" href="{{ $paginator->previousPageUrl() }}" rel="prev" style="width: 32px; height: 32px;">
                        <i class="fas fa-chevron-left small"></i>
                    </a>
                </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                <li class="page-item disabled"><span class="page-link border-0 text-muted">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                <li class="page-item active">
                    <span class="page-link rounded-circle border-0 d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 32px; height: 32px;">{{ $page }}</span>
                </li>
                @else
                <li class="page-item">
                    <a class="page-link rounded-circle border-0 d-flex align-items-center justify-content-center bg-white shadow-sm text-dark hover-primary" href="{{ $url }}" style="width: 32px; height: 32px;">{{ $page }}</a>
                </li>
                @endif
                @endforeach
                @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link rounded-circle border-0 d-flex align-items-center justify-content-center bg-white shadow-sm text-dark hover-primary" href="{{ $paginator->nextPageUrl() }}" rel="next" style="width: 32px; height: 32px;">
                        <i class="fas fa-chevron-right small"></i>
                    </a>
                </li>
                @else
                <li class="page-item disabled">
                    <span class="page-link rounded-circle border-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fas fa-chevron-right small"></i>
                    </span>
                </li>
                @endif
            </ul>
        </div>
    </div>
</nav>
@endif