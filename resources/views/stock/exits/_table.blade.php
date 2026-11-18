{{-- Liste de sorties (StockExit avec product, technician, creator chargés selon besoin). --}}
@if ($exits->isEmpty())
    <x-empty-state icon="arrow-up" message="Aucune sortie enregistrée." />
@else
    {{-- Téléphone : une carte par sortie. --}}
    <ul class="divide-y divide-slate-100 sm:hidden">
        @foreach ($exits as $exit)
            <li class="px-4 py-3 text-sm">
                <div class="flex items-start justify-between gap-3">
                    <span class="min-w-0 font-medium text-slate-900">{{ $exit->product->name }}</span>
                    <span class="whitespace-nowrap font-semibold tabular-nums text-orange-600">{{ $exit->product->formatQuantity($exit->quantity) }}</span>
                </div>
                <p class="mt-0.5 text-slate-600">{{ $exit->reason }}@if ($exit->intervention) &middot; {{ $exit->intervention }}@endif</p>
                <p class="mt-0.5 text-xs text-slate-400">
                    {{ $exit->created_at->format('d/m/Y H:i') }}
                    @if ($showTechnician) &middot; <a href="{{ route('technicians.show', $exit->technician_id) }}" class="hover:text-blue-600">{{ $exit->technician->name }}</a>@endif
                </p>
            </li>
        @endforeach
    </ul>

    <div class="hidden sm:block">
        <x-table>
            <x-slot name="head">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    @if ($showTechnician)<th class="px-4 py-3">Technicien</th>@endif
                    <th class="px-4 py-3">Produit</th>
                    <th class="px-4 py-3 text-right">Quantité</th>
                    <th class="px-4 py-3">Motif</th>
                    <th class="px-4 py-3">Intervention</th>
                    @if ($showTechnician)<th class="px-4 py-3">Enregistré par</th>@endif
                </tr>
            </x-slot>
    
            @foreach ($exits as $exit)
                <tr class="hover:bg-slate-50">
                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                        {{ $exit->created_at->format('d/m/Y') }}
                        <span class="text-xs text-slate-400">{{ $exit->created_at->format('H:i') }}</span>
                    </td>
                    @if ($showTechnician)
                        <td class="px-4 py-3 font-medium">
                            <a href="{{ route('technicians.show', $exit->technician_id) }}" class="text-slate-900 hover:text-blue-600">{{ $exit->technician->name }}</a>
                        </td>
                    @endif
                    <td class="px-4 py-3 text-slate-900">{{ $exit->product->name }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-right font-semibold tabular-nums text-orange-600">{{ $exit->product->formatQuantity($exit->quantity) }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $exit->reason }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $exit->intervention ?: '—' }}</td>
                    @if ($showTechnician)
                        <td class="px-4 py-3 text-slate-500">{{ $exit->creator->name }}</td>
                    @endif
                </tr>
            @endforeach
        </x-table>
    </div>

    @if ($exits instanceof \Illuminate\Contracts\Pagination\Paginator && $exits->hasPages())
        <div class="border-t border-slate-200 px-4 py-3">{{ $exits->links() }}</div>
    @endif
@endif
