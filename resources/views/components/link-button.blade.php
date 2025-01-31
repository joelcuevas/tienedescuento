@props(['primary' => null])

@php
$colors = 'bg-white text-gray-900 ring-gray-300 hover:bg-gray-50';

if ($primary) {
    $colors = 'bg-fuchsia-800 ring-transparent text-white hover:bg-fuchsia-700 focus:bg-fuchsia-700 active:bg-fuchsia-700';
}

@endphp

<a {{ $attributes->merge(['href' => '#', 'class' => 'inline-block rounded-full px-8 py-3 text-sm ring-1 shadow-xs ring-inset '.$colors]) }}>
    {{ $slot }}
</a>