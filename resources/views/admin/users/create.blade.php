<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add User') }}
            </h2>
            <a href="{{ route('admin.users.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                Back to Users
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-600">They sign in with this address.</p>
                        </div>

                        <div>
                            <x-input-label for="password" :value="__('Temporary Password')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-600">
                                Hand this to them directly. They will be asked to choose their own password the first time they sign in.
                            </p>
                        </div>

                        <div>
                            <x-input-label for="password_confirmation" :value="__('Confirm Temporary Password')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="flex items-start">
                            <input type="hidden" name="is_admin" value="0">
                            <input id="is_admin" type="checkbox" name="is_admin" value="1" @checked(old('is_admin'))
                                class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <label for="is_admin" class="ms-2 text-sm text-gray-700">
                                <span class="font-medium">Administrator</span>
                                <span class="block text-gray-600">Can manage user accounts, delete funds, and view the queue dashboard.</span>
                            </label>
                        </div>

                        <div class="flex justify-end">
                            <x-primary-button>{{ __('Create Account') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
