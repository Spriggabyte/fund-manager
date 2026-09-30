@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-naartjie-600 text-xs font-semibold uppercase tracking-wider leading-5 text-navy-700 focus:outline-none focus:border-naartjie-700 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-xs font-medium uppercase tracking-wider leading-5 text-gray-600 hover:text-navy-700 hover:border-naartjie-300 focus:outline-none focus:text-navy-700 focus:border-naartjie-300 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
