
    {{-- este navbar cambiarlo por lo que quieran poner, o indicar donde ponemos los titulos --}}
    <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
        <div class="container-fluid px-4">
            <div class="page-header-content">
                <div class="row align-items-center justify-content-between pt-3">
                    <div class="col-auto mb-3">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="file-plus"></i></div>
                            @yield('titulo')
                        </h1>
                    </div>
                    @include('utiles.alerts')
                    {{-- <div class="col-12 col-xl-auto mb-3">
                        <a class="btn btn-sm btn-light text-primary" href="blog-management-posts-list.html">
                            <i class="me-1" data-feather="arrow-left"></i>
                            Back to All Posts
                        </a>
                    </div> --}}
                </div>
            </div>
        </div>
    </header>

    @yield('contenido')


{{-- </div> --}}
