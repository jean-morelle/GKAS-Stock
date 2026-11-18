@props(['action', 'confirm' => 'Confirmer la suppression ?'])

<form method="POST" action="{{ $action }}" onsubmit="return confirm(@js($confirm))" class="inline">
    @csrf
    @method('DELETE')
    <button type="submit" {{ $attributes->merge(['class' => 'inline-flex items-center rounded-md p-1.5 text-slate-400 transition hover:bg-red-50 hover:text-red-600']) }} title="Supprimer">
        <x-icon name="trash" class="h-4 w-4" />
        <span class="sr-only">Supprimer</span>
    </button>
</form>
