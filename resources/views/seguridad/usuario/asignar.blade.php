
<br/>
<div class="flex-center position-ref full-height">
  <div class="content">
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
                    <th>Nombre</th>
                    <th>Nombre Completo</th>
                    <th>Usu Verificado</th>
                    <th>Mail</th>
                    <th>
                      <div class="float-right">
                        Opciones
                      </div>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($users as $item)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->last_name }}</td>
                    <td>{{ $item->email_verified_at }}</td>
                    <td>{{ $item->email }}</td>
                    <td>
                      <div class="float-right">
                        @if($padre === 'roles')
                          <a href="{{ url( '/roles/' . $rolid . '/usuarios/' . $item->id . '/asignar') }}" title="asignar usuario"><button class="btn btn-success btn-sm"><i class="fa fa-plus" aria-hidden="true"></i></button></a>
                        @else
                          <a href="{{ url( '/permisos/' . $perid . '/usuarios/' . $item->id . '/asignar') }}" title="asignar permiso"><button class="btn btn-success btn-sm"><i class="fa fa-plus" aria-hidden="true"></i></button></a>
                        @endif
                      </div>
                    </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
              <div class="pagination-wrapper"> {!! $users->appends(['search' => Request::get('search')])->render() !!} </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
