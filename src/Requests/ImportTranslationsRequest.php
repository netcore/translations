<?php

namespace Netcore\Translator\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportTranslationsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //'excel' => 'required|mimes: xlsx, xls' // Excel mime type detection seems to be buggy.
            'excel' => 'required',
        ];
    }
}
