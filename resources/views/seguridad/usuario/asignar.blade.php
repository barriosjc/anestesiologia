<br />
<div class="container-fluid">
    <div class="flex-center position-ref full-height">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Asignar nuevos usuarios</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Usuario</th>
                                        <th>Usu Verificado</th>
                                        <th>Mail</th>
                                        <th>
                                            <div class="float-right">
                                                Valores
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->email_verified_at }}</td>
                                            <td>{{ $item->email }}</td>
                                            <td>
                                                <div class="float-right">
                                                    @if ($padre === 'roles')
                                                        <a href="{{ url('/roles/' . $rolid . '/usuarios/' . $item->id . '/asignar') }}"
                                                            title="asignar usuario"><button
                                                                class="btn btn-success btn-sm"><i class="fa fa-plus"
                                                                    aria-hidden="true"></i></button></a>
                                                    @else
                                                        <a href="{{ url('/permisos/' . $perid . '/usuarios/' . $item->id . '/asignar') }}"
                                                            title="asignar permiso"><button
                                                                class="btn btn-success btn-sm"><i class="fa fa-plus"
                                                                    aria-hidden="true"></i></button></a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if(empty($users))
                                <div class="pagination-wrapper"> {!! $users->appends(['search' => Request::get('search')])->render() !!} </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
