<?php

namespace App\Services;

use App\Models\OrdenPagoAuditCab;
use App\Models\Profesional;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Importa ordenes de pago auditadas desde un archivo (CSV / XLSX / XLS).
 *
 * El archivo debe tener una fila de encabezado con los nombres de columna:
 *   nro_op, profesional_id, fecha, op_total, op_retenciones
 *
 * op_retenciones es la retencion de Ingresos Brutos (IB): tambien se acepta
 * una columna llamada "ib" o "ingresos_brutos".
 *
 * La columna profesional_id admite:
 *   - el id numerico del profesional, o
 *   - el CUIT del profesional, o
 *   - el nombre (coincidencia exacta, sin distinguir mayusculas).
 * Si el profesional no existe, la fila se importa igual con profesional_id = null.
 */
class OrdenPagoAuditImportService
{
    /**
     * Alias aceptados para cada columna, ya normalizados.
     *
     * op_retenciones es la retencion de Ingresos Brutos (IB).
     *
     * @var array<string, array<int, string>>
     */
    private const ALIAS = [
        'nro_op'         => ['nro_op', 'nroop', 'nro', 'numero', 'numeroop', 'orden', 'ordenpago'],
        'profesional_id' => ['profesional_id', 'profesionalid', 'profesional', 'cuit', 'nombre_profesional'],
        'fecha'          => ['fecha', 'fecha_op', 'fechaop'],
        'op_total'       => ['op_total', 'optotal', 'total', 'importe', 'monto'],
        'op_retenciones' => ['op_retenciones', 'opretenciones', 'retenciones', 'retencion', 'ib', 'ingresos_brutos'],
    ];

    /**
     * @return array{success: bool, message: string, errors: array<int, string>, importadas: int}
     */
    public function importar(string $rutaTemporal, ?string $extension = null): array
    {
        try {
            $filas = $this->leerFilas($rutaTemporal, $extension);
        } catch (\Throwable $e) {
            return [
                'success'    => false,
                'message'    => 'No se pudo leer el archivo: ' . $e->getMessage(),
                'errors'     => [],
                'importadas' => 0,
            ];
        }

        if ($filas === []) {
            return [
                'success'    => false,
                'message'    => 'El archivo no contiene filas para importar.',
                'errors'     => [],
                'importadas' => 0,
            ];
        }

        $errores   = [];
        $importadas = 0;

        DB::transaction(function () use ($filas, &$errores, &$importadas) {
            foreach ($filas as $numero => $fila) {
                $nroOp = trim((string) ($fila['nro_op'] ?? ''));

                if ($nroOp === '') {
                    $errores[] = 'Fila ' . $numero . ': falta el nro_op.';

                    continue;
                }

                if (mb_strlen($nroOp) > 20) {
                    $errores[] = 'Fila ' . $numero . ': el nro_op "' . $nroOp . '" supera los 20 caracteres.';

                    continue;
                }

                $valorProfesional = trim((string) ($fila['profesional_id'] ?? ''));
                $profesionalId    = $this->resolverProfesional($valorProfesional !== '' ? $valorProfesional : null);

                if ($valorProfesional !== '' && $profesionalId === null) {
                    $errores[] = 'Fila ' . $numero . ': el profesional "' . $valorProfesional . '" no existe.';
                }

                OrdenPagoAuditCab::updateOrCreate(
                    ['nro_op' => $nroOp],
                    [
                        'profesional_id' => $profesionalId,
                        'fecha'          => $this->parseFecha($fila['fecha'] ?? null),
                        'op_total'       => $this->parseNumero($fila['op_total'] ?? null),
                        'op_retenciones' => $this->parseNumero($fila['op_retenciones'] ?? null),
                    ]
                );

                $importadas++;
            }
        });

        $mensaje = 'Se importaron ' . $importadas . ' orden(es) de pago.';

        if ($errores !== []) {
            $mensaje .= ' Hay ' . count($errores) . ' aviso(s) en el archivo.';
        }

        return [
            'success'    => true,
            'message'    => $mensaje,
            'errors'     => array_slice($errores, 0, 20),
            'importadas' => $importadas,
        ];
    }

