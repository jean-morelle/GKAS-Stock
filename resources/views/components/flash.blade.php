@if (session('success'))
    <div x-data="{ show: true }" x-show="show" role="status"
         class="mb-6 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
        <x-icon name="check-circle" class="h-5 w-5 text-emerald-600" />
        <p class="flex-1">{{ session('success') }}</p>
        <button type="button" @click="show = false" class="text-emerald-600 hover:text-emerald-800"><x-icon name="x" class="h-4 w-4" /></button>
    </div>
@endif

@if (session('error'))
    <div x-data="{ show: true }" x-show="show" role="alert"
         class="mb-6 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
        <x-icon name="x-circle" class="h-5 w-5 text-red-600" />
        <p class="flex-1">{{ session('error') }}</p>
        <button type="button" @click="show = false" class="text-red-600 hover:text-red-800"><x-icon name="x" class="h-4 w-4" /></button>
    </div>
@endif
