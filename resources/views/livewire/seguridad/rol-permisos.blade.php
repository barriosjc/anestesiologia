<div>
    <div class="container-fluid">
        <div class="flex-center position-ref full-height">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <span>Permisos {{ $titulo }}</span>
                            <a href="{{ route('roles.index') }}" class="btn btn-warning btn-sm float-right"><i
                                    class="fa fa-arrow-left" aria-hidden="true"></i> Volver</a>
                        </div>
                        <div class="card-body">
                            @include('utiles.alerts')

                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="thead">
                                        <tr>
                                            <th>#</th>
                                            <th>Nombre</th>
                                            <th>Guard name</th>
                                            <th>Fecha Alta</th>
                                            <th class="float-right">Opciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($permisos as $item)
                                            <tr wire:key="permiso-asignado-{{ $item->id }}">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->guard_name }}</td>
                                                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                                                <td>
                                                    <div class="float-right">
                                                        <button wire:click="desasignar({{ $item->id }})"
                                                            class="btn btn-danger btn-sm" data-bs-toggle="tooltip"
                                                            title="Quitar permiso asignado al rol"><i
                                                                class="fa fa-minus text-white" aria-hidden="true"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No hay permisos asignados.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">Asignar nuevo permiso</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="thead">
                                        <tr>
                                            <th>#</th>
                                            <th>Nombre</th>
                                            <th>Guard name</th>
                                            <th>Fecha Alta</th>
                                            <th>
                                                <div class="float-right">Agregar</div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($permisoss as $item)
                                            <tr wire:key="permiso-disponible-{{ $item->id }}">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->guard_name }}</td>
                                                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                                                <td>
                                                    <div class="float-right">
                                                        <button wire:click="asignar({{ $item->id }})"
                                                            class="btn btn-success btn-sm" title="asignar rol"><i
                                                                class="fa fa-plus" aria-hidden="true"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No hay permisos para asignar.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
