<div>
    <div class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-5 lg:gap-x-8">
        @foreach ($products as $product)
        <a href="{{ $product->link }}" title="{{ $product->title }}" class="group text-sm">
            <div class="bg-gray-100/80 rounded-xl p-2 object-center w-full group-hover:bg-gray-200/70">
                <img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="mix-blend-multiply aspect-square object-contain">
            </div>
            <h3 class="mt-4 font-medium text-gray-900 truncate">{{ $product->title }}</h3>
            <p class="mt-1 text-gray-500 truncate">Tienda: {{ $product->store->name }}</p>
            <p class="mt-1 font-medium text-gray-900"><x-price :$product /></p>
        </a>
        @endforeach
    </div>
    <div class="mt-12">{{ $products->links() }}</div>
</div>