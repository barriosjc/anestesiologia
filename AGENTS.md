# Anestesiología — Contexto del proyecto

## Origen y objetivo
- Proyecto originado en Laravel 8, con JS/jQuery tradicional (controllers + blade + jQuery/AJAX).
- Ya migrado a Laravel 11 (upgrade de framework, sin cambios funcionales de código).
- Objetivo actual: reemplazar progresivamente todo el JS/jQuery por Livewire 3,
  migrando módulo por módulo hacia componentes (ver "Migración a Livewire" más abajo).

## Stack
- Laravel 11
- Livewire 3
- Bootstrap 5
- jQuery
- MySQL
- spatie/laravel-permission (RBAC)
- barryvdh/laravel-dompdf (PDF)
- maatwebsite/excel (exports)

## Convenciones
- Modelos en `app/Models/` con nombres en ingés (ej: `Parte_cab`, `Consumo_det`, `PresupuestoCab`)
- Vistas en `resources/views/{modulo}/` (ej: `cargas/cab/`, `presupuestos/presupuestos_cab/`)
- Controladores en `app/Http/Controllers/{modulo}/`
- Servicios en `app/Services/`
- Repositorios en `app/Repositories/`
- Enumeraciones en `app/Enums/`
- Exportaciones en `app/Exports/`

## Módulos principales

### Partes (quirófano)
- `ParteCab` / `ParteDet` — partes quirúrgicos
- Controller: `cargas/ParteController`
- Vistas: `cargas/cab/`, `cargas/det/`
- Permiso: `adm_partes`
- Formulario con búsqueda de paciente por DNI

### Consumos
- `ConsumoCab` / `ConsumoDet` — consumos de insumos/prácticas
- Controller: `produccion/ConsumoController`
- Vistas: `consumo/`
- Permiso: `adm_consumos`
- Incluye rendiciones y revalorizaciones

### Presupuestos
- `PresupuestoCab` / `PresupuestoDet` / `PresupuestoPago`
- Controller: `entidades/PresupuestoCabController`
- Vistas: `presupuestos/presupuestos_cab/`
- Permiso: `adm_presupuestos`
- Estados: I (Ingresado), P (Pagado), C (Cancelado), O (Cobrado), F (Facturado)

### Nomencladores
- `NomPadre`, `Nomenclador`, `Valores`, `PreciosListas`
- Controller: varios en `entidades/`
- Permiso: `adm_consumos`

### Entidades (CRUD)
- `Profesional`, `Centro`, `Cobertura`, `Parametro`, `Paciente`, `Gerenciadora`
- Controller: `entidades/*Controller`
- Vistas: `entidades/{entidad}/`
- Permiso: `adm_entidades`

### Seguridad
- `User`, `Role`, `Permission` (Spatie)
- Controller: `seguridad/`
- Vistas: `seguridad/`
- Permiso: `adm_permisos`

## Migración a Livewire (en progreso)
- Livewire 3 instalado, sin componentes aún
- Directorios: `app/Livewire/` y `resources/views/livewire/`
- Nombre de componentes: kebab-case (ej: `parte-create`, `presupuesto-table`)

### Plan de migración
1. **Partes** — crear/edit/listar (piloto)
2. **Presupuestos** — tabla con filtros reactivos
3. **Consumos** — carga dinámica con búsqueda
4. **Nomenclador/Valores** — CRUD con modales
5. Entidades CRUD (si aplica)
6. Seguridad (si aplica)

No migrar: Reportes PDF/Excel, Calendar, Auth.
