<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" href="{{ asset('favicon-32x32.png') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    <link rel="stylesheet" href="{{ asset('css/app.min.css') }}">
    <script src="{{ asset('js/app.min.js') }}"></script>
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
</head>

<body class="font-sans text-gray-900 antialiased">
    @include('includes.header')
    <div class="min-h-screen bg-gray-100">
        <div class="pt-8 pb-8">
            <p class="text-center font-bold text-theme-gray text-xl">Being a member to our website
                will give you
                best offers of
                Seatac
                airport parking! </p>
            <p class="text-center font-bold text-theme-gray text-xl">It is highly convenient,
                you can place your reservation in seconds and guarantee your space!</p>
        </div>
        <div class="flex flex-col sm:justify-center items-center sm:pt-0 px-8">
            <div>
                @if (request()->routeIs('login'))
                    <h1 class="mt-3 text-center text-4xl font-extrabold text-gray-700">Login</h1>
                @elseif(request()->routeIs('register'))
                    <h1 class="mt-3 text-center text-4xl font-extrabold text-gray-700">Sign Up</h1>
                @endif
            </div>
            <div class="w-full mx-auto sm:max-w-3xl px-12 py-8 my-8 bg-white shadow-md overflow-hidden rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </div>
    @include('includes.footer')
</body>

</html>
