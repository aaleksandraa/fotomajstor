# PRD / Specifikacija projekta: FotoMajstor

**Verzija:** 1.0  
**Tip dokumenta:** Product Requirements Document + tehničko-funkcionalna specifikacija  
**Primarni cilj:** SEO-first direktorijum fotografa i videografa za BiH, Srbiju, Hrvatsku i širu regiju  
**Plaćanje:** Nije dio sistema  
**Rezervacije:** Nisu dio sistema  
**Kontakt forme:** Nisu dio sistema  
**Kontakt:** Direktno preko telefona, e-maila i društvenih mreža  

---

## 1. Kratak opis projekta

**FotoMajstor** je moderna SEO-first web platforma za pronalazak fotografa i videografa prema:

- državi
- gradu/mjestu/opštini
- kategoriji fotografisanja/snimanja
- datumu dostupnosti
- tipu profila: fizičko lice ili pravno lice/firma
- tipu usluge: fotograf, videograf ili fotograf + videograf

Platforma služi kao profesionalni katalog, portfolio direktorijum i SEO landing sistem.

Korisnik ne rezerviše termin kroz platformu.  
Korisnik ne plaća kroz platformu.  
Korisnik ne šalje upit kroz kontakt formu.  

Korisnik direktno kontaktira fotografa putem:

- telefona
- e-maila
- Instagrama
- Facebooka
- TikToka
- YouTubea
- LinkedIna
- web stranice

---

## 2. Glavni cilj sistema

Najvažniji cilj sistema je **SEO pokrivenost za veliki broj lokalnih i uslužnih pojmova**.

Primjeri Google pojmova koje sistem mora pokriti:

- fotograf Modriča
- fotograf Doboj
- fotograf Banja Luka
- fotograf Beograd
- fotograf Zagreb
- fotograf Novi Sad
- fotograf Sarajevo
- fotograf za svadbe Doboj
- fotograf za vjenčanja Banja Luka
- snimanje rođendana Banja Luka
- fotograf za prvi rođendan Novi Sad
- videograf za svadbe Sarajevo
- dron snimanje Zagreb
- fotograf za krštenje Bijeljina
- fotografisanje enterijera Beograd
- komercijalni fotograf Zagreb
- fotograf za proizvode Sarajevo

Sistem mora automatski generisati SEO stranice za kombinacije:

- kategorija
- država
- grad/mjesto
- kategorija + grad
- kategorija + država
- tip usluge + grad
- profil fotografa
- blog članci
- portfolio albumi
- fotografovi blog članci

---

## 3. Šta sistem NIJE

Sistem nije:

- booking platforma
- marketplace sa plaćanjem
- sistem za rezervacije
- CRM
- chat platforma
- platforma za ugovaranje cijena
- sistem za fakture
- sistem za online narudžbe
- sistem sa internim porukama

U MVP-u ne praviti:

- Stripe
- PayPal
- online plaćanje
- kontakt forme
- chat
- recenzije
- korisničke naloge za posjetioce
- termine po satima
- kalendar rezervacija po satima
- mobilnu aplikaciju

---

## 4. Korisničke uloge

Sistem ima tri osnovna tipa korisnika.

---

### 4.1. Posjetilac

Posjetilac nema nalog.

Može:

- pretraživati fotografe i videografe
- filtrirati po državi
- filtrirati po gradu/mjestu
- filtrirati po kategoriji
- filtrirati po datumu
- filtrirati po tipu usluge
- pregledati javni profil fotografa
- pregledati portfolio
- čitati blog
- čitati fotografove članke
- kliknuti telefon
- kliknuti e-mail
- kliknuti društvene mreže

Ne može:

- slati kontakt formu
- rezervisati termin
- plaćati
- ostavljati recenziju u MVP-u
- kreirati nalog

---

### 4.2. Fotograf / Videograf

Registrovani korisnik.

Može:

- kreirati profil
- izabrati da li je fizičko lice ili pravno lice/firma
- izabrati tip usluge
- dodati opis profila
- dodati kontakt podatke
- dodati društvene mreže
- izabrati države u kojima radi
- izabrati gradove/mjesta/opštine u kojima radi
- izabrati kategorije koje radi
- dodati portfolio albume
- dodati fotografije u portfolio
- dodati alt tekst za slike
- označiti zauzete dane
- pisati blog članke koji se prikazuju na njegovom profilu
- dodati fotografije u blog članak
- pratiti osnovnu statistiku svog profila

