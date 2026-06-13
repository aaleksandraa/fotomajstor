<?php

namespace Database\Seeders;

use App\Enums\PhotographerBlogStatus;
use App\Enums\ProfileType;
use App\Enums\ServiceType;
use App\Models\Category;
use App\Models\City;
use App\Models\PhotographerBlogPost;
use App\Models\PhotographerProfile;
use App\Models\PortfolioAlbum;
use App\Models\PortfolioImage;
use App\Models\UnavailableDate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PhotographerSeeder extends Seeder
{
    public function run(): void
    {
        $firstNames = ['Marko', 'Ana', 'Ivana', 'Nikola', 'Jelena', 'Stefan', 'Milica', 'Aleksandar', 'Sara', 'Luka', 'Marija', 'Nemanja', 'Tamara', 'Vladimir', 'Katarina', 'Đorđe', 'Sofija', 'Filip', 'Teodora', 'Petar'];
        $lastNames = ['Petrović', 'Jovanović', 'Marković', 'Kovačević', 'Babić', 'Knežević', 'Đurić', 'Lukić', 'Ilić', 'Pavlović', 'Stanković', 'Tomić', 'Radić', 'Novak', 'Horvat', 'Kovač', 'Matić', 'Vuković', 'Simić', 'Nikolić'];
        $companyNames = ['Studio Vision', 'Creative Lens', 'Foto Studio Marković', 'Aperture Films', 'Momenti Studio', 'Lumen Production', 'Pixel Perfect', 'Golden Hour Studio', 'Frame & Light', 'Visual Story'];

        $photoCategories = Category::whereIn('slug', [
            'vjencanja', 'krstenja', 'rodjendani', 'prvi-rodjendan', 'portreti',
            'porodicno-fotografisanje', 'fotografisanje-proizvoda', 'enterijer', 'nekretnine',
            'komercijalno-fotografisanje', 'eventi', 'studio-fotografisanje', 'modna-fotografija',
            'fotografisanje-hrane', 'dron-fotografija',
        ])->pluck('id', 'slug');

        $videoCategories = Category::whereIn('slug', [
            'snimanje-vjencanja', 'snimanje-rodjendana', 'muzicki-spotovi',
            'reklamni-video', 'dron-video', 'snimanje-iz-zraka', 'korporativni-dogadjaji',
        ])->pluck('id', 'slug');

        $cities = City::with('country')->get();

        for ($i = 0; $i < 40; $i++) {
            $isVideographer = $i >= 30;
            $isCompany = $i % 3 === 0;

            if ($isVideographer) {
                $serviceType = ServiceType::Videographer;
            } elseif ($i % 5 === 0) {
                $serviceType = ServiceType::PhotographerVideographer;
            } else {
                $serviceType = ServiceType::Photographer;
            }

            $profileType = $isCompany ? ProfileType::Company : ProfileType::Individual;

            $firstName = $firstNames[$i % count($firstNames)];
            $lastName = $lastNames[($i * 7) % count($lastNames)];

            if ($isCompany) {
                $displayName = $companyNames[$i % count($companyNames)].' '.($i > 9 ? Str::upper(Str::random(2)) : '');
                $displayName = trim($displayName);
                $companyName = $displayName;
            } else {
                $displayName = "{$firstName} {$lastName}";
                $companyName = null;
            }

            $baseSlug = Str::slug($displayName);
            $slug = $baseSlug;
            $suffix = 2;
            while (PhotographerProfile::where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$suffix++;
            }

            $email = Str::slug($displayName, '.').($i + 1).'@example.com';

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $displayName,
                    'password' => Hash::make('password'),
                    'role' => \App\Enums\UserRole::Photographer,
                    'email_verified_at' => now(),
                ]
            );

            /** @var \App\Models\City $primaryCity */
            $primaryCity = $cities->random();

            $profile = PhotographerProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'profile_type' => $profileType,
                    'service_type' => $serviceType,
                    'display_name' => $displayName,
                    'first_name' => $isCompany ? null : $firstName,
                    'last_name' => $isCompany ? null : $lastName,
                    'company_name' => $companyName,
                    'company_tax_number' => $isCompany ? $this->numerify('##########') : null,
                    'slug' => $slug,
                    'profile_image' => placeholder_image('avatar-'.$slug, 400, 400),
                    'cover_image' => placeholder_image('cover-'.$slug, 1600, 900),
                    'about' => $this->bio($displayName, $serviceType),
                    'experience_years' => random_int(2, 18),
                    'phone' => '+387 6'.random_int(1, 6).' '.$this->numerify('### ###'),
                    'public_email' => 'kontakt@'.$baseSlug.'.ba',
                    'website' => $isCompany ? 'https://www.'.$baseSlug.'.ba' : null,
                    'primary_country_id' => $primaryCity->country_id,
                    'primary_city_id' => $primaryCity->id,
                    'verified' => $i % 2 === 0,
                    'active' => true,
                    'featured' => $i % 7 === 0,
                    'profile_views' => random_int(50, 5000),
                ]
            );

            $profile->socialLinks()->updateOrCreate([], [
                'instagram' => 'https://instagram.com/'.$baseSlug,
                'facebook' => 'https://facebook.com/'.$baseSlug,
                'tiktok' => $i % 2 === 0 ? 'https://tiktok.com/@'.$baseSlug : null,
                'youtube' => $isVideographer ? 'https://youtube.com/@'.$baseSlug : null,
                'linkedin' => $isCompany ? 'https://linkedin.com/company/'.$baseSlug : null,
            ]);

            // Categories
            $pool = $isVideographer ? $videoCategories : $photoCategories;
            if ($serviceType === ServiceType::PhotographerVideographer) {
                $pool = $photoCategories->merge($videoCategories);
            }
            $chosenCategories = collect($pool)->shuffle()->take(random_int(2, 4));
            $profile->categories()->sync($chosenCategories->values()->all());

            // Cities (1-3) + derived countries
            $chosenCities = $cities->shuffle()->take(random_int(1, 3));
            if (! $chosenCities->contains('id', $primaryCity->id)) {
                $chosenCities->push($primaryCity);
            }
            $profile->cities()->sync($chosenCities->pluck('id')->all());
            $profile->countries()->sync($chosenCities->pluck('country_id')->unique()->all());

            // Portfolio albums + images
            $albumCategoryIds = $chosenCategories->values()->all();
            $albumCount = random_int(2, 4);
            for ($a = 0; $a < $albumCount; $a++) {
                $categoryId = $albumCategoryIds[$a % max(count($albumCategoryIds), 1)] ?? null;
                $category = $categoryId ? Category::find($categoryId) : null;
                $title = $category ? $category->name : 'Galerija '.($a + 1);
                $albumSlug = Str::slug($title).'-'.($a + 1);

                $album = PortfolioAlbum::updateOrCreate(
                    ['photographer_profile_id' => $profile->id, 'slug' => $albumSlug],
                    [
                        'category_id' => $categoryId,
                        'title' => $title,
                        'description' => 'Izbor radova iz kategorije '.$title.'.',
                        'cover_image' => placeholder_image('album-'.$slug.'-'.$a, 1200, 900),
                        'sort_order' => $a,
                        'active' => true,
                    ]
                );

                $imageCount = random_int(6, 12);
                for ($img = 0; $img < $imageCount; $img++) {
                    PortfolioImage::updateOrCreate(
                        ['portfolio_album_id' => $album->id, 'image_path' => placeholder_image('img-'.$slug.'-'.$a.'-'.$img, 1000, 750)],
                        [
                            'title' => $title.' '.($img + 1),
                            'alt_text' => $this->altText($title, $primaryCity->name, $displayName),
                            'sort_order' => $img,
                        ]
                    );
                }
            }

            // Unavailable dates (3-8 upcoming days)
            $busyDays = collect(range(1, 60))->shuffle()->take(random_int(3, 8));
            foreach ($busyDays as $offset) {
                $date = now()->addDays($offset)->startOfDay();

                UnavailableDate::updateOrCreate(
                    ['photographer_profile_id' => $profile->id, 'date' => $date],
                    ['note' => 'Zauzeto']
                );
            }

            // Photographer blog posts (0-2)
            if ($i % 2 === 0) {
                $postCount = random_int(1, 2);
                for ($p = 0; $p < $postCount; $p++) {
                    $cat = Category::find($albumCategoryIds[0] ?? null);
                    $postTitle = $this->blogTitle($cat?->name ?? 'fotografija', $primaryCity->name);
                    PhotographerBlogPost::updateOrCreate(
                        ['photographer_profile_id' => $profile->id, 'slug' => Str::slug($postTitle).'-'.$p],
                        [
                            'title' => $postTitle,
                            'excerpt' => 'Priča sa nedavnog snimanja i nekoliko savjeta iz prakse.',
                            'content' => $this->blogContent($postTitle),
                            'featured_image' => placeholder_image('post-'.$slug.'-'.$p, 1200, 800),
                            'category_id' => $cat?->id,
                            'city_id' => $primaryCity->id,
                            'country_id' => $primaryCity->country_id,
                            'meta_title' => $postTitle.' - '.$displayName,
                            'meta_description' => Str::limit('Pročitajte članak: '.$postTitle.' autora '.$displayName.'.', 155),
                            'status' => PhotographerBlogStatus::Published,
                            'published_at' => now()->subDays(random_int(1, 90)),
                        ]
                    );
                }
            }
        }
    }

    private function bio(string $name, ServiceType $type): string
    {
        $craft = $type === ServiceType::Videographer ? 'video produkcije' : 'fotografije';

        return "Moj rad se kreće između fine-art estetike i klasične dokumentarne tradicije {$craft}. "
            ."Sarađivao sam s nekoliko poznatih studija, a moji radovi su objavljivani u regionalnim časopisima. "
            ."Cilj mi je da svaka galerija koju isporučim izgleda kao mala umjetnička priča — pažljivo birana, dosljedna u tonu i puna emocije. "
            ."Kao {$name}, posvećen sam tome da svaki događaj zabilježim autentično i bezvremeno.";
    }

    private function altText(string $category, string $city, string $name): string
    {
        return "{$category} u mjestu {$city} - {$name}";
    }

    private function blogTitle(string $category, string $city): string
    {
        $templates = [
            "{$category} u {$city} - galerija i priča sa događaja",
            "Kako izgleda {$category} iz moje perspektive",
            "Savjeti za {$category}: priprema i očekivanja",
        ];

        return $templates[array_rand($templates)];
    }

    private function numerify(string $pattern): string
    {
        return preg_replace_callback('/#/', fn (): string => (string) random_int(0, 9), $pattern);
    }

    private function blogContent(string $title): string
    {
        return "<p>{$title}.</p><p>U ovom članku dijelim utiske sa nedavnog snimanja, pristup radu i nekoliko praktičnih savjeta koji vam mogu pomoći da se bolje pripremite. "
            ."Svaki projekat počinjem razgovorom o vašoj viziji, lokacijama i atmosferi koju želite postići.</p>"
            ."<p>Vjerujem da najbolje fotografije nastaju kada se osjećate opušteno i prirodno. Zato veliku pažnju posvećujem ambijentu, svjetlu i trenucima koji se ne ponavljaju.</p>";
    }
}
