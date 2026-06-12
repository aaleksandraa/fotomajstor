<?php

namespace Database\Seeders;

use App\Enums\BlogStatus;
use App\Enums\UserRole;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('role', UserRole::Admin->value)->first();

        $posts = [
            ['Kako odabrati fotografa za vjenčanje', 'vjencanja'],
            ['Najljepše lokacije za vjenčanja u Bosni i Hercegovini', 'svadbe'],
            ['Savjeti za fotografisanje važnih događaja', 'eventi'],
            ['Trendovi portretne fotografije', 'portreti'],
            ['Koliko košta fotograf za svadbu', 'svadbe'],
            ['Kako odabrati videografa za rođendan', 'snimanje-rodjendana'],
            ['Zašto je portfolio najvažniji kod izbora fotografa', 'komercijalno-fotografisanje'],
        ];

        foreach ($posts as $index => [$title, $categorySlug]) {
            $category = Category::where('slug', $categorySlug)->first();

            BlogPost::updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'author_id' => $author?->id,
                    'title' => $title,
                    'excerpt' => Str::limit('U ovom vodiču pokrivamo sve što trebate znati o temi: '.$title.'.', 150),
                    'content' => $this->content($title),
                    'featured_image' => placeholder_image('blog-'.Str::slug($title), 1600, 900),
                    'category_id' => $category?->id,
                    'meta_title' => $title.' | FotoMajstor',
                    'meta_description' => Str::limit('Saznajte više: '.$title.'. Praktični savjeti i preporuke za pronalazak pravog profesionalca.', 155),
                    'status' => BlogStatus::Published,
                    'published_at' => now()->subDays(($index + 1) * 3),
                ]
            );
        }
    }

    private function content(string $title): string
    {
        return "<h2>{$title}</h2>"
            ."<p>Pronalazak pravog fotografa ili videografa može djelovati izazovno, ali uz nekoliko jasnih koraka proces postaje jednostavan. "
            ."U nastavku donosimo praktične savjete koji će vam pomoći da donesete pravu odluku.</p>"
            ."<h3>Definišite svoj stil</h3>"
            ."<p>Prije nego što krenete u potragu, razmislite o estetici koja vam se sviđa — od klasične i elegantne do moderne i dokumentarne.</p>"
            ."<h3>Pregledajte portfolio</h3>"
            ."<p>Portfolio je najbolji pokazatelj kvaliteta i dosljednosti rada. Obratite pažnju na konzistentnost tona, kompoziciju i emociju.</p>"
            ."<h3>Provjerite dostupnost i kontakt</h3>"
            ."<p>Kada pronađete profesionalca čiji vam se rad sviđa, provjerite njegovu dostupnost za vaš datum i kontaktirajte ga direktno.</p>";
    }
}
