<div>
    <div class="flex items-center mb-4 space-x-5">
        <h2 class="font-medium text-gray-900">
            Los mejores descuentos
        </h2>
    </div>

    <div class="space-y-12">
        <div class="grid grid-cols-2 gap-x-4 lg:gap-x-8 gap-y-8 lg:gap-y-12 sm:grid-cols-3 lg:grid-cols-6">
            @for ($i = 0; $i < 6; $i++) 
                <x-product-card-skeleton />
            @endfor
        </div>
    </div>  
</div>