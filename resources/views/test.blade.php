<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tailwind CSS Test Page') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Authentication Test Page</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-blue-100 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold text-blue-800">Login Status</h4>
                            <p class="text-blue-600 mt-2">✅ You are successfully logged in!</p>
                            <p class="text-sm text-blue-500 mt-1">User: {{ Auth::user()->name }}</p>
                        </div>
                        
                        <div class="bg-green-100 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold text-green-800">Tailwind Styling</h4>
                            <p class="text-green-600 mt-2">✅ Tailwind CSS is working perfectly!</p>
                            <button class="mt-3 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Test Button
                            </button>
                        </div>
                        
                        <div class="bg-purple-100 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold text-purple-800">Layout Integration</h4>
                            <p class="text-purple-600 mt-2">✅ Layout components working correctly!</p>
                            <div class="mt-3 space-x-2">
                                <span class="inline-block bg-purple-200 rounded-full px-3 py-1 text-sm font-semibold text-purple-800">Tag 1</span>
                                <span class="inline-block bg-purple-200 rounded-full px-3 py-1 text-sm font-semibold text-purple-800">Tag 2</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8">
                        <div class="bg-gradient-to-r from-blue-400 to-purple-500 p-6 rounded-lg shadow-lg text-white">
                            <h4 class="text-xl font-bold mb-2">Success!</h4>
                            <p class="mb-4">Laravel Breeze authentication with modern Tailwind UI is working perfectly.</p>
                            <div class="flex space-x-4">
                                <a href="{{ route('dashboard') }}" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg px-4 py-2 transition-all duration-200">
                                    Go to Dashboard
                                </a>
                                <a href="{{ route('profile.edit') }}" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg px-4 py-2 transition-all duration-200">
                                    Edit Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>