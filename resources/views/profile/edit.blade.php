<x-app-layout title="Mon profil">
    <x-slot name="header">Mon profil</x-slot>
    <x-slot name="subtitle">Vos informations de connexion.</x-slot>

    <div class="max-w-3xl space-y-6">
        <x-card>
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </x-card>

        <x-card>
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </x-card>
    </div>
</x-app-layout>
