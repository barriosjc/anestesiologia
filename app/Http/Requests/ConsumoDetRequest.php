<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class ConsumoDetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $req)
    {
           return [
            'presupuesto_cab_id' => 'required|integer|exists:presupuestos_cab,id',
            'cobertura_id'       => 'required|integer|exists:coberturas,id',
            'nivel'              => 'nullable|string|max:50',
            'nomenclador_id'     => 'required|integer',
            'nom_padre_id'       => 'required|integer|exists:nom_padres,id',
            'porcentaje'         => 'required|numeric|min:0|max:100',
            'valor'              => 'required|numeric|min:0',
            'observaciones'      => 'nullable|string|max:255',
        ];
    }
}