Ne može:

- odobriti sam sebi profil
- upravljati tuđim profilima
- uređivati sistemske kategorije
- uređivati države i gradove
- naplaćivati kroz platformu

---

### 4.3. Administrator

Administrator ima punu kontrolu.

Može:

- upravljati korisnicima
- upravljati fotografima/videografima
- odobravati profile
- deaktivirati profile
- označiti profil kao verifikovan
- označiti profil kao istaknut
- upravljati državama
- upravljati gradovima/mjestima/opštinama
- upravljati kategorijama
- upravljati globalnim blogom
- moderirati fotografove blog članke
- moderirati portfolio slike
- uređivati SEO meta podatke
- pregledati statistike
- upravljati jezicima
- upravljati prevodima statičkog sadržaja

---

## 5. Tip profila fotografa

Fotograf/videograf može biti:

---

### 5.1. Fizičko lice

Primjer:

- Marko Petrović
- Ana Jovanović
- Ivana Marković

Prikaz na profilu:

- Fizičko lice
- Fotograf
- Videograf
- Fotograf + videograf

Polja:

- ime
- prezime
- javni naziv profila
- opis
- kontakt
- društvene mreže
- kategorije
- lokacije rada

---

### 5.2. Pravno lice / firma

Primjer:

- Studio Vision d.o.o.
- Foto Studio Marković
- Creative Lens Agency

Prikaz na profilu:

- Registrovana firma
- Foto studio
- Video produkcija
- Fotograf + videograf

Polja:

- naziv firme
- poreski broj / PIB / OIB / VAT broj, opciono
- javni naziv profila
- opis
- kontakt
- web stranica
- društvene mreže
- kategorije
- lokacije rada

Napomena:

U MVP-u PIB/OIB/VAT može biti opciono i ne mora biti javno prikazan.

---

## 6. Podržane države i lokacije

Sistem mora imati hijerarhiju:

**Država → Grad/mjesto/opština**

Primarne države:

- Bosna i Hercegovina
- Srbija
- Hrvatska

Kasnije se mogu dodati:

- Crna Gora
- Slovenija
- Austrija
- Njemačka
- Švajcarska

Admin mora moći ručno dodavati:

- državu
- grad/mjesto/opštinu
- slug
- SEO naziv
- meta title
- meta description
- uvodni SEO tekst
- status aktivno/neaktivno
- redoslijed prikaza

---

## 7. Jezici i lokalizacija

Sistem mora biti pripremljen za više jezika.

Početni jezici:

- BHS / srpski / hrvatski / bosanski
- engleski

Kasnije moguće:

- njemački
- slovenački

Za MVP može postojati jedan glavni jezik, ali arhitektura mora podržati prevode.

Potrebno omogućiti prevode za:

- nazive kategorija
- opise kategorija
- nazive država
- nazive gradova ako je potrebno
- SEO meta title
- SEO meta description
- SEO tekstove
- FAQ pitanja i odgovore
- blog članke
- statičke UI tekstove

Predložena URL struktura za jezike:

- `/ba/fotografi/svadbe/doboj`
- `/rs/fotografi/svadbe/beograd`
- `/hr/fotografi/vjencanja/zagreb`
- `/en/photographers/weddings/belgrade`

Za prvu verziju može se koristiti bez prefiksa jezika ako je fokus lokalni BHS, ali kod mora biti pripremljen za kasnije dodavanje prefiksa.

---

## 8. Kategorije usluga

Admin upravlja kategorijama.

Početne kategorije:

- Svadbe
- Vjenčanja
- Krštenja
- Rođendani
- Prvi rođendan
- 18. rođendan
- Matura
- Portreti
- Porodično fotografisanje
- Fotografisanje proizvoda
- Enterijer
- Eksterijer
- Nekretnine
- Komercijalno fotografisanje
- Eventi
- Korporativni događaji
- Muzički spotovi
- Reklamni video
- Dron fotografija
- Dron video
- Snimanje rođendana
- Snimanje svadbe
- Snimanje vjenčanja
- Snimanje iz zraka
- Studio fotografisanje
- Lifestyle fotografisanje
- Modna fotografija
- Fotografisanje hrane
- Fotografisanje hotela i apartmana

