<?php

namespace App\Http\Controllers;

use App\Models\Jerarquium;
use Illuminate\Http\Request;

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
        $jerarquium = Jerarquium::paginate();

        return view('jerarquium.index', compact('jerarquium'))
            ->with('i', (request()->input('page', 1) - 1) * $jerarquium->perPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $jerarquium = new Jerarquium();
        return view('jerarquium.create', compact('jerarquium'));
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

        $jerarquium = Jerarquium::create($request->all());

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

        return view('jerarquium.show', compact('jerarquium'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $jerarquium = Jerarquium::find($id);

        return view('jerarquium.edit', compact('jerarquium'));
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
            ->with('success', 'Jerarquium updated successfully');
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
