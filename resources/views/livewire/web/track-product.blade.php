<div class="w-full sm:w-auto">
    @if (! Auth()->user())
        <x-link-button 
            primary 
            href="/login" 
            x-on:click.prevent="$dispatch('show-login-modal')"
            class="w-full py-3 sm:w-auto text-center"
        >
            ¡Empezar a monitorear este producto!
        </x-link-button>
    @else
        @if ($tracking)
            <x-button wire:click="untrack" class="w-full py-3 sm:w-auto text-center">
                Dejar de monitorear este producto
            </x-button>
        @else
            <x-button primary wire:click="track" class="w-full py-3 sm:w-auto text-center">
                ¡Empezar a monitorear este producto!
            </x-button>
        @endif
    @endif
</div>
