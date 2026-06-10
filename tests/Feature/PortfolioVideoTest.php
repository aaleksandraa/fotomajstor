<?php

namespace Tests\Feature;

use App\Enums\ServiceType;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\City;
use App\Models\PortfolioAlbum;
use App\Models\PortfolioImage;
use App\Models\PortfolioVideo;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\LocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PortfolioVideoTest extends TestCase
{
    use RefreshDatabase;

    public function test_youtube_and_vimeo_links_are_embedded_on_profile(): void
    {
        $this->seed([LocationSeeder::class, CategorySeeder::class]);

        $user = User::create([
            'name' => 'Video Fotograf',
            'email' => 'video@example.com',
            'password' => 'password',
            'role' => UserRole::Photographer,
            'email_verified_at' => now(),
        ]);

        $profile = $user->photographerProfile;
        $city = City::firstOrFail();
        $category = Category::firstOrFail();
        $profile->update([
            'active' => true,
            'display_name' => 'Video Studio',
            'slug' => 'video-studio',
            'primary_city_id' => $city->id,
            'primary_country_id' => $city->country_id,
        ]);
        $profile->categories()->attach($category->id);
        $profile->cities()->attach($city->id);

        $album = PortfolioAlbum::create([
            'photographer_profile_id' => $profile->id,
            'category_id' => $category->id,
            'title' => 'Video portfolio',
            'slug' => 'video-portfolio',
            'active' => true,
        ]);

        PortfolioVideo::create([
            'portfolio_album_id' => $album->id,
            'title' => 'YouTube primjer',
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
        PortfolioVideo::create([
            'portfolio_album_id' => $album->id,
            'title' => 'Vimeo primjer',
            'url' => 'https://vimeo.com/123456789',
        ]);

        $this->get('/fotograf/video-studio')
            ->assertOk()
            ->assertSee('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', false)
            ->assertSee('https://player.vimeo.com/video/123456789', false)
            ->assertSee('YouTube primjer')
            ->assertSee('Vimeo primjer');
    }

    public function test_only_youtube_and_vimeo_links_are_allowed(): void
    {
        $this->expectException(ValidationException::class);

        PortfolioVideo::create([
            'portfolio_album_id' => 1,
            'url' => 'https://example.com/video',
        ]);
    }

    public function test_videographer_profile_does_not_show_top_photo_hero(): void
    {
        $this->seed([LocationSeeder::class, CategorySeeder::class]);

        $user = User::create([
            'name' => 'Samo Video',
            'email' => 'samo-video@example.com',
            'password' => 'password',
            'role' => UserRole::Photographer,
            'email_verified_at' => now(),
        ]);

        $profile = $user->photographerProfile;
        $city = City::firstOrFail();
        $category = Category::firstOrFail();
        $profile->update([
            'active' => true,
            'display_name' => 'Samo Video',
            'slug' => 'samo-video',
            'service_type' => ServiceType::Videographer,
            'primary_city_id' => $city->id,
            'primary_country_id' => $city->country_id,
        ]);
        $profile->categories()->attach($category->id);
        $profile->cities()->attach($city->id);

        $album = PortfolioAlbum::create([
            'photographer_profile_id' => $profile->id,
            'category_id' => $category->id,
            'title' => 'Video portfolio',
            'slug' => 'samo-video-portfolio',
            'active' => true,
        ]);

        PortfolioImage::create([
            'portfolio_album_id' => $album->id,
            'image_path' => 'https://picsum.photos/seed/video-cover/800/600',
            'alt_text' => 'Video cover',
        ]);

        $this->get('/fotograf/samo-video')
            ->assertOk()
            ->assertDontSee('data-profile-hero', false)
            ->assertSee('Portfolio');
    }

    public function test_profile_portfolio_defers_images_after_initial_batch_and_keeps_seo_canonical(): void
    {
        $this->seed([LocationSeeder::class, CategorySeeder::class]);

        $user = User::create([
            'name' => 'SEO Portfolio',
            'email' => 'seo-portfolio@example.com',
            'password' => 'password',
            'role' => UserRole::Photographer,
            'email_verified_at' => now(),
        ]);

        $profile = $user->photographerProfile;
        $city = City::firstOrFail();
        $category = Category::firstOrFail();
        $profile->update([
            'active' => true,
            'display_name' => 'SEO Portfolio',
            'slug' => 'seo-portfolio',
            'primary_city_id' => $city->id,
            'primary_country_id' => $city->country_id,
            'about' => 'Profesionalni fotograf sa detaljnim portfolio prikazom.',
        ]);
        $profile->categories()->attach($category->id);
        $profile->cities()->attach($city->id);

        $album = PortfolioAlbum::create([
            'photographer_profile_id' => $profile->id,
            'category_id' => $category->id,
            'title' => 'SEO galerija',
            'slug' => 'seo-galerija',
            'active' => true,
        ]);

        for ($i = 1; $i <= 10; $i++) {
            PortfolioImage::create([
                'portfolio_album_id' => $album->id,
                'image_path' => "https://picsum.photos/seed/portfolio-{$i}/800/600",
                'alt_text' => "Portfolio slika {$i}",
                'sort_order' => $i,
            ]);
        }

        $response = $this->get('/fotograf/seo-portfolio')->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('<link rel="canonical" href="'.route('photographer.show', 'seo-portfolio').'">', $html);
        $this->assertStringContainsString('Učitaj još', $html);
        $this->assertStringContainsString('https://picsum.photos/seed/portfolio-1/800/600', $html);

        preg_match_all('/<img\b[^>]*data-portfolio-image[^>]*>/i', $html, $matches);

        $this->assertCount(10, $matches[0]);
        $this->assertCount(8, array_filter($matches[0], fn (string $tag) => str_contains($tag, 'data-portfolio-visible="initial"') && str_contains($tag, ' src=')));
        $this->assertCount(2, array_filter($matches[0], fn (string $tag) => str_contains($tag, 'data-portfolio-visible="deferred"') && ! str_contains($tag, ' src=')));
    }
}
