<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $citiesByCountry = json_decode(<<<'JSON'
{"Bosna i Hercegovina":[["Sarajevo","sarajevo"],["Banja Luka","banja-luka"],["Tuzla","tuzla"],["Zenica","zenica"],["Bijeljina","bijeljina"],["Mostar","mostar"],["Prijedor","prijedor"],["Brčko","brcko"],["Doboj","doboj"],["Bihać","bihac"],["Cazin","cazin"],["Zvornik","zvornik"],["Živinice","zivinice"],["Travnik","travnik"],["Gradiška","gradiska"],["Gračanica","gracanica"],["Visoko","visoko"],["Lukavac","lukavac"],["Tešanj","tesanj"],["Sanski Most","sanski-most"],["Velika Kladuša","velika-kladusa"],["Srebrenik","srebrenik"],["Gradačac","gradacac"],["Kakanj","kakanj"],["Livno","livno"],["Zavidovići","zavidovici"],["Prnjavor","prnjavor"],["Teslić","teslic"],["Kalesija","kalesija"],["Bosanska Krupa","bosanska-krupa"],["Žepče","zepce"],["Trebinje","trebinje"],["Laktaši","laktasi"],["Banovići","banovici"],["Konjic","konjic"],["Bugojno","bugojno"],["Goražde","gorazde"],["Novi Travnik","novi-travnik"],["Foča","foca"],["Vogošća","vogosca"],["Mrkonjić Grad","mrkonjic-grad"],["Široki Brijeg","siroki-brijeg"],["Ljubuški","ljubuski"],["Čapljina","capljina"],["Stolac","stolac"],["Orašje","orasje"],["Derventa","derventa"],["Maglaj","maglaj"],["Kiseljak","kiseljak"],["Jajce","jajce"],["Donji Vakuf","donji-vakuf"],["Hadžići","hadzici"],["Ilidža","ilidza"],["Istočno Sarajevo","istocno-sarajevo"],["Pale","pale"],["Modriča","modrica"],["Višegrad","visegrad"],["Neum","neum"],["Bileća","bileca"],["Prozor-Rama","prozor-rama"],["Tomislavgrad","tomislavgrad"],["Kupres","kupres"],["Drvar","drvar"],["Glamoč","glamoc"],["Ključ","kljuc"],["Bosanski Petrovac","bosanski-petrovac"],["Bužim","buzim"],["Odžak","odzak"],["Šamac","samac"],["Brod","brod"],["Srebrenica","srebrenica"],["Bratunac","bratunac"],["Vlasenica","vlasenica"],["Milići","milici"],["Rogatica","rogatica"],["Rudo","rudo"],["Čajniče","cajnice"],["Kalinovik","kalinovik"],["Nevesinje","nevesinje"],["Gacko","gacko"],["Berkovići","berkovici"],["Lopare","lopare"],["Ugljevik","ugljevik"],["Sokolac","sokolac"],["Han Pijesak","han-pijesak"],["Olovo","olovo"],["Breza","breza"],["Vareš","vares"],["Fojnica","fojnica"],["Kreševo","kresevo"],["Busovača","busovaca"],["Vitez","vitez"],["Kotor Varoš","kotor-varos"],["Čelinac","celinac"],["Kneževo","knezevo"],["Srbac","srbac"],["Kozarska Dubica","kozarska-dubica"],["Kostajnica","kostajnica"],["Čitluk","citluk"],["Posušje","posusje"],["Grude","grude"]],"Srbija":[["Beograd","beograd"],["Novi Sad","novi-sad"],["Niš","nis"],["Kragujevac","kragujevac"],["Subotica","subotica"],["Leskovac","leskovac"],["Pančevo","pancevo"],["Kruševac","krusevac"],["Kraljevo","kraljevo"],["Novi Pazar","novi-pazar"],["Zrenjanin","zrenjanin"],["Čačak","cacak"],["Šabac","sabac"],["Smederevo","smederevo"],["Valjevo","valjevo"],["Loznica","loznica"],["Sombor","sombor"],["Kikinda","kikinda"],["Vranje","vranje"],["Užice","uzice"],["Pirot","pirot"],["Zaječar","zajecar"],["Sremska Mitrovica","sremska-mitrovica"],["Jagodina","jagodina"],["Požarevac","pozarevac"],["Vršac","vrsac"],["Bor","bor"],["Prokuplje","prokuplje"],["Ruma","ruma"],["Paraćin","paracin"],["Bačka Palanka","backa-palanka"],["Inđija","indjija"],["Aranđelovac","arandjelovac"],["Vrbas","vrbas"],["Bečej","becej"],["Knjaževac","knjazevac"],["Senta","senta"],["Apatin","apatin"],["Negotin","negotin"],["Topola","topola"],["Sjenica","sjenica"],["Prijepolje","prijepolje"],["Priboj","priboj"],["Nova Varoš","nova-varos"],["Ivanjica","ivanjica"],["Arilje","arilje"],["Požega","pozega"],["Bajina Bašta","bajina-basta"],["Kosjerić","kosjeric"],["Lučani","lucani"],["Gornji Milanovac","gornji-milanovac"],["Raška","raska"],["Vrnjačka Banja","vrnjacka-banja"],["Trstenik","trstenik"],["Aleksandrovac","aleksandrovac"],["Brus","brus"],["Varvarin","varvarin"],["Ćićevac","cicevac"],["Aleksinac","aleksinac"],["Svrljig","svrljig"],["Merošina","merosina"],["Doljevac","doljevac"],["Gadžin Han","gadzin-han"],["Ražanj","razanj"],["Sokobanja","sokobanja"],["Boljevac","boljevac"],["Kladovo","kladovo"],["Majdanpek","majdanpek"],["Golubac","golubac"],["Veliko Gradište","veliko-gradiste"],["Malo Crniće","malo-crnice"],["Žabari","zabari"],["Petrovac na Mlavi","petrovac-na-mlavi"],["Kučevo","kucevo"],["Žagubica","zagubica"],["Bojnik","bojnik"],["Lebane","lebane"],["Medveđa","medvedja"],["Vlasotince","vlasotince"],["Crna Trava","crna-trava"],["Osečina","osecina"],["Ub","ub"],["Lajkovac","lajkovac"],["Ljig","ljig"],["Mionica","mionica"],["Koceljeva","koceljeva"],["Vladimirci","vladimirci"],["Bogatić","bogatic"],["Mali Zvornik","mali-zvornik"],["Ljubovija","ljubovija"],["Krupanj","krupanj"],["Lazarevac","lazarevac"],["Mladenovac","mladenovac"],["Obrenovac","obrenovac"],["Sopot","sopot"],["Surčin","surcin"],["Barajevo","barajevo"],["Grocka","grocka"],["Stara Pazova","stara-pazova"],["Pećinci","pecinci"],["Irig","irig"],["Šid","sid"],["Bač","bac"],["Bački Petrovac","backi-petrovac"],["Bačka Topola","backa-topola"],["Mali Iđoš","mali-idjos"],["Kula","kula"],["Odžaci","odzaci"],["Titel","titel"],["Žabalj","zabalj"],["Srbobran","srbobran"],["Temerin","temerin"],["Sečanj","secanj"],["Žitište","zitiste"],["Nova Crnja","nova-crnja"],["Novi Bečej","novi-becej"],["Kanjiža","kanjiza"],["Ada","ada"],["Čoka","coka"],["Novi Kneževac","novi-knezevac"],["Alibunar","alibunar"],["Bela Crkva","bela-crkva"],["Kovačica","kovacica"],["Kovin","kovin"],["Opovo","opovo"],["Plandište","plandiste"],["Rakovica","rakovica"],["Čukarica","cukarica"],["Zemun","zemun"],["Zvezdara","zvezdara"],["Palilula","palilula"],["Voždovac","vozdovac"],["Vračar","vracar"],["Savski Venac","savski-venac"],["Stari Grad","stari-grad"],["Novi Beograd","novi-beograd"]],"Hrvatska":[["Zagreb","zagreb"],["Split","split"],["Rijeka","rijeka"],["Osijek","osijek"],["Zadar","zadar"],["Velika Gorica","velika-gorica"],["Slavonski Brod","slavonski-brod"],["Pula","pula"],["Karlovac","karlovac"],["Sisak","sisak"],["Varaždin","varazdin"],["Šibenik","sibenik"],["Dubrovnik","dubrovnik"],["Bjelovar","bjelovar"],["Kaštela","kastela"],["Samobor","samobor"],["Vinkovci","vinkovci"],["Koprivnica","koprivnica"],["Čakovec","cakovec"],["Požega","pozega"],["Virovitica","virovitica"],["Đakovo","djakovo"],["Kutina","kutina"],["Petrinja","petrinja"],["Vukovar","vukovar"],["Zaprešić","zapresic"],["Sinj","sinj"],["Solin","solin"],["Križevci","krizevci"],["Metković","metkovic"],["Nova Gradiška","nova-gradiska"],["Trogir","trogir"],["Knin","knin"],["Rovinj","rovinj"],["Makarska","makarska"],["Poreč","porec"],["Ogulin","ogulin"],["Našice","nasice"],["Županja","zupanja"],["Ivanić-Grad","ivanic-grad"],["Opatija","opatija"],["Labin","labin"],["Daruvar","daruvar"],["Crikvenica","crikvenica"],["Kastav","kastav"],["Slatina","slatina"],["Belišće","belisce"],["Valpovo","valpovo"],["Duga Resa","duga-resa"],["Benkovac","benkovac"],["Prelog","prelog"],["Vrbovec","vrbovec"],["Novi Marof","novi-marof"],["Ivanec","ivanec"],["Čazma","cazma"],["Otočac","otocac"],["Donji Miholjac","donji-miholjac"],["Omiš","omis"],["Umag","umag"],["Vodice","vodice"],["Garešnica","garesnica"],["Delnice","delnice"],["Ploče","ploce"],["Buje","buje"],["Ludbreg","ludbreg"],["Pakrac","pakrac"],["Gospić","gospic"],["Imotski","imotski"],["Krapina","krapina"],["Nin","nin"],["Supetar","supetar"],["Biograd na Moru","biograd-na-moru"],["Novska","novska"],["Jastrebarsko","jastrebarsko"],["Dugo Selo","dugo-selo"],["Sveta Nedelja","sveta-nedelja"],["Popovača","popovaca"],["Ilok","ilok"],["Buzet","buzet"],["Hvar","hvar"],["Korčula","korcula"],["Krk","krk"],["Mali Lošinj","mali-losinj"],["Novalja","novalja"],["Pag","pag"],["Rab","rab"],["Cres","cres"],["Vis","vis"],["Komiža","komiza"],["Stari Grad","stari-grad"],["Vela Luka","vela-luka"],["Orebić","orebic"],["Tisno","tisno"],["Skradin","skradin"],["Drniš","drnis"],["Vodnjan","vodnjan"],["Bakar","bakar"],["Kraljevica","kraljevica"],["Novi Vinodolski","novi-vinodolski"],["Senj","senj"],["Ozalj","ozalj"],["Slunj","slunj"],["Glina","glina"],["Hrvatska Kostajnica","hrvatska-kostajnica"],["Lipik","lipik"],["Pleternica","pleternica"],["Kutjevo","kutjevo"],["Orahovica","orahovica"],["Otok","otok"],["Mursko Središće","mursko-sredisce"],["Lepoglava","lepoglava"],["Varaždinske Toplice","varazdinske-toplice"],["Klanjec","klanjec"],["Pregrada","pregrada"],["Zabok","zabok"],["Zlatar","zlatar"],["Donja Stubica","donja-stubica"],["Oroslavje","oroslavje"],["Pazin","pazin"],["Novigrad","novigrad"],["Vrgorac","vrgorac"],["Trilj","trilj"],["Vrlika","vrlika"]],"Slovenija":[["Ljubljana","ljubljana"],["Maribor","maribor"],["Celje","celje"],["Kranj","kranj"],["Koper","koper"],["Velenje","velenje"],["Novo Mesto","novo-mesto"],["Ptuj","ptuj"],["Trbovlje","trbovlje"],["Kamnik","kamnik"],["Jesenice","jesenice"],["Nova Gorica","nova-gorica"],["Domžale","domzale"],["Škofja Loka","skofja-loka"],["Izola","izola"],["Murska Sobota","murska-sobota"],["Postojna","postojna"],["Logatec","logatec"],["Vrhnika","vrhnika"],["Kočevje","kocevje"],["Slovenj Gradec","slovenj-gradec"],["Ravne na Koroškem","ravne-na-koroskem"],["Krško","krsko"],["Brežice","brezice"],["Ajdovščina","ajdovscina"],["Litija","litija"],["Sežana","sezana"],["Zagorje ob Savi","zagorje-ob-savi"],["Idrija","idrija"],["Črnomelj","crnomelj"],["Bled","bled"],["Rogaška Slatina","rogaska-slatina"],["Lendava","lendava"],["Grosuplje","grosuplje"],["Žalec","zalec"],["Medvode","medvode"],["Slovenska Bistrica","slovenska-bistrica"],["Slovenske Konjice","slovenske-konjice"],["Radovljica","radovljica"],["Tolmin","tolmin"],["Ilirska Bistrica","ilirska-bistrica"],["Ormož","ormoz"],["Trebnje","trebnje"],["Laško","lasko"],["Lenart v Slovenskih Goricah","lenart-v-slovenskih-goricah"],["Ljutomer","ljutomer"],["Metlika","metlika"],["Ribnica","ribnica"],["Ruše","ruse"],["Šentjur","sentjur"],["Šmarje pri Jelšah","smarje-pri-jelsah"],["Mengeš","menges"],["Mežica","mezica"],["Prevalje","prevalje"],["Dravograd","dravograd"],["Radenci","radenci"],["Gornja Radgona","gornja-radgona"],["Cerknica","cerknica"],["Bovec","bovec"],["Piran","piran"],["Portorož","portoroz"],["Ankaran","ankaran"],["Hrastnik","hrastnik"],["Radeče","radece"],["Žiri","ziri"],["Železniki","zelezniki"],["Cerkno","cerkno"],["Kanal ob Soči","kanal-ob-soci"],["Miren","miren"],["Vipava","vipava"],["Sevnica","sevnica"]],"Crna Gora":[["Podgorica","podgorica"],["Nikšić","niksic"],["Pljevlja","pljevlja"],["Bijelo Polje","bijelo-polje"],["Cetinje","cetinje"],["Bar","bar"],["Herceg Novi","herceg-novi"],["Berane","berane"],["Budva","budva"],["Ulcinj","ulcinj"],["Tivat","tivat"],["Rožaje","rozaje"],["Kotor","kotor"],["Danilovgrad","danilovgrad"],["Mojkovac","mojkovac"],["Kolašin","kolasin"],["Plav","plav"],["Andrijevica","andrijevica"],["Žabljak","zabljak"],["Plužine","pluzine"],["Šavnik","savnik"],["Gusinje","gusinje"],["Petnjica","petnjica"],["Tuzi","tuzi"],["Zeta","zeta"]]}
JSON, true, flags: JSON_THROW_ON_ERROR);

        $countries = [
            'Bosna i Hercegovina' => ['slug' => 'bih', 'code' => 'BA', 'language' => 'bs', 'sort_order' => 0],
            'Srbija' => ['slug' => 'srbija', 'code' => 'RS', 'language' => 'sr', 'sort_order' => 1],
            'Hrvatska' => ['slug' => 'hrvatska', 'code' => 'HR', 'language' => 'hr', 'sort_order' => 2],
            'Slovenija' => ['slug' => 'slovenija', 'code' => 'SI', 'language' => 'sl', 'sort_order' => 3],
            'Crna Gora' => ['slug' => 'crna-gora', 'code' => 'ME', 'language' => 'sr', 'sort_order' => 4],
        ];

        DB::transaction(function () use ($citiesByCountry, $countries): void {
            foreach ($citiesByCountry as $countryName => $cities) {
                $country = $countries[$countryName];

                DB::table('countries')->insertOrIgnore([
                    'name' => $countryName,
                    'slug' => $country['slug'],
                    'code' => $country['code'],
                    'default_language' => $country['language'],
                    'meta_title' => "Fotografi u državi {$countryName} | FotoMajstor",
                    'meta_description' => "Pronađite fotografe i videografe u državi {$countryName}. Pretražite po gradu, kategoriji i dostupnosti.",
                    'intro_text' => "Pregledajte profesionalne fotografe i videografe širom države {$countryName}.",
                    'active' => true,
                    'sort_order' => $country['sort_order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $countryId = DB::table('countries')->where('slug', $country['slug'])->value('id');

                foreach (array_chunk($cities, 100, true) as $cityChunk) {
                    $now = now();
                    $cityRows = [];

                    foreach ($cityChunk as $sortOrder => [$cityName, $slug]) {
                        $cityRows[] = [
                            'country_id' => $countryId,
                            'name' => $cityName,
                            'slug' => $slug,
                            'meta_title' => "Fotografi u {$cityName} | FotoMajstor",
                            'meta_description' => "Pronađite fotografe i videografe u mjestu {$cityName}. Pregledajte portfolio i kontaktirajte profesionalca direktno.",
                            'intro_text' => "Pronađite profesionalne fotografe i videografe u mjestu {$cityName}.",
                            'active' => true,
                            'sort_order' => $sortOrder,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    DB::table('cities')->insertOrIgnore($cityRows);

                    if (! Schema::hasTable('locations')) {
                        continue;
                    }

                    $citiesForLocations = DB::table('cities')
                        ->where('country_id', $countryId)
                        ->whereIn('slug', array_column($cityRows, 'slug'))
                        ->get();

                    $locationRows = $citiesForLocations->map(fn ($city) => [
                        'country_id' => $city->country_id,
                        'city_id' => $city->id,
                        'type' => 'city',
                        'name' => $city->name,
                        'slug' => $city->slug,
                        'region' => $city->region,
                        'meta_title' => $city->meta_title,
                        'meta_description' => $city->meta_description,
                        'intro_text' => $city->intro_text,
                        'active' => $city->active,
                        'indexable' => false,
                        'sort_order' => $city->sort_order,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all();

                    DB::table('locations')->insertOrIgnore($locationRows);
                }
            }
        });
    }

    public function down(): void
    {
        // Imported cities may be referenced after deployment, so this data migration is intentionally irreversible.
    }
};
