<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades;

use Illuminate\View\View;

class ConfigurationServiceProvider extends ServiceProvider
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
        //$this->menuItems = ["Home", "About Us", "Contact"];


        // Using closure based composers...
        Facades\View::composer('front.layouts.master', function (View $view) {
            $view->with(['generalSetting' => \App\Models\GeneralSetting::find('1'),
                           //'testimonials' => \App\Models\Testimonial::where('status', '=', 1)->orderBy('id', 'DESC')->get(),

                        ]);

        });


        Facades\View::composer('front.elements.side-testimonial', function (View $view) {
            $view->with(['testimonials' => \App\Models\Testimonial::where('status', '=', 1)->orderBy('id', 'DESC')->get(),

                        ]);

        });


    }
}
