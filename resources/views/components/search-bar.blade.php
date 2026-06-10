@props(['filters' => []])

@php
    $categories = \App\Models\Category::active()->ordered()->get(['id', 'name', 'slug']);
    $countries = \App\Models\Country::active()->ordered()->with(['cities' => fn ($q) => $q->active()->ordered()])->get();
    $dateFilter = $filters['date'] ?? '';
    $dateDisplay = '';

    if ($dateFilter) {
        try {
            $dateDisplay = \Illuminate\Support\Carbon::parse($dateFilter)->format('d.m.Y.');
        } catch (\Throwable $e) {
            $dateDisplay = $dateFilter;
        }
    }

    $categoryOptions = $categories
        ->map(fn ($category) => ['value' => $category->slug, 'label' => $category->name])
        ->prepend(['value' => '', 'label' => __('Sve kategorije')])
        ->values();

    $countryOptions = $countries
        ->map(fn ($country) => ['value' => $country->slug, 'label' => $country->name])
        ->prepend(['value' => '', 'label' => __('Sve države')])
        ->values();

    $cityOptions = $countries
        ->flatMap(fn ($country) => $country->cities->map(fn ($city) => [
            'value' => $city->slug,
            'label' => $city->name,
            'meta' => $country->name,
            'country' => $country->slug,
        ]))
        ->prepend(['value' => '', 'label' => __('Svi gradovi'), 'meta' => '', 'country' => ''])
        ->values();
@endphp

