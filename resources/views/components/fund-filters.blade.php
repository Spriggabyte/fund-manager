@props(['funds'])

@php
    // Every fund is already loaded on the page (no pagination), so filtering
    // runs client-side. Rows opt in with x-show="matches(name, class)".
    $classes = $funds->map->displayClass()->filter()->unique()->sort(SORT_NATURAL)->values();
    $index = $funds->map(fn ($fund) => ['name' => $fund->name, 'class' => $fund->displayClass() ?? ''])->values();
@endphp

<div x-data="{
        search: '',
        fundClass: '',
        funds: @js($index),
        matches(name, fundClass) {
            const term = this.search.trim().toLowerCase();
            return (term === '' || name.toLowerCase().includes(term))
                && (this.fundClass === '' || fundClass === this.fundClass);
        },
        get visibleCount() {
            return this.funds.filter(f => this.matches(f.name, f.class)).length;
        },
    }">
    <div class="px-6 py-3 border-b border-gray-200 bg-gray-50 flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
            </svg>
            <input type="search" x-model="search" placeholder="Search funds by name…"
                   class="w-full pl-9 border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <select x-model="fundClass"
                class="sm:w-48 border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">All classes</option>
            @foreach ($classes as $class)
                <option value="{{ $class }}">Class {{ $class }}</option>
            @endforeach
        </select>
        <p class="text-sm text-gray-500 whitespace-nowrap">
            <span x-text="visibleCount">{{ $funds->count() }}</span> of {{ $funds->count() }} funds
        </p>
    </div>

    {{ $slot }}

    <div x-show="visibleCount === 0" style="display: none" class="text-center py-10 text-sm text-gray-500">
        No funds match your filters.
        <button type="button" @click="search = ''; fundClass = ''" class="ml-1 text-indigo-600 hover:text-indigo-800 font-medium">Clear filters</button>
    </div>
</div>
