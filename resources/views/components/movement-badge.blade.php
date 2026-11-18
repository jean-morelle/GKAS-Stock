@props(['type'])

@if ($type === 'entry')
    <span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
        <x-icon name="arrow-down" class="h-3.5 w-3.5" /> Entrée
    </span>
@else
    <span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full bg-orange-50 px-2 py-0.5 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/20">
        <x-icon name="arrow-up" class="h-3.5 w-3.5" /> Sortie
    </span>
@endif
