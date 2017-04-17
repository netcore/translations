<?php

namespace Netcore\Translator\Controllers;

use Illuminate\Routing\Controller;
use Netcore\Translator\Models\Language;
use Netcore\Translator\Requests\LanguageRequest;

class LanguagesController extends Controller
{

    /**
     *
     * @var String
     */
    private $viewNamespace = 'laravel-translations-in-database::languages';

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
        $cacheTag = config('laravel_translations_in_database.languages_cache_tag');
        if($cacheTag AND function_exists($cacheTag)) {
            $this->cacheTag = $cacheTag();
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $languages = Language::all();

        return view($this->viewNamespace . '.index', compact('languages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view($this->viewNamespace . '.create');
    }

    /**
     * @param LanguageRequest $request
     * @return mixed
     */
    public function store(LanguageRequest $request)
    {
        Language::create($request->all());

        // Flush cache
        cache()->tags([
            $this->cacheTag
        ])->flush();

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
        return view($this->viewNamespace . '.edit', compact('language'));
    }

    /**
     * @param LanguageRequest $request
     * @param Language $language
     * @return mixed
     */
    public function update(LanguageRequest $request, Language $language)
    {
        $language->update($request->all());

        // Flush cache
        cache()->tags([
            $this->cacheTag
        ])->flush();

        return redirect()->back()->withSuccess('Successfully updated!');
    }

    /**
     * @param Language $language
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Language $language)
    {
        $language->delete(); // Soft delete

        // Flush cache
        cache()->tags([
            $this->cacheTag
        ])->flush();

        return response()->json([
            'state' => 'success'
        ]);
    }
}