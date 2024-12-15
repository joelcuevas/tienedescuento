@if ($paginator->hasPages())
    <nav class="flex items-center justify-between border-t border-gray-100 px-4 sm:px-0">
        <div class="-mt-px flex w-0 flex-1">
            @if ($paginator->onFirstPage())

            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center border-t-2 border-transparent pr-1 pt-4 text-sm text-gray-500 hover:text-gray-800">
                    <i class="fa-solid fa-arrow-left pr-2"></i>
                    @lang('Previous')
                </a>
            @endif
        </div>
        <div class="hidden md:-mt-px md:flex">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex items-center border-t-2 border-transparent px-4 pt-4 text-sm font-medium text-gray-500">...</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span href="{{ $url }}" class="inline-flex items-center border-t-2 border-transparent px-4 pt-4 text-sm font-medium border-indigo-500 text-indigo-600">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex items-center border-t-2 border-transparent px-4 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-800">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>
        <div class="-mt-px flex w-0 flex-1 justify-end">
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center border-t-2 border-transparent pl-1 pt-4 text-sm text-gray-500 hover:text-gray-800">
                    @lang('Next')
                    <i class="fa-solid fa-arrow-right pl-2"></i>
                </a>
            @else 

            @endif
        </div>
    </nav>
@endif
