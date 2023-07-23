<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\resetpasswordMaillable;
use Illuminate\Support\Facades\Mail;
use App\Models\Empresa;
use App\Models\user;

class ResetPasswordController extends Controller
{

    public function  restablecer()
    {
        return view('auth.passwords.email');
    }

    public function email(Request $request)
    {
        $validated = $request->validate(
            [
                'email' => 'required|string|max:200',
            ]
        );

        $user = user::where('email', $request->email)->first();

        if (!empty($user)) {
            $mensaje = "string para crear clave.";
            $hash = hash('md5', $mensaje); // Puedes usar otros algoritmos además de md5
            $clave = substr($hash, 0, 12);
            $empresa = empresa::where("id", $user->empresas_id)->first();
            $correo = new resetpasswordMaillable($user, $clave);
            Mail::send([], [], function ($message)  use ($request, $correo, $empresa) {
                $message->to($request->email, $request->last_name)
                    ->from($empresa->email_contacto, $empresa->email_nombre)
                    ->subject('Cambio de clave para ingreso a portal Clap!')
                    ->setBody($correo->render(), 'text/html');
            });
        }

        return back()->with(['status' => 'Se le ha enviado un email a '.$validated['email'].' con su nueva clave.']);
    }
}