Svaka kategorija mora imati:

- naziv
- slug
- opis
- SEO title
- SEO description
- glavnu sliku
- ikonicu, opciono
- aktivno/neaktivno
- redoslijed
- prevode

---

## 9. Dostupnost fotografa

Osnovno pravilo:

**Svi datumi su slobodni po defaultu.**

Fotograf označava samo dane kada nije slobodan.

Dostupnost se vodi:

- samo po danu
- bez sati
- bez vremenskih slotova

Primjer:

Fotograf označi zauzeto:

- 15.06.2026.
- 22.06.2026.
- 01.07.2026.

Ako posjetilac traži fotografa za 15.06.2026, taj fotograf se ne prikazuje u rezultatima ili se prikazuje kao zauzet ako je uključena opcija prikaza svih.

Za MVP preporuka:

- po defaultu prikazivati samo dostupne ako je datum odabran
- na profilu prikazati mini kalendar naredne 4 sedmice
- zauzeti dani označeni sivom bojom
- slobodni dani označeni zelenom bojom

---

## 10. Portfolio

Fotograf može kreirati više portfolio albuma.

Album može biti vezan za kategoriju.

Primjer:

- Svadbe
- Portreti
- Enterijer
- Rođendani
- Dron video
- Komercijalno

Svaki album ima:

- naslov
- slug
- opis
- kategoriju
- cover sliku
- redoslijed
- aktivno/neaktivno

Svaka slika ima:

- image_path
- webp_path
- title
- alt_text
- sort_order
- album_id

SEO pravilo:

Alt tekst slike je veoma važan.

Ako fotograf ne unese alt tekst, sistem ga automatski generiše.

Primjer alt teksta:

`Fotograf za svadbe u Doboju - Studio Vision`

`Portretno fotografisanje u Banjoj Luci - Marko Petrović`

`Fotografisanje enterijera u Beogradu - Creative Lens Studio`

---

## 11. Blog sistem

Sistem ima dva nivoa bloga.

---

### 11.1. Globalni blog

Globalni blog uređuje admin.

Primjeri članaka:

- Kako odabrati fotografa za vjenčanje
- Najljepše lokacije za vjenčanja u Bosni i Hercegovini
- Savjeti za fotografisanje važnih događaja
- Trendovi portretne fotografije
- Koliko košta fotograf za svadbu
- Kako odabrati videografa za rođendan
- Zašto je portfolio najvažniji kod izbora fotografa

Globalni blog se prikazuje na `/blog`.

---

### 11.2. Blog fotografa na profilu

Fotograf može pisati svoje blog članke.

Ti članci se prikazuju:

- na njegovom profilu
- eventualno u globalnom blog feedu ako admin odobri
- na SEO stranicama ako su relevantni

Primjeri fotografovih članaka:

- Svadba u Doboju - galerija i priča sa događaja
- Porodično fotografisanje u Banjoj Luci
- Kako izgleda fotografisanje enterijera za apartmane
- Rođendansko fotografisanje u Sarajevu
- Dron snimanje jedne vjenčane ceremonije

Fotografov blog članak mora imati:

- naslov
- slug
- excerpt
- content
- featured image
- galeriju slika
- kategoriju
- grad/mjesto
- SEO title
- SEO description
- status: draft / pending / published / rejected
- admin moderation
- published_at

Važno:

Fotograf ne objavljuje direktno bez kontrole ako je uključena moderacija.  
Admin može odobriti ili odbiti članak.

---

## 12. Javni dizajn i UI smjernice

Dizajn treba biti moderan, minimalistički, premium i fotografski.

Prema poslatim referencama, dizajn treba imati:

- elegantan logo prostor
- bijelu pozadinu
- tamni tekst
- serif headline font
- zaobljene kartice
- velike fotografske vizuale
- masonry portfolio grid
- sofisticirane filtere
- male pill tagove za kategorije
- status dostupnosti
- elegantan kalendar dostupnosti
- čiste profil kartice
- premium landing page hero
- blog kartice sa velikim slikama

Vizuelni osjećaj:

- premium
- miran
- kreativan
- fotografski
- moderan
- nije klasični stari direktorijum

