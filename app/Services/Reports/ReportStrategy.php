<?php

namespace App\Services\Reports;

interface ReportStrategy
{
    public function validate(array $filtros): array;
    public function generate(array $filtros);
    public function getViewName(): string;
    // formatos validos a4, letter, legal, --- portrait, landscape
    public function getFormat(): PdfFormat;
}
