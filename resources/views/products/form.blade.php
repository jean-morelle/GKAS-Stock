@php($editing = $product->exists)

<x-app-layout :title="$editing ? 'Modifier le produit' : 'Nouveau produit'">
    <x-slot name="header">{{ $editing ? 'Modifier : '.$product->name : 'Nouveau produit' }}</x-slot>
    <x-slot name="actions">
        <x-link-button variant="secondary" icon="arrow-left" :href="$editing ? route('products.show', $product) : route('products.index')">Retour</x-link-button>
    </x-slot>

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ $editing ? route('products.update', $product) : route('products.store') }}" class="space-y-5">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-input-label for="name" value="Nom du produit" required />
                    <x-text-input id="name" name="name" :value="old('name', $product->name)" required maxlength="150" autofocus placeholder="Ex. Câble réseau CAT6" />
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label for="reference" value="Référence" />
                    <x-text-input id="reference" name="reference" :value="old('reference', $product->reference)" maxlength="50" placeholder="Ex. CAT6-305" />
                    <x-input-error :messages="$errors->get('reference')" />
                </div>

                <div>
                    <x-input-label for="category_id" value="Catégorie" required />
                    <x-select-input id="category_id" name="category_id" required>
                        <option value="">— Sélectionner —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </x-select-input>
                    <x-input-error :messages="$errors->get('category_id')" />
                </div>

                <div>
                    <x-input-label for="unit" value="Unité" required />
                    <x-select-input id="unit" name="unit" required>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->value }}" @selected(old('unit', $product->unit?->value) === $unit->value)>{{ ucfirst($unit->value) }}</option>
                        @endforeach
                    </x-select-input>
                    <x-input-error :messages="$errors->get('unit')" />
                </div>

                <div>
                    <x-input-label for="alert_threshold" value="Seuil d'alerte" required />
                    <x-text-input id="alert_threshold" name="alert_threshold" type="number" min="0" step="1" :value="old('alert_threshold', $product->alert_threshold)" required />
                    <p class="mt-1 text-xs text-slate-500">Le produit passe en « stock faible » lorsque le stock est inférieur ou égal à ce seuil.</p>
                    <x-input-error :messages="$errors->get('alert_threshold')" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="description" value="Description" />
                    <x-textarea-input id="description" name="description">{{ old('description', $product->description) }}</x-textarea-input>
                    <x-input-error :messages="$errors->get('description')" />
                </div>
            </div>

            <div class="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">
                @if ($editing)
                    Stock actuel : <strong>{{ $product->formatQuantity($product->stock_quantity) }}</strong>.
                    Le stock ne se modifie pas ici : utilisez une <a href="{{ route('stock.entries.create', ['product' => $product->id]) }}" class="font-medium text-blue-600 hover:underline">entrée</a>
                    ou une <a href="{{ route('stock.exits.create', ['product' => $product->id]) }}" class="font-medium text-blue-600 hover:underline">sortie</a>.
                @else
                    Le stock initial est de 0. Enregistrez ensuite une entrée de stock pour l'alimenter.
                @endif
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 pt-5">
                <x-link-button variant="secondary" :href="$editing ? route('products.show', $product) : route('products.index')">Annuler</x-link-button>
                <x-primary-button>{{ $editing ? 'Enregistrer' : 'Créer le produit' }}</x-primary-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
