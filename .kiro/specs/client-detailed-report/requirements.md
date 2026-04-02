# Documento de Requerimientos — Reporte Detallado por Cliente

## Introducción

Este feature implementa una vista de reporte detallado y visualmente atractivo por cliente en el ERP de MikSoftware. El reporte consolida toda la información financiera y operativa del cliente en una sola página: datos generales, resumen de balance global, desglose de licencias MikPoS con sus mejoras asociadas, proyectos a medida con descripción completa, historial de pagos categorizado, y una línea de tiempo de la relación comercial. La vista reemplaza/complementa el estado de cuenta básico existente (`clients/{client}/statement`) con una versión mucho más completa, organizada por secciones colapsables y optimizada para impresión.

## Glosario

- **Sistema_Reporte**: Módulo del ERP encargado de generar y presentar el reporte detallado por cliente.
- **Cliente**: Entidad `Client` del sistema, puede ser de tipo `final` (cliente directo) o `reseller` (revendedor).
- **Licencia_MikPoS**: Entidad `MikposLicense`, licencia de software con ciclo de facturación, tarifa mensual y estado.
- **Mejora_MikPoS**: Entidad `MikposFeature`, mejora o cambio solicitado al sistema MikPoS con costo y estado.
- **Proyecto_Personalizado**: Entidad `CustomProject`, proyecto de desarrollo a medida con valor de contrato, fechas y estado.
- **Pago**: Entidad `Payment`, abono registrado contra la cuenta corriente del cliente con categoría (global, projects, features).
- **Balance_Global**: Cálculo financiero donde deuda = sum(contract_value de proyectos) + sum(total_cost de mejoras), y pagado = sum(amount de pagos).
- **Saldo_Pendiente**: Diferencia entre Balance_Global de deuda y total pagado, con mínimo de cero.
- **Sección_Colapsable**: Componente de UI que permite expandir o contraer contenido mediante Alpine.js.
- **Vista_Impresión**: Versión optimizada del reporte para impresión vía CSS `@media print`.

## Requerimientos

### Requerimiento 1: Acceso al Reporte Detallado

**Historia de Usuario:** Como administrador de MikSoftware, quiero acceder al reporte detallado de un cliente desde su perfil, para poder consultar toda su información financiera y operativa en un solo lugar.

#### Criterios de Aceptación

1. WHEN el usuario navega a la ruta `clients/{client}/report`, THE Sistema_Reporte SHALL renderizar la vista del reporte detallado para el Cliente especificado.
2. WHEN el usuario se encuentra en la vista de perfil del Cliente (`clients/{client}`), THE Sistema_Reporte SHALL mostrar un botón de acceso directo al reporte detallado.
3. WHEN el usuario se encuentra en la vista de estado de cuenta existente (`clients/{client}/statement`), THE Sistema_Reporte SHALL mostrar un enlace de navegación hacia el reporte detallado.
4. THE Sistema_Reporte SHALL cargar todas las relaciones del Cliente (mikposLicenses con features, customProjects con payments, payments) mediante eager loading para evitar consultas N+1.
5. IF el Cliente solicitado no existe o fue eliminado, THEN THE Sistema_Reporte SHALL redirigir al listado de clientes con un mensaje de error descriptivo.

### Requerimiento 2: Encabezado e Información General del Cliente

**Historia de Usuario:** Como administrador, quiero ver los datos generales del cliente al inicio del reporte, para identificar rápidamente de quién es el reporte.

#### Criterios de Aceptación

1. THE Sistema_Reporte SHALL mostrar en el encabezado del reporte: nombre del Cliente, email, teléfono, nombre de empresa, dirección y tipo de cliente (Final o Revendedor).
2. THE Sistema_Reporte SHALL mostrar la fecha y hora de generación del reporte en formato `dd/mm/YYYY HH:mm`.
3. WHERE el Cliente es de tipo `reseller`, THE Sistema_Reporte SHALL mostrar una insignia visual indicando el tipo Revendedor junto al nombre.
4. THE Sistema_Reporte SHALL mostrar la fecha de registro del Cliente en el sistema (campo `created_at`).

### Requerimiento 3: Resumen Financiero Global

**Historia de Usuario:** Como administrador, quiero ver un resumen financiero consolidado del cliente, para entender de un vistazo cuánto debe, cuánto ha pagado y cuál es su saldo pendiente.

#### Criterios de Aceptación

