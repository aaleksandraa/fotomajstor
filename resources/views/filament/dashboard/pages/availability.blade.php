<x-filament-panels::page>
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold capitalize text-gray-950 dark:text-white">{{ $this->monthLabel }}</h2>
                    <p class="mt-1 text-sm text-gray-500">Kliknite jedan ili više dana, zatim ih označite kao zauzete ili dostupne.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-filament::button color="gray" size="sm" icon="heroicon-o-chevron-left" wire:click="previousMonth">
                        Prethodni
                    </x-filament::button>
                    <x-filament::button color="gray" size="sm" wire:click="currentMonth">
                        Danas
                    </x-filament::button>
                    <x-filament::button color="gray" size="sm" icon="heroicon-o-chevron-right" icon-position="after" wire:click="nextMonth">
                        Sljedeći
                    </x-filament::button>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-3 gap-3">
                <div class="rounded-xl bg-emerald-50 p-3 dark:bg-emerald-950/40">
                    <p class="text-xs font-medium text-emerald-700 dark:text-emerald-300">Dostupnih dana</p>
                    <p class="mt-1 text-2xl font-semibold text-emerald-800 dark:text-emerald-200">{{ $this->summary['available'] }}</p>
                </div>
                <div class="rounded-xl bg-rose-50 p-3 dark:bg-rose-950/40">
                    <p class="text-xs font-medium text-rose-700 dark:text-rose-300">Zauzetih dana</p>
                    <p class="mt-1 text-2xl font-semibold text-rose-800 dark:text-rose-200">{{ $this->summary['busy'] }}</p>
                </div>
                <div class="rounded-xl bg-primary-50 p-3 dark:bg-primary-950/40">
                    <p class="text-xs font-medium text-primary-700 dark:text-primary-300">Odabrano</p>
                    <p class="mt-1 text-2xl font-semibold text-primary-800 dark:text-primary-200">{{ $this->summary['selected'] }}</p>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap gap-2 rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800">
                <x-filament::button color="danger" size="sm" icon="heroicon-o-x-circle" wire:click="markSelectedUnavailable">
                    Označi odabrane kao zauzete
                </x-filament::button>
                <x-filament::button color="success" size="sm" icon="heroicon-o-check-circle" wire:click="markSelectedAvailable">
                    Označi odabrane kao dostupne
                </x-filament::button>
                <x-filament::button color="gray" size="sm" wire:click="selectAvailableDays">
                    Odaberi sve dostupne
                </x-filament::button>
                <x-filament::button color="gray" size="sm" wire:click="selectBusyDays">
                    Odaberi sve zauzete
                </x-filament::button>
                <x-filament::button color="gray" size="sm" wire:click="clearSelection">
                    Poništi odabir
                </x-filament::button>
            </div>

            <div class="mt-6 grid grid-cols-7 gap-1.5 text-center text-xs font-medium text-gray-400">
                @foreach (['Pon','Uto','Sri','Čet','Pet','Sub','Ned'] as $d)
                    <div class="py-1">{{ $d }}</div>
                @endforeach
            </div>

            <div class="mt-2 grid grid-cols-7 gap-1.5">
                @for ($i = 0; $i < $this->leading; $i++)<div></div>@endfor

                @foreach ($this->days as $day)
                    @php $selected = in_array($day['date'], $selectedDates, true); @endphp
                    <button
                        type="button"
                        @disabled($day['past'])
                        @unless($day['past']) wire:click="selectDate('{{ $day['date'] }}')" @endunless
                        wire:key="day-{{ $day['date'] }}"
                        title="{{ $day['available'] ? 'Dostupan' : 'Zauzet' }} — {{ \Illuminate\Support\Carbon::parse($day['date'])->format('d.m.Y.') }}"
                        class="relative flex h-14 flex-col items-center justify-center rounded-xl border text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
                            @if ($day['past']) cursor-not-allowed border-transparent bg-gray-50 text-gray-300
                            @elseif ($selected) border-primary-500 bg-primary-100 text-primary-800 ring-2 ring-primary-400 dark:bg-primary-900/50 dark:text-primary-100
                            @elseif ($day['available']) border-emerald-100 bg-emerald-50 text-emerald-700 hover:border-emerald-300 hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300
                            @else border-rose-200 bg-rose-100 text-rose-700 hover:border-rose-400 hover:bg-rose-200 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300 @endif">
                        <span>{{ $day['day'] }}</span>
                        @unless ($day['past'])
                            <span class="mt-0.5 text-[9px] font-medium uppercase tracking-wide opacity-75">
                                {{ $day['available'] ? 'slobodan' : 'zauzet' }}
                            </span>
                        @endunless
                        @if ($selected)
                            <span class="absolute right-1 top-1 h-2 w-2 rounded-full bg-primary-600"></span>
                        @endif
                    </button>
                @endforeach
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-5 text-xs text-gray-500">
                <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-emerald-100 ring-1 ring-emerald-300"></span> Dostupan</span>
                <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-rose-100 ring-1 ring-rose-300"></span> Zauzet</span>
                <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-primary-100 ring-2 ring-primary-400"></span> Odabran za izmjenu</span>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h3 class="font-semibold text-gray-950 dark:text-white">Naredni zauzeti termini</h3>
                <p class="mt-1 text-sm text-gray-500">Posjetioci ove datume vide kao zauzete na vašem javnom profilu.</p>

                @if ($this->upcomingBusy)
                    <div class="mt-4 space-y-2">
                        @foreach ($this->upcomingBusy as $busy)
                            <button type="button" wire:click="toggleDate('{{ $busy['date'] }}')" class="flex w-full items-center justify-between rounded-lg bg-rose-50 px-3 py-2 text-left text-sm text-rose-700 transition hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-300">
                                <span class="capitalize">{{ $busy['label'] }}</span>
                                <span class="text-xs">Oslobodi</span>
                            </button>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                        Nemate označenih zauzetih termina.
                    </p>
                @endif
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h3 class="font-semibold text-gray-950 dark:text-white">Brze izmjene mjeseca</h3>
                <div class="mt-4 grid gap-2">
                    <x-filament::button color="danger" size="sm" wire:click="markMonthUnavailable">
                        Cijeli mjesec zauzet
                    </x-filament::button>
                    <x-filament::button color="success" size="sm" wire:click="markMonthAvailable">
                        Cijeli mjesec dostupan
                    </x-filament::button>
                    <x-filament::button color="gray" size="sm" tag="a" :href="route('photographer.show', $profile->slug)" target="_blank">
                        Pregledaj javni profil
                    </x-filament::button>
                </div>
            </div>
        </aside>
    </div>
</x-filament-panels::page>
