<?php

namespace Netcore\Translator\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Netcore\Translator\Models\Translation;

class ApiController extends Controller
{

    /**
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function index(Request $request)
    {
        $receivedSecret = $request->secret;

        $configuredSecret = config('translations.api.secret');

        if($configuredSecret !== null AND $configuredSecret != $receivedSecret ) {
            return response('In order to get translations, you must provide valid secret', 422);
        }
        
        return Translation::all();
    }
}