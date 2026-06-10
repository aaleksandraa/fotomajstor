<a href="{{ localized_route('blog.show', $post->slug) }}" class="group block overflow-hidden rounded-2xl border border-ink-100 bg-white transition hover:shadow-lg hover:shadow-ink-100">
    <div class="aspect-[16/10] overflow-hidden bg-ink-100">
        @if ($post->featured_image)
            <img src="{{ media_url($post->featured_image) }}" alt="{{ $post->title }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
        @endif
    </div>
    <div class="p-5">
        @if ($post->category)
            <span class="eyebrow">{{ $post->category->name }}</span>
        @endif
        <h3 class="mt-2 font-serif text-xl leading-snug text-ink-900 group-hover:text-accent-700">{{ $post->title }}</h3>
        <p class="mt-2 line-clamp-2 text-sm text-ink-500">{{ $post->excerpt }}</p>
        @if ($post->published_at)
            <p class="mt-3 text-xs text-ink-400">{{ $post->published_at->translatedFormat('d. F Y.') }}</p>
        @endif
    </div>
</a>
