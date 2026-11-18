{{-- Monogramme GKAS-GROUP. Pastille toujours blanche : le logo (fond blanc, bleu marine) reste lisible en thème sombre. --}}
<div {{ $attributes->merge(['class' => 'flex items-center gap-2.5']) }}>
    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#ffffff] p-1.5 shadow-sm ring-1 ring-slate-900/10">
        <img src="{{ asset('images/logo-mark.png') }}" alt="GKAS-GROUP" class="h-full w-full object-contain">
    </span>
    <span class="text-lg font-bold tracking-tight">GKAS <span class="font-medium opacity-70">Stock</span></span>
</div>
