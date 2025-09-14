<x-guest-layout>
    <form method="POST" action="{{ route('register') }}"
        class="flex flex-wrap items-center justify-center lg:justify-between lg:items-baseline">
        <div class="w-full md:w-8/12 lg:w-1/2 lg:pr-4">
            @csrf
            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Full Name')" />
                <x-text-input id="name" class="block mt-1 w-full bg-transparent" type="text" name="name"
                    :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div class="mt-4">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full bg-transparent" type="email" name="email"
                    :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full bg-transparent" type="password" name="password"
                    required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full bg-transparent" type="password"
                    name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <!-- Phone Number -->
            <div x-data class="mt-4">
                <x-input-label for="phone" :value="__('Phone Number (Optional)')" />
                <x-text-input id="phone" x-mask="(999) 999-9999" class="block mt-1 w-full bg-transparent"
                    type="text" name="phone" :value="old('phone')" autofocus autocomplete="phone" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
        </div>
        <div class="w-full mb-12 md:w-8/12 lg:w-1/2 lg:pl-4">

            <!-- Coupon Code -->
            <div class="mt-4">
                <x-input-label for="coupon_code" :value="__('Coupon Code (Optional)')" />
                <x-text-input id="coupon_code" class="block mt-1 w-full bg-transparent" type="text"
                    name="coupon_code" :value="old('coupon_code')" autofocus autocomplete="coupon_code" />
                <x-input-error :messages="$errors->get('coupon_code')" class="mt-2" />
            </div>

            <div class="flex items-center justify-center mt-8">
                <x-primary-button class="w-full text-center text-base font-semibold shadow-md rounded-lg">
                    {{ __('Sign Up') }}
                </x-primary-button>
            </div>

            <div class="flex items-center justify-end mt-6">
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>
            </div>

            <div class="flex items-center my-3 ">
                <hr class="flex-grow border-t border-gray-400">
                <span class="px-3 text-gray-600">
                    or
                </span>
                <hr class="flex-grow border-t border-gray-400">
            </div>

            <a href="{{ route('oauth.redirect', 'google') }}"
                class="w-full flex justify-center items-center bg-green-700 hover:bg-green-800 border-green-800 p-2.5 border text-white font-semibold text-center text-base shadow-md rounded-lg">
                <svg class="mr-2 -ml-1 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="2443" height="2500"
                    preserveAspectRatio="xMidYMid" viewBox="0 0 256 262" id="google">
                    <path fill="#4285F4"
                        d="M255.878 133.451c0-10.734-.871-18.567-2.756-26.69H130.55v48.448h71.947c-1.45 12.04-9.283 30.172-26.69 42.356l-.244 1.622 38.755 30.023 2.685.268c24.659-22.774 38.875-56.282 38.875-96.027">
                    </path>
                    <path fill="#34A853"
                        d="M130.55 261.1c35.248 0 64.839-11.605 86.453-31.622l-41.196-31.913c-11.024 7.688-25.82 13.055-45.257 13.055-34.523 0-63.824-22.773-74.269-54.25l-1.531.13-40.298 31.187-.527 1.465C35.393 231.798 79.49 261.1 130.55 261.1">
                    </path>
                    <path fill="#FBBC05"
                        d="M56.281 156.37c-2.756-8.123-4.351-16.827-4.351-25.82 0-8.994 1.595-17.697 4.206-25.82l-.073-1.73L15.26 71.312l-1.335.635C5.077 89.644 0 109.517 0 130.55s5.077 40.905 13.925 58.602l42.356-32.782">
                    </path>
                    <path fill="#EB4335"
                        d="M130.55 50.479c24.514 0 41.05 10.589 50.479 19.438l36.844-35.974C195.245 12.91 165.798 0 130.55 0 79.49 0 35.393 29.301 13.925 71.947l42.211 32.783c10.59-31.477 39.891-54.251 74.414-54.251">
                    </path>
                </svg>
                Continue with Google
            </a>
            <div class="mt-3">@signInWithApple('Black', true, 'Continue', 10)</div>
            <a href="{{ route('oauth.redirect', 'facebook') }}"
                class="w-full flex justify-center items-center bg-blue-600 hover:bg-blue-700 mt-3 border-blue-700 p-2.5 border text-white font-semibold text-center text-base shadow-md rounded-lg">
                <svg class="mr-2 -ml-1 w-5 h-5" xmlns="http://www.w3.org/2000/svg"
                    class="icon icon-tabler icon-tabler-brand-meta" width="24" height="24" viewBox="0 0 24 24"
                    stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path
                        d="M12 10.174c1.766 -2.784 3.315 -4.174 4.648 -4.174c2 0 3.263 2.213 4 5.217c.704 2.869 .5 6.783 -2 6.783c-1.114 0 -2.648 -1.565 -4.148 -3.652a27.627 27.627 0 0 1 -2.5 -4.174z" />
                    <path
                        d="M12 10.174c-1.766 -2.784 -3.315 -4.174 -4.648 -4.174c-2 0 -3.263 2.213 -4 5.217c-.704 2.869 -.5 6.783 2 6.783c1.114 0 2.648 -1.565 4.148 -3.652c1 -1.391 1.833 -2.783 2.5 -4.174z" />
                </svg>
                Continue with Meta
            </a>
        </div>
    </form>
</x-guest-layout>
