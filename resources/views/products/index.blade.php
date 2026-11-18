@php($canManage = auth()->user()->isResponsable())

<x-app-layout :title="$canManage ? 'Produits' : 'Produits disponibles'">
    <x-slot name="header">{{ $canManage ? 'Produits' : 'Produits disponibles' }}</x-slot>
    <x-slot name="subtitle">{{ $canManage ? 'Catalogue du matériel et état du stock.' : 'Consultez les quantités disponibles en magasin.' }}</x-slot>
    @if ($canManage)
        <x-slot name="actions">
            <x-link-button :href="route('products.create')" icon="plus">Nouveau produit</x-link-button>
        </x-slot>
    @endif

    <x-card :padding="false">
        <form method="GET" class="grid gap-2 border-b border-slate-200 p-4 sm:grid-cols-[1fr_auto_auto_auto]">
            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <x-text-input name="search" :value="$filters['search']" placeholder="Nom ou référence…" class="pl-9" />
            </div>
            <x-select-input name="category" aria-label="Catégorie">
                <option value="">Toutes les catégories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($filters['category'] === $category->id)>{{ $category->name }}</option>
                @endforeach
            </x-select-input>
            <x-select-input name="status" aria-label="Statut">
                <option value="">Tous les statuts</option>
                @foreach (\App\Enums\StockStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected($filters['status'] === $status)>{{ $status->label() }}</option>
                @endforeach
            </x-select-input>
            <div class="flex gap-2">
                <x-secondary-button type="submit" class="flex-1"><x-icon name="filter" class="h-4 w-4" /> Filtrer</x-secondary-button>
                @if ($filters['search'] || $filters['category'] || $filters['status'])
                    <x-link-button variant="secondary" :href="route('products.index')" title="Réinitialiser"><x-icon name="x" class="h-4 w-4" /></x-link-button>
                @endif
            </div>
        </form>

        @if ($products->isEmpty())
            <x-empty-state message="Aucun produit ne correspond à votre recherche." />
        @else
            <x-table>
                <x-slot name="head">
                    <tr>
                        <th class="px-4 py-3">Nom</th>
                        <th class="px-4 py-3">Référence</th>
                        <th class="px-4 py-3">Catégorie</th>
                        <th class="px-4 py-3 text-right">Stock</th>
                        <th class="px-4 py-3">Unité</th>
                        <th class="px-4 py-3 text-right">Seuil</th>
                        <th class="px-4 py-3">Statut</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </x-slot>

                @foreach ($products as $product)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900">
                            @if ($canManage)
                                <a href="{{ route('products.show', $product) }}" class="hover:text-blue-600">{{ $product->name }}</a>
                            @else
                                {{ $product->name }}
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-slate-500">{{ $product->reference ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $product->category->name }}</td>
                        <td class="px-4 py-3 text-right font-semibold tabular-nums">{{ number_format($product->stock_quantity, 0, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $product->unit->value }}</td>
                        <td class="px-4 py-3 text-right tabular-nums text-slate-500">{{ number_format($product->alert_threshold, 0, ',', ' ') }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$product->stockStatus()" /></td>
                        @if ($canManage)
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <x-icon-link icon="eye" label="Voir" :href="route('products.show', $product)" />
                                <x-icon-link icon="pencil" label="Modifier" :href="route('products.edit', $product)" />
                                @unless ($product->stock_entries_exists || $product->stock_exits_exists)
                                    <x-delete-form :action="route('products.destroy', $product)" confirm="Supprimer le produit « {{ $product->name }} » ?" />
                                @endunless
                            </td>
                        @else
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                @if ($product->stock_quantity > 0)
                                    <x-icon-link icon="arrow-up" label="Prendre" :href="route('stock.exits.create', ['product' => $product->id])" />
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </x-table>

            @if ($products->hasPages())
                <div class="border-t border-slate-200 px-4 py-3">{{ $products->links() }}</div>
            @endif
        @endif
    </x-card>
</x-app-layout>
