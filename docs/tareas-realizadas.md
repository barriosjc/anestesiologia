# Tareas realizadas

## 11/11/2026 — Obra social con dos códigos y retención en "Nombre del Contribuyente"

### Objetivo

Un mismo PDF puede informar la misma obra social con dos códigos distintos en
distintas filas (ej. `84 - LA PEQUEÑA` y `274 - LA PEQUEÑA`), y la auditoría se
cancelaba porque uno de esos códigos no existe en `coberturas.cod_uom`. Además,
en algunos PDFs la retención de **Caja de Prof. de la salud** no aparece en la
hoja *Valores*: viene en la hoja tipo *Nombre del Contribuyente*, con el
encabezado `Concepto … Retención` y el valor debajo de esa última columna.

### Cambios

- `coberturas.cod_uom` pasa de entero a texto y admite **varios códigos UOM
  separados por coma** (`84,274`). `OrdenPagoAuditCompareService` cruza el
  código del archivo contra esa lista (no usa la descripción para matchear). Los
  archivos sin código (formato `Obra Social - Plan: OSDE`) se siguen resolviendo
  por sigla/nombre.
- `XlsxJsonService` reconoce la hoja con encabezado `Concepto … Retención` y lee
  de ahí `retencion_caja_centavos`, gastos administrativos e IB. Después
  recalcula `total_retenciones_centavos`.
- Si la Caja de Prof. de la salud no se informa en ninguna hoja, se guarda en
  **0** y se agrega una advertencia al documento. El listado y el detalle de
  auditoría muestran un ícono/aviso para que el usuario analice si corresponde
  solicitarla.
- Se corrigió la lectura de **cantidad** en el formato *Orden de Pago* cuando la
  columna C trae el importe total en lugar de la cantidad.
- Tests nuevos: retención tomada solo de la hoja *Nombre del Contribuyente*,
  Caja ausente (0 + advertencia) y obra social con dos códigos separados por
  coma (`84,274`).

### Archivos principales

- `app/Services/OrdenPagoAuditCompareService.php`
- `app/Services/LlamaParse/XlsxJsonService.php`
- `app/Livewire/Entidades/CoberturaIndex.php`
- `database/migrations/2026_11_11_000000_change_coberturas_cod_uom_to_string.php`
- `tests/Feature/OrdenPagoAuditCompareTest.php`
- `tests/Feature/Llamaparse/LlamaParseXlsxTest.php`
- `docs/proceso-llamaparse.md`
- `docs/scripts-db-produccion.md`

---

## 04/11/2026 — Niveles por descripción con porcentaje y texto adicional

### Objetivo

Cuando el archivo informa una descripción como `Especial D 30% valor base`,
`Especial D valor base 30%` o `Especial D 30%`, y el código (`600042`) no existe
en `niveles.cod_uom`, el nivel debe resolverse por la descripción `Especial D` y
el porcentaje (`30`) debe usarse para cruzar el consumo.

### Cambios

- `OrdenPagoAuditCompareService` busca el nivel por descripción en dos pasos:
  primero la descripción base exacta (texto anterior al `%`) y después la
  descripción de `niveles` más larga que sea prefijo de la descripción del
  archivo. Así se tolera texto adicional antes o después del porcentaje.
- El porcentaje se sigue extrayendo del primer número seguido de `%`; ahora
  también acepta espacios no separables (NBSP).
- Se agregó un test para `Especial D valor base 30%` sin equivalencia de
  código en `niveles.cod_uom`.

### Archivos principales

- `app/Services/OrdenPagoAuditCompareService.php`
- `tests/Feature/OrdenPagoAuditCompareTest.php`

---

## 04/11/2026 — Retenciones de la orden de pago en el listado

### Objetivo

En `ordenes-pagos-audit/cab` la columna **Retención** debe mostrar la fila
**Caja de Prof. de la salud** de la hoja *Valores* del archivo, la columna
**Gast. Adm.** las filas **Gastos Administrativos 11%** y **5%**, y la columna
**IB** la retención de **Ingresos Brutos** (antes `op_retenciones` guardaba la
suma de todas las retenciones). **A pagar** es la diferencia entre el total y
esas retenciones.

### Causa

