<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' — ' : '' }}{{ config('app.name', 'GKAS Stock') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <x-theme-script />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full bg-slate-50 font-sans text-slate-800 antialiased">
        <div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" class="min-h-full">
            {{-- Overlay mobile --}}
            <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"
                 @click="sidebarOpen = false" style="display: none;"></div>

            {{-- Barre latérale --}}
            <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                   class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-slate-900 text-white transition-transform dark:border-r dark:border-slate-800 dark:bg-slate-950 duration-200 lg:translate-x-0">
                <div class="flex h-16 items-center justify-between px-5">
                    <a href="{{ route('dashboard') }}"><x-application-logo /></a>
                    <button type="button" class="text-slate-400 hover:text-white lg:hidden" @click="sidebarOpen = false">
                        <x-icon name="x" class="h-6 w-6" />
                        <span class="sr-only">Fermer le menu</span>
                    </button>
                </div>

                <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="home">Tableau de bord</x-nav-link>

                    @if (auth()->user()->isResponsable())
                        <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')" icon="cube">Produits</x-nav-link>
                        <x-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')" icon="tag">Catégories</x-nav-link>

                        <p class="px-3 pb-1 pt-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Mouvements</p>
                        <x-nav-link :href="route('stock.entries.index')" :active="request()->routeIs('stock.entries.*')" icon="arrow-down">Entrées</x-nav-link>
                        <x-nav-link :href="route('stock.exits.index')" :active="request()->routeIs('stock.exits.*')" icon="arrow-up">Sorties</x-nav-link>
                        <x-nav-link :href="route('stock.movements.index')" :active="request()->routeIs('stock.movements.*')" icon="clock">Historique</x-nav-link>
                        <x-nav-link :href="route('stock.alerts.index')" :active="request()->routeIs('stock.alerts.*')" icon="bell">
                            Alertes
                        </x-nav-link>

                        <p class="px-3 pb-1 pt-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Équipe</p>
                        <x-nav-link :href="route('technicians.index')" :active="request()->routeIs('technicians.*')" icon="users">Techniciens</x-nav-link>
                    @else
                        <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')" icon="cube">Produits disponibles</x-nav-link>
                        <x-nav-link :href="route('my-exits.index')" :active="request()->routeIs('my-exits.*')" icon="clipboard">Mes sorties</x-nav-link>
                    @endif
                </nav>

                <div class="border-t border-slate-800 px-5 py-4 text-xs text-slate-500">
                    GKAS-GROUP &middot; {{ now()->year }}
                </div>
            </aside>

            <div class="lg:pl-64">
                {{-- Barre supérieure --}}
                <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-6 lg:px-8">
                    <button type="button" class="-m-2 p-2 text-slate-600 lg:hidden" @click="sidebarOpen = true">
                        <x-icon name="menu" class="h-6 w-6" />
                        <span class="sr-only">Ouvrir le menu</span>
                    </button>

                    <div class="min-w-0 flex-1 truncate text-sm text-slate-500">
                        {{ now()->translatedFormat('l j F Y') }}
                    </div>

                    <x-theme-toggle />

                    <x-dropdown align="right" width="w-56">
                        <x-slot name="trigger">
                            <button type="button" class="flex items-center gap-3 rounded-lg px-2 py-1.5 text-left hover:bg-slate-100">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-700">
                                    {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                                </span>
                                <span class="hidden sm:block">
                                    <span class="block text-sm font-medium text-slate-800">{{ auth()->user()->name }}</span>
                                    <span class="block text-xs text-slate-500">{{ auth()->user()->role->label() }}</span>
                                </span>
                                <x-icon name="chevron-down" class="h-4 w-4 text-slate-400" />
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="border-b border-slate-100 px-4 py-2 sm:hidden">
                                <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-slate-500">{{ auth()->user()->role->label() }}</p>
                            </div>
                            <x-dropdown-link :href="route('profile.edit')">Mon profil</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    Se déconnecter
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </header>

                <main class="px-4 py-6 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-7xl">
                        @isset($header)
                            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">{{ $header }}</h1>
                                    @isset($subtitle)
                                        <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
                                    @endisset
                                </div>
                                @isset($actions)
                                    <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
                                @endisset
                            </div>
                        @endisset

                        <x-flash />

                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
