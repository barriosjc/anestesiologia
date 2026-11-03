# Proceso PDF → LlamaParse → XLSX → JSON

Fecha: 25/10/2026
Proyecto: Anestesiología (Laravel 11 + Livewire 3)

## Objetivo

Procesar órdenes de pago en PDF desde el módulo **Órdenes Pagos Audit.**, usando la API
de LlamaParse. No requiere binarios ni comandos en el servidor: funciona con salida HTTPS.

## Flujo

1. En "Órdenes Pagos Audit." se sube un PDF.
2. `LlamaParseService` guarda el PDF en `storage/app/private/llamaparse/{uuid}/`.
3. Se envía a `POST /api/v2/parse/upload` con:
   - `tier=agentic`
   - `output_options.tables_as_spreadsheet.enable=true`
   - OCR en español/inglés
4. La pantalla de detalle consulta `GET /api/v2/parse/{job_id}` cada pocos segundos.
   Si la consulta falla por timeout o corte de red, el documento **sigue en
   proceso** y se reintenta en el siguiente sondeo. Si quedó en `error` pero
   tiene `job_id`, el botón **Reintentar consulta** vuelve a consultar el mismo
   trabajo sin volver a subir el PDF ni consumir créditos.
5. Al completar, se descarga el XLSX de tablas y se procesa con `XlsxJsonService`.
6. Se guarda el JSON final, el XLSX, el markdown, los items y el consumo de créditos.
7. Se valida que la suma de los detalles coincida con el total informado.
8. Desde **Órdenes Pagos Audit.** → **Auditar PDF** se puede comparar ese JSON
   contra la base (`partes_cab`, `consumos_det`, `coberturas`, `niveles`,
   `nomenclador`). El detalle del proceso está en `docs/tareas-realizadas.md` y
   los scripts SQL en `docs/scripts-db-produccion.md` (secciones 8 a 12).
   También se puede auditar directo desde el detalle del documento: cuando el
   JSON está listo aparecen **Ver relaciones** y **Auditar PDF**; si ya existe
   una auditoría, muestra **Ver relaciones** y **Reauditar PDF**.
   **Auditar PDF** valida de forma estricta y cancela si hay datos sin
   equivalencia. **Ver relaciones** abre la tabla de relaciones y diferencias y,
   si la auditoría no existe, la genera sin validación estricta para mostrar
   también las filas marcadas como **No está en sistema** o **Falta en el
   archivo**.

## Criterios de matching de la auditoría

- **IOMA**: se detecta cuando `coberturas.cod_uom` coincide con el parámetro
  `COBERTURA_COD_IOMA` (valor `78`). En ese caso el código del archivo se busca
  en `nomenclador.codigo` con `nom_padre_id = 2`, sin usar `niveles.cod_uom`.
- **Resto de coberturas**: el código del archivo se busca en `niveles.cod_uom`.
  Si la descripción termina en porcentaje (ej. `Especial F 30%` o
  `Especial F 50% valor`), se quita el porcentaje y el nivel se busca por
  `niveles.descripcion`.
- **Consumos**: se cruzan por nivel (o código, para IOMA) **y**
  `consumos_det.porcentaje`. Si el archivo no informa porcentaje se prioriza el
  100 % y, si no existe, se usa el único consumo disponible.

### Validación previa y cancelación

Antes de guardar la auditoría se recorre el archivo completo. Si algún dato no
tiene código para relacionar, **no se guarda nada** (ni cabecera ni detalles):

- IOMA: código inexistente en `nomenclador` (`nom_padre_id = 2`).
- Resto: código inexistente en `niveles.cod_uom`.
- Descripción con porcentaje: descripción base inexistente en
  `niveles.descripcion`. En este caso **no** se usa el código como respaldo.

La pantalla muestra una tabla con atención, código, descripción, porcentaje y
motivo, y un enlace para codificar el dato en **Equivalencias UOM** o en el
**nomenclador de IOMA**. Al **Recalcular** una auditoría existente se aplica la
misma validación y la auditoría anterior queda sin cambios.

## Formatos soportados

- **Detalle Liquidación:** una fila por práctica, 9 columnas.
- **Orden de Pago:** dos filas por práctica, 7 columnas, con porcentaje/cantidad.
- **Mixto:** si un archivo trae ambos, cada hoja se procesa con el parser que corresponde.

## Configuración

```env
LLAMAPARSE_API_KEY=llx-...
LLAMAPARSE_TIER=agentic
LLAMAPARSE_IDIOMAS=es,en
LLAMAPARSE_TABLES_AS_SPREADSHEET=true
```

- Plan gratuito: 10.000 créditos/mes.
- Tier Agentic: ~10 créditos por página (~1.000 páginas/mes).

## Archivos principales

- `app/Services/LlamaParse/LlamaParseService.php`
- `app/Services/LlamaParse/XlsxJsonService.php`
- `app/Services/LlamaParse/DocumentoValidator.php`
- `app/Services/LlamaParse/ImporteCentavos.php`
- `app/Livewire/OrdenesPagosAudit/OrdenPagoAuditIndex.php`
- `app/Livewire/DocumentosLlamaparse/`
- `config/llamaparse.php`
- `database/migrations/2026_10_25_000000_create_documentos_llamaparse_table.php`

## Despliegue por FTP

1. Subir los archivos modificados/nuevos.
2. Agregar `LLAMAPARSE_API_KEY` al `.env` del hosting.
3. Ejecutar por phpMyAdmin el SQL de `docs/scripts-db-produccion.md` (sección 6).
4. Borrar `bootstrap/cache/config.php` si existe.
5. No hace falta `composer install`, ni binarios, ni comandos.

## Archivos a eliminar en el hosting

Módulos retirados que deben borrarse por FTP si ya estaban subidos:

- `app/Services/Ia/`
- `app/Services/Ocr/`
- `app/Services/Xlsx/`
- `app/Http/Controllers/Ia/`, `Ocr/`, `Xlsx/`
- `app/Livewire/DocumentosIa/`, `DocumentosOcr/`, `DocumentosXlsx/`
- `app/Models/DocumentoIa.php`, `DocumentoOcr.php`, `DocumentoXlsx.php`
- `app/Jobs/ProcesarDocumentoIa.php`, `ProcesarDocumentoOcr.php`
- `app/Console/Commands/ExtraerDocumentoIa.php`, `VerificarOcr.php`
- `app/Enums/EstadoDocumentoIa.php`, `EstadoDocumentoOcr.php`
- `app/Exceptions/IaException.php`, `OcrException.php`
- `resources/views/ia/`, `resources/views/xlsx/`
- `resources/views/livewire/documentos-ia/`, `documentos-ocr/`, `documentos-xlsx/`
- `config/ia.php`, `config/ocr.php`

En el `.env` del hosting, borrar las variables `IA_*` y `OCR_*` que ya no se usan.
