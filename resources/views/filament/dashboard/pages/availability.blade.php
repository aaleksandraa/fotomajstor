<x-filament-panels::page>
    <div class="mx-auto w-full max-w-2xl">
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
            <div class="mb-2 flex items-center justify-between">
                <x-filament::button color="gray" size="sm" icon="heroicon-o-chevron-left" wire:click="previousMonth">Prethodni</x-filament::button>
                <h2 class="text-lg font-semibold capitalize text-gray-900 dark:text-white">{{ $this->monthLabel }}</h2>
                <x-filament::button color="gray" size="sm" icon="heroicon-o-chevron-right" icon-position="after" wire:click="nextMonth">Sljedeći</x-filament::button>
            </div>

            <p class="mb-4 text-sm text-gray-500">Kliknite na dan da ga označite kao zauzet (ili da ga ponovo oslobodite). Podrazumijevano ste dostupni svaki dan.</p>

            <div class="mb-5 flex flex-wrap gap-2">
                <x-filament::button color="danger" size="sm" wire:click="markMonthUnavailable">
                    Označi cijeli mjesec kao zauzet
                </x-filament::button>
                <x-filament::button color="success" size="sm" wire:click="markMonthAvailable">
                    Označi cijeli mjesec kao dostupan
                </x-filament::button>
                <x-filament::button color="gray" size="sm" wire:click="currentMonth">
                    Trenutni mjesec
                </x-filament::button>
            </div>

            <div class="grid grid-cols-7 gap-1.5 text-center text-xs font-medium text-gray-400">
                @foreach (['Pon','Uto','Sri','Čet','Pet','Sub','Ned'] as $d)
                    <div>{{ $d }}</div>
                @endforeach
            </div>

            <div class="mt-2 grid grid-cols-7 gap-1.5">
                @for ($i = 0; $i < $this->leading; $i++)<div></div>@endfor

                @foreach ($this->days as $day)
                    <button
                        @disabled($day['past'])
                        @unless($day['past']) wire:click="toggleDate('{{ $day['date'] }}')" @endunless
                        wire:key="day-{{ $day['date'] }}"
                        class="flex h-12 items-center justify-center rounded-lg text-sm font-medium transition
                            @if ($day['past']) cursor-not-allowed bg-gray-50 text-gray-300 line-through
                            @elseif ($day['available']) bg-emerald-50 text-emerald-700 hover:bg-emerald-100
                            @else bg-rose-100 text-rose-700 line-through hover:bg-rose-200 @endif">
                        {{ $day['day'] }}
                    </button>
                @endforeach
            </div>

            <div class="mt-5 flex items-center gap-5 text-xs text-gray-500">
                <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-emerald-100"></span> Dostupan</span>
                <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-rose-100"></span> Zauzet</span>
            </div>
        </div>
    </div>
</x-filament-panels::page>
