
@extends('layouts.main')

@section('titulo', 'Empresas')
@section('contenido')
    <div class="container-fluid">
        <div class="flex-center position-ref full-height">
            <div class="row">
                <div class="col-md-12">
                    @include('utiles.alerts')
                    <div class="card card-default">
                        <div class="card-header">
                            <span class="card-title">{{ __('Seleccione') }} Empresa</span>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('empresa.set') }}" role="form">
                                @csrf

                                <div class="mb-3">
                                    <label class="small mb-1">Empresa</label>
                                    <select name="empresas_id" class="form-control" id="empresas_id">
                                        <option value=""> --- Select ---</option>
                                        @foreach ($empresas as $data)
                                            <option value="{{ $data->id }}">
                                                {{ $data->razon_social }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="box-footer mt20">
                                    <button type="submit" class="btn btn-primary">{{ __('Seleccionar') }}</button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
