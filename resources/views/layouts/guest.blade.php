<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <!-- Brand Header -->
            <div class="text-center mb-8">
                <a href="/" class="inline-block">
                    <img src="{{ asset('images/foord-logo.png') }}" alt="Foord" class="h-14 w-auto mx-auto">
                </a>
                <h1 class="mt-5 text-lg font-medium tracking-widest text-navy-700">Unit Trusts</h1>
                {{-- <p class="text-gray-600 text-sm">Secure authentication portal</p> --}}
            </div>

            <!-- Auth Form Card -->
            <div class="w-full sm:max-w-md">
                <div class="bg-white border-t-4 border-naartjie-600 shadow-md rounded overflow-hidden">
                    <div class="px-8 py-8">
                        {{ $slot }}
                    </div>
                </div>
                
                <!-- Additional Links -->
                <div class="mt-6 text-center">
                    <a href="/" class="text-sm text-gray-600 hover:text-naartjie-600 transition-colors duration-200">
                        ← Back to homepage
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>
