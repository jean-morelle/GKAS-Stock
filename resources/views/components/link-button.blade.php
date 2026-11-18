@props(['variant' => 'primary', 'icon' => null])

@php
$classes = match ($variant) {
    'secondary' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50',
    default => 'bg-blue-600 text-white hover:bg-blue-500',
};
@endphp

<a {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 $classes"]) }}>
    @if ($icon)<x-icon :name="$icon" class="h-4 w-4" />@endif
    {{ $slot }}
</a>
