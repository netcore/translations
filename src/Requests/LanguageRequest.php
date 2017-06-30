<?php

namespace Netcore\Translator\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LanguageRequest extends FormRequest
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
            'iso_code'        => [
                'required',
                'min:2',
                'max:2',
                Rule::unique('languages')->where(function ($q) {
                    $q->where('iso_code', $this->iso_code);
                    $q->where('deleted_at', null);
                })
            ],
            'title'           => 'required',
            'title_localized' => 'required'
        ];
    }
}