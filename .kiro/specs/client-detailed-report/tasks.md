# Plan de Implementación: Reporte Detallado por Cliente

## Descripción General

Implementar una vista de reporte detallado por cliente que consolida información financiera y operativa en una sola página, con secciones colapsables vía Alpine.js, endpoint API JSON y optimización para impresión. Se crea un servicio dedicado `ClientReportService` que orquesta la agregación de datos reutilizando `PaymentService` y `ResellerLicenseService`.

## Tareas

- [x] 1. Crear ClientReportService con lógica de agregación de datos
  - [x] 1.1 Crear `app/Services/ClientReportService.php` con inyección de `PaymentService` y `ResellerLicenseService`
    - Implementar método `generateReport(Client $client): array`
    - Eager loading de relaciones: `mikposLicenses.features`, `customProjects.payments`, `payments.customProject`, `mikposFeatures`
    - Delegar cálculo financiero a `PaymentService::getFinancialSummary()`
    - Calcular `active_monthly_total`: suma de `monthly_rate` de licencias activas no gratuitas
    - Filtrar mejoras huérfanas (`mikpos_license_id` es null)
    - Agrupar pagos por `category` con subtotales por grupo
    - Invocar `ResellerLicenseService::getResellerSummary()` solo si `client_type === ClientType::Reseller`
    - Generar `generated_at` en formato `dd/mm/YYYY HH:mm`
    - _Requerimientos: 1.4, 3.1, 3.2, 3.3, 3.4, 3.5, 4.5, 6.1, 7.4, 8.1_

  - [ ]* 1.2 Escribir tests unitarios para ClientReportService
    - Verificar que `generateReport` retorna todas las claves esperadas del array
    - Verificar cálculo correcto de `active_monthly_total` excluyendo licencias gratuitas
    - Verificar que `orphan_features` solo contiene mejoras sin `mikpos_license_id`
    - Verificar que `reseller_summary` es null para clientes tipo `final`
    - Verificar agrupación correcta de pagos por categoría con subtotales
    - _Requerimientos: 3.1, 3.2, 3.5, 4.5, 6.1, 6.2, 7.4, 8.1, 8.2_

- [x] 2. Registrar rutas y métodos de controlador
  - [x] 2.1 Agregar ruta web `clients/{client}/report` en `routes/web.php`
    - Registrar ruta con nombre `clients.report` apuntando a `ClientWebController::report`
    - Ubicar la ruta ANTES del `Route::resource('clients', ...)` para evitar conflictos
    - _Requerimientos: 1.1_

  - [x] 2.2 Implementar método `report()` en `app/Http/Controllers/Web/ClientWebController.php`
    - Inyectar `ClientReportService` via constructor con `private readonly`
    - Crear método `report(Client $client): View` que llame a `generateReport()` y retorne `view('clients.report', $reportData)`
    - Manejar cliente eliminado (soft deleted) redirigiendo a `clients.index` con mensaje de error
    - _Requerimientos: 1.1, 1.4, 1.5_

  - [x] 2.3 Agregar ruta API `api/v1/clients/{client}/report` en `routes/api.php`
    - Registrar ruta GET con nombre `api.v1.clients.report` apuntando a `ClientController::report`
    - _Requerimientos: 11.1_

  - [x] 2.4 Implementar método `report()` en `app/Http/Controllers/ClientController.php`
    - Inyectar `ClientReportService` via constructor con `private readonly`
    - Crear método `report(Client $client): JsonResponse` que retorne estructura `{ success, data, message }`
    - Retornar HTTP 404 si el cliente no existe
    - _Requerimientos: 11.1, 11.2, 11.3_

  - [ ]* 2.5 Escribir tests de feature para las rutas web y API del reporte
    - Test GET `clients/{client}/report` retorna vista correcta con status 200
    - Test GET `clients/{client}/report` con cliente inexistente redirige a `clients.index`
    - Test GET `api/v1/clients/{client}/report` retorna JSON con estructura `{ success, data, message }`
    - Test GET `api/v1/clients/{client}/report` con cliente inexistente retorna 404
    - _Requerimientos: 1.1, 1.5, 11.1, 11.2, 11.3_

- [x] 3. Punto de control — Verificar servicio y rutas
  - Asegurar que todos los tests pasan, preguntar al usuario si surgen dudas.

