<?php

namespace Netcore\Translator\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Netcore\Translator\Models\Translation;
use Netcore\Translator\Requests\ImportTranslationsRequest;
use Netcore\Translator\Models\Language;
use Netcore\Translator\Requests\StoreTranslationRequest;

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


        if (input('from_locale', null) && input('to_locale', null)) {
            session()->put('from_locale', input('from_locale', null));
            session()->put('to_locale', input('to_locale', null));
        }

        $fromLocale = session()->get('from_locale', null);
        $toLocale = session()->get('to_locale', null);

        if (!$fromLocale || !$toLocale) {
            $fromLocale = input('from_locale', array_get($locales, 0, ''));
            $toLocale = input('to_locale', array_get($locales, 1, ''));
        }

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

        Translation::where([
            'locale' => $locale,
            'group'  => $group,
            'key'    => $key
        ])->delete();

        $translation = Translation::forceCreate([
            'locale' => $locale,
            'group'  => $group,
            'key'    => $key,
            'value'  => $value
        ]);

        $keyToForget = 'translations';
        $function = config('translations.translations_key_in_cache');
        if ($function AND function_exists($function)) {
            $keyToForget = $function();
        }

        cache()->forget($keyToForget);

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

            $uiTranslations = config('translations.ui_translations.translations', []);
            $error = array_get($uiTranslations, 'couldnt_import_excel');

            return redirect()->back()->withError($error);
        }

        $translation = new Translation();
        $translation->import()->process($all_data);

        return redirect()->back();
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

    /**
     * @return mixed
     */
    public function manual()
    {
        $extends = config('translations.extends', 'layouts.admin');
        $section = config('translations.section', 'layouts.content');
        return view($this->viewNamespace . '.manual', compact('extends', 'section'));
    }

    /**
     * @param StoreTranslationRequest $request
     * @return mixed
     */
    public function storeTranslation(StoreTranslationRequest $request)
    {
        $group = $request->get('group');
        $key = trim($request->get('key'));

        $locales = Language::pluck('iso_code')->toArray();
        
        foreach($locales as $locale) {
            $value = '';
            Translation::forceCreate(
                compact('group', 'key', 'locale', 'value')
            );
        }
        
        $uiTranslations = config('translations.ui_translations.translations', []);
        $msg = array_get(
            $uiTranslations,
            'translation_has_been_added',
            'Translation has been added!'
        );

        return redirect()->route('admin.translations.index')->withSuccess($msg);
    }
}