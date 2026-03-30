<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class nomValoresRequest extends FormRequest
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
            //
        ];
    }

    public function rulesForStoreOne()
    {
        return [
            "nivel" => [
                "required",
                Rule::unique('nom_valores')->where(function ($query) {
                    return $query->where('grupo', request('grupo'));
                }),
            ],
            "valor" => "required",
            "grupo" => "required",
            "aplica_pocent_adic" => "required",
            "tipo" => "required",
            "moneda" => "required"
        ];
    }

    public function rulesForStoreGroup()
    {
        return [
            "nivel" => "required",
            "valor" => "required",
            "grupo" => "required",
            "aplica_pocent_adic" => "required",
            "tipo" => "required"
        ];
    }

    public function rulesForUpdateValue()
    {
        return [
            "valor" => "required",
        ];
    }
}
