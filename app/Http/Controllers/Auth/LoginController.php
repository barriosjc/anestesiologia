<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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
                // Artisan::call('route:clear');
                // Artisan::call('config:clear');
                // Artisan::call('view:clear');
                // Artisan::call('cache:clear');
                // Artisan::call('config:cache');
                // Artisan::call('route:cache');
                // Artisan::call('storage:link');
                
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
        try {
            $credentials = $this->credentials($request);
            // if (session()->has('empresa')) {
            //     $credentials['empresas_id'] = session('empresa')->id;
            // }
            // Modifica la validación para verificar la pertenencia del usuario a una empresa
            if (Auth::attempt($credentials)) {
                // $empresa = Empresa::where('id', Auth()->user()->empresas_id)->first();
                // if ($empresa) {
                //     session(['empresa' => $empresa]);
                //     Auth::shouldUse($empresa->uri);
                //     //dd(Auth::getDefaultDriver());
                // }else {
                //     $empresa = new Empresa;
                //     $empresa->uri = 'web';
                //     session(['empresa' => $empresa]);
                // }

                return true;
            }
        } catch (\Exception $e) {
            die("</br></br></br></br><pre>" .
                $e->getMessage() .
                "</pre>");
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

    /**
     * Get the post login redirect path based on user attributes.
     *
     * @return string
     */
    // protected function redirectTo()
    // {
    //     // dd(Auth());
    //     // $id = Auth()->user()->id;
    //     // $user = user::where("id", $id)->first();

    //     // $tiene = $user->hasRole('usuario', session('empresa')->uri);
    //     // dd($tiene);
    //     // if ($tiene){
    //     //     return route('respuesta');
    //     // }

    //     return route('main');

    // }
}
