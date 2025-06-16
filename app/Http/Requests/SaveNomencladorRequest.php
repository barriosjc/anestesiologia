<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SaveNomencladorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ajustá si necesitás lógica de autorización
    }

    public function rules(): array
    {
        return [
            'nom_padre_id' => 'required|exists:nom_padres,id',
            'organo_id' => 'nullable|exists:nom_organos,id',
            'nivel' => 'required|string|max:5',
            'descripcion' => 'required|string|max:200',
            'codigo' => [
                'required',
                'string',
                Rule::unique('nomenclador')->where(function ($query) {
                    return $query->where('nom_padre_id', $this->nom_padre_id);
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.max' => 'El código no puede tener más de 10 caracteres.',
            'nivel.required' => 'El nivel es obligatorio.',
            'nivel.max' => 'El nivel no puede tener más de 5 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede superar los 200 caracteres.',
            'nom_padre_id.exists' => 'El padre seleccionado no existe.',
            'organo_id.exists' => 'El órgano seleccionado no existe.',
            'codigo.unique' => 'Ya existe un código de nomenclador para este listado.',
        ];
    }
}
