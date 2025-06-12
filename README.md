# Laravel Repository Pattern Generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jdikasa/laravel-repository-pattern.svg?style=flat-square)](https://packagist.org/packages/jdikasa/laravel-repository-pattern)
[![Total Downloads](https://img.shields.io/packagist/dt/jdikasa/laravel-repository-pattern.svg?style=flat-square)](https://packagist.org/packages/jdikasa/laravel-repository-pattern)
[![Laravel 9.x|10.x|11.x](https://img.shields.io/badge/Laravel-9.x%7C10.x%7C11.x-red.svg?style=flat-square)](https://laravel.com)
[![PHP 8.0+](https://img.shields.io/badge/PHP-8.0+-blue.svg?style=flat-square)](https://php.net)

Un package Laravel puissant pour générer automatiquement tous les composants du pattern Repository en une seule commande : Model, Repository, Service, Controller, Transformer et Requests.

## ✨ Fonctionnalités

- 🚀 **Génération complète** : Model, Repository, Service, Controller, Transformer, Requests (Store/Update)
- 🎨 **Stubs personnalisables** : Adaptez les templates à vos besoins
- 📁 **Structure organisée** : Chaque composant dans son dossier approprié
- ⚡ **Une seule commande** : Générez tout le pattern en une fois
- 🔧 **Configuration flexible** : Personnalisez les namespaces et dossiers
- 📝 **Code propre** : Templates respectant les bonnes pratiques Laravel

## 🎯 Compatibilité

| Version Package | Laravel | PHP |
|----------------|---------|-----|
| 1.x | 9.x, 10.x, 11.x | 8.0+ |

## 📦 Installation

```bash
composer require jdikasa/laravel-repository-pattern
```

Le package se configure automatiquement grâce à la découverte automatique de Laravel.

## 🚀 Utilisation

### Commande principale

```bash
php artisan make:model-pattern Post
```

Cette commande génère automatiquement :

- ✅ `app/Models/Post.php` - Le modèle Eloquent
- ✅ `app/Repositories/PostRepository.php` - Le repository
- ✅ `app/Services/PostService.php` - La couche service
- ✅ `app/Http/Controllers/PostController.php` - Le contrôleur
- ✅ `app/Transformers/PostTransformer.php` - Le transformer
- ✅ `app/Http/Requests/Post/StorePostRequest.php` - Request pour création
- ✅ `app/Http/Requests/Post/UpdatePostRequest.php` - Request pour mise à jour

### Options disponibles

```bash
# Forcer l'écrasement des fichiers existants
php artisan make:model-pattern Post --force
```

## 🏗️ Structure générée

```
app/
├── Models/
│   └── Post.php
├── Repositories/
│   └── PostRepository.php
├── Services/
│   └── PostService.php
├── Http/
│   ├── Controllers/
│   │   └── PostController.php
│   └── Requests/
│       └── Post/
│           ├── StorePostRequest.php
│           └── UpdatePostRequest.php
└── Transformers/
    └── PostTransformer.php
```

## 🔧 Configuration

### Publier la configuration

```bash
php artisan vendor:publish --tag=repository-pattern-config
```

### Fichier de configuration

```php
// config/repository-pattern.php
return [

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
```

### Publier les stubs (optionnel)

```bash
php artisan vendor:publish --tag=repository-pattern-stubs
```

Les stubs seront publiés dans `resources/stubs/repository-pattern/` et vous pourrez les personnaliser selon vos besoins.

### Publier les helpers (optionnel)

```bash
php artisan vendor:publish --tag=repository-pattern-helpers
```

Les helpers seront publiés dans `app/Helpers/` et vous pourrez les personnaliser selon vos besoins.

## 📝 Exemple d'utilisation

Après avoir généré les composants pour `Post` :

### 1. Repository

```php
// app/Repositories/PostRepository.php
class PostRepository extends BaseRepository
{
    public function model(): string
    {
        return Post::class;
    }
    
    public function getPublishedPosts()
    {
        return $this->model->where('status', 'published')->get();
    }
}
```

### 2. Service

```php
// app/Services/PostService.php
class PostService
{
    public function __construct(
        private PostRepository $postRepository
    ) {}
    
    public function createPost(array $data): Post
    {
        return $this->postRepository->create($data);
    }
}
```

### 3. Controller

```php
// app/Http/Controllers/PostController.php
class PostController extends Controller
{
    public function __construct(
        private PostService $postService,
        private PostTransformer $postTransformer
    ) {}
    
    public function store(StorePostRequest $request)
    {
        $post = $this->postService->createPost($request->validated());
        
        return response()->json([
            'data' => $this->postTransformer->transform($post)
        ], 201);
    }
}
```

## 🎨 Personnalisation des stubs

Une fois les stubs publiés, vous pouvez personnaliser les templates dans `resources/stubs/repository-pattern/` :

- `repository.stub` - Template du repository
- `service.stub` - Template du service
- `controller.stub` - Template du contrôleur
- `transformer.stub` - Template du transformer
- `request.stub` - Template des requests

### Variables disponibles dans les stubs

- `{{ModelName}}` - Nom du modèle (ex: Post)
- `{{ModelNameLowercase}}` - Nom du modèle en camelCase (ex: post)
- `{{ModelNamePlural}}` - Nom du modèle au pluriel (ex: posts)
- `{{ModelNameKebab}}` - Nom du modèle en kebab-case (ex: post-category)
- `{{ModelNameSnake}}` - Nom du modèle en snake_case (ex: post_category)
- `{{RepositoryNamespace}}` - Namespace des repositories
- `{{ServiceNamespace}}` - Namespace des services
- `{{TransformerNamespace}}` - Namespace des transformers

## 🤝 Contribution

Les contributions sont les bienvenues ! Pour contribuer :

1. Fork le projet
2. Créez une branche pour votre fonctionnalité (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Committez vos changements (`git commit -am 'Ajout d'une nouvelle fonctionnalité'`)
4. Poussez vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Ouvrez une Pull Request

## 📋 Roadmap

- [ ] Support des migrations automatiques
- [ ] Génération des tests automatiques
- [ ] Support des relations Eloquent
- [ ] Templates pour API Resources
- [ ] Support des Factory et Seeders

## 🐛 Signaler un bug

Si vous trouvez un bug, veuillez ouvrir une issue sur [GitHub](https://github.com/jdikasa/laravel-repository-pattern/issues) avec :
- La version de Laravel utilisée
- La version du package
- Les étapes pour reproduire le bug
- Le message d'erreur complet

## 📄 Licence

Ce package est open source sous licence [MIT](LICENSE.md).

## 🏷️ Changelog

### v1.0.0 - Release initiale
- ✅ Génération complète du pattern Repository
- ✅ Support Laravel 9.x, 10.x, 11.x
- ✅ Stubs personnalisables
- ✅ Configuration flexible
- ✅ Documentation complète

---

<p align="center">
Développé avec ❤️ par <a href="https://github.com/jdikasa">Jean-louis Dikasa</a>
</p>