<form action="{{ localized_route('search') }}" method="GET"
      x-data="{
          category: {{ Illuminate\Support\Js::from($filters['category'] ?? '') }},
          country: {{ Illuminate\Support\Js::from($filters['country'] ?? '') }},
          city: {{ Illuminate\Support\Js::from($filters['city'] ?? '') }},
          date: {{ Illuminate\Support\Js::from($dateFilter) }},
          displayDate: {{ Illuminate\Support\Js::from($dateDisplay) }},
          open: null,
          calendarOpen: false,
          search: { category: '', country: '', city: '' },
          categories: {{ Illuminate\Support\Js::from($categoryOptions) }},
          countries: {{ Illuminate\Support\Js::from($countryOptions) }},
          cities: {{ Illuminate\Support\Js::from($cityOptions) }},
          weekdays: {{ Illuminate\Support\Js::from([__('Po'), __('Ut'), __('Sr'), __('Če'), __('Pe'), __('Su'), __('Ne')]) }},
          monthNames: {{ Illuminate\Support\Js::from([__('Januar'), __('Februar'), __('Mart'), __('April'), __('Maj'), __('Juni'), __('Juli'), __('August'), __('Septembar'), __('Oktobar'), __('Novembar'), __('Decembar')]) }},
          calendarMonth: null,
          calendarYear: null,
          init() {
              const base = this.date ? new Date(this.date + 'T12:00:00') : new Date();
              this.calendarMonth = base.getMonth();
              this.calendarYear = base.getFullYear();
          },
          selectedLabel(options, value) {
              return (options.find((option) => option.value === value) || options[0]).label;
          },
          toggle(type) {
              this.calendarOpen = false;

              if (this.open === type) {
                  this.open = null;
                  return;
              }

              this.open = type;
              this.search[type] = '';
              this.$nextTick(() => this.$refs[type + 'Search']?.focus());
          },
          optionMatches(option, type) {
              const term = this.search[type].trim().toLowerCase();

              if (! term) {
                  return true;
              }

              return [option.label, option.meta].filter(Boolean).some((value) => value.toLowerCase().includes(term));
          },
          filteredCategories() {
              return this.categories.filter((option) => this.optionMatches(option, 'category'));
          },
          filteredCountries() {
              return this.countries.filter((option) => this.optionMatches(option, 'country'));
          },
          filteredCities() {
              return this.cities.filter((option) => (! option.value || ! this.country || option.country === this.country) && this.optionMatches(option, 'city'));
          },
          choose(type, option) {
              this[type] = option.value;
              this.search[type] = '';

              if (type === 'country') {
                  const cityOption = this.cities.find((item) => item.value === this.city);
                  if (cityOption && cityOption.country && cityOption.country !== option.value) {
                      this.city = '';
                  }
              }

              this.open = null;
          },
          calendarDays() {
              const first = new Date(this.calendarYear, this.calendarMonth, 1);
              const daysInMonth = new Date(this.calendarYear, this.calendarMonth + 1, 0).getDate();
              const lead = (first.getDay() + 6) % 7;
              const days = [];

              for (let i = 0; i < lead; i++) {
                  days.push(null);
              }

              for (let day = 1; day <= daysInMonth; day++) {
                  days.push(day);
              }

              return days;
          },
          formatDate(day) {
              const month = String(this.calendarMonth + 1).padStart(2, '0');
              const dateDay = String(day).padStart(2, '0');
              return `${dateDay}.${month}.${this.calendarYear}.`;
          },
          isoDate(day) {
              const month = String(this.calendarMonth + 1).padStart(2, '0');
              const dateDay = String(day).padStart(2, '0');
              return `${this.calendarYear}-${month}-${dateDay}`;
          },
          chooseDate(day) {
              this.date = this.isoDate(day);
              this.displayDate = this.formatDate(day);
              this.calendarOpen = false;
          },
          clearDate() {
              this.date = '';
              this.displayDate = '';
              this.calendarOpen = false;
          },
          isSelected(day) {
              return this.date === this.isoDate(day);
          },
          previousMonth() {
              if (this.calendarMonth === 0) {
                  this.calendarMonth = 11;
                  this.calendarYear--;
                  return;
              }

              this.calendarMonth--;
          },
          nextMonth() {
              if (this.calendarMonth === 11) {
                  this.calendarMonth = 0;
                  this.calendarYear++;
                  return;
              }

              this.calendarMonth++;
          },
      }"
      class="relative z-40 grid w-full max-w-full grid-cols-1 gap-3 rounded-2xl border border-white/80 bg-white/95 p-3 text-left shadow-xl shadow-ink-900/5 ring-1 ring-ink-100/80 backdrop-blur md:grid-cols-[1.15fr_1fr_1fr_0.95fr_auto] md:items-end">
    <input type="hidden" name="category" x-model="category">
    <input type="hidden" name="country" x-model="country">
    <input type="hidden" name="city" x-model="city">
    <input type="hidden" name="date" x-model="date">

    <div class="relative min-w-0 rounded-xl border border-ink-100 bg-ink-50/80 p-3 transition focus-within:border-accent-300 focus-within:bg-white focus-within:ring-2 focus-within:ring-accent-100" @click.outside="if (open === 'category') open = null">
        <label class="mb-2 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wide text-ink-500">
            <svg class="h-4 w-4 text-accent-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 7h18M6 12h12M9 17h6"/></svg>
            {{ __('Kategorija') }}
        </label>
        <button type="button" @click="toggle('category')" class="flex w-full items-center justify-between gap-3 rounded-lg py-1.5 text-left text-sm font-medium text-ink-900">
            <span class="truncate" x-text="selectedLabel(categories, category)"></span>
            <svg class="h-4 w-4 shrink-0 text-ink-400 transition" :class="open === 'category' ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
        </button>
        <div x-show="open === 'category'" x-transition x-cloak class="absolute left-0 right-0 top-full z-50 mt-2 rounded-xl border border-ink-100 bg-white p-2 shadow-xl shadow-ink-900/10">
            <input type="text" x-ref="categorySearch" x-model="search.category" @keydown.escape="open = null" placeholder="{{ __('Upišite kategoriju...') }}" class="mb-2 w-full rounded-lg border border-ink-100 bg-ink-50 px-3 py-2 text-sm text-ink-900 placeholder:text-ink-400 focus:border-accent-300 focus:bg-white focus:ring-2 focus:ring-accent-100">
            <div class="max-h-56 overflow-y-auto">
                <template x-for="option in filteredCategories()" :key="option.value">
                    <button type="button" @click="choose('category', option)" class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-left text-sm text-ink-700 transition hover:bg-ink-50 hover:text-ink-900" :class="category === option.value ? 'bg-ink-900 text-white hover:bg-ink-900 hover:text-white' : ''">
                        <span x-text="option.label"></span>
                        <svg x-show="category === option.value" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 5 5L20 7"/></svg>
                    </button>
                </template>
                <div x-show="filteredCategories().length === 0" class="px-3 py-3 text-sm text-ink-400">{{ __('Nema rezultata') }}</div>
            </div>
        </div>
    </div>

    <div class="relative min-w-0 rounded-xl border border-ink-100 bg-ink-50/80 p-3 transition focus-within:border-accent-300 focus-within:bg-white focus-within:ring-2 focus-within:ring-accent-100" @click.outside="if (open === 'country') open = null">
        <label class="mb-2 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wide text-ink-500">
            <svg class="h-4 w-4 text-accent-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18a15 15 0 0 1 0-18Z"/></svg>
            {{ __('Država') }}
        </label>
        <button type="button" @click="toggle('country')" class="flex w-full items-center justify-between gap-3 rounded-lg py-1.5 text-left text-sm font-medium text-ink-900">
            <span class="truncate" x-text="selectedLabel(countries, country)"></span>
            <svg class="h-4 w-4 shrink-0 text-ink-400 transition" :class="open === 'country' ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
        </button>
        <div x-show="open === 'country'" x-transition x-cloak class="absolute left-0 right-0 top-full z-50 mt-2 rounded-xl border border-ink-100 bg-white p-2 shadow-xl shadow-ink-900/10">
            <input type="text" x-ref="countrySearch" x-model="search.country" @keydown.escape="open = null" placeholder="{{ __('Upišite državu...') }}" class="mb-2 w-full rounded-lg border border-ink-100 bg-ink-50 px-3 py-2 text-sm text-ink-900 placeholder:text-ink-400 focus:border-accent-300 focus:bg-white focus:ring-2 focus:ring-accent-100">
            <div class="max-h-56 overflow-y-auto">
                <template x-for="option in filteredCountries()" :key="option.value">
                    <button type="button" @click="choose('country', option)" class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-left text-sm text-ink-700 transition hover:bg-ink-50 hover:text-ink-900" :class="country === option.value ? 'bg-ink-900 text-white hover:bg-ink-900 hover:text-white' : ''">
                        <span x-text="option.label"></span>
                        <svg x-show="country === option.value" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 5 5L20 7"/></svg>
                    </button>
                </template>
                <div x-show="filteredCountries().length === 0" class="px-3 py-3 text-sm text-ink-400">{{ __('Nema rezultata') }}</div>
            </div>
        </div>
    </div>

    <div class="relative min-w-0 rounded-xl border border-ink-100 bg-ink-50/80 p-3 transition focus-within:border-accent-300 focus-within:bg-white focus-within:ring-2 focus-within:ring-accent-100" @click.outside="if (open === 'city') open = null">
        <label class="mb-2 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wide text-ink-500">
            <svg class="h-4 w-4 text-accent-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 21s-7-5.2-7-11a7 7 0 1 1 14 0c0 5.8-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
            {{ __('Grad / mjesto') }}
        </label>
        <button type="button" @click="toggle('city')" class="flex w-full items-center justify-between gap-3 rounded-lg py-1.5 text-left text-sm font-medium text-ink-900">
            <span class="truncate" x-text="selectedLabel(filteredCities(), city)"></span>
            <svg class="h-4 w-4 shrink-0 text-ink-400 transition" :class="open === 'city' ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
        </button>
        <div x-show="open === 'city'" x-transition x-cloak class="absolute left-0 right-0 top-full z-50 mt-2 rounded-xl border border-ink-100 bg-white p-2 shadow-xl shadow-ink-900/10">
            <input type="text" x-ref="citySearch" x-model="search.city" @keydown.escape="open = null" placeholder="{{ __('Upišite grad...') }}" class="mb-2 w-full rounded-lg border border-ink-100 bg-ink-50 px-3 py-2 text-sm text-ink-900 placeholder:text-ink-400 focus:border-accent-300 focus:bg-white focus:ring-2 focus:ring-accent-100">
            <div class="max-h-56 overflow-y-auto">
                <template x-for="option in filteredCities()" :key="option.value">
                    <button type="button" @click="choose('city', option)" class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-left text-sm text-ink-700 transition hover:bg-ink-50 hover:text-ink-900" :class="city === option.value ? 'bg-ink-900 text-white hover:bg-ink-900 hover:text-white' : ''">
                        <span class="min-w-0">
                            <span class="block truncate" x-text="option.label"></span>
                            <span x-show="option.meta" class="block truncate text-xs opacity-70" x-text="option.meta"></span>
                        </span>
                        <svg x-show="city === option.value" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 5 5L20 7"/></svg>
                    </button>
                </template>
                <div x-show="filteredCities().length === 0" class="px-3 py-3 text-sm text-ink-400">{{ __('Nema rezultata') }}</div>
            </div>
        </div>
    </div>

    <div class="relative min-w-0 rounded-xl border border-ink-100 bg-ink-50/80 p-3 transition focus-within:border-accent-300 focus-within:bg-white focus-within:ring-2 focus-within:ring-accent-100" @click.outside="calendarOpen = false">
        <label class="mb-2 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wide text-ink-500">
            <svg class="h-4 w-4 text-accent-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/></svg>
            {{ __('Datum') }}
        </label>
        <button type="button" @click="calendarOpen = ! calendarOpen; open = null" class="flex w-full items-center justify-between gap-3 rounded-lg py-1.5 text-left text-sm font-medium text-ink-900">
            <span class="truncate" :class="displayDate ? 'text-ink-900' : 'text-ink-400'" x-text="displayDate || {{ Illuminate\Support\Js::from(__('Odaberite datum')) }}"></span>
            <svg class="h-4 w-4 shrink-0 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/></svg>
        </button>

        <div x-show="calendarOpen" x-transition x-cloak class="absolute left-0 right-0 top-full z-50 mt-2 rounded-xl border border-ink-100 bg-white p-3 shadow-xl shadow-ink-900/10 md:left-auto md:w-80">
            <div class="flex items-center justify-between">
                <button type="button" @click="previousMonth()" class="rounded-lg p-2 text-ink-500 transition hover:bg-ink-50 hover:text-ink-900" aria-label="{{ __('Prethodni mjesec') }}">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
                </button>
                <div class="text-sm font-semibold text-ink-900" x-text="monthNames[calendarMonth] + ' ' + calendarYear"></div>
                <button type="button" @click="nextMonth()" class="rounded-lg p-2 text-ink-500 transition hover:bg-ink-50 hover:text-ink-900" aria-label="{{ __('Sljedeći mjesec') }}">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>

            <div class="mt-3 grid grid-cols-7 gap-1 text-center text-[11px] font-semibold uppercase text-ink-400">
                <template x-for="weekday in weekdays" :key="weekday">
                    <div x-text="weekday"></div>
                </template>
            </div>

            <div class="mt-2 grid grid-cols-7 gap-1">
                <template x-for="(day, index) in calendarDays()" :key="index">
                    <div>
                        <button x-show="day" type="button" @click="chooseDate(day)" class="flex h-9 w-full items-center justify-center rounded-lg text-sm font-medium text-ink-700 transition hover:bg-accent-50 hover:text-accent-700" :class="isSelected(day) ? 'bg-ink-900 text-white hover:bg-ink-900 hover:text-white' : ''" x-text="day"></button>
                    </div>
                </template>
            </div>

            <div class="mt-3 flex items-center justify-between border-t border-ink-100 pt-3">
                <button type="button" @click="clearDate()" class="text-xs font-medium text-ink-500 transition hover:text-ink-900">{{ __('Očisti datum') }}</button>
                <span class="text-xs text-ink-400" x-text="displayDate || {{ Illuminate\Support\Js::from(__('Nije odabrano')) }}"></span>
            </div>
        </div>
    </div>

    <button type="submit" class="btn-primary h-14 w-full rounded-xl px-6 shadow-lg shadow-ink-900/10 md:w-auto">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3-3"/></svg>
        {{ __('Pretraži') }}
    </button>
</form>
