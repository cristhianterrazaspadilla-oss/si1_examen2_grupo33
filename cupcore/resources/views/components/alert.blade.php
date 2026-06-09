@props([
    'type' => 'info',
    'message',
])

@php
    $type = strtolower($type);

    $styles = [
        'success' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200',
        'error' => 'border-rose-500/30 bg-rose-500/10 text-rose-200',
        'danger' => 'border-rose-500/30 bg-rose-500/10 text-rose-200',
        'warning' => 'border-amber-500/30 bg-amber-500/10 text-amber-200',
        'info' => 'border-cyan-500/30 bg-cyan-500/10 text-cyan-200',
    ][$type] ?? 'border-blue-500/30 bg-blue-500/10 text-blue-200';
@endphp

<div {{ $attributes->class(["flex items-start gap-3 rounded-2xl border p-4 backdrop-blur-md shadow-lg {$styles}"]) }} role="alert">
    @if($type === 'success')
        <svg class="h-5 w-5 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
        </svg>
    @elseif($type === 'error' || $type === 'danger')
        <svg class="h-5 w-5 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
        </svg>
    @elseif($type === 'warning')
        <svg class="h-5 w-5 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.515 2.625H3.72c-1.345 0-2.188-1.458-1.515-2.625l6.28-10.875zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
        </svg>
    @else
        <svg class="h-5 w-5 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
        </svg>
    @endif
    <span class="text-sm font-medium leading-relaxed break-words min-w-0 flex-1">{{ $message }}</span>
</div>
