@assets
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.20/locales-all.global.min.js"></script>
    <script src="{{ asset('js/availability-calendar.js') }}"></script>
@endassets

<x-filament-panels::page>
    @php
        $calendarLocale = match (app()->getLocale()) {
            'sr' => 'sr',
            'sl' => 'sl',
            'en' => 'en-gb',
            default => app()->getLocale(),
        };
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
                calendarLocale: @js($calendarLocale),
                intlLocale: @js($calendarIntlLocale),
            })"
            @availability-open-date.window="openDate($event.detail.date, $event.detail.busy)"
            @availability-mark-month.window="markVisibleMonth($event.detail.busy)"
            class="availability-shell overflow-hidden rounded-3xl border border-gray-200/80 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900"
        >
            <div class="availability-hero border-b border-gray-100 px-4 py-5 dark:border-white/10 sm:px-7 sm:py-6">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-primary-600 dark:text-primary-400">Kalendar dostupnosti</p>
                <h2 class="mt-2 text-xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-2xl">Upravljajte zauzetim terminima</h2>
                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">Kliknite bilo koji budući datum, provjerite status i odmah ga promijenite.</p>
            </div>

            <div class="p-3 sm:p-6 lg:p-7">
                <div x-ref="calendar" class="availability-fullcalendar"></div>
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

                    <p class="mt-5 rounded-2xl bg-gray-50 p-4 text-sm leading-6 text-gray-600 dark:bg-white/5 dark:text-gray-300" x-text="selectedBusy
                        ? 'Ovaj datum je trenutno označen kao zauzet. Možete ga ponovo učiniti dostupnim posjetiocima.'
                        : 'Ovaj datum je trenutno slobodan. Označite ga kao zauzet ako već imate rezervaciju.'"></p>

                    <div class="mt-6 grid gap-2 sm:grid-cols-2">
                        <button type="button" :disabled="saving" @click="modalOpen = false" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 disabled:opacity-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5">Odustani</button>
                        <button type="button" :disabled="saving" @click="applyDateStatus()" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition disabled:cursor-wait disabled:opacity-60" :class="selectedBusy ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-rose-600 hover:bg-rose-500'">
                            <svg x-show="saving" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-30" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-linecap="round" stroke-width="3"/></svg>
                            <span x-text="saving ? 'Spremanje...' : (selectedBusy ? 'Označi kao dostupno' : 'Označi kao zauzeto')"></span>
                        </button>
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
        .availability-fullcalendar { --fc-border-color: rgb(226 232 240); --fc-button-bg-color: white; --fc-button-border-color: rgb(226 232 240); --fc-button-text-color: rgb(51 65 85); --fc-button-hover-bg-color: rgb(248 250 252); --fc-button-hover-border-color: rgb(203 213 225); --fc-button-active-bg-color: rgb(245 158 11); --fc-button-active-border-color: rgb(245 158 11); --fc-today-bg-color: rgb(254 243 199 / .72); color: rgb(51 65 85); }
        .availability-fullcalendar .fc-scrollgrid { overflow: hidden; border-radius: 1rem; }
        .availability-fullcalendar .fc-toolbar { gap: .75rem; margin-bottom: 1.25rem; }
        .availability-fullcalendar .fc-toolbar-title { color: rgb(15 23 42); font-size: 1.25rem; font-weight: 800; letter-spacing: -.025em; text-transform: capitalize; }
        .availability-fullcalendar .fc-button { min-height: 2.5rem; border-radius: .75rem; box-shadow: 0 1px 2px rgb(15 23 42 / .05); font-size: .8rem; font-weight: 700; padding: .45rem .75rem; text-transform: none; }
        .availability-fullcalendar .fc-button:focus { box-shadow: 0 0 0 3px rgb(245 158 11 / .2); }
        .availability-fullcalendar .fc-col-header-cell { background: rgb(248 250 252); padding: .75rem .25rem; }
        .availability-fullcalendar .fc-col-header-cell-cushion { color: rgb(100 116 139); font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        .availability-fullcalendar .fc-daygrid-day { cursor: pointer; min-height: 6.25rem; transition: background-color .18s ease, box-shadow .18s ease; }
        .availability-fullcalendar .fc-daygrid-day:not(.fc-day-past):hover { background: rgb(255 251 235); box-shadow: inset 0 0 0 2px rgb(245 158 11 / .45); z-index: 2; }
        .availability-fullcalendar .fc-daygrid-day-number { color: rgb(51 65 85); font-size: .8rem; font-weight: 800; padding: .65rem; }
        .availability-fullcalendar .fc-day-today .fc-daygrid-day-number { display: grid; height: 2rem; width: 2rem; place-items: center; border-radius: .75rem; background: rgb(245 158 11); color: white; margin: .3rem; padding: 0; }
        .availability-fullcalendar .availability-free-day:not(.fc-day-past) { background: rgb(187 247 208 / .72); box-shadow: inset 0 0 0 1px rgb(22 101 52 / .08); }
        .availability-fullcalendar .availability-busy-day { background: rgb(254 202 202 / .78); box-shadow: inset 0 0 0 1px rgb(153 27 27 / .08); }
        .availability-fullcalendar .availability-busy-event { background: rgb(185 28 28); border: 0; border-radius: .55rem; box-shadow: 0 3px 8px rgb(185 28 28 / .22); font-size: .68rem; font-weight: 800; margin: 0 .35rem; padding: .15rem .35rem; }
        .availability-fullcalendar .fc-day-past { cursor: not-allowed; opacity: .42; }
        .availability-fullcalendar .fc-day-other { background: rgb(248 250 252 / .7); }
        .dark .availability-hero { background: radial-gradient(circle at 90% -20%, rgb(245 158 11 / .2), transparent 38%), linear-gradient(135deg, rgb(30 41 59 / .7), rgb(17 24 39)); }
        .dark .availability-stat, .dark .availability-side-card { border-color: rgb(255 255 255 / .1); background: rgb(17 24 39); box-shadow: 0 12px 30px rgb(0 0 0 / .12); }
        .dark .availability-stat-free { background: linear-gradient(145deg, rgb(6 78 59 / .28), rgb(17 24 39) 75%); }
        .dark .availability-stat-busy { background: linear-gradient(145deg, rgb(136 19 55 / .25), rgb(17 24 39) 75%); }
        .dark .availability-stat-icon { background: rgb(255 255 255 / .08); box-shadow: none; }
        .dark .availability-fullcalendar { --fc-border-color: rgb(255 255 255 / .1); --fc-button-bg-color: rgb(255 255 255 / .06); --fc-button-border-color: rgb(255 255 255 / .12); --fc-button-text-color: rgb(226 232 240); --fc-button-hover-bg-color: rgb(255 255 255 / .12); --fc-button-hover-border-color: rgb(255 255 255 / .2); --fc-today-bg-color: rgb(245 158 11 / .13); color: rgb(203 213 225); }
        .dark .availability-fullcalendar .fc-toolbar-title { color: white; }
        .dark .availability-fullcalendar .fc-col-header-cell { background: rgb(255 255 255 / .045); }
        .dark .availability-fullcalendar .fc-col-header-cell-cushion { color: rgb(148 163 184); }
        .dark .availability-fullcalendar .fc-daygrid-day-number { color: rgb(226 232 240); }
        .dark .availability-fullcalendar .availability-free-day:not(.fc-day-past) { background: rgb(134 239 172 / .24); box-shadow: inset 0 0 0 1px rgb(134 239 172 / .12); }
        .dark .availability-fullcalendar .availability-busy-day { background: rgb(252 165 165 / .24); box-shadow: inset 0 0 0 1px rgb(252 165 165 / .12); }
        .dark .availability-fullcalendar .availability-busy-event { background: rgb(248 113 113); color: rgb(69 10 10); box-shadow: 0 3px 8px rgb(248 113 113 / .2); }
        .dark .availability-fullcalendar .fc-daygrid-day:not(.fc-day-past):hover { background: rgb(245 158 11 / .12); box-shadow: inset 0 0 0 2px rgb(245 158 11 / .55); }
        .dark .availability-fullcalendar .fc-day-other { background: rgb(255 255 255 / .018); }
        @media (max-width: 640px) {
            .availability-fullcalendar .fc-toolbar { align-items: stretch; flex-wrap: wrap; }
            .availability-fullcalendar .fc-toolbar-chunk:nth-child(2) { order: -1; width: 100%; text-align: center; }
            .availability-fullcalendar .fc-toolbar-chunk:first-child { display: flex; width: 100%; }
            .availability-fullcalendar .fc-toolbar-chunk:first-child .fc-button-group, .availability-fullcalendar .fc-toolbar-chunk:first-child .fc-button { flex: 1; }
            .availability-fullcalendar .fc-daygrid-day { min-height: 4.4rem; }
            .availability-fullcalendar .fc-daygrid-day-number { font-size: .72rem; padding: .35rem; }
            .availability-fullcalendar .fc-day-today .fc-daygrid-day-number { height: 1.65rem; width: 1.65rem; margin: .18rem; }
            .availability-fullcalendar .fc-col-header-cell { padding: .55rem .1rem; }
            .availability-fullcalendar .fc-col-header-cell-cushion { font-size: .6rem; letter-spacing: .02em; }
            .availability-fullcalendar .availability-busy-event { font-size: 0; min-height: .38rem; margin: 0 .22rem; padding: 0; }
        }
    </style>
</x-filament-panels::page>
