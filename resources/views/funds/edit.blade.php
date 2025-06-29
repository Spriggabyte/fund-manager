<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Fund: ' . $fund->name) }}
            </h2>
            <div class="flex space-x-3">
                <a href="{{ route('funds.show', $fund) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    View Fund
                </a>
                <a href="{{ route('dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Success Message -->
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('funds.update', $fund) }}" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <!-- Fund Name -->
                        <div>
                            <x-input-label for="name" :value="__('Fund Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $fund->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-600">Enter a descriptive name for your fund</p>
                        </div>

                        <!-- Fund Class -->
                        <div>
                            <x-input-label for="class" :value="__('Fund Class (Optional)')" />
                            <select id="class" name="class" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Select a fund class...</option>
                                <option value="Equity" {{ old('class', $fund->class) === 'Equity' ? 'selected' : '' }}>Equity</option>
                                <option value="Bond" {{ old('class', $fund->class) === 'Bond' ? 'selected' : '' }}>Bond</option>
                                <option value="Mixed" {{ old('class', $fund->class) === 'Mixed' ? 'selected' : '' }}>Mixed</option>
                                <option value="Money Market" {{ old('class', $fund->class) === 'Money Market' ? 'selected' : '' }}>Money Market</option>
                                <option value="Real Estate" {{ old('class', $fund->class) === 'Real Estate' ? 'selected' : '' }}>Real Estate</option>
                                <option value="Commodities" {{ old('class', $fund->class) === 'Commodities' ? 'selected' : '' }}>Commodities</option>
                            </select>
                            <x-input-error :messages="$errors->get('class')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-600">Choose the type of fund you're creating</p>
                        </div>

                        <!-- JSON Data -->
                        <div>
                            <x-input-label for="data" :value="__('Fund Data (Optional JSON)')" />
                            <textarea id="data" name="data" rows="10" 
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm font-mono text-sm"
                                placeholder='&#123;
  "value": 100000.00,
  "performance": "+5.2%",
  "risk_level": "medium",
  "inception_date": "2024-01-01",
  "expense_ratio": 0.75,
  "holdings": [
    &#123;"symbol": "AAPL", "percentage": 15.2&#125;,
    &#123;"symbol": "MSFT", "percentage": 12.8&#125;
  ]
&#125;'>{{ old('data', $fund->data ? json_encode($fund->data, JSON_PRETTY_PRINT) : '') }}</textarea>
                            <x-input-error :messages="$errors->get('data')" class="mt-2" />
                            <div class="mt-2 text-sm text-gray-600">
                                <p class="font-medium">Enter valid JSON data for fund details. Example fields:</p>
                                <ul class="mt-1 list-disc list-inside space-y-1 text-gray-500">
                                    <li><code class="bg-gray-100 px-1 rounded">value</code> - Current fund value (number)</li>
                                    <li><code class="bg-gray-100 px-1 rounded">performance</code> - Performance percentage (string)</li>
                                    <li><code class="bg-gray-100 px-1 rounded">risk_level</code> - Risk assessment (string)</li>
                                    <li><code class="bg-gray-100 px-1 rounded">inception_date</code> - Fund start date (string)</li>
                                    <li><code class="bg-gray-100 px-1 rounded">expense_ratio</code> - Annual fee percentage (number)</li>
                                    <li><code class="bg-gray-100 px-1 rounded">holdings</code> - Array of fund holdings</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Fund Info -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-600">Fund ID:</span>
                                    <span class="text-gray-900">{{ $fund->id }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Created:</span>
                                    <span class="text-gray-900">{{ $fund->created_at->format('M j, Y g:i A') }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Last Updated:</span>
                                    <span class="text-gray-900">{{ $fund->updated_at->format('M j, Y g:i A') }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Owner:</span>
                                    <span class="text-gray-900">{{ $fund->user->name }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('funds.show', $fund) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Update Fund') }}
                            </x-primary-button>
                        </div>
                    </form>

                    <!-- Delete Section (Separate Form) -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Danger Zone</h3>
                                <p class="text-sm text-gray-600">Permanently delete this fund. This action cannot be undone.</p>
                            </div>
                            <form method="POST" action="{{ route('funds.destroy', $fund) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out"
                                    onclick="return confirm('Are you sure you want to delete this fund? This action cannot be undone.')">
                                    Delete Fund
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>