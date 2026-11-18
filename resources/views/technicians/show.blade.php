<x-app-layout :title="$technician->name">
    <x-slot name="header">{{ $technician->name }}</x-slot>
    <x-slot name="subtitle">Historique des sorties · {{ $technician->email }}</x-slot>
    <x-slot name="actions">
        <x-link-button variant="secondary" icon="arrow-left" :href="route('technicians.index')">Techniciens</x-link-button>
        <x-link-button icon="arrow-up" :href="route('stock.exits.create', ['technician' => $technician->id])">Nouvelle sortie</x-link-button>
    </x-slot>

    <x-card class="mb-6">
        <form method="GET" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5 lg:items-end"
              x-data @change.debounce="if ($event.target.id === 'switch_technician') window.location = $event.target.value">
            <div>
                <x-input-label for="switch_technician" value="Technicien" />
                <x-select-input id="switch_technician">
                    @foreach ($technicians as $t)
                        <option value="{{ route('technicians.show', $t) }}" @selected($t->id === $technician->id)>{{ $t->name }}</option>
                    @endforeach
                </x-select-input>
            </div>
            <div>
                <x-input-label for="product" value="Produit" />
                <x-select-input id="product" name="product">
                    <option value="">Tous</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected((int) ($filters['product'] ?? 0) === $product->id)>{{ $product->name }}</option>
                    @endforeach
                </x-select-input>
            </div>
            <div>
                <x-input-label for="date_from" value="Du" />
                <x-text-input id="date_from" name="date_from" type="date" :value="$filters['date_from'] ?? ''" />
            </div>
            <div>
                <x-input-label for="date_to" value="Au" />
                <x-text-input id="date_to" name="date_to" type="date" :value="$filters['date_to'] ?? ''" />
            </div>
            <div class="flex gap-2">
                <x-primary-button class="flex-1"><x-icon name="filter" class="h-4 w-4" /> Filtrer</x-primary-button>
                @if (array_filter($filters))
                    <x-link-button variant="secondary" :href="route('technicians.show', $technician)" title="Réinitialiser"><x-icon name="x" class="h-4 w-4" /></x-link-button>
                @endif
            </div>
            @if ($errors->any())
                <div class="sm:col-span-2 lg:col-span-5"><x-input-error :messages="$errors->all()" /></div>
            @endif
        </form>
        <p class="mt-3 text-sm text-slate-500">
            Période :
            <strong class="text-slate-700">
                @if (! empty($filters['date_from']) || ! empty($filters['date_to']))
                    {{ ! empty($filters['date_from']) ? \Illuminate\Support\Carbon::parse($filters['date_from'])->format('d/m/Y') : 'début' }}
                    → {{ ! empty($filters['date_to']) ? \Illuminate\Support\Carbon::parse($filters['date_to'])->format('d/m/Y') : 'aujourd\'hui' }}
                @else
                    tout l'historique
                @endif
            </strong>
        </p>
    </x-card>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-card title="Matériel reçu (cumul)" :padding="false" class="lg:col-span-1">
            @if ($summary->isEmpty())
                <x-empty-state icon="cube" message="Aucun matériel sur la période." />
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($summary as $row)
                        <li class="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                            <span class="min-w-0">
                                <span class="block truncate font-medium text-slate-900">{{ $row->product->name }}</span>
                                <span class="text-xs text-slate-500">{{ $row->operations }} sortie(s)</span>
                            </span>
                            <span class="whitespace-nowrap font-semibold tabular-nums text-orange-600">{{ $row->product->formatQuantity((int) $row->total) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>

        <x-card title="Détail des sorties" :padding="false" class="lg:col-span-2">
            @include('stock.exits._table', ['exits' => $exits, 'showTechnician' => false])
        </x-card>
    </div>
</x-app-layout>
