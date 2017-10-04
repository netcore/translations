<?php namespace Netcore\Translator;

use Illuminate\Contracts\View\View;
use Illuminate\Translation\TranslationServiceProvider;
use Netcore\Translator\Commands\DownloadTranslations;
use Netcore\Translator\Helpers\TransHelper;

class ServiceProvider extends TranslationServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerLoader();

        $this->app->singleton('translator', function ($app) {

            $loader = $app['translation.loader'];

            // When registering the translator component, we'll need to set the default
            // locale as well as the fallback locale.
            $locale = TransHelper::getLanguage();

            $trans = new \Netcore\Translator\Translator($loader, $locale);

            $trans->setFallback($app['config']['app.fallback_locale']);

            return $trans;
        });

        // Views
        $viewNamespace = 'translations';
        $this->loadViewsFrom(__DIR__.'/views', $viewNamespace);

        // Default config
        $this->mergeConfigFrom(
            __DIR__.'/config/translations.php',
            'translations'
        );

        // Publish config to override defaults
        $this->publishes([
            __DIR__.'/config/translations.php' => config_path('translations.php'),
        ], 'config');

        // Migrations
        $this->loadMigrationsFrom(__DIR__.'/migrations');
        
        // View composers
        view()->composer($viewNamespace.'::*', function(View $view) use ($viewNamespace) {
            $view->with(compact('viewNamespace'));
        });
        
        view()->composer($viewNamespace.'::languages.*', function(View $view) {
            $uiTranslations = config('translations.ui_translations.languages', []);
            $view->with(compact('uiTranslations'));
        });
        
        view()->composer($viewNamespace.'::translations.*', function(View $view) {
            $uiTranslations = config('translations.ui_translations.translations', []);
            $view->with(compact('uiTranslations'));
        });
        
        view()->composer($viewNamespace.'::partials.*', function(View $view) {
            $uiTranslations = config('translations.ui_translations.partials', []);
            $view->with(compact('uiTranslations'));
        });

        // Commands
        $this->commands([
            DownloadTranslations::class
        ]);
    }
}