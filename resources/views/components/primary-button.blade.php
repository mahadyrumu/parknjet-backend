<button
    {{ $attributes->merge(['type' => 'submit', 'class' => 'p-2.5 bg-red-600 rounded-md border border-red-600 text-white text-base font-normal tracking-tight']) }}>
    {{ $slot }}
</button>