1. THE Sistema_Reporte SHALL mostrar en tarjetas destacadas: total deuda acumulada, total abonos realizados y saldo pendiente a la fecha.
2. THE Sistema_Reporte SHALL desglosar la deuda total en dos subtotales: deuda por Proyectos_Personalizados (sum de contract_value) y deuda por Mejoras_MikPoS (sum de total_cost).
3. THE Sistema_Reporte SHALL mostrar una barra de progreso visual que represente el porcentaje de pago completado (total_paid / total_global_debt * 100).
4. THE Sistema_Reporte SHALL mostrar el conteo total de pagos registrados para el Cliente.
5. THE Sistema_Reporte SHALL calcular el Saldo_Pendiente como max(0, total_global_debt - total_paid).

### Requerimiento 4: Sección de Licencias MikPoS

**Historia de Usuario:** Como administrador, quiero ver todas las licencias MikPoS del cliente con sus detalles y mejoras asociadas, para tener una visión completa de los productos contratados.

#### Criterios de Aceptación

1. THE Sistema_Reporte SHALL mostrar una Sección_Colapsable titulada "Licencias MikPoS" con el conteo total de licencias del Cliente.
2. WHEN la sección de licencias está expandida, THE Sistema_Reporte SHALL listar cada Licencia_MikPoS con: license_key, site_url, ciclo de facturación (label del enum BillingCycle), tarifa mensual, cuota de instalación, estado (label del enum LicenseStatus) y fecha de activación.
3. WHERE la Licencia_MikPoS tiene `is_free_promotion` en verdadero, THE Sistema_Reporte SHALL mostrar una etiqueta visual "Promoción Gratuita" junto a la licencia.
4. WHEN una Licencia_MikPoS tiene Mejoras_MikPoS asociadas, THE Sistema_Reporte SHALL listar cada mejora anidada bajo la licencia correspondiente con: título, descripción, costo total, estado y fecha de completado o fecha estimada de entrega.
5. THE Sistema_Reporte SHALL mostrar un subtotal del valor mensual recurrente de todas las licencias activas (excluyendo las de promoción gratuita).
6. IF el Cliente no tiene Licencias_MikPoS registradas, THEN THE Sistema_Reporte SHALL mostrar un mensaje indicando "No se han registrado licencias para este cliente."

### Requerimiento 5: Sección de Proyectos a Medida

**Historia de Usuario:** Como administrador, quiero ver todos los proyectos a medida del cliente con su descripción completa, fechas y estado, para entender el alcance de trabajo contratado.

#### Criterios de Aceptación

1. THE Sistema_Reporte SHALL mostrar una Sección_Colapsable titulada "Proyectos a Medida" con el conteo total de proyectos del Cliente.
2. WHEN la sección de proyectos está expandida, THE Sistema_Reporte SHALL mostrar cada Proyecto_Personalizado con: nombre, descripción completa, valor de contrato, estado (label del enum ProjectStatus), fecha de inicio, fecha estimada de fin y fecha real de fin.
3. WHILE un Proyecto_Personalizado tiene el accessor `is_overdue` en verdadero, THE Sistema_Reporte SHALL mostrar una etiqueta visual de alerta "Retrasado" junto al proyecto.
4. THE Sistema_Reporte SHALL mostrar los pagos directamente asociados a cada Proyecto_Personalizado (relación payments del proyecto) con fecha, monto y método de pago.
5. THE Sistema_Reporte SHALL mostrar un subtotal de la deuda total por proyectos (sum de contract_value).
6. IF el Cliente no tiene Proyectos_Personalizados registrados, THEN THE Sistema_Reporte SHALL mostrar un mensaje indicando "No se han registrado proyectos para este cliente."

### Requerimiento 6: Sección de Mejoras MikPoS sin Licencia

**Historia de Usuario:** Como administrador, quiero ver las mejoras MikPoS que no están asociadas a una licencia específica, para tener visibilidad completa de todos los trabajos solicitados.

#### Criterios de Aceptación

1. WHEN existen Mejoras_MikPoS con `mikpos_license_id` nulo para el Cliente, THE Sistema_Reporte SHALL mostrar una Sección_Colapsable titulada "Mejoras Generales MikPoS" listando cada mejora con: título, descripción, costo total, estado y fechas.
2. IF todas las Mejoras_MikPoS del Cliente están asociadas a una licencia, THEN THE Sistema_Reporte SHALL omitir la sección de mejoras generales.

### Requerimiento 7: Historial Completo de Pagos

**Historia de Usuario:** Como administrador, quiero ver el historial completo de pagos del cliente organizado cronológicamente y por categoría, para entender la relación de pagos y abonos realizados.

#### Criterios de Aceptación

