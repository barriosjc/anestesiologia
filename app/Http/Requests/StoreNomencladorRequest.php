<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNomencladorRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $nomencladorId = empty($_REQUEST['id']) ? null : $_REQUEST['id'];

        return [
            'nom_padre_id' => 'required|integer|exists:nom_padres,id',
            'nombre' => 'required|string|max:255',
            'codigo' => [
                'required',
                'string',
                Rule::unique('nom_practicas_estudios', 'codigo')->ignore($nomencladorId),
            ],
        ];
    }

    public function messages()
    {
        return [
            'codigo.unique' => 'El código ingresado ya fue asignado a otra práctica o estudio, no se puede guardar o modificar. Por favor, ingrese otro.',
        ];
    }
}    
