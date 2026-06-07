@props([
    'type' => 'info',
    'message',
])

<div {{ $attributes->class(["alert alert-{$type}"]) }}>
    <span class="font-medium leading-7">{{ $message }}</span>
</div>
