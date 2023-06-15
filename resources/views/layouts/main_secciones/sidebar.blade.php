<nav class="sidenav shadow-right sidenav-light">
    <div class="sidenav-menu">
        <div class="nav accordion" id="accordionSidenav">
            {{-- Sidenav Menu Heading (Account) --}}
            {{-- * * Note: * * Visible only on and above the sm breakpoint --}}
            <div class="sidenav-menu-heading d-sm-none">Account</div>
            {{-- Sidenav Link (Alerts) --}}
            {{-- * * Note: * * Visible only on and above the sm breakpoint --}}
            <a class="nav-link d-sm-none" href="#!">
                <div class="nav-link-icon"><i data-feather="bell"></i></div>
                Alerts
                <span class="badge bg-warning-soft text-warning ms-auto">4 New!</span>
            </a>
            {{-- Sidenav Link (Messages) --}}
            {{-- * * Note: * * Visible only on and above the sm breakpoint --}}
            <a class="nav-link d-sm-none" href="#!">
                <div class="nav-link-icon"><i data-feather="mail"></i></div>
                Messages
                <span class="badge bg-success-soft text-success ms-auto">2 New!</span>
            </a>
            {{-- Sidenav Heading (Addons) --}}
            <div class="sidenav-menu-heading">Encuestas</div>
            {{-- Sidenav Link (Empresa) --}}
            @role('super-admin')
            <a class="nav-link" href="{{ route('empresa.select') }}">
                <div class="nav-link-icon"><i data-feather="bar-chart"></i></div>
                Seleccionar empresa
            </a>
            @endrole
            {{-- Sidenav Link (Charts) --}}
            <a class="nav-link" href="{{ route('respuesta') }}">
                <div class="nav-link-icon"><i data-feather="bar-chart"></i></div>
                Responder o cargar
            </a>
            {{-- Sidenav Accordion (Reconocimientos) --}}
            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                data-bs-target="#collapseReconocimientos" aria-expanded="false" aria-controls="collapseReconocimientos">
                <div class="nav-link-icon"><i data-feather="award"></i></div>
                Mis reconocimientos
                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
            </a>
            <div class="collapse" id="collapseReconocimientos" data-bs-parent="#accordionSidenav">
                <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPages">
                    <a class="nav-link" href="{{ route('reconocimientos.realizados', 'user') }}">Reconocimientos realizados</a>
                    <a class="nav-link" href="{{ route('reconocimientos.recibidos', 'user') }}">Reconocimientos recibidos</a>
                </nav>
            </div>
            
            {{-- Sidenav Accordion (Reconocimientos) --}}
            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                data-bs-target="#collapseDashboards" aria-expanded="false" aria-controls="collapseDashboards">
                <div class="nav-link-icon"><i data-feather="award"></i></div>
                Informes (rrhh)
                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
            </a>
            <div class="collapse" id="collapseDashboards" data-bs-parent="#DasboardSidenav">
                <nav class="sidenav-menu-nested nav accordion" id="DasboardSidenavPages">
                    <a class="nav-link" href="{{ route('reconocimientos.realizados', 'todos') }}">Reconocimientos</a>
                </nav>
            </div>

            {{-- Sidenav Menu Heading (Core) lo muestra si tiene permiso para crear encuestas --}}
            @role('super-admin')
                <div class="sidenav-menu-heading">Core</div>
                <a class="nav-link" href="{{ route('empresas.index') }}">
                    <div class="nav-link-icon"><i data-feather="home"></i></div>
                    ABM de empresas
                </a>
            @endrole
            @role('super-admin')
            <a class="nav-link" href="{{ route('reconocimientos.index') }}">
                <div class="nav-link-icon"><i data-feather="home"></i></div>
                ABM reconocimientos
            </a>
        @endrole
            @can('creacion de encuestas')
                {{-- Sidenav Accordion (Encuestas) --}}
                <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                    data-bs-target="#collapseDashboards" aria-expanded="false" aria-controls="collapseDashboards">
                    <div class="nav-link-icon"><i data-feather="activity"></i></div>
                    Administración de encuestas
                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseDashboards" data-bs-parent="#accordionSidenav">
                    <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPages">
                        <a class="nav-link" href="{{ route('opcion.index') }}">Opciones</a>
                        <a class="nav-link" href="{{ route('encuesta.create') }}">Crear encuesta</a>
                    </nav>
                </div>
            @endcan
            {{-- Sidenav Heading (Custom) --}}
            <div class="sidenav-menu-heading">Custom</div>
            {{-- Sidenav Accordion (Pages) --}}
            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                data-bs-target="#collapsePages" aria-expanded="false" aria-controls="collapsePages">
                <div class="nav-link-icon"><i data-feather="grid"></i></div>
                Seguridad
                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
            </a>
            <div class="collapse" id="collapsePages" data-bs-parent="#accordionSidenav">
                <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPagesMenu">
                    {{-- Nested Sidenav Accordion (Pages -> Account) --}}
                    @can('abm usuarios')
                        <a class="nav-link" href="{{ route('usuario.index') }}">ABM Usuarios</a>
                    @endcan
                    <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                        data-bs-target="#pagesCollapseAccount" aria-expanded="false"
                        aria-controls="pagesCollapseAccount">
                        Mis datos
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="pagesCollapseAccount" data-bs-parent="#accordionSidenavPagesMenu">
                        <nav class="sidenav-menu-nested nav">
                            <a class="nav-link" href="{{ route('profile', ['id' => Auth()->user()->id]) }}">Mi
                                perfil</a>
                            <a class="nav-link"
                                href="{{ route('profile.password', ['id' => Auth()->user()->id]) }}">Cambio de
                                clave</a>
                            {{-- @can('crear nuevos usuarios')
                            <a class="nav-link" href="{{ route('profile.nuevo') }}">Nuevo usuario</a>
                            @endcan --}}
                        </nav>
                    </div>
                    {{-- Nested Sidenav Accordion (Pages -> Authentication) --}}
                    {{-- <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#pagesCollapseAuth" aria-expanded="false" aria-controls="pagesCollapseAuth">
                                    Authentication
                                    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a> --}}
                    {{-- <div class="collapse" id="pagesCollapseAuth" data-bs-parent="#accordionSidenavPagesMenu">
                        <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPagesAuth">
                            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                                data-bs-target="#pagesCollapseAuthBasic" aria-expanded="false"
                                aria-controls="pagesCollapseAuthBasic">
                                Basic
                                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="pagesCollapseAuthBasic"
                                data-bs-parent="#accordionSidenavPagesAuth">
                                <nav class="sidenav-menu-nested nav">
                                    <a class="nav-link" href="auth-login-basic.html">Login</a>
                                    <a class="nav-link" href="auth-register-basic.html">Register</a>
                                    <a class="nav-link" href="auth-password-basic.html">Forgot Password</a>
                                </nav>
                            </div>
                            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                                data-bs-target="#pagesCollapseAuthSocial" aria-expanded="false"
                                aria-controls="pagesCollapseAuthSocial">
                                Social
                                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="pagesCollapseAuthSocial"
                                data-bs-parent="#accordionSidenavPagesAuth">
                                <nav class="sidenav-menu-nested nav">
                                    <a class="nav-link" href="auth-login-social.html">Login</a>
                                    <a class="nav-link" href="auth-register-social.html">Register</a>
                                    <a class="nav-link" href="auth-password-social.html">Forgot Password</a>
                                </nav>
                            </div>
                        </nav>
                    </div> --}}
                    {{-- Nested Sidenav Accordion (Pages -> Error) --}}

                    @can('seguridad')
                        <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                            data-bs-target="#pagesCollapseError" aria-expanded="false"
                            aria-controls="pagesCollapseError">
                            Administración de accesos
                            <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="pagesCollapseError" data-bs-parent="#accordionSidenavPagesMenu">
                            <nav class="sidenav-menu-nested nav">
                                {{-- <a class="nav-link" href="{{route('usuario.index')}}">Usuarios</a>                             --}}
                                <a class="nav-link" href="{{ route('permisos.index') }}">Permisos</a>
                                <a class="nav-link" href="{{ route('roles.index') }}">Grupos</a>
                            </nav>
                        </div>
                    @endcan
                    {{-- <a class="nav-link" href="pricing.html">Pricing</a>
                                <a class="nav-link" href="invoice.html">Invoice</a> --}}
                </nav>
            </div>
            {{-- Sidenav Accordion (Applications) --}}
            {{-- <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                data-bs-target="#collapseApps" aria-expanded="false" aria-controls="collapseApps">
                <div class="nav-link-icon"><i data-feather="globe"></i></div>
                Applications
                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
            </a>
            <div class="collapse" id="collapseApps" data-bs-parent="#accordionSidenav">
                <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavAppsMenu">
                    <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                        data-bs-target="#appsCollapseKnowledgeBase" aria-expanded="false"
                        aria-controls="appsCollapseKnowledgeBase">
                        Knowledge Base
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="appsCollapseKnowledgeBase" data-bs-parent="#accordionSidenavAppsMenu">
                        <nav class="sidenav-menu-nested nav">
                            <a class="nav-link" href="knowledge-base-home-1.html">Home 1</a>
                            <a class="nav-link" href="knowledge-base-home-2.html">Home 2</a>
                            <a class="nav-link" href="knowledge-base-category.html">Category</a>
                            <a class="nav-link" href="knowledge-base-article.html">Article</a>
                        </nav>
                    </div>
                    <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                        data-bs-target="#appsCollapseUserManagement" aria-expanded="false"
                        aria-controls="appsCollapseUserManagement">
                        User Management
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="appsCollapseUserManagement" data-bs-parent="#accordionSidenavAppsMenu">
                        <nav class="sidenav-menu-nested nav">
                            <a class="nav-link" href="user-management-list.html">Users List</a>
                            <a class="nav-link" href="user-management-edit-user.html">Edit User</a>
                            <a class="nav-link" href="user-management-add-user.html">Add User</a>
                            <a class="nav-link" href="user-management-groups-list.html">Groups List</a>
                            <a class="nav-link" href="user-management-org-details.html">Organization Details</a>
                        </nav>
                    </div>
                    <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                        data-bs-target="#appsCollapsePostsManagement" aria-expanded="false"
                        aria-controls="appsCollapsePostsManagement">
                        Posts Management
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="appsCollapsePostsManagement"
                        data-bs-parent="#accordionSidenavAppsMenu">
                        <nav class="sidenav-menu-nested nav">
                            <a class="nav-link" href="blog-management-posts-list.html">Posts List</a>
                            <a class="nav-link" href="blog-management-create-post.html">Create Post</a>
                            <a class="nav-link" href="blog-management-edit-post.html">Edit Post</a>
                            <a class="nav-link" href="blog-management-posts-admin.html">Posts Admin</a>
                        </nav>
                    </div>
                </nav>
            </div>
            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                data-bs-target="#collapseFlows" aria-expanded="false" aria-controls="collapseFlows">
                <div class="nav-link-icon"><i data-feather="repeat"></i></div>
                Flows
                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
            </a>
            <div class="collapse" id="collapseFlows" data-bs-parent="#accordionSidenav">
                <nav class="sidenav-menu-nested nav">
                    <a class="nav-link" href="multi-tenant-select.html">Multi-Tenant Registration</a>
                    <a class="nav-link" href="wizard.html">Wizard</a>
                </nav>
            </div>
            <div class="sidenav-menu-heading">UI Toolkit</div>
            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                <div class="nav-link-icon"><i data-feather="layout"></i></div>
                Layout
                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
            </a>
            <div class="collapse" id="collapseLayouts" data-bs-parent="#accordionSidenav">
                <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavLayout">
                    <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                        data-bs-target="#collapseLayoutSidenavVariations" aria-expanded="false"
                        aria-controls="collapseLayoutSidenavVariations">
                        Navigation
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="collapseLayoutSidenavVariations"
                        data-bs-parent="#accordionSidenavLayout">
                        <nav class="sidenav-menu-nested nav">
                            <a class="nav-link" href="layout-static.html">Static Sidenav</a>
                            <a class="nav-link" href="layout-dark.html">Dark Sidenav</a>
                            <a class="nav-link" href="layout-rtl.html">RTL Layout</a>
                        </nav>
                    </div>
                    <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                        data-bs-target="#collapseLayoutContainers" aria-expanded="false"
                        aria-controls="collapseLayoutContainers">
                        Container Options
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="collapseLayoutContainers" data-bs-parent="#accordionSidenavLayout">
                        <nav class="sidenav-menu-nested nav">
                            <a class="nav-link" href="layout-boxed.html">Boxed Layout</a>
                            <a class="nav-link" href="layout-fluid.html">Fluid Layout</a>
                        </nav>
                    </div>
                    <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                        data-bs-target="#collapseLayoutsPageHeaders" aria-expanded="false"
                        aria-controls="collapseLayoutsPageHeaders">
                        Page Headers
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="collapseLayoutsPageHeaders" data-bs-parent="#accordionSidenavLayout">
                        <nav class="sidenav-menu-nested nav">
                            <a class="nav-link" href="header-simplified.html">Simplified</a>
                            <a class="nav-link" href="header-compact.html">Compact</a>
                            <a class="nav-link" href="header-overlap.html">Content Overlap</a>
                            <a class="nav-link" href="header-breadcrumbs.html">Breadcrumbs</a>
                            <a class="nav-link" href="header-light.html">Light</a>
                        </nav>
                    </div>
                    <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                        data-bs-target="#collapseLayoutsStarterTemplates" aria-expanded="false"
                        aria-controls="collapseLayoutsStarterTemplates">
                        Starter Layouts
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="collapseLayoutsStarterTemplates"
                        data-bs-parent="#accordionSidenavLayout">
                        <nav class="sidenav-menu-nested nav">
                            <a class="nav-link" href="starter-default.html">Default</a>
                            <a class="nav-link" href="starter-minimal.html">Minimal</a>
                        </nav>
                    </div>
                </nav>
            </div>
            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                data-bs-target="#collapseComponents" aria-expanded="false" aria-controls="collapseComponents">
                <div class="nav-link-icon"><i data-feather="package"></i></div>
                Components
                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
            </a>
            <div class="collapse" id="collapseComponents" data-bs-parent="#accordionSidenav">
                <nav class="sidenav-menu-nested nav">
                    <a class="nav-link" href="alerts.html">Alerts</a>
                    <a class="nav-link" href="avatars.html">Avatars</a>
                    <a class="nav-link" href="badges.html">Badges</a>
                    <a class="nav-link" href="buttons.html">Buttons</a>
                    <a class="nav-link" href="cards.html">
                        Cards
                        <span class="badge bg-primary-soft text-primary ms-auto">Updated</span>
                    </a>
                    <a class="nav-link" href="dropdowns.html">Dropdowns</a>
                    <a class="nav-link" href="forms.html">
                        Forms
                        <span class="badge bg-primary-soft text-primary ms-auto">Updated</span>
                    </a>
                    <a class="nav-link" href="modals.html">Modals</a>
                    <a class="nav-link" href="navigation.html">Navigation</a>
                    <a class="nav-link" href="progress.html">Progress</a>
                    <a class="nav-link" href="step.html">Step</a>
                    <a class="nav-link" href="timeline.html">Timeline</a>
                    <a class="nav-link" href="toasts.html">Toasts</a>
                    <a class="nav-link" href="tooltips.html">Tooltips</a>
                </nav>
            </div>
            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                data-bs-target="#collapseUtilities" aria-expanded="false" aria-controls="collapseUtilities">
                <div class="nav-link-icon"><i data-feather="tool"></i></div>
                Utilities
                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
            </a>
            <div class="collapse" id="collapseUtilities" data-bs-parent="#accordionSidenav">
                <nav class="sidenav-menu-nested nav">
                    <a class="nav-link" href="animations.html">Animations</a>
                    <a class="nav-link" href="background.html">Background</a>
                    <a class="nav-link" href="borders.html">Borders</a>
                    <a class="nav-link" href="lift.html">Lift</a>
                    <a class="nav-link" href="shadows.html">Shadows</a>
                    <a class="nav-link" href="typography.html">Typography</a>
                </nav>
            </div>
        </div>
    </div> --}}
            {{-- Sidenav Footer --}}
            <div class="sidenav-footer">
                <div class="sidenav-footer-content">
                    <div class="sidenav-footer-subtitle">Usuario :</div>
                    <div class="sidenav-footer-title">{{ auth()->user()->name }}</div>
                </div>
            </div>
</nav>
