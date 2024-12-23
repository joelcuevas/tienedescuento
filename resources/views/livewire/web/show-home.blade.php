<div class="space-y-10">
    @foreach ($featured as $category => $products)
        <div>
            <h1 class="text-base font-medium text-gray-900 mb-4">{{ $category }}</h1>
            <x-product-grid :$products />
        </div>
    @endforeach
</div>
