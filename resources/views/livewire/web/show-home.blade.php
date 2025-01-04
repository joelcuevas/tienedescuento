<x-slot name="title">
    Descubre descuentos y ofertas exclusivas en electrónicos y más
</x-slot>

<x-slot name="meta">
    <meta name="description" content="Encuentra los mejores descuentos y ofertas exclusivas. Rastrea precios y ahorra dinero en tus compras diarias de tecnología, línea blanca, moda, hogar y más.">
    <meta name="keywords" content="descuentos, ofertas, rastreador de precios, price tracker, promociones, precios bajos, seguimiento de precios, mejor precio, {{ $stores->pluck('name')->unique()->join(', ') }}">
</x-slot>

<div class="space-y-10">
    <h1 class="font-medium text-gray-900">Los Mejores Descuentos de {{ mmyy() }}</h1>
    <ul class="flex space-x-6">
        @foreach ($stores as $store)
            <li>
                <a href="{{ $store->link() }}" alt="{{ $store->name }}">
                    <img class="h-20 rounded-full" src="{{ $store->image_url }}">
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
        @foreach ($featured as $category => $data)
            <div>
                <div class="flex items-center mb-4 space-x-5">
                    <h2 class="font-medium text-gray-900">
                        Hasta {{ $data['products']->first()->discount }}% de Descuento en {{ $data['title'] }}
                    </h2>

                    <x-link 
                        class="block text-sm" 
                        href="{{ route('catalogs.store', $category) }}"
                        alt="Ver más descuentos en {{ $category }}"
                    >
                        <span>Ver más</span>
                        <i class="fa fa-arrow-right text-xs"></i>
                    </x-link>
                </div>

                <x-product-grid :products="$data['products']" />
            </div>
        @endforeach
    </div>
</div>
