<x-slot name="title">
    {{ $product->title }}
    {{ $product->discount > 0 ? "con {$product->discount}% de descuento" : 'descubre su descuento' }}
    en {{ mmyy() }}
</x-slot>

<div>
    <div class="lg:grid lg:grid-cols-12 lg:auto-rows-min lg:gap-x-12"> 
        <div class="lg:col-span-8 lg:row-span-1"> 
            <div class="flex space-x-6">
                <div class="w-32 min-w-32 lg:w-40 lg:min-w-40">
                    <x-product-thumb :$product />
                </div>

                <div>
                    @if ($product->isOutdated())
                        <div class="rounded-xl bg-yellow-100 px-4 py-4 mb-6 text-xs text-gray-800">
                            Han pasado algunos días desde el último precio vigente de este producto. Es posible que el artículo se encuentre agotado 
                            o descontinuado. Verifica directo en la tienda para más información.
                        </div>
                    @endif

                    <div class="text-sm flex w-full truncate mt-6 lg:mt-3">
                        <a href="{{ $product->storeLink() }}" class="max-w-[50%] truncate text-gray-600 hover:text-gray-900">{{ $product->store->name }}</a>
                        <span class="text-gray-300 px-2">/</span>
                        <a href="{{ $product->categoryLink() }}" class="max-w-[50%] truncate text-gray-600 hover:text-gray-900">{{ $product->category }}</a>
                        @if ($product->brand)
                            <span class="text-gray-300 px-2">/</span>
                            <a href="{{ $product->categoryBrandLink() }}" class="max-w-[50%] truncate text-gray-600 hover:text-gray-900">{{ $product->brand }}</a>
                        @endif
                    </div>

                    <h1 class="font-medium text-lg my-2">{{ $product->title }}</h1>

                    <div class="text-xs text-gray-600 mb-3">
                        <x-link href="{{ $product->external_url }}" target="_blank" class="text-gray-600">
                            SKU: {{ $product->sku }} -  Comprarlo en {{ $product->store->name }}
                            <i class="fa-solid fa-arrow-up-right-from-square text-[0.5rem] text-gray-500 pl-1"></i>
                        </x-link>
                    </div>

                    <div class="flex items-center space-x-4">
                        @if ($product->discount > 0)
                            <div class="text-lg font-medium text-red-700">{{ $product->latest_price_formatted }}</div>
                            <div class="text-sm line-through text-gray-500">{{ $product->regular_price_formatted }}</div>
                        @elseif ($product->discount < 0)
                            <div>{{ $product->latest_price_formatted }}</div>
                            <div class="text-sm text-gray-500">({{ abs($product->discount) }}% más caro)</div>
                        @else
                            <div>{{ $product->latest_price_formatted }}</div>
                            <div class="text-sm text-gray-500">(precio regular)</div>
                        @endif
                        <div class="text-xs text-gray-500">Actualizado {{ $product->priced_at->diffForHumans() }}</div>
                    </div>

                    <div class="mt-4 flex flex-col lg:flex-row items-start lg:items-center lg:space-x-2 space-y-2 lg:space-y-0">
                        @livewire('web.track-product', [$product])
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-8 row-start-2">
            <h2 class="text-sm font-medium mt-10 mb-3">Rastreo de precios {{ $yearsSpan }}</h2>
            <canvas id="prices-chart" height="60"></canvas>

            <h2 class="text-sm font-medium mt-10 mb-3">¿Tiene descuento "{{ $product->title }}"?</h2>
            @if ($product->hasBestPrice())
                <p class="text-sm text-gray-600">
                    <span class="font-medium">¡Este es el mejor precio de los últimos {{ $lastDays }} días!</span> 
                    Su precio normal suele estar entre 
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

            <h2 class="text-sm font-medium mt-10 mb-3">¿Cómo se calcula el descuento real en Tiene Descuento?</h2>
            <p class="text-sm text-gray-600">
                Algunas marcas cometen la mala práctica de subir sus precios antes de una venta especial. El algoritmo de Tiene Descuento
                es capaz de identificar el rango de precios en el que suele encontrarse el producto y eliminar esos "falsos descuentos"
                que las tiendas te quieren hacer creer. El descuento real indicado aquí, considera factores estadísticos para decirte la verdad.
            </p>
        </div>

        <div class="mt-10 lg:mt-0 lg:col-span-4 lg:row-span-4">
            @if ($related) 
                <h2 class="text-sm text-gray-600 mb-1">Productos Similares</h2>
                <div class="text-base font-medium mb-4">¡Compara precios antes de comprar!</div>
                <div class="grid grid-cols-3 gap-x-4 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $relatedProduct)
                        <x-product-card :product="$relatedProduct" textSize="xs" />
                    @endforeach
                </div>
            @endif
        </div>
    </div>  
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@3.1.0/dist/chartjs-plugin-annotation.min.js"></script>
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
                            backgroundColor: '#444',
                            borderColor: '#444',
                            data: @json($data->map(fn ($data) => $data->aggregate)),
                            stepped: 'middle',
                            borderWidth: 1.5,
                            pointRadius: 2,
                            pointHoverRadius: 4,
                            pointHitRadius: 5,
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
                            ticks: {
                                maxTicksLimit: 12,
                                font: {
                                    size: 8,
                                    family: 'figtree',
                                },
                            },
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
