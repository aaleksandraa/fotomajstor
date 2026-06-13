<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryAlias;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Vjenčanja', 'vjencanja', 'Elegantna ceremonija zauvijek zabilježena.'],
            ['Krštenja', 'krstenja', 'Nježne uspomene porodičnih svečanosti.'],
            ['Rođendani', 'rodjendani', 'Slavlje, smijeh i nezaboravne uspomene.'],
            ['Prvi rođendan', 'prvi-rodjendan', 'Prvi veliki dan vašeg mališana.'],
            ['18. rođendan', 'osamnaesti-rodjendan', 'Punoljetstvo dostojno zabilježeno.'],
            ['Matura', 'matura', 'Kraj jednog poglavlja, početak novog.'],
            ['Portreti', 'portreti', 'Studijski i prirodni portreti.'],
            ['Porodično fotografisanje', 'porodicno-fotografisanje', 'Topli porodični trenuci.'],
            ['Fotografisanje proizvoda', 'fotografisanje-proizvoda', 'Proizvodi koji prodaju.'],
            ['Enterijer', 'enterijer', 'Arhitektura i dizajn enterijera.'],
            ['Eksterijer', 'eksterijer', 'Vanjski prostori i arhitektura.'],
            ['Nekretnine', 'nekretnine', 'Profesionalno fotografisanje nekretnina.'],
            ['Komercijalno fotografisanje', 'komercijalno-fotografisanje', 'Brendovi, proizvodi i kampanje.'],
            ['Eventi', 'eventi', 'Dinamika događaja u svakom kadru.'],
            ['Korporativni događaji', 'korporativni-dogadjaji', 'Profesionalni poslovni eventi.'],
            ['Muzički spotovi', 'muzicki-spotovi', 'Muzički i promotivni video spotovi.'],
            ['Reklamni video', 'reklamni-video', 'Reklame koje ostavljaju utisak.'],
            ['Dron fotografija', 'dron-fotografija', 'Snimci iz vazduha visoke razlučivosti.'],
            ['Dron video', 'dron-video', 'Kinematografski snimci iz zraka.'],
            ['Snimanje rođendana', 'snimanje-rodjendana', 'Video uspomene sa proslava.'],
            ['Snimanje vjenčanja', 'snimanje-vjencanja', 'Ceremonija u pokretu.'],
            ['Snimanje iz zraka', 'snimanje-iz-zraka', 'Dron video produkcija.'],
            ['Studio fotografisanje', 'studio-fotografisanje', 'Kontrolisano studijsko svjetlo.'],
            ['Lifestyle fotografisanje', 'lifestyle-fotografisanje', 'Prirodni, neusiljeni trenuci.'],
            ['Modna fotografija', 'modna-fotografija', 'Editorial i modne kampanje.'],
            ['Fotografisanje hrane', 'fotografisanje-hrane', 'Gastronomija koja izaziva apetit.'],
            ['Fotografisanje hotela i apartmana', 'fotografisanje-hotela-i-apartmana', 'Smještaj u najboljem svjetlu.'],
        ];

        foreach ($categories as $index => [$name, $slug, $description]) {
            Category::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'description' => $description,
                'image' => $this->imageFor($slug),
                'meta_title' => "Fotografi za {$name} | FotoMajstor",
                'meta_description' => "Pronađite najbolje fotografe i videografe za {$name}. Pregledajte portfolio, dostupnost i kontakt podatke.",
                'intro_text' => "Pregledajte provjerene profesionalce specijalizovane za kategoriju {$name}.",
                'active' => true,
                'sort_order' => $index,
            ]);
        }

        $weddings = Category::where('slug', 'vjencanja')->first();
        if ($weddings) {
            CategoryAlias::updateOrCreate(['slug' => 'svadbe'], [
                'category_id' => $weddings->id,
                'label' => 'Svadbe',
            ]);
        }

        $weddingVideo = Category::where('slug', 'snimanje-vjencanja')->first();
        if ($weddingVideo) {
            CategoryAlias::updateOrCreate(['slug' => 'snimanje-svadbe'], [
                'category_id' => $weddingVideo->id,
                'label' => 'Snimanje svadbe',
            ]);
        }
    }

    private function imageFor(string $slug): string
    {
        $photos = [
            'vjencanja' => 'photo-1519741497674-611481863552',
            'krstenja' => 'photo-1519689680058-324335c77eba',
            'rodjendani' => 'photo-1530103862676-de8c9debad1d',
            'prvi-rodjendan' => 'photo-1544126592-807ade215a0b',
            'osamnaesti-rodjendan' => 'photo-1492684223066-81342ee5ff30',
            'matura' => 'photo-1492684223066-81342ee5ff30',
            'portreti' => 'photo-1500648767791-00dcc994a43e',
            'porodicno-fotografisanje' => 'photo-1511895426328-dc8714191300',
            'fotografisanje-proizvoda' => 'photo-1523275335684-37898b6baf30',
            'enterijer' => 'photo-1600566753086-00f18fb6b3ea',
            'eksterijer' => 'photo-1487958449943-2429e8be8625',
            'nekretnine' => 'photo-1600585154340-be6161a56a0c',
            'komercijalno-fotografisanje' => 'photo-1552664730-d307ca884978',
            'eventi' => 'photo-1492684223066-81342ee5ff30',
            'korporativni-dogadjaji' => 'photo-1505373877841-8d25f7d46678',
            'muzicki-spotovi' => 'photo-1493225457124-a3eb161ffa5f',
            'reklamni-video' => 'photo-1492619375914-88005aa9e8fb',
            'dron-fotografija' => 'photo-1508614999368-9260051292e5',
            'dron-video' => 'photo-1508614999368-9260051292e5',
            'snimanje-rodjendana' => 'photo-1530103862676-de8c9debad1d',
            'snimanje-vjencanja' => 'photo-1519741497674-611481863552',
            'snimanje-iz-zraka' => 'photo-1508614999368-9260051292e5',
            'studio-fotografisanje' => 'photo-1524504388940-b1c1722653e1',
            'lifestyle-fotografisanje' => 'photo-1529139574466-a303027c1d8b',
            'modna-fotografija' => 'photo-1539109136881-3be0616acf4b',
            'fotografisanje-hrane' => 'photo-1504674900247-0877df9cc836',
            'fotografisanje-hotela-i-apartmana' => 'photo-1566073771259-6a8506099945',
        ];

        $photo = $photos[$slug] ?? 'photo-1452780212940-6f5c0d14d848';

        return "https://images.unsplash.com/{$photo}?auto=format&fit=crop&w=1200&h=900&q=82";
    }
}
