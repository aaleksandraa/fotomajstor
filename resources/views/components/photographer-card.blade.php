@props(['photographer'])

@php
    $images = $photographer->albums
        ->flatMap(fn ($album) => $album->images)
        ->take(3)
        ->values();
    if ($images->isEmpty() && $photographer->cover_image) {
        $images = collect([(object) ['image_path' => $photographer->cover_image, 'alt_text' => $photographer->display_name]]);
    }
    $photoCount = $photographer->albums->sum(fn ($album) => $album->images->count());
    $available = ($photographer->busy_today_count ?? 0) === 0;
@endphp

<a href="{{ localized_route('photographer.show', $photographer->slug) }}"
   class="group block overflow-hidden rounded-2xl border border-ink-100 bg-white transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-ink-100">
    <div class="relative grid h-56 grid-cols-3 gap-1 bg-ink-100">
        <div class="col-span-2 overflow-hidden">
            @if ($img = $images->get(0))
                <img src="{{ media_url($img->image_path) }}" alt="{{ $img->alt_text ?? $photographer->display_name }}"
                     loading="lazy" width="800" height="600" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
            @endif
        </div>
        <div class="grid grid-rows-2 gap-1">
            <div class="overflow-hidden">
                @if ($img = $images->get(1))
                    <img src="{{ media_url($img->image_path) }}" alt="{{ $img->alt_text ?? '' }}" loading="lazy" width="400" height="300" class="h-full w-full object-cover">
                @endif
            </div>
            <div class="overflow-hidden">
                @if ($img = $images->get(2))
                    <img src="{{ media_url($img->image_path) }}" alt="{{ $img->alt_text ?? '' }}" loading="lazy" width="400" height="300" class="h-full w-full object-cover">
                @endif
            </div>
        </div>

        <div class="absolute left-3 top-3 flex items-center gap-2">
            <x-availability-badge :available="$available" />
            <span class="rounded-full bg-white/90 px-2.5 py-1 text-xs font-medium text-ink-800">{{ $photographer->service_type->label() }}</span>
        </div>
    </div>

    <div class="p-4">
        <div class="flex items-start justify-between gap-2">
            <div>
                <h3 class="font-serif text-lg leading-tight text-ink-900">{{ $photographer->display_name }}</h3>
                <p class="mt-0.5 text-sm text-ink-500">
                    {{ $photographer->primaryCity?->name }}@if ($photographer->experience_years) · {{ __(':count godina iskustva', ['count' => $photographer->experience_years]) }} @endif
                </p>
            </div>
            @if ($photographer->verified)
                <span title="{{ __('Verifikovan') }}" class="text-accent-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="m9.5 16.5-3.5-3.5 1.4-1.4 2.1 2.1 5.6-5.6 1.4 1.4z" opacity=".0"/><path d="M12 2 9.8 4.2 6.7 4l-.6 3.1L3.6 9.5 5 12l-1.4 2.5 2.5 1.4.6 3.1 3.1-.2L12 22l2.2-2.2 3.1.2.6-3.1 2.5-1.4L19 12l1.4-2.5-2.5-1.4-.6-3.1-3.1.2zm-1.2 13.4-3.2-3.2 1.3-1.3 1.9 1.9 4.1-4.1 1.3 1.3z"/></svg>
                </span>
            @endif
        </div>

        <div class="mt-3 flex flex-wrap gap-1.5">
            @foreach ($photographer->categories->take(3) as $category)
                <span class="pill">{{ $category->name }}</span>
            @endforeach
        </div>

        <div class="mt-4 flex items-center justify-between border-t border-ink-100 pt-3 text-sm text-ink-400">
            <span class="inline-flex items-center gap-1.5">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="12" cy="12" r="3"/></svg>
                {{ trans_choice(':count fotografija|:count fotografija', $photoCount, ['count' => $photoCount]) }}
            </span>
            <span class="font-medium text-ink-900 transition group-hover:text-accent-600">{{ __('Pogledaj profil') }} &rarr;</span>
        </div>
    </div>
</a>
