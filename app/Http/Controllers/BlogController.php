<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Support\Seo;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::published()->with('category')->latest('published_at')->paginate(9);

        $seo = [
            'title' => 'Blog — savjeti i vodiči | FotoMreža',
            'description' => 'Savjeti, vodiči i inspiracija za pronalazak pravog fotografa ili videografa za vaš događaj.',
            'canonical' => paginated_canonical(localized_route('blog.index')),
            'jsonLd' => [Seo::breadcrumbs([
                ['name' => 'Početna', 'url' => localized_route('home')],
                ['name' => 'Blog', 'url' => localized_route('blog.index')],
            ])],
        ];

        return view('public.blog-index', compact('posts', 'seo'));
    }

    public function show(BlogPost $post)
    {
        abort_unless($post->status === \App\Enums\BlogStatus::Published, 404);
        $post->load('category', 'author');

        $related = BlogPost::published()->where('id', '!=', $post->id)
            ->when($post->category_id, fn ($q) => $q->where('category_id', $post->category_id))
            ->latest('published_at')->take(3)->get();

        $description = safe_public_text($post->meta_description ?? $post->excerpt ?? $post->content);

        $seo = [
            'title' => $post->meta_title ?? "{$post->title} | FotoMreža",
            'description' => $description,
            'image' => media_url($post->featured_image),
            'type' => 'article',
            'locales' => [config('locales.default')],
            'canonical' => route('blog.show', $post->slug),
            'canonicalLocale' => config('locales.default'),
            'robots' => app()->getLocale() === config('locales.default') ? 'index, follow' : 'noindex, follow',
            'jsonLd' => [
                Seo::blogPosting(
                    $post->title,
                    $description,
                    media_url($post->featured_image),
                    $post->published_at?->toIso8601String(),
                    localized_route('blog.show', $post->slug),
                    $post->author?->name,
                ),
            ],
        ];

        return view('public.blog-show', compact('post', 'related', 'seo'));
    }
}
