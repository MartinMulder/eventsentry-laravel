<?php

namespace MartinMulder\EventSentry\Laravel;

use MartinMulder\EventSentry\Laravel\EventSentry;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class EventSentryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        /**
         * Merging app config with the default configfile,
         * so users can implement the config partialy.
         *
         * NOTE: This only merges the first level of a multi-dimentional array!
         */
        $this->mergeConfigFrom(
        	__DIR__."/../config/eventsentry.php", "eventsentry"
        );

        Log::info('Starting eventsentry service provider');
        $this->app->singleton('eventsentry', function ($app) {
            Log::debug("building eventsentry client");
        		return new EventSentry(
                    $app['config']['eventsentry']['connections']['default']['host'],
                    $app['config']['eventsentry']['connections']['default']['user'],
                    $app['config']['eventsentry']['connections']['default']['password']
                );
        	}
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Loading routes
        $this->loadRoutesFrom(__DIR__."/../routes/web.php");

        // Loading migrations
        // $this->loadMigrationsFrom(__DIR__."/../database/migrations");

        // Loading translations
        // $this->loadTranslationsFrom(__DIR__."/../resources/lang", 'eventsentry');

        // Loading views
        $this->loadViewsFrom(__DIR__."/../resources/views", 'eventsentry');

        // Publishes files
        $this->publishes([
        	__DIR__."/../config/eventsentry.php" => config_path('eventsentry.php'),
        	//__DIR__."/../resources/lang" => resources_path('lang/vendor/eventsentry'),
        	__DIR__."/../resources/views" => resource_path('views/vendor/eventsentry'),
        ]);
    }
}
