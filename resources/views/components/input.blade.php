@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-gray-900 focus:ring-gray-900 rounded-md placeholder:text-gray-500']) !!}>
