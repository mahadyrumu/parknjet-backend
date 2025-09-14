<nav {{ $attributes->merge(['class' => '']) }} aria-label="Breadcrumb">
    <ol role="list" class="flex gap-2 flex-wrap">
        <li>
            <a href="{{ route('dashboard.index') }}" class="text-black hover:underline text-xl">Dashboard</a>
        </li>
        {{ isset($content) ? $content : "" }}
    </ol>
    {{ isset($button) ? $button : "" }}
</nav>
