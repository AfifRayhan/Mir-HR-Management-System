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
        \Illuminate\Support\Facades\View::composer('partials.ui-sidebar', function ($view) {
            $menuService = app(\App\Services\MenuService::class);
            $sidebarItems = $menuService->getSidebarMenu();
            
            // Remove Employee Dashboard from HR Sidebar
            $filteredItems = $sidebarItems->filter(function ($item) {
                return $item->slug !== 'employee-dashboard';
            });

            $view->with('sidebarItems', $filteredItems);
        });

        \Illuminate\Support\Facades\View::composer('partials.employee-sidebar', function ($view) {
            $menuService = app(\App\Services\MenuService::class);
            $sidebarItems = $menuService->getSidebarMenu();
            
            // Remove HR Dashboard from Employee Sidebar (safety)
            $filteredItems = $sidebarItems->filter(function ($item) {
                return $item->slug !== 'hr-dashboard';
            });

            $view->with('sidebarItems', $filteredItems);
        });
    }
}
