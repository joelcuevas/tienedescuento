<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Laravel') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/3c033f8319.js" crossorigin="anonymous"></script>
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Styles -->
    @livewireStyles
</head>
<body class="font-sans antialiased">
    <x-banner />
    
    <div class="min-h-screen bg-white">
        <header class="bg-white" x-data="{open: false}">
            <nav class="mx-auto flex max-w-7xl items-center justify-between p-6 py-6 lg:px-8 lg:py-8 border-b border-gray-100 mb-8 lg:mb-10" aria-label="Global">
                <div class="flex items-center gap-x-12">
                    <a href="/" class="text-2xl font-bold tracking-tight">
                        ¿Tiene Descuento?
                    </a>
                    <div class="hidden lg:flex lg:gap-x-12">
                        <a href="{{ route('home') }}" class="text-sm/6 text-gray-600 hover:text-gray-900">@lang('Discounts')</a>
                        <a href="{{ route('stores.index') }}" class="text-sm/6 text-gray-600 hover:text-gray-900">@lang('Stores')</a>
                    </div>
                </div>
                <div class="flex items-center space-x-2 lg:hidden">
                    <button x-on:click="open=!open" type="button" class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-700">
                        <i class="fa fa-search"></i>
                    </button>
                    <button x-on:click="open=!open" type="button" class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-700">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>
                <div class="hidden lg:flex lg:items-center lg:space-x-10">
                    <form method="GET" action="{{ route('products.search') }}">
                        <x-input name="q" class="w-[20rem] !rounded-full text-sm px-4" placeholder="{{ __('Search') }}..." value="{{ request()->q }}"></x-input>
                    </form>
                    <a href="/login" x-on:click.prevent="$dispatch('show-login-modal')" class="text-sm/6 text-gray-600 hover:text-gray-900">@lang('Track Prices') <span aria-hidden="true">&rarr;</span></a>
                </div>
            </nav>

            <div x-cloak x-show="open" class="lg:hidden" role="dialog" aria-modal="true">
                <div x-show="open" x-on:click="open=false" class="fixed inset-0 z-10 bg-gray-100/50"></div>
                <div class="fixed inset-y-0 right-0 z-10 w-full overflow-y-auto bg-white px-6 py-6 sm:max-w-sm sm:ring-1 sm:ring-gray-900/10">
                    <div class="flex items-center justify-between">
                        <a href="#" class="-m-1.5 p-1.5">
                            <img class="h-8 w-auto" src="https://tailwindui.com/plus/img/logos/mark.svg?color=indigo&shade=600" alt="">
                        </a>
                        <button x-on:click="open=false" type="button" class="-m-2.5 rounded-md p-2.5 text-gray-700">
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="mt-6 flow-root">
                        <div class="-my-6 divide-y divide-gray-500/10">
                            <div class="space-y-2 py-6">
                                <a href="#" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 text-gray-900 hover:bg-gray-50">Product</a>
                                <a href="#" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 text-gray-900 hover:bg-gray-50">Features</a>
                                <a href="#" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 text-gray-900 hover:bg-gray-50">Marketplace</a>
                                <a href="#" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 text-gray-900 hover:bg-gray-50">Company</a>
                            </div>
                            <div class="py-6">
                                <a href="#" class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 text-gray-900 hover:bg-gray-50">Monitorear</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <main>
            <x-login-modal />
            <div class="bg-white">
                <div class="mx-auto max-w-7xl overflow-hidden px-6 pb-12 bg-white lg:px-8">
                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>
    
    @stack('modals')
    
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@3.1.0/dist/chartjs-plugin-annotation.min.js"></script>
    @stack('scripts')
</body>
</html>
