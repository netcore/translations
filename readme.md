## Keep and edit all Laravel translations in a database

By default Laravel translations are kept in language files under resources folder.
This makes standart CRUD operations very cumbersome, so we decided it would be better to
store all translations in a database. Benefits of this are:

1. Easy CRUD operations and nice UI for admin
2. Avoid GIT conflicts if lang files are edited in filesystem
3. Performance is still great. Database is accessed only once and then translations are cached
4. Import/export translations as Excel files for storing on Google Spreadsheets or locally

![screenshot](http://image.prntscr.com/image/6a1d7f96919e42118c250dfaac5e8b48.png)

## Installation

Require this package with composer:

    composer require netcore/translations --dev

Add our service provider to "providers" array in config/app.php:

    \Netcore\Translator\ServiceProvider::class
        
Run migrations to create "translations" and "languages" tables:

    php artisan migrate
        
Add routes to RouteServiceProvider.php. Choose middleware that will allow admins only to edit translations:

    Route::group([
        'middleware' => ['web', 'isAdmin'],
        'namespace'  => null,
        'prefix'     => 'admin',
        'as'         => 'admin.'
    ], function (Router $router) {
        \Netcore\Translator\Router::adminRoutes($router);
    });
    
Package uses cache tags. You can check out laravel documentation to find out more about cache tags .
https://laravel.com/docs/5.4/cache#cache-tags

One of the options is to use redis.

First of all, install redis package by running this composer command.

    composer require predis/predis

Then you need to change your cache driver in .env file to redis like this

    CACHE_DRIVER=redis
        
Publish config files for defining your Admin layout to extend, translating ACP UI and more:

    php artisan vendor:publish --tag=config
    
## How to download translations from live server to development

We often want to get exact copy of translations from live server to either development or our local server.
In order to do that, we must expose API routes in RouteServiceProvider.php:

    Route::group([
        'middleware' => ['api'],
        'namespace'  => null,
        'prefix'     => 'api',
        'as'         => 'api.'
    ], function ($router) {
        Router::apiRoutes($router);
    });
    
After that, point this .env variable to your live server:

    NETCORE_TRANSLATIONS_DOWNLOAD_FROM=https://project.eu/api/translations/index

And then run ```php artisan translations:download``` on your development or local machine.

## Is it battle tested?

This package has already been battle tested in numerous Netcore projects. 
We finally got tired of copying the code over to new projects, so code has been extracted to installable package.

## Future plans

1. Unit tests
2. Different branches for different versions of Laravel
3. Rewrite ACP UI with Vue.js

