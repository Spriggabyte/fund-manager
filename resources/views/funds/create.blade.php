<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create New Fund') }}
            </h2>
            <a href="{{ route('dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('funds.store') }}" class="space-y-6">
                        @csrf
                        
                        <!-- Fund Name -->
                        <div>
                            <x-input-label for="name" :value="__('Fund Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-600">Enter a descriptive name for your fund</p>
                        </div>

                        <!-- Fund Class -->
                        <div>
                            <x-input-label for="class" :value="__('Fund Class (Optional)')" />
                            <select id="class" name="class" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Select a fund class...</option>
                                <option value="Equity" {{ old('class') === 'Equity' ? 'selected' : '' }}>Equity</option>
                                <option value="Bond" {{ old('class') === 'Bond' ? 'selected' : '' }}>Bond</option>
                                <option value="Mixed" {{ old('class') === 'Mixed' ? 'selected' : '' }}>Mixed</option>
                                <option value="Money Market" {{ old('class') === 'Money Market' ? 'selected' : '' }}>Money Market</option>
                                <option value="Real Estate" {{ old('class') === 'Real Estate' ? 'selected' : '' }}>Real Estate</option>
                                <option value="Commodities" {{ old('class') === 'Commodities' ? 'selected' : '' }}>Commodities</option>
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
&#125;'>{{ old('data') }}</textarea>
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

                        <!-- JSON Validation Helper -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-800">
                                        <strong>JSON Tips:</strong> Make sure your JSON is properly formatted with matching brackets and quotes around string values. 
                                        You can validate your JSON using online tools before saving.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('dashboard') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Create Fund') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>