<div>
    <div class="lg:grid lg:grid-cols-2 lg:gap-x-8"> 
        <div>   
            <div class="md:flex items-start justify-between space-x-5">
                <div>
                    <div class="flex items-center space-x-3 text-sm">
                        <a href="#" class="text-gray-600 hover:text-gray-900">Tienda: {{ $product->store->name }}</a>
                        <span class="text-gray-300">/</span>
                        <a href="#" class="text-gray-600 hover:text-gray-900">Marca: {{ $product->brand }}</a>
                    </div>
                    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-balance text-gray-900 sm:text-4xl">{{ $product->title }}</h1>
                    <div class="mt-3 text-gray-600 text-sm">SKU: {{ $product->sku }}</div>
                    <div class="text-gray-900 mt-6">
                        @if ($product->discount > 0)
                            <div class="flex items-center space-x-4">
                                <div class="font-medium text-3xl text-green-800 rounded-2xl">{{ $product->latest_price_formatted }}</div>
                                <div class="font-medium text-sm line-through text-gray-500">Precio Normal: {{ $product->regular_price_formatted }}</div>
                            </div>
                            <div class="mt-1 text-base text-green-800 font-medium">
                                <i class="fa-regular pr-1 fa-face-laugh"></i> 
                                ¡{{ abs($product->discount) }}% de descuento contra su precio normal!
                            </div>
                        @elseif ($product->discount < 0)
                            <div class="flex items-center space-x-3">
                                <span class="font-medium text-3xl text-red-800">{{ $product->latest_price_formatted }}</span>
                                <span class="font-medium text-sm line-through text-gray-500">Precio Normal: {{ $product->regular_price_formatted }}</span>
                            </div>
                            <div class="mt-1 text-base text-red-800 font-medium">
                                <i class="fa-regular pr-1 fa-face-frown"></i> 
                                {{ abs($product->discount) }}% superior a su precio normal.
                            </div>
                        @else
                            <div class="text-3xl">{{ $product->regular_price_formatted }}</div>
                            <div class="mt-1 text-base text-gray-500 font-medium">
                                <i class="fa-regular pr-1 fa-face-meh"></i> 
                                Precio normal.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="min-w-32">
                    <div class="bg-gray-100/90 rounded-xl p-0.5 object-center h-32 w-32">
                        <img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="mix-blend-multiply aspect-square object-contain">
                    </div>
                </div>
            </div>
        </div>
      
        <div class="lg:col-start-2 lg:row-span-2 lg:mt-0 lg:self-center">
            <!-- ads -->
        </div>
        
        <div class="mt-7 lg:col-start-1 lg:row-start-2 lg:self-start">
            <canvas id="prices-chart" height="80"></canvas>
            <a href="{{ $product->url }}" target="_blank" class="mt-6 bg-gray-800 text-white hover:bg-gray-900 rounded-lg w-full p-3 block text-center">Comprar en {{ $product->store->name }}</a>
        </div>
    </div>  
    
    @push('scripts')
        <script>
            const regularPrice = <?php echo json_encode($product->regular_price); ?>;

            new Chart(
                document.getElementById('prices-chart'),
                {
                    type: 'line',
                    data: {
                        labels: @json($data->map(fn ($data) => $data->date)),
                        datasets: [{
                            label: 'Precio',
                            backgroundColor: '#333',
                            borderColor: '#333',
                            data: @json($data->map(fn ($data) => $data->aggregate)),
                            stepped: 'middle',
                        }],
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
                                    line1: {
                                        type: 'line',
                                        yMin: regularPrice,
                                        yMax: regularPrice,
                                        borderColor: 'rgb(153, 27, 27)',
                                        borderWidth: 1,
                                        borderDash: [10, 10],
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
