<?php

namespace Jdikasa\LaravelRepositoryPattern\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class MakeModelWithPatternCommand extends Command
{
    protected $signature = 'make:model {name} {--with-pattern} {--force}';
    protected $description = 'Create a model with Repository pattern components';

    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        $name = $this->argument('name');
        $withPattern = $this->option('with-pattern');
        $force = $this->option('force');

        if (!$withPattern) {
            $this->call('make:model', ['name' => $name]);
            return;
        }

        $this->info("🚀 Génération du pattern Repository pour : {$name}");
        
        $components = [
            'model' => 'Model',
            'repository' => 'Repository', 
            'service' => 'Service',
            'controller' => 'Controller',
            'transformer' => 'Transformer',
            'request' => 'Request',
        ];
        
        foreach ($components as $type => $suffix) {
            $this->info("✓ Génération du {$type}");
            if ($type === 'request') {
                $prefix = 'Store';
                $this->generateComponent($name, $type, $prefix, $suffix, $force);
                $this->info("✓ {$type} pour {$prefix} généré");
                $prefix = 'Update';
                $this->generateComponent($name, $type,  $prefix, $suffix, $force);
                $this->info("✓ {$type} pour {$prefix} généré");
            }else {
                $this->generateComponent($name, $type, '', $suffix, $force);
                $this->info("✓ {$type} généré");
            }
        }

        $this->displaySummary($name);
    }

    protected function generateComponent($name, $type, $prefix, $suffix, $force)
    {
        try {
            if ($type === 'model') {
                $this->call('make:model', ['name' => $name]);
                $this->line("  ✓ {$name} model created");
                return;
            }
    
            $className = "{$prefix}{$name}{$suffix}";
            $stub = $this->getStub($type);
            $content = $this->replaceStubVariables($stub, $name, $prefix ? $className : '');
            
            $path = $this->getPath($type, $name, $className);
            
            if ($this->files->exists($path) && !$force) {
                $this->error("  ✗ {$className} already exists (use --force to overwrite)");
                return;
            }
    
            $this->ensureDirectoryExists(dirname($path));
            $this->files->put($path, $content);
            
            $this->line("  ✓ {$className} created");
        } catch (\Exception $e) {
            $this->error("  ✗ Erreur lors de la génération de {$className}: {$e->getMessage()}");
        }
    }

    protected function getPath($type, $name, $className)
    {
        $paths = [
            'repository' => app_path("Repositories/{$className}.php"),
            'service' => app_path("Services/{$className}.php"),
            'controller' => app_path("Http/Controllers/{$className}.php"),
            'transformer' => app_path("Transformers/{$className}.php"),
            'request' => app_path("Http/Requests/{$name}/{$className}.php"),
        ];

        return $paths[$type];
    }

    protected function getStub($type)
    {
        // Chercher d'abord dans les stubs publiés
        $customStubPath = resource_path("stubs/repository-pattern/{$type}.stub");
        if ($this->files->exists($customStubPath)) {
            return $this->files->get($customStubPath);
        }

        // Sinon utiliser les stubs du package
        $packageStubPath = __DIR__ . "/../Stubs/{$type}.stub";
        return $this->files->get($packageStubPath);
    }

    protected function replaceStubVariables($stub, $name, $requestName)
    {
        $config = config('repository-pattern', []);
        
        $replacements = [
            '{{ModelName}}' => $name,
            '{{RequestName}}' => $requestName,
            '{{ModelNameLowercase}}' => Str::camel($name),
            '{{ModelNamePlural}}' => Str::plural(Str::camel($name)),
            '{{ModelNameKebab}}' => Str::kebab($name),
            '{{ModelNameSnake}}' => Str::snake($name),
            '{{RepositoryNamespace}}' => $config['repository_namespace'] ?? 'App\\Repositories',
            '{{ServiceNamespace}}' => $config['service_namespace'] ?? 'App\\Services',
            '{{TransformerNamespace}}' => $config['transformer_namespace'] ?? 'App\\Transformers',
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