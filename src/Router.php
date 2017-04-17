<?php namespace Netcore\Translator;

use Netcore\Translator\Controllers\LanguagesController;
use Netcore\Translator\Controllers\TranslationsController;

class Router
{

    /**
     * Register routes
     *
     * @return void
     */
    public static function routes(\Illuminate\Routing\Router $router)
    {

        $router->group(['prefix' => 'translations'], function(\Illuminate\Routing\Router $router) {

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

            $router->get('/{group?}', [
                'as'   => 'translations.index',
                'uses' => TranslationsController::class . '@index'
            ]);
        });

        $router->resources([
            'languages'=> LanguagesController::class
        ]);
    }
}