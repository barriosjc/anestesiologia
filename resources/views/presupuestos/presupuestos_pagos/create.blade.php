@extends('layouts.main')

@section('contenido')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ "Pagos del presupuesto - {$presupuestosCab->fecha} - {$presupuestosCab->nombre} -  {$presupuestosCab->valor}" }}
                            </span>
                            <div class="float-right">
                                <a href="{{ route('presupuestos.cab.edit', $presupuestosCab->id) }}" title="Volver">
                                    <button class="btn btn-warning btn-sm float-right">
                                        <i class="fa fa-arrow-left" aria-hidden="true"></i> Volver
                                    </button>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead">
                                    <tr><th>Fecha</th>
                                        <th>Usuario</th>
										<th>Valor</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- @dd($presupuestosDet) --}}
                                    @foreach ($pagos as $item)
                                        <tr>
                                            <td>{{ $item->fecha }}</td>
                                            <td>{{ $item->user->name }}</td>
                                            <td>{{ $item->valor }}</td>
                                            <td>
                                                    <form id="delete-form-{{ $item->id }}" action="{{ route('presupuestos.pagos.destroy',[$item->presupuesto_cab_id,$item->id]) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            title="Borrar práctica o estudio"
                                                            onclick="confirmDelete({{ $item->id }})"><i
                                                                class="far fa-trash-alt text-white"></i></button>
                                                    </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @include('presupuestos.presupuestos_pagos.form')
            </div>
        </div>
    </div>

@endsection
