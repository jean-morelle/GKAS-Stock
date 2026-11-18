@props(['title' => null, 'padding' => true])

<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm']) }}>
    @if ($title || isset($headerActions))
        <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-3.5">
            <h2 class="text-sm font-semibold text-slate-900">{{ $title }}</h2>
            {{ $headerActions ?? '' }}
        </div>
    @endif
    <div @class(['p-5' => $padding])>
        {{ $slot }}
    </div>
</section>
