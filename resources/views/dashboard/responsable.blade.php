@php
    // Filtre « date début » transmis aux listes détaillées.
    $dateFrom = $since->toDateString();
@endphp

<x-app-layout title="Tableau de bord">
    <x-slot name="header">Tableau de bord</x-slot>
    <x-slot name="actions">
        <form method="GET" action="{{ route('dashboard') }}">
            <x-select-input name="period" aria-label="Période" onchange="this.form.submit()">
                @foreach ($periods as $key => $label)
                    <option value="{{ $key }}" @selected($key === $period)>{{ $label }}</option>
                @endforeach
            </x-select-input>
            <noscript><x-secondary-button type="submit" class="mt-2">Afficher</x-secondary-button></noscript>
        </form>
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Entrées" :value="$stats['entries']" icon="arrow-down" tone="emerald" :href="route('stock.entries.index', ['date_from' => $dateFrom])" />
        <x-stat-card label="Sorties" :value="$stats['exits']" icon="arrow-up" tone="orange" :href="route('stock.exits.index', ['date_from' => $dateFrom])" />
        <x-stat-card label="Stock faible" :value="$stats['low']" icon="exclamation" tone="amber" :href="route('stock.alerts.index')" />
        <x-stat-card label="Ruptures" :value="$stats['out']" icon="x-circle" tone="red" :href="route('products.index', ['status' => 'rupture'])" />
    </div>

    {{-- Matériel pris par chaque technicien sur la période --}}
    <x-card title="Matériel pris par technicien" :padding="false" class="mt-6">
        <x-slot name="headerActions">
            <a href="{{ route('stock.movements.index', ['date_from' => $dateFrom]) }}" class="text-sm font-medium text-blue-600 hover:underline">Historique</a>
        </x-slot>

        @if ($technicians->isEmpty())
            <x-empty-state icon="users" message="Aucun technicien enregistré." />
        @else
            <x-table>
                <x-slot name="head">
                    <tr>
                        <th class="px-4 py-3">Technicien</th>
                        <th class="px-4 py-3">Matériel pris</th>
                        <th class="px-4 py-3">Dernière sortie</th>
                    </tr>
                </x-slot>
                @foreach ($technicians as $technician)
                    <tr class="align-top hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium">
                            <a href="{{ route('technicians.show', ['technician' => $technician->id, 'date_from' => $dateFrom]) }}" class="text-slate-900 hover:text-blue-600">{{ $technician->name }}</a>
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            @forelse ($technician->products->take(4) as $line)
                                <span class="block">{{ $line->product->name }} <span class="font-semibold tabular-nums">{{ $line->product->formatQuantity((int) $line->total) }}</span></span>
                            @empty
                                <span class="text-slate-400">Aucune sortie</span>
                            @endforelse
                            @if ($technician->products->count() > 4)
                                <a href="{{ route('technicians.show', ['technician' => $technician->id, 'date_from' => $dateFrom]) }}" class="text-xs font-medium text-blue-600 hover:underline">
                                    + {{ $technician->products->count() - 4 }} autre(s) produit(s)
                                </a>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $technician->last_exit_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    </tr>
                @endforeach
            </x-table>
        @endif
    </x-card>
</x-app-layout>
