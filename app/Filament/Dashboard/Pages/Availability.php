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

    public function toggleDate(string $date): void
    {
        validator(
            ['date' => $date],
            ['date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today']],
        )->validate();

        $existing = $this->profile->unavailableDates()->whereDate('date', $date)->first();

        if ($existing) {
            $existing->delete();
        } else {
            $this->profile->unavailableDates()->create(['date' => $date, 'note' => 'Zauzeto']);
        }
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

    public function currentMonth(): void
    {
        $this->month = now()->startOfMonth()->format('Y-m');
    }

    public function previousMonth(): void
    {
        $this->month = Carbon::createFromFormat('Y-m', $this->month)->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = Carbon::createFromFormat('Y-m', $this->month)->addMonth()->format('Y-m');
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
}