Las retenciones no están en el detalle de la liquidación: vienen en la hoja
*Valores* del XLSX que genera LlamaParse (`Caja de Prof. de la salud`,
`Gastos Administrativos 11%`, `Gastos Administrativos 5%`, cheques y
transferencias). Además, muchas OPs no tienen retención de IB: esa información
simplemente no está en el archivo.

### Cambios

- `XlsxJsonService` toma la hoja *Valores* (encabezado `Valor / Banco / Numero
  / Fecha / Importe`) y guarda en la cabecera del JSON:
  `retencion_caja_centavos`, `retencion_gastos_11_centavos`,
  `retencion_gastos_5_centavos` y `retencion_ib_centavos` (este último solo si
  el archivo informa IB).
- `OrdenPagoAuditCompareService` guarda esas retenciones como snapshot en la
  cabecera: `retencion_caja`, `retencion_gastos` y `op_retenciones` (IB).
  Si el archivo no informa el dato, la columna queda en `NULL` (no en cero).
- `OrdenPagoAuditCab` agrega los accesores `caja_prof_salud`, `gastos_admin`,
  `ib` y `a_pagar` (`total - IB - caja - gastos admins`). Si el archivo no trae
  retenciones, `a_pagar` queda en `NULL`.
- El listado y el detalle muestran `—` cuando no hay dato.
- `OrdenPagoAuditImportService`: la columna del CSV `op_retenciones` es la
  retención de IB; también acepta `ib` e `ingresos_brutos`.
- Nuevo comando `documentos-llamaparse:regenerar-json`: regenera el JSON de los
  documentos ya procesados desde el XLSX guardado en `storage` (sin volver a
  llamar a LlamaParse ni gastar credits) y, con `--auditar`, vuelve a generar
  la auditoría.

### Comandos

- `php artisan documentos-llamaparse:regenerar-json --dry-run` → muestra las
  retenciones que encuentra en cada XLSX.
- `php artisan documentos-llamaparse:regenerar-json --auditar` → regenera el
  JSON y las auditorías.

### Base de datos

Columnas nuevas en `ordenes_pagos_audit_cab` (script en
`docs/scripts-db-produccion.md`, sección 13):

```sql
ALTER TABLE ordenes_pagos_audit_cab
    ADD COLUMN retencion_caja DECIMAL(14,2) NULL AFTER op_total,
    ADD COLUMN retencion_gastos DECIMAL(14,2) NULL AFTER retencion_caja;
```

### Archivos principales

- `app/Services/LlamaParse/XlsxJsonService.php`
- `app/Services/OrdenPagoAuditCompareService.php`
- `app/Services/OrdenPagoAuditImportService.php`
- `app/Models/OrdenPagoAuditCab.php`
- `app/Console/Commands/RegenerarJsonDocumentosLlamaparse.php`
- `resources/views/livewire/ordenes-pagos-audit/orden-pago-audit-index.blade.php`
- `resources/views/livewire/ordenes-pagos-audit/orden-pago-audit-detalle.blade.php`
- `database/migrations/2026_11_04_000100_add_retenciones_to_ordenes_pagos_audit_cab_table.php`
- `tests/Feature/OrdenPagoAuditCompareTest.php`
- `tests/Feature/OrdenPagoAuditTest.php`
- `tests/Feature/Llamaparse/RegenerarJsonDocumentosLlamaparseTest.php`

---

## 04/11/2026 — Nro OP de la auditoría tomado del PDF

### Objetivo

La columna **Nro OP** de `ordenes-pagos-audit/cab` mostraba `8` / `5` (el id
del documento) en lugar del número real de la orden de pago
(`OPL X 0000-00077740` → `77740`).

### Causa

`OrdenPagoAuditCompareService` toma el número de `documento.numero` del JSON.
Ese JSON se genera desde el **XLSX de tablas** de LlamaParse, y ese XLSX **no
incluye el título del comprobante**: la hoja `--ORDEN DE PAGO--` sólo trae
Agente, CUIT, Dirección y Fecha. El número `OPL X 0000-00077740` existe
únicamente en el **markdown**, que se guardaba pero no se leía. Sin número, la
auditoría caía en el fallback `DOC-<id>` y la vista mostraba esos dígitos.

### Cambios

- `app/Services/LlamaParse/NumeroOrdenPagoExtractor.php` (nuevo): extrae el
  número del markdown (`OPL`, `ORDEN DE PAGO`, `OP`), recorre todas las páginas
  y devuelve sólo los dígitos (`000000077740`).
