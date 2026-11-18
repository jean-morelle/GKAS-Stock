@props(['label', 'value', 'icon', 'tone' => 'blue', 'href' => null])

@php
$tones = [
    'blue' => 'bg-blue-50 text-blue-600',
    'amber' => 'bg-amber-50 text-amber-600',
    'red' => 'bg-red-50 text-red-600',
    'emerald' => 'bg-emerald-50 text-emerald-600',
    'orange' => 'bg-orange-50 text-orange-600',
];
$tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm'.($href ? ' transition hover:border-blue-300 hover:shadow' : '')]) }}>
    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg {{ $tones[$tone] }}">
        <x-icon :name="$icon" class="h-6 w-6" />
    </span>
    <span class="min-w-0">
        <span class="block truncate text-sm text-slate-500">{{ $label }}</span>
        <span class="block text-2xl font-semibold tabular-nums text-slate-900">{{ $value }}</span>
    </span>
</{{ $tag }}>
