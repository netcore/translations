<?php

namespace Netcore\Translator\Helpers;

use Netcore\Translator\Models\Language;

class TransHelper
{

    /**
     * @return Language
     */
    public static function getLanguage()
    {

        $defaultKeyInSession = config('app.locale');

        $keyInSession = config('laravel_translations_in_database.locale_iso_key_in_session', 'locale');
        if($keyInSession AND function_exists($keyInSession)) {
            $keyInSession = $keyInSession();
        } else {
            $keyInSession = 'locale';
        }

        $iso = session()->get($keyInSession, $defaultKeyInSession);

        $cached = cache()->rememberForever('language-' . $iso, function () use ($iso) {

            try {
                $language = Language::whereIsoCode($iso)->first();

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
                $language->iso_code = $iso;
            } catch (\Exception $e) {
                $language = new Language();
                $language->iso_code = $iso;
            }

            return $language;
        });

        return $cached;
    }
}