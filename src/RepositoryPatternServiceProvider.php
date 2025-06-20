<?php

namespace Jdikasa\LaravelRepositoryPattern;

use Illuminate\Support\ServiceProvider;
use Jdikasa\LaravelRepositoryPattern\Commands\MakeModelWithPatternCommand;

class RepositoryPatternServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publier les stubs personnalisables
        $this->publishes([
            __DIR__.'/Stubs' => resource_path('Stubs/repository-pattern'),
        ], 'repository-pattern-stubs');

        // Publier la configuration
        $this->publishes([
            __DIR__.'/config/repository-pattern.php' => config_path('repository-pattern.php'),
        ], 'repository-pattern-config');

        // Publier les helpers
        $this->publishes([
            __DIR__.'/Helpers' => app_path('Helpers'),
        ],'repository-pattern-helpers');

        // Enregistrer les commandes - Compatible Laravel 12
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModelWithPatternCommand::class,
            ]);
        }

        // Merger la configuration
        $this->mergeConfigFrom(
            __DIR__.'/config/repository-pattern.php',
            'repository-pattern'
        );

        // Support pour les nouveaux starter kits Laravel 12
        $this->loadViewsFrom(__DIR__.'/resources/views', 'repository-pattern');
    }

    public function register()
    {
        // Enregistrer les services si nécessaire
        $this->app->singleton(MakeModelWithPatternCommand::class, function ($app) {
            return new MakeModelWithPatternCommand($app['files']);
        });
    }
}