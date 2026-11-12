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

        $this->registerBusinessSearchIndexing();
    }

    /**
     * Keep the directory search index (App\Services\BusinessSearchIndex) in step
     * with edits. Affected businesses are re-indexed after the response is sent.
     */
    private function registerBusinessSearchIndexing(): void
    {
        $touch = fn ($ids) => \App\Services\BusinessSearchIndex::touch($ids);
        $companiesOfUser = fn ($umId) => \Illuminate\Support\Facades\DB::table('user_companies_map')
            ->where('ucm_um_id', $umId)->pluck('ucm_cmp_id')->all();

        foreach (['saved', 'deleted'] as $event) {
            \App\Models\Companies\CompaniesDetail::$event(fn ($m) => $touch($m->cmpd_cmp_id));
            \App\Models\BusinessPortfolio::$event(fn ($m) => $touch($m->company_id));
            \App\Models\BusinessPortfolioItem::$event(fn ($m) => $touch($m->company_id));
            \App\Models\Companies\CategoryToCompany::$event(fn ($m) => $touch($m->ctc_cmp_id));
            \App\Models\User\UserToCompanies::$event(fn ($m) => $touch($m->ucm_cmp_id));
            \App\Models\User\UserDetails::$event(fn ($m) => $touch($companiesOfUser($m->ud_um_id)));
        }
        \App\Models\User\UserMaster::saved(function ($m) use ($touch, $companiesOfUser) {
            if ($m->wasChanged(['um_status', 'um_user_name']) || $m->wasRecentlyCreated) {
                $touch($companiesOfUser($m->um_id));
            }
        });
        \App\Models\Business\BusinessCategoryMaster::saved(function ($m) use ($touch) {
            if ($m->wasChanged('name')) {
                $touch(\Illuminate\Support\Facades\DB::table('categories_to_companies')->where('ctc_bcm_id', $m->bcm_id)->pluck('ctc_cmp_id')->all());
            }
        });
    }
}
