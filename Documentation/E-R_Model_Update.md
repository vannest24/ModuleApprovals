# Registro de Modificaciones al Modelo Entidad-Relación

A continuación se detallan los cambios y reestructuraciones aplicadas al esquema de la base de datos para dar soporte al sistema de flujos de aprobación asíncronos:

1. 🗃️ Incorporación de Nuevas Entidades (Tablas)
* Entidad `Area`:** Se añade la tabla para segmentar la estructura organizacional de los usuarios y aprobadores. Incluye los atributos `ID` (Primary Key) y `nombre_area`.
* Entidad `Documento`:** Se crea la tabla para centralizar la metadata de los archivos cargados. Cuenta con los atributos `ID` (Primary Key), `Nombre_documento` e `ID_area` como llave foránea.

2. Definición y Actualización de Relaciones (Mapping)
* Relación `Usuario` ↔ `Area` (Many-to-One):** Se implementó una relación en la tabla `Usuario` apuntando hacia `Area` mediante la llave foránea `ID_area (FK)`. 
  *(Nota técnica: En el diagrama se visualiza una relación de muchos usuarios pertenecientes a una misma área).*
* Relación `Aprobaciones` ↔ `Area` (Many-to-One):** Se añade el campo `ID_area (FK)` en la entidad `Aprobaciones` para asociar directamente el dictamen técnico con el departamento correspondiente.

3. Extensión de Atributos (Campos Nuevos)
* Tabla `Solicitudes`: Se añade la columna `Razon_cambio` de tipo alfanumérico para almacenar la justificación obligatoria del Document Change Request (DCR).

4. Actualización de Capa de Entrada de Datos (Controladores)
* Sincronización de Formularios: Se actualizó `OriginHomeController` para procesar y almacenar el campo `Razon_cambio` enviado mediante POST.
* Gestión de Archivos Nativos: Se eliminó la dependencia del enlace externo `link_sharepoint`. Se implementó el manejo mediante la clase `UploadedFile` de Symfony para guardar los documentos de manera nativa en el servidor (directorio local del proyecto).
* Validación de Estados:** Se verificaron las reglas de negocio en `ApprovalHomeController` para garantizar que la transición de los estatus (PENDIENTE, APROBADA, RECHAZADA) reflejen fielmente el nuevo flujo asíncrono.