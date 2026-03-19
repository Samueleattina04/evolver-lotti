@if ($paginator->hasPages())
<div class="pagination">
    {{-- Precedente --}}
    @if ($paginator->onFirstPage())
        <span class="page-btn disabled">←</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="page-btn">←</a>
    @endif

    {{-- Prima pagina --}}
    @if ($paginator->currentPage() > 3)
        <a href="{{ $paginator->url(1) }}" class="page-btn">1</a>
        @if ($paginator->currentPage() > 4)
            <span class="page-btn disabled" style="border:none;background:transparent">…</span>
        @endif
    @endif

    {{-- Pagine intorno alla corrente --}}
    @for ($i = max(1, $paginator->currentPage() - 2); $i <= min($paginator->lastPage(), $paginator->currentPage() + 2); $i++)
        @if ($i === $paginator->currentPage())
            <span class="page-btn active">{{ $i }}</span>
        @else
            <a href="{{ $paginator->url($i) }}" class="page-btn">{{ $i }}</a>
        @endif
    @endfor

    {{-- Ultima pagina --}}
    @if ($paginator->currentPage() < $paginator->lastPage() - 2)
        @if ($paginator->currentPage() < $paginator->lastPage() - 3)
            <span class="page-btn disabled" style="border:none;background:transparent">…</span>
        @endif
        <a href="{{ $paginator->url($paginator->lastPage()) }}" class="page-btn">{{ $paginator->lastPage() }}</a>
    @endif

    {{-- Successivo --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="page-btn">→</a>
    @else
        <span class="page-btn disabled">→</span>
    @endif

    <span class="page-info">
        {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} di {{ number_format($paginator->total()) }}
    </span>
</div>
@endif
