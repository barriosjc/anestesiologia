@extends('layouts.main')

@section('titulo', 'Opiciones')
@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Valores') }}
                            </span>

                            <div class="float-right">
                                <a href="{{ route('opcion.create') }}" class="btn btn-primary btn-sm float-right"
                                    data-placement="left">
                                    {{ __('Create New') }}
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

                                        <th>Descripcion</th>
                                        <th>Detalle</th>
                                        <th>Imagen</th>
                                        <th>Style</th>
                                        <th>Habilitada</th>
                                        <th>Puntos</th>
                                        <th>
                                            <div class="float-right">
                                                Valores
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($opciones as $opcion)
                                        <tr>
                                            <td>{{ ++$i }}</td>

                                            <td>{{ $opcion->descripcion }}</td>
                                            <td>{{ $opcion->detalle }}</td>
                                            <td>{{ $opcion->imagen }}</td>
                                            <td>{{ $opcion->style }}</td>
                                            @if ($opcion->habilitada == 1)
                                                <td>
                                                    <div class="badge bg-primary text-white rounded-pill-yes-no">
                                                        SI </div>
                                                </td>
                                            @else
                                                <td>
                                                    <div class="badge bg-danger text-white rounded-pill-yes-no">
                                                        NO</div>
                                                </td>
                                            @endif
                                            <td>{{ $opcion->puntos }}</td>
                                            <td style="width: 180px;">
                                                <form action="{{ route('opcion.destroy', $opcion->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <a class="btn btn-sm btn-primary"
                                                        href="{{ route('opcion.show', $opcion->id) }}">
                                                        <i class="fa fa-fw fa-eye"></i>
                                                    </a>
                                                    <a class="btn btn-sm btn-success"
                                                        href="{{ route('opcion.edit', $opcion->id) }}">
                                                        <i class="fa fa-fw fa-edit"></i>
                                                    </a>
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Confirma eliminar?')">
                                                        <i class="fa fa-fw fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {!! $opciones->links() !!}
            </div>
        </div>
    </div>
@endsection
