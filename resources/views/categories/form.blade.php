@php($editing = $category->exists)

<x-app-layout :title="$editing ? 'Modifier la catégorie' : 'Nouvelle catégorie'">
    <x-slot name="header">{{ $editing ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}</x-slot>
    <x-slot name="actions">
        <x-link-button variant="secondary" icon="arrow-left" :href="route('categories.index')">Retour</x-link-button>
    </x-slot>

    <x-card class="max-w-2xl">
        <form method="POST" action="{{ $editing ? route('categories.update', $category) : route('categories.store') }}" class="space-y-5">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div>
                <x-input-label for="name" value="Nom" required />
                <x-text-input id="name" name="name" :value="old('name', $category->name)" required maxlength="100" autofocus />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="description" value="Description" />
                <x-textarea-input id="description" name="description">{{ old('description', $category->description) }}</x-textarea-input>
                <x-input-error :messages="$errors->get('description')" />
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 pt-5">
                <x-link-button variant="secondary" :href="route('categories.index')">Annuler</x-link-button>
                <x-primary-button>{{ $editing ? 'Enregistrer' : 'Créer la catégorie' }}</x-primary-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
