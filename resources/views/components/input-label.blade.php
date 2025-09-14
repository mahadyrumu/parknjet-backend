@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-black font-normal tracking-tight']) }}>
    {{ $value ?? $slot }}
</label>