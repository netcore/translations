<?php

namespace Netcore\Translator\PassThroughs\Translation;

use Netcore\Translator\Models\Translation;
use Netcore\Translator\Models\Language;
use Netcore\Translator\PassThroughs\PassThrough;

class Export extends PassThrough
{

    /**
     * @return mixed
     */
    public function get()
    {
        $excel = app('excel');

        $filename = 'Translations';
        $title = 'Translations';

        $allTranslations = Translation::orderBy('group', 'asc')
            ->orderBy('key', 'asc')
            ->get();

        $translations = [];
        foreach ($allTranslations as $translation) {
            $translations[$translation->group . '.' . $translation->key][$translation->locale] = $translation;
            $translations[$translation->group . '.' . $translation->key]['group'] = $translation->group;
            $translations[$translation->group . '.' . $translation->key]['key'] = $translation->key;
            $translations[$translation->group . '.' . $translation->key]['id'] = $translation->id;
        }

        $translations = collect($translations)->map(function ($item) {
            return (object)$item;
        })->sortBy('group');

        return $excel->create($filename, function ($excel) use ($translations, $title) {

            $excel->setTitle($title);

            $excel->sheet('Translations', function ($sheet) use ($translations, $title) {

                $languages = Language::all();

                $rows =
                    [
                        [
                            'key',
                        ],
                    ];

                foreach ($languages as $language) {
                    $rows[0][] = $language->iso_code;
                }

                // Now $rows would be something like ['key', 'lv', 'ru']

                $translations = $translations->map(function ($t, $index) use ($languages) {

                    $item = [
                        $t->group . '.' . $t->key,
                    ];

                    foreach ($languages as $language) {
                        $iso_code = $language->iso_code;
                        $translation = object_get($t, $iso_code, new \stdClass());
                        $value = object_get($translation, 'value', '');

                        $item[] = $value;
                    }

                    return $item;
                })->all();

                $rows = array_merge($rows, ($translations));

                $sheet->fromArray($rows, null, 'A1', false, false);

                $sheet->row(1, function ($row) {
                    $row->setFontWeight('bold');
                });

            });
        });
    }
}