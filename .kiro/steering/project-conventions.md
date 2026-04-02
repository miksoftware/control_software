---
inclusion: always
---

# MikSoftware Control System — Convenciones del Proyecto

## Descripción General

ERP interno de MikSoftware para gestión de clientes, licencias MikPoS, mejoras/cambios, proyectos a medida y pagos/abonos con sistema de cuenta corriente global.

## Stack Tecnológico

- PHP 8.3+ con `declare(strict_types=1)` en TODOS los archivos PHP
- Laravel 13.x (Breeze para auth scaffolding)
- Laravel Sanctum 4.0 (autenticación API)
- Vite 8.0 con laravel-vite-plugin
- Tailwind CSS 3.x con plugin @tailwindcss/forms
- Alpine.js 3.x para interactividad en frontend
- SQLite (desarrollo), configurable a MySQL/PostgreSQL
- PHPUnit 12.x para testing (SQLite :memory: en tests)
- Laravel Pint para code style

## Arquitectura

### Patrón: MVC + Service Layer

- **Modelos** (`app/Models/`): Eloquent con SoftDeletes, Enums como casts, Accessors, Scopes
- **Controladores API** (`app/Http/Controllers/`): Retornan `JsonResponse` con estructura `{ success, data, message }`
- **Controladores Web** (`app/Http/Controllers/Web/`): Retornan `View` o `RedirectResponse` con flash messages
- **Servicios** (`app/Services/`): Lógica de negocio compleja (PaymentService, ResellerLicenseService)
- **Form Requests** (`app/Http/Requests/`): Validación con mensajes en español
- **Enums** (`app/Enums/`): PHP 8.1 backed enums con métodos `label()`, `color()`, `icon()`

### Controladores Duales

Cada recurso tiene DOS controladores:
1. `app/Http/Controllers/{Resource}Controller.php` → API JSON (prefijo `/api/v1/`)
2. `app/Http/Controllers/Web/{Resource}WebController.php` → Vistas Blade

## Convenciones de Código

### PHP

- `declare(strict_types=1)` obligatorio en todo archivo PHP
- Type hints completos en parámetros y retornos
- Docblocks con `@param`, `@return`, `@throws`
- Inyección de dependencias via constructor con `readonly`
- `DB::transaction()` para operaciones atómicas
- `lockForUpdate()` cuando hay riesgo de race conditions

### Nomenclatura

| Elemento | Convención | Ejemplo |
|---|---|---|
| Modelos | Singular, PascalCase | `Client`, `MikposLicense`, `CustomProject` |
| Controladores API | `{Resource}Controller` | `ClientController`, `PaymentController` |
| Controladores Web | `{Resource}WebController` en `Web/` | `ClientWebController`, `LicenseWebController` |
| Servicios | `{Nombre}Service` con `final class` | `PaymentService`, `ResellerLicenseService` |
| Form Requests | `Store{Resource}Request`, `Update{Resource}Request` | `StoreClientRequest` |
| Enums | PascalCase, backed string | `ClientType`, `BillingCycle`, `LicenseStatus` |
| Migraciones | Timestamp + descripción snake_case | `2026_03_31_000001_create_clients_table.php` |
| Rutas web | Kebab-case, resource routes | `clients`, `licenses`, `features`, `projects`, `payments` |
| Rutas API | Prefijo `v1`, `apiResource` | `api.v1.clients`, `api.v1.licenses` |
| Vistas Blade | Directorio por recurso, kebab-case | `clients/index.blade.php`, `licenses/show.blade.php` |

### Modelos Eloquent

- Usar `$fillable` (no `$guarded`)
- Casts via método `casts(): array` (no propiedad `$casts`)
- Accessors con `Illuminate\Database\Eloquent\Casts\Attribute`
- Scopes como métodos `scope{Name}(Builder $query)`
- Relaciones tipadas: `HasMany`, `BelongsTo`
- SoftDeletes en todos los modelos de dominio

### Respuestas API

Estructura estándar JSON:
```json
{
  "success": true,
  "message": "Mensaje descriptivo en español.",
  "data": {}
}
```

Códigos HTTP usados:
- `200` OK (lectura, actualización)
- `201` Created (creación)
- `409` Conflict (eliminación con dependencias activas)
- `422` Unprocessable Entity (validación, lógica de negocio)

### Respuestas Web

- Redirect con flash `success` o `error` tras operaciones
- `back()->with('error', ...)` para errores de validación de negocio
- `redirect()->route(...)` tras operaciones exitosas

## Dominio de Negocio

### Entidades Principales

1. **Client** — Dos tipos: `final` (cliente directo) y `reseller` (revendedor)
2. **MikposLicense** — Licencias de software con ciclos de facturación y auto-generación de `license_key` (formato `MKP-XXXX-XXXX-XXXX`)
3. **MikposFeature** — Mejoras/cambios solicitados al sistema MikPoS, con costo y estado
4. **CustomProject** — Proyectos de desarrollo a medida con valor de contrato y fechas
5. **Payment** — Abonos a cuenta corriente del cliente, categorizados (global, projects, features)

