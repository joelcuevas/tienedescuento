<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="{{ config('app.name') }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ request()->url() }}">
    {!! $meta ?? '' !!}
    
    <title>{!! isset($title) ? $title.' - ' : '' !!} {{ config('app.name') }}</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="preload" href="https://kit.fontawesome.com/3c033f8319.js" as="script" crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/3c033f8319.js" crossorigin="anonymous"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased">
    <x-banner />
    <x-login-modal />
    
    <div class="min-h-screen">
        @livewire('navigation-menu')
        
        <main>
            <div class="mx-auto max-w-7xl overflow-hidden px-4 md:px-6 lg:px-8 py-8 lg:py-12 bg-white">
                @if (isset($header))
                    <header class="bg-white">
                        <div class="mb-6 md:mb-10">
                            {{ $header }}
                        </div>
                    </header>
                @endif
                
                {{ $slot }}
            </div>
        </main>
        
        <footer class="mt-4 border-t border-gray-200 text-sm">
            <div class="mx-auto max-w-7xl overflow-hidden px-4 md:px-6 lg:px-8 py-8 lg:py-12 bg-white">
                <x-taxonomy-tree />
                <div class="border-t border-gray-200 mt-10 pt-10">
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }} - @lang('All rights reserved').</p>
                </div>
            </div>
        </footer>              
    </div>
    
    @stack('modals')
    @livewireScripts
    @stack('scripts')
</body>
</html>
