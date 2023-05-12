@extends('layouts.main')

@section('titulo', "Usuarios")
@section('contenido')
<div class="flex-center position-ref full-height">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">Editar Usuario #{{ $user->id }}</div>
          <div class="card-body">
            <a href="{{ url('/usuario') }}" title="Volver"><button class="btn btn-warning btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Volver</button></a>
            <br />
            <br />

            @if ($errors->any())
            <ul class="alert alert-danger">
              @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
              @endforeach
            </ul>
            @endif

            <form method="POST" action="{{ url('/usuario/' . $user->id) }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
              {{ method_field('PATCH') }}
              {{ csrf_field() }}

              @include ('seguridad.usuario.form', ['formMode' => 'edit'])

            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection