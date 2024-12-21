<div>
    <div class="space-y-10">
        @foreach ($products->chunk(12) as $group)
            <div class="grid grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-4 lg:grid-cols-6 lg:gap-x-8">
                @foreach ($group as $product)
                    <a href="{{ $product->link }}" title="{{ $product->title }}" class="group text-sm w-full">
                        <div class="bg-gray-100/80 rounded-xl p-2 object-center w-full aspect-[3/4] group-hover:bg-gray-200/70">
                            <img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="mix-blend-multiply w-full h-full rounded-lg object-cover">
                        </div>
                        <div class="mt-4 text-gray-500 truncate">{{ $product->store->name }}</div>
                        <h3 class="mt-1 font-medium text-gray-900 truncate">{{ $product->title }}</h3>
                        <div class="mt-1 flex space-x-2 items-center">
                            @if ($product->discount > 0)
                                <div class="text-sm font-medium text-red-700">{{ $product->latest_price_formatted }}</div>
                                <div class="text-xs line-through text-gray-400">{{ $product->regular_price_formatted }}</div>
                            @elseif ($product->discount < 0)
                                <div>{{ $product->latest_price_formatted }}</div>
                            @else
                                <div>{{ $product->latest_price_formatted }}</div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endforeach
    </div>
    
    @if ($products instanceof \Illuminate\Pagination\AbstractPaginator)
        <div class="mt-12">{{ $products->links() }}</div>
    @endif

    @if (config('ads.enabled'))
        <ins class="adsbygoogle"
            style="display:block"
            data-ad-client="ca-pub-9285674270424452"
            data-ad-slot="3526061510"
            data-ad-format="auto"
            data-full-width-responsive="true">
        </ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    @endif
</div>