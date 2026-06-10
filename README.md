# FotoMreža

SEO-first katalog i portfolio direktorijum fotografa i videografa za BiH, Srbiju, Hrvatsku i regiju.

> Platforma je **katalog** — nema online plaćanja, rezervacija ni kontakt formi. Posjetioci kontaktiraju fotografe direktno (telefon, e-mail, društvene mreže).

## Tehnologija

- **Laravel 12** (PHP 8.2+)
- **Blade + Tailwind CSS v4 + Alpine.js** za javni, server-renderovani (SEO-friendly) frontend
- **Filament v3** za dva panela:
  - `/admin` — administratorski panel
  - `/dashboard` — panel za fotografe (self-service)
- **Intervention Image** za obradu slika
- **SQLite** za razvoj (zero-config), **MySQL** za produkciju (vidi `.env.example`)

## Pokretanje (development)

```bash
composer install
npm install
cp .env.example .env        # već postoji .env za dev (SQLite)
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
npm run build               # ili: npm run dev
php artisan serve
```

Aplikacija: `http://127.0.0.1:8000`

### Pristupni podaci (nakon seed-a)

- **Admin:** `admin@fotomreza.example` / `password` → `/admin`
- **Fotograf (primjer):** bilo koji `*@example.com` / `password` → `/dashboard`
- Novi fotgrafi se registruju na `/dashboard/register`

## Produkcija (MySQL)

U `.env` postaviti:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pronadji_fotografa
DB_USERNAME=...
DB_PASSWORD=...
```

## Struktura

| Sloj | Lokacija |
|------|----------|
| Enumi | `app/Enums` |
| Modeli | `app/Models` |
| Javni kontroleri | `app/Http/Controllers` |
| SEO helper (JSON-LD) | `app/Support/Seo.php` |
| Admin paneli | `app/Filament/Resources`, `app/Providers/Filament/AdminPanelProvider.php` |
| Dashboard fotografa | `app/Filament/Dashboard`, `DashboardPanelProvider.php` |
| Javni viewovi | `resources/views/public`, `layouts`, `partials`, `components` |
| Seederi | `database/seeders` |

## SEO rute

- `/` — početna
- `/fotografi` — pretraga (filteri: kategorija, država, grad, datum, tip usluge)
- `/fotografi/{kategorija}` — landing po kategoriji
- `/fotografi/grad/{grad}` — landing po gradu
- `/{drzava}/fotografi/{grad}` — landing država + grad
- `/{drzava}/fotografi/{kategorija}/{grad}` — landing kategorija + grad
- `/fotograf/{slug}` — profil fotografa
- `/fotograf/{slug}/blog/{post}` — članak fotografa
- `/kategorije`, `/gradovi`, `/blog`, `/blog/{post}`
- `/sitemap.xml`, `/robots.txt`

SEO sloj: meta title/description, canonical, OpenGraph, Twitter card, JSON-LD (Organization, WebSite+SearchAction, ProfessionalService, BreadcrumbList, BlogPosting, FAQPage), automatski sitemap i robots.

## Testovi

```bash
php artisan test
```

`tests/Feature/SmokeTest.php` pokriva javne rute, filtere pretrage, SEO landing stranice te kontrolu pristupa admin/dashboard panelima po ulozi.
