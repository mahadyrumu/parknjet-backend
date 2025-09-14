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

<body class="h-screen flex items-center justify-center font-sans text-gray-900 antialiased bg-gray-100">
    <div class="lg:w-full w-5/6 mx-auto sm:max-w-3xl px-8 py-8 my-8 bg-white shadow-md overflow-hidden rounded-lg">
        <div class="flex items-center justify-center mb-8"> <img src="{{ asset('images/logo.png') }}"
                alt="Park n Jet Logo" class="h-12"></div>

        @if ($user->isVerified == 1)
            <p class="text-center">Your email has been verified successfully. Please choose a link from below
                where you
                want to go.</p>
            <div class="mt-4 text-blue-600">
                <div class="flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0,0,256,256">
                        <g fill="#4c7deb" fill-rule="nonzero" stroke="none" stroke-width="1" stroke-linecap="butt"
                            stroke-linejoin="miter" stroke-miterlimit="10" stroke-dasharray="" stroke-dashoffset="0"
                            font-family="none" font-weight="none" font-size="none" text-anchor="none"
                            style="mix-blend-mode: normal">
                            <g transform="scale(10.66667,10.66667)">
                                <path d="M19,11v9h-5v-6h-4v6h-5v-9h-1.4l8.4,-7.6l8.4,7.6z" opacity="0.3"></path>
                                <path
                                    d="M20,21h-7v-6h-2v6h-7v-9h-3l11,-9.9l11,9.9h-3zM15,19h3v-8.8l-6,-5.4l-6,5.4v8.8h3v-6h6z">
                                </path>
                            </g>
                        </g>
                    </svg>
                    <a href="{{ env('APP_URL') }}/dashboard" class="mt-1 ml-2">Dashboard</a>
                </div>
                <div class="flex items-center justify-center mt-2">
                    <svg class="w-5 fill-current stroke-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path
                            d="M16 10c0 .55-.05 1-.6 1H11v4.4c0 .55-.45.6-1 .6s-1-.05-1-.6V11H4.6c-.55 0-.6-.45-.6-1s.05-1 .6-1H9V4.6c0-.55.45-.6 1-.6s1 .05 1 .6V9h4.4c.55 0 .6.45.6 1z" />
                    </svg>
                    <a href="{{ env('APP_URL') }}/reservation" class="ml-2">New Reservation</a>
                </div>
            </div>
        @else
            <div class="text-center">
                <p>Sorry, your verifcation link is expired. Click here to verify again.</p>
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <div class="mt-4">
                        <x-primary-button class="w-full text-base font-semibold shadow-md rounded-lg">
                            {{ __('Resend Verification Email') }}
                        </x-primary-button>
                    </div>
                </form>
                @if (session('status') == 'verification-link-sent')
                    <div class="mt-4 font-medium text-sm text-black">
                        {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</body>

</html>
