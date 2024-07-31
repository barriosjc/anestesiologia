@php($guard = 'web')

@if (Auth()->user()->hasRole('super-admin'))
    @php($super = true)
@else
    @php($super = false)
@endif

<nav class="sidenav shadow-right sidenav-light">
    <div class="sidenav-menu">
        <div class="nav accordion" id="accordionSidenav">
            @role('super-admin')
                <div class="sidenav-menu-heading">AUDITORIA</div>
                <a class="nav-link" href="{{ route('consumos.partes.filtrar') }}">
                    <div class="nav-link-icon"><i data-feather="home"></i></div>
                    Auditoria carga
                </a>
                <a class="nav-link" href="{{ route('consumo.rendiciones.filtrar') }}">
                    <div class="nav-link-icon"><i data-feather="home"></i></div>
                    Generar Rendiciones
                </a>
                <a class="nav-link" href="{{ route('usuario.index') }}">
                    <div class="nav-link-icon"><i data-feather="home"></i></div>
                    Listados
                </a>
                <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                    data-bs-target="#pagesCollapseError" aria-expanded="false" aria-controls="pagesCollapseError">
                    <div class="nav-link-icon"><i class="fa fa-key" aria-hidden="true"></i></div>
                    Administración
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="pagesCollapseError" data-bs-parent="#accordionSidenavPagesMenu">
                    <nav class="sidenav-menu-nested nav">
                        @if (Auth()->user()->hasPermissionTo('example', $guard) || $super)
                            <a class="nav-link" href="{{ route('profesionales.index') }}">Médicos</a>
                        @endif
                        @if (Auth()->user()->hasPermissionTo('example', $guard) || $super)
                            <a class="nav-link" href="{{ route('usuario.index') }}">Lugares prestación</a>
                        @endif
                        @if (Auth()->user()->hasPermissionTo('example', $guard) || $super)
                            <a class="nav-link" href="{{ route('usuario.index') }}">Gerenciadoras</a>
                        @endif
                        @if (Auth()->user()->hasPermissionTo('example', $guard) || $super)
                            <a class="nav-link" href="{{ route('usuario.index') }}">Nomenclador</a>
                        @endif
                        @if (Auth()->user()->hasPermissionTo('example', $guard) || $super)
                            <a class="nav-link" href="{{ route('nomenclador.valores.listado') }}">Valorización</a>
                        @endif
                    </nav>
                </div>
            @endrole

            @if (Auth()->user()->hasPermissionTo('example', $guard) ||
                    Auth()->user()->hasPermissionTo('example', $guard) ||
                    $super)
                <div class="sidenav-menu-heading">Usuarios  </div>
                {{-- Sidenav Accordion (Votaciones) --}}
                <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                    data-bs-target="#collapseDashboards" aria-expanded="false" aria-controls="collapseDashboards">
                    <div class="nav-link-icon"><i data-feather="activity"></i></div>
                    Renciones
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseDashboards" data-bs-parent="#accordionSidenav">
                    <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPages">
                        @if (Auth()->user()->hasPermissionTo('example', $guard) || $super)
                            <a class="nav-link" href="{{ route('partes_cab.create') }}">Nueva</a>
                        @endif
                        @if (Auth()->user()->hasPermissionTo('example', $guard) || $super)
                            <a class="nav-link" href="{{ route('partes_cab.index') }}">Listados</a>
                        @endif
                    </nav>
                </div>
            @endif

            @if (Auth()->user()->hasPermissionTo('example', $guard) || $super)
                <div class="sidenav-menu-heading">SEGURIDAD</div>
                @if (Auth()->user()->hasPermissionTo('example', $guard) || $super)
                    <a class="nav-link" href="{{ route('usuario.index') }}">ABM Usuarios</a>
                @endif
                {{-- <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                    data-bs-target="#pagesCollapseError" aria-expanded="false" aria-controls="pagesCollapseError">
                    <div class="nav-link-icon"><i class="fa fa-key" aria-hidden="true"></i></div>
                    Administración de accesos
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a> --}}
                {{-- <div class="collapse" id="pagesCollapseError" data-bs-parent="#accordionSidenavPagesMenu">
                    <nav class="sidenav-menu-nested nav"> --}}
                        @if ($super)
                            <a class="nav-link" href="{{ route('usuario.index') }}">Permisos</a>
                        @endif
                        @if (Auth()->user()->hasPermissionTo('example', $guard) || $super)
                            <a class="nav-link" href="{{ route('usuario.index') }}">Perfiles</a>
                        @endif
                    {{-- </nav> --}}
                {{-- </div> --}}
            @endif

        </div>
    </div>

    {{-- Sidenav Footer --}}
    <div class="sidenav-footer">
        <div class="sidenav-footer-content">
            <div class="sidenav-footer-subtitle">Usuario :<strong> {{ auth()->user()->name }}</strong>
            {{-- <div class="fw-bold">{{ auth()->user()->name }}</div> --}}
        </div>
        </div>
    </div>
</nav>
