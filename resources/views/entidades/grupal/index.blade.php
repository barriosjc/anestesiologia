@extends('layouts.main')

@section('titulo', 'Cargos')
@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Grupal') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('grupals.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
                                  {{ __('Nuevo') }}
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

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($grupals as $grupal)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $grupal->descripcion }}</td>

                                            <td>
                                                <form action="{{ route('grupals.destroy',$grupal->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('grupals.show',$grupal->id) }}"><i class="fa fa-fw fa-eye"></i> </a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('grupals.edit',$grupal->id) }}"><i class="fa fa-fw fa-edit"></i></a>
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-fw fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {!! $grupals->links() !!}
            </div>
        </div>
    </div>
@endsection
