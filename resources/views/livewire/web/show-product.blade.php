<div>
    <div class="lg:grid lg:grid-cols-2 lg:gap-x-8"> 
        <div>   
            <div class="flex items-start justify-between space-x-5">
                <div>
                    <div class="text-sm sm:flex sm:items-center truncate w-full">
                        <a href="{{ $product->store_link }}" class="block sm:inline  text-gray-600 hover:text-gray-900">{{ $product->store->name }}</a>
                        <span class="text-gray-300 px-2">/</span>
                        <a href="{{ $product->category_link }}" class="block sm:inline  text-gray-600 hover:text-gray-900">{{ $product->category }}</a>
                        <span class="text-gray-300 px-2">/</span>
                        <a href="{{ $product->category_brand_link }}" class="block sm:inline  text-gray-600 hover:text-gray-900">{{ $product->brand }}</a>
                    </div>
                    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-balance text-gray-900 sm:text-4xl">{{ $product->title }}</h1>
                    <div class="mt-2 text-gray-600 text-sm">SKU: {{ $product->sku }}</div>
                    <div class="text-gray-900 mt-6">
                        @if (auth()->user())
                            @if ($product->discount > 0)
                                <div class="flex items-center space-x-4">
                                    <div class="font-medium text-3xl text-green-800 rounded-2xl">{{ $product->latest_price_formatted }}</div>
                                    <div class="font-medium text-sm line-through text-gray-500">Precio regular: {{ $product->regular_price_formatted }}</div>
                                </div>
                                <div class="mt-1 text-base text-green-800 font-medium">
                                    <i class="fa-solid pr-1 fa-fire"></i> 
                                    ¡{{ abs($product->discount) }}% de descuento contra su precio regular!
                                </div>
                            @elseif ($product->discount < 0)
                                <div class="flex items-center space-x-3">
                                    <span class="font-medium text-3xl text-red-800">{{ $product->latest_price_formatted }}</span>
                                    <span class="font-medium text-sm line-through text-gray-500">Precio regular: {{ $product->regular_price_formatted }}</span>
                                </div>
                                <div class="mt-1 text-base text-red-800 font-medium">
                                    <i class="fa-regular pr-1 fa-thumbs-down"></i> 
                                    {{ abs($product->discount) }}% superior a su precio regular
                                </div>
                            @else
                                <div class="font-medium text-3xl">{{ $product->regular_price_formatted }}</div>
                                <div class="mt-1 text-base text-gray-500 font-medium">
                                    <i class="fa-regular pr-1 fa-thumbs-up"></i> 
                                    Dentro de su precio regular
                                </div>
                            @endif
                        @else
                            <div class="flex items-center space-x-4">
                                <div class="font-medium text-3xl">{{ $product->latest_price_formatted }}</div>
                            </div>
                            <a href="/login" x-on:click.prevent="$dispatch('show-login-modal')" class="mt-1 text-base text-gray-500 font-medium hover:text-fuchsia-800">
                                <i class="fa-solid pr-1 fa-lock"></i> 
                                Desbloquear el precio más bajo
                            </a>
                        @endif
                    </div>
                </div>

                <div class="min-w-24 sm:min-w-32">
                    <div class="w-24 sm:w-32 bg-gray-100/90 rounded-xl p-1 object-center aspect-[3/4] text-center">
                        <img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="mix-blend-multiply w-full h-full object-cover rounded-lg">
                    </div>
                </div>
            </div>

            @if (!auth()->user())
                <a href="/login" 
                    x-on:click.prevent="$dispatch('show-login-modal')"
                    class="flex items-center justify-between space-x-4 mt-7 bg-gradient-to-r from-fuchsia-800 to-violet-700 hover:from-fuchsia-900 hover:to-violet-800 text-white rounded-xl p-5 text-sm"
                >
                    <div class="flex items-center space-x-4">
                        <div class="text-4xl text-white flex items-center justify-center">
                            <i class="fa-solid fa-fire"></i>
                        </div>
                        <div>
                            <div class="font-medium text-base">¡Descubre si este es el precio más bajo!</div>
                            <div class="text-white opacity-80">Inicia sesión para ver hasta 12 meses de precios de este producto.</div>
                        </div>
                    </div>
                    <div class="bg-white rounded-full text-xl min-w-9 min-h-9 text-violet-800 flex items-center justify-center">
                        <i class="fa fa-arrow-right"></i>
                    </div>
                </a>
            @endif
        </div>
      
        <div class="lg:col-start-2 lg:row-span-2 lg:mt-0 lg:self-center">
            @if (config('ads.enabled'))
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
       
        <div class="mt-7 lg:col-start-1 lg:row-start-2 lg:self-start">
            @if (auth()->user())
                <canvas id="prices-chart" height="80"></canvas>
            @else
                <a href="/login" class="relative cursor-pointer group" x-on:click.prevent="$dispatch('show-login-modal')">
                    <img src="/images/chart-skeleton.png" class="w-full">
                    <div class="absolute flex flex-col items-center justify-center top-0 w-full h-full">
                        <i class="fa-solid fa-lock text-4xl text-gray-400 group-hover:text-fuchsia-800"></i>
                        <div class="text-sm text-gray-400 group-hover:text-fuchsia-800 mt-2">Desbloquear el historial de precios más bajos</div>
                    </div>
                </a>
            @endif
            <x-link-button href="{{ $product->url }}" target="_blank" class="mt-6 w-full !p-3 block text-center">
                Comprar en {{ $product->store->name }}
                <i class="fa-solid fa-arrow-up-right-from-square text-xs pl-2"></i>
            </x-link-button>
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
                                    maxTicksLimit: 10,
                                    font: {
                                        size: 12,
                                        family: 'figtree',
                                    },
                                    callback: function (value) {
                                        return new Intl.NumberFormat('en-US', {
                                            style: 'currency',
                                            currency: 'USD',
                                            minimumFractionDigits: 0,
                                            maximumFractionDigits: 0,
                                        }).format(value);
                                    }
                                }
                            }
                        },
                    },
                },
            );
        </script>
    @endpush
</div>
