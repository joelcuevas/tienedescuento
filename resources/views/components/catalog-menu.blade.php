<div class="relative z-20 isolate w-full" x-data="{ categoriesOpen: false }" @click.outside="categoriesOpen = false">
    <div class="text-sm text-gray-600 flex grow-0 space-x-6">
        <a href="/" class="px-1 py-3 whitespace-nowrap">{{ __('Trending') }}</a>
        <a href="#" 
           class="px-1 py-3 whitespace-nowrap" 
           @click.prevent="categoriesOpen = !categoriesOpen">{{ __('Categories') }}</a>
        <a href="#" class="px-1 py-3 whitespace-nowrap">{{ __('Stores') }}</a>
        <div class="border-r border-gray-200 my-3"></div>
        @foreach ($stores as $store)
            <a href="{{ $store->link() }}" class="px-1 py-3 whitespace-nowrap hidden md:block">{{ $store->name }}</a>
        @endforeach
    </div>

    <!-- Flyout Menu -->
    <div 
        x-show="categoriesOpen" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="hidden md:block bg-white absolute inset-x-0 top-14 -z-10 ring-1 shadow-lg ring-gray-200 rounded-md"
        @click.outside="categoriesOpen = false">
        <div class="mx-auto max-w-7xl w-full px-6 py-8 lg:px-8">
            <x-taxonomy-tree />
        </div>
    </div>
</div>