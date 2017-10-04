<?php namespace Netcore\Translator;

use Netcore\Translator\Controllers\ApiController;
use Netcore\Translator\Controllers\LanguagesController;
use Netcore\Translator\Controllers\TranslationsController;

class Router
{

    /**
     * Register API routes
     *
     * @return void
     */
    public static function apiRoutes(\Illuminate\Routing\Router $router)
    {
        $router->group(['prefix' => 'translations'], function (\Illuminate\Routing\Router $router) {

            $router->get('/index', [
                'as'   => 'translations.api.index',
                'uses' => ApiController::class . '@index'
            ]);
        });
    }

    /**
     * Register admin routes
     *
     * @return void
     */
    public static function adminRoutes(\Illuminate\Routing\Router $router)
    {

        $router->group(['prefix' => 'translations'], function (\Illuminate\Routing\Router $router) {

            $router->get('/export', [
                'as'   => 'translations.export',
                'uses' => TranslationsController::class . '@export'
            ]);

            $router->post('/import', [
                'as'   => 'translations.import',
                'uses' => TranslationsController::class . '@import'
            ]);

            $router->post('/edit/{group}', [
                'as'   => 'translations.edit',
                'uses' => TranslationsController::class . '@edit'
            ]);

            $router->get('/manual', [
                'as'   => 'translations.manual',
                'uses' => TranslationsController::class . '@manual'
            ]);

            $router->post('/store', [
                'as'   => 'translations.store',
                'uses' => TranslationsController::class . '@storeTranslation'
            ]);

            $router->get('/{group?}', [
                'as'   => 'translations.index',
                'uses' => TranslationsController::class . '@index'
            ]);
        });

        $router->resources([
            'languages' => LanguagesController::class
        ]);
    }

    /**
     * Register admin routes
     *
     * @deprecated
     * @return void
     */
    public static function routes(\Illuminate\Routing\Router $router)
    {
        self::adminRoutes($router);
    }
}