<div>
    <div class="container-fluid">
        <div class="flex-center position-ref full-height">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <span>Usuarios {{ $titulo }}</span>
                            <a href="{{ route('roles.index') }}" class="btn btn-warning btn-sm float-right"><i
                                    class="fas fa-arrow-left" aria-hidden="true"></i> Volver</a>
                        </div>
                        <div class="card-body">
                            @include('utiles.alerts')

                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="thead">
                                        <tr>
                                            <th>#</th>
                                            <th>Usuario</th>
                                            <th>Avatar</th>
                                            <th>Mail</th>
                                            <th class="float-right">Valores</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($user as $item)
                                            <tr wire:key="usuario-asignado-{{ $item->id }}">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td><img src="{{ Storage::disk('usuarios')->url($item->foto) }}"
                                                        class="rounded-circle" width="45px" alt=""></td>
                                                <td>{{ $item->email }}</td>
                                                <td>
                                                    <div class="float-right">
                                                        <button wire:click="desasignar({{ $item->id }})"
                                                            class="btn btn-danger btn-sm" data-bs-toggle="tooltip"
                                                            title="Quitar Rol asignados al usuario"><i
                                                                class="fa fa-minus text-white" aria-hidden="true"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No hay usuarios asignados.</td>
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
                        <div class="card-header">Asignar nuevos usuarios</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="thead">
                                        <tr>
                                            <th>#</th>
                                            <th>Usuario</th>
                                            <th>Usu Verificado</th>
                                            <th>Mail</th>
                                            <th>
                                                <div class="float-right">Valores</div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($users as $item)
                                            <tr wire:key="usuario-disponible-{{ $item->id }}">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->email_verified_at }}</td>
                                                <td>{{ $item->email }}</td>
                                                <td>
                                                    <div class="float-right">
                                                        <button wire:click="asignar({{ $item->id }})"
                                                            class="btn btn-success btn-sm" title="asignar usuario"><i
                                                                class="fa fa-plus" aria-hidden="true"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No hay usuarios para asignar.</td>
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
