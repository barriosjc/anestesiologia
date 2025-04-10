<?php

namespace App\Http\Requests;

use App\Models\PresupuestoCab;
use Illuminate\Foundation\Http\FormRequest;

class PresupuestoCabRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->isMethod('post') ? $this->rulesForStore() : $this->rulesForUpdate();
    }

    private function rulesForStore(): array
    {
        return [
            'fecha'        => ['required', 'date'],
            'nombre'       => ['required', 'string', 'max:255'],
            'fecha_nac'    => ['nullable', 'date'],
            'dni'          => ['nullable', 'string', 'max:20'],
            'centro_id'    => ['required', 'integer', 'exists:centros,id'],
            'observaciones'=> ['nullable', 'string', 'max:2000'],
        ];
    }

    private function rulesForUpdate(): array
    {
        return [
            'fecha'        => ['sometimes', 'date'],
            'nombre'       => ['sometimes', 'string', 'max:255'],
            'fecha_nac'    => ['nullable', 'date'],
            'dni'          => ['nullable', 'string', 'max:20'],
            'centro_id'    => ['sometimes', 'integer', 'exists:centros,id'],
            'observaciones'=> ['nullable', 'string', 'max:2000'],
        ];
    }
    
    public function withValidator($validator)
    {
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $presupuesto = PresupuestoCab::find($this->route('presupuesto'));

            if (!$presupuesto || $presupuesto->estado !== 'I') {
                $validator->errors()->add('estado', 'Solo se pueden modificar presupuestos en estado que no tenga pagos realizados.');
            }
        }
    }
}
