<x-app-layout title="Entrées de stock">
    <x-slot name="header">Entrées de stock</x-slot>
    <x-slot name="subtitle">Réceptions de matériel et réapprovisionnements.</x-slot>
    <x-slot name="actions">
        <x-link-button :href="route('stock.entries.create')" icon="plus">Nouvelle entrée</x-link-button>
    </x-slot>

    <x-card :padding="false">
        <form method="GET" class="flex flex-col flex-wrap gap-2 border-b border-slate-200 p-4 sm:flex-row">
            <x-select-input name="product" aria-label="Produit" class="sm:max-w-xs">
                <option value="">Tous les produits</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected($filters['product'] === $product->id)>{{ $product->name }}</option>
                @endforeach
            </x-select-input>
            @include('stock._date-filters')
            <x-secondary-button type="submit"><x-icon name="filter" class="h-4 w-4" /> Filtrer</x-secondary-button>
            @if (array_filter($filters))
                <x-link-button variant="secondary" :href="route('stock.entries.index')" title="Réinitialiser"><x-icon name="x" class="h-4 w-4" /></x-link-button>
            @endif
        </form>

        @if ($entries->isEmpty())
            <x-empty-state icon="arrow-down" message="Aucune entrée enregistrée." />
        @else
            <x-table>
                <x-slot name="head">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Produit</th>
                        <th class="px-4 py-3 text-right">Quantité</th>
                        <th class="px-4 py-3">Motif</th>
                        <th class="px-4 py-3">Observation</th>
                        <th class="px-4 py-3">Enregistré par</th>
                    </tr>
                </x-slot>

                @foreach ($entries as $entry)
                    <tr class="hover:bg-slate-50">
                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                            {{ $entry->created_at->format('d/m/Y') }}
                            <span class="text-xs text-slate-400">{{ $entry->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-900">
                            <a href="{{ route('products.show', $entry->product) }}" class="hover:text-blue-600">{{ $entry->product->name }}</a>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right font-semibold tabular-nums text-emerald-600">+{{ $entry->product->formatQuantity($entry->quantity) }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $entry->reason ?: '—' }}</td>
                        <td class="max-w-xs truncate px-4 py-3 text-slate-500" title="{{ $entry->observation }}">{{ $entry->observation ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $entry->user->name }}</td>
                    </tr>
                @endforeach
            </x-table>

            @if ($entries->hasPages())
                <div class="border-t border-slate-200 px-4 py-3">{{ $entries->links() }}</div>
            @endif
        @endif
    </x-card>
</x-app-layout>
