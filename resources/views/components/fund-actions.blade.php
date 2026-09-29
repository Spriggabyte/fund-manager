@props(['fund'])

<x-dropdown align="right" width="48">
    <x-slot name="trigger">
        <button type="button" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition ease-in-out duration-150">
            Actions
            <svg class="ms-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </x-slot>

    <x-slot name="content">
        <x-dropdown-link :href="route('funds.show', $fund)">View</x-dropdown-link>
        <x-dropdown-link :href="route('funds.edit', $fund)">Edit</x-dropdown-link>
        <x-dropdown-link :href="route('funds.pdf', $fund)">Export as PDF</x-dropdown-link>
        @can('delete', $fund)
            <form method="POST" action="{{ route('funds.destroy', $fund) }}" class="border-t border-gray-100">
                @csrf
                @method('DELETE')
                <button type="submit" class="block w-full px-4 py-2 text-start text-sm leading-5 text-red-600 hover:bg-red-50 focus:outline-none focus:bg-red-50 transition duration-150 ease-in-out" onclick="return confirm('Delete {{ addslashes($fund->name) }}? This cannot be undone.')">Delete</button>
            </form>
        @endcan
    </x-slot>
</x-dropdown>
