<x-app-layout title="Catégories">
    <x-slot name="header">Catégories</x-slot>
    <x-slot name="subtitle">Classement des produits du stock.</x-slot>
    <x-slot name="actions">
        <x-link-button :href="route('categories.create')" icon="plus">Nouvelle catégorie</x-link-button>
    </x-slot>

    <x-card :padding="false">
        <form method="GET" class="flex gap-2 border-b border-slate-200 p-4">
            <div class="relative flex-1 sm:max-w-sm">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <x-text-input name="search" :value="$search" placeholder="Rechercher une catégorie…" class="pl-9" />
            </div>
            <x-secondary-button type="submit">Rechercher</x-secondary-button>
        </form>

        @if ($categories->isEmpty())
            <x-empty-state icon="tag" message="Aucune catégorie trouvée." />
        @else
            <x-table>
                <x-slot name="head">
                    <tr>
                        <th class="px-4 py-3">Nom</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3 text-right">Produits</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </x-slot>

                @foreach ($categories as $category)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $category->name }}</td>
                        <td class="max-w-md truncate px-4 py-3 text-slate-500">{{ $category->description ?: '—' }}</td>
                        <td class="px-4 py-3 text-right tabular-nums">
                            <a href="{{ route('products.index', ['category' => $category->id]) }}" class="text-blue-600 hover:underline">{{ $category->products_count }}</a>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <x-icon-link icon="pencil" label="Modifier" :href="route('categories.edit', $category)" />
                            @if ($category->products_count === 0)
                                <x-delete-form :action="route('categories.destroy', $category)" confirm="Supprimer la catégorie « {{ $category->name }} » ?" />
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-table>

            @if ($categories->hasPages())
                <div class="border-t border-slate-200 px-4 py-3">{{ $categories->links() }}</div>
            @endif
        @endif
    </x-card>
</x-app-layout>
