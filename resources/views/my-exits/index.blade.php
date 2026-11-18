<x-app-layout title="Mes sorties">
    <x-slot name="header">Mes sorties</x-slot>
    <x-slot name="subtitle">Matériel que vous avez pris dans le stock.</x-slot>
    <x-slot name="actions">
        <x-link-button icon="arrow-up" :href="route('stock.exits.create')">Nouvelle sortie</x-link-button>
    </x-slot>

    <x-card :padding="false" class="mb-6">
        <form method="GET" class="flex flex-col flex-wrap gap-2 p-4 sm:flex-row">
            <div class="relative sm:max-w-xs sm:flex-1">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <x-text-input type="search" name="search" :value="$filters['search']" class="pl-9" placeholder="Client, intervention, motif…" aria-label="Rechercher" />
            </div>
            <x-select-input name="product" aria-label="Produit" class="sm:max-w-xs">
                <option value="">Tous les produits</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected($filters['product'] === $product->id)>{{ $product->name }}</option>
                @endforeach
            </x-select-input>
            @include('stock._date-filters')
            <x-secondary-button type="submit"><x-icon name="filter" class="h-4 w-4" /> Filtrer</x-secondary-button>
            @if (array_filter($filters))
                <x-link-button variant="secondary" :href="route('my-exits.index')" title="Réinitialiser"><x-icon name="x" class="h-4 w-4" /></x-link-button>
            @endif
        </form>
    </x-card>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-card title="Matériel pris" :padding="false" class="lg:col-span-1">
            @if ($assigned->isEmpty())
                <x-empty-state icon="cube" :message="array_filter($filters) ? 'Aucune sortie pour ces filtres.' : 'Vous n\'avez encore rien pris dans le stock.'" />
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($assigned as $row)
                        <li class="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                            <span class="min-w-0">
                                <span class="block truncate font-medium text-slate-900">{{ $row->product->name }}</span>
                                <span class="text-xs text-slate-500">Dernière sortie : {{ \Illuminate\Support\Carbon::parse($row->last_at)->format('d/m/Y') }}</span>
                            </span>
                            <span class="whitespace-nowrap font-semibold tabular-nums text-slate-900">{{ $row->product->formatQuantity((int) $row->total) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>

        <x-card title="Historique" :padding="false" class="lg:col-span-2">
            @include('stock.exits._table', ['exits' => $exits, 'showTechnician' => false])
        </x-card>
    </div>
</x-app-layout>
