<x-app-layout title="Nouvelle entrée">
    <x-slot name="header">Nouvelle entrée de stock</x-slot>
    <x-slot name="subtitle">Le stock du produit sera augmenté de la quantité saisie.</x-slot>
    <x-slot name="actions">
        <x-link-button variant="secondary" icon="arrow-left" :href="route('stock.entries.index')">Retour</x-link-button>
    </x-slot>

    @php
        $stocks = $products->mapWithKeys(fn ($p) => [$p->id => ['stock' => $p->formatQuantity($p->stock_quantity), 'unit' => $p->unit->short()]]);
    @endphp

    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('stock.entries.store') }}" class="space-y-5"
              x-data="{ productId: @js((string) old('product_id', $selectedProduct ?? '')), stocks: @js($stocks) }">
            @csrf

            <div>
                <x-input-label for="product_id" value="Produit" required />
                <x-select-input id="product_id" name="product_id" x-model="productId" required>
                    <option value="">— Sélectionner un produit —</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}{{ $product->reference ? ' ('.$product->reference.')' : '' }}</option>
                    @endforeach
                </x-select-input>
                <x-input-error :messages="$errors->get('product_id')" />
                <p x-show="stocks[productId]" x-cloak class="mt-2 text-sm text-slate-600">
                    Stock actuel : <strong x-text="stocks[productId]?.stock"></strong>
                </p>
            </div>

            <div>
                <x-input-label for="quantity" value="Quantité" required />
                <div class="relative">
                    <x-text-input id="quantity" name="quantity" type="number" min="1" step="1" :value="old('quantity')" required class="pr-16" />
                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-slate-400" x-text="stocks[productId]?.unit"></span>
                </div>
                <x-input-error :messages="$errors->get('quantity')" />
            </div>

            <div>
                <x-input-label for="reason" value="Motif" />
                <x-text-input id="reason" name="reason" :value="old('reason')" maxlength="255" placeholder="Ex. Réapprovisionnement fournisseur" />
                <x-input-error :messages="$errors->get('reason')" />
            </div>

            <div>
                <x-input-label for="observation" value="Observation" />
                <x-textarea-input id="observation" name="observation">{{ old('observation') }}</x-textarea-input>
                <x-input-error :messages="$errors->get('observation')" />
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 pt-5">
                <x-link-button variant="secondary" :href="route('stock.entries.index')">Annuler</x-link-button>
                <x-primary-button><x-icon name="arrow-down" class="h-4 w-4" /> Enregistrer l'entrée</x-primary-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
