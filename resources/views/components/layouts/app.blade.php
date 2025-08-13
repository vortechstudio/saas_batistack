<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main>
        @isset($slot)
            {{ $slot }}
        @else
            @yield("content")
        @endisset
    </flux:main>
</x-layouts.app.sidebar>
