<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>403 Error - SB Admin Pro</title>
    <link href="{{ asset('/css/custom.css') }}" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="{{ asset('img/favicon.jpg') }}" />
    <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js" crossorigin="anonymous"></script>
    <!-- Agregar Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <style>
        /* Estilos personalizados */
        html, body {
            height: 100%; /* Asegura que el body ocupe toda la altura */
            margin: 0;
        }
        #layoutError {
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Ocupa toda la altura de la ventana */
        }
        #layoutError_content {
            flex: 1; /* Hace que el contenido principal crezca para empujar el footer abajo */
        }
        .imagen-responsiva {
            max-width: 100%; /* Responsiva */
            height: auto;
            margin: 0 auto; /* Centrado horizontal */
            display: block; /* Necesario para que margin funcione */
        }
        #layoutError_footer {
            background-color: #f8f9fa; /* Color claro para el footer */
        }
    </style>
</head>
<body class="bg-white">
    <div id="layoutError">
        <div id="layoutError_content">
            <main>
                <div class="container-xl px-4 mt-5"> <!-- Margen superior para centrar verticalmente -->
                    <div class="row justify-content-center">
                        <div class="col-lg-6">
                            <div class="text-center mt-4">
                                <img class="img-fluid p-4 imagen-responsiva" src="{{ asset('libs/sbadmin/assets/img/illustrations/403-error-forbidden.svg') }}" alt="403 Error" />
                                <p class="lead">No tiene permiso de acceso a este recurso en el estado actual.</p>
                                <a class="text-arrow-icon" href="{{ route('main') }}">
                                    <i class="ms-0 me-1" data-feather="arrow-left"></i>
                                    Vuelve al planillero
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <div id="layoutError_footer">
            <footer class="footer-admin mt-auto footer-light">
                <div class="container-xl px-4">
                    <div class="row">
                        <div class="col-md-6 small">Copyright © MediNexus 2025</div>
                        <div class="col-md-6 text-md-end small">
                            <a href="#!">Privacy Policy</a>
                            ·
                            <a href="#!">Terms & Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script>
        // Inicializar Feather Icons
        feather.replace();
    </script>
</body>
</html>