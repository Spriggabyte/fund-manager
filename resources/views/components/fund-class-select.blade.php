@props(['selected' => ''])

<x-input-label for="class" :value="__('Fund Class (Optional)')" />
<select id="class" name="class" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
    <option value="">Select a fund class...</option>
    @foreach (['Equity', 'Bond', 'Mixed', 'Money Market', 'Real Estate', 'Commodities'] as $class)
        <option value="{{ $class }}" {{ old('class', $selected) === $class ? 'selected' : '' }}>{{ $class }}</option>
    @endforeach
</select>
<x-input-error :messages="$errors->get('class')" class="mt-2" />
<p class="mt-1 text-sm text-gray-600">Choose the type of fund you're creating</p>
