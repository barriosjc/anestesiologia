<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\NomPadreRepository;
use App\Repositories\NomPadreRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(NomPadreRepositoryInterface::class, NomPadreRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Cache::flush();
    }
}
