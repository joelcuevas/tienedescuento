<x-slot name="title">
    Descubre los Mejores Descuentos de {{ country() }} en {{ mmyy() }}
</x-slot>

<x-slot name="meta">
    <meta name="description" content="Encuentra los mejores descuentos y ofertas exclusivas. Rastrea precios y ahorra dinero en tus compras diarias de tecnología, línea blanca, moda, hogar y más.">
    <meta name="keywords" content="descuentos, ofertas, rastreador de precios, price tracker, promociones, precios bajos, seguimiento de precios, mejor precio, {{ country() }}, {{ mmyy() }}, {{ $stores->pluck('name')->unique()->join(', ') }}">
</x-slot>

<div class="space-y-12">
    <div class="space-y-12">
        @foreach ($taxonomies as $taxonomy)
            <livewire:web.featured-products :key="$taxonomy" :country="request()->countryCode" :$taxonomy :lazy="$loop->index > 3" />
        @endforeach
    </div>
</div>
