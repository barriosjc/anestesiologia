<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNomencladorRequest extends FormRequest
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

    public function rules()
    {
        $nomencladorId = empty($_REQUEST['id']) ? null : $_REQUEST['id'];
        $nomPadreId = session('ses_nom_padre_id');

        return [
            'nom_padre_id' => [
                'integer',
                function ($attribute, $value, $fail) use ($nomPadreId) {
                    if (empty($nomPadreId)) {
                        $fail('El valor del id del nomenclador Padre no está cargado en la sesión.');
                    }
                },
            ],
            'nombre' => 'required|string|max:255',
            'codigo' => [
                'required',
                'string',
                Rule::unique('nom_practicas_estudios', 'codigo')
                    ->where('nom_padre_id', request('nom_padre_id'))
                    ->ignore($nomencladorId),
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
