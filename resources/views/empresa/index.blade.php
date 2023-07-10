@extends('layouts.main')

@section('titulo', 'Empresas')
@section('contenido')
    <div class="container-fluid">
        <div class="flex-center position-ref full-height">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">

                                        <span id="card_title">
                                            {{ __('Empresa') }}
                                        </span>

                                        <div class="float-right">
                                            <a href="{{ route('empresas.create') }}"
                                                class="btn btn-primary btn-sm float-right" data-placement="left">
                                                {{ __('Crear nueva') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="thead">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Razón Social</th>
                                                    <th>Contacto</th>
                                                    <th>Telefono</th>
                                                    <th>Prefijo</th>
                                                    <th>logo</th>
                                                    <th>
                                                        <div class="float-right">
                                                            Opciones
                                                        </div>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($empresas as $empresa)
                                                    <tr>
                                                        <td>{{ ++$i }}</td>

                                                        <td>{{ $empresa->razon_social }}</td>
                                                        <td>{{ $empresa->contacto }}</td>
                                                        <td>{{ $empresa->telefono }}</td>
                                                        <td>{{ $empresa->uri }}</td>
                                                        <td><img src="{{asset(Storage::disk('empresas')->url($empresa->logo??'')) }}"
                                                                width="45px" alt=""> </td>
                                                        <td>
                                                            <div class="float-right">
                                                                <form action="{{ route('empresas.destroy', $empresa->id) }}"
                                                                    method="POST">
                                                                    <a class="btn btn-sm btn-primary "
                                                                        href="{{ route('empresas.show', $empresa->id) }}"><i
                                                                            class="fa fa-fw fa-eye"></i></a>
                                                                    <a class="btn btn-sm btn-success"
                                                                        href="{{ route('empresas.edit', $empresa->id) }}"><i
                                                                            class="fa fa-fw fa-edit"></i></a>
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                                            onclick="return confirm('Confirma eliminar?')"><i
                                                                            class="fa fa-fw fa-trash"></i></button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            {!! $empresas->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
