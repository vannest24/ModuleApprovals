# Arquitectura del Sistema - Documento Técnico Inicial

Este documento describe la línea base de la arquitectura del sistema, dividida en sus tres capas principales: Persistencia, Lógica y Presentación. Este documento se mantendrá actualizado de forma incremental conforme evolucione el proyecto.

---

## 1. Capa de Base de Datos y Persistencia

### Modelo Entidad-Relación (E-R)
El esquema relacional actual gestiona las solicitudes de cambio de documentos (DCR), los usuarios y el flujo de aprobaciones de forma asíncrona. Las relaciones estructurales principales son:
- Un Usuario (Originador) puede crear múltiples `SolicitudesDcr`.
- Una `SolicitudDcr` tiene un `Documento` asociado (Relación Uno a Uno).
- Una `SolicitudDcr` desencadena múltiples `Aprobaciones` (Relación Uno a Muchos).
- Los `Usuarios` pertenecen a un `Area` específica para segmentación organizacional.

### Entidades (Doctrine ORM)
Las clases PHP mapeadas a la base de datos incluyen:
- **`SolicitudesDcr`**: Entidad principal que almacena detalles del trámite como nombre del documento, número de revisión, fecha límite, razón de cambio, estado general y sus relaciones con el originador, documento físico y aprobadores asignados.
- **`Aprobaciones`**: Registra el dictamen técnico individual. Contiene el estatus de la aprobación (PENDIENTE, APROBADA, RECHAZADA), fecha de respuesta y comentarios justificativos.
- **`User`**: Entidad nativa de seguridad de Symfony. Gestiona credenciales de acceso, roles (`ROLE_USER`, `ROLE_APROBADOR`) y se vincula al perfil extendido del empleado (`Usuario`).
- **`Documento` y `Area`**: Centralizan la metadata del archivo nativo y la estructura de departamentos, respectivamente.

### Controladores y Operaciones CRUD
- **`OriginHomeController`**: 
  - *Create*: Inserta nuevas `SolicitudesDcr`, persiste el `Documento` subido al servidor y genera los registros en bloque de `Aprobaciones` en estatus Pendiente.
  - *Read*: Consulta y ordena cronológicamente las solicitudes del usuario autenticado para el panel de historial.
- **`ApprovalHomeController`**:
  - *Read*: Recupera la lista de solicitudes pendientes mediante `QueryBuilder` filtrando por el aprobador autenticado.
  - *Update*: Modifica los estados de `Aprobaciones` y la cabecera `SolicitudesDcr` basándose en el dictamen emitido.

---

## 2.  Capa de Backend y Lógica de Negocio

### Flujo de Envío de Datos
El ciclo de vida del procesamiento en los controladores (Ej. OriginHomeController) opera bajo el patrón PRG (Post/Redirect/Get):
1. El controlador intercepta la petición verificando el método (`$request->isMethod('POST')`).
2. Se instancian las entidades y se inyectan los datos capturados.
3. Se procesan archivos nativos a través de la clase `UploadedFile` de Symfony, moviéndolos a la carpeta pública (`public/uploads/documentos`).
4. El `EntityManager` de Doctrine almacena en caché temporal los objetos (`persist()`) y ejecuta la transacción a la base de datos (`flush()`).
5. Se emite un mensaje en sesión (`addFlash()`) y se redirige la vista para evitar envíos duplicados de formularios.

### Configuración del Entorno
- **Archivo `.env`**: Centraliza las variables globales inyectadas al framework. Aquí se define la cadena de conexión primaria (`DATABASE_URL`), el secreto de encriptación (`APP_SECRET`) y el entorno de despliegue (`APP_ENV`).

### Peticiones HTTP ("GET" y "POST")
- **GET**: Expone las interfaces visuales (ej. formularios vacíos o listados). Endpoints: `/origin/nuevo`, `/approval/home`.
- **POST**: Ejecuta la lógica de mutación de datos.
  - *Payload Originador*: Captura `nombre_documento`, `numero_revision`, `razon_cambio`, `fecha_limite`, array de `aprobadores[]` y el `documento_file`.
  - *Payload Aprobador*: Captura `solicitud_id`, la decisión lógica (`accion`) y `comentarios`.

---

## 3.  Capa de Frontend y Presentación

### Plantillas Twig
La capa visual usa el motor Twig para componer pantallas modulares y dinámicas:
- **`base.html.twig`**: Plantilla maestra (Layout) que importa librerías globales, hojas de estilo principales, barras de navegación y define los bloques base.
- **Módulo Originador (`origin_home/`)**: Contiene `nuevo.html.twig` para la captura de datos y `historial.html.twig` para el renderizado del seguimiento y visualización de modales.
- **Módulo Aprobador (`approval_home/`)**: Agrupa `index.html.twig` (panel de tareas) y `detalle.html.twig` (vista profunda para emitir dictámenes).

### Estilos CSS
El diseño se gestiona con archivos granulares ubicados en `public/css/`:
- **`formularios.css`**: Define los estilos de las tarjetas (cards), transiciones de inputs, y la personalización visual profunda del selector avanzado (Tom Select).
- **`historial.css`**: Controla el diseño del DataGrid (tabla), badges con colores semánticos por estado de solicitud (Amarillo, Verde, Rojo), y animaciones de modales flotantes.
- **`aprobaciones.css`**: Contiene layouts para las interfaces de decisión y paneles informativos del dictamen técnico.

### Scripts e Integraciones
- **Framework UI**: Se emplea Bootstrap 5.3 para los componentes estructurales responsivos.
- **Iconografía**: Uso de Bootstrap Icons mediante clases semánticas (ej. `bi-person-check-fill`) para reforzar la experiencia visual.
- **JavaScript Nativo / Librerías Externas**: 
  - Integración de **Tom Select** para transformar etiquetas `<select>` tradicionales en selectores múltiples con buscador interactivo y diseño de *chips*.
  - Uso de JS para el control del DOM (apertura y cierre asíncrono de modales de detalles).
  - Intercepción de mensajes *Flash* provenientes de Symfony para disparar alertas estéticas al usuario.