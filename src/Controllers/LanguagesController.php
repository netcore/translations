<?php

namespace Netcore\Translator\Controllers;

use Illuminate\Routing\Controller;
use Netcore\Translator\Models\Language;
use Netcore\Translator\Models\Translation;
use Netcore\Translator\Requests\LanguageRequest;
use Netcore\Translator\Requests\UpdateLanguageRequest;

class LanguagesController extends Controller
{

    /**
     *
     * @var String
     */
    private $viewNamespace = 'translations::languages';

    /**
     *
     * @var String
     */
    private $viewExtends, $viewSection;

    /**
     *
     * @var String
     */
    private $cacheTag = 'languages';

    /**
     * LanguagesController constructor.
     */
    public function __construct()
    {
        $cacheTag = config('translations.languages_cache_tag');
        if ($cacheTag AND function_exists($cacheTag)) {
            $this->cacheTag = $cacheTag();
        }
        $this->viewExtends = config('translations.extends', 'layouts.admin');
        $this->viewSection = config('translations.section', 'layouts.content');

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $languages = Language::all();
        $extends = $this->viewExtends;
        $section = $this->viewSection;

        return view($this->viewNamespace . '.index', compact('languages', 'extends', 'section'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $extends = $this->viewExtends;
        $section = $this->viewSection;

        return view($this->viewNamespace . '.create', compact('extends', 'section'));
    }

    /**
     * @param LanguageRequest $request
     * @return mixed
     */
    public function store(LanguageRequest $request)
    {
        $newLocale = $request->get('iso_code');
        $language = Language::whereIsoCode($newLocale)->onlyTrashed()->first();

        if ($language) {
            $language->update($request->all());
            $language->restore();
        } else {
            Language::create($request->all());
        }

        if (config('translations.copy_translations_from_fallback', false)) {
            $this->copyFallbackTranslations($newLocale);
        }

        $this->clearCache();

        $keyToForget = 'translations';
        $function = config('translations.translations_key_in_cache');
        if ($function AND function_exists($function)) {
            $keyToForget = $function();
        }

        cache()->forget($keyToForget);

        return redirect()->route('admin.languages.index')->withSuccess('Successfully created!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Language $language
     * @return \Illuminate\Http\Response
     */
    public function edit(Language $language)
    {
        $extends = $this->viewExtends;
        $section = $this->viewSection;

        return view($this->viewNamespace . '.edit', compact('language', 'extends', 'section'));
    }

    /**
     * @param UpdateLanguageRequest $request
     * @param Language $language
     * @return mixed
     */
    public function update(UpdateLanguageRequest $request, Language $language)
    {
        if ($request->get('is_fallback', 0)) {
            Language::whereIsFallback(1)->update([
                'is_fallback' => 0
            ]);
        }

        $language->update($request->all());

        $this->clearCache();

        return redirect()->back()->withSuccess('Successfully updated!');
    }

    /**
     * @param Language $language
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Language $language)
    {
        $language->delete(); // Soft delete

        $this->clearCache();

        return response()->json([
            'state' => 'success'
        ]);
    }

    /**
     *
     */
    private function clearCache()
    {
        $isRedis = config('cache.default') == 'redis';

        if ($isRedis) {
            // Flush only tagged cache
            cache()->tags([$this->cacheTag])->flush();
            info('Netcore/Translator cleared cache tag ' . $this->cacheTag);
        } else {
            // Flush all cache
            cache()->flush();
            info('Netcore/Translator cleared all cache globally');
        }
    }

    /**
     * Copies translations from fallback language to new language
     *
     * @param $newLocale
     */
    private function copyFallbackTranslations($newLocale)
    {
        \DB::transaction(function () use ($newLocale) {

            $existingTranslations = Translation::get();
            $fallbackLanguage = Language::whereIsFallback(1)->first();

            if ($fallbackLanguage) {
                $fallbackIsoCode = $fallbackLanguage->iso_code;

                $fallbackTranslations = Translation::whereLocale($fallbackIsoCode)->get();

                if ($fallbackTranslations) {
                    $copiedTranslationsWithNewLocale = $fallbackTranslations->map(function ($translation) use (
                        $newLocale
                    ) {
                        unset($translation->id);
                        $translation->locale = mb_strtolower($newLocale);

                        return $translation;
                    })->toArray();

                    $translationsToCreate = [];

                    foreach ($copiedTranslationsWithNewLocale as $translation) {

                        // Safety feature - dont create entry if it already exists
                        // This might happen if administrator creates new language, deletes it, and then creates again
                        $exists = $existingTranslations
                            ->where('group', $translation['group'])
                            ->where('key', $translation['key'])
                            ->where('locale', $translation['locale'])
                            ->first();

                        if (!$exists) {
                            $translationsToCreate[] = $translation;
                        }
                    }

                    foreach (array_chunk($translationsToCreate, 300) as $chunk) {
                        Translation::insert($chunk);
                    }
                }
            }
        });
    }
}