@props(['product', 'verbose' => false])

<div class="text-gray-900 flex items-center space-x-2">
    @if ($product->discount > 0)
        @if ($verbose)
            <span class="font-medium text-sm line-through text-gray-500">{{ $product->regular_price_formatted }}</span>
        @endif

        <span class="font-medium bg-green-800 text-white px-2 rounded-2xl">{{ $product->latest_price_formatted }}</span>

        @if ($verbose)
            <span class="pl-6 text-base text-green-800 font-medium">
                <i class="fa-regular pr-1 fa-face-laugh"></i> 
                ¡{{ abs($product->discount) }}% de descuento real!
            </span>
        @endif
    @elseif ($product->discount < 0)
        @if ($verbose)
            <span class="font-medium text-sm line-through text-gray-500">{{ $product->regular_price_formatted }}</span>
        @endif
        
        <span class="font-medium text-red-800">{{ $product->latest_price_formatted }}</span>
        @if ($verbose)
            <span class="pl-6 text-base text-red-800 font-medium">
                <i class="fa-regular pr-1 fa-face-frown"></i> 
                {{ abs($product->discount) }}% superior al precio regular
            </span>
        @endif
    @else
        <span>{{ $product->regular_price_formatted }}</span>
        @if ($verbose)
            <span class="pl-6 text-base text-gray-500 font-medium">
                <i class="fa-regular pr-1 fa-face-meh"></i> 
                Precio regular
            </span>
        @endif
    @endif
</div>