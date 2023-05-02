@extends('layouts.app')

@section('template_title')
    Opcione
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Opcione') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('opcion.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
                                  {{ __('Create New') }}
                                </a>
                              </div>
                        </div>
                    </div>
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success">
                            <p>{{ $message }}</p>
                        </div>
                    @endif

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
										<th>Puntos Min</th>
										<th>Puntos Max</th>

                                        <th></th>
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
											<td>{{ $opcion->habilitada }}</td>
											<td>{{ $opcion->puntos }}</td>
											<td>{{ $opcion->puntos_min }}</td>
											<td>{{ $opcion->puntos_max }}</td>

                                            <td>
                                                <form action="{{ route('opcion.destroy',$opciones->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('opcion.show',$opciones->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('opcion.edit',$opciones->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-fw fa-trash"></i> {{ __('Delete') }}</button>
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
