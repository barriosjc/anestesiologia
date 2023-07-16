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
            @if (Session::has('empresa'))
                @php($guard = session('empresa')->uri)
            @else
                @php($guard = 'web')
            @endif
            @if (Auth()->user()->hasRole('super-admin'))
                @php($super = true)
            @else
                @php($super = false)
            @endif
            @if (Auth()->user()->validar('Valores', $guard) ||
                    Auth()->user()->validar('Crear votaciones', $guard) ||
                    $super)
                <div class="sidenav-menu-heading">ADMINISTRADOR</div>
                {{-- Sidenav Accordion (Votaciones) --}}
                <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                    data-bs-target="#collapseDashboards" aria-expanded="false" aria-controls="collapseDashboards">
                    <div class="nav-link-icon"><i data-feather="activity"></i></div>
                    Abm de Votaciones
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseDashboards" data-bs-parent="#accordionSidenav">
                    <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPages">
                        @if (Auth()->user()->validar('Valores', $guard) || $super)
                            <a class="nav-link" href="{{ route('opcion.index') }}">Valores</a>
                        @endif
                        @if (Auth()->user()->validar('Crear encuesta', $guard) || $super)
                            <a class="nav-link" href="{{ route('encuesta.create') }}">Crear votaciones</a>
                        @endif
                    </nav>
                </div>
            @endif
            {{-- Sidenav Accordion (Reconocimientos) --}}

            @if (Auth()->user()->validar('Logros', $guard) ||
                    Auth()->user()->validar('Dashboard', $guard) ||
                    Auth()->user()->validar('Dashboard', $guard) ||
                    $super)
                <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                    data-bs-target="#collapseInformes" aria-expanded="false" aria-controls="collapseInformes">
                    <div class="nav-link-icon"><i data-feather="award"></i></div>
                    Informes (rrhh)
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseInformes" data-bs-parent="#DasboardSidenav">
                    <nav class="sidenav-menu-nested nav accordion" id="DasboardSidenavPages">
                        @if (Auth()->user()->validar('Reconocimientos', $guard) || $super)
                            <a class="nav-link"
                                href="{{ route('reconocimientos.realizados', 'todos') }}">Reconocimientos</a>
                        @endif
                        @if (Auth()->user()->validar('Logros', $guard) || $super)
                            <a class="nav-link" href="{{ route('reconocimientos.index') }}">Logros</a>
                        @endif
                        @if (Auth()->user()->validar('Dashboard', $guard) || $super)
                            <a class="nav-link" href="{{ route('dashboard.show') }}">Dashboard</a>
                        @endif
                    </nav>
                </div>
            @endif
            @if (Auth()->user()->validar('ABM Usuarios', $guard) ||
                    Auth()->user()->validar('Importar usuarios', $guard) ||
                    $super)
                <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                    data-bs-target="#collapseUsuarios" aria-expanded="false" aria-controls="collapseUsuarios">
                    <div class="nav-link-icon"><i class="fa fa-user" aria-hidden="true"></i></div>
                    Usuarios
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseUsuarios" data-bs-parent="#DasboardSidenav">
                    <nav class="sidenav-menu-nested nav accordion" id="UsuariosSidenavPages">
                        @if (Auth()->user()->validar('ABM Usuarios', $guard) || $super)
                            <a class="nav-link" href="{{ route('usuario.index') }}">ABM Usuarios</a>
                        @endif
                        @if (Auth()->user()->validar('Importar usuarios', $guard) || $super)
                            <a class="nav-link" href="{{ route('usuarios.importar.ver') }}">Importar usuarios</a>
                        @endif
                    </nav>
                </div>
            @endif

            @if (Auth()->user()->validar('Perfiles', $guard) ||
                    $super)
                <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                    data-bs-target="#pagesCollapseError" aria-expanded="false" aria-controls="pagesCollapseError">
                    <div class="nav-link-icon"><i class="fa fa-key" aria-hidden="true"></i></div>
                    Administración de accesos
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="pagesCollapseError" data-bs-parent="#accordionSidenavPagesMenu">
                    <nav class="sidenav-menu-nested nav">
                        {{-- <a class="nav-link" href="{{route('usuario.index')}}">Usuarios</a>                             --}}
                        @if ($super)
                            <a class="nav-link" href="{{ route('permisos.index') }}">Permisos</a>
                        @endif
                        @if (Auth()->user()->validar('Perfiles', $guard) || $super)
                            <a class="nav-link" href="{{ route('roles.index') }}">Perfiles</a>
                        @endif
                    </nav>
                </div>
            @endif

            @if (Auth()->user()->validar('Listado de reconocimientos', $guard) ||
                    Auth()->user()->validar('Reconocimientos realizados', $guard) ||
                    Auth()->user()->validar('Reconocimientos recibidos', $guard) ||
                    Auth()->user()->validar('Asignar insignias', $guard) ||
                    $super)
                <div class="sidenav-menu-heading">USUARIOS</div>
                {{-- Sidenav Link (Charts) --}}
                @if (Auth()->user()->validar('Listado de reconocimientos', $guard) || $super)
                    <a class="nav-link" href="{{ route('reconocimientos.realizados', 'periodo') }}">
                        <div class="nav-link-icon"><i data-feather="bar-chart"></i></div>
                        Listado de reconocimientos
                    </a>
                @endif
                @if (Auth()->user()->validar('Asignar insignias', $guard) || $super)
                    <a class="nav-link" href="{{ route('respuesta') }}">
                        <div class="nav-link-icon"><i data-feather="bar-chart"></i></div>
                        Emitir Reconocimiento
                    </a>
                @endif
                @if (Auth()->user()->validar('Reconocimientos realizados', $guard) || $super)
                    <a class="nav-link" href="{{ route('reconocimientos.realizados', 'user') }}">
                        <div class="nav-link-icon"><i class="fa-solid fa-medal"></i></div>Reconocimientos
                        realizados
                    </a>
                @endif
                @if (Auth()->user()->validar('Reconocimientos recibidos', $guard) || $super)
                    <a class="nav-link" href="{{ route('reconocimientos.recibidos', 'user') }}">
                        <div class="nav-link-icon"><i class="fa-solid fa-medal"></i></div>Reconocimientos
                        recibidos
                    </a>
                @endif
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
