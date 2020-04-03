<?php

namespace Marshmallow\NovaDataImporter;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Marshmallow\NovaDataImporter\Http\Middleware\Authorize;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nova-data-importer');

        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'nova-data-importer');

        $this->publishes([
           __DIR__.'/../config/config.php' => config_path('nova-data-importer.php'),
        ]);

        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {

        });
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
                ->namespace(__NAMESPACE__ . '\\Http\\Controllers')
                ->prefix('nova-vendor/nova-data-importer')
                ->group(__DIR__.'/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
