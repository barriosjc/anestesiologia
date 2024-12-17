@extends('layouts.main')

@section('contenido')
    <section class="content container-fluid">
        <div class="card card-default">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="card-title">Mensajes</span>
            </div>
            <div class="card-body">
                <p> 03/12/2024 -Administrativos y Facturistas -  En el cambio de estado de los partes, el texto ingresado se mantiene y se puede cambiar o borrar antes de guardar.</p>           
                <p> 10/12/2024 -Administrativos y Facturistas -  Se implemento la Toma de dias, para que cada usuario identifique que dia se hace cargo de los Partes de esa fecha.
                    Primero 'toma la fecha' haciendo click sobre la fecha y terminada la carga y control de los Partes de un día, ingresa nuevamente y marca 'Cerrar fecha' y puede cargar observaciones si corresponde.
                </p>  
                         
            </div>
        </div>
    </section>
@endsection
