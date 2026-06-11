@assets
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.20/locales-all.global.min.js"></script>
    <script src="{{ asset('js/availability-calendar.js') }}"></script>
@endassets

<x-filament-panels::page>
    <div
        x-data="availabilityCalendar({
            initialDate: @js($month . '-01'),
            today: @js(today()->toDateString()),
            busyDates: @js($this->busyDates),
        })"
        class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]"
    >
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-5 py-5 dark:border-gray-800 sm:px-7">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-primary-600">Kalendar dostupnosti</p>
                        <h2 class="mt-1 text-xl font-semibold text-gray-950 dark:text-white">Upravljajte zauzetim terminima</h2>
                        <p class="mt-1 text-sm text-gray-500">Kliknite na dan i potvrdite promjenu statusa.</p>
                    </div>
                    <div class="flex gap-3">
                        <div class="rounded-xl bg-emerald-50 px-4 py-2 dark:bg-emerald-950/40">
                            <p class="text-xs text-emerald-700 dark:text-emerald-300">Dostupno</p>
                            <p class="text-xl font-semibold text-emerald-800 dark:text-emerald-200">{{ $this->summary['available'] }}</p>
                        </div>
                        <div class="rounded-xl bg-rose-50 px-4 py-2 dark:bg-rose-950/40">
                            <p class="text-xs text-rose-700 dark:text-rose-300">Zauzeto</p>
                            <p class="text-xl font-semibold text-rose-800 dark:text-rose-200">{{ $this->summary['busy'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-7">
                <div wire:ignore x-ref="calendar" class="availability-fullcalendar"></div>
            </div>

            <div class="flex flex-wrap items-center gap-5 border-t border-gray-100 px-5 py-4 text-xs text-gray-500 dark:border-gray-800 sm:px-7">
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-emerald-500"></span> Slobodan termin</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-rose-500"></span> Zauzet termin</span>
                <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-primary-500"></span> Današnji datum</span>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h3 class="font-semibold text-gray-950 dark:text-white">Naredni zauzeti termini</h3>
                <p class="mt-1 text-sm text-gray-500">Kliknite termin da ga odmah označite kao dostupnog.</p>

                @if ($this->upcomingBusy)
                    <div class="mt-4 space-y-2">
                        @foreach ($this->upcomingBusy as $busy)
                            <button
                                type="button"
                                @click="openDate(@js($busy['date']), true)"
                                class="flex w-full items-center justify-between rounded-xl border border-rose-100 bg-rose-50 px-3 py-2.5 text-left text-sm text-rose-700 transition hover:border-rose-300 hover:bg-rose-100 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300"
                            >
                                <span class="capitalize">{{ $busy['label'] }}</span>
                                <span class="text-xs font-medium">Promijeni</span>
                            </button>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                        Nemate označenih zauzetih termina.
                    </p>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h3 class="font-semibold text-gray-950 dark:text-white">Brze izmjene mjeseca</h3>
                <p class="mt-1 text-sm text-gray-500">Ove akcije primjenjuju se na mjesec prikazan u kalendaru.</p>
                <div class="mt-4 grid gap-2">
                    <x-filament::button color="danger" size="sm" wire:click="markMonthUnavailable" @click="markVisibleMonth(true)">
                        Cijeli mjesec zauzet
                    </x-filament::button>
                    <x-filament::button color="success" size="sm" wire:click="markMonthAvailable" @click="markVisibleMonth(false)">
                        Cijeli mjesec dostupan
                    </x-filament::button>
                    <x-filament::button color="gray" size="sm" tag="a" :href="route('photographer.show', $profile->slug)" target="_blank">
                        Pregledaj javni profil
                    </x-filament::button>
                </div>
            </div>
        </aside>

        <div
            x-show="modalOpen"
            x-cloak
            x-transition.opacity
            @keydown.escape.window="modalOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/60 p-4 backdrop-blur-sm"
        >
            <div @click.outside="modalOpen = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em]" :class="selectedBusy ? 'text-rose-600' : 'text-emerald-600'" x-text="selectedBusy ? 'Zauzet termin' : 'Slobodan termin'"></p>
                        <h3 class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white" x-text="selectedLabel"></h3>
                    </div>
                    <button type="button" @click="modalOpen = false" class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800">
                        <span class="sr-only">Zatvori</span>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <p class="mt-4 text-sm leading-6 text-gray-600 dark:text-gray-300" x-text="selectedBusy
                    ? 'Ovaj datum je trenutno označen kao zauzet. Možete ga ponovo učiniti dostupnim posjetiocima.'
                    : 'Ovaj datum je trenutno slobodan. Označite ga kao zauzet ako već imate rezervaciju.'"></p>

                <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <x-filament::button color="gray" @click="modalOpen = false">Odustani</x-filament::button>
                    <button
                        type="button"
                        @click="applyDateStatus()"
                        class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold text-white transition"
                        :class="selectedBusy ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-rose-600 hover:bg-rose-500'"
                        x-text="selectedBusy ? 'Označi kao dostupno' : 'Označi kao zauzeto'"
                    ></button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .availability-fullcalendar {
            --fc-border-color: rgb(229 231 235);
            --fc-button-bg-color: rgb(255 255 255);
            --fc-button-border-color: rgb(229 231 235);
            --fc-button-text-color: rgb(55 65 81);
            --fc-button-hover-bg-color: rgb(249 250 251);
            --fc-button-hover-border-color: rgb(209 213 219);
            --fc-button-active-bg-color: rgb(245 158 11);
            --fc-button-active-border-color: rgb(245 158 11);
            --fc-today-bg-color: rgb(254 243 199 / 0.55);
        }
        .availability-fullcalendar .fc-toolbar-title { font-size: 1.2rem; font-weight: 700; text-transform: capitalize; }
        .availability-fullcalendar .fc-button { border-radius: .65rem; box-shadow: none; font-weight: 600; padding: .45rem .75rem; text-transform: none; }
        .availability-fullcalendar .fc-col-header-cell { background: rgb(249 250 251); padding: .7rem .25rem; }
        .availability-fullcalendar .fc-col-header-cell-cushion { color: rgb(107 114 128); font-size: .75rem; font-weight: 700; text-transform: uppercase; }
        .availability-fullcalendar .fc-daygrid-day { cursor: pointer; transition: background-color .18s ease, box-shadow .18s ease; }
        .availability-fullcalendar .fc-daygrid-day:hover { background: rgb(249 250 251); box-shadow: inset 0 0 0 2px rgb(245 158 11 / .35); }
        .availability-fullcalendar .fc-daygrid-day-number { color: rgb(31 41 55); font-weight: 700; padding: .6rem; }
        .availability-fullcalendar .availability-free-day:not(.fc-day-past) { background: rgb(240 253 244 / .65); }
        .availability-fullcalendar .availability-busy-day { background: rgb(255 241 242 / .85); }
        .availability-fullcalendar .availability-busy-event { background: rgb(225 29 72); border: 0; border-radius: .5rem; font-size: .7rem; font-weight: 700; margin: 0 .3rem; padding: .1rem .3rem; }
        .availability-fullcalendar .fc-day-past { cursor: not-allowed; opacity: .45; }
        .dark .availability-fullcalendar { --fc-border-color: rgb(55 65 81); --fc-button-bg-color: rgb(31 41 55); --fc-button-border-color: rgb(75 85 99); --fc-button-text-color: rgb(229 231 235); --fc-button-hover-bg-color: rgb(55 65 81); }
        .dark .availability-fullcalendar .fc-col-header-cell { background: rgb(31 41 55); }
        .dark .availability-fullcalendar .fc-daygrid-day-number { color: rgb(229 231 235); }
        .dark .availability-fullcalendar .availability-free-day:not(.fc-day-past) { background: rgb(6 78 59 / .18); }
        .dark .availability-fullcalendar .availability-busy-day { background: rgb(136 19 55 / .2); }
        @media (max-width: 640px) {
            .availability-fullcalendar .fc-toolbar { align-items: flex-start; flex-direction: column; gap: .75rem; }
            .availability-fullcalendar .fc-daygrid-day-number { padding: .35rem; }
            .availability-fullcalendar .availability-busy-event { font-size: 0; min-height: .4rem; padding: 0; }
        }
    </style>
</x-filament-panels::page>
