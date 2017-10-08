<?php
use Netcore\Translator\Models\Language;

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
