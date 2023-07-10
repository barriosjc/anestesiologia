<nav class="sidenav shadow-right sidenav-light">
    <div class="sidenav-menu">
        <div class="nav accordion" id="accordionSidenav">
            @role('super-admin')
                <div class="sidenav-menu-heading">SUPER ADMIN</div>
                <a class="nav-link" href="{{ route('empresa.select') }}">
                    <div class="nav-link-icon"><i data-feather="bar-chart"></i></div>
                    Seleccionar empresa
                </a>
                <a class="nav-link" href="{{ route('empresas.index') }}">
                    <div class="nav-link-icon"><i data-feather="home"></i></div>
                    ABM de empresas
                </a>
            @endrole

            @if(Auth()->user()->can('Opciones') || Auth()->user()->can('Crear encuesta'))
                <div class="sidenav-menu-heading">ADMINISTRADOR</div>
                {{-- Sidenav Accordion (Encuestas) --}}
                <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                    data-bs-target="#collapseDashboards" aria-expanded="false" aria-controls="collapseDashboards">
                    <div class="nav-link-icon"><i data-feather="activity"></i></div>
                    Abm encuestas
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseDashboards" data-bs-parent="#accordionSidenav">
                    <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPages">
                        @can('Opciones')
                        <a class="nav-link" href="{{ route('opcion.index') }}">Opciones</a>
                        @endcan
                        @can('Crear encuesta')
                        <a class="nav-link" href="{{ route('encuesta.create') }}">Crear encuesta</a>
                        @endcan
                    </nav>
                </div>
            @endif
            {{-- Sidenav Accordion (Reconocimientos) --}}
            @if(Auth()->user()->can('Logros') || Auth()->user()->can('Dashboard'))
                <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                    data-bs-target="#collapseInformes" aria-expanded="false" aria-controls="collapseInformes">
                    <div class="nav-link-icon"><i data-feather="award"></i></div>
                    Informes (rrhh)
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseInformes" data-bs-parent="#DasboardSidenav">
                    <nav class="sidenav-menu-nested nav accordion" id="DasboardSidenavPages">
                        @can('Reconocimientos') 
                        <a class="nav-link" href="{{ route('reconocimientos.realizados', 'todos') }}">Reconocimientos</a>
                        @endcan
                        @can('Logros')
                        <a class="nav-link" href="{{ route('reconocimientos.index') }}">Logros</a>
                        @endcan
                        @can('Dashboard')
                        <a class="nav-link" href="{{ route('dashboard.show') }}">Dashboard</a>
                        @endcan
                    </nav>
                </div>
            @endif
            @if(Auth()->user()->can('ABM Usuarios') || Auth()->user()->can('Importar usuarios'))
                <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                    data-bs-target="#collapseUsuarios" aria-expanded="false" aria-controls="collapseUsuarios">
                    <div class="nav-link-icon"><i class="fa fa-user" aria-hidden="true"></i></div>
                    Usuarios
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseUsuarios" data-bs-parent="#DasboardSidenav">
                    <nav class="sidenav-menu-nested nav accordion" id="UsuariosSidenavPages">
                        @can('ABM Usuarios')
                        <a class="nav-link" href="{{ route('usuario.index') }}">ABM Usuarios</a>
                        @endcan
                        @can('Importar usuarios')
                        <a class="nav-link" href="{{ route('usuarios.importar.ver') }}">Importar usuarios</a>
                        @endcan
                    </nav>
                </div>
            @endif

            @if(Auth()->user()->can('Permisos') || Auth()->user()->can('Perfiles'))
                <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                    data-bs-target="#pagesCollapseError" aria-expanded="false" aria-controls="pagesCollapseError">
                    <div class="nav-link-icon"><i class="fa fa-key" aria-hidden="true"></i></div>
                    Administración de accesos
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="pagesCollapseError" data-bs-parent="#accordionSidenavPagesMenu">
                    <nav class="sidenav-menu-nested nav">
                        {{-- <a class="nav-link" href="{{route('usuario.index')}}">Usuarios</a>                             --}}
                        @can('Permisos')
                        <a class="nav-link" href="{{ route('permisos.index') }}">Permisos</a>
                        @endcan
                        @can('Perfiles')
                        <a class="nav-link" href="{{ route('roles.index') }}">Perfiles</a>
                        @endcan
                    </nav>
                </div>
            @endif

            @if(Auth()->user()->can('Listado de reconocimientos') ||Auth()->user()->can('Reconocimientos realizados') || Auth()->user()->can('Reconocimientos recibidos') || Auth()->user()->can('Emitir Reconocimiento'))
                <div class="sidenav-menu-heading">USUARIOS</div>
                {{-- Sidenav Link (Charts) --}}
                @can('Listado de reconocimientos')
                <a class="nav-link" href="{{ route('reconocimientos.realizados', 'periodo') }}">
                    <div class="nav-link-icon"><i data-feather="bar-chart"></i></div>
                    Listado de reconocimientos
                </a>
                @endcan
                @can('Emitir Reconocimiento')
                <a class="nav-link" href="{{ route('respuesta') }}">
                    <div class="nav-link-icon"><i data-feather="bar-chart"></i></div>
                    Emitir Reconocimiento
                </a>
                @endcan
                @can('Reconocimientos realizados') 
                <a class="nav-link" href="{{ route('reconocimientos.realizados', 'user') }}">
                    <div class="nav-link-icon"><i class="fa-solid fa-medal"></i></div>Reconocimientos
                    realizados</a>
                @endcan
                @can('Reconocimientos recibidos')
                <a class="nav-link" href="{{ route('reconocimientos.recibidos', 'user') }}">
                    <div class="nav-link-icon"><i class="fa-solid fa-medal"></i></div>Reconocimientos
                    recibidos</a>
                @endcan
            @endif
        </div>
    </div>

    {{-- Sidenav Footer --}}
    <div class="sidenav-footer">
        <div class="sidenav-footer-content">
            <div class="sidenav-footer-subtitle">Usuario :</div>
            <div class="sidenav-footer-title">{{ auth()->user()->name }}</div>
        </div>
    </div>
</nav>
