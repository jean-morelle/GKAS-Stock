<x-app-layout :title="$product->name">
    <x-slot name="header">{{ $product->name }}</x-slot>
    <x-slot name="subtitle">{{ $product->category->name }}@if ($product->reference) · Réf. {{ $product->reference }}@endif</x-slot>
    <x-slot name="actions">
        <x-link-button variant="secondary" icon="arrow-left" :href="route('products.index')">Produits</x-link-button>
        <x-link-button variant="secondary" icon="pencil" :href="route('products.edit', $product)">Modifier</x-link-button>
        <x-link-button variant="secondary" icon="arrow-down" :href="route('stock.entries.create', ['product' => $product->id])">Entrée</x-link-button>
        <x-link-button icon="arrow-up" :href="route('stock.exits.create', ['product' => $product->id])">Sortie</x-link-button>
    </x-slot>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Stock actuel</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $product->formatQuantity($product->stock_quantity) }}</p>
            <x-status-badge :status="$product->stockStatus()" class="mt-2" />
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Seuil d'alerte</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums">{{ $product->formatQuantity($product->alert_threshold) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total entré</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums text-emerald-600">+{{ $product->formatQuantity($totalIn) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total sorti</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums text-orange-600">−{{ $product->formatQuantity($totalOut) }}</p>
        </div>
    </div>

    @if ($product->description)
        <x-card title="Description" class="mb-6">
            <p class="whitespace-pre-line text-sm text-slate-600">{{ $product->description }}</p>
        </x-card>
    @endif

    <x-card title="Historique des mouvements" :padding="false">
        @include('stock.movements._table', ['movements' => $movements, 'showProduct' => false])
    </x-card>
</x-app-layout>
