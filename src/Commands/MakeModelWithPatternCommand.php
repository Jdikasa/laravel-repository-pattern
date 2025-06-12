<?php

namespace Jdikasa\LaravelRepositoryPattern\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;

class MakeModelWithPatternCommand extends Command
{
    protected $signature = 'make:model-pattern {name} {--force : Force overwrite existing files}';
    protected $description = 'Create a model with Repository pattern components';

    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        $config = config('repository-pattern', []);
        $name = $this->argument('name');
        $force = $this->option('force');

        $this->info("🚀 Génération du pattern Repository pour : {$name}");

        $components = [
            // 'model' => 'Model',
            'repository' => 'Repository',
            'service' => 'Service',
            'controller' => 'Controller',
            'transformer' => 'Transformer',
            'request' => 'Request',
        ];

        foreach ($components as $type => $suffix) {
            $this->info("✓ Génération du {$type}");

            if ($type === 'request') {
                if(!$config['generations']['request']) return;

                $prefix = $config['preffixes']['request']['store'] ?? 'Store';
                $result = $this->generateComponent($name, $type, $prefix, $suffix, $force);
                if ($result) {
                    $this->info("✓ {$type} pour {$prefix} généré");
                }

                $prefix = $config['preffixes']['request']['update'] ?? 'Update';
                $result = $this->generateComponent($name, $type, $prefix, $suffix, $force);
                if ($result) {
                    $this->info("✓ {$type} pour {$prefix} généré");
                }
            } else {
                if(!$config['generations'][$type]) return;

                $result = $this->generateComponent($name, $type, '', $suffix, $force);
                if ($result) {
                    $this->info("✓ {$type} généré");
                }
            }
        }

