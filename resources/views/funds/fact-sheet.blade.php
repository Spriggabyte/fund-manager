<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Fund Fact Sheet: ' . $fund->name) }}
            </h2>
            <div class="flex space-x-3">
                <a href="{{ route('funds.show', $fund) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    Standard View
                </a>
                <a href="{{ route('funds.edit', $fund) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    Full Edit
                </a>
                <a href="{{ route('dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="factSheetEditor">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
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

            <!-- Edit Mode Toggle -->
            <div class="bg-white shadow-sm sm:rounded-lg border">
                <div class="px-6 py-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Professional Fund Fact Sheet</h3>
                        <button @click="toggleEditMode()" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out text-sm">
                            <span x-show="!editMode">Enable Editing</span>
                            <span x-show="editMode">View Mode</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Fund Header -->
            <div class="bg-white shadow-sm sm:rounded-lg border">
                <div class="px-8 py-6">
                    <div class="text-center mb-6">
                        <div x-show="!editMode" class="text-3xl font-bold text-gray-900 mb-2" x-text="fundData.fund_name || 'Fund Name'"></div>
                        <div x-show="editMode" class="mb-2">
                            <input type="text" 
                                x-model="fundData.fund_name" 
                                @change="saveFundData('fund_name', $event.target.value)"
                                class="text-3xl font-bold text-gray-900 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-center w-full"
                                placeholder="Fund Name" />
                        </div>
                        
                        <div x-show="!editMode" class="text-lg text-gray-600 mb-1" x-text="fundData.fund_class || 'Fund Class'"></div>
                        <div x-show="editMode" class="mb-1">
                            <input type="text" 
                                x-model="fundData.fund_class" 
                                @change="saveFundData('fund_class', $event.target.value)"
                                class="text-lg text-gray-600 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-center w-full"
                                placeholder="Fund Class" />
                        </div>
                        
                        <div class="text-sm text-gray-500" x-text="'As at ' + (fundData.as_at_date || 'Date')"></div>
                        <div x-show="editMode" class="mt-2">
                            <input type="date" 
                                x-model="fundData.as_at_date" 
                                @change="saveFundData('as_at_date', $event.target.value)"
                                class="text-sm text-gray-500 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-center mx-auto" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fund Information Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Fund Details -->
                <div class="bg-white shadow-sm sm:rounded-lg border">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Fund Information</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <!-- Fund Manager -->
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-600">Fund Manager:</dt>
                                <dd class="text-sm text-gray-900">
                                    <div x-show="!editMode" x-text="fundData.fund_manager || 'Not specified'"></div>
                                    <div x-show="editMode">
                                        <input type="text" 
                                            x-model="fundData.fund_manager" 
                                            @change="saveFundData('fund_manager', $event.target.value)"
                                            class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
                                            placeholder="Fund Manager" />
                                    </div>
                                </dd>
                            </div>

                            <!-- Launch Date -->
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-600">Launch Date:</dt>
                                <dd class="text-sm text-gray-900">
                                    <div x-show="!editMode" x-text="fundData.launch_date || 'Not specified'"></div>
                                    <div x-show="editMode">
                                        <input type="date" 
                                            x-model="fundData.launch_date" 
                                            @change="saveFundData('launch_date', $event.target.value)"
                                            class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                                    </div>
                                </dd>
                            </div>

                            <!-- Fund Size -->
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-600">Fund Size:</dt>
                                <dd class="text-sm text-gray-900">
                                    <div x-show="!editMode" x-text="fundData.fund_size || 'Not specified'"></div>
                                    <div x-show="editMode">
                                        <input type="text" 
                                            x-model="fundData.fund_size" 
                                            @change="saveFundData('fund_size', $event.target.value)"
                                            class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
                                            placeholder="e.g., R 1.2 billion" />
                                    </div>
                                </dd>
                            </div>

                            <!-- Benchmark -->
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-600">Benchmark:</dt>
                                <dd class="text-sm text-gray-900">
                                    <div x-show="!editMode" x-text="fundData.benchmark || 'Not specified'"></div>
                                    <div x-show="editMode">
                                        <input type="text" 
                                            x-model="fundData.benchmark" 
                                            @change="saveFundData('benchmark', $event.target.value)"
                                            class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
                                            placeholder="Benchmark" />
                                    </div>
                                </dd>
                            </div>

                            <!-- Risk Profile -->
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-600">Risk Profile:</dt>
                                <dd class="text-sm text-gray-900">
                                    <div x-show="!editMode" x-text="fundData.risk_profile || 'Not specified'"></div>
                                    <div x-show="editMode">
                                        <select x-model="fundData.risk_profile" 
                                            @change="saveFundData('risk_profile', $event.target.value)"
                                            class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option value="">Select risk profile...</option>
                                            <option value="Conservative">Conservative</option>
                                            <option value="Moderate">Moderate</option>
                                            <option value="Aggressive">Aggressive</option>
                                        </select>
                                    </div>
                                </dd>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Investment Details -->
                <div class="bg-white shadow-sm sm:rounded-lg border">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Investment Details</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <!-- Minimum Investment -->
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-600">Minimum Investment:</dt>
                                <dd class="text-sm text-gray-900">
                                    <div x-show="!editMode" x-text="fundData.minimum_investment || 'Not specified'"></div>
                                    <div x-show="editMode">
                                        <input type="text" 
                                            x-model="fundData.minimum_investment" 
                                            @change="saveFundData('minimum_investment', $event.target.value)"
                                            class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
                                            placeholder="e.g., R 10,000" />
                                    </div>
                                </dd>
                            </div>

                            <!-- Management Fee -->
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-600">Management Fee:</dt>
                                <dd class="text-sm text-gray-900">
                                    <div x-show="!editMode" x-text="fundData.management_fee || 'Not specified'"></div>
                                    <div x-show="editMode">
                                        <input type="text" 
                                            x-model="fundData.management_fee" 
                                            @change="saveFundData('management_fee', $event.target.value)"
                                            class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
                                            placeholder="e.g., 1.25% p.a." />
                                    </div>
                                </dd>
                            </div>

                            <!-- Performance Fee -->
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-600">Performance Fee:</dt>
                                <dd class="text-sm text-gray-900">
                                    <div x-show="!editMode" x-text="fundData.performance_fee || 'Not specified'"></div>
                                    <div x-show="editMode">
                                        <input type="text" 
                                            x-model="fundData.performance_fee" 
                                            @change="saveFundData('performance_fee', $event.target.value)"
                                            class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
                                            placeholder="e.g., 20% of outperformance" />
                                    </div>
                                </dd>
                            </div>

                            <!-- Total Expense Ratio -->
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-600">Total Expense Ratio:</dt>
                                <dd class="text-sm text-gray-900">
                                    <div x-show="!editMode" x-text="fundData.total_expense_ratio || 'Not specified'"></div>
                                    <div x-show="editMode">
                                        <input type="text" 
                                            x-model="fundData.total_expense_ratio" 
                                            @change="saveFundData('total_expense_ratio', $event.target.value)"
                                            class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
                                            placeholder="e.g., 1.45% p.a." />
                                    </div>
                                </dd>
                            </div>

                            <!-- Entry Fee -->
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-600">Entry Fee:</dt>
                                <dd class="text-sm text-gray-900">
                                    <div x-show="!editMode" x-text="fundData.entry_fee || 'Not specified'"></div>
                                    <div x-show="editMode">
                                        <input type="text" 
                                            x-model="fundData.entry_fee" 
                                            @change="saveFundData('entry_fee', $event.target.value)"
                                            class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
                                            placeholder="e.g., 0%" />
                                    </div>
                                </dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Table -->
            <div class="bg-white shadow-sm sm:rounded-lg border">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Performance (% per annum)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fund</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Benchmark</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outperformance</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="(performance, period) in fundData.performance_data || {}" :key="period">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 capitalize" x-text="period.replace('_', ' ')"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div x-show="!editMode" x-text="performance.fund + '%'"></div>
                                        <div x-show="editMode">
                                            <input type="number" 
                                                :x-model.number="performance.fund" 
                                                @change="savePerformanceData(period, 'fund', $event.target.value)"
                                                class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-20"
                                                step="0.1" />
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div x-show="!editMode" x-text="performance.benchmark + '%'"></div>
                                        <div x-show="editMode">
                                            <input type="number" 
                                                :x-model.number="performance.benchmark" 
                                                @change="savePerformanceData(period, 'benchmark', $event.target.value)"
                                                class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-20"
                                                step="0.1" />
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm" :class="performance.outperformance >= 0 ? 'text-green-600' : 'text-red-600'">
                                        <span x-text="(performance.outperformance >= 0 ? '+' : '') + performance.outperformance + '%'"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Asset Allocation -->
            <div class="bg-white shadow-sm sm:rounded-lg border">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Asset Allocation</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <template x-for="(allocation, asset) in fundData.asset_allocation || {}" :key="asset">
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="font-medium text-gray-900 capitalize" x-text="asset.replace('_', ' ')"></div>
                                    <div class="text-right">
                                        <div x-show="!editMode" class="font-medium text-gray-900" x-text="allocation + '%'"></div>
                                        <div x-show="editMode" class="flex items-center space-x-2">
                                            <input type="number" 
                                                :x-model.number="fundData.asset_allocation[asset]" 
                                                @change="saveAssetAllocation(asset, $event.target.value)"
                                                class="w-20 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                                step="0.1" min="0" max="100" />
                                            <span class="text-sm text-gray-500">%</span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="space-y-3">
                            <h4 class="font-medium text-gray-900">Geographic Allocation</h4>
                            <template x-for="(allocation, region) in fundData.geographic_allocation || {}" :key="region">
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="font-medium text-gray-900 capitalize" x-text="region.replace('_', ' ')"></div>
                                    <div class="text-right">
                                        <div x-show="!editMode" class="font-medium text-gray-900" x-text="allocation + '%'"></div>
                                        <div x-show="editMode" class="flex items-center space-x-2">
                                            <input type="number" 
                                                :x-model.number="fundData.geographic_allocation[region]" 
                                                @change="saveGeographicAllocation(region, $event.target.value)"
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
            </div>

            <!-- Top 10 Investments -->
            <div class="bg-white shadow-sm sm:rounded-lg border">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Top 10 Investments</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% of Fund</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="(investment, index) in fundData.top_10_investments || []" :key="index">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="index + 1"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div x-show="!editMode" x-text="investment.company"></div>
                                        <div x-show="editMode">
                                            <input type="text" 
                                                :x-model="investment.company" 
                                                @change="saveTopInvestment(index, 'company', $event.target.value)"
                                                class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" />
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div x-show="!editMode" x-text="investment.percentage + '%'"></div>
                                        <div x-show="editMode" class="flex items-center space-x-2">
                                            <input type="number" 
                                                :x-model.number="investment.percentage" 
                                                @change="saveTopInvestment(index, 'percentage', $event.target.value)"
                                                class="w-20 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                                step="0.1" min="0" max="100" />
                                            <span class="text-sm text-gray-500">%</span>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('factSheetEditor', () => ({
                fundData: @json($fund->data ?? {}),
                editMode: false,
                saving: false,
                message: {
                    show: false,
                    type: 'success',
                    text: ''
                },

                toggleEditMode() {
                    this.editMode = !this.editMode;
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

                async savePerformanceData(period, type, value) {
                    if (this.saving) return;
                    
                    this.saving = true;
                    
                    try {
                        if (!this.fundData.performance_data) {
                            this.fundData.performance_data = {};
                        }
                        if (!this.fundData.performance_data[period]) {
                            this.fundData.performance_data[period] = { fund: 0, benchmark: 0, outperformance: 0 };
                        }
                        
                        this.fundData.performance_data[period][type] = parseFloat(value) || 0;
                        this.fundData.performance_data[period].outperformance = 
                            this.fundData.performance_data[period].fund - this.fundData.performance_data[period].benchmark;

                        await this.saveFundData('performance_data', this.fundData.performance_data);
                    } finally {
                        this.saving = false;
                    }
                },

                async saveAssetAllocation(asset, value) {
                    if (this.saving) return;
                    
                    this.saving = true;
                    
                    try {
                        if (!this.fundData.asset_allocation) {
                            this.fundData.asset_allocation = {};
                        }
                        
                        this.fundData.asset_allocation[asset] = parseFloat(value) || 0;
                        await this.saveFundData('asset_allocation', this.fundData.asset_allocation);
                    } finally {
                        this.saving = false;
                    }
                },

                async saveGeographicAllocation(region, value) {
                    if (this.saving) return;
                    
                    this.saving = true;
                    
                    try {
                        if (!this.fundData.geographic_allocation) {
                            this.fundData.geographic_allocation = {};
                        }
                        
                        this.fundData.geographic_allocation[region] = parseFloat(value) || 0;
                        await this.saveFundData('geographic_allocation', this.fundData.geographic_allocation);
                    } finally {
                        this.saving = false;
                    }
                },

                async saveTopInvestment(index, field, value) {
                    if (this.saving) return;
                    
                    this.saving = true;
                    
                    try {
                        if (!this.fundData.top_10_investments) {
                            this.fundData.top_10_investments = [];
                        }
                        
                        if (!this.fundData.top_10_investments[index]) {
                            this.fundData.top_10_investments[index] = { company: '', percentage: 0 };
                        }
                        
                        if (field === 'percentage') {
                            this.fundData.top_10_investments[index][field] = parseFloat(value) || 0;
                        } else {
                            this.fundData.top_10_investments[index][field] = value;
                        }
                        
                        await this.saveFundData('top_10_investments', this.fundData.top_10_investments);
                    } finally {
                        this.saving = false;
                    }
                }
            }))
        })
    </script>
</x-app-layout>