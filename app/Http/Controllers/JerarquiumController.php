<?php

namespace App\Http\Controllers;

use App\Models\Jerarquium;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Empresa; 
use Exception;
use Throwable;
// use Carbon\Exceptions\Exception;

/**
 * Class JerarquiumController
 * @package App\Http\Controllers
 */
class JerarquiumController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $jerarquiums = Jerarquium::simplepaginate();
        $users = User::all();
        $jefeusers = User::all();
        $empresas = Empresa::all();

        return view('entidades.jerarquium.index', compact('jerarquiums', "users", "jefeusers", "empresas"))
            ->with('i', (request()->input('page', 1) - 1) * $jerarquiums->perPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $jerarquium = new Jerarquium();
        $users = User::all();
        $jefeusers = User::all();
        // $empresas = Empresa::pluck("razon_social", "id")->toArray();
        // $empresas = ["0" => " --- Select ---"] + $empresas;
        $empresas = Empresa::select("razon_social", "id")->get();


        return view('entidades.jerarquium.create',  compact('jerarquium', "users", "jefeusers", "empresas"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate(Jerarquium::$rules);
        try {   
                $jerarquium = Jerarquium::create($request->all());

        } catch (Throwable $e) {
            //dd($e->getMessage());
            $msg = "Error reportado: ".$e->getMessage();
            return back()->withErrors(['message' => $msg]);
        }
        return redirect()->route('jerarquias.index')
            ->with('success', 'Jerarquium created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $jerarquium = Jerarquium::find($id);

        return view('entidades.jerarquium.show', compact('jerarquium'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $jerarquiums = Jerarquium::find($id);

        return view('entidades.jerarquium.edit', compact('jerarquiums'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Jerarquium $jerarquium
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Jerarquium $jerarquium)
    {
        request()->validate(Jerarquium::$rules);

        $jerarquium->update($request->all());

        return redirect()->route('jerarquias.index')
            ->with('success', 'Jerarquium actualizada correctamente');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $jerarquium = Jerarquium::find($id)->delete();

        return redirect()->route('jerarquias.index')
            ->with('success', 'Jerarquium deleted successfully');
    }
}
