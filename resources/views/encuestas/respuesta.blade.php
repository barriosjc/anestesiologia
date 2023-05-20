@extends('layouts.main')

@section('titulo', $titulo)
@section('contenido')
<div class="container-fluid px-4">
    <ul class="navbar navbar-expand-lg navbar-light bg-light">
        <li class="nav-item">
            <h6>Paso 1.</h6>
        </li>
    </ul>
    <form method="POST" action="{{ route('respuesta.store') }}"  role="form">
    @csrf
    <input type="hidden" name="users_id" value="{{auth()->id()}}">
    <input type="hidden" name="encuestas_id" value="{{$encuestas==null || count($encuestas) ? $encuestas[0]->encuestas_id : null}}">
    <button type="button" class="btn btn-lg btn-danger" data-toggle="popover" title="Popover title" data-content="And here's some amazing content. It's very engaging. Right?">Click to toggle popover</button>
    @foreach ($encuestas as $item)
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
                    <input type="hidden" name="puntos[{{$item->encuestas_opciones_id}}]" value="{{$item->eo_puntos}}">
                    <input name="opciones[]" type="checkbox" value={{$item->encuestas_opciones_id}}
                            {{in_array($item->encuestas_opciones_id, (old('opciones') !== null ? old('opciones') : array())) ? 'checked' : ''}}>
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
                <input type="radio" name='ck_tipo' value='ck_individual' id='ck_individual'
                            {{ old('ck_tipo') == 'ck_individual' ? 'checked' : ''}}>
                <label>Reconocimiento individual</label>
                <select name="user_id_reconocido" class="form-control" id="user_id_reconocido" >
                    <option value=""> --- Select ---</option>
                    @foreach ($users as $data)
                        <option value="{{ $data->id }}"{{old('user_id_reconocido') == $data->id ? 'selected' : ''}}> {{ $data->name }}</option>
                    @endforeach
                </select>
            </div>                    
        </div>
        <div class="col-12">
            <div class="form-group row">
                <input type="radio" name="ck_tipo" value="ck_grupal" id="ck_grupal"
                        {{ old('ck_tipo') == 'ck_grupal' ? 'checked' : ''}}>
                <label>Reconocimiento grupal</label>
                <select name="grupal_id_reconocido" class="form-control" id="grupal_id_reconocido" >
                    <option value=""> --- Select ---</option>
                    @foreach ($grupal as $data)
                        <option value="{{ $data->id }}" {{old('grupal_id_reconocido') == $data->id ? 'selected' : ''}} > {{ $data->descripcion }}</option>
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
            <textarea class="form-control" name="observaciones" rows='4'>{{ old('observaciones') }}</textarea>
        </div>
        <div class="col-12">
            <div class="box-footer mt20">
                <button type="submit" class="btn btn-primary">{{ __('Enviar') }}</button>
            </div>
        </div>
    </form>
</div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            //para habilitar los popovers
            // $(function () {
            //     $('[data-toggle="popover"]').popover()
            // })
            const option1 = document.querySelector('#ck_individual');
            const option2 = document.querySelector('#ck_grupal');
            if(!document.getElementById('ck_individual').checked){
                document.getElementById('user_id_reconocido').disabled = true;
            }
            if(!document.getElementById('ck_grupal').checked){
                document.getElementById('grupal_id_reconocido').disabled = true;
            }
            option1.addEventListener('change', (event) => {
                document.querySelector('#grupal_id_reconocido').options[0].selected = true;
                document.getElementById('grupal_id_reconocido').disabled = true;
                document.getElementById('user_id_reconocido').disabled = false;
            });

            option2.addEventListener('change', (event) => {
                document.querySelector('#user_id_reconocido').options[0].selected = true;
                document.getElementById('user_id_reconocido').disabled = true;
                document.getElementById('grupal_id_reconocido').disabled = false;
            });
    }, false);
    </script>
@endsection