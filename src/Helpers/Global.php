<?php

use Netcore\Translator\Models\Language;
use Netcore\Translator\Models\Translation;

// In order to stay backwards compatible, we should define this
// helper function only if it is not defined in project itself
if (!function_exists('trans_model')) {
    /**
     * @param          $row
     * @param Language $language
     * @param String $attribute
     * @return String
     */
    function trans_model($row, Language $language, $attribute = null): String
    {
        if (object_get($row, 'id')) {
            if (is_null($attribute)) {
                $model = $row->translateOrNew($language->iso_code);

                if (is_object($model)) {
                    return $model;
                }
            }

            if ($row) {
                return (string)$row->translateOrNew($language->iso_code)->$attribute;
            }
        }

        return '';
    }
}

if (!function_exists('lg')) {

    /**
     * @param $key
     * @param array $replace
     * @param null $locale
     * @param $value
     * @return String
     */
    function lg($key, array $replace = [], $locale = null, $value = null): String
    {
        $createTranslations = config('translations.create_translations_on_the_fly', false);

        if ($createTranslations) {
            $translations = cache('translations');
            $languages = languages();
            if (isset($locale) && !$languages->where('iso_code', $locale)) {
                $value = $locale;
            }
            $fallbackIsoCode = $languages->where('is_fallback', 1)->first()->iso_code;
            $transKey = $fallbackIsoCode . '.' . $key;

            if (!isset($translations[$transKey]) && isset($value)) {
                $translations = [
                    'key' => $key,
                ];

                foreach ($languages->pluck('iso_code')->toArray() as $code) {
                    $translations[$code] = $value;
                }

                $translation = new Translation();
                $translation->import()->process([$translations]);

                cache()->forget('translations');
            }
        }

        return trans($key, $replace, $locale);
    }
}