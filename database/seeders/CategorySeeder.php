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
                'image' => placeholder_image('cat-'.$slug, 1200, 900),
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
}
