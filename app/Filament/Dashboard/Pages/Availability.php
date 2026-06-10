<?php

namespace App\Filament\Dashboard\Pages;

use App\Models\PhotographerProfile;
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

    public function toggle(string $date): void
    {
        $existing = $this->profile->unavailableDates()->whereDate('date', $date)->first();

        if ($existing) {
            $existing->delete();
        } else {
            $this->profile->unavailableDates()->create(['date' => $date, 'note' => 'Zauzeto']);
        }
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
