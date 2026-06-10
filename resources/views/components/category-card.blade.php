@props(['category', 'count' => null])

<a href="{{ localized_route('category.show', $category->slug) }}"
   class="group relative block aspect-[4/3] overflow-hidden rounded-2xl bg-ink-900">
    @if ($category->image)
        <img src="{{ media_url($category->image) }}" alt="{{ $category->name }}" loading="lazy" width="1200" height="900"
             class="absolute inset-0 h-full w-full object-cover opacity-90 transition duration-500 group-hover:scale-105 group-hover:opacity-100">
    @endif
    <div class="absolute inset-0 bg-gradient-to-t from-ink-900/85 via-ink-900/20 to-transparent"></div>
    <div class="absolute inset-x-0 bottom-0 p-5">
        <h3 class="font-serif text-xl text-white">{{ $category->name }}</h3>
        @if ($category->description)
            <p class="mt-1 line-clamp-1 text-sm text-white/75">{{ $category->description }}</p>
        @endif
        @if (! is_null($count))
            <p class="mt-2 text-xs font-semibold uppercase tracking-wider text-accent-300">{{ trans_choice(':count profesionalac|:count profesionalaca', $count, ['count' => $count]) }}</p>
        @endif
    </div>
</a>
