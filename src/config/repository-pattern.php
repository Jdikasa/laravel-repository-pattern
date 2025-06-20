<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Repository Pattern Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour le générateur de pattern Repository
    |
    */

    /**
     * Namespaces personnalisés
     */
    'namespaces' => [
        'model' => 'App\\Models',
        'repository' => 'App\\Repositories',
        'service' => 'App\\Services',
        'transformer' => 'App\\Transformers',
        'controller' => 'App\\Http\\Controllers',
        'request' => 'App\\Http\\Requests',
    ],

    /**
     * Répertoires de génération
     * 
     * Ces repertoires doivent correspondre à vos namespaces personnalisés
     */
    'paths' => [
        'model' => 'app/Models',
        'repository' => 'app/Repositories',
        'service' => 'app/Services',
        'transformer' => 'app/Transformers',
        'controller' => 'app/Http/Controllers',
        'request' => 'app/Http/Requests',
    ],

    /**
     * Options de génération
     * 
     * Choisissez quelles classes générer
     * 
     * Les options suivantes peuvent être définies à true ou false
     */
    'generations' => [
        'model' => true,
        'controller' => true,
        'repository' => true,
        'transformer' => true,
        'service' => true,
        'request' => true,
    ],

    'model_implementation' => [
        // Si show == true
        // Ceci ajoutera la declaration 'protected $table' avec comme valeur : preffixe + _ + nom du model au pluriel
        'table' => [
            'show' => true,
            'preffixe' => "t"
        ],
        'usePrimaryKeySuffixe' => true,
        // Si la valeur est true, Ceci ajoutera la declaration 'protected $primaryKey' avec comme valeur : id + _ + nom du model au singulier (si usePrimaryKeySuffixe == true)
        'primaryKey' => true,
        'keyType' => 'string',
        'incrementing' => false,
        'timestamps' => true,
        'connection' => '',
        'guard_name' => 'api',

        'traites' => [
            'Illuminate\\Database\\Eloquent\\Concerns\\HasUuids',
            'Illuminate\\Database\\Eloquent\\Factories\\HasFactory',
            'Illuminate\\Database\\Eloquent\\SoftDeletes',
        ],
    ],

    /**
     * Templates personnalisés
     *
     * L'option suivante vous permet de définir des paths vers vos stubs personnalisés
     */
    'custom_stubs' => [
        'model' => 'resources/Stubs/repository-pattern/model.stub',
        'repository' => 'resources/Stubs/repository-pattern/repository.stub',
        'service' => 'resources/Stubs/repository-pattern/service.stub',
        'controller' => 'resources/Stubs/repository-pattern/controller.stub',
        'transformer' => 'resources/Stubs/repository-pattern/transformer.stub',
        'request' => 'resources/Stubs/repository-pattern/request.stub',
    ],

    /**
     * Preffixes des classes
     */
    'preffixes' => [
        'model' => '',
        'repository' => '',
        'service' => '',
        'controller' => '',
        'transformer' => '',
        'request' => [
            'store' => 'Store',
            'update' => 'Update'
        ],
    ],

    /**
     * Suffixes des classes
     */
    'suffixes' => [
        'model' => '',
        'repository' => 'Repository',
        'service' => 'Service',
        'controller' => 'Controller',
        'transformer' => 'Transformer',
        'request' => 'Request',
    ],
];
