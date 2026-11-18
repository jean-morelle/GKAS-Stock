<x-app-layout title="Sorties de stock">
    <x-slot name="header">Sorties de stock</x-slot>
    <x-slot name="subtitle">Matériel remis aux techniciens.</x-slot>
    <x-slot name="actions">
        <x-link-button :href="route('stock.exits.create')" icon="plus">Nouvelle sortie</x-link-button>
    </x-slot>

    <x-card :padding="false">
        <form method="GET" class="flex flex-col flex-wrap gap-2 border-b border-slate-200 p-4 sm:flex-row">
            <x-select-input name="technician" aria-label="Technicien" class="sm:max-w-xs">
                <option value="">Tous les techniciens</option>
                @foreach ($technicians as $technician)
                    <option value="{{ $technician->id }}" @selected($filters['technician'] === $technician->id)>{{ $technician->name }}</option>
                @endforeach
            </x-select-input>
            <x-select-input name="product" aria-label="Produit" class="sm:max-w-xs">
                <option value="">Tous les produits</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected($filters['product'] === $product->id)>{{ $product->name }}</option>
                @endforeach
            </x-select-input>
            @include('stock._date-filters')
            <x-secondary-button type="submit"><x-icon name="filter" class="h-4 w-4" /> Filtrer</x-secondary-button>
            @if (array_filter($filters))
                <x-link-button variant="secondary" :href="route('stock.exits.index')" title="Réinitialiser"><x-icon name="x" class="h-4 w-4" /></x-link-button>
            @endif
        </form>

        @include('stock.exits._table', ['exits' => $exits, 'showTechnician' => true])
    </x-card>
</x-app-layout>
