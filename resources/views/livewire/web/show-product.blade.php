<div class="mt-6">
    <div class="lg:grid lg:grid-cols-2 lg:gap-x-8">            
        <div class="md:flex items-start justify-between space-x-5">
            <div>
                <div class="flex items-center space-x-3 text-sm">
                    <a href="#" class="text-gray-600 hover:text-gray-900">Tienda: {{ $product->store->name }}</a>
                    <span class="text-gray-300">/</span>
                    <a href="#" class="text-gray-600 hover:text-gray-900">Marca: {{ $product->brand }}</a>
                </div>
                <h1 class="mt-4 text-2xl font-semibold tracking-tight text-balance text-gray-900 sm:text-4xl">{{ $product->title }}</h1>
                <div class="mt-4 text-gray-600 text-sm">SKU: {{ $product->sku }}</div>
                <div class="mt-3 text-xl"><x-price :$product :verbose="true" /></div>
            </div>

            <div class="min-w-32 mt-4">
                <img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="h-32 w-32 aspect-square rounded-xl p-1 object-cover border border-gray-100">
            </div>
        </div>
      
        <div class="mt-10 lg:col-start-2 lg:row-span-2 lg:mt-0 lg:self-center">
            <!-- ads -->
        </div>
        
        <div class="mt-10 lg:col-start-1 lg:row-start-2 lg:self-start">
            <canvas id="prices-chart" height="80"></canvas>
            <a href="{{ $product->url }}" target="_blank" class="mt-6 bg-gray-800 text-white hover:bg-gray-900 rounded-lg w-full p-3 block text-center">Comprar en {{ $product->store->name }}</a>
        </div>
    </div>  
    
    @push('scripts')
        <script>
            const myChart = new Chart(
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
                        },
                        scales: {
                            x: {
                                ticks: false,
                            },
                            y: {
                                ticks: {
                                    maxTicksLimit: 5,
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
                        }
                    }
                },
            );
        </script>
    @endpush
</div>
