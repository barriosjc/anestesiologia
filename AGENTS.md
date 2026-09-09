# Anestesiología — Contexto del proyecto

## Origen y objetivo
- Proyecto originado en Laravel 8, con JS/jQuery tradicional (controllers + blade + jQuery/AJAX).
- Migrado a Laravel 11: esqueleto moderno (`bootstrap/app.php`), Vite, sin jQuery (CDN Bootstrap 5 + JS vanilla).
- Todo el JS/jQuery fue reemplazado por Livewire 3 (ver "Migración a Livewire" más abajo).

## Stack
- Laravel 11
- Livewire 3
- Bootstrap 5 (CDN)
- Vite (sin bundle referenciado en vistas; JS/CSS por CDN)
- MySQL
- spatie/laravel-permission (RBAC)
- barryvdh/laravel-dompdf (PDF)
- maatwebsite/excel (exports)

## Convenciones
- Modelos en `app/Models/` con nombres en ingés (ej: `ParteCab`, `ConsumoDet`, `PresupuestoCab`)
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
- Componente Livewire: `Consumos/Cargar/ConsumoCargarIndex` (carga + cambio de estado)
- Vistas: `consumo/`
- Permiso: `adm_consumos`
- Incluye rendiciones y revalorizaciones

### Presupuestos
- `PresupuestoCab` / `PresupuestoDet` / `PresupuestoPago`
- Componentes Livewire: `Presupuestos/*` (creación de partes vía `Services/PresupuestoParteService`)
- Controller (solo PDF): `entidades/PresupuestoCabController`
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

## Migración a Livewire (completa)
- Livewire 3 instalado; todos los módulos y Auth migrados a componentes
- Directorios: `app/Livewire/` y `resources/views/livewire/`
- Nombre de componentes: kebab-case (ej: `parte-create`, `presupuesto-table`)

<<<<<<< HEAD
=======
## Esqueleto Laravel 11
- `bootstrap/app.php` con `Application::configure()` (routing, middleware y aliases, exceptions)
- Sin `app/Http/Kernel.php`, `app/Console/Kernel.php` ni `app/Exceptions/Handler.php`
- Middleware custom en `app/Http/Middleware/` registrados como aliases en `bootstrap/app.php`
- Rate limiters (`api`, `login`) en `app/Providers/RouteServiceProvider.php`
- Build de assets con Vite (`package.json` + `vite.config.js`); sin jQuery/laravel-mix

>>>>>>> d6c2154c0add594dee2072297cdca7f4bbbc4856
No migrar: Reportes PDF/Excel, Calendar, descargas de archivos, ejecución de migraciones.
