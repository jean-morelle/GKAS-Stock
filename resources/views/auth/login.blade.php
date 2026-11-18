<x-guest-layout>
    <h2 class="text-2xl font-semibold text-slate-900">Connexion</h2>
    <p class="mt-1 text-sm text-slate-500">Accédez à votre espace GKAS Stock.</p>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="Adresse e-mail" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div x-data="{ show: false }">
            <x-input-label for="password" value="Mot de passe" />
            <div class="relative">
                <x-text-input id="password" type="password" ::type="show ? 'text' : 'password'" name="password" required autocomplete="current-password" class="pr-10" />
                <button type="button" @click="show = ! show" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600"
                        :title="show ? 'Masquer le mot de passe' : 'Afficher le mot de passe'">
                    <x-icon name="eye" class="h-5 w-5" />
                    <span class="sr-only">Afficher ou masquer le mot de passe</span>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                <span class="ms-2 text-sm text-slate-600">Se souvenir de moi</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-blue-600 hover:text-blue-500" href="{{ route('password.request') }}">
                    Mot de passe oublié ?
                </a>
            @endif
        </div>

        <x-primary-button class="w-full py-2.5">Se connecter</x-primary-button>
    </form>
</x-guest-layout>