Inspiracija:

- Airbnb search UX
- Behance portfolio
- WeddingWire
- The Knot
- moderni editorial blogovi
- premium photography portfolio sajtovi

---

## 13. Glavne javne stranice

### 13.1. Početna stranica `/`

Sadrži:

- header
- hero sekciju sa velikom fotografijom
- headline
- search bar
- popularne pretrage
- statistike
- popularne kategorije
- popularne gradove
- istaknute fotografe
- najnovije portfolio radove
- blog sekciju
- SEO tekst
- FAQ
- footer

Hero headline:

`Pronađite fotografa ili videografa za vaš događaj`

Search polja:

- kategorija
- država
- grad/mjesto
- datum
- dugme Pretraži

---

### 13.2. Pretraga `/fotografi`

Query parametri:

- country
- city
- category
- date
- service_type
- profile_type

Prikaz:

- naslov
- broj rezultata
- search input
- filteri
- grid/list toggle
- sorting
- kartice fotografa

Kartica fotografa:

- cover/portfolio preview
- display_name
- status dostupnosti
- tip usluge
- tip profila
- kategorije
- gradovi
- broj fotografija
- dugme za profil

---

### 13.3. Profil fotografa `/fotograf/{slug}`

Sadrži:

- veliki portfolio/cover grid na vrhu
- profilnu karticu
- ime/naziv
- dostupnost
- tip usluge
- tip profila
- gradove
- kategorije
- godine iskustva
- kontakt dugmad
- društvene mreže
- opis
- portfolio po kategorijama
- kalendar dostupnosti
- kontakt info
- fotografove blog članke

Kontakt dugmad:

- Pozovi
- E-mail
- Instagram
- Facebook
- TikTok
- YouTube
- LinkedIn
- Web

Bez kontakt forme.

---

### 13.4. Kategorije `/kategorije`

Grid kategorija sa velikim slikama.

Primjeri:

- Svadbe
- Vjenčanja
- Krštenja
- Rođendani
- Portreti
- Enterijer
- Dron
- Spotovi

---

### 13.5. Gradovi `/gradovi`

Stranica sa državama i gradovima.

Primjer:

Bosna i Hercegovina:

- Banja Luka
- Sarajevo
- Tuzla
- Mostar
- Doboj
- Modriča
- Bijeljina
- Brčko

Srbija:

- Beograd
- Novi Sad
- Niš
- Kragujevac

Hrvatska:

- Zagreb
- Split
- Rijeka
- Osijek

---

### 13.6. SEO kategorija stranica

Ruta:

`/fotografi/{categorySlug}`

Primjer:

`/fotografi/svadbe`

H1:

`Fotografi za svadbe`

---

### 13.7. SEO grad stranica

Ruta:

`/fotografi/grad/{citySlug}`

Primjer:

`/fotografi/grad/modrica`

H1:

`Fotografi u Modriči`

---

### 13.8. SEO država + grad stranica

Ruta:

`/{countrySlug}/fotografi/{citySlug}`

Primjer:

`/bih/fotografi/modrica`

H1:

`Fotografi u Modriči`

---

### 13.9. SEO kategorija + grad stranica

Ruta:

`/{countrySlug}/fotografi/{categorySlug}/{citySlug}`

Primjer:

`/bih/fotografi/svadbe/doboj`

H1:

`Fotograf za svadbe u Doboju`

Ovo je najvažniji SEO tip stranice.

---

### 13.10. Blog `/blog`

Prikazuje globalne blog članke.

---

### 13.11. Blog detalj `/blog/{slug}`

Prikaz jednog blog članka.

---

### 13.12. Fotografov blog članak

Ruta:

`/fotograf/{photographerSlug}/blog/{postSlug}`

Primjer:

`/fotograf/studio-vision/blog/svadba-u-doboju`

---

## 14. SEO sistem

SEO je najvažniji modul.

Svaka javna stranica mora imati:

- meta title
- meta description
- canonical URL
- OpenGraph title
- OpenGraph description
- OpenGraph image
- Twitter card
- JSON-LD schema
- breadcrumbs
- indeksabilan HTML
- SEO-friendly URL
- sitemap entry

---

### 14.1. SEO title šabloni

Profil:

`{display_name} - {service_type_label} | {primary_city}`

