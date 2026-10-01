<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

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
        // The application uses Bootstrap in its shared layouts. Laravel's
        // Tailwind paginator leaves its SVG sizing classes undefined here,
        // causing the previous/next icons to expand across the page.
        Paginator::useBootstrapFive();
    }
}
