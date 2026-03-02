@props(['value' => ''])

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
&#125;'>{{ $value }}</textarea>
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
