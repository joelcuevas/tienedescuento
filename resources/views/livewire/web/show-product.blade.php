<div>
    <div class="bg-white">
        <div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 sm:py-24 lg:grid lg:max-w-7xl lg:grid-cols-2 lg:gap-x-8 lg:px-8">
            <div class="lg:max-w-lg lg:self-end">
                <nav aria-label="Breadcrumb">
                    <ol role="list" class="flex items-center space-x-2">
                        <li>
                            <div class="flex items-center text-sm">
                                <a href="#" class="font-medium text-gray-500 hover:text-gray-900">{{ $product->store->name }}</a>
                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="ml-2 size-5 shrink-0 text-gray-300">
                                    <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                                </svg>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center text-sm">
                                <a href="#" class="font-medium text-gray-500 hover:text-gray-900">Marca: {{ $product->brand }}</a>
                            </div>
                        </li>
                    </ol>
                </nav>
                
                <div class="mt-4">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                        {{ $product->title }}
                    </h1>
                </div>
            </div>
            
            <!-- Product image -->
            <div class="mt-10 lg:col-start-2 lg:row-span-2 lg:mt-0 lg:self-center">
                <img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="aspect-square w-full rounded-lg object-cover">
            </div>
            
            <!-- Product form -->
            <div class="mt-10 lg:col-start-1 lg:row-start-2 lg:max-w-lg lg:self-start">
                <section aria-labelledby="options-heading">
                    <h2 id="options-heading" class="sr-only">Product options</h2>
                    <canvas id="price-chart"></canvas>
                    <div class="mt-10">
                        <a href="{{ $product->url }}" class="flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-600 px-8 py-3 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50">Comprar en {{ $product->store->name }}</a>
                    </div>
                </section>
            </div>
        </div>
    </div>      

    @push('scripts')
    <script>
        const myChart = new Chart(
            document.getElementById('price-chart'),
            {
                type: 'line',
                data: {
                    labels: @json($data->map(fn ($data) => $data->date)),
                    datasets: [{
                        label: 'Precio',
                        backgroundColor: 'rgba(255, 99, 132, 0.3)',
                        borderColor: 'rgb(255, 99, 132)',
                        data: @json($data->map(fn ($data) => $data->aggregate)),
                        stepped: 'middle',
                    }],
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                callback: function (value, index, values) {
                                    return this.getLabelForValue(value);
                                },
                                maxRotation: 90,
                                minRotation: 90,
                            }
                        },
                        y: {
                            ticks: {
                                callback: function (value) {
                                    return '$' + value.toFixed(0);
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