Primjer:

`Ivana Marković - Fotograf | Sarajevo`

Kategorija:

`Fotografi za {category} | FotoMajstor`

Grad:

`Fotografi u {city} | FotoMajstor`

Kategorija + grad:

`Fotograf za {category} u {city} | FotoMajstor`

Država + grad:

`Fotografi u {city}, {country} | FotoMajstor`

Blog:

`{blog_title} | FotoMajstor`

Fotografov blog:

`{blog_title} - {photographer_name}`

---

### 14.2. SEO description šabloni

Kategorija + grad:

`Pronađite fotografe i videografe za {category} u mjestu {city}. Pregledajte portfolio, dostupnost, kontakt podatke i direktno kontaktirajte odabranog fotografa.`

Grad:

`Pretražite fotografe i videografe u mjestu {city}. Pogledajte profile, kategorije, portfolio radove i kontakt podatke profesionalaca.`

Profil:

`Pogledajte profil, portfolio, dostupnost i kontakt podatke za {display_name}. Kategorije: {categories}. Lokacije: {cities}.`

---

### 14.3. Sitemap

Generisati `/sitemap.xml` za:

- homepage
- sve aktivne profile
- sve kategorije
- sve države
- sve gradove
- sve kombinacije kategorija + grad
- globalne blog članke
- fotografove blog članke
- portfolio albume, opciono

---

### 14.4. Robots.txt

Generisati `/robots.txt`.

Dozvoliti indeksiranje javnih stranica.

Zabraniti:

- `/admin`
- `/dashboard`
- `/login`
- `/register`
- interne API rute
- preview/draft rute

---

### 14.5. Structured data

Implementirati JSON-LD:

- Organization
- WebSite
- BreadcrumbList
- LocalBusiness / ProfessionalService za profile
- BlogPosting za blog
- ImageObject za portfolio slike
- FAQPage za SEO landing stranice

---

## 15. Baza podataka

### 15.1. users

- id
- name
- email
- password
- role enum: admin, photographer
- email_verified_at nullable
- created_at
- updated_at

---

### 15.2. countries

- id
- name
- slug unique
- code nullable
- default_language nullable
- meta_title nullable
- meta_description nullable
- intro_text nullable
- active boolean default true
- sort_order integer default 0
- created_at
- updated_at

---

### 15.3. cities

- id
- country_id FK
- name
- slug
- region nullable
- meta_title nullable
- meta_description nullable
- intro_text nullable
- active boolean default true
- sort_order integer default 0
- created_at
- updated_at

Unique:

- country_id + slug

---

### 15.4. photographer_profiles

- id
- user_id FK
- profile_type enum: individual, company
- service_type enum: photographer, videographer, photographer_videographer
- display_name
- first_name nullable
- last_name nullable
- company_name nullable
- company_tax_number nullable
- slug unique
- profile_image nullable
- cover_image nullable
- about text nullable
- experience_years nullable integer
- phone nullable
- secondary_phone nullable
- public_email nullable
- website nullable
- primary_country_id nullable FK
- primary_city_id nullable FK
- verified boolean default false
- active boolean default false
- featured boolean default false
- profile_views integer default 0
- created_at
- updated_at

---

### 15.5. photographer_social_links

- id
- photographer_profile_id FK
- instagram nullable
- facebook nullable
- tiktok nullable
- youtube nullable
- linkedin nullable
- created_at
- updated_at

---

### 15.6. categories

- id
- name
- slug unique
- description nullable
- image nullable
- icon nullable
- meta_title nullable
- meta_description nullable
- intro_text nullable
- active boolean default true
- sort_order integer default 0
- created_at
- updated_at

---

### 15.7. photographer_category

- photographer_profile_id FK
- category_id FK

---

### 15.8. photographer_city

- photographer_profile_id FK
- city_id FK

---

### 15.9. photographer_country

- photographer_profile_id FK
- country_id FK

Napomena:

Ako fotograf odabere grad, zemlja se može izvesti iz grada, ali ova tabela može pomoći kod fotografa koji rade na nivou cijele države.

---

### 15.10. portfolio_albums

- id
- photographer_profile_id FK
- category_id nullable FK
- title
- slug
- description nullable
- cover_image nullable
- sort_order integer default 0
- active boolean default true
- created_at
- updated_at

