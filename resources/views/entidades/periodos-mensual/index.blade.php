@extends('layouts.app')

@section('template_title')
    Periodos Mensual
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Periodos Mensual') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('periodosmensual.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
										<th>Desde</th>
										<th>Hasta</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($periodosMensuals as $periodosMensual)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $periodosMensual->descripcion }}</td>
											<td>{{ $periodosMensual->desde }}</td>
											<td>{{ $periodosMensual->hasta }}</td>

                                            <td>
                                                <form action="{{ route('periodosmensual.destroy',$periodosMensual->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('periodosmensual.show',$periodosMensual->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('periodosmensual.edit',$periodosMensual->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $periodosMensuals->links() !!}
            </div>
        </div>
    </div>
@endsection
