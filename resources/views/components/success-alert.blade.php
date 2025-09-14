<div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 10000)" {{ $attributes->merge(['class' => 'p-4 bg-green-300/20 flex justify-between rounded border border-green-400 text-lg mb-8']) }}>
    <p class="font-light text-black">{{ $slot }}</p>
    <button @click="show = false" class="hover:text-theme-gray focus:outline-none focus:shadow-outline-white" aria-label="Close">
        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M14.35 14.35a1 1 0 0 1-1.41 0L10 11.42l-2.94 2.93a1 1 0 1 1-1.41-1.41L8.58 10 5.65 7.06a1 1 0 0 1 1.41-1.41L10 8.58l2.93-2.93a1 1 0 0 1 1.41 1.41L11.42 10l2.93 2.93a1 1 0 0 1 0 1.42z" clip-rule="evenodd"></path>
        </svg>
    </button>
</div>