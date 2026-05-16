<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\ResetpasswordMaillable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{

    public function restablecer()
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
            $clave = bin2hex(random_bytes(5));
            $hash = Hash::make($clave);
            $user->password = $hash;
            $user->cambio_password = 1;
            $user->save();

            $correo = new ResetpasswordMaillable($user, $clave);
            Mail::send([], [], function ($message) use ($user, $correo) {
                $message->to($user->email, $user->name)
                    ->subject('Cambio de clave para ingreso al portal')
                    ->setBody($correo->render(), 'text/html');
            });
        }

        return back()->with(['status' => 'Se le ha enviado un email a '.$validated['email'].' con su nueva clave.']);
    }
}
