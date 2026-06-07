@props([
    'title',
    'subtitle' => null,
])

<div class="mb-6">
    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">CUPCore</p>
    <h1 class="mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">{{ $title }}</h1>
    @if ($subtitle)
        <p class="mt-3 max-w-3xl text-sm leading-7 text-base-content/70">{{ $subtitle }}</p>
    @endif
</div>
