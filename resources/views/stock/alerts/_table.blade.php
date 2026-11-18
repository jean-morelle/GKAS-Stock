{{-- Produits en alerte : $products, $withCategory (bool), $withAction (bool). --}}
<x-table>
    <x-slot name="head">
        <tr>
            <th class="px-4 py-3">Produit</th>
            @if ($withCategory ?? true)<th class="px-4 py-3">Catégorie</th>@endif
            <th class="px-4 py-3 text-right">Stock actuel</th>
            <th class="px-4 py-3 text-right">Seuil</th>
            <th class="px-4 py-3">Statut</th>
            @if ($withAction ?? true)<th class="px-4 py-3 text-right">Action</th>@endif
        </tr>
    </x-slot>

    @foreach ($products as $product)
        <tr class="hover:bg-slate-50">
            <td class="px-4 py-3 font-medium text-slate-900">
                <a href="{{ route('products.show', $product) }}" class="hover:text-blue-600">{{ $product->name }}</a>
            </td>
            @if ($withCategory ?? true)<td class="px-4 py-3 text-slate-600">{{ $product->category->name }}</td>@endif
            <td class="whitespace-nowrap px-4 py-3 text-right font-semibold tabular-nums">{{ $product->formatQuantity($product->stock_quantity) }}</td>
            <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-slate-500">{{ $product->formatQuantity($product->alert_threshold) }}</td>
            <td class="px-4 py-3"><x-status-badge :status="$product->stockStatus()" /></td>
            @if ($withAction ?? true)
                <td class="whitespace-nowrap px-4 py-3 text-right">
                    <a href="{{ route('stock.entries.create', ['product' => $product->id]) }}" class="text-sm font-medium text-blue-600 hover:underline">Réapprovisionner</a>
                </td>
            @endif
        </tr>
    @endforeach
</x-table>
