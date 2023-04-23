@extends('layouts.app')

@section('template_title')
    Jerarquium
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Jerarquium') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('jerarquias.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
                                        
										<th>Users Id</th>
										<th>Users Empresas Id</th>
										<th>User Jefe Id</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($jerarquium as $jerarquium)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
											<td>{{ $jerarquium->users_id }}</td>
											<td>{{ $jerarquium->users_empresas_id }}</td>
											<td>{{ $jerarquium->user_jefe_id }}</td>

                                            <td>
                                                <form action="{{ route('jerarquias.destroy',$jerarquium->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary " href="{{ route('jerarquias.show',$jerarquium->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>
                                                    <a class="btn btn-sm btn-success" href="{{ route('jerarquias.edit',$jerarquium->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
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
                {!! $jerarquium->links() !!}
            </div>
        </div>
    </div>
@endsection
