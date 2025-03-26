<?php

namespace App\Http\Controllers\entidades;

use App\Http\Controllers\Controller;
use App\Repositories\NomPadreRepositoryInterface;

class NomPadreController extends Controller
{
    protected $nomPadreRepository;

    public function __construct(NomPadreRepositoryInterface $nomPadreRepository)
    {
        $this->nomPadreRepository = $nomPadreRepository;
    }

    public function index(string $tipo)
    {
        $nomencladores = $this->nomPadreRepository->getByTipo($tipo);
        session(['ses_nom_tipo' => $tipo]);

        return view('entidades.nom_padres.index', compact('nomencladores'));
    }
}
