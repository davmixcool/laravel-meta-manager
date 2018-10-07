<?php

namespace Davmixcool\MetaManager;

use Illuminate\Support\ServiceProvider;

class MetaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/views', 'meta');

        $this->publishes([
            __DIR__.'/config/meta.php' => config_path('meta.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
