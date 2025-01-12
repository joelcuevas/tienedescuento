@props(['product', 'tracker' => false])

<div class="relative bg-gray-100/80 rounded-xl p-2 w-full aspect-[3/4] group-hover:bg-gray-200/70 flex items-center">
    <div class="rounded-lg overflow-hidden w-full {{ $product->store->thumbAspect() == 'square' ? '' : 'h-full' }}">
        <img 
            src="{{ $product->image_url }}" 
            alt="{{ $product->title }}" 
            loading="lazy" 
            class="
                mix-blend-multiply w-full h-full rounded-lg 
                {{ $product->store->thumbAspect() == 'square' ? 'object-scale-down' : 'object-cover' }}
                {{ $product->isOutdated() ? 'grayscale opacity-60' : '' }}
            ">
    </div>
    
    @if ($product->isOutdated())
        <div class="absolute top-0 left-0 w-full h-full flex items-center justify-center">
            <div class="text-gray-500 text-xs">
                Podría estar agotado
            </div>
        </div>
    @endif
    
    @if ($product->discount > 0)
        <div class="absolute top-2 right-2 inline-flex text-xs rounded-full bg-red-700 text-white px-2 leading-6">
            -{{ abs($product->discount) }}%
        </div>
    @endif

    @if ($tracker && Auth::user() && Auth::user()->isTracking($product))
        <div title="Monitoreando" class="absolute -bottom-4 right-2 inline-flex text-sm rounded-full bg-fuchsia-800 text-white h-8 w-8 items-center justify-center leading-6">
            <i class="fa fa-eye"></i>
        </div>
    @endif
</div>