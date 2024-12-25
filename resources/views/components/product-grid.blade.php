<div>
    <div class="space-y-10">
        @foreach ($products->chunk(12) as $group)
            <div class="grid grid-cols-2 gap-x-4 lg:gap-x-8 gap-y-8 lg:gap-y-10 sm:grid-cols-4 lg:grid-cols-6">
                @foreach ($group as $product)
                    <a href="{{ $product->link }}" title="{{ $product->title }}" class="group text-sm w-full">
                        <div class="relative bg-gray-100/80 rounded-xl p-2 object-center w-full aspect-[3/4] group-hover:bg-gray-200/70">
                            <!-- do not delete!!! image-fits: object-cover, object-contain -->
                            <img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="mix-blend-multiply w-full h-full rounded-lg {{ $product->store->getThumbAspect() == 'square' ? 'object-contain rounded-xl' : 'object-cover' }}">
                            
                            @if ($product->discount)
                                <div class="absolute top-2 right-2 inline-flex text-sm rounded-full bg-red-700 text-white px-2 leading-6">-{{ abs($product->discount) }}%</div>
                            @endif

                            @if (Auth::user() && Auth::user()->isTracking($product))
                                <div title="Monitoreando" class="absolute -bottom-4 right-2 inline-flex text-sm rounded-full bg-fuchsia-800 text-white h-8 w-8 items-center justify-center leading-6">
                                    <i class="fa fa-eye"></i>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 text-gray-500 truncate">{{ $product->store->name }}</div>

                        <h3 class="mt-1 font-medium text-gray-900 line-clamp-2 lg:line-clamp-none lg:truncate">{{ $product->title }}</h3>

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
</div>