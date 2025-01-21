<div>
    @if (isset($store) && $store)
        <div class="flex items-center mb-10 space-x-6">
            <div>
                <a href="{{ $store->link() }}">
                    <img src="{{ $store->image_url }}" class="aspect-square object-cover h-16 rounded-full">
                </a>
            </div>
            <div>
                <h1 class="{{ $title ? 'text-2xl' : 'text-3xl' }} text-gray-900 font-medium truncate">
                    <a class="text-gray-900" href="{{ $store->link() }}">{{ $store->name }}</a>
                </h1>
                @if ($title)
                    <h2 class="text-gray-500 text-sm truncate">{{ $title }}</h2>
                @endif
            </div>
        </div>

        <x-taxonomy-tree :$store />
    @elseif ($title)
        <h1 class="text-xl text-gray-900 font-medium mb-10">@lang('Results for'): "{{ $title }}"</h1>
    @endif
    
    <x-product-grid :$products />
</div>
