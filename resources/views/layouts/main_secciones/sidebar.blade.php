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

            @can('creacion de encuestas')
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
                        <a class="nav-link" href="{{ route('opcion.index') }}">Opciones</a>
                        <a class="nav-link" href="{{ route('encuesta.create') }}">Crear encuesta</a>
                    </nav>
                </div>
            @endcan
            {{-- Sidenav Accordion (Reconocimientos) --}}
            @can('informes')
                <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                    data-bs-target="#collapseInformes" aria-expanded="false" aria-controls="collapseInformes">
                    <div class="nav-link-icon"><i data-feather="award"></i></div>
                    Informes (rrhh)
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseInformes" data-bs-parent="#DasboardSidenav">
                    <nav class="sidenav-menu-nested nav accordion" id="DasboardSidenavPages">
                        <a class="nav-link" href="{{ route('reconocimientos.realizados', 'todos') }}">Reconocimientos</a>
                        <a class="nav-link" href="{{ route('reconocimientos.index') }}">Logros</a>
                        <a class="nav-link" href="{{ route('dashboard.show') }}">Dashboard</a>
                    </nav>
                </div>
            @endcan
            @can('abm usuarios')
                <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                    data-bs-target="#collapseUsuarios" aria-expanded="false" aria-controls="collapseUsuarios">
                    <div class="nav-link-icon"><i class="fa fa-user" aria-hidden="true"></i></div>
                    Usuarios
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseUsuarios" data-bs-parent="#DasboardSidenav">
                    <nav class="sidenav-menu-nested nav accordion" id="UsuariosSidenavPages">
                        <a class="nav-link" href="{{ route('usuario.index') }}">ABM Usuarios</a>
                        <a class="nav-link" href="{{ route('usuarios.importar.ver') }}">Importar usuarios</a>
                    </nav>
                </div>
            @endcan

            @can('seguridad')
                <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                    data-bs-target="#pagesCollapseError" aria-expanded="false" aria-controls="pagesCollapseError">
                    <div class="nav-link-icon"><i class="fa fa-key" aria-hidden="true"></i></div>
                    Administración de accesos
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="pagesCollapseError" data-bs-parent="#accordionSidenavPagesMenu">
                    <nav class="sidenav-menu-nested nav">
                        {{-- <a class="nav-link" href="{{route('usuario.index')}}">Usuarios</a>                             --}}
                        <a class="nav-link" href="{{ route('permisos.index') }}">Permisos</a>
                        <a class="nav-link" href="{{ route('roles.index') }}">Perfiles</a>
                    </nav>
                </div>
            @endcan

            @can('reconocimientos personal')
                <div class="sidenav-menu-heading">USUARIOS</div>
                {{-- Sidenav Link (Charts) --}}
                <a class="nav-link" href="{{ route('respuesta') }}">
                    <div class="nav-link-icon"><i data-feather="bar-chart"></i></div>
                    Emitir Reconocimiento
                </a>
                <a class="nav-link" href="{{ route('reconocimientos.realizados', 'user') }}">
                    <div class="nav-link-icon"><i class="fa-solid fa-medal"></i></div>Reconocimientos
                    realizados</a>
                <a class="nav-link" href="{{ route('reconocimientos.recibidos', 'user') }}">
                    <div class="nav-link-icon"><i class="fa-solid fa-medal"></i></div>Reconocimientos
                    recibidos</a>
            @endcan
        </div>
    </div>

    <i class="fa-solid fa-medal"></i>
    {{-- Sidenav Footer --}}
    <div class="sidenav-footer">
        <div class="sidenav-footer-content">
            <div class="sidenav-footer-subtitle">Usuario :</div>
            <div class="sidenav-footer-title">{{ auth()->user()->name }}</div>
        </div>
    </div>
</nav>
