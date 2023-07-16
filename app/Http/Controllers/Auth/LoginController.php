<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Empresa;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\PermissionServiceProvider;

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
                //var_dump(Auth::getDefaultDriver(), Auth::getDefaultGuardName());
                Auth::setDefaultDriver($empresa->uri);
                //PermissionServiceProvider::setDefaultAuthGuard($empresa->uri);
                Cache::flush();
                Artisan::call('config:clear');  
                Artisan::call('cache:clear');  
                //Artisan::call('config:cache');  
            
                Auth::shouldUse($empresa->uri);          
                //dd(Auth::getDefaultDriver());
            }else {
                $empresa = new Empresa;
                $empresa->uri = 'web';
                session(['empresa' => $empresa]);
            }

            return true;
        }

        return false;
    }

    protected function authenticated(Request $request, $user)
    {
        // // dd('llega a auten', $user);
        // if ($user->empresas_id === 0) {
        //     return redirect()->route('empresa.select');
        // }

        // if ($user->cambio_password === 1) {
        //     return redirect()->route('profile.password');
        // }

        return redirect()->route('main');

    }

}
