@php($editing = $technician->exists)

<x-app-layout :title="$editing ? 'Modifier le technicien' : 'Nouveau technicien'">
    <x-slot name="header">{{ $editing ? 'Modifier : '.$technician->name : 'Nouveau technicien' }}</x-slot>
    <x-slot name="subtitle">Le technicien pourra se connecter pour consulter ses sorties et le stock disponible.</x-slot>
    <x-slot name="actions">
        <x-link-button variant="secondary" icon="arrow-left" :href="route('technicians.index')">Retour</x-link-button>
    </x-slot>

    <x-card class="max-w-2xl">
        <form method="POST" action="{{ $editing ? route('technicians.update', $technician) : route('technicians.store') }}" class="space-y-5">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div>
                <x-input-label for="name" value="Nom complet" required />
                <x-text-input id="name" name="name" :value="old('name', $technician->name)" required maxlength="255" autofocus />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="email" value="Adresse e-mail (identifiant de connexion)" required />
                <x-text-input id="email" name="email" type="email" :value="old('email', $technician->email)" required autocomplete="off" />
                <x-input-error :messages="$errors->get('email')" />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="password" value="Mot de passe" :required="! $editing" />
                    <x-text-input id="password" name="password" type="password" :required="! $editing" autocomplete="new-password" />
                    @if ($editing)<p class="mt-1 text-xs text-slate-500">Laisser vide pour conserver le mot de passe actuel.</p>@endif
                    <x-input-error :messages="$errors->get('password')" />
                </div>
                <div>
                    <x-input-label for="password_confirmation" value="Confirmation" :required="! $editing" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" :required="! $editing" autocomplete="new-password" />
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 pt-5">
                <x-link-button variant="secondary" :href="route('technicians.index')">Annuler</x-link-button>
                <x-primary-button>{{ $editing ? 'Enregistrer' : 'Créer le compte' }}</x-primary-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
