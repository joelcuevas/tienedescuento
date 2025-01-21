@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-fuchsia-800 focus:ring-fuchsia-800 rounded-md placeholder:text-gray-500']) !!}>
