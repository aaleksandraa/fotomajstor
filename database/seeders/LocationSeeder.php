<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name' => 'Bosna i Hercegovina',
                'slug' => 'bih',
                'code' => 'BA',
                'default_language' => 'bs',
                'cities' => ['Banja Luka', 'Sarajevo', 'Tuzla', 'Mostar', 'Doboj', 'Modriča', 'Bijeljina', 'Brčko', 'Zenica', 'Prijedor', 'Bihać', 'Cazin', 'Goražde', 'Gradiška', 'Konjic', 'Livno', 'Lukavac', 'Tešanj', 'Trebinje', 'Visoko', 'Zvornik'],
            ],
            [
                'name' => 'Srbija',
                'slug' => 'srbija',
                'code' => 'RS',
                'default_language' => 'sr',
                'cities' => ['Beograd', 'Novi Sad', 'Niš', 'Kragujevac', 'Subotica', 'Čačak', 'Kraljevo', 'Kruševac', 'Leskovac', 'Novi Pazar', 'Pančevo', 'Smederevo', 'Užice', 'Valjevo', 'Vranje', 'Zrenjanin'],
            ],
            [
                'name' => 'Hrvatska',
                'slug' => 'hrvatska',
                'code' => 'HR',
                'default_language' => 'hr',
                'cities' => ['Zagreb', 'Split', 'Rijeka', 'Osijek', 'Zadar', 'Dubrovnik', 'Pula', 'Šibenik', 'Varaždin', 'Karlovac', 'Slavonski Brod', 'Velika Gorica'],
            ],
            [
                'name' => 'Slovenija',
                'slug' => 'slovenija',
                'code' => 'SI',
                'default_language' => 'sl',
                'cities' => ['Ljubljana', 'Maribor', 'Kranj', 'Celje', 'Koper', 'Novo Mesto', 'Ptuj', 'Nova Gorica', 'Murska Sobota', 'Velenje'],
            ],
            [
                'name' => 'Crna Gora',
                'slug' => 'crna-gora',
                'code' => 'ME',
                'default_language' => 'sr',
                'cities' => ['Podgorica', 'Nikšić', 'Budva', 'Bar', 'Herceg Novi', 'Kotor', 'Tivat', 'Cetinje', 'Bijelo Polje', 'Ulcinj'],
            ],
        ];

        foreach ($data as $countryIndex => $row) {
            $country = Country::updateOrCreate(['slug' => $row['slug']], [
                'name' => $row['name'],
                'code' => $row['code'],
                'default_language' => $row['default_language'],
                'meta_title' => "Fotografi u državi {$row['name']} | FotoMreža",
                'meta_description' => "Pretražite fotografe i videografe u državi {$row['name']}. Po gradovima, kategorijama i dostupnosti.",
                'intro_text' => "Pregledajte profesionalne fotografe i videografe širom države {$row['name']}.",
                'active' => true,
                'sort_order' => $countryIndex,
            ]);

            foreach ($row['cities'] as $cityIndex => $cityName) {
                $slug = \Illuminate\Support\Str::slug($cityName);

                City::updateOrCreate(
                    ['country_id' => $country->id, 'slug' => $slug],
                    [
                        'name' => $cityName,
                        'meta_title' => "Fotografi u {$cityName} | FotoMreža",
                        'meta_description' => "Pretražite fotografe i videografe u mjestu {$cityName}. Pogledajte profile, portfolio i kontakt podatke.",
                        'intro_text' => "Pronađite provjerene fotografe i videografe u mjestu {$cityName}.",
                        'active' => true,
                        'sort_order' => $cityIndex,
                    ]
                );
            }
        }

        if (! Schema::hasTable('locations')) {
            return;
        }

        City::with('country')->get()->each(function (City $city) {
            Location::updateOrCreate(['city_id' => $city->id], [
                'country_id' => $city->country_id,
                'type' => 'city',
                'name' => $city->getRawOriginal('name'),
                'slug' => $city->slug,
                'region' => $city->region,
                'meta_title' => $city->meta_title,
                'meta_description' => $city->meta_description,
                'intro_text' => $city->intro_text,
                'active' => $city->active,
                'indexable' => $city->photographers()->where('active', true)->exists(),
                'sort_order' => $city->sort_order,
            ]);
        });
    }
}
