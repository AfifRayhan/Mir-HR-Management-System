<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
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
        \Illuminate\Support\Facades\View::composer(['partials.ui-sidebar', 'partials.employee-sidebar'], function ($view) {
            $menuService = app(\App\Services\MenuService::class);
            $view->with('sidebarItems', $menuService->getSidebarMenu());
        });
    }
}
