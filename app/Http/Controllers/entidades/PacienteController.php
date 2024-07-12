<?php

namespace App\Http\Controllers\entidades;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Paciente;

class PacienteController extends Controller
{
    public function buscar(Request $request)
    {
        $dni = $request->input('dni');
        $paciente = Paciente::where('dni', $dni)->first();

        if ($paciente) {
            $paciente->fec_nacimiento = \Carbon\Carbon::parse($paciente->fec_nacimiento)->format('Y-m-d');
            return response()->json(['success' => true, 'data' => $paciente]);
        } else {
            return response()->json(['success' => false, 'message' => 'Paciente no encontrado']);
        }
    }
}
