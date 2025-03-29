<?php

namespace App\Repositories;

use App\Models\NomPadre;

class NomPadreRepository implements NomPadreRepositoryInterface
{
    protected $model;

    public function __construct(NomPadre $model)
    {
        $this->model = $model;
    }

    public function getByTipo(string $tipo)
    {
        return $this->model->where('tipo', $tipo)->paginate(20);
    }

    public function store($request)
    {
        $this->model->create($request->all());
    }   
}
