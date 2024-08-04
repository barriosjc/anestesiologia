<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ProdProfCoberExport implements FromView
{
    public function view(): View {
        $consumos = DB::table('v_rendiciones')->get();
        return view("Reportes.Rendiciones.ProfFactxCentro", compact('consumos'));
    }
}
