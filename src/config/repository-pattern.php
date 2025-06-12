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

    /**
     * Templates personnalisés
     *
     * L'option suivante vous permet de définir des paths vers vos stubs personnalisés
     */
    'custom_stubs' => [
        'repository' => 'resources/stubs/repository-pattern/repository.stub',
        'service' => 'resources/stubs/repository-pattern/service.stub',
        'controller' => 'resources/stubs/repository-pattern/controller.stub',
        'transformer' => 'resources/stubs/repository-pattern/transformer.stub',
        'request' => 'resources/stubs/repository-pattern/request.stub',
    ],

    /**
     * Preffixes des classes
     */
    'preffixes' => [
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
        'repository' => 'Repository',
        'service' => 'Service',
        'controller' => 'Controller',
        'transformer' => 'Transformer',
        'request' => 'Request',
    ],
];
