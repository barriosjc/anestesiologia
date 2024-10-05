@extends('layouts.main')

@section('titulo', 'Asignar reconocimientos')
@section('contenido')
    <div class="container-fluid">
        <div class="flex-center position-ref full-height">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span id="card_title">
                            {{ __('Reconocimientos') }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead">
                                <tr>
                                    <th>No</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Motivo</th>
                                    <th>
                                        <div class="float-right">
                                            Valores
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reconocimientos as $reconocimiento)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $reconocimiento->last_name }}</td>
                                        <td>{{ $reconocimiento->email }}</td>
                                        <td>{{ $reconocimiento->motivo }}</td>
                                        <td>
                                            <div class="float-right">
                                                <form action="{{ route('reconocimientos.delete', $reconocimiento->reconocimiento_id) }}"
                                                    method="POST">
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
                        {!! $reconocimientos->links() !!}
                    </div>

                    <hr>
                    <form action="{{ route('reconocimientos.save') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Usuario</label>
                                    <select name="users_id" class="form-control" id="users_id">
                                        <option value=""> -- Seleccione --</option>
                                        @foreach ($users as $data)
                                            <option value="{{ $data->id }}">
                                                {{ $data->last_name . ' - ' . $data->email }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="small mb-1" for="cargo">Reconocimiento</label>
                                <input class="form-control" id="motivo" name="motivo" type="text"
                                    placeholder="reconocimiento al empleado" />
                            </div>
                            <div class="col-12 mt-4">
                                <!-- Save changes button-->
                                <button class="btn btn-primary" type="submit">Guardar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
