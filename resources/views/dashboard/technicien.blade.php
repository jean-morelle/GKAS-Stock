<x-app-layout title="Tableau de bord">
    <x-slot name="header">Bonjour {{ auth()->user()->name }}</x-slot>
    <x-slot name="subtitle">Votre activité et le matériel que vous avez pris dans le stock.</x-slot>

    <div class="grid gap-3 sm:grid-cols-3 sm:gap-4">
        <x-stat-card label="Sorties ce mois" :value="$stats['month']" icon="arrow-up" tone="orange" :href="route('my-exits.index', ['date_from' => now()->startOfMonth()->toDateString()])" />
        <x-stat-card label="Produits ce mois" :value="$stats['products']" icon="cube" tone="emerald" />
        <x-stat-card label="Sorties au total" :value="$stats['total']" icon="clock" tone="blue" :href="route('my-exits.index')" />
    </div>

    <x-card title="Mes dernières interventions" :padding="false" class="mt-6">
        <x-slot name="headerActions">
            <a href="{{ route('my-exits.index') }}" class="text-sm font-medium text-blue-600 hover:underline">Tout voir</a>
        </x-slot>

        @if ($interventions->isEmpty())
            <x-empty-state icon="clipboard" message="Vous n'avez encore rien pris dans le stock." />
        @else
            <ul class="divide-y divide-slate-100">
                @foreach ($interventions as $intervention)
                    <li class="px-5 py-4 text-sm">
                        <div class="flex flex-wrap items-baseline justify-between gap-x-3">
                            <p class="font-medium text-slate-900">
                                {{ $intervention->intervention ?: $intervention->reason }}
                                @if ($intervention->intervention)
                                    <span class="font-normal text-slate-500">&middot; {{ $intervention->reason }}</span>
                                @endif
                            </p>
                            <p class="text-xs text-slate-400">{{ $intervention->date->format('d/m/Y H:i') }}</p>
                        </div>
                        <ul class="mt-2 flex flex-wrap gap-1.5">
                            @foreach ($intervention->exits as $exit)
                                <li class="rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-700">
                                    {{ $exit->product->name }} <span class="font-semibold tabular-nums">{{ $exit->product->formatQuantity($exit->quantity) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-card>
</x-app-layout>
