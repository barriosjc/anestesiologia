<?php

namespace App\Http\Controllers\produccion;

use Illuminate\Http\Request;
use App\Http\Controllers\produccion\PdfFormat;

interface ReportStrategy
{
    public function validate(Request $request) :array;
    public function generate(Request $request);
    public function getViewName(): string;
    // formatos validos a4, letter, legal, --- portrait, landscape
    public function getFormat():PdfFormat;
}
