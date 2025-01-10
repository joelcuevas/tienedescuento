<div>
    <div class="space-y-12">
        @foreach ($products->chunk(14) as $group)
            <div class="grid grid-cols-2 gap-x-4 lg:gap-x-8 gap-y-8 lg:gap-y-12 sm:grid-cols-4 lg:grid-cols-6">
                @foreach ($group as $product)
                    <x-product-card :$product />
                @endforeach
            </div>
        @endforeach
    </div>
    
    @if ($products instanceof \Illuminate\Pagination\AbstractPaginator)
        <div class="mt-12">{{ $products->links() }}</div>
    @endif
</div>