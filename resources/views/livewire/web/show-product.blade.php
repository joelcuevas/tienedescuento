<x-slot name="title">
    {{ $product->title }}
    {{ $product->discount > 0 ? "con {$product->discount}% de descuento" : 'descubre su descuento' }}
    en {{ now()->translatedFormat('F Y') }}
</x-slot>

<div>
    <div class="lg:grid lg:grid-cols-12 lg:grid-rows-[auto_auto] lg:gap-x-8"> 
        <div class="lg:col-span-2 lg:row-span-2">
            <div class="w-full bg-gray-100/90 rounded-xl p-3 object-center aspect-[3/2] lg:aspect-[3/4] text-center flex justify-center">
                <img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="mix-blend-multiply h-full object-cover rounded-lg">
            </div>
        </div>

        <div class="lg:col-span-6">  
            @if ($product->isOutdated())
                <div class="rounded-xl bg-yellow-100 px-4 py-4 mb-6 text-xs text-gray-800">
                    Han pasado algunos días desde el último precio vigente de este producto. Es posible que el artículo se encuentre agotado 
                    o descontinuado. Verifica directo en la tienda para más información.
                </div>
            @endif

            <div class="text-sm flex w-full truncate mt-6 lg:mt-3">
                <a href="{{ $product->store_link }}" class="max-w-[50%] truncate text-gray-600 hover:text-gray-900">{{ $product->store->name }}</a>
                <span class="text-gray-300 px-2">/</span>
                <a href="{{ $product->category_link }}" class="max-w-[50%] truncate text-gray-600 hover:text-gray-900">{{ $product->category }}</a>
                @if ($product->brand)
                    <span class="text-gray-300 px-2">/</span>
                    <a href="{{ $product->category_brand_link }}" class="max-w-[50%] truncate text-gray-600 hover:text-gray-900">{{ $product->brand }}</a>
                @endif
            </div>

            <h1 class="font-medium text-lg text-balance my-2">{{ $product->title }}</h1>

            <div class="text-xs text-gray-600 mb-3">SKU: {{ $product->sku }}</div>

            <div class="flex items-center space-x-2 mb-10">
                @if ($product->discount > 0)
                    <div class="text-sm line-through text-gray-400">{{ $product->regular_price_formatted }}</div>
                    <div class="text-lg font-medium text-red-700">{{ $product->latest_price_formatted }}</div>
                    <div class="text-sm rounded-full bg-red-700 text-white px-2 leading-6">-{{ abs($product->discount) }}% real</div>
                @elseif ($product->discount < 0)
                    <div>{{ $product->latest_price_formatted }}</div>
                    <div class="text-sm text-gray-500">({{ abs($product->discount) }}% más caro)</div>
                @else
                    <div>{{ $product->latest_price_formatted }}</div>
                    <div class="text-sm text-gray-500">(precio regular)</div>
                @endif
            </div>

            <div class="flex flex-col lg:flex-row items-start lg:items-center lg:space-x-2 space-y-2 lg:space-y-0">
                @livewire('web.track-product', [$product])

                <x-link-button href="{{ $product->url }}" target="_blank" class="px-8 py-2">
                    Comprarlo en {{ $product->store->name }}
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs text-gray-600 pl-1"></i>
                </x-link-button>
            </div>
        </div>
      
        <div class="lg:col-span-4">
            @if (config('ads.enabled'))
                <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-9285674270424452" crossorigin="anonymous"></script>
                <ins class="adsbygoogle"
                    style="display:block"
                    data-ad-format="autorelaxed"
                    data-ad-client="ca-pub-9285674270424452"
                    data-ad-slot="3697451903">
                </ins>
                <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
                </script>
            @endif
        </div>

        <div class="lg:col-span-6 lg:col-start-3 row-start-2">
            <h2 class="text-sm font-medium mt-10 mb-3">¿Tiene descuento "{{ $product->title }}" en {{ now()->translatedFormat('F Y') }}?</h2>
            @if ($product->hasBestPrice())
                <p class="text-sm text-gray-600">
                    <span class="font-medium">¡Este es el mejor precio de los últimos {{ $lastMonths }} meses!</span> 
                    El precio normal de "{{ $product->title }}" suele estar entre 
                    <span class="font-medium">{{ $product->regular_price_lower_formatted }}</span> 
                    y <span class="font-medium">{{ $product->regular_price_upper_formatted }}</span>, 
                    por lo que <span class="font-medium">{{ $product->latest_price_formatted }} 
                    en {{ $product->priced_at->translatedFormat('F Y') }}</span> 
                    es la mejor oferta que vas a encontrar. ¡Aprovéchala antes de que termine!
                </p>
            @elseif ($product->hasDiscount())
                <p class="text-sm text-gray-600">
                    <span class="font-medium">¡Sí, tiene {{ abs($product->discount) }}% de descuento real!</span>
                    El precio normal de "{{ $product->title }}" suele estar entre 
                    <span class="font-medium">{{ $product->regular_price_lower_formatted }}</span> 
                    y <span class="font-medium">{{ $product->regular_price_upper_formatted }}</span>, 
                    por lo que <span class="font-medium">{{ $product->latest_price_formatted }} 
                    en {{ $product->priced_at->translatedFormat('F Y') }}</span> 
                    es una excelente oferta. ¡Aprovéchala antes de que termine!
            @else
                <p class="text-sm text-gray-600">
                    No, el precio regular de "{{ $product->title }}" es de {{ $product->regular_price_formatted }} que es {{ abs($product->discount) }}% más barato, 
                    por lo que podría no ser un buen momento para comprarlo. <span class="font-medium">¿Quieres que te avise cuando tenga descuento?</span>.
                </p>
            @endif

            <h2 class="text-sm font-medium mt-10 mb-3">Historial de precios {{ $yearsSpan }} de "{{ $product->title }}"</h2>
            <canvas id="prices-chart" height="60"></canvas>

            <h2 class="text-sm font-medium mt-10 mb-3">¿Cómo se calcula el descuento real en Tiene Descuento?</h2>
            <p class="text-sm text-gray-600">
                Algunas marcas cometen la mala práctica de subir sus precios antes de una venta especial. El algoritmo de Tiene Descuento
                es capaz de identificar el rango de precios en el que suele encontrarse el producto y eliminar esos "falsos descuentos"
                que las tiendas te quieren hacer creer. El descuento real indicado aquí, considera factores estadísticos para decirte la verdad.
            </p>
        </div>
    </div>  
