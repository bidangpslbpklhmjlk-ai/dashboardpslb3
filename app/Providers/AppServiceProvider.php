<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator; // <--- TAMBAH INI
use App\Models\Page;
use App\Models\GatepassLog;                     
use App\Observers\GatepassLogObserver;  

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
        // View Composer untuk Sidebar
        View::composer('components.admin-sidebar', function ($view) {
            $view->with('pages', Page::orderBy('category')->get());
        });

        // Pagination style Tailwind
        Paginator::useTailwind();
        GatepassLog::observe(GatepassLogObserver::class);
    }
}