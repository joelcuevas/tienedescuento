<div class="text-sm text-gray-600 flex grow-0 space-x-6">
    <a href="/" class="px-1 py-3 whitespace-nowrap">Lo Más Hot</a>
    <a href="#" class="px-1 py-3 whitespace-nowrap">Categorías</a>
    <a href="#" class="px-1 py-3 whitespace-nowrap">Tiendas</a>
    <div class="border-r border-gray-200 my-3"></div>
    @foreach ($stores as $store)
        <a href="{{ $store->link() }}" class="px-1 py-3 whitespace-nowrap">{{ $store->name }}</a>
    @endforeach
</div>