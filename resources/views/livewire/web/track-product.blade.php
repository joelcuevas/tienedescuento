<div>
    @if ($style == 'chip')
        @if ($tracking)
            <button wire:click.prevent="toggle" title="{{ __('Stop tracking this product') }}" class="group/chip hover:bg-red-600 absolute -bottom-4 right-2 inline-flex text-sm rounded-full bg-fuchsia-800 text-white h-8 w-8 items-center justify-center leading-6">
                <i class="fa fa-heart block group-hover/chip:hidden"></i>
                <i class="fa fa-times hidden group-hover/chip:block"></i>
            </button>
        @endif
    @else
        <div class="w-full sm:w-auto">
            @if (! Auth::user())
                <x-link-button 
                    primary 
                    href="/login" 
                    x-on:click.prevent="$dispatch('show-login-modal')"
                    class="w-full py-3 sm:w-auto text-center"
                >
                    {{ __('Start tracking this product!') }}
                </x-link-button>
            @else
                @if ($tracking)
                    <x-button wire:click.prevent="toggle" class="w-full py-3 sm:w-auto text-center">
                        {{ __('Stop tracking this product') }}
                    </x-button>
                @else
                    <x-button primary wire:click.prevent="toggle" class="w-full py-3 sm:w-auto text-center">
                        {{ __('Start tracking this product!') }}
                    </x-button>
                @endif
            @endif
        </div>
    @endif
</div>

