<div>
    @if ($products->count())
        <div class="sm:flex sm:items-center sm:space-x-5 mb-4">
            <h2 class="font-medium text-gray-900">
                {{ __('Up to :discount% discount in :category', ['discount' => $products->first()->discount, 'category' => $title]) }}
            </h2>

            <x-link 
                class="block text-sm" 
                href="{{ route('catalogs.store', $taxonomy) }}"
                alt="{{ __('See more discounts in :category', ['category' => $taxonomy]) }}"
            >
                <span>{{ __('See more') }}</span>
                <i class="fa fa-arrow-right text-xs"></i>
            </x-link>
        </div>

        <x-product-grid :$products />
    @endif
</div>