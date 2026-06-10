<?php

namespace App\Http\Controllers;

use App\Enums\ServiceType;
use App\Models\Category;
use App\Models\CategoryAlias;
use App\Models\City;
use App\Models\PhotographerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['country', 'city', 'category', 'date', 'service_type', 'profile_type', 'q', 'sort']);
        $filters['date'] = $this->normalizeDateFilter($filters['date'] ?? null);
        if (! empty($filters['category'])) {
            $filters['category'] = CategoryAlias::with('category')
                ->where('slug', $filters['category'])
                ->first()?->category?->slug ?? $filters['category'];
        }

        $query = PhotographerProfile::search($filters)
            ->with(['primaryCity', 'categories', 'albums' => fn ($q) => $q->active()->with('images')])
            ->withCount(['unavailableDates as busy_today_count' => fn ($q) => $q->whereDate('date', today())]);

        match ($filters['sort'] ?? 'relevant') {
            'newest' => $query->latest('created_at'),
            'experience' => $query->orderByDesc('experience_years'),
            'popular' => $query->orderByDesc('profile_views'),
            default => $query->ranked(),
        };

        $photographers = $query->paginate(12)->withQueryString();

        $categories = Category::active()->ordered()->get();
        $cities = City::active()->ordered()->get();

        $seo = [
            'title' => 'Fotografi i videografi | FotoMreža',
            'description' => 'Pretražite fotografe i videografe po državi, gradu, kategoriji i datumu dostupnosti. Pogledajte portfolio i kontaktirajte direktno.',
            'canonical' => localized_route('search'),
            'robots' => $request->query() ? 'noindex, follow' : 'index, follow',
        ];

        return view('public.search', [
            'photographers' => $photographers,
            'filters' => $filters,
            'categories' => $categories,
            'cities' => $cities,
            'serviceTypes' => ServiceType::options(),
            'total' => $photographers->total(),
            'seo' => $seo,
        ]);
    }

    protected function normalizeDateFilter(?string $date): ?string
    {
        $date = trim((string) $date);

        if ($date === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{4})\.?$/', $date, $match)) {
            $day = (int) $match[1];
            $month = (int) $match[2];
            $year = (int) $match[3];

            return checkdate($month, $day, $year)
                ? Carbon::create($year, $month, $day)->toDateString()
                : null;
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $match)) {
            $year = (int) $match[1];
            $month = (int) $match[2];
            $day = (int) $match[3];

            return checkdate($month, $day, $year) ? $date : null;
        }

        return null;
    }
}
