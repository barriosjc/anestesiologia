<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Empresa;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    public function showLoginForm()
    {
        return view('auth.auth-login-basic');
    }


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    //     public function loggedOut()
    //     {
    //         session()->forget('empresa');
    //     }
    protected function attemptLogin(Request $request)
    {
        $credentials = $this->credentials($request);
        if (session()->has('empresa')) {
            $credentials['empresas_id'] = session('empresa')->id;
        }
        // Modifica la validación para verificar la pertenencia del usuario a una empresa
        if (Auth::attempt($credentials)) {
            $empresa = Empresa::where('id', Auth()->user()->empresas_id)->first();
            if ($empresa) {
                session(['empresa' => $empresa]);
                return true;
            }
        }
        
        return false;
    }
}
