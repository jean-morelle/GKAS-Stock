<x-app-layout title="Alertes de stock">
    <x-slot name="header">Alertes de stock</x-slot>
    <x-slot name="subtitle">Produits en rupture ou dont le stock est inférieur ou égal au seuil d'alerte.</x-slot>

    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <x-stat-card label="Produits en rupture" :value="$outOfStock->count()" icon="x-circle" tone="red" />
        <x-stat-card label="Produits en stock faible" :value="$lowStock->count()" icon="exclamation" tone="amber" />
    </div>

    <div class="space-y-6">
        <x-card title="Ruptures de stock" :padding="false">
            @if ($outOfStock->isEmpty())
                <x-empty-state icon="check-circle" message="Aucun produit en rupture." />
            @else
                @include('stock.alerts._table', ['products' => $outOfStock])
            @endif
        </x-card>

        <x-card title="Stock faible" :padding="false">
            @if ($lowStock->isEmpty())
                <x-empty-state icon="check-circle" message="Aucun produit en stock faible." />
            @else
                @include('stock.alerts._table', ['products' => $lowStock])
            @endif
        </x-card>
    </div>
</x-app-layout>
