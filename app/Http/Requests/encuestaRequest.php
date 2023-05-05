<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class encuestaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'empresas_id' => ['required'],
            'encuesta' =>['required', 'max:50'],
            'edicion' => ['required', 'max:200'],
            'opciones_id' => ['required'],
            
        ];
    }
}
