<?php

namespace App\Services\Reports;

use Illuminate\Support\Collection;

interface ReportStrategy
{
    public function validate(array $filtros): array;
    public function generate(array $filtros): Collection;
    public function getViewName(): string;
    // formatos validos a4, letter, legal, --- portrait, landscape
    public function getFormat(): PdfFormat;
}
