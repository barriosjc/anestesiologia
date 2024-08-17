@extends('layouts.main')

@section('titulo', 'Actualización - Plantillas de empleados')
@section('contenido')
    <div class="container-fluid">
        <div class="flex-center position-ref full-height">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">Importar datos de usuarios</div>
                        <div class="card-body">
                            <h3>1 . Descargar plantilla de empleados</h3>
                            <a href="{{route('usuarios.exportar')}}" title="">
                                <button class="btn btn-warning btn-sm">
                                    <i class="fa fa-table-cells" aria-hidden="true"></i>
                                    <span style="padding-left: 10px;" class="ml-2">Descargar</span>
                                </button>
                            </a>
                            <h3 class="mt-5" >2 . Realice las modificaciones en el archivo descargado</h3>
                            <ul>
                                <li>Agregue nuevos usuarios al final del Excel.</li>
                                <li>Modifique datos de usuarios existentes.</li>
                                <li>Borre la linea de usuarios a deshabilitar.</li>
                                <li>El email no se puede ser vacio y no se puede repetir</li>
                            </ul>
                            <h3 class="mt-5">3 . Cargar nueva plantilla de empleados</h3>
                            <form method="POST" action="{{route('usuarios.importar.subir')}}" accept-charset="UTF-8"  enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="file" name="usuarios" id="usuarios" class="form-control"
                                            aria-describedby="btncarga">
                                        <button class="nput-group-text btn btn-primary" type="=submit"
                                            id="btncarga">Importar
                                            usuarios</button>
                                    </div>
                                </div>
                                <p class="text-danger"><small>Seleccione el archivo con la lista de usuarios y luego haga click sobre 'Importar usuarios'</small> </p>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
