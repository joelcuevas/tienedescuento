<div class="space-y-6 mt-6 mb-2">
    @if(! empty(\JoelButcher\Socialstream\Socialstream::providers()) && config('socialstream.prompt'))
        <div class="relative flex items-center">
            <div class="flex-grow border-t border-gray-100"></div>
            <span class="flex-shrink text-sm text-gray-400 px-6">
                {{ config('socialstream.prompt', 'Login Via') }}
            </span>
            <div class="flex-grow border-t border-gray-100"></div>
        </div>
    @endif

    <x-input-error :for="'socialstream'" class="text-center"/>

    <div class="grid gap-4">
        @foreach (\JoelButcher\Socialstream\Socialstream::providers() as $provider)
            <x-link-button class="flex gap-2 items-center justify-center"
                href="{{ route('oauth.redirect', $provider['id']) }}">
                <x-socialstream-icons.provider-icon :provider="$provider['id']" class="h-6 w-6"/>
                <span class="block font-medium text-sm text-gray-700">{{ $provider['buttonLabel'] }}</span>
            </x-link-button>
        @endforeach
    </div>
</div>
