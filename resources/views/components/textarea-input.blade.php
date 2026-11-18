<textarea {{ $attributes->merge(['rows' => 3, 'class' => 'block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500']) }}>{{ $slot }}</textarea>
