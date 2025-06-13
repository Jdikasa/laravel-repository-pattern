<?php

namespace Jdikasa\LaravelRepositoryPattern\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
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
        $result = [];

        $this->info("🚀 Génération du pattern Repository pour : {$name}");

        $components = [
            'model' => $config['suffixes']['model'] ?? '',
            'repository' => $config['suffixes']['repository'] ?? 'Repository',
            'service' => $config['suffixes']['service'] ?? 'Service',
            'controller' => $config['suffixes']['controller'] ?? 'Controller',
            'transformer' => $config['suffixes']['transformer'] ?? 'Transformer',
            'request' => $config['suffixes']['request'] ?? 'Request',
        ];

        foreach ($components as $type => $suffix) {
            $this->info("✓ Génération du {$type}");

            if ($type === 'request') {
                if(!$config['generations']['request']) return;

                $prefix = $config['preffixes']['request']['store'] ?? 'Store';
                $result[$type] = $this->generateComponent($name, $type, $prefix, $suffix, $force);
                if ($result[$type]) {
                    $this->info("✓ {$type} pour {$prefix} généré");
                }

                $prefix = $config['preffixes']['request']['update'] ?? 'Update';
                $result[$type] = $this->generateComponent($name, $type, $prefix, $suffix, $force);
                if ($result[$type]) {
                    $this->info("✓ {$type} pour {$prefix} généré");
                }
            } else {
                if(!$config['generations'][$type]) return;

                $result[$type] = $this->generateComponent($name, $type, '', $suffix, $force);
                if ($result[$type]) {
                    $this->info("✓ {$type} généré");
                }
            }
        }

        $this->displaySummary($name, $result);
    }

    protected function generateComponent($name, $type, $prefix, $suffix, $force)
    {
        try {
            // if ($type === 'model') {
            //     Artisan::call('make:model', ['name' => $name]);
            //     $this->line("  ✓ {$name} model created");
            //     return true;
            // }

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

            return $path;
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
            'model' => Str::replace('//', '/', $config['paths']['model'] . '/' . $className . '.php') ?? app_path("Models/{$className}.php"),
            'repository' => Str::replace('//', '/', $config['paths']['repository'] . '/' . $className . '.php') ?? app_path("Repositories/{$className}.php"),
            'service' => Str::replace('//', '/', $config['paths']['service'] . '/' . $className . '.php') ?? app_path("Services/{$className}.php"),
            'controller' => Str::replace('//', '/', $config['paths']['controller'] . '/' . $className . '.php') ?? app_path("Http/Controllers/{$className}.php"),
            'transformer' => Str::replace('//', '/', $config['paths']['transformer'] . '/' . $className . '.php') ?? app_path("Transformers/{$className}.php"),
            'request' => Str::replace('//', '/', $config['paths']['request'] . '/' . $name . '/' . $className . '.php') ?? app_path("Http/Requests/{$name}/{$className}.php"),
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
            '{{ModelsNamespace}}' => Str::replace('/', '\\', $config['namespaces']['model'])  ?? 'App\\Models',
            '{{ControllersNamespace}}' => Str::replace('/', '\\', $config['namespaces']['controller'])  ?? 'App\\Http\\Controllers',
            '{{ServicesNamespace}}' => Str::replace('/', '\\', $config['namespaces']['service'])  ?? 'App\\Services',
            '{{TransformersNamespace}}' => Str::replace('/', '\\', $config['namespaces']['transformer'])  ?? 'App\\Transformers',
            '{{RequestsNamespace}}' => Str::replace('/', '\\', $config['namespaces']['request'])  ?? 'App\\Http\\Requests',
            '{{RepositoriesNamespace}}' => Str::replace('/', '\\', $config['namespaces']['repository'])  ?? 'App\\Repositories',

            // Suffixes
            '{{ModelSufix}}' => Str::ucfirst(Str::camel($config['suffixes']['model'] ?? '')),
            '{{ServiceSufix}}' => Str::ucfirst(Str::camel($config['suffixes']['service'] ?? 'Service')),
            '{{ControllerSufix}}' => Str::ucfirst(Str::camel($config['suffixes']['controller'] ?? 'Controller')),
            '{{TransformerSufix}}' => Str::ucfirst(Str::camel($config['suffixes']['transformer'] ?? 'Transformer')),
            '{{RepositorySufix}}' => Str::ucfirst(Str::camel($config['suffixes']['repository'] ?? 'Repository')),
            '{{RequestSufix}}' => Str::ucfirst(Str::camel($config['suffixes']['request'] ?? 'Request')),

            // Prefixes
            '{{ModelPrefix}}' => Str::ucfirst(Str::camel($config['preffixes']['model'] ?? '')),
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

            // Traits
            '{{traitImports}}' => implode(";\n", Arr::map($config['model_implementation']['traites'], function ($trait) {
                if (class_basename($trait) == 'HasFactory') return '';
                return 'use '.$trait.';';
            }) ?? []),
            '{{traitUses}}' => implode("\n        ", Arr::map($config['model_implementation']['traites'], function ($trait) {
                if (class_basename($trait) == 'HasFactory') return '';
                return 'use '.class_basename($trait).';';
            })  ?? []),

            '{{table}}' => $config['model_implementation']['table']['show'] ? 'protected $table = "'.Str::snake(($config['model_implementation']['table']['preffixe'] ?? '').Str::plural(Str::ucfirst($name))).'";'  : '',
            '{{primaryKey}}' => $config['model_implementation']['primaryKey'] ? 'protected $primaryKey = "'.Str::snake('id'.($config['model_implementation']['usePrimaryKeySuffixe'] ? Str::singular(Str::ucfirst($name)) : '')).'";' : '',
            '{{keyType}}' => $config['model_implementation']['keyType'] ? 'protected $keyType = "'.$config['model_implementation']['keyType'].'";' : '',
            '{{incrementing}}' => 'public $incrementing = '.$config['model_implementation']['incrementing'] ? 'true' : 'false'.';',
            '{{timestamps}}' => 'public $timestamps = '.$config['model_implementation']['timestamps'] ? 'true' : 'false'.';',
            '{{connection}}' => $config['model_implementation']['connection'] ? 'protected $connection = "'.$config['model_implementation']['connection'].'";' : '',
            '{{guard_name}}' => $config['model_implementation']['guard_name'] ? 'protected $guard_name = "'.$config['model_implementation']['guard_name'].'";' : '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $stub);
    }

    protected function ensureDirectoryExists($directory)
    {
        if (!$this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }
    }

    protected function displaySummary($name, $paths)
    {
        $this->newLine();
        $this->info("📋 Résumé de la génération :");
        $this->table(
            ['Composant', 'Fichier', 'Emplacement'],
            [
                ['Model', "{$name}.php", $paths['model']],
                ['Repository', "{$name}Repository.php", $paths['repository']],
                ['Service', "{$name}Service.php", $paths['service']],
                ['Controller', "{$name}Controller.php", $paths['controller']],
                ['Transformer', "{$name}Transformer.php", $paths['transformer']],
                ['Request', "Store{$name}Request.php", $paths['request']],
                ['Request', "Update{$name}Request.php", $paths['request']],
            ]
        );

        $this->newLine();
        $this->info("🎉 Pattern Repository généré avec succès pour {$name} !");
        $this->comment("💡 N'oubliez pas d'ajouter vos routes et validations.");
    }
}