- `XlsxJsonService::documento()` acepta el markdown como tercer parámetro y lo
  usa sólo cuando las celdas no traen número. `LlamaParseService` ahora calcula
  el markdown **antes** de generar el JSON y se lo pasa.
- `OrdenPagoAuditCompareService`: si `documento.numero` es `null`, intenta el
  markdown antes de caer en `DOC-<id>`. Así los documentos ya procesados se
  corrigen al volver a auditarlos.
- `OrdenPagoAuditCab::getNroOp8Attribute()` ignora el sufijo `-<docId>` que se
  agrega cuando dos documentos traen la misma OP (índice único de `nro_op`).
- Comando `ordenes-pagos:reparar-nro-op [--dry-run]`: completa el `nro_op` de
  las auditorías que quedaron con `DOC-<id>`, usando el markdown del documento.
  No toca números cargados a mano.

### Archivos principales

- `app/Services/LlamaParse/NumeroOrdenPagoExtractor.php`
- `app/Services/LlamaParse/XlsxJsonService.php`
- `app/Services/LlamaParse/LlamaParseService.php`
- `app/Services/OrdenPagoAuditCompareService.php`
- `app/Models/OrdenPagoAuditCab.php`
- `app/Console/Commands/RepararNroOpOrdenPago.php`
- `tests/Feature/Llamaparse/NumeroOrdenPagoExtractorTest.php`
- `tests/Feature/Llamaparse/LlamaParseXlsxTest.php`
- `tests/Feature/OrdenPagoAuditCompareTest.php`
- `tests/Feature/OrdenPagoAuditTest.php`

### Comando de reparación

- `php artisan ordenes-pagos:reparar-nro-op --dry-run` → muestra los cambios.
- `php artisan ordenes-pagos:reparar-nro-op` → los guarda.

---

## 04/11/2026 — Subida de archivos de Órdenes de Pago sin Livewire

### Objetivo

El modal **Subir** de `ordenes-pagos-audit/cab` fallaba con
*"The file failed to upload"* incluso con PDFs de menos de 10 MB. La subida
temporal de Livewire (`wire:model` + URL firmada + `/livewire/upload-file`)
depende de tres pasos y cualquier falla intermedia termina en ese mensaje
genérico, sin decir qué pasó.

### Cambios

- La subida se rehizo como un **POST multipart directo** del navegador
  (`XMLHttpRequest`) a `POST /ordenes-pagos-audit/subir`
  (`OrdenPagoAuditUploadController`). El archivo va en un único request.
- El modal muestra **progreso real** (`xhr.upload.onprogress`) y el error que
  devuelve el servidor tal cual (validación, sesión expirada, permisos, tamaño).
- `public/js/upload-directo.js`: helper reutilizable. Se configura con
  `data-upload-*` en el contenedor y no depende de Livewire.
- Al terminar una importación de CSV/XLSX se emite
  `orden-pago-audit-importada` y el componente refresca el listado
  (`#[On] refrescarListado`). Con un PDF se navega al detalle del documento.
- `OrdenPagoAuditImportService::importar()` acepta la extensión explícita: el
  archivo subido por HTTP queda en una ruta temporal sin extensión
  (`/tmp/phpXXXX.tmp`), y antes se deducía el lector por la extensión del path.
- El máximo informado es el **menor entre la aplicación y PHP**
  (`upload_max_filesize` / `post_max_size`). En este entorno PHP limita a 16 MB
  aunque `LLAMAPARSE_MAX_FILE_MB` diga 100.
- Se eliminaron del componente `WithFileUploads`, `$archivo`,
  `$erroresImportacion`, `importar()` y `procesarPdfConLlamaparse()`.
- Si el PDF se guarda pero LlamaParse no lo recibe, la respuesta abre igual el
  detalle del documento (que queda en estado Error) para reprocesarlo.

### Archivos principales

- `app/Http/Controllers/OrdenesPagosAudit/OrdenPagoAuditUploadController.php`
- `public/js/upload-directo.js`
- `app/Livewire/OrdenesPagosAudit/OrdenPagoAuditIndex.php`
- `resources/views/livewire/ordenes-pagos-audit/orden-pago-audit-index.blade.php`
- `app/Services/OrdenPagoAuditImportService.php`
- `routes/web.php`
- `tests/Feature/OrdenPagoAuditTest.php`

### Ruta nueva

