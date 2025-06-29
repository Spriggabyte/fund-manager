<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Fund Details: ' . $fund->name) }}
            </h2>
            <div class="flex space-x-3">
                <a href="{{ route('funds.fact-sheet', $fund) }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    Fact Sheet
                </a>
                <a href="{{ route('funds.edit', $fund) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    Full Edit
                </a>
                <a href="{{ route('dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="fundEditor">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Success/Error Messages -->
            <div x-show="message.show" x-transition class="rounded-lg p-4" :class="message.type === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg x-show="message.type === 'success'" class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <svg x-show="message.type === 'error'" class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm" :class="message.type === 'success' ? 'text-green-800' : 'text-red-800'" x-text="message.text"></p>
                    </div>
                </div>
            </div>

            <!-- Fund Overview Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Fund Overview</h3>
                        <button @click="toggleEditMode()" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out text-sm">
                            <span x-show="!editMode">Quick Edit</span>
                            <span x-show="editMode">View Mode</span>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Fund Name & Class -->
                        <div class="md:col-span-2">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $fund->name }}</h3>
                            <div class="flex items-center space-x-4 mb-4">
                                @if($fund->class)
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full
                                        @if($fund->class === 'Equity') bg-green-100 text-green-800
                                        @elseif($fund->class === 'Bond') bg-blue-100 text-blue-800
                                        @else bg-purple-100 text-purple-800 @endif">
                                        {{ $fund->class }}
                                    </span>
                                @else
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Unclassified
                                    </span>
                                @endif
                                <span class="text-sm text-gray-500">ID: {{ $fund->id }}</span>
                            </div>
                            <div class="text-sm text-gray-600">
                                <p><strong>Created:</strong> {{ $fund->created_at->format('F j, Y \a\t g:i A') }}</p>
                                <p><strong>Last Updated:</strong> {{ $fund->updated_at->format('F j, Y \a\t g:i A') }}</p>
                                <p><strong>Owner:</strong> {{ $fund->user->name }}</p>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="space-y-4">
                            <!-- Current Value -->
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-green-800">Current Value</div>
                                <div x-show="!editMode" class="text-2xl font-bold text-green-900">
                                    $<span x-text="formatNumber(fundData.value || 0)"></span>
                                </div>
                                <div x-show="editMode" class="mt-2">
                                    <input type="number" 
                                        x-model.number="fundData.value" 
                                        @change="saveFundData('value', $event.target.value)"
                                        class="w-full border-green-300 focus:border-green-500 focus:ring-green-500 rounded-md shadow-sm text-lg font-bold text-green-900"
                                        step="0.01" />
                                </div>
                            </div>

                            <!-- Performance -->
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-blue-800">Performance</div>
                                <div x-show="!editMode" class="text-2xl font-bold text-blue-900" x-text="fundData.performance || 'N/A'"></div>
                                <div x-show="editMode" class="mt-2">
                                    <input type="text" 
                                        x-model="fundData.performance" 
                                        @change="saveFundData('performance', $event.target.value)"
                                        class="w-full border-blue-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-lg font-bold text-blue-900"
                                        placeholder="e.g., +5.2%" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Fund Data -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Fund Details</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Risk Level -->
                        <div class="border-l-4 border-yellow-500 pl-4">
                            <dt class="text-sm font-medium text-gray-600">Risk Level</dt>
                            <dd class="mt-1">
                                <div x-show="!editMode" class="text-sm font-medium text-gray-900 capitalize" x-text="fundData.risk_level || 'Not specified'"></div>
                                <div x-show="editMode" class="mt-1">
                                    <select x-model="fundData.risk_level" 
                                        @change="saveFundData('risk_level', $event.target.value)"
                                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Select risk level...</option>
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="very_high">Very High</option>
                                    </select>
                                </div>
                            </dd>
                        </div>

                        <!-- Inception Date -->
                        <div class="border-l-4 border-purple-500 pl-4">
                            <dt class="text-sm font-medium text-gray-600">Inception Date</dt>
                            <dd class="mt-1">
                                <div x-show="!editMode" class="text-sm font-medium text-gray-900" x-text="fundData.inception_date || 'Not specified'"></div>
                                <div x-show="editMode" class="mt-1">
                                    <input type="date" 
                                        x-model="fundData.inception_date" 
                                        @change="saveFundData('inception_date', $event.target.value)"
                                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                                </div>
                            </dd>
                        </div>

                        <!-- Expense Ratio -->
                        <div class="border-l-4 border-red-500 pl-4">
                            <dt class="text-sm font-medium text-gray-600">Expense Ratio</dt>
                            <dd class="mt-1">
                                <div x-show="!editMode" class="text-sm font-medium text-gray-900">
                                    <span x-text="fundData.expense_ratio ? fundData.expense_ratio + '%' : 'Not specified'"></span>
                                </div>
                                <div x-show="editMode" class="mt-1">
                                    <input type="number" 
                                        x-model.number="fundData.expense_ratio" 
                                        @change="saveFundData('expense_ratio', $event.target.value)"
                                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        step="0.01" min="0" max="100" />
                                </div>
                            </dd>
                        </div>

                        <!-- Minimum Investment -->
                        <div class="border-l-4 border-green-500 pl-4">
                            <dt class="text-sm font-medium text-gray-600">Minimum Investment</dt>
                            <dd class="mt-1">
                                <div x-show="!editMode" class="text-sm font-medium text-gray-900">
                                    $<span x-text="formatNumber(fundData.minimum_investment || 0)"></span>
                                </div>
                                <div x-show="editMode" class="mt-1">
                                    <input type="number" 
                                        x-model.number="fundData.minimum_investment" 
                                        @change="saveFundData('minimum_investment', $event.target.value)"
                                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        step="1" min="0" />
                                </div>
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Holdings -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-show="fundData.holdings && fundData.holdings.length > 0">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Top Holdings</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <template x-for="(holding, index) in fundData.holdings" :key="index">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-indigo-100 rounded-full p-2 text-indigo-600 font-medium text-sm" x-text="holding.symbol"></div>
                                    <div>
                                        <div class="font-medium text-gray-900" x-text="holding.symbol"></div>
                                        <div class="text-sm text-gray-500" x-text="holding.name || 'No name provided'"></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div x-show="!editMode" class="font-medium text-gray-900" x-text="holding.percentage + '%'"></div>
                                    <div x-show="editMode" class="flex items-center space-x-2">
                                        <input type="number" 
                                            :x-model.number="holding.percentage" 
                                            @change="saveHolding(index, holding)"
                                            class="w-20 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                            step="0.1" min="0" max="100" />
                                        <span class="text-sm text-gray-500">%</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Raw JSON Data (for debugging) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-show="showRawData">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Raw JSON Data</h3>
                        <button @click="showRawData = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <pre class="bg-gray-100 p-4 rounded-lg text-sm font-mono overflow-x-auto" x-text="JSON.stringify(fundData, null, 2)"></pre>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <div class="flex space-x-4">
                            <a href="{{ route('funds.edit', $fund) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                                Full Edit Mode
                            </a>
                            <a href="{{ route('funds.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                                All Funds
                            </a>
                            <button @click="showRawData = !showRawData" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                                <span x-show="!showRawData">Show JSON</span>
                                <span x-show="showRawData">Hide JSON</span>
                            </button>
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

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('fundEditor', () => ({
                fundData: {!! json_encode($fund->data ?? []) !!},
                editMode: false,
                showRawData: false,
                saving: false,
                message: {
                    show: false,
                    type: 'success',
                    text: ''
                },

                toggleEditMode() {
                    this.editMode = !this.editMode;
                },

                formatNumber(num) {
                    return new Intl.NumberFormat('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(num);
                },

                showMessage(type, text) {
                    this.message = { show: true, type, text };
                    setTimeout(() => {
                        this.message.show = false;
                    }, 5000);
                },

                async saveFundData(field, value) {
                    if (this.saving) return;
                    
                    this.saving = true;
                    
                    try {
                        const response = await fetch(`{{ route('funds.update-data', $fund) }}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                field: field,
                                value: value
                            })
                        });

                        const data = await response.json();
                        
                        if (response.ok) {
                            this.fundData = data.fund_data;
                            this.showMessage('success', `${field.replace('_', ' ')} updated successfully`);
                        } else {
                            this.showMessage('error', data.message || 'Error updating fund data');
                        }
                    } catch (error) {
                        this.showMessage('error', 'Network error occurred');
                        console.error('Error:', error);
                    } finally {
                        this.saving = false;
                    }
                },

                async saveHolding(index, holding) {
                    if (this.saving) return;
                    
                    this.saving = true;
                    
                    try {
                        const response = await fetch(`{{ route('funds.update-holding', $fund) }}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                index: index,
                                holding: holding
                            })
                        });

                        const data = await response.json();
                        
                        if (response.ok) {
                            this.fundData = data.fund_data;
                            this.showMessage('success', `Holding ${holding.symbol} updated successfully`);
                        } else {
                            this.showMessage('error', data.message || 'Error updating holding');
                        }
                    } catch (error) {
                        this.showMessage('error', 'Network error occurred');
                        console.error('Error:', error);
                    } finally {
                        this.saving = false;
                    }
                }
            }))
        })
    </script>
</x-app-layout>