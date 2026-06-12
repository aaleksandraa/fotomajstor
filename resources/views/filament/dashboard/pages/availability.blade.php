@assets
    <link rel="stylesheet" href="{{ asset('vendor/vanilla-calendar-pro/index.css') }}?v={{ filemtime(public_path('vendor/vanilla-calendar-pro/index.css')) }}">
    <script src="{{ asset('js/availability-calendar.js') }}?v={{ filemtime(public_path('js/availability-calendar.js')) }}"></script>
@endassets

<x-filament-panels::page>
    @php
        $calendarIntlLocale = match (app()->getLocale()) {
            'bs' => 'bs-BA',
            'hr' => 'hr-HR',
            'sr' => 'sr-Latn-RS',
            'sl' => 'sl-SI',
            'de' => 'de-DE',
            'it' => 'it-IT',
            default => 'en-GB',
        };
    @endphp

    <div class="grid gap-5 2xl:grid-cols-[minmax(0,1fr)_340px]">
        <div
            wire:ignore
            wire:key="availability-calendar-stable"
            x-data="availabilityCalendar({
                initialDate: @js($month . '-01'),
                today: @js(today()->toDateString()),
                busyDates: @js($this->busyDates),
                intlLocale: @js($calendarIntlLocale),
                libraryUrl: @js(asset('vendor/vanilla-calendar-pro/index.mjs') . '?v=' . filemtime(public_path('vendor/vanilla-calendar-pro/index.mjs'))),
                busyLabel: @js(__('Zauzet')),
                freeLabel: @js(__('Slobodan')),
            })"
            @availability-open-date.window="openDate($event.detail.date, $event.detail.busy)"
            @availability-mark-month.window="markVisibleMonth($event.detail.busy)"
            @theme-changed.window="syncTheme($event.detail)"
            class="availability-shell overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900"
        >
            <div class="availability-hero border-b border-gray-100 px-4 py-5 dark:border-white/10 sm:px-7 sm:py-6">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary-600 dark:text-primary-400">Kalendar dostupnosti</p>
                <h2 class="mt-2 text-xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-2xl">Upravljajte zauzetim terminima</h2>
                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">Kliknite bilo koji budući datum, provjerite status i odmah ga promijenite.</p>
            </div>

            <div class="p-3 sm:p-6 lg:p-7">
                <div x-ref="calendar" @click="handleCalendarClick($event)" class="availability-modern-calendar"></div>
            </div>

            <div class="availability-legend grid gap-2 border-t border-gray-100 px-4 py-4 text-xs font-medium text-gray-600 dark:border-white/10 dark:text-gray-300 sm:flex sm:flex-wrap sm:items-center sm:gap-5 sm:px-7">
                <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500 ring-4 ring-emerald-500/10"></span> Slobodan termin</span>
                <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-rose-500 ring-4 ring-rose-500/10"></span> Zauzet termin</span>
                <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-primary-500 ring-4 ring-primary-500/10"></span> Današnji datum</span>
            </div>

            <div
                x-show="modalOpen"
                x-cloak
                x-transition.opacity
                @keydown.escape.window="if (! saving) modalOpen = false"
                class="fixed inset-0 z-50 flex items-end justify-center bg-gray-950/65 p-0 backdrop-blur-sm sm:items-center sm:p-4"
            >
                <div
                    @click.outside="if (! saving) modalOpen = false"
                    x-transition:enter="transition duration-200 ease-out"
                    x-transition:enter-start="translate-y-full opacity-0 sm:translate-y-3"
                    x-transition:enter-end="translate-y-0 opacity-100"
                    class="w-full max-w-md rounded-t-3xl border border-white/10 bg-white p-5 shadow-2xl dark:bg-gray-900 sm:rounded-3xl sm:p-6"
                >
                    <div class="mx-auto mb-5 h-1.5 w-12 rounded-full bg-gray-200 dark:bg-gray-700 sm:hidden"></div>
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-start gap-3">
                            <span class="mt-0.5 grid h-11 w-11 shrink-0 place-items-center rounded-2xl" :class="selectedBusy ? 'bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300'">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v3m8-3v3M3.5 9h17M5 4h14a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-[0.18em]" :class="selectedBusy ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'" x-text="selectedBusy ? 'Zauzet termin' : 'Slobodan termin'"></p>
                                <h3 class="mt-1 text-xl font-bold capitalize leading-tight text-gray-950 dark:text-white" x-text="selectedLabel"></h3>
                            </div>
                        </div>
                        <button type="button" :disabled="saving" @click="modalOpen = false" class="rounded-xl p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 disabled:opacity-40 dark:hover:bg-white/10 dark:hover:text-white">
                            <span class="sr-only">Zatvori</span>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>
                        </button>
                    </div>

                    <p class="mt-5 text-sm leading-6 text-gray-600 dark:text-gray-300">Odaberite status termina. Status možete ponovo promijeniti u bilo kojem trenutku.</p>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <button
                            type="button"
                            :disabled="saving"
                            @click="applyDateStatus(false)"
                            class="availability-status-choice availability-status-choice-free"
                            :class="{ 'availability-status-choice-active': ! selectedBusy }"
                        >
                            <span class="availability-status-choice-icon">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
                            </span>
                            <span>
                                <strong>Slobodan dan</strong>
                                <small x-text="! selectedBusy ? 'Trenutni status' : 'Označi dostupnim'"></small>
                            </span>
                        </button>
                        <button
                            type="button"
                            :disabled="saving"
                            @click="applyDateStatus(true)"
                            class="availability-status-choice availability-status-choice-busy"
                            :class="{ 'availability-status-choice-active': selectedBusy }"
                        >
                            <span class="availability-status-choice-icon">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18"/></svg>
                            </span>
                            <span>
                                <strong>Zauzet dan</strong>
                                <small x-text="selectedBusy ? 'Trenutni status' : 'Označi zauzetim'"></small>
                            </span>
                        </button>
                    </div>

                    <div class="mt-5 flex items-center justify-between gap-3 border-t border-gray-100 pt-4 dark:border-white/10">
                        <span class="inline-flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <svg x-show="saving" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-30" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-linecap="round" stroke-width="3"/></svg>
                            <span x-text="saving ? 'Spremanje promjene...' : 'Promjena se odmah objavljuje'"></span>
                        </span>
                        <button type="button" :disabled="saving" @click="modalOpen = false" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 disabled:opacity-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5">Zatvori</button>
                    </div>
                </div>
            </div>
        </div>

        <aside class="grid content-start gap-5 md:grid-cols-2 2xl:grid-cols-1">
            <div class="md:col-span-2 grid grid-cols-2 gap-3 2xl:col-span-1">
                <div class="availability-stat availability-stat-free">
                    <span class="availability-stat-icon"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span></span>
                    <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Dostupno</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-950 dark:text-emerald-100">{{ $this->summary['available'] }}</p>
                    <p class="mt-1 text-xs text-emerald-700/70 dark:text-emerald-300/70">dana u mjesecu</p>
                </div>
                <div class="availability-stat availability-stat-busy">
                    <span class="availability-stat-icon"><span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span></span>
                    <p class="text-xs font-semibold text-rose-700 dark:text-rose-300">Zauzeto</p>
                    <p class="mt-1 text-2xl font-bold text-rose-950 dark:text-rose-100">{{ $this->summary['busy'] }}</p>
                    <p class="mt-1 text-xs text-rose-700/70 dark:text-rose-300/70">dana u mjesecu</p>
                </div>
            </div>

            <div class="availability-side-card">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-gray-950 dark:text-white">Naredni zauzeti termini</h3>
                        <p class="mt-1 text-sm leading-5 text-gray-500 dark:text-gray-400">Kliknite termin da promijenite status.</p>
                    </div>
                    <span class="rounded-lg bg-rose-50 px-2 py-1 text-xs font-bold text-rose-600 dark:bg-rose-500/10 dark:text-rose-300">{{ count($this->upcomingBusy) }}</span>
                </div>

                @if ($this->upcomingBusy)
                    <div class="mt-4 space-y-2">
                        @foreach ($this->upcomingBusy as $busy)
                            <button type="button" @click="$dispatch('availability-open-date', { date: @js($busy['date']), busy: true })" class="group flex w-full items-center justify-between gap-3 rounded-xl border border-rose-100 bg-rose-50/70 px-3 py-3 text-left text-sm text-rose-800 transition hover:-translate-y-0.5 hover:border-rose-300 hover:bg-rose-100 dark:border-rose-500/15 dark:bg-rose-500/10 dark:text-rose-200 dark:hover:border-rose-500/35 dark:hover:bg-rose-500/15">
                                <span class="capitalize">{{ $busy['label'] }}</span>
                                <svg class="h-4 w-4 shrink-0 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="mt-4 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4 text-sm leading-6 text-emerald-700 dark:border-emerald-500/15 dark:bg-emerald-500/10 dark:text-emerald-300">Nemate označenih zauzetih termina.</div>
                @endif
            </div>

            <div class="availability-side-card">
                <h3 class="font-bold text-gray-950 dark:text-white">Brze izmjene mjeseca</h3>
                <p class="mt-1 text-sm leading-5 text-gray-500 dark:text-gray-400">Akcije se primjenjuju na mjesec prikazan u kalendaru.</p>
                <div class="mt-4 grid gap-2">
                    <x-filament::button color="danger" size="sm" wire:click="markMonthUnavailable" @click="$dispatch('availability-mark-month', { busy: true })">Cijeli mjesec zauzet</x-filament::button>
                    <x-filament::button color="success" size="sm" wire:click="markMonthAvailable" @click="$dispatch('availability-mark-month', { busy: false })">Cijeli mjesec dostupan</x-filament::button>
                    <x-filament::button color="gray" size="sm" tag="a" :href="route('photographer.show', $profile->slug)" target="_blank">Pregledaj javni profil</x-filament::button>
                </div>
            </div>
        </aside>
    </div>

    <style>
        .availability-hero { background: radial-gradient(circle at 90% -20%, rgb(245 158 11 / .18), transparent 38%), linear-gradient(135deg, rgb(255 251 235 / .7), rgb(255 255 255)); }
        .availability-stat, .availability-side-card { position: relative; overflow: hidden; border: 1px solid rgb(229 231 235 / .85); border-radius: 1.25rem; background: white; box-shadow: 0 1px 2px rgb(15 23 42 / .04); }
        .availability-stat { padding: 1rem; }
        .availability-side-card { padding: 1.25rem; }
        .availability-stat-free { background: linear-gradient(145deg, rgb(236 253 245), white 75%); }
        .availability-stat-busy { background: linear-gradient(145deg, rgb(255 241 242), white 75%); }
        .availability-stat-icon { position: absolute; right: 1rem; top: 1rem; display: grid; height: 1.75rem; width: 1.75rem; place-items: center; border-radius: .75rem; background: rgb(255 255 255 / .8); box-shadow: 0 1px 3px rgb(15 23 42 / .08); }
        .availability-status-choice { display: flex; min-height: 5.5rem; align-items: center; gap: .75rem; border: 1px solid rgb(229 231 235); border-radius: 1rem; padding: .85rem; text-align: left; transition: border-color .18s ease, background-color .18s ease, box-shadow .18s ease, transform .18s ease; }
        .availability-status-choice:hover { transform: translateY(-1px); }
        .availability-status-choice:disabled { cursor: wait; opacity: .6; transform: none; }
        .availability-status-choice-icon { display: grid; height: 2.5rem; width: 2.5rem; flex-shrink: 0; place-items: center; border-radius: .8rem; }
        .availability-status-choice strong, .availability-status-choice small { display: block; }
        .availability-status-choice strong { color: rgb(17 24 39); font-size: .875rem; }
        .availability-status-choice small { margin-top: .2rem; color: rgb(107 114 128); font-size: .7rem; font-weight: 600; }
        .availability-status-choice-free .availability-status-choice-icon { background: rgb(220 252 231); color: rgb(22 163 74); }
        .availability-status-choice-free:hover, .availability-status-choice-free.availability-status-choice-active { border-color: rgb(74 222 128); background: rgb(240 253 244); box-shadow: 0 0 0 3px rgb(34 197 94 / .1); }
        .availability-status-choice-busy .availability-status-choice-icon { background: rgb(254 226 226); color: rgb(220 38 38); }
        .availability-status-choice-busy:hover, .availability-status-choice-busy.availability-status-choice-active { border-color: rgb(248 113 113); background: rgb(254 242 242); box-shadow: 0 0 0 3px rgb(239 68 68 / .1); }
        .availability-modern-calendar [data-vc="calendar"] { min-width: 0; padding: 0; width: 100%; }
        .availability-modern-calendar [data-vc="header"] { margin-bottom: 1.25rem; }
        .availability-modern-calendar [data-vc-header="content"] { gap: .15rem; }
        .availability-modern-calendar [data-vc="month"], .availability-modern-calendar [data-vc="year"] { color: rgb(15 23 42); cursor: default; font-size: 1.25rem; font-weight: 800; letter-spacing: -.025em; text-transform: capitalize; }
        .availability-modern-calendar [data-vc-arrow] { border: 1px solid rgb(226 232 240); border-radius: .75rem; height: 2.5rem; transition: background-color .18s ease, border-color .18s ease; width: 2.5rem; }
        .availability-modern-calendar [data-vc-arrow]:hover { background: rgb(248 250 252); border-color: rgb(203 213 225); }
        .availability-modern-calendar [data-vc="week"] { gap: .3rem; margin-bottom: .6rem; }
        .availability-modern-calendar [data-vc-week-day] { color: rgb(71 85 105); font-size: .72rem; font-weight: 800; letter-spacing: .05em; min-height: 2.35rem; text-transform: capitalize; }
        .availability-modern-calendar [data-vc="dates"] { gap: .35rem; }
        .availability-modern-calendar [data-vc="dates"][data-vc-dates="row"] { gap: .35rem; }
        .availability-modern-calendar [data-vc-date] { min-width: 0; padding: 0; }
        .availability-modern-calendar [data-vc-date-btn] { align-items: flex-start; border: 1px solid rgb(226 232 240); border-radius: .9rem; color: rgb(51 65 85); font-size: .78rem; font-weight: 800; justify-content: flex-start; min-height: 6rem; padding: .65rem; position: relative; transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease; }
        .availability-modern-calendar [data-vc-date-btn]::after { bottom: .55rem; content: attr(data-availability-status); font-size: .62rem; font-weight: 800; left: .55rem; letter-spacing: .02em; position: absolute; text-transform: uppercase; }
        .availability-modern-calendar [data-vc-date]:not([data-vc-date-disabled]) [data-vc-date-btn]:hover { border-color: rgb(245 158 11); box-shadow: 0 8px 20px rgb(15 23 42 / .08), inset 0 0 0 1px rgb(245 158 11 / .25); transform: translateY(-1px); }
        .availability-modern-calendar .availability-free-day [data-vc-date-btn] { background: rgb(187 247 208 / .72); border-color: rgb(74 222 128 / .55); color: rgb(20 83 45); }
        .availability-modern-calendar .availability-free-day [data-vc-date-btn]::after { color: rgb(21 128 61); }
        .availability-modern-calendar .availability-busy-day [data-vc-date-btn] { background: rgb(254 202 202 / .82); border-color: rgb(248 113 113 / .58); color: rgb(127 29 29); }
        .availability-modern-calendar .availability-busy-day [data-vc-date-btn]::after { color: rgb(185 28 28); }
        .availability-modern-calendar .availability-status-updated [data-vc-date-btn] { animation: availability-status-confirmed .65s ease-out; }
        .availability-modern-calendar [data-vc-date-today] [data-vc-date-btn] { box-shadow: inset 0 0 0 2px rgb(245 158 11); }
        .availability-modern-calendar [data-vc-date-disabled] { opacity: .35; }
        .availability-modern-calendar [data-vc-date-month="prev"], .availability-modern-calendar [data-vc-date-month="next"] { opacity: .25; }
        .dark .availability-hero { background: radial-gradient(circle at 90% -20%, rgb(245 158 11 / .2), transparent 38%), linear-gradient(135deg, rgb(30 41 59 / .7), rgb(17 24 39)); }
        .dark .availability-stat, .dark .availability-side-card { border-color: rgb(255 255 255 / .1); background: rgb(17 24 39); box-shadow: 0 12px 30px rgb(0 0 0 / .12); }
        .dark .availability-stat-free { background: linear-gradient(145deg, rgb(6 78 59 / .28), rgb(17 24 39) 75%); }
        .dark .availability-stat-busy { background: linear-gradient(145deg, rgb(136 19 55 / .25), rgb(17 24 39) 75%); }
        .dark .availability-stat-icon { background: rgb(255 255 255 / .08); box-shadow: none; }
        .dark .availability-status-choice { border-color: rgb(255 255 255 / .1); }
        .dark .availability-status-choice strong { color: white; }
        .dark .availability-status-choice small { color: rgb(156 163 175); }
        .dark .availability-status-choice-free .availability-status-choice-icon { background: rgb(34 197 94 / .14); color: rgb(134 239 172); }
        .dark .availability-status-choice-free:hover, .dark .availability-status-choice-free.availability-status-choice-active { border-color: rgb(74 222 128 / .55); background: rgb(34 197 94 / .1); }
        .dark .availability-status-choice-busy .availability-status-choice-icon { background: rgb(239 68 68 / .14); color: rgb(252 165 165); }
        .dark .availability-status-choice-busy:hover, .dark .availability-status-choice-busy.availability-status-choice-active { border-color: rgb(248 113 113 / .55); background: rgb(239 68 68 / .1); }
        .dark .availability-modern-calendar [data-vc="calendar"] { background: transparent; }
        .dark .availability-modern-calendar [data-vc="month"], .dark .availability-modern-calendar [data-vc="year"] { color: white; }
        .dark .availability-modern-calendar [data-vc-arrow] { background-color: rgb(255 255 255 / .05); border-color: rgb(255 255 255 / .12); }
        .dark .availability-modern-calendar [data-vc-arrow]:hover { background-color: rgb(255 255 255 / .1); border-color: rgb(255 255 255 / .2); }
        .dark .availability-modern-calendar [data-vc-week-day] { color: rgb(203 213 225); }
        .dark .availability-modern-calendar [data-vc-date-btn] { border-color: rgb(255 255 255 / .1); color: rgb(226 232 240); }
        .dark .availability-modern-calendar .availability-free-day [data-vc-date-btn] { background: rgb(134 239 172 / .24); border-color: rgb(134 239 172 / .22); color: rgb(220 252 231); }
        .dark .availability-modern-calendar .availability-free-day [data-vc-date-btn]::after { color: rgb(134 239 172); }
        .dark .availability-modern-calendar .availability-busy-day [data-vc-date-btn] { background: rgb(252 165 165 / .24); border-color: rgb(252 165 165 / .22); color: rgb(254 226 226); }
        .dark .availability-modern-calendar .availability-busy-day [data-vc-date-btn]::after { color: rgb(252 165 165); }
        @keyframes availability-status-confirmed {
            0% { box-shadow: 0 0 0 0 rgb(245 158 11 / .55); transform: scale(.96); }
            55% { box-shadow: 0 0 0 7px rgb(245 158 11 / .13); transform: scale(1.015); }
            100% { box-shadow: 0 0 0 0 transparent; transform: scale(1); }
        }
        @media (max-width: 640px) {
            .availability-modern-calendar [data-vc="header"] { margin-bottom: 1rem; }
            .availability-modern-calendar [data-vc="month"], .availability-modern-calendar [data-vc="year"] { font-size: 1rem; }
            .availability-modern-calendar [data-vc="week"], .availability-modern-calendar [data-vc="dates"], .availability-modern-calendar [data-vc="dates"][data-vc-dates="row"] { gap: .18rem; }
            .availability-modern-calendar [data-vc-week-day] { font-size: .58rem; min-height: 1.9rem; overflow: hidden; white-space: nowrap; }
            .availability-modern-calendar [data-vc-date-btn] { border-radius: .65rem; font-size: .7rem; min-height: 3.9rem; padding: .35rem; }
            .availability-modern-calendar [data-vc-date-btn]::after { bottom: .32rem; content: ""; height: .35rem; left: 50%; transform: translateX(-50%); width: .35rem; }
            .availability-modern-calendar .availability-free-day [data-vc-date-btn]::after { background: rgb(34 197 94); border-radius: 999px; }
            .availability-modern-calendar .availability-busy-day [data-vc-date-btn]::after { background: rgb(239 68 68); border-radius: 999px; }
        }
    </style>
</x-filament-panels::page>
