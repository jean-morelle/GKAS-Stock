@php
    $isTechnician = auth()->user()->isTechnicien();
    $backUrl = $isTechnician ? route('my-exits.index') : route('stock.exits.index');

    $catalog = $products->map(fn ($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'reference' => $p->reference,
        'category' => $p->category?->name ?? 'Sans catégorie',
        'qty' => $p->stock_quantity,
        'label' => $p->formatQuantity($p->stock_quantity),
        'unit' => $p->unit->short(),
    ])->sortBy([['category', 'asc'], ['name', 'asc']])->values();

    // Lignes à réafficher après une erreur, sinon le produit choisi depuis la liste des produits.
    $initialLines = collect(old('lines', $selectedProduct ? [['product_id' => $selectedProduct, 'quantity' => 1]] : []))
        ->map(fn ($line) => ['product_id' => (int) ($line['product_id'] ?? 0), 'quantity' => $line['quantity'] ?? ''])
        ->filter(fn ($line) => $line['product_id'])
        ->values();

    $lineErrors = collect($errors->getMessages())->filter(fn ($messages, $key) => str_starts_with($key, 'lines'));
@endphp

<x-app-layout title="Nouvelle sortie">
    <x-slot name="header">Nouvelle sortie de stock</x-slot>
    <x-slot name="subtitle">{{ $isTechnician ? 'Matériel que vous prenez dans le stock.' : 'Remise de matériel à un technicien.' }}</x-slot>
    <x-slot name="actions">
        <x-link-button variant="secondary" icon="arrow-left" :href="$backUrl">Retour</x-link-button>
    </x-slot>

    <form method="POST" action="{{ route('stock.exits.store') }}" class="max-w-3xl space-y-6"
          x-data="exitForm({
              catalog: @js($catalog),
              lines: @js($initialLines),
              errors: @js($lineErrors),
              maxLines: {{ \App\Http\Requests\StockExitRequest::MAX_LINES }},
          })">
        @csrf

        {{-- 1. Bénéficiaire --}}
        <x-card>
            @if ($isTechnician)
                <div class="flex items-center gap-3 text-sm">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-blue-600"><x-icon name="user" class="h-5 w-5" /></span>
                    <span class="text-slate-600">Technicien<br><strong class="text-base text-slate-900">{{ auth()->user()->name }}</strong></span>
                </div>
            @else
                <x-input-label for="technician_id" value="Technicien" required />
                <x-select-input id="technician_id" name="technician_id" required>
                    <option value="">— Sélectionner un technicien —</option>
                    @foreach ($technicians as $technician)
                        <option value="{{ $technician->id }}" @selected((int) old('technician_id', $selectedTechnician) === $technician->id)>{{ $technician->name }}</option>
                    @endforeach
                </x-select-input>
                <x-input-error :messages="$errors->get('technician_id')" />
            @endif
        </x-card>

        {{-- 2. Produits --}}
        <x-card title="Matériel à sortir">
            <div class="relative">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <x-text-input type="search" x-model="search" x-ref="search" class="pl-9"
                              placeholder="Rechercher un produit (nom ou référence)…" aria-label="Rechercher un produit" autocomplete="off" />
            </div>

            <div class="mt-2 max-h-72 overflow-y-auto rounded-lg border border-slate-200">
                <template x-for="group in groups" :key="group.name">
                    <div>
                        <p class="sticky top-0 bg-slate-50 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-slate-500" x-text="group.name"></p>
                        <template x-for="product in group.products" :key="product.id">
                            <button type="button" @click="add(product)" :disabled="product.qty === 0 || has(product.id)"
                                    class="flex w-full items-center justify-between gap-3 border-t border-slate-100 px-3 py-2.5 text-left text-sm hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-50">
                                <span class="min-w-0">
                                    <span class="block truncate font-medium text-slate-900" x-text="product.name"></span>
                                    <span class="block text-xs text-slate-500" x-text="product.reference"></span>
                                </span>
                                <span class="shrink-0 text-right text-xs">
                                    <span x-show="has(product.id)" class="font-medium text-blue-600">Ajouté</span>
                                    <span x-show="!has(product.id) && product.qty === 0" class="font-medium text-red-600">Rupture</span>
                                    <span x-show="!has(product.id) && product.qty > 0" class="font-semibold tabular-nums text-slate-700" x-text="product.label"></span>
                                </span>
                            </button>
                        </template>
                    </div>
                </template>
                <p x-show="groups.length === 0" class="px-3 py-6 text-center text-sm text-slate-500">Aucun produit ne correspond à la recherche.</p>
            </div>

            {{-- Lignes choisies --}}
            <div class="mt-5">
                <p class="mb-2 text-sm font-medium text-slate-700">Produits choisis <span class="text-red-500">*</span></p>

                <p x-show="lines.length === 0" class="rounded-lg border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500">
                    Touchez un produit dans la liste ci-dessus pour l'ajouter.
                </p>

                <ul class="space-y-2">
                    <template x-for="(line, index) in lines" :key="line.product_id">
                        <li class="rounded-lg border border-slate-200 p-3" :class="lineError(index) || exceeds(line) ? 'border-red-200 bg-red-50' : ''">
                            <input type="hidden" :name="`lines[${index}][product_id]`" :value="line.product_id">
                            <div class="flex items-center gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-slate-900" x-text="product(line).name"></p>
                                    <p class="text-xs text-slate-500">Disponible : <span class="tabular-nums" x-text="product(line).label"></span></p>
                                </div>
                                <div class="relative w-32 shrink-0">
                                    <input type="number" min="1" step="1" inputmode="numeric" required
                                           :name="`lines[${index}][quantity]`" x-model="line.quantity" :max="product(line).qty"
                                           :id="`quantity-${line.product_id}`" :aria-label="`Quantité ${product(line).name}`"
                                           class="block w-full rounded-lg border-slate-300 pr-12 text-right text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400" x-text="product(line).unit"></span>
                                </div>
                                <button type="button" @click="remove(index)" class="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-600" :aria-label="`Retirer ${product(line).name}`">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </div>
                            <p x-show="exceeds(line)" class="mt-1 text-sm text-red-600">Stock insuffisant : la quantité dépasse le stock disponible.</p>
                            <p x-show="lineError(index)" class="mt-1 text-sm text-red-600" x-text="lineError(index)"></p>
                        </li>
                    </template>
                </ul>
                <x-input-error :messages="$errors->get('lines')" />
            </div>
        </x-card>

        {{-- 3. Intervention --}}
        <x-card title="Intervention">
            <div class="space-y-5">
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="reason" value="Motif" required />
                        <x-text-input id="reason" name="reason" :value="old('reason')" required maxlength="255" placeholder="Ex. Installation caméras" />
                        <x-input-error :messages="$errors->get('reason')" />
                    </div>
                    <div>
                        <x-input-label for="intervention" value="Intervention / client" />
                        <x-text-input id="intervention" name="intervention" :value="old('intervention')" maxlength="255" placeholder="Ex. Client ABC" />
                        <x-input-error :messages="$errors->get('intervention')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="observation" value="Observation" />
                    <x-textarea-input id="observation" name="observation">{{ old('observation') }}</x-textarea-input>
                    <x-input-error :messages="$errors->get('observation')" />
                </div>
            </div>
        </x-card>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-end">
            <x-link-button variant="secondary" :href="$backUrl">Annuler</x-link-button>
            <x-primary-button x-bind:disabled="!canSubmit">
                <x-icon name="arrow-up" class="h-4 w-4" />
                <span x-text="lines.length > 1 ? `Enregistrer la sortie (${lines.length} produits)` : 'Enregistrer la sortie'">Enregistrer la sortie</span>
            </x-primary-button>
        </div>
    </form>

    <script>
        function exitForm({ catalog, lines, errors, maxLines }) {
            const normalize = (text) => (text ?? '').toString().normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();

            return {
                catalog,
                lines,
                errors,
                search: '',

                get groups() {
                    const term = normalize(this.search.trim());
                    const groups = [];
                    for (const product of this.catalog) {
                        if (term && !normalize(product.name + ' ' + product.reference).includes(term)) continue;
                        let group = groups.find((g) => g.name === product.category);
                        if (!group) groups.push(group = { name: product.category, products: [] });
                        group.products.push(product);
                    }
                    return groups;
                },
                get canSubmit() {
                    return this.lines.length > 0 && !this.lines.some((line) => this.exceeds(line));
                },

                product(line) {
                    return this.catalog.find((p) => p.id === Number(line.product_id)) ?? { name: '?', label: '', unit: '', qty: 0 };
                },
                has(id) {
                    return this.lines.some((line) => Number(line.product_id) === id);
                },
                exceeds(line) {
                    return Number(line.quantity) > this.product(line).qty;
                },
                lineError(index) {
                    return (this.errors[`lines.${index}.quantity`] ?? this.errors[`lines.${index}.product_id`] ?? [])[0] ?? '';
                },
                add(product) {
                    if (this.has(product.id) || product.qty === 0 || this.lines.length >= maxLines) return;
                    this.lines.push({ product_id: product.id, quantity: 1 });
                    this.errors = {};
                    this.search = '';
                    this.$nextTick(() => document.getElementById(`quantity-${product.id}`)?.select());
                },
                remove(index) {
                    this.lines.splice(index, 1);
                    this.errors = {};
                },
            };
        }
    </script>
</x-app-layout>
