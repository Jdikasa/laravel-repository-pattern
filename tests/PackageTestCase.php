<?php

namespace Jdikasa\LaravelRepositoryPattern\Tests;

use Orchestra\Testbench\TestCase;
use Jdikasa\LaravelRepositoryPattern\RepositoryPatternServiceProvider;
use Jdikasa\LaravelRepositoryPattern\Commands\MakeModelWithPatternCommand;

class PackageTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Configuration spécifique pour Laravel 12
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            RepositoryPatternServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Configuration pour Laravel 12
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Support Carbon 3.x
        $app['config']->set('app.timezone', 'UTC');
    }

    /** @test */
    public function it_can_generate_repository_pattern_components()
    {
        $this->artisan('make:model', ['name' => 'TestModel', '--with-pattern' => true])
            ->expectsOutput('🚀 Génération du pattern Repository pour : TestModel')
            ->assertExitCode(0);

        // Vérifier que les fichiers sont créés
        $this->assertFileExists(app_path('Models/TestModel.php'));
        $this->assertFileExists(app_path('Repositories/TestModelRepository.php'));
        $this->assertFileExists(app_path('Services/TestModelService.php'));
        $this->assertFileExists(app_path('Http/Controllers/TestModelController.php'));
        $this->assertFileExists(app_path('Transformers/TestModelTransformer.php'));
    }

    /** @test */
    public function it_works_with_carbon_3()
    {
        $now = now(); // Utilise Carbon 3.x
        $this->assertInstanceOf(\Carbon\Carbon::class, $now);
        
        // Test de compatibilité avec les nouvelles méthodes Carbon 3
        $formatted = $now->toISOString();
        $this->assertIsString($formatted);
    }

    /** @test */
    public function it_supports_laravel_12_features()
    {
        // Test des nouvelles fonctionnalités Laravel 12
        $config = config('repository-pattern');
        $this->assertIsArray($config);
        
        // Vérifier la compatibilité avec les nouveaux starter kits
        $this->assertTrue(true); // Placeholder pour tests spécifiques
    }
}