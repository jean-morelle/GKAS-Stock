@props(['value' => null, 'required' => false])

<label {{ $attributes->merge(['class' => 'mb-1 block text-sm font-medium text-slate-700']) }}>
    {{ $value ?? $slot }}@if ($required)<span class="text-red-500"> *</span>@endif
</label>
