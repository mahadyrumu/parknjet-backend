<button type="button" {{ $attributes->merge(['class' => 'p-2.5 mb-1 rounded-md text-white text-base font-normal bg-theme-red inline-flex gap-2.5 justify-center items-center']) }}>
    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M9.99996 15.8333V4.16666M15.8333 10H4.16663" stroke="white" stroke-width="2" stroke-linecap="round"/>
        </svg>
    <span>{{ $slot }}</span>
</button>
