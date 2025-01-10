<x-slot name="title">
    Descubre los Mejores Descuentos de {{ country() }} en {{ mmyy() }}
</x-slot>

<x-slot name="meta">
    <meta name="description" content="Encuentra los mejores descuentos y ofertas exclusivas. Rastrea precios y ahorra dinero en tus compras diarias de tecnología, línea blanca, moda, hogar y más.">
    <meta name="keywords" content="descuentos, ofertas, rastreador de precios, price tracker, promociones, precios bajos, seguimiento de precios, mejor precio, {{ country() }}, {{ mmyy() }}, {{ $stores->pluck('name')->unique()->join(', ') }}">
</x-slot>

<div class="space-y-10">
    <h1 class="font-medium text-gray-900">Los mejores descuentos de {{ country() }} en {{ mmyy() }}</h1>

    <ul class="flex space-x-6">
        @foreach ($stores as $store)
            <li>
                <a href="{{ $store->link() }}" alt="{{ $store->name }}" class="flex flex-col justify-center items-center space-y-2">
                    <img class="h-24 w-24 rounded-full p-0.5 border border-gray-200 hover:border-gray-400/50" src="{{ $store->image_url }}" alt="Ver descuentos de {{ $store->name }}">
                    <div class="text-xs text-gray-600">{{ $store->name }}</div>
                </a>
            </li>
        @endforeach
    </ul>

    @if (config('ads.enabled'))
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-9285674270424452" crossorigin="anonymous"></script>
        <ins class="adsbygoogle"
            style="display:block"
            data-ad-client="ca-pub-9285674270424452"
            data-ad-slot="3526061510"
            data-ad-format="auto"
            data-full-width-responsive="true">
        </ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    @endif

    <div class="space-y-12">
        @foreach ($taxonomies as $taxonomy)
            <livewire:web.featured-products :key="$taxonomy" :country="request()->countryCode" :$taxonomy :lazy="$loop->index > 3" />
        @endforeach
    </div>
</div>
