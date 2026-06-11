<?php

namespace App\Filament\Dashboard\Pages;

use App\Models\PhotographerProfile;
use App\Models\UnavailableDate;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class Availability extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Dostupnost';

    protected static ?string $title = 'Kalendar dostupnosti';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.dashboard.pages.availability';

    public string $month;

    public PhotographerProfile $profile;

    public function mount(): void
    {
        $this->profile = auth()->user()->photographerProfile()->firstOrFail();
        $this->month = now()->startOfMonth()->format('Y-m');
    }

    public function setDateStatus(string $date, bool $busy): void
    {
        $this->validateDate($date);

        if ($busy) {
            $this->profile->unavailableDates()->updateOrCreate(
                ['date' => $date],
                ['note' => 'Zauzeto'],
            );
        } else {
            $this->profile->unavailableDates()->whereDate('date', $date)->delete();
        }

        Notification::make()
            ->title($busy ? 'Dan je označen kao zauzet' : 'Dan je označen kao dostupan')
            ->success()
            ->send();
    }

    public function markMonthUnavailable(): void
    {
        $dates = collect($this->days)
            ->reject(fn (array $day): bool => $day['past'])
            ->map(fn (array $day): array => [
                'photographer_profile_id' => $this->profile->id,
                'date' => $day['date'],
                'note' => 'Zauzeto',
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if ($dates === []) {
            Notification::make()->title('Nema budućih dana za označavanje')->warning()->send();

            return;
        }

        UnavailableDate::upsert($dates, ['photographer_profile_id', 'date'], ['note', 'updated_at']);

        Notification::make()->title('Mjesec je označen kao zauzet')->success()->send();
    }

    public function markMonthAvailable(): void
    {
        $start = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $this->profile->unavailableDates()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->delete();

        Notification::make()->title('Mjesec je označen kao dostupan')->success()->send();
    }

    public function setMonthFromCalendar(string $month): void
    {
        validator(['month' => $month], ['month' => ['required', 'date_format:Y-m']])->validate();
        $this->month = $month;
    }

    /** @return array<int, array{date: string, day: int, available: bool, past: bool}> */
    public function getDaysProperty(): array
    {
        $start = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $busy = $this->profile->unavailableDates()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')->map(fn ($d) => Carbon::parse($d)->toDateString())->all();

        $days = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $days[] = [
                'date' => $d->toDateString(),
                'day' => $d->day,
                'available' => ! in_array($d->toDateString(), $busy, true),
                'past' => $d->isPast() && ! $d->isToday(),
            ];
        }

        return $days;
    }

    public function getLeadingProperty(): int
    {
        return Carbon::createFromFormat('Y-m', $this->month)->startOfMonth()->dayOfWeekIso - 1;
    }

    public function getMonthLabelProperty(): string
    {
        return Carbon::createFromFormat('Y-m', $this->month)->translatedFormat('F Y.');
    }

    /** @return array{available: int, busy: int} */
    public function getSummaryProperty(): array
    {
        $futureDays = collect($this->days)->reject(fn (array $day): bool => $day['past']);

        return [
            'available' => $futureDays->where('available', true)->count(),
            'busy' => $futureDays->where('available', false)->count(),
        ];
    }

    /** @return array<int, string> */
    public function getBusyDatesProperty(): array
    {
        return $this->profile->unavailableDates()
            ->whereDate('date', '>=', today())
            ->orderBy('date')
            ->pluck('date')
            ->map(fn ($date): string => Carbon::parse($date)->toDateString())
            ->all();
    }

    /** @return array<int, array{date: string, label: string}> */
    public function getUpcomingBusyProperty(): array
    {
        return $this->profile->unavailableDates()
            ->whereDate('date', '>=', today())
            ->orderBy('date')
            ->limit(8)
            ->get()
            ->map(fn (UnavailableDate $unavailable): array => [
                'date' => $unavailable->date->toDateString(),
                'label' => $unavailable->date->translatedFormat('D, d.m.Y.'),
            ])
            ->all();
    }

    private function validateDate(string $date): void
    {
        validator(
            ['date' => $date],
            ['date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today']],
        )->validate();
    }
}
