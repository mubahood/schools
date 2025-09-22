@if ($paginator->hasPages())
<nav class="kb-pagination" role="navigation" aria-label="Pagination Navigation">
    <div class="pagination-info">
        <span class="pagination-text">
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
        </span>
    </div>
    
    <ul class="pagination-list">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="pagination-item disabled">
                <span class="pagination-link">
                    <i class="fas fa-chevron-left"></i>
                    Previous
                </span>
            </li>
        @else
            <li class="pagination-item">
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-link" rel="prev">
                    <i class="fas fa-chevron-left"></i>
                    Previous
                </a>
            </li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="pagination-item disabled">
                    <span class="pagination-link dots">{{ $element }}</span>
                </li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="pagination-item active">
                            <span class="pagination-link current">{{ $page }}</span>
                        </li>
                    @else
                        <li class="pagination-item">
                            <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="pagination-item">
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-link" rel="next">
                    Next
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        @else
            <li class="pagination-item disabled">
                <span class="pagination-link">
                    Next
                    <i class="fas fa-chevron-right"></i>
                </span>
            </li>
        @endif
    </ul>
</nav>

<style>
.kb-pagination {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    margin: 2rem 0;
}

.pagination-info {
    color: var(--text-muted);
    font-size: 0.9rem;
}

.pagination-list {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    list-style: none;
    padding: 0;
    margin: 0;
}

.pagination-item {
    display: flex;
    align-items: center;
}

.pagination-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    color: var(--text-muted);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    min-width: 44px;
    justify-content: center;
}

.pagination-link:hover {
    border-color: var(--primary-color);
    background: var(--primary-light);
    color: var(--primary-color);
    text-decoration: none;
}

.pagination-item.active .pagination-link {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}

.pagination-item.disabled .pagination-link {
    background: #f8fafc;
    border-color: #e2e8f0;
    color: #cbd5e1;
    cursor: not-allowed;
}

.pagination-item.disabled .pagination-link:hover {
    background: #f8fafc;
    border-color: #e2e8f0;
    color: #cbd5e1;
}

.pagination-link.dots {
    border: none;
    background: none;
    color: var(--text-muted);
}

.pagination-link.current {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .pagination-list {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .pagination-link {
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
    }
    
    .pagination-info {
        text-align: center;
        font-size: 0.8rem;
    }
}
</style>
@endif