{{-- Bouton clair / sombre. --}}
<button type="button"
        x-data="{ dark: document.documentElement.classList.contains('dark') }"
        @click="
            dark = ! dark;
            document.documentElement.classList.toggle('dark', dark);
            try { localStorage.setItem('theme', dark ? 'dark' : 'light'); } catch (e) {}
        "
        :title="dark ? 'Passer en thème clair' : 'Passer en thème sombre'"
        {{ $attributes->merge(['class' => 'inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900']) }}>
    <x-icon name="moon" class="h-5 w-5" x-show="! dark" />
    <x-icon name="sun" class="h-5 w-5" x-show="dark" x-cloak />
    <span class="sr-only">Changer de thème</span>
</button>
