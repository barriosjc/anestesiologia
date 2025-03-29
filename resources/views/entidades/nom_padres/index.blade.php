@extends('layouts.main')

@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span id="card_title">
                                {{ __('Nomencladores') }}
                            </span>
                            <div class="float-right">
                                <a href="{{ route('nom_padres.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
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
                                        <th>Nro</th>
                                        <th>Nombre</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($nomencladores as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->nombre }}</td>
                                            <td>
                                                @if ($item->tipo == 'a')
                                                    <a class="btn btn-sm btn-primary "
                                                        href="{{ route('nomenclador.listas.listas', $item->id) }}"><i
                                                            class="fa fa-fw fa-eye"></i></a>
                                                @else
                                                    <a class="btn btn-sm btn-primary "
                                                        href="{{ route('nom_practicas_estudios.index', $item->id) }}"><i
                                                            class="fa fa-fw fa-eye"></i></a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @if (!empty($nomencladores))
                    {!! $nomencladores->appends(request()->query())->links('vendor.pagination.bootstrap-4') !!}
                @endif
            </div>
        </div>
    </div>
    <script src="{{ asset('js/util.js') }}"></script>
@endsection