- `POST /ordenes-pagos-audit/subir` → importación CSV/XLSX o envío del PDF a
  LlamaParse.

---

## 02/11/2026 — Auditoría: la descripción no marca diferencia

### Objetivo

Evitar falsos positivos en la auditoría de órdenes de pago: como los ítems se
relacionan por código, una descripción abreviada en el archivo (por ejemplo
`Colecistectomía` contra `Colecistectomía videolaparoscópica...`) no debe
convertir la fila en “Diferencia” ni pintarla de amarillo.

### Cambios

- El estado `diferencia` ahora se define por el valor, la fecha o la obra
  social. La comparación de descripción se sigue guardando en
  `dif_descripcion`, pero no afecta el estado ni la severidad de la fila.
- El atributo que no coincide con el sistema se marca con un **punto rojo**
  delante del dato: valor, fecha, obra social o descripción. Se agregó el
  parcial `partials/dif-punto.blade.php` y el estilo `.opa-dif-punto`.
- Se agregó el accessor `OrdenPagoAuditDet::dif_valor` (diferencia de importe
  mayor o igual a 0,01).
- Las filas que solo tienen la descripción abreviada quedan con estado
  “Coincide” y gravedad “Sin diferencia”, con el punto rojo en la descripción.

### Archivos principales

- `app/Services/OrdenPagoAuditCompareService.php`
- `app/Models/OrdenPagoAuditDet.php`
- `resources/views/livewire/ordenes-pagos-audit/orden-pago-audit-detalle.blade.php`
- `resources/views/livewire/ordenes-pagos-audit/partials/dif-punto.blade.php`
- `public/css/custom.css`

---

## 02/11/2026 — Botón "Ver relaciones" en el detalle del PDF

### Objetivo

Poder abrir la tabla de relaciones y diferencias de una orden de pago sin
depender de que la validación estricta haya terminado bien, y ver también los
datos que no se pudieron relacionar.

### Cambios

- En el detalle del PDF procesado, al lado de **Auditar PDF / Reauditar PDF**,
  se agregó el botón **Ver relaciones**. Si la auditoría ya existe, la abre;
  si no existe, la genera y abre la tabla.
- **Auditar PDF** se mantiene como validación estricta: si hay datos sin
  equivalencia, cancela la auditoría y muestra la tabla de inconsistencias para
  codificarlos.
- **Ver relaciones** genera la auditoría sin validación estricta
  (`OrdenPagoAuditCompareService::auditar($documento, false)`), de modo que la
  tabla muestra:
  - Filas del archivo que no están en el sistema:
    `No está en sistema (atención)`, `No está en sistema (código)` o
    `No está en sistema (práctica)`.
  - Filas del sistema que no están en el archivo: `Falta en el archivo`.
  - Repeticiones del archivo sin contraparte: `Sin contraparte en sistema`.
- Se actualizaron las etiquetas en `OrdenPagoAuditDet` y en los filtros de la
  vista de detalle de la auditoría.

### Archivos principales

- `app/Services/OrdenPagoAuditCompareService.php`
- `app/Livewire/DocumentosLlamaparse/DocumentoLlamaparseDetalle.php`
- `app/Models/OrdenPagoAuditDet.php`
- `resources/views/livewire/documentos-llamaparse/documento-llamaparse-detalle.blade.php`
- `resources/views/livewire/ordenes-pagos-audit/orden-pago-audit-detalle.blade.php`

---

## 02/11/2026 — Coberturas: relación solo por `cod_uom`

### Objetivo

Relacionar la obra social informada por los PDFs (en cualquier formato) con
`coberturas` usando únicamente `coberturas.cod_uom`, sin depender de `id_uom`.

### Cambios

- Se eliminó la columna `coberturas.id_uom` y su uso en el alta/edición,
  búsqueda y modelo `Cobertura`.
- `OrdenPagoAuditCompareService` compara el prefijo de la obra social del
  archivo contra `coberturas.cod_uom` y ya no intenta `id_uom` como respaldo.
- En `ordenes_pagos_audit_det` se renombraron `id_uom_archivo` e
  `id_uom_sistema` a `cod_uom_archivo` y `cod_uom_sistema`.
- La vista de detalle de auditoría y el orden por obra social usan las nuevas
  columnas `cod_uom_*`.

## 29/10/2026 — Cruce IOMA y niveles con porcentaje

### Objetivo

