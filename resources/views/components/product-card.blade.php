<a href="{{ $product->link() }}" title="{{ $product->title }}" class="group text-sm w-full">
    <x-product-thumb :$product :tracker="true" />

    <div class="mt-4 text-gray-500 truncate">{{ $product->store->name }}</div>

    <h3 class="mt-1 font-medium text-gray-900 line-clamp-2">{{ $product->title }}</h3>

    <div class="mt-1 flex space-x-2 items-center">
        @if ($product->discount > 0)
            <div class="text-sm font-medium text-red-700">{{ $product->latest_price_formatted }}</div>
            <div class="text-xs line-through text-gray-500">{{ $product->regular_price_formatted }}</div>
        @elseif ($product->discount < 0)
            <div>{{ $product->latest_price_formatted }}</div>
        @else
            <div>{{ $product->latest_price_formatted }}</div>
        @endif
    </div>
</a>