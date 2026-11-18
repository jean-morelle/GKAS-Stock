@props(['icon' => 'cube', 'message' => 'Aucun élément à afficher.'])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-12 text-center']) }}>
    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
        <x-icon :name="$icon" class="h-6 w-6" />
    </span>
    <p class="mt-3 text-sm text-slate-500">{{ $message }}</p>
    {{ $slot }}
</div>