Adaptar la auditoría de órdenes de pago para dos casos particulares:

1. **IOMA**: el código del archivo se cruza contra `nomenclador.codigo` con
   `nom_padre_id = 2`, no contra `niveles.cod_uom`.
2. **Descripciones con porcentaje**: si el archivo informa, por ejemplo,
   `Especial F 30%` o `Especial F 50% valor`, no se usa el código sino la
   descripción base (`Especial F`) para buscar el nivel en `niveles` por
   `descripcion`. El porcentaje se usa después para distinguir dos consumos
   del mismo nomenclador en `consumos_det` (uno al 100 % y otro al 30 %, 50 %,
   etc.).

### Cambios

- Nuevo parámetro `COBERTURA_COD_IOMA` (valor inicial `78`). Guarda el
  `coberturas.cod_uom` que identifica a IOMA.
- Nuevas columnas en `ordenes_pagos_audit_det`:
  `codigo_sistema`, `porcentaje_archivo` y `porcentaje_sistema`.
- `OrdenPagoAuditCompareService`:
  - Detecta IOMA comparando `coberturas.cod_uom` con el parámetro.
  - Para IOMA resuelve el nivel con `nomenclador` (`nom_padre_id = 2`) por
    código y cruza los consumos por código + porcentaje.
  - Para el resto de las coberturas, si la descripción termina en `%`,
    resuelve el nivel por `niveles.descripcion` y cruza por nivel + porcentaje.
  - Si el archivo no informa porcentaje, prioriza el consumo al 100 % y, si
    no hay, el único consumo disponible para ese nivel/código.
  - Las descripciones se comparan sin el sufijo de porcentaje para no marcar
    una diferencia falsa (`Especial F 30%` contra `Especial F`).
- La auditoría se cancela si el archivo tiene datos que no se puedan relacionar:
  - Atención que no existe en `partes_cab` para el profesional.
  - Obra social cuyo prefijo no coincide con `coberturas.id_uom` ni con
    `coberturas.cod_uom` (este último cubre IOMA), o que el archivo no informa.
  - IOMA: código que no existe en `nomenclador` (`nom_padre_id = 2`).
  - Resto: código que no existe en `niveles.cod_uom`.
  - Descripción con `%`: descripción base que no existe en
    `niveles.descripcion` (no se usa el código como respaldo).
  En ese caso no se guarda cabecera ni detalles y se muestran los datos
  inconsistentes (atención, obra social, código, descripción, porcentaje y
  motivo) con un enlace a **Equivalencias UOM**, al **nomenclador de IOMA**, a
  **Coberturas** o al listado de **Partes** para codificarlos o cargarlos.
  Lo mismo ocurre al **Recalcular** una auditoría existente: se muestra la tabla
  y la auditoría anterior queda intacta.
- La vista de detalle muestra el código de sistema, el porcentaje del archivo
  y el porcentaje del sistema.
- Corrección sobre la tabla `niveles` preexistente: es MyISAM con `id INT` y
  sin `created_at`/`updated_at`. Por eso:
  - `ordenes_pagos_audit_det.nivel_id` quedó como `INT NULL` y **sin** clave
    foránea a `niveles` (MySQL falla con `errno 1824`).
  - El modelo `Nivel` usa `$timestamps = false`.
  - La validación de Equivalencias UOM se ajustó a los tamaños reales:
    `nivel`/`cod_uom` 10 caracteres y `descripcion` 50.

### Archivos principales

- `app/Services/OrdenPagoAuditCompareService.php`
- `app/Models/OrdenPagoAuditDet.php`
- `app/Models/Nivel.php`
- `app/Livewire/OrdenesPagosAudit/OrdenPagoAuditIndex.php`
- `app/Livewire/OrdenesPagosAudit/OrdenPagoAuditDetalle.php`
- `app/Livewire/Entidades/CoberturaIndex.php`
- `app/Livewire/Partes/ParteFiltro.php`
- `app/Livewire/Partes/ParteTabla.php`
- `app/Livewire/Nomencladores/NivelesIndex.php`
- `resources/views/livewire/ordenes-pagos-audit/orden-pago-audit-index.blade.php`
- `resources/views/livewire/ordenes-pagos-audit/orden-pago-audit-detalle.blade.php`
- `resources/views/livewire/entidades/cobertura-index.blade.php`
- `database/migrations/2026_10_28_000300_create_ordenes_pagos_audit_det_table.php`
- `database/migrations/2026_10_29_000000_add_cobertura_cod_ioma_parametro.php`
- `database/migrations/2026_10_29_000100_add_porcentajes_to_ordenes_pagos_audit_det_table.php`
- `docs/scripts-db-produccion.md`

