# Scripts de base de datos para producción

Acá se acumulan **todos** los cambios de estructura y datos que hay que correr en
producción. La idea es ejecutarlos en orden, una sola vez, después de subir el código.

Fecha de inicio: 21/10/2026
Motor: MySQL (InnoDB; `niveles` es MyISAM preexistente, utf8mb4)

## Antes de ejecutar

1. Hacer backup de la base de datos.
2. Correr los scripts en el orden en que aparecen.
3. Si en el hosting existe `bootstrap/cache/config.php` o `bootstrap/cache/routes-v7.php`,
   borrarlos por FTP después de subir el código y cambiar el `.env`, o los cambios no se ven.
4. Los `INSERT` de roles/permisos usan `WHERE NOT EXISTS` para poder re-ejecutarse sin error.

---

## 1. Tabla `ordenes_pagos_audit_cab`

Módulo Órdenes de Pago – Auditoría.

```sql
CREATE TABLE IF NOT EXISTS ordenes_pagos_audit_cab (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nro_op VARCHAR(20) NOT NULL,
    profesional_id INT NULL,
    fecha DATE NULL,
    op_total DECIMAL(14,2) NOT NULL DEFAULT 0,
    op_retenciones DECIMAL(14,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY ordenes_pagos_audit_cab_nro_op_unique (nro_op),
    KEY ordenes_pagos_audit_cab_profesional_id_index (profesional_id),
    KEY ordenes_pagos_audit_cab_fecha_index (fecha),
    CONSTRAINT ordenes_pagos_audit_cab_profesional_id_foreign
        FOREIGN KEY (profesional_id) REFERENCES profesionales (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

> Si la FK falla por tipo, verificar el tipo real de `profesionales.id` y usar el mismo
> (`INT` o `INT UNSIGNED`).

---

## 2. Rol `facturista`

```sql
INSERT INTO roles (name, guard_name, created_at, updated_at)
SELECT 'facturista', 'web', NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM roles WHERE name = 'facturista' AND guard_name = 'web'
);
```

---

## 3. Columna `atencion` en `partes_cab`

```sql
ALTER TABLE partes_cab
    ADD COLUMN atencion VARCHAR(10) NULL AFTER profesional_id;
```

> Se usa `VARCHAR(10)` para no perder ceros a la izquierda (ej: `0000123456`).
> MySQL no soporta `ADD COLUMN IF NOT EXISTS`; correr una sola vez o controlar con
> `information_schema.columns`.

---

## 4. Columna `atencion` en `presupuestos_cab`

```sql
ALTER TABLE presupuestos_cab
    ADD COLUMN atencion VARCHAR(10) NULL AFTER observaciones;
```

---

## 5. Columna `cuit` en `profesionales`

Se reemplaza el campo DNI por CUIT (hasta 11 caracteres, sin guiones).

```sql
ALTER TABLE profesionales
    CHANGE COLUMN dni cuit VARCHAR(11) NULL;
