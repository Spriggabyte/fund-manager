@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-xs uppercase tracking-wider text-gray-600']) }}>
    {{ $value ?? $slot }}
</label>
