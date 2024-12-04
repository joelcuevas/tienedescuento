@props(['product', 'verbose' => false])

<div class="text-gray-900 flex items-center space-x-2">
    @if ($product->latest_price < $product->regular_price || true)
        <span class="font-medium line-through text-gray-500">{{ $product->regular_price_formatted }}</span>
        <span class="font-medium text-red-800">{{ $product->latest_price_formatted }}</span>

        @if ($verbose)
            <span class="pl-6 text-base text-green-700 font-medium"><i class="fa-regular pr-1 fa-face-laugh"></i> ¡35% de descuento real!</span>
        @endif
    @elseif ($product->latest_price > $product->regular_price)
        <span class="font-medium line-through text-gray-500">{{ $product->regular_price_formatted }}</span>
        <span class="font-medium bg-red-800 text-white px-1 rounded-md">{{ $product->latest_price_formatted }}</span>
        @if ($verbose)
            <span class="pl-6 text-base text-red-800 font-medium"><i class="fa-regular pr-1 fa-face-frown"></i> 23% superior al precio regular</span>
        @endif
    @else
        <span>{{ $product->regular_price_formatted }}</span>
        @if ($verbose)
            <span class="pl-6 text-base text-gray-500 font-medium"><i class="fa-regular pr-1 fa-face-meh"></i> Precio regular</span>
        @endif
    @endif
</div>