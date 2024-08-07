<?php
namespace App\Http\Controllers\produccion;

use Illuminate\Http\Request;

interface ReportStrategy
{
    public function validate(Request $request) :array;
    public function generate(Request $request);
    public function getViewName(): string;
}
