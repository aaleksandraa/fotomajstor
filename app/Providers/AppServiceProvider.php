<?php

namespace App\Providers;

use App\Services\ImageService;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\CategoryAlias;
use App\Models\City;
use App\Models\Country;
use App\Models\Location;
use App\Models\PhotographerBlogPost;
use App\Models\PhotographerProfile;
use App\Models\PortfolioAlbum;
use App\Models\PortfolioImage;
use App\Models\PortfolioVideo;
use App\Models\PhotographerBlogImage;
use App\Observers\SeoContentObserver;
use App\Support\FilamentWebpUpload;
use Filament\Forms\Components\FileUpload;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale(config('app.locale', 'bs'));
        Paginator::useTailwind();

        if (app()->environment('production')) {
            URL::forceRootUrl(config('app.url'));
            URL::forceScheme('https');
        }

        foreach ([BlogPost::class, Category::class, CategoryAlias::class, City::class, Country::class, Location::class, PhotographerBlogImage::class, PhotographerBlogPost::class, PhotographerProfile::class, PortfolioAlbum::class, PortfolioImage::class, PortfolioVideo::class] as $model) {
            $model::observe(SeoContentObserver::class);
        }

        // WebP upload: brz temp upload, konverzija pri spremanju, relativni URL za preview.
        FileUpload::macro('webp', function (string $directory = 'uploads', int $maxWidth = 1600) {
            /** @var FileUpload $this */
            return $this
                ->image()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'])
                ->maxSize(12288)
                ->disk('public')
                ->directory($directory)
                ->visibility('public')
                ->fetchFileInformation(false)
                ->orientImagesFromExif(false)
                ->getUploadedFileUsing(fn (FileUpload $component, string $file, string | array | null $storedFileNames) => FilamentWebpUpload::uploadedFileMeta($component, $file, $storedFileNames))
                ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file) => FilamentWebpUpload::saveAsWebp($file, $directory, $maxWidth));
        });
    }
}
