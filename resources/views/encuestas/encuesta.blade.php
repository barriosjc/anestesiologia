@extends('layouts.main')

@section('contenido')
<div class='content-fluid'>
    {{-- este navbar cambiarlo por lo que quieran poner, o indicar donde ponemos los titulos --}}
    <ul class="navbar navbar-expand-lg navbar-light bg-light">
        <li class="nav-item">
            <h3>Encuesta Clorox, periodo: Segundo trimestre 2023</h3>
        </li>
    </ul>
    <ul class="navbar navbar-expand-lg navbar-light bg-light">
        <li class="nav-item">
            <h6>Paso 1.</h6>
        </li>
    </ul>
    <form>
    @foreach ($encuesta as $item)
        @if (!($loop->index % $item->opcionesxcol))
            {{$cierra = false}}
            <div class="row">
        @endif
        <div class="col-lg-6 col-xl-3 mb-4">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-lg fw-bold">{{$item->descripcion}}</div>
                        </div>
                        <img src="{{asset('libs/sbadmin/assets/img/encuestas') ."/". $item->imagen}}" width="100px" height="100px"/>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between small">
                    <input name="voto" type="checkbox" value="1"/>
                </div>
            </div>
        </div>
        @if (!(($loop->index + 1) % $item->opcionesxcol))
            @php($cierra = true) 
            </div>
        @endif    
        @if ($loop->last and !$cierra)
            </div> 
        @endif    
    @endforeach

    <div class="row">
        <div class="col-12">
            <ul class="navbar navbar-expand-lg navbar-light bg-light">
                <li class="nav-item">
                    <h6>Paso 2. Elige a quién o quiénes deseas reconocer.</h6>
                </li>
            </ul>
            </div>
        <div class="col-12">
            <div class="form-group row">
                <input type="checkbox" name='ck_individual' id='ck_individual'>
                <label>Reconocimiento individual</label>
                <select name="user_id" class="form-control" id="users_id" >
                    <option value=""> --- Select ---</option>
                    @foreach ($users as $data)
                        <option value="{{ $data->id }}"> {{ $data->name }}</option>
                    @endforeach
                </select>
            </div>                    
        </div>
        <div class="col-12">
            <div class="form-group row">
                <input type="checkbox" name="ck_grupal" id="ck_grupal">
                <label>Reconocimiento grupal</label>
                <select name="grupal_id" class="form-control" id="grupal_id" >
                    <option value=""> --- Select ---</option>
                    @foreach ($grupal as $data)
                        <option value="{{ $data->id }}"> {{ $data->descripcion }}</option>
                    @endforeach
                </select>
            </div>                    
        </div>                    
    </div>
        <div class="col-12">
            <ul class="navbar navbar-expand-lg navbar-light bg-light">
                <li class="nav-item">
                    <h6>Persona/s que quieres reconocer:</h6>
                </li>
            </ul>
        </div>
        <div class="col-12">
            <h6>Paso 3 - Justificación del reconocimiento</h6>
        </div>
        <div class="col-12">
            <textarea class="form-control" name="observaciones" rows='4'></textarea>
        </div>
</form>
</div>
@endsection