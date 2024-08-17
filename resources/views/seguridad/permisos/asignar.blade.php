<br />
<div class="container-fluid">
    <div class="flex-center position-ref full-height">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Asignar nuevo permiso</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Guard name</th>
                                        <th>Fecha Alta</th>
                                        <th>
                                            <div class="float-right">
                                                Agregar
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- muestra los permisos que no estan asignados --}}
                                    @foreach ($permisoss as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->guard_name }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                                            <td>
                                                <div class="float-right">
                                                    @if ($padre === 'usuarios')
                                                        <a href="{{ url('/usuario/' . $usuid . '/permisos/' . $item->id . '/asignar') }}"
                                                            title="asignar usuario"><button
                                                                class="btn btn-success btn-sm"><i class="fa fa-plus"
                                                                    aria-hidden="true"></i></button></a>
                                                    @else
                                                        <a href="{{ url('/roles/' . $rolid . '/permisos/' . $item->id . '/asignar') }}"
                                                            title="asignar rol  "><button
                                                                class="btn btn-success btn-sm"><i class="fa fa-plus"
                                                                    aria-hidden="true"></i></button></a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {{-- <div class="pagination-wrapper"> {!! $permisoss->appends(['search' => Request::get('search')])->render() !!} </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
