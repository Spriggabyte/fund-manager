<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Your password was set for you. Choose your own before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.change.update') }}">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="password" :value="__('New Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autofocus autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <button type="submit" form="logout-form" class="underline text-sm text-gray-600 hover:text-gray-900 me-4">
                {{ __('Log out') }}
            </button>

            <x-primary-button>
                {{ __('Save Password') }}
            </x-primary-button>
        </div>
    </form>

    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
        @csrf
    </form>
</x-guest-layout>
