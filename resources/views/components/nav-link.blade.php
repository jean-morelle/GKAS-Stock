@props(['active' => false, 'icon'])

{{-- Lien de la barre latérale (fond bleu nuit dans les deux thèmes). --}}
@php
$classes = $active
    ? 'bg-slate-800 text-white'
    : 'text-slate-300 hover:bg-slate-800/60 hover:text-white';
@endphp

<a {{ $attributes->merge(['class' => "group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition $classes"]) }}
   @if ($active) aria-current="page" @endif>
    <x-icon :name="$icon" @class(['h-5 w-5', 'text-blue-400' => $active, 'text-slate-400 group-hover:text-slate-200' => ! $active]) />
    <span>{{ $slot }}</span>
</a>