    /**
     * Lee el archivo y devuelve un array indexado por numero de fila con las
     * claves ya normalizadas (nro_op, profesional_id, fecha, op_total, op_retenciones).
     *
     * La extension se recibe aparte porque el archivo subido por HTTP queda en
     * una ruta temporal sin extension (ej: /tmp/phpA1B2.tmp).
     *
     * @return array<int, array<string, string|null>>
     */
    private function leerFilas(string $ruta, ?string $extension = null): array
    {
        $extension = strtolower($extension ?? pathinfo($ruta, PATHINFO_EXTENSION));

        if (in_array($extension, ['csv', 'txt'], true)) {
            return $this->leerCsv($ruta);
        }

        $spreadsheet = IOFactory::createReaderForFile($ruta)->load($ruta);

        return $this->leerHoja($spreadsheet->getActiveSheet());
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function leerCsv(string $ruta): array
    {
        $contenido = file_get_contents($ruta);

        if ($contenido === false) {
            throw new \RuntimeException('No se pudo abrir el archivo.');
        }

        $contenido = preg_replace('/^\xEF\xBB\xBF/', '', $contenido);
        $lineas    = preg_split('/\r\n|\r|\n/', (string) $contenido);

        $filas = [];

        foreach ($lineas as $linea) {
            if (trim($linea) === '') {
                continue;
            }

            $filas[] = str_getcsv($linea, $this->detectarSeparador($linea));
        }

        return $this->mapearFilas($filas);
    }

    /**
     * Detecta el separador del CSV: ';' (formato regional) o ','.
     */
    private function detectarSeparador(string $linea): string
    {
        return substr_count($linea, ';') >= substr_count($linea, ',') ? ';' : ',';
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function leerHoja(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $hoja): array
    {
        $filas = [];

        foreach ($hoja->toArray(null, true, true, false) as $fila) {
            $filas[] = array_values($fila);
        }

        return $this->mapearFilas($filas);
    }

    /**
     * Interpreta la primera fila como encabezado y devuelve el resto mapeado.
     *
     * @param  array<int, array<int, mixed>>  $filas
     * @return array<int, array<string, string|null>>
     */
    private function mapearFilas(array $filas): array
    {
        if ($filas === []) {
            return [];
        }

        $encabezado = array_map(fn ($valor) => $this->normalizar((string) $valor), array_shift($filas));

        // Mapa: columna -> indice en el archivo
        $indices = [];

        foreach ($encabezado as $indice => $nombre) {
            if ($nombre === '') {
                continue;
            }

            foreach (self::ALIAS as $columna => $alias) {
                if (in_array($nombre, $alias, true) && ! isset($indices[$columna])) {
                    $indices[$columna] = $indice;
                }
            }
        }

        if (! isset($indices['nro_op'])) {
            throw new \RuntimeException('El archivo debe tener una columna "nro_op".');
        }

        $resultado = [];

        foreach ($filas as $posicion => $fila) {
            if ($this->filaVacia($fila)) {
                continue;
            }

            $numero  = $posicion + 2; // 1 = encabezado
            $mapeada = [];

            foreach (array_keys(self::ALIAS) as $columna) {
                $indice = $indices[$columna] ?? null;

                $mapeada[$columna] = $indice !== null && isset($fila[$indice])
                    ? trim((string) $fila[$indice])
                    : null;
            }

            $resultado[$numero] = $mapeada;
        }

        return $resultado;
    }

    /**
     * @param  array<int, mixed>  $fila
     */
    private function filaVacia(array $fila): bool
    {
        foreach ($fila as $valor) {
            if (trim((string) $valor) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Resuelve el profesional por id, por cuit o por nombre.
     */
    private function resolverProfesional(?string $valor): ?int
    {
        if ($valor === null || trim($valor) === '') {
            return null;
        }

        $valor = trim($valor);

        if (is_numeric($valor)) {
            $profesional = Profesional::find((int) $valor);

            if ($profesional) {
                return $profesional->id;
            }
        }

        $profesional = Profesional::where('cuit', $valor)->first();

        if ($profesional) {
            return $profesional->id;
        }

        $profesional = $this->buscarPorCuit($valor);

        if ($profesional) {
            return $profesional->id;
        }

        $profesional = Profesional::whereRaw('UPPER(nombre) = ?', [mb_strtoupper($valor)])->first();

        if ($profesional) {
            return $profesional->id;
        }

        return Profesional::where('nombre', 'like', '%' . $valor . '%')
            ->orderBy('nombre')
            ->first()?->id;
    }

    /**
     * Busca el profesional por CUIT ignorando guiones y otros separadores.
     * El valor del archivo y el de la base se comparan solo con sus digitos,
     * sin modificar los datos guardados.
     */
    private function buscarPorCuit(string $valor): ?Profesional
    {
        $cuit = $this->soloDigitos($valor);

        if ($cuit === null || strlen($cuit) !== 11) {
            return null;
        }

        $profesional = Profesional::query()
            ->whereRaw(
                "REPLACE(REPLACE(REPLACE(REPLACE(cuit, '-', ''), '.', ''), ' ', ''), '/', '') = ?",
                [$cuit]
            )
            ->first();

        if ($profesional !== null) {
            return $profesional;
        }

        $sufijo = substr($cuit, -6);

        return Profesional::query()
            ->whereNotNull('cuit')
            ->where('cuit', 'like', '%' . $sufijo . '%')
            ->get(['id', 'nombre', 'cuit'])
            ->first(fn (Profesional $candidato) => $this->soloDigitos($candidato->cuit) === $cuit);
    }

    /**
     * Deja solo los digitos de un CUIT (quita guiones, puntos y espacios).
     */
    private function soloDigitos(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $digitos = (string) preg_replace('/\D+/', '', $valor);

        return $digitos === '' ? null : $digitos;
    }

    private function parseFecha(?string $valor): ?string
    {
        if ($valor === null || trim($valor) === '') {
            return null;
        }

        $valor = trim($valor);

        foreach (['d/m/Y', 'd/m/y', 'Y-m-d', 'd-m-Y'] as $formato) {
            try {
                $carbon = Carbon::createFromFormat($formato, $valor);

                if ($carbon->isValid()) {
                    return $carbon->toDateString();
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        try {
            return Carbon::parse($valor)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseNumero(?string $valor): float
    {
        if ($valor === null || trim($valor) === '') {
            return 0.0;
        }

        $valor = trim($valor);

        // Acepta "1.234,56" (formato regional) y "1,234.56" (formato us).
        if (str_contains($valor, ',') && str_contains($valor, '.')) {
            $valor = str_contains(substr($valor, (int) strrpos($valor, ',')), '.')
                ? str_replace(',', '', $valor)
                : str_replace(['.', ','], ['', '.'], $valor);
        } elseif (str_contains($valor, ',')) {
            $valor = str_replace(',', '.', $valor);
        }

        return is_numeric($valor) ? (float) $valor : 0.0;
    }

    /**
     * Normaliza un texto para comparar nombres de columna:
     * minusculas, sin acentos y solo con letras/numeros/guiones bajos.
     */
    private function normalizar(string $texto): string
    {
        $texto = mb_strtolower(trim($texto));
        $texto = strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ü' => 'u', 'ñ' => 'n', 'ç' => 'c',
        ]);

        return (string) preg_replace('/[^a-z0-9_]+/', '_', $texto);
    }
}
