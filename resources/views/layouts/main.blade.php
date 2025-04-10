<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>{{ config('app.name', 'SAADA') }}</title>
    <link rel="icon" type="image/x-icon" href="{{asset('favicon.ico')}}" />
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- FontAwesome -->
    <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"
        crossorigin="anonymous"></script>

    <!-- Feather Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js" crossorigin="anonymous"></script>

    <!-- bootstraps -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> 

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />

    <!-- Include base CSS (optional) -->
    <link href="{{ asset('libs/sbadmin/css/styles.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet" />
</head>

<body class="nav-fixed">
    @include('layouts.main_secciones.header')

    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            @include('layouts.main_secciones.sidebar')
        </div>
        <div id="layoutSidenav_content" class="mt-3">
            @include('utiles.alerts')
            @include('layouts.main_secciones.contenido')

            @include('layouts.main_secciones.footer')
        </div>
    </div>

    <!-- Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>

    <!-- Bootstrap -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    
    <!-- SB Admin Scripts -->
    <script src="{{ asset('libs/sbadmin/js/scripts.js') }}"></script>
    {{-- <script src="{{ asset('libs/sbadmin/js/datatables/datatables-simple-demo.js') }}"></script>  --}}

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <!-- Simple DataTables -->
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.js" crossorigin="anonymous"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>


    <!-- Custom Util Scripts -->
    <script src="{{ asset('js/util.js') }}"></script>
    
    @stack('scripts')
</body>
