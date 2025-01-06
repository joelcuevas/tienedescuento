<div>
    @if ($products->count())
        <div class="flex items-center mb-4 space-x-5">
            <h2 class="font-medium text-gray-900">
                Hasta {{ $products->first()->discount }}% de Descuento en {{ $title }}
            </h2>

            <x-link 
                class="block text-sm" 
                href="{{ route('catalogs.store', $taxonomy) }}"
                alt="Ver más descuentos en {{ $taxonomy }}"
            >
                <span>Ver más</span>
                <i class="fa fa-arrow-right text-xs"></i>
            </x-link>
        </div>

        <x-product-grid :$products />
    @endif
</div>