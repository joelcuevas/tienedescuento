<div>
    <div class="grid grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-4 lg:grid-cols-6 lg:gap-x-8">
        @foreach ($products as $product)
        <a href="{{ $product->link }}" title="{{ $product->title }}" class="group text-sm w-full">
            <div class="bg-gray-100/80 rounded-xl p-2 object-center w-full aspect-[3/4] group-hover:bg-gray-200/70">
                <img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="mix-blend-multiply rounded-lg aspect-[3/4] object-cover">
            </div>
            <h3 class="mt-4 font-medium text-gray-900 line-clamp-2 text-balance">{{ $product->title }}</h3>
            <p class="mt-1 text-gray-500 truncate">{{ $product->store->name }} / {{ $product->categories->first()?->title }}</p>
            <p class="mt-1 font-medium text-gray-900"><x-price :$product /></p>
        </a>
        @endforeach
    </div>
    @if ($products instanceof \Illuminate\Pagination\AbstractPaginator)
        <div class="mt-12">{{ $products->links() }}</div>
    @endif
</div>