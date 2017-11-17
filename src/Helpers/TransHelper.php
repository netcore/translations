<?php

namespace Netcore\Translator\Helpers;

use Illuminate\Support\Collection;
use Netcore\Translator\Models\Language;
use Illuminate\Support\Facades\Schema;

class TransHelper
{

    /**
     * @var
     */
    private static $cachedAllLanguages;

    /**
     * @return Language
     */
    public static function getLanguage()
    {

        $defaultKeyInSession = config('app.locale');

        $keyInSession = config('translations.locale_iso_key_in_session', 'locale');
        if ($keyInSession AND function_exists($keyInSession)) {
            $keyInSession = $keyInSession();
        } else {
            $keyInSession = 'locale';
        }

        $pkValue = session()->get($keyInSession, $defaultKeyInSession);

        $cached = cache()->rememberForever('language-' . $pkValue, function () use ($pkValue) {

            $pkField = config('translations.languages_primary_key', 'iso_code');

            try {
                $language = Language::where($pkField, $pkValue)->first();

                if ($language) {
                    return $language;
                }

                // Fallback to first..
                $language = Language::first();

                if ($language) {
                    return $language;
                }

                // Fallback to iso itself..
                $language = new Language();
                $language->$pkField = $pkValue;
            } catch (\Exception $e) {
                $language = new Language();
                $language->$pkField = $pkValue;
            }

            return $language;
        });

        return $cached;
    }

    /**
     * @return mixed
     */
    public static function getFallbackLanguage()
    {
        $cached = cache()->rememberForever('fallback-language', function () {

            $language = Language::where('is_fallback', 1)->first();

            if ($language) {
                return $language;
            }

            // Fallback to first..
            $language = Language::first();

            return $language;
        });

        return $cached;
    }

    /**
     * @return Collection
     */
    public static function getAllLanguages(): Collection
    {
        if(self::$cachedAllLanguages !== null) {
            return self::$cachedAllLanguages;
        }

        $languages = collect();
        if (Schema::hasTable('languages')) {
            $languages = \Netcore\Translator\Models\Language::all();
        }

        if(!$languages->count()) {
            $languages = collect([
                new \Netcore\Translator\Models\Language([
                    'iso_code'        => config('app.locale'),
                    'title'           => strtoupper(config('app.locale')),
                    'title_localized' => strtoupper(config('app.locale'))
                ])
            ]);
        }

        self::$cachedAllLanguages = $languages;

        return $languages;
    }
}