- [x] 4. Crear vista Blade del reporte detallado
  - [x] 4.1 Crear `resources/views/clients/report.blade.php` — Encabezado e información general
    - Usar `<x-app-layout>` con `@section('title', 'Reporte Detallado')`
    - Mostrar breadcrumb con enlace "Volver al perfil" hacia `clients.show`
    - Mostrar nombre del cliente, email, teléfono, empresa, dirección, tipo de cliente
    - Mostrar insignia visual "Revendedor" si `client_type === reseller`
    - Mostrar fecha de registro del cliente (`created_at` formato `dd/mm/YYYY`)
    - Mostrar fecha y hora de generación del reporte (`generated_at`)
    - Incluir botón "Imprimir Reporte" con `onclick="window.print()"`
    - _Requerimientos: 2.1, 2.2, 2.3, 2.4, 9.1_

  - [x] 4.2 Agregar sección de Resumen Financiero Global a la vista
    - Tres tarjetas destacadas: total deuda acumulada, total abonos realizados, saldo pendiente
    - Desglose de deuda: subtotal proyectos (`total_projects_debt`) + subtotal mejoras (`total_features_debt`)
    - Barra de progreso visual con porcentaje de pago (`payment_progress`)
    - Conteo total de pagos registrados (`payments_count`)
    - Usar colores de marca: primary (#1B0B3B) y accent (#FF7152)
    - _Requerimientos: 3.1, 3.2, 3.3, 3.4, 3.5, 10.1_

  - [x] 4.3 Agregar sección condicional de Información de Revendedor
    - Mostrar solo si `reseller_summary` no es null
    - Mostrar: total licencias, licencias pagadas, licencias gratuitas, si la próxima es gratuita, cuántas faltan para la próxima gratuita
    - _Requerimientos: 8.1, 8.2_

  - [x] 4.4 Agregar sección colapsable de Licencias MikPoS
    - Sección colapsable con Alpine.js (`x-data="{ open: false }"`, `x-show`, `x-collapse`)
    - Título "Licencias MikPoS" con conteo total de licencias
    - Listar cada licencia con: license_key, site_url, ciclo de facturación (label), tarifa mensual, cuota de instalación, estado (label), fecha de activación
    - Etiqueta "Promoción Gratuita" si `is_free_promotion` es true
    - Listar mejoras anidadas bajo cada licencia: título, descripción, costo total, estado, fecha completado o estimada
    - Subtotal mensual recurrente de licencias activas no gratuitas (`active_monthly_total`)
    - Mensaje vacío: "No se han registrado licencias para este cliente."
    - Indicadores de color por estado según tabla del diseño
    - _Requerimientos: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 10.3, 10.5_

  - [x] 4.5 Agregar sección colapsable de Proyectos a Medida
    - Sección colapsable con título "Proyectos a Medida" y conteo total
    - Mostrar cada proyecto: nombre, descripción completa, valor de contrato, estado (label), fecha inicio, fecha estimada fin, fecha real fin
    - Etiqueta "Retrasado" si `is_overdue` es true (color rojo)
    - Listar pagos asociados a cada proyecto: fecha, monto, método de pago
    - Subtotal deuda por proyectos (`total_projects_debt`)
    - Mensaje vacío: "No se han registrado proyectos para este cliente."
    - _Requerimientos: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 10.5_

  - [x] 4.6 Agregar sección colapsable condicional de Mejoras Generales MikPoS
    - Mostrar solo si `orphan_features` no está vacío
    - Sección colapsable titulada "Mejoras Generales MikPoS"
    - Listar cada mejora: título, descripción, costo total, estado, fechas
    - _Requerimientos: 6.1, 6.2_

  - [x] 4.7 Agregar sección colapsable de Historial de Pagos
    - Sección colapsable con título "Historial de Pagos" y conteo total
    - Listar cada pago ordenado por fecha descendente: fecha (dd/mm/YYYY), monto, método de pago (label), categoría, referencia, notas
    - Mostrar nombre del proyecto vinculado si categoría es "projects" y tiene `custom_project_id`
    - Subtotales agrupados por categoría: global, projects, features
    - Gran total de todos los pagos al final
    - Mensaje vacío: "No se han registrado pagos para este cliente."
    - _Requerimientos: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6_

- [x] 5. Punto de control — Verificar vista completa
  - Asegurar que todos los tests pasan, preguntar al usuario si surgen dudas.

- [x] 6. Estilos de impresión y responsividad
  - [x] 6.1 Agregar estilos `@media print` en la vista del reporte
    - Ocultar sidebar, header móvil, botones de acción (imprimir, volver)
    - Forzar `display: block` en todas las secciones colapsables (sobreescribir `x-show`)
    - Mostrar logo MikSoftware y fecha de generación en encabezado de impresión (visible solo en print)
    - Aplicar `break-inside: avoid` en tarjetas y tablas
    - _Requerimientos: 9.2, 9.3, 9.4_

  - [x] 6.2 Verificar diseño responsivo con Tailwind CSS
    - Asegurar grid responsivo: 1 columna en móvil, 2-3 columnas en tablet/escritorio
    - Verificar que las tarjetas financieras se adaptan correctamente
    - Verificar que las tablas tienen scroll horizontal en pantallas pequeñas
    - _Requerimientos: 10.2, 10.4_

- [x] 7. Modificar vistas existentes para enlazar al reporte
  - [x] 7.1 Agregar botón "Ver Reporte Detallado" en `resources/views/clients/show.blade.php`
    - Agregar botón junto a los botones existentes ("Estado de Cuenta", "Editar Perfil")
    - Enlazar a `route('clients.report', $client)`
    - Estilo consistente con los botones existentes
    - _Requerimientos: 1.2_

  - [x] 7.2 Agregar enlace "Ver Reporte Completo" en `resources/views/clients/statement.blade.php`
    - Agregar enlace de navegación en el encabezado de la vista
    - Enlazar a `route('clients.report', $client)`
    - _Requerimientos: 1.3_

- [x] 8. Punto de control final — Verificar integración completa
  - Asegurar que todos los tests pasan, preguntar al usuario si surgen dudas.

## Notas

- Las tareas marcadas con `*` son opcionales y pueden omitirse para un MVP más rápido.
- Cada tarea referencia requerimientos específicos para trazabilidad.
- Los puntos de control aseguran validación incremental.
- Todo el texto de UI debe estar en español según las convenciones del proyecto.
- Usar `declare(strict_types=1)` en todos los archivos PHP nuevos.
