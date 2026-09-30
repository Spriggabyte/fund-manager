@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-naartjie-500 focus:ring-naartjie-500 rounded-md shadow-sm']) }}>
