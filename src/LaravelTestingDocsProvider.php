<?php

namespace Tenorio\Laravel\Testing\Docs;

use Illuminate\Support\ServiceProvider;

class LaravelTestingDocsProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/views', 'testingdocs');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        include __DIR__.'/routes.php';
        $this->app->make('Tenorio\Laravel\Testing\Docs\LaravelTestingDocsController');
    }
}