---

## 28/10/2026 — Auditoría de órdenes de pago contra JSON

### Objetivo

Comparar el JSON normalizado de una orden de pago / detalle de liquidación
(procesado con LlamaParse) contra los datos cargados en la base y mostrar las
diferencias en una tabla filtrable, coloreada según la magnitud de la diferencia.

### Cambios de estructura

- Nueva tabla `niveles` (`id`, `nivel`, `cod_uom`, `descripcion`).
  `cod_uom` es el código que viene en el archivo; `nivel` es el nivel interno
  que usa `nomenclador` / `nom_valores`.
- Nueva columna `coberturas.id_uom`. Guarda el prefijo numérico de la obra
  social del archivo (ej.: `143 - UOM` → `143`).
- Nuevos campos en `ordenes_pagos_audit_cab`:
  `documento_llamaparse_id`, `nro_liquidacion`, `total_sistema`,
  `total_diferencia`, `cantidad_diferencias`, `auditado_at`.
- Nueva tabla `ordenes_pagos_audit_det` con el resultado de cada comparación
  (atención, fechas, obra social, período, código/nivel, descripciones,
  valores, diferencia, porcentaje, severidad y estado).

Los scripts SQL de producción están en `docs/scripts-db-produccion.md`
(secciones 8 a 12).

### Flujo

1. En **Órdenes Pagos Audit.** se presiona **Auditar PDF**.
2. Se elige un documento ya procesado (`documentos_llamaparse`) con JSON.
3. El servicio compara:
   - Profesional: `cabecera.cuit` contra `profesionales.cuit`; si no hay CUIT,
     por nombre.
   - Atención: `atenciones[].atencion` contra `partes_cab.atencion` del
     profesional.
   - Código: `detalles[].codigo_nivel` contra `niveles.cod_uom` para obtener
     el nivel interno (para IOMA y descripciones con porcentaje rige lo
     agregado el 29/10/2026).
   - Práctica: el nivel interno contra `nomenclador.nivel` (o
     `nom_practicas_estudios.codigo`) de los consumos del parte.
   - Valor: `detalles[].importe` contra la suma de `consumos_det.valor` del
     mismo nivel.
   - Obra social: prefijo del archivo contra `coberturas.id_uom`.
   - Fecha: `detalles[].fecha` / `atenciones[].fecha` contra
     `partes_cab.fec_prestacion`.
   - Descripción: `detalles[].descripcion_nivel` contra
     `niveles.descripcion`. Es informativa: como el match es por código, una
     descripción abreviada no marca la fila como diferencia.
   - Período: solo informativo.
4. Se guarda la cabecera y el detalle de la auditoría y se abre la vista de
   resultados.

### Colores

La severidad se calcula por porcentaje de diferencia de valor:

- **Baja**: hasta 5 %.
- **Media**: más de 5 % y hasta 20 %.
- **Alta**: más de 20 %, o fila faltante en el archivo / en la base.

La diferencia de estado se define por el valor, la fecha o la obra social. La
descripción no cambia el estado. El dato que no coincide con el sistema (valor,
fecha, obra social o descripción) se marca con un **punto rojo** delante del
atributo. La tabla permite filtrar por texto, estado, gravedad y “solo
diferencias”.

### Archivos principales

- `app/Services/OrdenPagoAuditCompareService.php`
- `app/Livewire/OrdenesPagosAudit/OrdenPagoAuditIndex.php`
- `app/Livewire/OrdenesPagosAudit/OrdenPagoAuditDetalle.php`
- `app/Livewire/Nomencladores/NivelesIndex.php`
- `app/Models/Nivel.php`
- `app/Models/OrdenPagoAuditDet.php`
- `resources/views/livewire/ordenes-pagos-audit/orden-pago-audit-detalle.blade.php`
- `resources/views/livewire/nomencladores/niveles-index.blade.php`

### Rutas nuevas

- `GET /ordenes-pagos-audit/cab/{orden}` → detalle de la auditoría.
- `GET /nomenclador/niveles` → administración de equivalencias UOM.
