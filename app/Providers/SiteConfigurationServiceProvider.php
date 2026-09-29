<?php

namespace App\Providers;

use Illuminate\Support\Facades;

use Illuminate\Support\ServiceProvider;

use Illuminate\View\View;

class SiteConfigurationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //adding site setting data for usage in common templates
        //$this->settings = ;
        $this->menuItems = ["Home", "About Us", "Contact"];
        //\App\Models\GeneralSetting::find('1');
        // view()->composer('front.elements.head', function ($view) {
        //     $view->with(['general' => $this->menuItems]);
        // });


        // Using closure based composers...
        Facades\View::composer('front.elements.head', function (View $view) {
            $view->with(['general' => $this->menuItems]);

        });


    }
}
