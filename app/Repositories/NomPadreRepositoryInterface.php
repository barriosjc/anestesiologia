<?php

namespace App\Repositories;

interface NomPadreRepositoryInterface
{
    public function getByTipo(string $tipo);
}