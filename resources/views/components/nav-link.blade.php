@props(['active'])

@php
$classes = ($active ?? false)
            ? 'border-mid-green border-2 text-gray-900 focus:outline-none focus:border-mid-green transition duration-150 ease-in-out'
            : 'border-transparent text-gray-500 border-2 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
