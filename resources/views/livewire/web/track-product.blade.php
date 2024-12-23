<div>
    @if (! Auth()->user())
        <x-link-button primary href="/login" x-on:click.prevent="$dispatch('show-login-modal')">
            @if ($product->hasDiscount())
                ¡Avísame si baja más de precio!
            @else
                ¡Avísame cuando tenga descuento!
            @endif
        </x-link-button>
    @else
        @if ($tracking)
            <x-button wire:click="untrack">
                Dejar de monitorear
            </x-button>
        @else
            <x-button primary wire:click="track">
                @if ($product->hasDiscount())
                    ¡Avísame si baja más de precio!
                @else
                    ¡Avísame cuando tenga descuento!
                @endif
            </x-button>
        @endif
    @endif
</div>
