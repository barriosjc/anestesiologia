@if (Auth()->user()->hasRole('super-admin'))
    @php($super = true)
@else
    @php($super = false)
@endif

<nav class="sidenav shadow-right sidenav-light">
    <div class="sidenav-menu">
        <div class="nav accordion" id="accordionSidenav">
            <div class="sidenav-menu-heading">AUDITORIA</div>
                @if (Auth()->user()->hasPermissionTo('adm_consumos', 'web') || $super)
                    <a class="nav-link" href="{{ route('consumos.partes.filtrar') }}">
                        <div class="nav-link-icon"><i class="fa-regular fa-eye"></i></div>
                        Auditoria carga
                    </a>
                @endif
                @role('super-admin')
                <a class="nav-link" href="{{ route('consumo.rendiciones.filtrar') }}">
                    <div class="nav-link-icon"><i class="fa-solid fa-circle-dollar-to-slot"></i></div>
                    Generar Rendiciones
                </a>
                <a class="nav-link" href="{{ route('consumo.rendiciones.listado') }}">
                    <div class="nav-link-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
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
                        @if (Auth()->user()->hasPermissionTo('adm_consumos', 'web') || $super)
                            <a class="nav-link" href="{{ route('profesionales.index') }}">Médicos</a>
                        @endif
                        @if (Auth()->user()->hasPermissionTo('adm_consumos', 'web') || $super)
                            <a class="nav-link" href="{{ route('centros.index') }}">Centros</a>
                        @endif
                        @if (Auth()->user()->hasPermissionTo('adm_consumos', 'web') || $super)
                            <a class="nav-link" href="{{ route('coberturas.index') }}">Coberturas</a>
                        @endif
                        @if (Auth()->user()->hasPermissionTo('adm_consumos', 'web') || $super)
                            <a class="nav-link" href="{{ route('coberturas.index') }}">Nomenclador</a>
                        @endif
                        <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                            data-bs-target="#pagesCollapsePrecios" aria-expanded="false"
                            aria-controls="pagesCollapsePrecios">
                            Listas de precios
                            <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="pagesCollapsePrecios" data-bs-parent="#accordionSidenavPagesMenu">
                            <nav class="sidenav-menu-nested nav">
                                @if (Auth()->user()->hasPermissionTo('adm_consumos', 'web') || $super)
                                    <a class="nav-link" href="{{ route('nomenclador.listas.listas') }}">Listas</a>
                                @endif
                                @if (Auth()->user()->hasPermissionTo('adm_consumos', 'web') || $super)
                                    <a class="nav-link" href="{{ route('nomenclador.valores.listas') }}">Precios</a>
                                @endif
                            </nav>
                        </div>
                </div>
            @endrole

            @if (Auth()->user()->hasPermissionTo('adm_partes', 'web') || $super)
                <div class="sidenav-menu-heading">ADMINISTRATIVOS </div>
                {{-- Sidenav Accordion (Votaciones) --}}
                <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                    data-bs-target="#collapseDashboards" aria-expanded="false" aria-controls="collapseDashboards">
                    <div class="nav-link-icon"><i data-feather="activity"></i></div>
                    Carga de Partes
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseDashboards" data-bs-parent="#accordionSidenav">
                    <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPages">
                        @if (Auth()->user()->hasPermissionTo('adm_partes', 'web') || $super)
                            <a class="nav-link" href="{{ route('partes_cab.create') }}">Nuevo</a>
                        @endif
                        @if (Auth()->user()->hasPermissionTo('adm_partes', 'web') || $super)
                            <a class="nav-link" href="{{ route('partes_cab.filtrar') }}">Listados</a>
                        @endif
                    </nav>
                </div>
            @endif

            @role('super-admin')
                <div class="sidenav-menu-heading">SEGURIDAD</div>
                <a class="nav-link" href="{{ route('usuario.index') }}">ABM Usuarios</a>
                <a class="nav-link" href="{{ route('permisos.index') }}">Permisos</a>
                <a class="nav-link" href="{{ route('roles.index') }}">Perfiles</a>
            @endrole
            </div>
        </div>

        {{-- Sidenav Footer --}}
        <div class="sidenav-footer">
            <div class="sidenav-footer-content">
                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i data-feather="log-out"></i>
                    {{ __('Logout') }}
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
                <div class="sidenav-footer-subtitle">Usuario :<strong> {{ auth()->user()->name }}</strong>
                    {{-- <div class="fw-bold">{{ auth()->user()->name }}</div> --}}
                </div>
            </div>
        </div>
    </nav>
