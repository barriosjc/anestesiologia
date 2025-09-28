<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;  

class ParteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request): array
    {
        $currentYear = date('Y');

        return [
            'dni' => 'required|string|max:20',
            'cobertura_id' => 'required',
            'centro_id' => 'required',
            'profesional_id' => 'required',
            'nombre' => 'required',
            'gerenciadora_id' => 'required',
            'fec_nacimiento' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($currentYear) {
                    $year = explode('-', $value)[0];
                    if ($year < 1900 || $year > $currentYear) {
                        $fail("El campo fecha de nacimiento debe ser un año entre 1900 y $currentYear.");
                    }
                },
            ],
            'fec_prestacion' => [
                'nullable',
                'date_format:Y-m-d\TH:i',
                'required_with:fec_prestacion_fin',
                // function ($attribute, $value, $fail) use ($request) {
                //     if ($request->filled('fec_prestacion_fin')) {
                //         $ini = Carbon::createFromFormat('Y-m-d\TH:i', $value);
                //         $fin = Carbon::createFromFormat('Y-m-d\TH:i', $request->fec_prestacion_fin);
                //         if ($ini->gt($fin)) {
                //             $fail('La fecha de presentación debe ser anterior a la fecha de fin.');
                //         }
                //     }
                // },
            ],
            'fec_prestacion_fin' => [
                'nullable',
                'date_format:Y-m-d\TH:i',
                'required_with:fec_prestacion',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->filled('fec_prestacion')) {
                        $ini = Carbon::createFromFormat('Y-m-d\TH:i', $request->fec_prestacion);
                        $fin = Carbon::createFromFormat('Y-m-d\TH:i', $value);
                        if ($fin->lt($ini)) {
                            $fail('La fecha de fin debe ser posterior a la fecha de presentación.');
                        }
                    }
                },
            ],

        ];
      }
}
