<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-blue-50 via-white to-indigo-50">
            <!-- Brand Header -->
            <div class="text-center mb-8">
                <a href="/" class="inline-block">
                    <div class="bg-white rounded-full p-4 shadow-lg border border-gray-100">
                        <img src="https://foord.co.za/themes/custom/mirum/logo.png" width="200" height="200" />
                    </div>
                </a>
                <h1 class="mt-4 text-2xl font-bold text-gray-900">Foord Unit Trusts</h1>
                {{-- <p class="text-gray-600 text-sm">Secure authentication portal</p> --}}
            </div>

            <!-- Auth Form Card -->
            <div class="w-full sm:max-w-md">
                <div class="bg-white/80 backdrop-blur-sm border border-gray-200 shadow-xl rounded-2xl overflow-hidden">
                    <div class="px-8 py-8">
                        {{ $slot }}
                    </div>
                </div>
                
                <!-- Additional Links -->
                <div class="mt-6 text-center">
                    <a href="/" class="text-sm text-gray-600 hover:text-indigo-600 transition-colors duration-200">
                        ← Back to homepage
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>