```

> `CHANGE COLUMN` renombra y cambia el tipo conservando los datos y la posición.
> Antes de ejecutar, verificar que ningún valor existente supere los 11 caracteres.

---

## 6. Tabla `documentos_llamaparse`

Módulo PDF → LlamaParse → XLSX → JSON. No requiere binarios: solo salida HTTPS.

```sql
CREATE TABLE IF NOT EXISTS documentos_llamaparse (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    archivo VARCHAR(255) NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    mime VARCHAR(100) NOT NULL,
    tamano BIGINT UNSIGNED NOT NULL DEFAULT 0,
    estado VARCHAR(30) NOT NULL DEFAULT 'pendiente',
    job_id VARCHAR(255) NULL,
    tier VARCHAR(30) NULL,
    progreso TINYINT UNSIGNED NOT NULL DEFAULT 0,
    progreso_mensaje VARCHAR(255) NULL,
    respuesta LONGTEXT NULL,
    markdown LONGTEXT NULL,
    items LONGTEXT NULL,
    xlsx_archivo VARCHAR(255) NULL,
    json LONGTEXT NULL,
    json_crudo LONGTEXT NULL,
    usage_credits DECIMAL(8,2) NULL,
    error TEXT NULL,
    procesado_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY documentos_llamaparse_uuid_unique (uuid),
    KEY documentos_llamaparse_job_id_index (job_id),
    KEY documentos_llamaparse_estado_index (estado),
    KEY documentos_llamaparse_user_id_index (user_id),
    CONSTRAINT documentos_llamaparse_user_id_foreign
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Variables de entorno necesarias:

```env
LLAMAPARSE_API_KEY=llx-...
LLAMAPARSE_TIER=agentic
```

---

## 7. Limpieza de módulos retirados

Se retiraron los módulos Documento IA, Documento OCR y Documento XLSX. **Hacer backup
antes de ejecutar.** Si ya no se necesitan los datos históricos:

```sql
DROP TABLE IF EXISTS documentos_ocr;
DROP TABLE IF EXISTS documentos_xlsx;
DROP TABLE IF EXISTS documentos_ia;
```

Para retirar el permiso que ya no se usa:

```sql
DELETE rp FROM role_has_permissions rp
JOIN permissions p ON p.id = rp.permission_id
WHERE p.name = 'adm_documentos_ia';

DELETE mp FROM model_has_permissions mp
JOIN permissions p ON p.id = mp.permission_id
WHERE p.name = 'adm_documentos_ia';

DELETE FROM permissions WHERE name = 'adm_documentos_ia';
```

---

## 8. Tabla `niveles` (equivalencias UOM)

Relaciona el código que viene en el archivo (`cod_uom`) con el nivel interno
que usa el nomenclador (`nivel`). La descripción es la canónica del código.

> **La tabla ya existe en producción.** Su definición real es MyISAM, con
> `id INT` (con signo) y sin `created_at` / `updated_at`:

```sql
CREATE TABLE IF NOT EXISTS niveles (
    id INT NOT NULL AUTO_INCREMENT,
    nivel VARCHAR(10) NULL,
    cod_uom VARCHAR(10) NULL,
    descripcion VARCHAR(50) NULL,
    PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

> Al ser **MyISAM**, MySQL no permite referenciarla con una clave foránea.
> Por eso `ordenes_pagos_audit_det.nivel_id` no lleva FK (ver sección 11).
> El modelo `Nivel` usa `$timestamps = false` porque no existen esas columnas.

Se administra desde **Nomencladores → Equivalencias UOM**. También se puede
cargar por SQL:

```sql
UPDATE niveles SET nivel = '600011', descripcion = 'Especial F'
WHERE cod_uom = '600011';

INSERT INTO niveles (nivel, cod_uom, descripcion)
SELECT '600011', '600011', 'Especial F'
WHERE NOT EXISTS (SELECT 1 FROM niveles WHERE cod_uom = '600011');
```

> `cod_uom` no tiene índice único en la tabla existente: **no** usar
> `ON DUPLICATE KEY UPDATE`, porque insertaría filas duplicadas.

> `descripcion` debe estar cargada para los niveles que el archivo puede
> informar con porcentaje (ej.: `Especial F 30%`, `Especial F 50% valor`).
> La auditoría busca el nivel por esa descripción, sin el porcentaje.

---

## 9. Columna `coberturas.id_uom` (retirada)

La obra social del archivo se relaciona **solo** con `coberturas.cod_uom`. Por
ejemplo, `143 - UOM` se cruza con `cod_uom = '143'`.

Si en producción ya se había agregado la columna `id_uom`, eliminarla:

```sql
ALTER TABLE coberturas DROP COLUMN id_uom;
```

> MySQL no soporta `DROP COLUMN IF EXISTS`; controlar antes con
> `information_schema.columns` o correr una sola vez.

---

## 10. Campos de auditoría en `ordenes_pagos_audit_cab`

```sql
ALTER TABLE ordenes_pagos_audit_cab
    ADD COLUMN documento_llamaparse_id INT UNSIGNED NULL AFTER id,
    ADD COLUMN nro_liquidacion VARCHAR(50) NULL AFTER nro_op,
    ADD COLUMN total_sistema DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER op_total,
    ADD COLUMN total_diferencia DECIMAL(14,2) NOT NULL DEFAULT 0 AFTER total_sistema,
    ADD COLUMN cantidad_diferencias INT UNSIGNED NOT NULL DEFAULT 0 AFTER total_diferencia,
    ADD COLUMN auditado_at TIMESTAMP NULL AFTER cantidad_diferencias,
    ADD UNIQUE KEY ordenes_pagos_audit_cab_documento_unique (documento_llamaparse_id),
    ADD CONSTRAINT ordenes_pagos_audit_cab_documento_foreign
        FOREIGN KEY (documento_llamaparse_id) REFERENCES documentos_llamaparse (id)
        ON DELETE SET NULL;
```

`nro_op` sigue siendo obligatorio. Si el JSON no trae número, el sistema usa
`DOC-{id}` como respaldo.

---

## 11. Tabla `ordenes_pagos_audit_det`

Guarda el resultado de comparar cada fila del JSON contra la base.

> Si la tabla ya existía con las columnas `id_uom_archivo` / `id_uom_sistema`,
> renombrarlas:
>
> ```sql
> ALTER TABLE ordenes_pagos_audit_det
>     CHANGE COLUMN id_uom_archivo cod_uom_archivo VARCHAR(20) NULL,
>     CHANGE COLUMN id_uom_sistema cod_uom_sistema VARCHAR(20) NULL;
> ```

```sql
CREATE TABLE IF NOT EXISTS ordenes_pagos_audit_det (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    orden_pago_audit_cab_id INT UNSIGNED NOT NULL,
    parte_cab_id INT UNSIGNED NULL,
    consumo_det_id INT UNSIGNED NULL,
    cobertura_id INT UNSIGNED NULL,
    nivel_id INT NULL,
    atencion VARCHAR(20) NULL,
    fecha_archivo DATE NULL,
    fecha_sistema DATE NULL,
    obra_social_archivo VARCHAR(120) NULL,
    cod_uom_archivo VARCHAR(20) NULL,
    cod_uom_sistema VARCHAR(20) NULL,
    periodo_archivo VARCHAR(10) NULL,
    codigo_archivo VARCHAR(20) NULL,
    nivel_sistema VARCHAR(20) NULL,
    codigo_sistema VARCHAR(20) NULL,
    descripcion_archivo VARCHAR(200) NULL,
    descripcion_sistema VARCHAR(200) NULL,
    valor_archivo DECIMAL(14,2) NOT NULL DEFAULT 0,
    valor_sistema DECIMAL(14,2) NOT NULL DEFAULT 0,
    diferencia DECIMAL(14,2) NOT NULL DEFAULT 0,
    porcentaje_diferencia DECIMAL(8,2) NOT NULL DEFAULT 0,
    porcentaje_archivo DECIMAL(6,2) NULL,
    porcentaje_sistema DECIMAL(6,2) NULL,
    severidad VARCHAR(20) NOT NULL DEFAULT 'sin-diferencia',
    estado VARCHAR(30) NOT NULL DEFAULT 'ok',
    dif_fecha TINYINT(1) NOT NULL DEFAULT 0,
    dif_obra_social TINYINT(1) NOT NULL DEFAULT 0,
    dif_descripcion TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY ordenes_pagos_audit_det_atencion_index (atencion),
    KEY ordenes_pagos_audit_det_parte_index (parte_cab_id),
    KEY ordenes_pagos_audit_det_consumo_index (consumo_det_id),
    KEY ordenes_pagos_audit_det_cobertura_index (cobertura_id),
    KEY ordenes_pagos_audit_det_nivel_index (nivel_id),
    KEY ordenes_pagos_audit_det_nivel_sistema_index (nivel_sistema),
    KEY ordenes_pagos_audit_det_cab_severidad_index (orden_pago_audit_cab_id, severidad),
    KEY ordenes_pagos_audit_det_cab_estado_index (orden_pago_audit_cab_id, estado),
    CONSTRAINT ordenes_pagos_audit_det_cab_foreign
        FOREIGN KEY (orden_pago_audit_cab_id) REFERENCES ordenes_pagos_audit_cab (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

> **No agregar FK de `nivel_id` a `niveles`**: esa tabla es MyISAM y su `id`
> es `INT` con signo. MySQL rechaza la creación con
> `errno 1824 Failed to open the referenced table 'niveles'`. El índice
> `ordenes_pagos_audit_det_nivel_index` alcanza para los cruces.

Si la tabla ya existe, agregar las columnas nuevas:

```sql
ALTER TABLE ordenes_pagos_audit_det
    ADD COLUMN codigo_sistema VARCHAR(20) NULL AFTER nivel_sistema,
    ADD COLUMN porcentaje_archivo DECIMAL(6,2) NULL AFTER porcentaje_diferencia,
    ADD COLUMN porcentaje_sistema DECIMAL(6,2) NULL AFTER porcentaje_archivo;
```

---

## 12. Parámetro `COBERTURA_COD_IOMA`

Guarda el valor de `coberturas.cod_uom` que identifica a IOMA. Cuando el parte
pertenece a esa cobertura, la auditoría cruza el código del archivo contra
`nomenclador.codigo` con `nom_padre_id = 2` (IOMA) en lugar de `niveles.cod_uom`.

```sql
UPDATE parametros
SET valor = '78', deleted_at = NULL, updated_at = NOW()
WHERE nombre = 'COBERTURA_COD_IOMA';

INSERT INTO parametros (nombre, valor, created_at, updated_at)
SELECT 'COBERTURA_COD_IOMA', '78', NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM parametros WHERE nombre = 'COBERTURA_COD_IOMA'
);
```

> `parametros.nombre` no tiene índice único: **no** usar
> `ON DUPLICATE KEY UPDATE`, porque insertaría una fila nueva en cada corrida.
> El `UPDATE` + `INSERT ... WHERE NOT EXISTS` de arriba es re-ejecutable.

---

## Historial de cambios

| Fecha | Cambio | Estado |
|---|---|---|
| 21/10/2026 | `ordenes_pagos_audit_cab` | pendiente |
| 21/10/2026 | rol `facturista` | pendiente |
| 21/10/2026 | columna `partes_cab.atencion` | pendiente |
| 21/10/2026 | columna `presupuestos_cab.atencion` | pendiente |
| 23/10/2026 | columna `profesionales.cuit` (reemplaza `dni`) | pendiente |
| 25/10/2026 | `documentos_llamaparse` | pendiente |
| 25/10/2026 | limpieza de `documentos_ia`, `documentos_ocr`, `documentos_xlsx` | pendiente |
| 28/10/2026 | tabla `niveles` | pendiente |
| 28/10/2026 | columna `coberturas.id_uom` | pendiente |
| 28/10/2026 | campos de auditoría en `ordenes_pagos_audit_cab` | pendiente |
| 28/10/2026 | tabla `ordenes_pagos_audit_det` | pendiente |
| 29/10/2026 | parámetro `COBERTURA_COD_IOMA` | pendiente |
| 29/10/2026 | columnas `codigo_sistema`, `porcentaje_archivo`, `porcentaje_sistema` en `ordenes_pagos_audit_det` | pendiente |
| 29/10/2026 | corrección: `ordenes_pagos_audit_det.nivel_id` sin FK a `niveles` (MyISAM) | pendiente |
| 02/11/2026 | retiro de `coberturas.id_uom` (solo se usa `cod_uom`) | pendiente |
| 02/11/2026 | renombre `id_uom_archivo`/`id_uom_sistema` a `cod_uom_archivo`/`cod_uom_sistema` en `ordenes_pagos_audit_det` | pendiente |
