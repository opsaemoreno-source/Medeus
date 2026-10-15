# Medeus

Panel interno de analítica de audiencia para OPSA (marcas **La Prensa** y **El Heraldo**). Centraliza en un solo lugar:

- **Suscriptores / usuarios registrados** provenientes de Evolok (plataforma de registro y paywall).
- **Compras y suscripciones** de esos usuarios (también de Evolok).
- **Encuestas** creadas en Typeform y sus respuestas.
- **Estadísticas** cruzadas entre las tres fuentes.
- **Administración de un chatbot analítico** que responde preguntas sobre las respuestas de las encuestas (temas, prompts, versiones y auditoría de conversaciones). Su motor vive en un servicio externo.

Las tres fuentes se enlazan por un mismo identificador de usuario de Evolok (`GUID`, también llamado *User ID* o `userid`/`idUsuario`), que permite saber qué suscriptor respondió cada encuesta y qué compras tiene. Ver [Identificador común de usuario](#identificador-común-de-usuario).

Toda la data de negocio se guarda y consulta en **Google BigQuery** (proyecto `admanagerapiaccess-382213`, dataset `UsuariosOPSA`). La base de datos local de Laravel solo guarda usuarios del panel, sesiones, caché, colas y las tablas del chatbot.

---

## Índice

1. [Stack](#stack)
2. [Arquitectura general](#arquitectura-general)
3. [Instalación y configuración](#instalación-y-configuración)
4. [Módulos funcionales](#módulos-funcionales)
5. [Procesos programados y comandos Artisan](#procesos-programados-y-comandos-artisan)
6. [Modelo de datos en BigQuery](#modelo-de-datos-en-bigquery)
7. [Modelo de datos local (chatbot)](#modelo-de-datos-local-chatbot)
8. [Estructura del código](#estructura-del-código)
9. [Observaciones y deuda técnica](#observaciones-y-deuda-técnica)

---

## Stack

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.2+, Laravel 12 |
| Autenticación | Laravel Breeze (vistas Blade) + columna `users.is_admin` |
| Datos de negocio | Google BigQuery (`google/cloud-bigquery`) |
| BD local | SQLite por defecto (`DB_CONNECTION=sqlite`); el chatbot está pensado para MySQL en producción |
| Frontend | Blade, Bootstrap 5, jQuery, DataTables 1.13 (CDN), Chart.js (CDN), Bootstrap Icons |
| Assets | Laravel Mix (`webpack.mix.cjs`) compila `resources/js/app.js` y `resources/css/app.scss` a `public/` |
| Integraciones | Evolok (API de reportes), Typeform (API REST), API externa del chatbot (backend Python + Vertex AI) |

---

## Arquitectura general

```
                 ┌──────────────┐        ┌──────────────┐
                 │   Evolok     │        │   Typeform   │
                 │ (usuarios y  │        │ (encuestas y │
                 │   compras)   │        │  respuestas) │
                 └──────┬───────┘        └──────┬───────┘
     comandos evolok:*  │                       │  EncuestaProcessorService
     (cron diario)      ▼                       ▼  (manual + cron diario)
                 ┌─────────────────────────────────────────┐
                 │  BigQuery  admanagerapiaccess-382213     │
                 │  dataset UsuariosOPSA                    │
                 │  tablas base + _Update + vistas vta_*    │
                 └───────────────────┬─────────────────────┘
                                     │ consultas SQL (servicios)
                                     ▼
┌───────────────┐   HTTP    ┌──────────────────────────────┐
│ API chatbot   │◄──────────│        Medeus (Laravel)       │
│ (Python,      │  sync de  │  Suscriptores · Compras ·     │
│  Vertex AI)   │  temas    │  Encuestas · Estadísticas ·   │
│               │──────────►│  Ciudades · Chatbot · Usuarios│
└───────────────┘ escribe   └──────────────┬───────────────┘
                  conversaciones           │
                  y logs en la BD local    ▼
                                    BD local (SQLite/MySQL)
```

Flujo típico:

1. Cada día los comandos `evolok:*` descargan de Evolok los usuarios y compras creados o modificados y los insertan (streaming insert) en BigQuery.
2. Las encuestas de Typeform se dan de alta manualmente desde el panel; las que tienen `autoUpdate` se refrescan cada mañana.
3. Las pantallas del panel consultan BigQuery en vivo a través de los servicios de `app/Services`.
4. El chatbot analítico consulta las respuestas de las encuestas cargadas en BigQuery. Se configura aquí (prompts y contexto por tema) y se sincroniza con la API externa; esa API registra conversaciones, mensajes y logs de IA en la misma BD local, que el panel muestra para auditoría.

---

## Instalación y configuración

### Requisitos

- PHP 8.2+ con extensiones habituales de Laravel (`pdo_sqlite` o `pdo_mysql`, `mbstring`, `openssl`, `curl`, `json`).
- Composer.
- Node.js (solo si se van a recompilar CSS/JS).
- Una cuenta de servicio de Google con permisos de lectura/escritura sobre el dataset `UsuariosOPSA`.

### Pasos

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

Colocar el JSON de la cuenta de servicio de BigQuery en `storage/app/google/bigquery.json` (o indicar otra ruta con `GOOGLE_BIGQUERY_KEY_PATH`).

Los assets compilados ya están versionados en `public/css` y `public/js`. Para recompilarlos:

```bash
npm install
npm run dev        # desarrollo
npm run watch      # desarrollo, recompila al guardar
npm run build      # producción (minificado)
```

Los scripts usan Laravel Mix con [webpack.mix.cjs](webpack.mix.cjs) y generan `public/js/app.js`, `public/css/app.css` y `public/mix-manifest.json`.

### Variables de entorno propias del proyecto

Además de las estándar de Laravel (`APP_*`, `DB_*`, `SESSION_*`, etc.):

| Variable | Uso |
|---|---|
| `GOOGLE_PROJECT_ID` | Proyecto de BigQuery. Por defecto `admanagerapiaccess-382213`. |
| `BIGQUERY_DATASET` | Dataset de BigQuery. Por defecto `UsuariosOPSA`. |
| `GOOGLE_BIGQUERY_KEY_PATH` | Ruta al JSON de la cuenta de servicio. Por defecto `storage/app/google/bigquery.json`. |
| `TYPEFORM_TOKEN` | Token personal de la API de Typeform. |
| `EVOLOK_BASE_URL` | URL base de Evolok. Por defecto `https://gro.evolok.net`. |
| `EVOLOK_USERNAME` / `EVOLOK_PASSWORD` | Credenciales de consola de Evolok usadas para los reportes. |
| `EVOLOK_TIMEOUT` | Timeout HTTP en segundos (por defecto 300). |
| `CHATBOT_API_URL` | URL base de la API del chatbot (se llama a `/admin/sync-topic` y `/admin/deactivate-topic/{slug}`). |
| `CHATBOT_API_KEY` | Se envía en la cabecera `X-API-KEY` a la API del chatbot. |

### Actualizar el servidor

Después de cada `git pull`, regenerar la caché de configuración; si no, Laravel sigue usando la configuración anterior y no ve claves nuevas de `config/`:

```bash
php artisan config:clear
php artisan config:cache   # si producción usa caché de configuración
```

### Scheduler

Los procesos automáticos dependen del scheduler de Laravel. En el servidor debe existir un cron (o tarea programada de Windows) que ejecute cada minuto:

```bash
php artisan schedule:run
```

### Primer usuario administrador

No hay seeder de administrador. Crear el usuario (registro o `tinker`) y marcar `is_admin = 1` en la tabla `users`. Solo los administradores ven y usan el módulo **Usuarios**.

---

## Módulos funcionales

Todas las rutas están en [routes/web.php](routes/web.php) y requieren sesión iniciada salvo donde se indica. `/` redirige al dashboard o al login. El dashboard ([resources/views/dashboard.blade.php](resources/views/dashboard.blade.php)) es un menú de tarjetas hacia cada módulo.

### Suscriptores (`/suscriptores`)

Listado de usuarios registrados en Evolok, leído de la vista `vta_usuariosEvolok`.

- **Controlador:** [SuscriptoresController](app/Http/Controllers/SuscriptoresController.php) · **Servicio:** [BigQueryService](app/Services/BigQueryService.php)
- Tabla DataTables con paginación del lado del servidor (`/suscriptores/data`).
- Filtros: texto (nombre), rango de fecha de creación, género, país, ciudad, canal, marca (incluye "sin marca"), rango de edad, nivel educativo, profesión y estado civil.
- `/suscriptores/catalogos` entrega los catálogos (`data_estadoCivil`, `data_nivelEducativo`, `data_profesion`, `data_paises`).
- `/suscriptores/exportar` descarga un CSV con todos los campos de los usuarios que cumplen los filtros.
- `/suscriptores/estadistica` devuelve el conteo de registros por día.

### Compras (`/compras`)

Compras y suscripciones de Evolok cruzadas con los datos del usuario.

- **Controlador:** [ComprasController](app/Http/Controllers/ComprasController.php) · **Servicio:** [ComprasService](app/Services/ComprasService.php)
- Lee la vista `vta_Compras` (estado actual de cada compra) y `vta_ComprasHistorico` para ingresos históricos.
- Filtros: estado, marca, canal, tipo de pago, producto, plan, texto y rango de fecha de inicio de suscripción.
- Calcula totales de ingresos por moneda (`precioFinal × cantidad` de las compras en estado `ACTIVE`) y permite exportar a CSV (`/compras/exportar`).

### Encuestas (`/encuestas`)

Integración con Typeform.

- **Controlador:** [EncuestasController](app/Http/Controllers/EncuestasController.php) · **Servicio:** [EncuestaProcessorService](app/Services/EncuestaProcessorService.php)
- El listado muestra las encuestas registradas en `EncuestasTypeform` con su número de respuestas (tokens distintos en `EncuestasTypeformDetalle`).
- **Procesar** (`POST /encuestas/procesar`): alta de una encuesta nueva a partir de su ID de Typeform. Descarga metadatos del formulario, inserta la fila base y luego todas las respuestas (paginadas de 1000 en 1000) en la tabla de detalle, en lotes de 500 filas. Falla si el formulario ya existe.
- **Actualizar** (`POST /encuestas/actualizar`): descarga todas las respuestas de Typeform y solo inserta las cuyo `token` aún no existe en BigQuery (así también rellena huecos).
- **Auto-actualización** (`POST /encuestas/sync-auto-update`): activa o desactiva la bandera `autoUpdate` de cada encuesta; las marcadas se actualizan cada día a las 06:00.
- Cada respuesta se aplana a una fila por pregunta: `userid`, pregunta, tipo, respuesta normalizada, fechas de inicio/fin, `token`, `user_agent` y plataforma.
- `userid` viene del campo oculto `hidden.userid` del formulario. Cuando la encuesta se responde desde los sitios de La Prensa o El Heraldo, ese campo se llena con el GUID de Evolok leído de las cookies del sitio, lo que identifica al suscriptor que respondió. Si el usuario no tiene sesión, queda como `GUEST` y la respuesta solo se distingue por su `token`.

### Estadísticas (`/estadisticas`)

Hub con pestañas que se cargan por AJAX y devuelven HTML parcial.

- **Controlador:** [EstadisticasController](app/Http/Controllers/EstadisticasController.php)
- `/estadisticas/encuestas` ([EncuestasService](app/Services/EncuestasService.php)): KPIs (usuarios registrados vs. invitados que respondieron, total, % de completación) y demografía de quienes respondieron (género, ciudad normalizada, país, nivel educativo, etc.).
- `/estadisticas/suscriptores` ([SuscriptoresService](app/Services/SuscriptoresService.php)): distribución de suscriptores por atributos y por ciudad (original o normalizada).
- `/estadisticas/compras` ([ComprasService](app/Services/ComprasService.php)): estadísticas de compras y de compras activas.
- `/estadisticas/avanzado` ([EstadisticasAvanzadasService](app/Services/EstadisticasAvanzadasService.php)): cruces entre fuentes (usuarios con compra y encuesta, conversión encuesta → compra, compras por respuesta, top países por perfil y por IP, top ciudades, profesiones, niveles educativos). Los filtros se construyen con [UsuariosSegmentacionService](app/Services/UsuariosSegmentacionService.php).
- Gráficos con Chart.js.

### Ciudades (`/cities`)

Mantenimiento del catálogo de normalización de ciudades (`catalogo_ciudadesNormalizacion`), que alimenta la vista `vta_ciudadesNormalizadas`. Sirve para agrupar variantes escritas por los usuarios ("Tegus", "TEGUCIGALPA M.D.C.", …) bajo una ciudad canónica.

- **Controlador:** [CityAliasController](app/Http/Controllers/CityAliasController.php) · **Servicio:** [CityAliasService](app/Services/CityAliasService.php) · **Repositorio:** [CityAliasRepository](app/Repositories/CityAliasRepository.php)
- Listado paginado filtrable por ciudad canónica y estado, alta y edición de alias, búsqueda de canónicas y verificación de duplicados.
- En las consultas, el alias se compara contra la ciudad del usuario en mayúsculas, sin acentos y sin caracteres no alfanuméricos (ver `EncuestasService::demografia`).

### Chatbot (`/chatbot`)

Administración de un chatbot que responde preguntas en lenguaje natural sobre las respuestas de las encuestas, generando SQL sobre BigQuery. Su fuente son los datos de Typeform cargados por el módulo Encuestas (`EncuestasTypeformDetalle`), que pueden cruzarse con el perfil del suscriptor mediante el GUID. La tabla que puede consultar cada tema se define en `config_json.allowed_table`. El motor (backend Python con Vertex AI, embebido en Looker Studio mediante iframe) **no forma parte de este repositorio**; Medeus solo lo configura y audita.

- **Temas** ([ChatbotController](app/Http/Controllers/ChatbotController.php), modelo [ChatbotTopic](app/Models/ChatbotTopic.php)): cada tema tiene nombre, `slug`, estado activo y:
  - `config_json`: `token`, `allowed_table` (tabla permitida) y `out_of_scope_answer`.
  - Cinco prompts: `analysis_prompt`, `business_context`, `dataset_context`, `sql_base_prompt`, `validation_prompt`. Al crear un tema se precargan con plantillas de [ChatbotDefaults](app/Defaults/ChatbotDefaults.php).
- **Versionado:** al crear un tema se guarda una versión; al editar, si algo cambió, se guarda el estado *anterior* en `chatbot_topic_versions`. Desde `/chatbot/{topic}/versions` se puede restaurar una versión.
- **Sincronización** ([ChatbotSyncService](app/Services/ChatbotSyncService.php)): crear, editar, duplicar, activar o restaurar una versión de un tema hace `POST {CHATBOT_API_URL}/admin/sync-topic`; desactivar llama a `/admin/deactivate-topic/{slug}`. El resultado queda en `sync_status` (`synced`, `error`, `disabled`), `sync_error` y `synced_at`.
- **Conversaciones** ([ChatbotConversationController](app/Http/Controllers/ChatbotConversationController.php)): listado filtrable por tema y `session_id` con conteo de mensajes, y detalle de cada conversación.
- **Ejecución** ([ChatbotExecutionController](app/Http/Controllers/ChatbotExecutionController.php)): `/chatbot/conversations/{id}/execution` muestra los logs de IA (`chatbot_ai_logs`) agrupados por mensaje: etapa, prompt enviado, respuesta, éxito y tipo de error.

### Usuarios (`/users`) y perfil

- CRUD de usuarios del panel ([UserController](app/Http/Controllers/UserController.php)), restringido a administradores por el middleware `admin` ([AdminMiddleware](app/Http/Middleware/AdminMiddleware.php)), que registra en el log los intentos no autorizados.
- `/profile`: edición de datos propios, contraseña y eliminación de cuenta (Breeze).

---

## Procesos programados y comandos Artisan

Definidos en [routes/console.php](routes/console.php). Todos corren con `withoutOverlapping` y en segundo plano.

| Hora | Comando | Qué hace |
|---|---|---|
| 03:30 | `chatbot:purgar-conversaciones-vacias` | Borra conversaciones del chatbot sin mensajes, logs de IA ni resultados de consulta con más de 24 h de antigüedad. |
| 06:00 | `encuestas:actualizar` | Ejecuta `actualizarEncuesta` sobre cada encuesta con `autoUpdate = TRUE`. |
| 06:10 | `evolok:usuarios` | Usuarios **creados** en el día operativo anterior → `UsuariosEvolok`. |
| 06:15 | `evolok:compras` | Compras **creadas** en el día operativo anterior → `Compras`. |
| 18:00 | `evolok:usuarios-actualizados` | Usuarios **modificados** en el día operativo anterior → `UsuariosEvolok_Update`. |
| 18:05 | `evolok:compras-actualizadas` | Compras **modificadas** en el día operativo anterior → `Compras_Update`. |

**Día operativo:** de 06:00 del día anterior a 05:59:59.999 del día actual (hora del servidor), convertido a UTC para las consultas Mongo de Evolok ([EvolokDates](app/Services/Evolok/EvolokDates.php)). Se excluyen usuarios del `admin_realm`.

### Comandos manuales

```bash
# Re-ejecutar un día concreto de Evolok
php artisan evolok:usuarios 2026-10-01
php artisan evolok:compras 2026-10-01

# Backfill de un rango de fechas (inclusive)
php artisan evolok:reprocesar 2026-09-01 2026-09-30
php artisan evolok:reprocesar 2026-09-01 2026-09-30 --solo=usuarios-actualizados,compras-actualizadas

# Borrar y recargar completo el detalle de una o varias encuestas
php artisan encuestas:reprocesar FORM_ID_1 FORM_ID_2 [--force]

# Ver qué conversaciones vacías se borrarían, sin borrar
php artisan chatbot:purgar-conversaciones-vacias --dry-run --horas=48 --lote=500
```

Notas:

- `evolok:reprocesar` admite en `--solo`: `usuarios`, `compras`, `usuarios-actualizados`, `compras-actualizadas` (por defecto `usuarios,compras`). Inserta filas nuevas; no borra lo que ya exista para ese día, así que puede generar duplicados en las tablas base (las vistas `vta_*` se quedan con la versión más reciente por ID).
- `encuestas:reprocesar` descarga todo de Typeform **antes** de borrar. BigQuery no permite `DELETE` sobre filas que siguen en el *streaming buffer*: si la encuesta se actualizó hace menos de ~90 minutos, el comando falla con un mensaje explicativo.
- `chatbot:purgar-conversaciones-vacias` exige que no existan `chatbot_ai_logs` asociados porque esa FK no tiene `cascadeOnDelete`; mensajes, resultados y gráficos sí se borrarían en cascada, por eso también se verifican.

### Integración con Evolok

[EvolokClient](app/Services/Evolok/EvolokClient.php) se autentica con `POST /console/api/auth` (cookie de sesión en memoria) y luego ejecuta `PUT /rm/rest/reports-creator/query-result` con una consulta estilo Mongo y la lista de columnas (`@columns`). Los resultados se transforman con [EvolokUsuariosMapper](app/Services/Evolok/EvolokUsuariosMapper.php) y [EvolokComprasMapper](app/Services/Evolok/EvolokComprasMapper.php) (una fila por compra) y se insertan con [EvolokSyncCommand::insertRows](app/Console/Commands/Evolok/EvolokSyncCommand.php). Las fechas nulas o inválidas se guardan como `0001-01-01 00:00:00`.

---

## Modelo de datos en BigQuery

Proyecto `admanagerapiaccess-382213`, dataset `UsuariosOPSA`.

| Objeto | Tipo | Origen / uso |
|---|---|---|
| `UsuariosEvolok` | Tabla | Usuarios nuevos (`evolok:usuarios`). |
| `UsuariosEvolok_Update` | Tabla | Estados posteriores de usuarios (`evolok:usuarios-actualizados`). |
| `vta_usuariosEvolok` | Vista | Último estado de cada usuario. La consultan casi todos los módulos. |
| `Compras` | Tabla | Compras nuevas (`evolok:compras`). |
| `Compras_Update` | Tabla | Estados posteriores de compras (`evolok:compras-actualizadas`). |
| `vta_Compras` | Vista | Último estado de cada compra (módulo Compras). |
| `vta_ComprasHistorico` | Vista | Usada para ingresos históricos (definición no incluida aquí). |
| `EncuestasTypeform` | Tabla | Una fila por encuesta: `id`, `titulo`, `fechaCreacion`, `fechaPublicacion`, `noCampos`, `noRespuestas`, `autoUpdate`. |
| `EncuestasTypeformDetalle` | Tabla | Una fila por respuesta a pregunta: `userid`, `idEncuesta`, `pregunta`, `idPregunta`, `tipoPregunta`, `respuesta`, `idRespuesta`, `tipoRespuesta`, `fechaInicio`, `fechaFin`, `token`, `user_agent`, `platform`. |
| `catalogo_ciudadesNormalizacion` | Tabla | Alias → ciudad canónica (módulo Ciudades). |
| `vta_ciudadesNormalizadas` | Vista | Catálogo normalizado con `alias_norm` y `ciudad_canonica`. |
| `data_paises`, `data_profesion`, `data_nivelEducativo`, `data_estadoCivil` | Tablas | Catálogos (id → etiqueta). |

### Identificador común de usuario

El GUID de Evolok es la llave que une usuarios, compras y encuestas:

| Tabla / vista | Campo |
|---|---|
| `UsuariosEvolok`, `UsuariosEvolok_Update`, `vta_usuariosEvolok` | `userid` |
| `Compras`, `Compras_Update`, `vta_Compras` | `idUsuario` |
| `EncuestasTypeformDetalle` | `userid` (`GUEST` si no se identificó al usuario) |

En Typeform el GUID llega como campo oculto (`hidden.userid`), tomado de las cookies de los sitios de La Prensa y El Heraldo al momento de responder. Gracias a eso se puede saber qué suscriptores respondieron una encuesta, su perfil demográfico y qué compraron. Los cruces de **Estadísticas → Encuestas** y **Estadísticas → Avanzado** (usuarios con compra y encuesta, conversión encuesta → compra, compras por respuesta) se basan en este enlace:

```sql
SELECT d.idEncuesta, d.pregunta, d.respuesta, u.genero, c.nombreProductoDisplay
FROM `UsuariosOPSA.EncuestasTypeformDetalle` d
JOIN `UsuariosOPSA.vta_usuariosEvolok` u ON u.userid = d.userid
LEFT JOIN `UsuariosOPSA.vta_Compras` c ON c.idUsuario = d.userid
WHERE d.userid != 'GUEST'
```

### Patrón base + `_Update`

Las tablas se llenan solo con *streaming inserts* (nunca se actualizan). Cada cambio de un usuario o compra se agrega como fila nueva en la tabla `_Update`, y las vistas se quedan con la fila más reciente por ID usando `ROW_NUMBER()` sobre `fechaModificacion`.

### Esquema `UsuariosEvolok` / `UsuariosEvolok_Update`

Ambas tablas tienen el mismo esquema.

| Campo | Tipo | Atributo Evolok |
|---|---|---|
| `userid` | STRING | `GUID` |
| `fechaCreacion` | DATETIME | `Created` |
| `fechaModificacion` | DATETIME | `LastModified` |
| `marca` | STRING | `Brand` |
| `dni` | STRING | `dni` |
| `fechaNacimiento` | DATE | `dob` |
| `correo` | STRING | `email_address` |
| `estadoCivil` | STRING | `estadocivil` |
| `nombre` | STRING | `first_name` |
| `apellido` | STRING | `last_name` |
| `genero` | STRING | `gender` |
| `nivelEducativo` | STRING | `nivel_educativo` |
| `telefono` | STRING | `mobile_number` |
| `profesion` | STRING | `profesion` |
| `compra` | STRING | `purchased` |
| `pais` | STRING | `visitor_country` (país por IP) |
| `ciudad` | STRING | `address1_city` |
| `paisPerfil` | STRING | `address1_country` |
| `direccion` | STRING | `address1_line1` |
| `estado` | STRING | `State` |
| `ultimaSesion` | DATETIME | último valor de `LastLoginDate` |
| `canal` | STRING | `ProfileChannel` |
| `suscripcionActiva` | STRING | `active_subscription` |
| `la_prensa` | STRING | `la_prensa` |
| `el_heraldo` | STRING | `el_heraldo` |
| `tandc` | STRING | `tandc` |
| `mauticId` | STRING | `mauticId` |
| `newsletter_amiga` | STRING | `newsletter_amiga` |
| `newsletter_deportes` | STRING | `newsletter_deportes` |
| `newsletter_medicina` | STRING | `newsletter_medica` |
| `newsletter_saber` | STRING | `newsletter_saber` |

### Esquema `Compras` / `Compras_Update`

Ambas tablas tienen el mismo esquema; todos los campos son `NULLABLE`.

| Campo | Tipo | Origen en Evolok |
|---|---|---|
| `idUsuario` | STRING | `GUID` del usuario |
| `idCompra` | STRING | `purchase.id` |
| `idProducto` | STRING | `orderItem.product.id` |
| `nombreProducto` | STRING | `product.name` |
| `nombreProductoDisplay` | STRING | `product.displayName` |
| `tipoProducto` | STRING | `product.type` |
| `tipoProducto2` | STRING | `product.productType` |
| `fechaCreacion` | STRING | `product.createdDate` |
| `fechaModificacion` | DATETIME | `product.lastModifiedDate` |
| `idPlanPago` | STRING | `paymentPlan.id` |
| `nombrePlanPago` | STRING | `paymentPlan.name` |
| `nombrePlanPagoDisplay` | STRING | `paymentPlan.displayName` |
| `idFrecuencia` | STRING | `frequency.id` |
| `noDias` | STRING | `frequency.numberOfDays` |
| `tipoPeriodo` | STRING | `frequency.periodType` |
| `nombreFrecuencia` | STRING | `frequency.displayName` |
| `precio` | FLOAT | `paymentPlan.price` |
| `precioFinal` | FLOAT | `paymentPlan.finalPrice` |
| `idMoneda` | STRING | `paymentPlan.currency.id` |
| `idDescuento` | STRING | `discount.id` |
| `tipoDescuento` | STRING | `discount.type` |
| `valorDescuento` | FLOAT | `discount.value` |
| `codigosPromocion` | STRING | `paymentPlan.promoCodes` (JSON) |
| `cantidad` | STRING | `orderItem.quantity` |
| `fechaInicioEntrega` | DATETIME | `startDeliveryDate` |
| `fechaInicioSuscripcion` | DATETIME | `subscription.startDate` |
| `fechaFinalSuscripcion` | DATETIME | `subscription.endDate` |
| `tipoPago` | STRING | `paymentType` |
| `tipoOrden` | STRING | `orderType` |
| `marca` | STRING | `brand` |
| `canal` | STRING | `channel` |
| `estado` | STRING | `status` |
| `ultimaFechaPago` | DATETIME | `lastPaymentDate` |
| `proximaFechaPago` | DATETIME | `nextPaymentDate` |
| `fechaPedidoCancelacion` | DATETIME | `cancelationRequestDate` |

### Vistas

`vta_usuariosEvolok`:

```sql
SELECT
    userid, fechaCreacion, fechaModificacion, marca, dni, fechaNacimiento,
    correo, estadoCivil, nombre, apellido, genero, nivelEducativo, telefono,
    profesion, compra, pais, ciudad, paisPerfil, direccion, estado,
    ultimaSesion, canal, suscripcionActiva, la_prensa, el_heraldo, tandc,
    mauticId, newsletter_amiga, newsletter_deportes, newsletter_medicina,
    newsletter_saber
FROM (
    SELECT
        *,
        ROW_NUMBER() OVER (
            PARTITION BY userid
            ORDER BY fechaModificacion DESC
        ) AS _rank
    FROM (
        -- Estado base del usuario
        SELECT * FROM `admanagerapiaccess-382213.UsuariosOPSA.UsuariosEvolok`

        UNION ALL

        -- Estados posteriores del usuario
        SELECT * FROM `admanagerapiaccess-382213.UsuariosOPSA.UsuariosEvolok_Update`
    )
)
WHERE _rank = 1
```

Último estado de cada compra (consulta que corresponde a `vta_Compras`):

```sql
SELECT *
FROM (
    SELECT
        *,
        ROW_NUMBER() OVER (
            PARTITION BY idCompra
            ORDER BY fechaModificacion DESC
        ) AS rn
    FROM (
        SELECT * FROM `UsuariosOPSA.Compras`
        UNION ALL
        SELECT * FROM `UsuariosOPSA.Compras_Update`
    )
)
WHERE rn = 1
```

---

## Modelo de datos local (chatbot)

Migraciones en [database/migrations](database/migrations). Además de las tablas estándar de Laravel (`users` con `is_admin`, `sessions`, `cache`, `jobs`, …):

| Tabla | Contenido | Quién escribe |
|---|---|---|
| `chatbot_topics` | Temas: nombre, `slug` único, `active`, `config_json`, los cinco prompts, `sync_status`, `sync_error`, `synced_at`. | Medeus |
| `chatbot_topic_versions` | Histórico de prompts y configuración por tema. | Medeus |
| `chatbot_conversations` | Conversación por `topic_id` + `session_id` (único), `client_id`, `title`, `hidden_at` (el usuario final la ocultó), `last_activity_at`. | API del chatbot |
| `chatbot_messages` | Mensajes (`role`, `content`, `sql_query`, `include_in_context`, `message_status`, `error_type`). | API del chatbot |
| `chatbot_ai_logs` | Cada llamada al modelo: `stage`, `prompt`, `response`, `success`, `error_type`. FK a conversación **sin** borrado en cascada. | API del chatbot |
| `chatbot_query_results` | SQL ejecutado y resultado en JSON. | API del chatbot |
| `chatbot_charts` | Definición JSON de gráficos generados. | API del chatbot |

---

## Estructura del código

```
app/
├── Console/Commands/          Comandos Artisan (encuestas, chatbot)
│   └── Evolok/                Sincronización Evolok → BigQuery
├── Defaults/ChatbotDefaults   Plantillas de prompts para temas nuevos
├── Http/
│   ├── Controllers/           Un controlador por módulo (+ Auth de Breeze)
│   └── Middleware/            AdminMiddleware (alias "admin")
├── Models/                    User y modelos del chatbot
├── Repositories/              CityAliasRepository (BigQuery)
└── Services/                  Lógica y SQL de cada módulo
    └── Evolok/                Cliente HTTP, mappers y utilidades de fecha
resources/views/               Vistas Blade por módulo
routes/web.php                 Rutas del panel
routes/auth.php                Rutas de Breeze + rutas solo admin
routes/console.php             Tareas programadas
```

Los servicios obtienen el `BigQueryClient` compartido (registrado en [AppServiceProvider](app/Providers/AppServiceProvider.php)) y las referencias a tablas a través de [App\Support\BigQuery](app/Support/BigQuery.php), que lee proyecto, dataset y credenciales de `config/services.php`. Los valores que llegan de la petición se pasan como parámetros nombrados (`@nombre`); las fechas se validan (`Y-m-d`, con hora opcional) antes de usarse como literal, porque las columnas de fecha mezclan `TIMESTAMP` y `DATETIME`. Los resultados se pasan a vistas Blade o se devuelven como JSON/HTML parcial para DataTables y Chart.js.

---

## Observaciones y deuda técnica

### Pendiente

- **Historial de git:** `MaDeUsDB` y `MaDeUsDB.sqlite.sql` ya no se versionan, pero siguen en commits anteriores con un usuario, su hash de contraseña y una sesión. Conviene cambiar esa contraseña si se usa en algún entorno.

### Resuelto

- `public/adminer.php` y `public/adminer.5.4.1.php` se eliminaron del repositorio; `MaDeUsDB` y `MaDeUsDB.sqlite.sql` dejaron de versionarse. Los cuatro están en `.gitignore`. Producción usa MySQL, así que quitar `MaDeUsDB` no la afecta.
- `sync_status` y `sync_error` están en `$fillable` de `ChatbotTopic`, así que el resultado de cada sincronización con la API del chatbot se guarda.
- `ComprasController::data` consulta las compras una sola vez por petición.
- El filtro `ciudad` de la segmentación usa `mb_strtoupper`, así que las letras con tilde se comparan igual que en `UPPER(u.ciudad)`.
- `GET /suscriptores/data` exige sesión (`auth`).
- Las consultas a BigQuery usan parámetros nombrados para los valores de la petición; las fechas se validan antes de usarse y los nombres de columna dinámicos se validan como identificadores.
- `Route::resource('users')` queda solo en `routes/auth.php` (`auth` + `admin`). También se quitó la ruta `/estadisticas` duplicada en `routes/web.php`.
- Los KPIs de encuestas reconocen `GUEST` sin distinguir mayúsculas, así que las respuestas anónimas cuentan como invitados.
- Restaurar una versión de un tema del chatbot sincroniza con la API.
- Proyecto, dataset y cliente de BigQuery salen de `config/services.php` (`GOOGLE_PROJECT_ID`, `BIGQUERY_DATASET`); `CHATBOT_API_URL` y `CHATBOT_API_KEY` se leen con `config()`. Funciona con `php artisan config:cache`.
- `CityAliasService::normalize` produce la misma forma que `alias_norm` (mayúsculas, sin acentos, solo `A-Z0-9`) y se usa para detectar duplicados al crear y al editar. El alias se guarda tal como se escribió.
- `npm run dev`, `npm run watch` y `npm run build` compilan con Laravel Mix.
- Se corrigieron dos consultas que fallaban siempre: el filtro de edad de suscriptores (`TIMESTAMP_DIFF` sobre `fechaNacimiento`, que es `DATE`) y las estadísticas de compras (usaban el alias `c` sin declararlo).
- Pruebas: consultas parametrizadas, KPIs de encuestas, alias de ciudades, rutas protegidas y restauración de versiones del chatbot. Usan un cliente de BigQuery falso ([tests/Concerns/FakeBigQuery.php](tests/Concerns/FakeBigQuery.php)), así que no salen a la red.
