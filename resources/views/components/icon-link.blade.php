@props(['icon', 'label'])

<a {{ $attributes->merge(['class' => 'inline-flex items-center rounded-md p-1.5 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600']) }} title="{{ $label }}">
    <x-icon :name="$icon" class="h-4 w-4" />
    <span class="sr-only">{{ $label }}</span>
</a>
