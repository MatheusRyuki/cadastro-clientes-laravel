<?php

namespace App\Providers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));
        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('pagination::simple-tailwind');

        View::composer('layouts.customers', function ($view) {
            $view->with('trashCount', Customer::onlyTrashed()->count());
        });
    }
}
