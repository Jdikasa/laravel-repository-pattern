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

    // Namespaces personnalisés
    'repository_namespace' => 'App\\Repositories',
    'service_namespace' => 'App\\Services',
    'transformer_namespace' => 'App\\Transformers',

    // Répertoires de génération
    'repository_path' => 'app/Repositories',
    'service_path' => 'app/Services', 
    'transformer_path' => 'app/Transformers',

    // Options de génération
    'generate_interfaces' => false,
    'generate_tests' => false,
    'generate_form_requests' => false,

    // Templates personnalisés
    'custom_stubs' => [
        'repository' => null,
        'service' => null,
        'controller' => null,
        'transformer' => null,
        'request' => null,
    ],

    // Suffixes des classes
    'suffixes' => [
        'repository' => 'Repository',
        'service' => 'Service',
        'controller' => 'Controller',
        'transformer' => 'Transformer',
        'request' => 'Request',
    ],

    // Options du transformer
    'transformer' => [
        'include_timestamps' => true,
        'date_format' => 'iso', // iso, human, timestamp
        'include_relations' => false,
    ],

    // Support des nouveaux starter kits Laravel 12
    'starter_kits' => [
        'react' => [
            'enabled' => false,
            'with_workos' => false,
        ],
        'vue' => [
            'enabled' => false,
            'with_workos' => false,
        ],
        'livewire' => [
            'enabled' => false,
            'with_workos' => false,
        ],
    ],

    // Options Carbon 3.x
    'carbon' => [
        'version' => '3.x',
        'immutable' => true,
        'locale' => 'fr',
    ],
];