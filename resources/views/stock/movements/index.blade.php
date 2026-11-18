<x-app-layout title="Historique des mouvements">
    <x-slot name="header">Historique des mouvements</x-slot>
    <x-slot name="subtitle">Toutes les entrées et sorties de stock.</x-slot>

    <x-card :padding="false">
        <form method="GET" class="grid gap-3 border-b border-slate-200 p-4 sm:grid-cols-2 lg:grid-cols-6 lg:items-end">
            <div>
                <x-input-label for="type" value="Type" />
                <x-select-input id="type" name="type">
                    <option value="">Tous</option>
                    <option value="entry" @selected(($filters['type'] ?? '') === 'entry')>Entrées</option>
                    <option value="exit" @selected(($filters['type'] ?? '') === 'exit')>Sorties</option>
                </x-select-input>
            </div>
            <div>
                <x-input-label for="product_id" value="Produit" />
                <x-select-input id="product_id" name="product_id">
                    <option value="">Tous</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected((int) ($filters['product_id'] ?? 0) === $product->id)>{{ $product->name }}</option>
                    @endforeach
                </x-select-input>
            </div>
            <div>
                <x-input-label for="technician_id" value="Technicien" />
                <x-select-input id="technician_id" name="technician_id">
                    <option value="">Tous</option>
                    @foreach ($technicians as $technician)
                        <option value="{{ $technician->id }}" @selected((int) ($filters['technician_id'] ?? 0) === $technician->id)>{{ $technician->name }}</option>
                    @endforeach
                </x-select-input>
            </div>
            <div>
                <x-input-label for="date_from" value="Date début" />
                <x-text-input id="date_from" name="date_from" type="date" :value="$filters['date_from'] ?? ''" />
            </div>
            <div>
                <x-input-label for="date_to" value="Date fin" />
                <x-text-input id="date_to" name="date_to" type="date" :value="$filters['date_to'] ?? ''" />
            </div>
            <div class="flex gap-2">
                <x-primary-button class="flex-1"><x-icon name="filter" class="h-4 w-4" /> Filtrer</x-primary-button>
                @if ($filters)
                    <x-link-button variant="secondary" :href="route('stock.movements.index')" title="Réinitialiser"><x-icon name="x" class="h-4 w-4" /></x-link-button>
                @endif
            </div>
            @if ($errors->any())
                <div class="sm:col-span-2 lg:col-span-6">
                    <x-input-error :messages="$errors->all()" />
                </div>
            @endif
        </form>

        @if (! empty($filters['technician_id']) && ($filters['type'] ?? null) !== 'exit')
            <p class="border-b border-slate-200 bg-slate-50 px-4 py-2 text-xs text-slate-500">Filtre technicien actif : seules les sorties sont affichées.</p>
        @endif

        @include('stock.movements._table', ['movements' => $movements])
    </x-card>
</x-app-layout>
