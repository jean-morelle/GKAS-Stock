<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'GKAS Stock') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <x-theme-script />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full font-sans text-slate-800 antialiased">
        <div class="flex min-h-full">
            {{-- Panneau de marque (écrans larges) --}}
            <div class="relative hidden w-1/2 flex-col justify-between bg-slate-900 p-12 text-white lg:flex">
                <x-application-logo />
                <div>
                    <h1 class="text-3xl font-semibold leading-tight">Gestion et traçabilité<br>du stock de matériel</h1>
                    <p class="mt-4 max-w-md text-slate-400">
                        Entrées, sorties, matériel remis aux techniciens et alertes de stock faible, centralisés en un seul endroit.
                    </p>
                </div>
                <p class="text-sm text-slate-500">GKAS-GROUP &middot; Usage interne</p>
            </div>

            <div class="relative flex flex-1 flex-col items-center justify-center bg-slate-50 px-6 py-12">
                <x-theme-toggle class="absolute right-4 top-4" />
                <div class="mb-8 text-slate-900 lg:hidden">
                    <x-application-logo />
                </div>
                <div class="w-full max-w-sm">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
