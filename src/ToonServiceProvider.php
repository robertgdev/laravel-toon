<?php

namespace RobertGDev\LaravelToon;

use Illuminate\Support\ServiceProvider;
use RobertGDev\LaravelToon\Console\ToonCommand;

class ToonServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge default config
        $this->mergeConfigFrom(
            __DIR__.'/../config/toon.php',
            'toon'
        );

        // Register the Laravel Toon wrapper as a singleton
        $this->app->singleton('toon', function ($app) {
            return new Toon();
        });

        // Register the facade alias
        $this->app->alias('toon', Toon::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Register commands
            $this->commands([
                ToonCommand::class,
            ]);

            // Publish config file
            $this->publishes([
                __DIR__.'/../config/toon.php' => config_path('toon.php'),
            ], 'toon-config');
        }
    }
}