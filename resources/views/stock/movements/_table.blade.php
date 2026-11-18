{{-- Tableau des mouvements (lignes issues de MovementHistory). --}}
@if ($movements->isEmpty())
    <x-empty-state icon="clock" message="Aucun mouvement enregistré." />
@else
    <x-table>
        <x-slot name="head">
            <tr>
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3">Type</th>
                @if ($showProduct ?? true)<th class="px-4 py-3">Produit</th>@endif
                <th class="px-4 py-3 text-right">Quantité</th>
                <th class="px-4 py-3">Technicien</th>
                <th class="px-4 py-3">Utilisateur</th>
                <th class="px-4 py-3">Motif</th>
            </tr>
        </x-slot>

        @foreach ($movements as $movement)
            <tr class="hover:bg-slate-50">
                <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                    {{ $movement->created_at->format('d/m/Y') }}
                    <span class="text-xs text-slate-400">{{ $movement->created_at->format('H:i') }}</span>
                </td>
                <td class="px-4 py-3"><x-movement-badge :type="$movement->type" /></td>
                @if ($showProduct ?? true)
                    <td class="px-4 py-3 font-medium text-slate-900">
                        <a href="{{ route('products.show', $movement->product_id) }}" class="hover:text-blue-600">{{ $movement->product_name }}</a>
                    </td>
                @endif
                <td @class([
                    'whitespace-nowrap px-4 py-3 text-right font-semibold tabular-nums',
                    'text-emerald-600' => $movement->type === 'entry',
                    'text-orange-600' => $movement->type === 'exit',
                ])>
                    {{ $movement->type === 'entry' ? '+' : '−' }}{{ $movement->unit->format($movement->quantity) }}
                </td>
                <td class="px-4 py-3">
                    @if ($movement->technician_id)
                        <a href="{{ route('technicians.show', $movement->technician_id) }}" class="text-slate-700 hover:text-blue-600">{{ $movement->technician_name }}</a>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-slate-600">{{ $movement->user_name }}</td>
                <td class="px-4 py-3 text-slate-600">
                    {{ $movement->reason ?: '—' }}
                    @if ($movement->intervention)
                        <span class="block text-xs text-slate-400">{{ $movement->intervention }}</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-table>

    @if ($movements->hasPages())
        <div class="border-t border-slate-200 px-4 py-3">{{ $movements->links() }}</div>
    @endif
@endif
