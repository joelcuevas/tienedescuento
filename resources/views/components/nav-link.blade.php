@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center text-sm leading-5 text-gray-600 hover:text-gray-900 focus:outline-none transition duration-150 ease-in-out'
            : 'inline-flex items-center text-sm leading-5 text-gray-600 hover:text-gray-900 focus:outline-none focus:text-gray-600 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
