@if ($product->discount > 0)
    <div class="text-sm line-through text-gray-400">{{ $product->regular_price_formatted }}</div>
    <div class="text-lg font-medium text-red-700">{{ $product->latest_price_formatted }}</div>
    <div class="text-sm rounded-full bg-red-700 text-white px-2 leading-6">-{{ abs($product->discount) }}% real</div>
@elseif ($product->discount < 0)
    <div>{{ $product->latest_price_formatted }}</div>
    <div class="text-sm text-gray-500">({{ abs($product->discount) }}% más caro)</div>
@else
    <div>{{ $product->latest_price_formatted }}</div>
    <div class="text-sm text-gray-500">(precio regular)</div>
@endif