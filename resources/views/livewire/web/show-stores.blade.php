<div>
    <div>
        <div class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-4 lg:gap-x-8">
            @foreach ($stores as $store)
            <a href="{{ route('catalogs.index', $store->slug) }}" title="{{ $store->title }}" class="group text-sm w-full">
                <div class="bg-gray-100/80 rounded-xl p-2 aspect-square object-center w-full group-hover:bg-gray-200/70">
                    <img src="{{ $store->image_url }}" alt="{{ $store->title }}" class="mix-blend-multiply rounded-lg aspect-square object-cover">
                </div>
                <h3 class="mt-4 font-medium text-gray-900 line-clamp-2 text-balance">{{ $store->title }}</h3>
            </a>
            @endforeach
        </div>
        <div class="mt-12">{{ $stores->links() }}</div>
    </div>
</div>