        $this->displaySummary($name);
    }

    protected function generateComponent($name, $type, $prefix, $suffix, $force)
    {
        try {
            if ($type === 'model') {
                Artisan::call('make:model', ['name' => $name]);
                $this->line("  ✓ {$name} model created");
                return true;
            }

            $className = "{$prefix}{$name}{$suffix}";

            // Debug: Afficher le nom de classe généré
            $this->info("Debug: Génération de {$className}");

            $stub = $this->getStub($type);
            if (!$stub) {
                $this->error("  ✗ Stub pour {$type} non trouvé");
                return false;
            }

            $content = $this->replaceStubVariables($stub, $name, $prefix ? $className : '');
            $path = $this->getPath($type, $name, $className);

            // Debug: Afficher le chemin
            $this->info("Debug: Chemin = {$path}");

            if ($this->files->exists($path) && !$force) {
                $this->error("  ✗ {$className} already exists (use --force to overwrite)");
                return false;
            }

            $this->ensureDirectoryExists(dirname($path));
            $result = $this->files->put($path, $content);

            if ($result === false) {
                $this->error("  ✗ Impossible d'écrire {$className}");
                return false;
            }

            $this->line("  ✓ {$className} created at {$path}");
            return true;
        } catch (\Exception $e) {
            $this->error("  ✗ Erreur lors de la génération de {$className}: {$e->getMessage()}");
            $this->error("  ✗ Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    protected function getPath($type, $name, $className)
    {
        $config = config('repository-pattern', []);
        $paths = [
            'repository' => Str::replace('//', '/', $config['paths']['repository'] . '/' . $className . '.php') ?? app_path("Repositories/{$className}.php"),
            'service' => Str::replace('//', '/', $config['paths']['service'] . '/' . $className . '.php') ?? app_path("Services/{$className}.php"),
            'controller' => Str::replace('//', '/', $config['paths']['controller'] . '/' . $className . '.php') ?? app_path("Http/Controllers/{$className}.php"),
            'transformer' => Str::replace('//', '/', $config['paths']['transformer'] . '/' . $className . '.php') ?? app_path("Transformers/{$className}.php"),
            'request' => Str::replace('//', '/', $config['paths']['request'] . '/' . $className . '.php') ?? app_path("Http/Requests/{$name}/{$className}.php"),
        ];

        return $paths[$type];
    }

    protected function getStub($type)
    {
        $config = config('repository-pattern', []);
        // Chercher d'abord dans les stubs publiés
        $customStubPath = $config['custom_stubs'][$type] ?? resource_path("stubs/repository-pattern/{$type}.stub");
        if ($this->files->exists($customStubPath)) {
            $this->info("Debug: Utilisation du stub personnalisé : {$customStubPath}");
            return $this->files->get($customStubPath);
        }

        // Sinon utiliser les stubs du package
        $packageStubPath = __DIR__ . "/../Stubs/{$type}.stub";

        $this->info("Debug: Tentative d'utilisation du stub package : {$packageStubPath}");

        if (!$this->files->exists($packageStubPath)) {
            $this->error("Debug: Stub non trouvé : {$packageStubPath}");
            return null;
        }

        return $this->files->get($packageStubPath);
    }

    protected function replaceStubVariables($stub, $name, $requestName)
    {
        $config = config('repository-pattern', []);

        $replacements = [
            // Model
            '{{ModelName}}' => $name,

            // Namespaces
            '{{ControllersNamespace}}' => Str::replace('/', '\\', $config['namespaces']['controller'])  ?? 'App\\Http\\Controllers',
            '{{ServicesNamespace}}' => Str::replace('/', '\\', $config['namespaces']['service'])  ?? 'App\\Services',
            '{{TransformersNamespace}}' => Str::replace('/', '\\', $config['namespaces']['transformer'])  ?? 'App\\Transformers',
            '{{RequestsNamespace}}' => Str::replace('/', '\\', $config['namespaces']['request'])  ?? 'App\\Http\\Requests',
            '{{RepositoriesNamespace}}' => Str::replace('/', '\\', $config['namespaces']['repository'])  ?? 'App\\Repositories',

            // Suffixes
            '{{ServiceSufix}}' => Str::ucfirst(Str::camel($config['suffixes']['service'] ?? 'Service')),
            '{{ControllerSufix}}' => Str::ucfirst(Str::camel($config['suffixes']['controller'] ?? 'Controller')),
            '{{TransformerSufix}}' => Str::ucfirst(Str::camel($config['suffixes']['transformer'] ?? 'Transformer')),
            '{{RepositorySufix}}' => Str::ucfirst(Str::camel($config['suffixes']['repository'] ?? 'Repository')),
            '{{RequestSufix}}' => Str::ucfirst(Str::camel($config['suffixes']['request'] ?? 'Request')),

            // Prefixes
            '{{ServicePrefix}}' => Str::ucfirst(Str::camel($config['preffixes']['service'] ?? '')),
            '{{ControllerPrefix}}' => Str::ucfirst(Str::camel($config['preffixes']['controller'] ?? '')),
            '{{TransformerPrefix}}' => Str::ucfirst(Str::camel($config['preffixes']['tronsformer'] ?? '')),
            '{{RepositoryPrefix}}' => Str::ucfirst(Str::camel($config['preffixes']['repository'] ?? '')),
            '{{RequestStorePrefix}}' => Str::ucfirst(Str::camel($config['preffixes']['request']['store'] ?? 'Store')),
            '{{RequestUpdatePrefix}}' => Str::ucfirst(Str::camel($config['preffixes']['request']['update'] ?? 'Update')),

            // Request class name
            '{{RequestName}}' => $requestName,

            '{{ModelNameLowercase}}' => Str::camel($name),
            '{{ModelNamePlural}}' => Str::plural(Str::camel($name)),
            '{{ModelNameKebab}}' => Str::kebab($name),
            '{{ModelNameSnake}}' => Str::snake($name),

            // '{{RepositoryNamespace}}' => $config['repository_namespace'] ?? 'App\\Repositories',
            // '{{ServiceNamespace}}' => $config['service_namespace'] ?? 'App\\Services',
            // '{{TransformerNamespace}}' => $config['transformer_namespace'] ?? 'App\\Transformers',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $stub);
    }

    protected function ensureDirectoryExists($directory)
    {
        if (!$this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }
    }

    protected function displaySummary($name)
    {
        $this->newLine();
        $this->info("📋 Résumé de la génération :");
        $this->table(
            ['Composant', 'Fichier', 'Emplacement'],
            [
                ['Model', "{$name}.php", 'app/Models/'],
                ['Repository', "{$name}Repository.php", 'app/Repositories/'],
                ['Service', "{$name}Service.php", 'app/Services/'],
                ['Controller', "{$name}Controller.php", 'app/Http/Controllers/'],
                ['Transformer', "{$name}Transformer.php", 'app/Transformers/'],
                ['Request', "Store{$name}Request.php", 'app/Http/Requests/{$name}/'],
                ['Request', "Update{$name}Request.php", 'app/Http/Requests/{$name}/'],
            ]
        );

        $this->newLine();
        $this->info("🎉 Pattern Repository généré avec succès pour {$name} !");
        $this->comment("💡 N'oubliez pas d'ajouter vos routes et validations.");
    }
}