---

### 15.11. portfolio_images

- id
- portfolio_album_id FK
- image_path
- webp_path nullable
- title nullable
- alt_text nullable
- sort_order integer default 0
- created_at
- updated_at

---

### 15.12. unavailable_dates

- id
- photographer_profile_id FK
- date date
- note nullable
- created_at
- updated_at

Unique:

- photographer_profile_id + date

---

### 15.13. blog_posts

Globalni blog.

- id
- author_id FK nullable
- title
- slug unique
- excerpt nullable
- content longtext
- featured_image nullable
- category_id nullable FK
- city_id nullable FK
- country_id nullable FK
- meta_title nullable
- meta_description nullable
- status enum: draft, published
- published_at nullable
- created_at
- updated_at

---

### 15.14. photographer_blog_posts

Fotografovi blog članci.

- id
- photographer_profile_id FK
- title
- slug
- excerpt nullable
- content longtext
- featured_image nullable
- category_id nullable FK
- city_id nullable FK
- country_id nullable FK
- meta_title nullable
- meta_description nullable
- status enum: draft, pending, published, rejected
- rejection_reason nullable
- published_at nullable
- created_at
- updated_at

Unique:

- photographer_profile_id + slug

---

### 15.15. photographer_blog_images

- id
- photographer_blog_post_id FK
- image_path
- webp_path nullable
- title nullable
- alt_text nullable
- sort_order integer default 0
- created_at
- updated_at

---

### 15.16. profile_views

- id
- photographer_profile_id FK
- ip_hash nullable
- user_agent nullable
- viewed_at timestamp
- created_at
- updated_at

---

## 16. Dashboard fotografa

Rute:

- `/dashboard`
- `/dashboard/profile`
- `/dashboard/categories`
- `/dashboard/locations`
- `/dashboard/portfolio`
- `/dashboard/availability`
- `/dashboard/social-links`
- `/dashboard/blog`

---

### 16.1. Dashboard početna

Prikazuje:

- status profila
- broj pregleda
- broj portfolio slika
- broj kategorija
- broj gradova
- broj blog članaka
- upozorenja ako profil nije kompletan

---

### 16.2. Uređivanje profila

Fotograf uređuje:

- tip profila
- tip usluge
- display_name
- ime
- prezime
- naziv firme
- poreski broj, opciono
- profilnu sliku
- cover sliku
- opis
- godine iskustva
- telefon
- drugi telefon
- javni e-mail
- web stranicu
- primarnu državu
- primarni grad

Validacija:

- display_name obavezno
- profile_type obavezno
- service_type obavezno
- barem jedan kontakt obavezan: phone, public_email ili instagram
- about preporučeno minimum 100 karaktera

---

### 16.3. Kategorije

Fotograf bira više kategorija.

---

### 16.4. Lokacije

Fotograf bira:

- države u kojima radi
- gradove/mjesta/opštine u kojima radi

---

### 16.5. Portfolio

Fotograf može:

- kreirati album
- dodati opis albuma
- vezati album za kategoriju
- uploadovati slike
- promijeniti redoslijed
- obrisati sliku
- dodati alt tekst
- postaviti cover albuma

---

### 16.6. Dostupnost

Kalendar po mjesecima.

Funkcije:

- klik na dan = označi zauzeto
- ponovni klik = ukloni zauzetost
- nema sati
- nema termina
- nema rezervacija

---

### 16.7. Blog fotografa

Fotograf može:

- kreirati članak
- dodati naslov
- dodati slug
- dodati kratak opis
- dodati sadržaj
- dodati glavnu sliku
- dodati galeriju slika
- vezati članak za kategoriju
- vezati članak za grad
- poslati članak na odobrenje

Statusi:

- draft
- pending
- published
- rejected

---

## 17. Admin panel

Koristiti Filament.

Admin resursi:

- UserResource
- PhotographerProfileResource
- CountryResource
- CityResource
- CategoryResource
- PortfolioAlbumResource
- PortfolioImageResource
- BlogPostResource
- PhotographerBlogPostResource
- ProfileViewResource

Admin mora moći:

