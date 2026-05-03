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
            <!-- Flash Messages -->
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3"><p class="text-sm text-green-800">{{ session('success') }}</p></div>
                    </div>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3"><p class="text-sm text-red-800">{{ session('error') }}</p></div>
                    </div>
                </div>
            @endif

            <!-- Excel Import Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6" x-data="{ factsheetName: '', priceGraphName: '', inflationGraphName: '' }">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        Import Excel Data
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">Upload the factsheet and/or graph data Excel files to automatically populate fund fields and charts.</p>
                    <form method="POST" action="{{ route('funds.import', $fund) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="factsheet" :value="__('Factsheet (.xlsx)')" />
                                <input type="file" id="factsheet" name="factsheet" accept=".xlsx,.xls"
                                    @change="factsheetName = $event.target.files[0]?.name || ''"
                                    class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                                <p class="mt-1 text-xs text-gray-500">e.g. 810A_FACTSHEET.xlsx — imports top investments, performance, asset allocation, etc.</p>
                                <p x-show="factsheetName" class="mt-1 text-xs text-green-600 font-medium" x-text="'Selected: ' + factsheetName"></p>
                            </div>
                            <div>
                                <x-input-label for="price_graph" :value="__('Price Graph (.xlsx)')" />
                                <input type="file" id="price_graph" name="price_graph" accept=".xlsx,.xls"
                                    @change="priceGraphName = $event.target.files[0]?.name || ''"
                                    class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                                <p class="mt-1 text-xs text-gray-500">e.g. 810A_PRICE_GRAPH.xlsx — Portfolio Performance vs Benchmark chart.</p>
                                <p x-show="priceGraphName" class="mt-1 text-xs text-green-600 font-medium" x-text="'Selected: ' + priceGraphName"></p>
                            </div>
                            <div>
                                <x-input-label for="inflation_graph" :value="__('Inflation Graph (.xlsx)')" />
                                <input type="file" id="inflation_graph" name="inflation_graph" accept=".xlsx,.xls"
                                    @change="inflationGraphName = $event.target.files[0]?.name || ''"
                                    class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                                <p class="mt-1 text-xs text-gray-500">e.g. 810_SA_INFLATION_GRAPH.xlsx — Investment Strategy vs SA Inflation chart.</p>
                                <p x-show="inflationGraphName" class="mt-1 text-xs text-green-600 font-medium" x-text="'Selected: ' + inflationGraphName"></p>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                style="background-color: #16a34a; padding: 0.5rem 1.5rem; border-radius: 0.5rem; color: white; font-weight: 700; cursor: pointer;"
                                onmouseover="this.style.backgroundColor='#15803d'"
                                onmouseout="this.style.backgroundColor='#16a34a'">
                                Import Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Main Edit Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('funds.update', $fund) }}" class="space-y-8">
                        @csrf
                        @method('PUT')

                        {{-- ===== FUND INFO ===== --}}
                        <fieldset class="border border-gray-200 rounded-lg p-4">
                            <legend class="text-sm font-semibold text-gray-700 px-2">Fund Information</legend>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="name" :value="__('Fund Name')" />
                                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $fund->name)" required />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="class" :value="__('Fund Class')" />
                                    <select id="class" name="class" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Select...</option>
                                        @foreach (['Equity', 'Bond', 'Mixed', 'Money Market', 'Real Estate', 'Commodities'] as $c)
                                            <option value="{{ $c }}" {{ old('class', $fund->class) === $c ? 'selected' : '' }}>{{ $c }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('class')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="template" :value="__('Template')" />
                                    <select id="template" name="template" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        @foreach (['show' => 'Default', 'show-equity' => 'Equity', 'show-flexible' => 'Flexible', 'show-international' => 'International'] as $val => $label)
                                            <option value="{{ $val }}" {{ old('template', $fund->template) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="fund_date" :value="__('Fund Date')" />
                                    <x-text-input id="fund_date" class="block mt-1 w-full" type="text" name="fund_date" :value="old('fund_date', $fund->fund_date)" placeholder="e.g. 31 May 2025" />
                                </div>
                                <div>
                                    <x-input-label for="logo_url" :value="__('Logo URL')" />
                                    <x-text-input id="logo_url" class="block mt-1 w-full" type="text" name="logo_url" :value="old('logo_url', $fund->logo_url)" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="description" :value="__('Description')" />
                                    <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $fund->description) }}</textarea>
                                </div>
                            </div>
                        </fieldset>

                        {{-- ===== SIDEBAR FIELDS ===== --}}
                        <fieldset class="border border-gray-200 rounded-lg p-4">
                            <legend class="text-sm font-semibold text-gray-700 px-2">Sidebar Information</legend>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @php
                                    $sidebarFields = [
                                        'category' => 'Category',
                                        'domicile' => 'Domicile',
                                        'minimums' => 'Minimums',
                                        'unit_price' => 'Unit Price',
                                        'isin_number' => 'ISIN Number',
                                        'sedol' => 'SEDOL',
                                        'time_horizon' => 'Time Horizon',
                                        'base_currency' => 'Base Currency',
                                        'inception_date' => 'Inception Date',
                                        'number_of_units' => 'Number of Units',
                                        'portfolio_size' => 'Portfolio Size',
                                    ];
                                @endphp
                                @foreach ($sidebarFields as $field => $label)
                                    <div>
                                        <x-input-label :for="$field" :value="$label" />
                                        <x-text-input :id="$field" class="block mt-1 w-full" type="text" :name="$field" :value="old($field, $fund->$field)" />
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 space-y-4">
                                @php
                                    $sidebarTextareas = [
                                        'benchmark' => 'Benchmark',
                                        'risk_of_loss' => 'Risk of Loss',
                                        'fund_managers' => 'Fund Managers',
                                        'foreign_assets' => 'Foreign Assets',
                                        'equity_indicator_description' => 'Equity Indicator Description',
                                        'last_distributions' => 'Last Distributions',
                                        'management_company' => 'Management Company',
                                        'income_distributions' => 'Income Distributions',
                                        'portfolio_orientation' => 'Portfolio Orientation',
                                        'income_characteristics' => 'Income Characteristics',
                                        'significant_restrictions' => 'Significant Restrictions',
                                    ];
                                @endphp
                                @foreach ($sidebarTextareas as $field => $label)
                                    <div>
                                        <x-input-label :for="$field" :value="$label" />
                                        <textarea :id="$field" name="{{ $field }}" rows="2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">{{ old($field, $fund->$field) }}</textarea>
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>

                        {{-- ===== FOOTER ===== --}}
                        <fieldset class="border border-gray-200 rounded-lg p-4">
                            <legend class="text-sm font-semibold text-gray-700 px-2">Footer / Contact</legend>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <x-input-label for="footer_email" :value="__('Email')" />
                                    <x-text-input id="footer_email" class="block mt-1 w-full" type="text" name="footer_email" :value="old('footer_email', $fund->footer_email)" />
                                </div>
                                <div>
                                    <x-input-label for="footer_phone" :value="__('Phone')" />
                                    <x-text-input id="footer_phone" class="block mt-1 w-full" type="text" name="footer_phone" :value="old('footer_phone', $fund->footer_phone)" />
                                </div>
                                <div>
                                    <x-input-label for="footer_website" :value="__('Website')" />
                                    <x-text-input id="footer_website" class="block mt-1 w-full" type="text" name="footer_website" :value="old('footer_website', $fund->footer_website)" />
                                </div>
                            </div>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <x-input-label for="footer_info" :value="__('Footer Info Text')" />
                                    <textarea id="footer_info" name="footer_info" rows="2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">{{ old('footer_info', $fund->footer_info) }}</textarea>
                                </div>
                                <div>
                                    <x-input-label for="footer_free_of_charge" :value="__('Free of Charge Text')" />
                                    <x-text-input id="footer_free_of_charge" class="block mt-1 w-full" type="text" name="footer_free_of_charge" :value="old('footer_free_of_charge', $fund->footer_free_of_charge)" />
                                </div>
                                <div>
                                    <x-input-label for="footer_logo_url" :value="__('Footer Logo URL')" />
                                    <x-text-input id="footer_logo_url" class="block mt-1 w-full" type="text" name="footer_logo_url" :value="old('footer_logo_url', $fund->footer_logo_url)" />
                                </div>
                            </div>
                        </fieldset>

                        {{-- ===== IMPORTANT INFO ===== --}}
                        <fieldset class="border border-gray-200 rounded-lg p-4">
                            <legend class="text-sm font-semibold text-gray-700 px-2">Important Information</legend>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="important_info_title" :value="__('Title')" />
                                    <x-text-input id="important_info_title" class="block mt-1 w-full" type="text" name="important_info_title" :value="old('important_info_title', $fund->important_info_title)" />
                                </div>
                                <div>
                                    <x-input-label for="important_info_published_date" :value="__('Published Date')" />
                                    <x-text-input id="important_info_published_date" class="block mt-1 w-full" type="text" name="important_info_published_date" :value="old('important_info_published_date', $fund->important_info_published_date)" />
                                </div>
                            </div>
                            <div class="mt-4">
                                <x-input-label for="important_info_paragraphs" :value="__('Paragraphs (JSON array of strings)')" />
                                <textarea id="important_info_paragraphs" name="important_info_paragraphs" rows="6"
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm font-mono text-xs">{{ old('important_info_paragraphs', json_encode($fund->important_info_paragraphs ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
                                <x-input-error :messages="$errors->get('important_info_paragraphs')" class="mt-2" />
                            </div>
                        </fieldset>

                        {{-- ===== JSON DATA SECTIONS ===== --}}
                        @php
                            $jsonSections = [
                                'asset_allocation' => 'Asset Allocation',
                                'top_investments' => 'Top Investments',
                                'performance_table' => 'Performance Table',
                                'chart_data' => 'Chart Data',
                                'fees' => 'Fees',
                            ];
                        @endphp
                        @foreach ($jsonSections as $field => $label)
                            <fieldset class="border border-gray-200 rounded-lg p-4" x-data="{ open: false }">
                                <legend class="text-sm font-semibold text-gray-700 px-2 cursor-pointer" @click="open = !open">
                                    {{ $label }} (JSON)
                                    <span class="text-xs text-gray-400" x-text="open ? '▼' : '▶'"></span>
                                </legend>
                                <div x-show="open" x-transition>
                                    <textarea id="{{ $field }}" name="{{ $field }}" rows="10"
                                        class="block mt-2 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm font-mono text-xs">{{ old($field, json_encode($fund->$field ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
                                    <x-input-error :messages="$errors->get($field)" class="mt-2" />
                                    <p class="mt-1 text-xs text-gray-500">This data is typically populated via Excel import. Edit manually only if needed.</p>
                                </div>
                            </fieldset>
                        @endforeach

                        <!-- Fund Info Bar -->
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

                    <!-- Delete Section -->
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
