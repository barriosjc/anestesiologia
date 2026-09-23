<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>{{ config('app.name', 'MediNexus') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logomedinexustransparente.png') }}" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" rel="stylesheet" crossorigin="anonymous" />
    @livewireStyles
    <style>
        body.bg-primary {
            background: linear-gradient(135deg, #1782e0, #2ab8e8) !important;
            min-height: 100vh;
        }
        .card {
            border: 0;
            box-shadow: 0 0.5rem 1.5rem rgba(23, 130, 224, 0.15);
        }
        .card .btn-primary {
            background-color: #1782e0;
            border-color: #1782e0;
        }
        .card .btn-primary:hover {
            background-color: #0d66c4;
            border-color: #0d66c4;
        }
        .card .form-check-input:checked {
            background-color: #1782e0;
            border-color: #1782e0;
        }
    </style>
</head>
<body class="bg-primary">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container-xl px-4">
                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    @livewireScripts
</body>
</html>