</div>

@push('scripts')
    <script>
        const upperBound = <?php echo json_encode($product->regular_price_upper); ?>;
        const lowerBound = <?php echo json_encode($product->regular_price_lower); ?>;

        new Chart(
            document.getElementById('prices-chart'),
            {
                type: 'line',
                data: {
                    labels: @json($data->map(fn ($data) => $data->date)),
                    datasets: [
                        {
                            label: 'Precio',
                            backgroundColor: '#333',
                            borderColor: '#333',
                            borderWidth: 2,
                            data: @json($data->map(fn ($data) => $data->aggregate)),
                            stepped: 'middle',
                        },
                    ],
                },
                options: {
                    animation: false,
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            titleFont: { size: 12, weight: 'bold' },
                            bodyFont: { size: 12 },
                            padding: 10,
                            cornerRadius: 6,
                            bodySpacing: 1,
                            titleFont: {
                                size: 12,
                                family: 'figtree',
                            },
                            bodyFont: {
                                size: 12,
                                family: 'figtree',
                            },
                            callbacks: {
                                label: function(context) {
                                    return new Intl.NumberFormat('en-US', {
                                        style: 'currency',
                                        currency: 'USD',
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2,
                                    }).format(context.raw);
                                }
                            }
                        },
                        annotation: {
                            annotations: {

                                regularBand: {
                                    type: 'box',
                                    xMin: 0,
                                    xMax: @json($data->count() - 1),
                                    yMin: lowerBound,
                                    yMax: upperBound,
                                    backgroundColor: 'rgba(0, 128, 0, 0.1)',
                                    borderWidth: 0,
                                    drawTime: 'beforeDatasetsDraw',
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            ticks: false,
                        },
                        y: {
                            ticks: {
                                maxTicksLimit: 6,
                                font: {
                                    size: 9,
                                    family: 'figtree',
                                },
                                callback: function (value) {
                                    return new Intl.NumberFormat('en-US', {
                                        style: 'currency',
                                        currency: 'USD',
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 0,
                                    }).format(value);
                                },
                            },
                        },
                    },
                },
            },
        );
    </script>
@endpush
