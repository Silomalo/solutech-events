<div class="h-screen">
    @php
    $has_category = auth()->user()->user_system_category ?? null;
    @endphp
    @if ($has_category)
    <x-layouts.app.sidebar :title="$title ?? null">
        <flux:main>
            {{ $slot }}
        </flux:main>
    </x-layouts.app.sidebar>
    @else
    <x-layouts.app.header :title="$title ?? null">
        <flux:main>
            {{ $slot }}
        </flux:main>
    </x-layouts.app.header>

    @endif
</div>
