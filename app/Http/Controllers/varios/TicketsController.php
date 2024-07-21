<?php

namespace App\Http\Controllers;

use App\Models\Tickets_det;
use Illuminate\Http\Request;
use App\Models\ordenes_compras_det;
use Illuminate\Support\Facades\Mail;
use App\Mail\ticketFrontMailable;
use App\Models\Cliente;

class TicketsController extends Controller
{
    public function index($detalle_id, $activo)
    {
        $valores = UtilesController::ProfileCants();
        $tickets = Tickets_det::v_listar($detalle_id)->get();
        $activo = 'open';

        return view('profile.product-message', compact('detalle_id', 'tickets', 'valores', 'activo'));
    }

    public function save(Request $request)
    {
        $validated = $request->validate(
            [
                'cut' => 'file|mimes:svg,jpg,jpeg,png,pdf|max:2048',
                'print' => 'file|mimes:svg,jpg,jpeg,png,pdf|max:2048',

            ],
        [
            'cut.mimes', 'Error!, Los formatos de archivos válidos son JPEG, PNG y PDF.',
            'cut.max', 'Error!, El tamaño del archivo no puede superar los 2 MB.',
            'print.mimes', 'Error!, Los formatos de archivos válidos son JPEG, PNG y PDF.',
            'print.max', 'Error!, El tamaño del archivo no puede superar los 2 MB.'
        ]);
                
        $tickets = new Tickets_det;
        $tickets->ordenes_compras_det_id = $request->ordenes_compras_det_id;
        $tickets->users_id = auth()->user()->id;
        $tickets->insidente =  $request->mensaje;
        $tickets->cli_imp = 'c';
        $oc_det = ordenes_compras_det::find($request->ordenes_compras_det_id);

        // Guardar los archivos
        if ($request->hasFile('print')) {
            $printFile = $request->file('print');
            $printFileName = 'print_' . time() . '.' . $printFile->getClientOriginalExtension();
            $filePath = 'clientes/' . $tickets->users_id;
            $printFile->storeAs($filePath, $printFileName, 'public');
            $tickets->print_path = $tickets->users_id . '/' . $printFileName;
            $oc_det->path_print = $tickets->print_path;
        }
        if ($request->hasFile('cut')) {
            $cutFile = $request->file('cut');
            $cutFileName = 'cut_' . time() . '.' . $cutFile->getClientOriginalExtension();
            $filePath = 'clientes/' . $tickets->users_id;
            $cutFile->storeAs($filePath, $cutFileName, 'public');
            $tickets->cut_path = $tickets->users_id . '/' . $cutFileName;
            $oc_det->path_cut = $tickets->cut_path;
        }
        $tickets->save();
        $oc_det->save();

        // mail a la imprenta
        $ticket = Tickets_det::v_listar($request->ordenes_compras_det_id)->where('tickets_det.id', $tickets->id)->first();
        $email = UtilesController::traerParamValor('aprueba_registros');
        //$email_user = Auth()->user();
        $cliente = cliente::where('user_id', Auth()->user()->id)->first();
        $correo = new TicketFrontMailable($ticket, $cliente, 'front');
        Mail::send([], [], function ($message)  use ($email, $correo) {
            $message->to($email, 'new ticket from user')
                ->subject('Nuevo ticket cargado por el cliente')
                ->setBody($correo->render(), 'text/html');
        });
        
        //$valores = UtilesController::ProfileCants();
        $tickets = Tickets_det::v_listar($request->ordenes_compras_det_id)
            ->orderby('created_at', 'asc')->get();
        $activo = 'open';
        $detalle_id = $request->ordenes_compras_det_id;

        return redirect()->route('orders.detail.product', [$detalle_id, $activo]) 
                ->with('success_msg', 'Message sended!');
    }

    public function back_index($detalle_id, $activo)
    {
        $orden = ordenes_compras_det::v_ordenes_detalle('ordenes_compras_det.id', $detalle_id)->first();
        $valores = UtilesController::ProfileCants();
        $tickets = Tickets_det::v_listar($detalle_id)
                        ->orderby('created_at', 'asc')->get();
        $activo = 'open';

        return view('ordenes.product-message', compact('detalle_id', 'tickets', 'valores', 'activo', 'orden'));
    }

    public function back_save(Request $request)
    {
        $tickets = new Tickets_det;
        $tickets->ordenes_compras_det_id = $request->ordenes_compras_det_id;
        $tickets->users_id = auth()->user()->id;
        $tickets->insidente =  $request->mensaje;
        $tickets->cli_imp = 'i';
        $tickets->save();

        // mail al cliente
        $ticket = Tickets_det::v_listar($request->ordenes_compras_det_id)->where('tickets_det.id', $tickets->id)->first();

        //$email = UtilesController::traerParamValor('aprueba_registros');
        //$email_user = Auth()->user();
        $cliente = cliente::where('user_id', $ticket->cli_users_id)->first();
        $email = $cliente->user->email;
        $correo = new TicketFrontMailable($ticket, $cliente, 'back');
        Mail::send([], [], function ($message)  use ($email, $correo) {
            $message->to($email, 'new ticket from Imprint')
                ->subject('Nuevo ticket cargado por la impresanta')
                ->setBody($correo->render(), 'text/html');
        });
        
        // $valores = UtilesController::ProfileCants();
        $tickets = Tickets_det::v_listar($request->ordenes_compras_det_id)
                        ->orderby('created_at', 'asc')->get();
        $activo = 'open';
        $detalle_id = $request->ordenes_compras_det_id;

        return redirect()->route('back.orders.detail.product', [$detalle_id, $activo]) 
                ->with('success_msg', 'Message sended!');
    }

    public function traer_cant_msg($id, $origen)
    {
        $tickets = Tickets_det::where('ordenes_compras_det_id', $id)
                        ->orderby('created_at', 'desc')
                        ->limit(5)
                        ->get();
// dd($tickets);
        $cant = 0;
        foreach($tickets as $item){
            if($item->cli_imp == $origen) {
                $cant = $cant + 1;
            }else{
                break;
            }
        }

        return response()->json($cant, 200);
    }

    public function traer_cant_msg_oc($id, $origen)
    {
        $cant = 0;
        $ordenes = ordenes_compras_det::where('ordenes_compras_id', $id)->get();
        foreach($ordenes as $det){
            $tickets = Tickets_det::where('ordenes_compras_det_id', $det->id)
                        ->orderby('created_at', 'desc')
                        ->limit(5)
                        ->get();

            foreach($tickets as $item){
                if($item->cli_imp == $origen) {
                    $cant = $cant + 1;
                }else{
                    break;
                }
            }
        }

        return $cant;

        // return response()->json($cant, 200);
    }
}
