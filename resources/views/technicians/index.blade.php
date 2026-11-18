<x-app-layout title="Techniciens">
    <x-slot name="header">Techniciens</x-slot>
    <x-slot name="subtitle">Comptes des techniciens et matériel qui leur a été remis.</x-slot>
    <x-slot name="actions">
        <x-link-button :href="route('technicians.create')" icon="plus">Nouveau technicien</x-link-button>
    </x-slot>

    <x-card :padding="false">
        <form method="GET" class="flex gap-2 border-b border-slate-200 p-4">
            <div class="relative flex-1 sm:max-w-sm">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <x-text-input name="search" :value="$search" placeholder="Nom ou e-mail…" class="pl-9" />
            </div>
            <x-secondary-button type="submit">Rechercher</x-secondary-button>
        </form>

        @if ($technicians->isEmpty())
            <x-empty-state icon="users" message="Aucun technicien trouvé." />
        @else
            <x-table>
                <x-slot name="head">
                    <tr>
                        <th class="px-4 py-3">Nom</th>
                        <th class="px-4 py-3">E-mail</th>
                        <th class="px-4 py-3 text-right">Sorties</th>
                        <th class="px-4 py-3">Dernière sortie</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </x-slot>

                @foreach ($technicians as $technician)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('technicians.show', $technician) }}" class="flex items-center gap-3 font-medium text-slate-900 hover:text-blue-600">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600">
                                    {{ mb_strtoupper(mb_substr($technician->name, 0, 1)) }}
                                </span>
                                {{ $technician->name }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $technician->email }}</td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ $technician->stock_exits_count }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-500">
                            {{ $technician->stock_exits_max_created_at ? \Illuminate\Support\Carbon::parse($technician->stock_exits_max_created_at)->format('d/m/Y') : '—' }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <x-icon-link icon="eye" label="Historique" :href="route('technicians.show', $technician)" />
                            <x-icon-link icon="pencil" label="Modifier" :href="route('technicians.edit', $technician)" />
                            @if ($technician->stock_exits_count === 0)
                                <x-delete-form :action="route('technicians.destroy', $technician)" confirm="Supprimer le compte de « {{ $technician->name }} » ?" />
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-table>

            @if ($technicians->hasPages())
                <div class="border-t border-slate-200 px-4 py-3">{{ $technicians->links() }}</div>
            @endif
        @endif
    </x-card>
</x-app-layout>
