@extends('layouts.main')

@section('titulo', 'Perfil de usuario')

@section('contenido')
    <div class="container" id="formreadonly">
        <div class="row">
            @if(!isset($readonly)) 
                @php($readonly = false)
            @endif
            <div class="col-xl-4">
                <form method="POST" action="{{ route('profile.foto') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ old('user_id', $user->id) }}" />
                    <!-- Profile picture card-->
                    <div class="card mb-4 mb-xl-0">
                        <div class="card-header">Foto de perfil</div>
                        <div class="card-body text-center">
                            <!-- Profile picture image-->
                            <img class="img-account-profile rounded-circle mb-2"
                                src="{{ Storage::disk('usuarios')->url($user->foto) }}" alt="" />
                                    <h3><span class="badge bg-primary text-white">{{ $recibidos }} puntos obtenidos</span></h3>
                        </div>
                    </div>
                </form>
    
                <div class="card">
                    <div class="card-header text-white bg-primary">
                        <div class="row">
                            <div class="col">
                                Insignias obtenidas
                            </div>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush">
                        @foreach ($reconocimientos as $data)
                            <li class="list-group-item"><i class="fa-regular fa-star-half-stroke"></i> {{ $data->motivo }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="col-xl-8">
                <!-- Account details card-->
                <div class="card mb-4">
                    <div class="card-header">Detalle de usuario</div>
                    <div class="card-body">
                            <input type="hidden" name="id" value="{{ old('user_id', $user->id) }}" />
                            <div class="mb-3">
                                <label class="small mb-1 text-info">Empresa</label>
                                <p class="small mb-1">{{$user->empresas->razon_social}}</p>

                            </div>
                            <!-- Form Row-->
                            <div class="row gx-3 mb-3">
                                <div class="col-md-6">
                                    <label class="small mb-1 text-info" for="last_name">Nombre y apellido</label>
                                    <p class="small mb-1" for="last_name">{{$user->last_name}}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="small mb-1 text-info" for="name">Usuario</label>
                                    <p class="small mb-1" for="last_name">{{$user->name}}</p>
                                </div>
                            </div>
                            <div class="row gx-3 mb-3">
                                <div class="col-md-6">
                                    <label class="small mb-1 text-info">Email address</label>
                                    <p class="small mb-1" for="last_name">{{$user->email}}</p>  
                                </div>
                                <div class="col-md-6">
                                    <label class="small mb-1 text-info">Cargo</label>
                                    <p class="small mb-1" for="last_name">{{$user->cargo}}</p>  
                                </div>
                            </div>
                            <!-- Form Row-->
                            <div class="row gx-3 mb-3">
                                <div class="col-md-6">
                                    <label class="small mb-1 text-info" for="telefono">Phone number</label>
                                    <p class="small mb-1" for="last_name">{{$user->telefono}}</p>  
                                </div>
                                <div class="col-md-6">
                                    <label class="small mb-1 text-info">Jefe</label>
                                    <p class="small mb-1" for="last_name">{{$user->jefes->last_name}}</p>           
                                </div>
                            </div>
                            <!-- Form Row-->
                            <div class="row gx-3 mb-3">
                                <div class="col-md-6">
                                    <label class="small mb-1 text-info" for="telefono">Area</label>
                                    <p class="small mb-1" for="last_name">{{$user->area}}</p>  
                                </div>

                            </div>
                            <!-- Form Row-->
                            <div class="row gx-3 mb-3">
                                <!-- Form Group (phone number)-->
                                <div class="col-md-12">
                                    <label class="small mb-1 text-info" for="observaciones">Observaciones</label>
                                    <p class="small mb-1" for="last_name">{{$user->observaciones}}</p> 
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    
    
    </div>
@endsection