- dodati državu
- dodati grad/mjesto
- dodati kategoriju
- odobriti profil
- deaktivirati profil
- verifikovati profil
- istaknuti profil
- odobriti fotografov blog
- odbiti fotografov blog uz razlog
- urediti SEO meta podatke
- pregledati portfolio
- obrisati neprikladan sadržaj

---

## 18. Search logika

Input:

- country nullable
- city nullable
- category nullable
- date nullable
- service_type nullable
- profile_type nullable

Query:

- samo active profile
- ako country postoji: gdje profil radi u toj državi ili ima gradove u toj državi
- ako city postoji: whereHas cities
- ako category postoji: whereHas categories
- ako service_type postoji: filtriraj service_type
- ako profile_type postoji: filtriraj individual/company
- ako date postoji: isključi fotografe koji imaju unavailable_dates.date = selected date

Sortiranje:

1. featured DESC
2. verified DESC
3. profile_views DESC
4. created_at DESC

---

## 19. Validacije

### Registracija fotografa

- name required
- email required unique
- password required confirmed
- role automatski photographer

Admin se ne registruje javno.

---

### Profil

- profile_type required
- service_type required
- display_name required
- public_email nullable email
- website nullable url
- experience_years nullable integer min 0 max 80
- mora postojati barem jedan kontakt: phone, public_email ili instagram

---

### Slike

- jpg, jpeg, png, webp
- max 5MB po slici
- automatski resize
- automatski WebP
- lazy loading na frontendu

---

### Blog

- title required
- content required
- slug unique po autoru
- featured_image nullable
- status prema ulozi

---

## 20. Seed podaci

Za development seedovati:

- admin korisnika
- države: BiH, Srbija, Hrvatska
- gradove za svaku državu
- kategorije
- 30 fotografa
- 10 videografa
- fizička lica i firme
- portfolio albume
- portfolio slike
- zauzete datume
- globalne blog članke
- fotografove blog članke

---

## 21. Tehnologija

Preporučeni stack:

- Laravel 12
- PHP 8.3+
- MySQL
- Inertia + React
- Tailwind CSS
- Filament Admin
- Laravel Storage
- WebP image optimization
- SSR/prerendering gdje je potrebno za SEO
- Automatski sitemap
- Robots.txt
- JSON-LD schema

---

## 22. Implementacioni redoslijed

1. Laravel setup
2. Auth setup
3. Role system
4. Migracije
5. Modeli i relacije
6. Seederi
7. Admin panel
8. Photographer dashboard
9. Country/city/category management
10. Portfolio upload
11. Availability calendar
12. Photographer blog
13. Public homepage
14. Search results
15. Photographer profile page
16. Category landing pages
17. Country/city landing pages
18. Category + city SEO pages
19. Global blog
20. Photographer blog public pages
21. SEO meta system
22. Sitemap
23. Robots.txt
24. JSON-LD
25. Responsive polish
26. Testing

---

## 23. Testirati

Obavezno testirati:

- registraciju fotografa
- kreiranje profila fizičkog lica
- kreiranje profila firme
- izbor države
- izbor grada
- izbor kategorije
- upload portfolija
- WebP konverziju
- alt tekst slika
- označavanje zauzetih datuma
- pretragu po državi
- pretragu po gradu
- pretragu po kategoriji
- pretragu po datumu
- skrivanje zauzetih fotografa
- profil fotografa
- kontakt linkove
- fotografov blog
- admin odobrenje profila
- admin odobrenje bloga
- SEO URL strukturu
- sitemap
- robots.txt
- mobilni prikaz

---

## 24. Konačni MVP rezultat

MVP mora omogućiti:

- fotograf se registruje
- fotograf pravi profil
- fotograf bira fizičko lice ili firma
- fotograf bira države i gradove gdje radi
- fotograf bira kategorije
- fotograf dodaje portfolio
- fotograf označava zauzete dane
- fotograf piše blog članke za svoj profil
- admin odobrava profil i članke
- posjetilac pretražuje po državi, gradu, kategoriji i datumu
- posjetilac otvara profil
- posjetilac direktno kontaktira fotografa
- sistem automatski pravi SEO stranice za najvažnije pojmove
- sistem ima globalni blog
- sistem ima sitemap i robots.txt

Glavni fokus projekta:

**SEO, moderan dizajn, jednostavan kontakt, kvalitetni profili, portfolio sadržaj i profesionalna lokalna pretraga.**
