<?php

namespace App\Http\Controllers\entidades;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\NomPadreRepositoryInterface;

class NomPadreController extends Controller
{
    protected $nomPadreRepository;

    public function __construct(NomPadreRepositoryInterface $nomPadreRepository)
    {
        $this->nomPadreRepository = $nomPadreRepository;
    }

    public function index(Request $request)
    {
        $tipo = $request->has('tipo') ? $request->tipo : session('ses_nom_tipo');
        session(['ses_nom_tipo' => $tipo]);
        $nomencladores = $this->nomPadreRepository->getByTipo(session('ses_nom_tipo'));

        return view('entidades.nom_padres.index', compact('nomencladores'));
    }

    public function create()
    {
        return view('entidades.nom_padres.create');
    }

    public function store(Request $request)
    {
        $this->nomPadreRepository->store($request);

        return redirect()->route('entidades.nom_padres.index', session('ses_nom_tipo'));
    }
}
