<header class="sticky top-0 bg-white z-[51] print:hidden">
    <nav class="container max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex flex-wrap justify-between items-center">
        <a href="{{ env('APP_URL') }}/">
            <img class="h-12" src={{ asset('images/logo.png') }} alt="Logo" />
        </a>
        <button type="button" id="dropdownButton" class="lg:hidden">
            <svg class="fill-theme-gray" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24"
                height="24">
                <rect x="2" y="5" width="20" height="2" />
                <rect x="2" y="11" width="20" height="2" />
                <rect x="2" y="17" width="20" height="2" />
            </svg>
        </button>
        <ul class="flex flex-col lg:flex-row w-full lg:w-auto pt-4 lg:pt-0 gap-4 absolute lg:static top-full bg-gray-50 lg:bg-transparent border lg:border-0 z-10 left-0 p-8 lg:p-0 lg:flex"
            id="dropdownMenu">
            <li>
                <a class="font-normal text-lg transition ease-in-out text-theme-red duration-300"
                    href="{{ env('APP_URL') }}/availability">
                    Check Availability
                </a>
            </li>
            <li>
                <a class="font-normal text-theme-gray text-lg transition ease-in-out hover:text-theme-red duration-300"
                    href="{{ env('APP_URL') }}/packages">Prepaid
                    Packages</a>
            </li>
            <li>
                <a class="font-normal text-theme-gray text-lg transition ease-in-out hover:text-theme-red duration-300"
                    href="{{ env('APP_URL') }}/directions">Directions</a>
            </li>
            <li>
                <a class="font-normal text-theme-gray text-lg transition ease-in-out hover:text-theme-red duration-300"
                    href="{{ env('APP_URL') }}/faq">FAQ</a>
            </li>
            <li>
                <a class="font-normal text-theme-gray text-lg transition ease-in-out hover:text-theme-red duration-300"
                    href="{{ env('APP_URL') }}/login">Login</a>
            </li>
        </ul>
    </nav>
</header>
<script>
    // handle dropdown toggle
    const dropdownButton = document.getElementById('dropdownButton');
    const dropdownMenu = document.getElementById('dropdownMenu');
    const screenSize = window.matchMedia("(min-width: 640px)");

    if (screenSize.matches) {
        dropdownMenu.classList.toggle('hidden');
    }

    dropdownButton.addEventListener('click', () => {
        dropdownMenu.classList.toggle('hidden');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!dropdownButton.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.add('hidden');
        }
    });
</script>
