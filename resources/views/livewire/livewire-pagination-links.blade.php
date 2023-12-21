@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    @if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center lg:justify-between">
        <div class="hidden lg:flex text-sm text-gray-700 leading-5 gap-1">
            <span>{!! __('Showing') !!}</span>
            <span class="font-medium">{{ $paginator->firstItem() }}</span>
            <span>{!! __('to') !!}</span>
            <span class="font-medium">{{ $paginator->lastItem() }}</span>
            <span>{!! __('of') !!}</span>
            <span class="font-medium">{{ $paginator->total() }}</span>
            <span>{!! __('results') !!}</span>
        </div>

        <div class="join">
            <button @class(['join-item btn', 'btn-disabled'=> $paginator->onFirstPage()])
                wire:click="previousPage('{{ $paginator->getPageName() }}')">{!! __('pagination.previous') !!}</button>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <button class="join-item btn hidden md:flex">...</button>
                @elseif (is_array($element))
                    @foreach ($element as $page => $url)

                        @if($page == ($paginator->currentPage()-3) && $page != 1 )
                            <button class="join-item btn btn-disabled hidden md:flex px-0">...</button>
                        @elseif($page == ($paginator->currentPage()+3) && $page != $paginator->lastPage())
                            <button class="join-item btn btn-disabled hidden md:flex px-0">...</button>
                        @elseif($page < ($paginator->currentPage()-3) && $page != 1 )
                            {{-- do nothing --}}
                        @elseif($page > ($paginator->currentPage()+3) && $page != $paginator->lastPage() )
                            {{-- do nothing --}}
                        @else
                            <button @class([ 'join-item btn hidden md:flex', 'btn-active'=> $page == $paginator->currentPage() ])
                                wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}"
                                wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')">{{ $page }}</button>
                         @endif

                    @endforeach
                @endif
            @endforeach

            <button @class(['join-item btn', 'btn-disabled'=> !$paginator->hasMorePages() ])
                wire:click="nextPage('{{ $paginator->getPageName() }}')">{!! __('pagination.next') !!}</button>
        </div>
    </nav>
    @endif
</div>