<?php

namespace Netcore\Translator\PassThroughs\Translation;

use Netcore\Translator\PassThroughs\PassThrough;
use Netcore\Translator\Models\Translation;
use Netcore\Translator\Models\Language;

class Import extends PassThrough
{

    /**
     *
     * Counter to give feedback to user
     *
     * @var int
     */
    private $existingKeysCount = 0;

    /**
     *
     * 1. Parse excel file and create a collection of all entries in that file
     * 2. Determine which keys already exist in DB via some collection magic (dont fire endless queries)
     * 3. Delete from DB those keys that already exist there
     * 4. Mass-insert all entries that are in excel
     *
     * 1. Parse excel file and create a collection of all entries in that file
     * 2. Determine which keys do not exist in DB via some collection magic (dont fire endless queries)
     * 3. Mass-insert all new keys
     * 4. Show confirmation message with the following stats:
     *    1) x new keys were found. These were added.
     *    2) x keys already exist. These were not changed.
     *
     * @param array $allData
     * @return bool
     */
    public function process($allData)
    {
        \DB::transaction(function () use ($allData) {

            // 1.
            $parsedTranslations = $this->parsedTranslations($allData);

            // 2.
            $newTranslations = $this->newTranslations($parsedTranslations);

            // 3.
            foreach (array_chunk($newTranslations, 300) as $chunk) {
                Translation::insert($chunk);
            }

            // 4.
            $localesCount = Language::pluck('iso_code')->count();
            $newKeysCount = round(count($newTranslations) / $localesCount);
            $existingKeysCount = round($this->existingKeysCount / $localesCount);
            $this->flashMessages($newKeysCount, $existingKeysCount);
        });

        $keyToForget = 'translations';
        $function = config('translations.translations_key_in_cache');
        if ($function AND function_exists($function)) {
            $keyToForget = $function();
        }

        cache()->forget($keyToForget);

        return true;
    }

    /**
     * @param $allData
     * @return \Illuminate\Support\Collection
     */
    private function parsedTranslations($allData)
    {
        $locales = Language::pluck('iso_code')->toArray();

        $parsedTranslations = [];

        foreach ($allData as $pageNr => $pageData) {

            // If this is not empty, it means we only have one sheet.
            // Otherwise, we have multiple sheets.
            $groupKey = array_get($pageData, 'key', '');

            if ($groupKey) {
                foreach ($allData as $row) {
                    $newItems = $this->translationsFromOneRow($row, $locales);
                    foreach ($newItems as $item) {
                        $parsedTranslations[] = $item;
                    }
                }
                break;
            } else {
                foreach ($pageData as $row) {
                    $newItems = $this->translationsFromOneRow($row, $locales);
                    foreach ($newItems as $item) {
                        $parsedTranslations[] = $item;
                    }
                }
            }
        }

        return $parsedTranslations;
    }

    /**
     * @param $row
     * @param $locales
     * @return \Illuminate\Support\Collection
     */
    private function translationsFromOneRow($row, $locales)
    {
        $translations = collect();

        $groupKey = array_get($row, 'key', '');

        if (!$groupKey) {
            return $translations;
        }

        $firstDot = strpos($groupKey, '.');
        if ($firstDot === false) {
            return $translations;
        }

        $group = substr($groupKey, 0, $firstDot);
        $key = substr($groupKey, $firstDot + 1);

        foreach ($row as $fieldname => $value) {
            if ($fieldname AND in_array($fieldname, $locales)) {

                $data = ([
                    'locale' => $fieldname,
                    'group'  => $group,
                    'key'    => $key,
                    'value'  => array_get($row, $fieldname, ''),
                ]);

                if (function_exists('domain_id')) {
                    $data['domain_id'] = domain_id();
                }

                $translations->push($data);
            }
        }

        return $translations;
    }

    /**
     * @param $parsedTranslations
     * @return array
     */
    private function newTranslations($parsedTranslations)
    {
        $fieldsToSelect = [
            'locale',
            'group',
            'key'
        ];

        $callableDomainId = is_callable('domain_id');

        if ($callableDomainId) {
            $fieldsToSelect[] = 'domain_id';
        }

        $existing = Translation::select($fieldsToSelect)->get();
        $newTranslations = [];

        foreach ($parsedTranslations as $parsedTranslation) {

            $existsQuery = $existing
                ->where('locale', $parsedTranslation['locale'])
                ->where('group', $parsedTranslation['group'])
                ->where('key', $parsedTranslation['key']);

            if ($callableDomainId) {
                $existsQuery = $existsQuery
                    ->where('domain_id', domain_id());
            }

            $exists = $existsQuery->first();

            if ($exists == false) {
                $newTranslations[] = $parsedTranslation;
            } else {
                $this->existingKeysCount++;
            }
        }

        return $newTranslations;
    }

    /**
     * @param $newKeysCount
     * @param $existingKeysCount
     */
    private function flashMessages($newKeysCount, $existingKeysCount)
    {
        $response = [];
        $uiTranslations = config('translations.ui_translations', []);
        
        if($newKeysCount) {
            $xNewKeysWereFound = array_get(
                $uiTranslations,
                'x-new-keys-were-found',
                ':count new keys added to system!'
            );
            $response[] = str_replace(':count', $newKeysCount, $xNewKeysWereFound);
        } else {
            $noNewKeysWereFound = array_get(
                $uiTranslations,
                'new-keys-were-not-found',
                'No new keys to add! Doing nothing.'
            );
            $response[] = $noNewKeysWereFound;
        }

        if($existingKeysCount) {
            $xKeysAlreadyExist = array_get(
                $uiTranslations,
                'x-keys-already-exist',
                ':count keys already exist. These were not changed.'
            );
            $response[] = str_replace(':count', $existingKeysCount, $xKeysAlreadyExist);
        }

        session()->flash('translations-success', $response);
    }
}