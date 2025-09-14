@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'focus:border-purple-500 focus:ring-purple-500 p-2.5 bg-neutral-100 rounded border border-black border-opacity-10 text-black font-light tracking-tight placeholder:text-slate-600 placeholder:font-light']) !!}>