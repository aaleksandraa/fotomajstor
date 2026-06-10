@props(['locales' => [], 'current' => 'bs'])

@php $currentMeta = $locales[$current] ?? ['short' => strtoupper($current), 'label' => $current]; @endphp

<div x-data="{ open: false }" class="relative" @click.outside="open = false">
    <button @click="open = !open" type="button"
            class="flex items-center gap-1.5 rounded-full border border-ink-200 px-3 py-1.5 text-sm font-medium text-ink-700 transition hover:border-ink-300 hover:text-ink-900"
            aria-label="{{ __('Jezik') }}">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M3 12h18M12 3c2.5 2.7 2.5 15.3 0 18M12 3c-2.5 2.7-2.5 15.3 0 18"/></svg>
        {{ $currentMeta['short'] }}
        <svg class="h-3.5 w-3.5 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
    </button>

    <div x-show="open" x-transition x-cloak
         class="absolute right-0 z-50 mt-2 w-44 overflow-hidden rounded-xl border border-ink-100 bg-white py-1 shadow-lg"
         style="display:none">
        @foreach ($locales as $code => $meta)
            <a href="{{ \App\Support\LocalizedUrl::for(request()->fullUrl(), $code) }}"
               hreflang="{{ $meta['hreflang'] ?? $code }}"
               class="flex items-center justify-between px-4 py-2 text-sm transition hover:bg-ink-50 {{ $code === $current ? 'font-semibold text-ink-900' : 'text-ink-600' }}">
                <span>{{ $meta['label'] }}</span>
                <span class="text-xs text-ink-400">{{ $meta['short'] }}</span>
            </a>
        @endforeach
    </div>
</div>
