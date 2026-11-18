{{-- Champs « du … au … » des listes filtrables ($filters['date_from'], $filters['date_to']). --}}
<label class="flex items-center gap-2 text-sm text-slate-600">
    Du
    <x-text-input name="date_from" type="date" :value="$filters['date_from'] ?? ''" aria-label="Date début" class="sm:w-40" />
</label>
<label class="flex items-center gap-2 text-sm text-slate-600">
    au
    <x-text-input name="date_to" type="date" :value="$filters['date_to'] ?? ''" aria-label="Date fin" class="sm:w-40" />
</label>
@if ($errors->has('date_from') || $errors->has('date_to'))
    <x-input-error :messages="[...$errors->get('date_from'), ...$errors->get('date_to')]" class="w-full" />
@endif