1. THE Sistema_Reporte SHALL mostrar una Sección_Colapsable titulada "Historial de Pagos" con el conteo total de pagos del Cliente.
2. THE Sistema_Reporte SHALL listar cada Pago ordenado por fecha descendente con: fecha de pago (formato dd/mm/YYYY), monto, método de pago (label del enum PaymentMethod), categoría (global, projects, features), referencia y notas.
3. WHEN un Pago tiene categoría "projects" y un `custom_project_id` asociado, THE Sistema_Reporte SHALL mostrar el nombre del Proyecto_Personalizado vinculado junto al pago.
4. THE Sistema_Reporte SHALL mostrar subtotales agrupados por categoría de pago: total pagado en categoría global, total pagado en categoría projects y total pagado en categoría features.
5. THE Sistema_Reporte SHALL mostrar el gran total de todos los pagos al final de la sección.
6. IF el Cliente no tiene Pagos registrados, THEN THE Sistema_Reporte SHALL mostrar un mensaje indicando "No se han registrado pagos para este cliente."

### Requerimiento 8: Información de Revendedor

**Historia de Usuario:** Como administrador, quiero ver información específica de la promoción de revendedor cuando el cliente es de ese tipo, para saber cuántas licencias gratuitas ha obtenido y cuántas faltan para la próxima.

#### Criterios de Aceptación

1. WHERE el Cliente es de tipo `reseller`, THE Sistema_Reporte SHALL mostrar una sección adicional con el resumen de revendedor: total de licencias, licencias pagadas, licencias gratuitas, si la próxima licencia es gratuita y cuántas licencias pagadas faltan para la próxima gratuita.
2. WHERE el Cliente es de tipo `final`, THE Sistema_Reporte SHALL omitir la sección de información de revendedor.

### Requerimiento 9: Funcionalidad de Impresión

**Historia de Usuario:** Como administrador, quiero poder imprimir el reporte detallado del cliente, para tener una copia física o PDF del estado completo del cliente.

#### Criterios de Aceptación

1. THE Sistema_Reporte SHALL incluir un botón "Imprimir Reporte" que invoque `window.print()`.
2. WHEN el usuario activa la impresión, THE Sistema_Reporte SHALL aplicar estilos CSS `@media print` que oculten el sidebar, la navegación y los botones de acción.
3. WHEN el usuario activa la impresión, THE Sistema_Reporte SHALL expandir automáticamente todas las Secciones_Colapsables para que el contenido completo sea visible en la versión impresa.
4. THE Sistema_Reporte SHALL incluir el logo de MikSoftware y la fecha de generación en el encabezado de la versión impresa.

### Requerimiento 10: Diseño Visual y Responsividad

**Historia de Usuario:** Como administrador, quiero que el reporte sea visualmente atractivo y se adapte a diferentes tamaños de pantalla, para poder consultarlo cómodamente desde cualquier dispositivo.

#### Criterios de Aceptación

1. THE Sistema_Reporte SHALL utilizar los colores de marca del sistema: primary (#1B0B3B) y accent (#FF7152) de forma consistente con el resto del ERP.
2. THE Sistema_Reporte SHALL utilizar Tailwind CSS para el diseño responsivo, adaptándose a pantallas móviles (< 768px), tablets (768px-1024px) y escritorio (> 1024px).
3. THE Sistema_Reporte SHALL utilizar Alpine.js para la interactividad de las Secciones_Colapsables sin recargar la página.
4. THE Sistema_Reporte SHALL utilizar el layout principal del sistema (`x-app-layout`) para mantener consistencia con el sidebar y la navegación.
5. THE Sistema_Reporte SHALL aplicar indicadores de color por estado: verde para activo/completado, amarillo para pendiente, azul para en progreso, rojo para cancelado/retrasado.

### Requerimiento 11: Endpoint API del Reporte

**Historia de Usuario:** Como desarrollador, quiero tener un endpoint API que retorne los datos del reporte detallado en formato JSON, para poder consumirlos desde otras integraciones o futuras aplicaciones móviles.

#### Criterios de Aceptación

1. WHEN se realiza una petición GET a `api/v1/clients/{client}/report`, THE Sistema_Reporte SHALL retornar un JSON con la estructura estándar `{ success, data, message }` conteniendo todos los datos del reporte.
2. THE Sistema_Reporte SHALL incluir en el campo `data` del JSON: información del cliente, resumen financiero, licencias con mejoras anidadas, proyectos con pagos asociados, historial de pagos y resumen de revendedor (cuando aplique).
3. IF el Cliente solicitado no existe, THEN THE Sistema_Reporte SHALL retornar un código HTTP 404 con un mensaje de error descriptivo.
