@props(['available' => true, 'compact' => false])

@if ($available)
    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>{{ $compact ? __('Slobodan') : __('Dostupan') }}
    </span>
@else
    <span class="inline-flex items-center gap-1.5 rounded-full bg-ink-100 px-2.5 py-1 text-xs font-medium text-ink-500">
        <span class="h-1.5 w-1.5 rounded-full bg-ink-400"></span>{{ __('Zauzet') }}
    </span>
@endif
