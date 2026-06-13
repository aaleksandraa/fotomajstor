<div class="legal-register-documents space-y-3" x-data="{ open: null }">
    <p class="text-sm text-gray-600 dark:text-gray-300">
        {{ __('Prije registracije pročitajte dokumente. Klikom na naslov dokument se otvara ovdje, bez napuštanja stranice.') }}
    </p>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-white/5">
        <button type="button" @click="open = open === 'privacy' ? null : 'privacy'" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold text-gray-950 dark:text-white">
            <span>{{ __('Politika privatnosti') }}</span>
            <span x-text="open === 'privacy' ? '−' : '+'" aria-hidden="true"></span>
        </button>
        <div x-show="open === 'privacy'" x-collapse x-cloak class="max-h-72 overflow-y-auto border-t border-gray-200 px-4 py-4 text-sm text-gray-600 dark:border-white/10 dark:text-gray-300">
            @include('public.partials.privacy-content')
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-white/5">
        <button type="button" @click="open = open === 'terms' ? null : 'terms'" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold text-gray-950 dark:text-white">
            <span>{{ __('Uslovi korištenja') }}</span>
            <span x-text="open === 'terms' ? '−' : '+'" aria-hidden="true"></span>
        </button>
        <div x-show="open === 'terms'" x-collapse x-cloak class="max-h-72 overflow-y-auto border-t border-gray-200 px-4 py-4 text-sm text-gray-600 dark:border-white/10 dark:text-gray-300">
            @include('public.partials.terms-content')
        </div>
    </div>

    <style>
        .legal-register-documents section + section { margin-top: 1.25rem; }
        .legal-register-documents h2 { margin-bottom: .35rem; font-size: .875rem; font-weight: 700; color: inherit; }
        .legal-register-documents p + p, .legal-register-documents ul { margin-top: .5rem; }
        .legal-register-documents ul { list-style: disc; padding-left: 1.15rem; }
        .legal-register-documents li + li { margin-top: .25rem; }
        .legal-register-documents a { color: rgb(217 119 6); text-decoration: underline; }
    </style>
</div>
