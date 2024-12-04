<div>
    <div class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-5 lg:gap-x-8">
        @foreach ($products as $product)
        <a href="{{ $product->link }}" title="{{ $product->title }}" class="group text-sm">
            <img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="aspect-square object-cover object-center w-full rounded-xl border border-gray-200 p-2 bg-white group-hover:opacity-75">
            <h3 class="mt-4 font-medium text-gray-900 truncate">{{ $product->title }}</h3>
            <p class="mt-1 text-gray-500 truncate">{{ $product->store->name }} / {{ $product->categories[0]->title }}</p>
            <p class="mt-1 font-medium text-gray-900"><x-price :$product /></p>
        </a>
        @endforeach
    </div>
    <div class="mt-12">{{ $products->links() }}</div>
</div>
