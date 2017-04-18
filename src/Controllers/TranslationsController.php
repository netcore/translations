<?php

namespace Netcore\Translator\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Netcore\Translator\Models\Translation;
use Netcore\Translator\Requests\ImportTranslationsRequest;
use Netcore\Translator\Models\Language;

class TranslationsController extends Controller
{
    /**
     *
     * @var String
     */
    private $viewNamespace = 'translations::translations';

    /**
     * @param null $group
     * @return mixed
     */
    public function index($group = null)
    {
        /*
         * Check if group exists.
         */
        if ($group AND Translation::whereGroup($group)->count() == 0) {
            return redirect()->route('admin.translations.index');
        }

        //Set the default locale as the first one.
        $locales = Language::orderBy('is_fallback', 'DESC')->pluck('iso_code')->toArray();
        $locales = array_unique($locales);

        $groups = Translation::groupBy('group');

        $fromLocale = input('from_locale', array_get($locales, 0, ''));
        $toLocale = input('to_locale', array_get($locales, 1, ''));

        if (!in_array($fromLocale, $locales)) {
            $fromLocale = array_get($locales, 0, '');
        }

        if (!in_array($toLocale, $locales)) {
            $toLocale = array_get(
                $locales,
                1,
                array_get($locales, 0)
            );
        }

        $query = Translation::orderBy('group', 'asc')
            ->orderBy('key', 'asc')
            ->whereIn('locale', [$fromLocale, $toLocale]);

        if ($group) {
            $query->whereGroup($group);
        }

        $allTranslations = $query->get();

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

        $uiTranslations = config('translations.ui_translations.translations', []);

        $groups = ['' => array_get($uiTranslations, 'all_groups')] + $groups->pluck('group', 'group')->toArray();

        $extends = config('translations.extends', 'layouts.admin');
        $section = config('translations.section', 'layouts.content');

        return view($this->viewNamespace . '.index', compact(
            'fromLocale',
            'toLocale',
            'locales',
            'groups',
            'group',
            'translations',
            'extends',
            'section'
        ));
    }

    /**
     * @param Request $request
     * @param $group
     * @return array
     */
    public function edit(Request $request, $group)
    {
        $name = $request->get('name');
        $value = $request->get('value');

        list($locale, $key) = explode('|', $name, 2);

        $translation = Translation::firstOrNew([
            'locale' => $locale,
            'group'  => $group,
            'key'    => $key,
        ]);
        $translation->value = (string)$value ?: null;
        $translation->save();

        cache()->forget('translations');

        return ['status' => 'ok'];
    }

    /**
     * @param ImportTranslationsRequest $request
     * @return mixed
     */
    public function import(ImportTranslationsRequest $request)
    {
        // 1. Parse excel file and create a collection of all entries in that file
        // 2. Determine which keys already exist in DB via some collection magic (dont fire endless queries)
        // 3. Delete from DB those keys that already exist there
        // 4. Mass-insert all entries that are in excel

        $file = $request->file('excel');

        try {
            $excel = app('excel');
            $all_data = $excel->load($file)
                ->get()
                ->toArray();
        } catch (\Exception $e) {

            $uiTranslations = config('translations.ui_translations', []);
            $error = array_get($uiTranslations, 'couldnt-import-excel');

            return redirect()->back()->withError($error);
        }

        $translation = new Translation();
        $translation->import()->process($all_data);

        $uiTranslations = config('translations.ui_translations', []);
        $msg = array_get($uiTranslations, 'translations-have-been-imported');

        return redirect()->back()->withSuccess($msg);
    }

    /**
     *
     */
    public function export()
    {
        $translation = new Translation();
        $excel = $translation->export()->get();
        $excel->download('xlsx');
    }

}