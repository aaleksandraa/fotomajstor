<div class="mt-2 text-center">
    <a href="{{ url('/') }}"
       class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition hover:text-amber-600 dark:text-gray-400">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19 3 12l7-7M3 12h18"/>
        </svg>
        {{ __('Nazad na početnu') }}
    </a>
    <div class="mt-3 flex flex-wrap justify-center gap-x-3 gap-y-1 text-xs text-gray-400">
        <a href="{{ localized_route('privacy') }}" target="_blank" class="transition hover:text-amber-600">{{ __('Politika privatnosti') }}</a>
        <a href="{{ localized_route('terms') }}" target="_blank" class="transition hover:text-amber-600">{{ __('Uslovi korištenja') }}</a>
    </div>
</div>
<div class="mb-3 flex flex-wrap justify-center gap-1.5">
    @foreach (config('locales.supported', []) as $code => $language)
        <a href="{{ route('locale.switch', ['locale' => $code, 'redirect' => request()->fullUrl()]) }}"
           class="rounded-md px-2 py-1 text-xs font-semibold {{ app()->getLocale() === $code ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300' }}">
            {{ $language['short'] }}
        </a>
    @endforeach
</div>
