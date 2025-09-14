<footer class="bg-gray-50 border-t border-gray-100 print:hidden">
    <div class="container max-w-7xl mx-auto px-4 pt-10 pb-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 justify-items-between gap-8">
            <div class="">
                <h4 class="text-lg font-medium text-theme-gray mb-3"> Resources</h4>
                <ul>
                    <li>
                        <a href="{{ env('APP_URL') }}/faq"
                            class="text-md text-theme-gray hover:text-gray-900 py-1 inline-block">FAQ</a>
                    </li>
                    <li>
                        <a href="{{ env('APP_URL') }}/reservation"
                            class="text-md text-theme-gray hover:text-gray-900 py-1 inline-block">New
                            Reservation</a>
                    </li>
                    <li>
                        <a href="{{ env('APP_URL') }}/directions"
                            class="text-md text-theme-gray hover:text-gray-900 py-1 inline-block">Get
                            Direction</a>
                    </li>
                    <li>
                        <a href="{{ env('APP_URL') }}/pickup-request"
                            class="text-md text-theme-gray hover:text-gray-900 py-1 inline-block">Pickup Request</a>
                    </li>
                    <li>
                        <a href="{{ env('APP_URL') }}/reservation-finder"
                            class="text-md text-theme-gray hover:text-gray-900 py-1 inline-block">Search Reservation</a>
                    </li>
                </ul>
            </div>
            <div class="">
                <h4 class="text-lg font-medium text-theme-gray mb-3"> Company</h4>
                <ul>
                    <li>
                        <a href="{{ env('APP_URL') }}/about-us"
                            class="text-md text-theme-gray hover:text-gray-900 py-1 inline-block">About Us</a>
                    </li>
                    <li>
                        <a href="{{ env('APP_URL') }}/contact-us"
                            class="text-md text-theme-gray hover:text-gray-900 py-1 inline-block">Contact Us</a>
                    </li>
                    <li>
                        <a href="{{ env('APP_URL') }}/terms"
                            class="text-md text-theme-gray hover:text-gray-900 py-1 inline-block">Terms & Conditions</a>
                    </li>
                    <li>
                        <a href="{{ env('APP_URL') }}/privacy-policy"
                            class="text-md text-theme-gray hover:text-gray-900 py-1 inline-block">Privacy Policy</a>
                    </li>
                </ul>
            </div>
            <div class="">
                <h4 class="text-lg font-medium text-theme-gray mb-3"> More</h4>
                <ul>
                    <li>
                        <a href="{{ env('APP_URL') }}/packages"
                            class="text-md text-theme-gray hover:text-gray-900 py-1 inline-block">Prepaid
                            Package</a>
                    </li>

                    <li>
                        <a href="{{ env('APP_URL') }}/login"
                            class="text-md text-theme-gray hover:text-gray-900 py-1 inline-block">Login</a>
                    </li>
                    <li>
                        <a href="{{ env('APP_URL') }}/signup"
                            class="text-md text-theme-gray hover:text-gray-900 py-1 inline-block">Sign
                            Up</a>
                    </li>
                    <li>
                        <a href="{{ env('APP_URL') }}/careers"
                            class="text-md text-theme-gray hover:text-gray-900 py-1 inline-block">Careers</a>
                    </li>
                </ul>
            </div>
            <div class="">
                <address class="mb-2 not-italic">
                    <h4 class="text-lg font-medium text-theme-gray mb-2">
                        Park N Jet Lot 1 :
                    </h4>
                    <p class="text-theme-gray">
                        <a href="https://www.google.com/maps?daddr=18220+8th+Ave+S+SeaTac+WA+98148" target="_blank">
                            18220 8th Ave S SeaTac, WA 98148 </a> <br />
                        Hotline: <a href="tel:+(206) 241-6600">(206) 241-6600</a>
                    </p>
                </address>
                <address class="not-italic">
                    <h4 class="text-lg font-medium text-theme-gray mb-2">
                        Park N Jet Lot 2 :
                    </h4>
                    <p class="text-theme-gray">
                        <a href="https://www.google.com/maps?daddr=1244+S+140th+st+Burien+WA+98168" target="_blank">
                            1244 S 140th St Seattle, WA 98168 </a> <br />
                        Hotline: <a href="tel:+(206) 244-4500">(206) 244-4500</a>
                    </p>
                </address>
            </div>
            <div class="sm:col-span-2 lg:col-span-4 mt-3 pt-2 border-t border-gray-200">
                <p class="text-center text-theme-gray pt-2">
                    Park N Jet Seatac Airport Parking © {{ date('Y') }}
                </p>
            </div>
        </div>
    </div>
</footer>
