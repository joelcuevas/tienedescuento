<x-slot name="title">
    Descubre los Mejores Descuentos de {{ country() }} en {{ mmyy() }}
</x-slot>

<x-slot name="meta">
    <meta name="description" content="Encuentra los mejores descuentos y ofertas exclusivas. Rastrea precios y ahorra dinero en tus compras diarias de tecnología, línea blanca, moda, hogar y más.">
    <meta name="keywords" content="descuentos, ofertas, rastreador de precios, price tracker, promociones, precios bajos, seguimiento de precios, mejor precio, {{ country() }}, {{ mmyy() }}, {{ $stores->pluck('name')->unique()->join(', ') }}">
</x-slot>

<div class="space-y-12">
    <div class="mb-12">
        <div class="grid grid-cols-4 gap-x-4 lg:gap-x-8 gap-y-4 lg:gap-y-12 sm:grid-cols-6 lg:grid-cols-10">
            @foreach ($stores as $store)
                <a href="{{ $store->link() }}" alt="{{ $store->name }}" class="w-20 sm:w-24 aspect-square flex flex-col justify-center items-center space-y-2">
                    <img class="rounded-full p-0.5 border border-gray-200 hover:border-gray-400/50" src="{{ $store->image_url }}" alt="{{ __('See more discounts in :category', ['category' => $store->name]) }}">
                    <div class="text-xs text-gray-600">{{ $store->name }}</div>
                </a>
            @endforeach
        </div>
    </div>

    <div class="space-y-12">
        @foreach ($taxonomies as $taxonomy)
            <livewire:web.featured-products :key="$taxonomy" :country="request()->countryCode" :$taxonomy :lazy="$loop->index > 3" />
        @endforeach
    </div>
</div>
