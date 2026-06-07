@props([
    'title' => null,
])

<div {{ $attributes->class('card border border-blue-300/12 bg-white/6 shadow-[0_25px_80px_rgba(2,6,23,0.55)] backdrop-blur-2xl') }}>
    <div class="card-body gap-5 p-5 sm:p-6 lg:p-8">
        @if ($title)
            <h2 class="card-title text-xl font-semibold tracking-tight text-white">{{ $title }}</h2>
        @endif
        {{ $slot }}
    </div>
</div>
