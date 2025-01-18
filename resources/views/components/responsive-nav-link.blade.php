@props(['active'])

@php
$classes = 'block w-full ps-3 pe-4 py-2 text-start text-base font-medium text-gray-600 rounded-xl focus:outline-none focus:text-fuchsia-800 focus:bg-fuchsia-100 transition duration-150 ease-in-out';

$classes .= ($active ?? false)
            ? ' bg-fuchsia-900 text-white'
            : ' hover:text-fuchsia-800';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