### Reglas de Negocio Críticas

- **Promoción Revendedor**: Cada 5ta licencia es gratuita (4 pagadas → 1 gratis). Se aplica automáticamente via `ResellerLicenseService`.
- **Cuenta Corriente Global**: Los pagos se registran contra el saldo global del cliente. La deuda se calcula sumando `contract_value` de proyectos + `total_cost` de features.
- **Categorías de Pago**: `global` (sin restricción), `projects` y `features` (no pueden exceder la deuda de su categoría).
- **Eliminación Protegida**: No se puede eliminar un cliente con licencias activas o proyectos activos.
- **Auto-completado de fechas**: Al marcar un proyecto o feature como `completed`, se asigna `actual_end_date` / `completed_at` automáticamente si no se proporciona.

### Relaciones del Modelo de Datos

```
Client (1) ──→ (N) MikposLicense
Client (1) ──→ (N) MikposFeature
Client (1) ──→ (N) CustomProject
Client (1) ──→ (N) Payment
MikposLicense (1) ──→ (N) MikposFeature
CustomProject (1) ──→ (N) Payment (opcional)
```

### Enums del Sistema

| Enum | Valores |
|---|---|
| `ClientType` | `final`, `reseller` |
| `BillingCycle` | `monthly`, `quarterly`, `semi_annual`, `annual` |
| `LicenseStatus` | `active`, `suspended`, `cancelled` |
| `ProjectStatus` | `pending`, `in_progress`, `completed`, `cancelled` |
| `PaymentMethod` | `cash`, `transfer`, `card`, `other` |

## Frontend

### Layout y UI

- Layout principal: `resources/views/layouts/app.blade.php` (sidebar + main content)
- Colores de marca: `primary: #1B0B3B` (morado oscuro), `accent: #FF7152` (naranja)
- Fuente: Inter (Bunny CDN)
- Sidebar responsivo con Alpine.js (`x-data="{ sidebarOpen: false }"`)
- Notificaciones flash con auto-dismiss (4-5 segundos)

### Componentes Blade

Componentes reutilizables en `resources/views/components/`:
- `nav-link-sidebar` — Links de navegación del sidebar
- `primary-button`, `secondary-button`, `danger-button` — Botones estilizados
- `text-input`, `input-label`, `input-error` — Campos de formulario
- `modal`, `dropdown` — Componentes interactivos
- `application-logo` — Logo de la aplicación

### Vistas por Recurso

Cada recurso tiene vistas en su directorio: `index`, `create`, `show`, `edit`
Excepción: `clients/` también tiene `statement.blade.php` (estado de cuenta)

## Rutas

### Web (autenticadas con `auth` + `verified`)
- `GET /dashboard` — Dashboard invocable
- `resource clients` — CRUD completo + `clients/{client}/statement`
- `resource licenses, features, projects` — CRUD completo
- `resource payments` — Sin `edit` ni `update`

### API (prefijo `/api/v1/`)
- `apiResource clients, licenses, features, projects` — CRUD REST
- `apiResource payments` — Sin `update`
- `GET payments/financial-summary` — Resumen financiero por cliente

## Base de Datos

### Convenciones de Migraciones

- Decimales monetarios: `decimal(12, 2)` con default apropiado
- Foreign keys con `constrained()`, `cascadeOnUpdate()`, `restrictOnDelete()`
- Índices compuestos para queries frecuentes: `['client_id', 'status']`, `['client_id', 'is_free_promotion']`
- Soft deletes (`$table->softDeletes()`) en todas las tablas de dominio
- Strings con longitud explícita para campos acotados: `string('status', 20)`

### Esquema de Tablas

- `clients` — name, email (unique), phone, company_name, address, client_type, notes
- `mikpos_licenses` — client_id, license_key (unique, 64), site_url, billing_cycle, monthly_rate, installation_fee, is_free_promotion, status, activated_at, next_billing_at
- `mikpos_features` — client_id, mikpos_license_id (nullable), title, description, total_cost, status, estimated_delivery_at, completed_at
- `custom_projects` — client_id, name, description, contract_value, status, start_date, estimated_end_date, actual_end_date
- `payments` — client_id, category, custom_project_id (nullable), amount, payment_method, reference, notes, paid_at

## Testing

- PHPUnit 12.x con suites `Unit` y `Feature`
- Base de datos: SQLite `:memory:` en tests
- Ejecutar tests: `composer test` o `php artisan test`
- Variables de entorno de test definidas en `phpunit.xml`

## Comandos Útiles

- `composer dev` — Inicia servidor, queue, logs y Vite concurrentemente
- `composer test` — Limpia config y ejecuta tests
- `composer setup` — Instalación completa del proyecto
- `php artisan db:seed` — Ejecuta seeders (crea admin: admin@miksoftware.com)

## Idioma

- Interfaz de usuario, mensajes de validación y comentarios de negocio: **Español**
- Código (variables, métodos, clases): **Inglés**
- Nombres de tablas y columnas: **Inglés**
