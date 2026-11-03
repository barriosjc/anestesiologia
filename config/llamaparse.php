<?php

return [

    /*
    |--------------------------------------------------------------------------
    | LlamaParse (PDF -> XLSX -> JSON)
    |--------------------------------------------------------------------------
    |
    | Flujo alternativo al OCR local: el PDF se envia a la API de LlamaParse,
    | se descarga el XLSX de tablas y se procesa con el mismo XlsxJsonService.
    | Solo necesita salida HTTP, por lo que funciona en hosting compartido.
    |
    */

    'api_key' => env('LLAMAPARSE_API_KEY'),

    'base_url' => rtrim((string) env('LLAMAPARSE_BASE_URL', 'https://api.cloud.llamaindex.ai/api/v2'), '/'),

    'tier' => env('LLAMAPARSE_TIER', 'agentic'),

    'version' => env('LLAMAPARSE_VERSION', 'latest'),

    'max_file_mb' => (int) env('LLAMAPARSE_MAX_FILE_MB', 100),

    'disk' => env('LLAMAPARSE_DISK', 'local'),

    'ruta' => env('LLAMAPARSE_RUTA', 'llamaparse'),

    'timeout_subida' => (int) env('LLAMAPARSE_TIMEOUT_SUBIDA', 180),

    'timeout_consulta' => (int) env('LLAMAPARSE_TIMEOUT_CONSULTA', 30),

    'timeout_descarga' => (int) env('LLAMAPARSE_TIMEOUT_DESCARGA', 60),

    'verify_ssl' => filter_var(env('LLAMAPARSE_VERIFY_SSL', true), FILTER_VALIDATE_BOOLEAN),

    'idiomas' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('LLAMAPARSE_IDIOMAS', 'es,en'))
    ))),

    'tables_as_spreadsheet' => filter_var(
        env('LLAMAPARSE_TABLES_AS_SPREADSHEET', true),
        FILTER_VALIDATE_BOOLEAN
    ),

    'atenciones_esperadas' => ($esperadas = env('LLAMAPARSE_ATENCIONES_ESPERADAS')) !== null && $esperadas !== ''
        ? (int) $esperadas
        : null,

    'corregir_17_a_11' => filter_var(
        env('LLAMAPARSE_CORREGIR_17_A_11', true),
        FILTER_VALIDATE_BOOLEAN
    ),

];
