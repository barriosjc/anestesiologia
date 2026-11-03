# Tareas realizadas

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
