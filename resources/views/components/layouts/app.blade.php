<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    <link rel="icon" href="{{ asset('favicon-32x32.png') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap"
        rel="stylesheet">

    <!-- Scripts -->
    <link rel="stylesheet" href="{{ asset('css/app.min.css') }}">
    <script src="{{ asset('js/app.min.js') }}"></script>
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
    @stack('style')
    @livewireStyles

    <!-- for date picker -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.30.1/moment.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
</head>

<body>
    <livewire:header />

    {{-- Common Loader --}}
    <div wire:loading.flex
        class="hidden w-screen h-[calc(100vh-70px)] bottom-0 left-0 fixed backdrop-blur-lg bg-white/30 justify-center z-50">
        <div class="flex items-center justify-center h-screen">
            <x-loading-icon />
        </div>
    </div>

    @yield('content') @if (isset($slot))
        {{ $slot }}
    @endif

    @include('includes.footer')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // This function needs to remove once I convert the full dashboard in livewire
        function alertDelete(url) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d01f30",
                cancelButtonColor: "#222325",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = url;
                }
            });
        }
        // Delete livewere records
        function deleteRecord(delMethold, id) {
            Swal.fire({
                title: "Are you sure?",
                text: "Are you sure to delete this record?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d01f30",
                cancelButtonColor: "#222325",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.livewire.emit(delMethold, id);
                }
            });
        }
    </script>
    @stack('after-script')
</body>

</html>
