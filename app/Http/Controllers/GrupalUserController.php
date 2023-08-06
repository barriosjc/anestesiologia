<?php

namespace App\Http\Controllers;

use App\Models\GrupalUser;
use Illuminate\Http\Request;

/**
 * Class GrupalUserController
 * @package App\Http\Controllers
 */
class GrupalUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $grupalUsers = GrupalUser::simplepaginate();

        return view('grupaluser.index', compact('grupalUsers'))
            ->with('i', (request()->input('page', 1) - 1) * $grupalUsers->perPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $grupalUser = new GrupalUser();
        return view('grupaluser.create', compact('grupalUser'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate(GrupalUser::$rules);

        $grupalUser = GrupalUser::create($request->all());

        return redirect()->route('grupalusers.index')
            ->with('success', 'GrupalUser created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $grupalUser = GrupalUser::find($id);

        return view('grupaluser.show', compact('grupalUser'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $grupalUser = GrupalUser::find($id);

        return view('grupaluser.edit', compact('grupalUser'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  GrupalUser $grupalUser
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GrupalUser $grupalUser)
    {
        request()->validate(GrupalUser::$rules);

        $grupalUser->update($request->all());

        return redirect()->route('grupalusers.index')
            ->with('success', 'GrupalUser actualizada correctamente');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $grupalUser = GrupalUser::find($id)->delete();

        return redirect()->route('grupalusers.index')
            ->with('success', 'GrupalUser deleted successfully');
    }
}
