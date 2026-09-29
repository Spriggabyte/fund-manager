@props(['fund', 'inline' => false])

@php
    // Stored in UTC (app.timezone); shown in SAST for the Foord team.
    $updated = $fund->updated_at?->copy()->timezone('Africa/Johannesburg');
@endphp

@if ($updated)
    @if ($inline)
        <span {{ $attributes->merge(['class' => 'text-sm text-gray-500']) }} title="{{ $updated->format('j M Y, H:i') }} SAST">
            Updated {{ $updated->diffForHumans() }}
        </span>
    @else
        <div {{ $attributes }} title="{{ $updated->format('j M Y, H:i') }} SAST">
            <div class="text-sm text-gray-900">{{ $updated->format('j M Y, H:i') }}</div>
            <div class="text-xs text-gray-500">{{ $updated->diffForHumans() }}</div>
        </div>
    @endif
@else
    <span class="text-gray-400 text-sm">-</span>
@endif
