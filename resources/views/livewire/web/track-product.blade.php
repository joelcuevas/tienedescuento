<div class="w-full sm:w-auto">
    @if (! Auth()->user())
        <x-link-button 
            primary 
            href="/login" 
            x-on:click.prevent="$dispatch('show-login-modal')"
            class="w-full py-3 sm:w-auto text-center"
        >
            @if ($product->hasDiscount())
                ¡Avísame si baja más de precio!
            @else
                ¡Avísame cuando tenga descuento!
            @endif
        </x-link-button>
    @else
        @if ($tracking)
            <x-button wire:click="untrack" class="w-full py-3 sm:w-auto text-center">
                Dejar de monitorear
            </x-button>
        @else
            <x-button primary wire:click="track" class="w-full py-3 sm:w-auto text-center">
                @if ($product->hasDiscount())
                    ¡Avísame si baja más de precio!
                @else
                    ¡Avísame cuando tenga descuento!
                @endif
            </x-button>
        @endif
    @endif
</div>
