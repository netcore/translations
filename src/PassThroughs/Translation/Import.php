<?php

namespace Netcore\Translator\PassThroughs\Translation;

use Netcore\Translator\PassThroughs\PassThrough;
use Netcore\Translator\Models\Translation;
use Netcore\Translator\Models\Language;

class Import extends PassThrough
{

    private $translation;

    /**
     * Import constructor.
     *
     * @param Translation $translation
     */
    public function __construct(Translation $translation)
    {
        $this->translation = $translation;
    }

    /**
     *
     * 1. Parse excel file and create a collection of all entries in that file
     * 2. Determine which keys already exist in DB via some collection magic (dont fire endless queries)
     * 3. Delete from DB those keys that already exist there
     * 4. Mass-insert all entries that are in excel
     *
     * @param array $all_data
     * @return bool
     */
    public function process($all_data)
    {
        \DB::transaction(function () use ($all_data) {
            $locales = Language::pluck('iso_code')->toArray();

            // 1.

            $translations = collect();

            foreach ($all_data as $page_nr => $page_data) {

                // If this is not empty, it means we only have one sheet.
                // Otherwise, we have multiple sheets.
                $group_key = array_get($page_data, 'key', '');

                if ($group_key) {
                    foreach ($all_data as $row) {
                        $new_items = $this->translationsFromOneRow($row, $locales);
                        foreach ($new_items as $item) {
                            $translations->push($item);
                        }
                    }
                    break;
                } else {
                    foreach ($page_data as $row) {
                        $new_items = $this->translationsFromOneRow($row, $locales);
                        foreach ($new_items as $item) {
                            $translations->push($item);
                        }
                    }
                }
            }

            // 2.

            $existing = collect(Translation::get());
            $delete_ids = [];

            foreach ($translations as $translation) {

                $existsQuery = $existing
                    ->where('locale', $translation['locale'])
                    ->where('group', $translation['group'])
                    ->where('key', $translation['key']);

                if( is_callable('domain_id') ) {
                    $existsQuery = $existsQuery
                        ->where('domain_id', domain_id());
                }

                $exists = $existsQuery->first();

                if ($exists) {
                    $delete_ids[] = $exists->id;
                }
            }

            // 3.

            foreach (array_chunk($delete_ids, 300) as $chunk) {
                info('Deleting chunk. Elements count - ' . count($chunk));
                Translation::whereIn('id', $chunk)->delete();
            }

            // 4.

            foreach (array_chunk($translations->toArray(), 300) as $chunk) {
                info('Inserting chunk. Elements count - ' . count($chunk));
                Translation::insert($chunk);
            }
        });

        cache()->forget('translations');

        return true;
    }

    /**
     * @param $row
     * @param $locales
     * @return \Illuminate\Support\Collection
     */
    private function translationsFromOneRow($row, $locales)
    {
        $translations = collect();

        $group_key = array_get($row, 'key', '');

        if (!$group_key) {
            return $translations;
        }

        $first_dot = strpos($group_key, '.');
        if ($first_dot === false) {
            return $translations;
        }

        $group = substr($group_key, 0, $first_dot);
        $key = substr($group_key, $first_dot + 1);

        foreach ($row as $fieldname => $value) {
            if ($fieldname AND in_array($fieldname, $locales)) {
                
                $data = ([
                    'locale' => $fieldname,
                    'group'  => $group,
                    'key'    => $key,
                    'value'  => array_get($row, $fieldname, ''),
                ]);

                if( is_callable('domain_id') ) {
                    $data['domain_id'] = domain_id();
                }
                
                $translations->push($data);
            }
        }

        return $translations;
    }
}