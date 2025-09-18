# Sistema de Gestión de RRHH (SECM RRHH)

Este es un sistema web para la gestión de Recursos Humanos, desarrollado en PHP, con una base de datos MySQL y una interfaz de usuario construida con Bootstrap y jQuery.

## 1. Estructura del Proyecto

La estructura de carpetas principal es la siguiente:

- **/api**: Contiene todos los scripts PHP que actúan como endpoints de la API. Se encargan de la lógica de negocio y la comunicación con la base de datos.
- **/assets**: Almacena los recursos estáticos.
  - **/css**: Hojas de estilo (CSS).
  - **/js**: Scripts de JavaScript.
  - **/img**: Imágenes, como logos o fotos de perfil subidas.
- **/config**: Archivos de configuración, principalmente `db.php` para la conexión a la base de datos.
- **/partials**: Fragmentos de código PHP/HTML reutilizables, como la barra de navegación (`navbar.php`) o partes de formularios.
- **Archivos `.php` en la raíz**: Son las páginas visibles por el usuario (el "frontend"), como `dashboard.php`, `empleados.php`, etc.

---

## 2. Configuración de la Base de Datos

Estos son los pasos y el esquema necesarios para configurar la base de datos en un nuevo entorno (por ejemplo, en otra computadora o servidor).

### 2.1. Creación de la Base de Datos y Usuario

Primero, ejecuta los siguientes comandos en tu cliente de MySQL para crear la base de datos y el usuario con los permisos necesarios.

```sql
-- 1. Crear la base de datos
CREATE DATABASE secmrrhh CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Crear el usuario (ajusta 'localhost' si te conectas de forma remota)
CREATE USER 'secmrrhh'@'localhost' IDENTIFIED BY 'tu_contraseña_segura';

-- 3. Otorgar todos los permisos al usuario sobre la nueva base de datos
GRANT ALL PRIVILEGES ON secmrrhh.* TO 'secmrrhh'@'localhost';

-- 4. Aplicar los cambios
FLUSH PRIVILEGES;
```

### 2.2. Archivo de Configuración

Asegúrate de que el archivo `config/db.php` tenga las credenciales correctas:

```php
// c:\laragon\www\secmrrhh\config\db.php
$host = 'localhost';
$db   = 'secmrrhh'; // Nombre de la base de datos
$user = 'secmrrhh'; // Usuario de la base de datos
$pass = 'tu_contraseña_segura'; // Contraseña del usuario
// ...
```

### 2.3. Esquema de Tablas (SQL)

Una vez creada la base de datos, ejecuta el siguiente script SQL para crear todas las tablas necesarias.

```sql
USE secmrrhh;

-- Tabla de Usuarios del Sistema
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `rol` enum('admin','usuario') NOT NULL DEFAULT 'usuario',
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `ultimo_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Empresas
CREATE TABLE `empresas` (
  `id_emp` int NOT NULL AUTO_INCREMENT,
  `denominacion` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `cuit` varchar(20) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `art_proveedor` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `art_vigencia` date DEFAULT NULL,
  `art_coeficiente` decimal(5,3) DEFAULT NULL COMMENT 'Coeficiente de ART como porcentaje. Ej: 1.400 para 1.40%',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_emp`), UNIQUE KEY `cuit` (`cuit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Tabla de Sucursales
CREATE TABLE `sucursales` (
  `id_sucursal` int(11) NOT NULL AUTO_INCREMENT,
  `id_empresa` int(11) NOT NULL,
  `denominacion` varchar(255) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `localidad` varchar(100) DEFAULT NULL,
  `cod_postal` varchar(20) DEFAULT NULL,
  `telefonos` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_sucursal`),
  KEY `id_empresa` (`id_empresa`),
  CONSTRAINT `sucursales_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_emp`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tablas de Estructura Organizacional
CREATE TABLE `areas` (
  `id_area` int(11) NOT NULL AUTO_INCREMENT,
  `id_empresa` int(11) NOT NULL,
  `denominacion` varchar(100) NOT NULL,
  PRIMARY KEY (`id_area`),
  KEY `id_empresa` (`id_empresa`),
  CONSTRAINT `areas_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_emp`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `funciones` (
  `id_funcion` int(11) NOT NULL AUTO_INCREMENT,
  `denominacion` varchar(100) NOT NULL,
  `codigo_afip_actividad` varchar(10) DEFAULT NULL,
  `codigo_afip_puesto` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id_funcion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tablas de Ubicaciones
CREATE TABLE `paises` (
  `id_pais` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id_pais`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `provincias` (
  `id_provincia` int(11) NOT NULL AUTO_INCREMENT,
  `id_pais` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id_provincia`),
  KEY `id_pais` (`id_pais`),
  CONSTRAINT `provincias_ibfk_1` FOREIGN KEY (`id_pais`) REFERENCES `paises` (`id_pais`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tablas de Datos Laborales (Catálogos)
CREATE TABLE `bancos` (`id_banco` int(11) NOT NULL AUTO_INCREMENT, `nombre` varchar(100) NOT NULL, `cuit` varchar(20) DEFAULT NULL, `direccion` varchar(255) DEFAULT NULL, `telefono` varchar(50) DEFAULT NULL, `email` varchar(100) DEFAULT NULL, `codigo_sucursal` varchar(50) DEFAULT NULL, `codigo_bcra` varchar(50) DEFAULT NULL, `responsable_contacto` varchar(100) DEFAULT NULL, `activo` tinyint(1) NOT NULL DEFAULT 1, PRIMARY KEY (`id_banco`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `convenios` (`id_convenio` int(11) NOT NULL AUTO_INCREMENT, `nombre` varchar(255) NOT NULL, `abreviatura` varchar(50) DEFAULT NULL, PRIMARY KEY (`id_convenio`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `obras_sociales` (`id_obra_social` int(11) NOT NULL AUTO_INCREMENT, `nombre` varchar(255) NOT NULL, `abreviatura` varchar(50) DEFAULT NULL, PRIMARY KEY (`id_obra_social`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `art` (`id_art` int(11) NOT NULL AUTO_INCREMENT, `nombre` varchar(255) NOT NULL, `cuit` varchar(20) DEFAULT NULL, `direccion` varchar(255) DEFAULT NULL, `telefono` varchar(50) DEFAULT NULL, `email` varchar(100) DEFAULT NULL, `responsable_contacto` varchar(100) DEFAULT NULL, `nro_poliza` varchar(100) DEFAULT NULL, `vigencia_desde` date DEFAULT NULL, `vigencia_hasta` date DEFAULT NULL, `activo` tinyint(1) NOT NULL DEFAULT 1, PRIMARY KEY (`id_art`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `sindicatos` (`id_sindicato` int(11) NOT NULL AUTO_INCREMENT, `nombre` varchar(255) NOT NULL, `cuit` varchar(20) DEFAULT NULL, `direccion` varchar(255) DEFAULT NULL, `telefono` varchar(50) DEFAULT NULL, `email` varchar(100) DEFAULT NULL, `nro_inscripcion_mtess` varchar(100) DEFAULT NULL, `responsable_contacto` varchar(100) DEFAULT NULL, `id_obra_social` int(11) DEFAULT NULL, `activo` tinyint(1) NOT NULL DEFAULT 1, PRIMARY KEY (`id_sindicato`), KEY `id_obra_social` (`id_obra_social`), CONSTRAINT `sindicatos_ibfk_1` FOREIGN KEY (`id_obra_social`) REFERENCES `obras_sociales` (`id_obra_social`) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Modalidades de Contrato
CREATE TABLE `modalidades_contrato` (
  `id_modalidad` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_modalidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla Principal de Empleados
CREATE TABLE `personal` (
  `id_personal` int(11) NOT NULL AUTO_INCREMENT,
  `legajo` int(11) NOT NULL,
  `apellido` varchar(75) NOT NULL,
  `nombre` varchar(75) NOT NULL,
  `documento` varchar(20) NOT NULL,
  `cuil` varchar(20) NOT NULL,
  `nacimiento` date DEFAULT NULL,
  `sexo` char(1) DEFAULT NULL,
  `estado_civil` varchar(20) DEFAULT NULL,
  `nacionalidad` varchar(50) DEFAULT NULL,
  `telefono_celular` varchar(50) DEFAULT NULL,
  `telefono_fijo` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `id_pais` int(11) DEFAULT NULL,
  `id_provincia` int(11) DEFAULT NULL,
  `localidad` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `domicilio_real` varchar(255) DEFAULT NULL,
  `ingreso` date DEFAULT NULL,
  `antiguedad` int(11) DEFAULT NULL,
  `id_sucursal` int(11) DEFAULT NULL,
  `id_area` int(11) DEFAULT NULL,
  `id_funcion` int(11) DEFAULT NULL,
  `id_convenio` int(11) DEFAULT NULL,
  `id_modalidad_contrato` int(11) DEFAULT NULL,
  `jornada` varchar(50) DEFAULT NULL,
  `id_obra_social` int(11) DEFAULT NULL,
  `id_art` int(11) DEFAULT NULL,
  `id_sindicato` int(11) DEFAULT NULL,
  `cuit_empresa` varchar(20) DEFAULT NULL,
  `id_banco` int(11) DEFAULT NULL,
  `cbu_o_alias` varchar(50) DEFAULT NULL,
  `forma_pago` varchar(50) DEFAULT NULL,
  `sueldo_basico` decimal(10,2) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `foto_path` varchar(255) DEFAULT NULL,
  `redes_sociales` json DEFAULT NULL,
  `edad` int(11) DEFAULT NULL,
  `id_puesto` int(11) DEFAULT 0,
  PRIMARY KEY (`id_personal`),
  UNIQUE KEY `legajo` (`legajo`),
  UNIQUE KEY `cuil` (`cuil`),
  KEY `id_sucursal` (`id_sucursal`),
  CONSTRAINT `personal_ibfk_1` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE SET NULL,
  CONSTRAINT `personal_ibfk_2` FOREIGN KEY (`id_modalidad_contrato`) REFERENCES `modalidades_contrato` (`id_modalidad`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tablas de Documentación
CREATE TABLE `documento_tipos` (
  `id_tipo_documento` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_tipo_documento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `documentacion` (
  `id_documento` int(11) NOT NULL AUTO_INCREMENT,
  `id_personal` int(11) NOT NULL,
  `id_tipo_documento` int(11) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `nombre_archivo_original` varchar(255) NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_documento`),
  KEY `id_personal` (`id_personal`),
  KEY `id_tipo_documento` (`id_tipo_documento`),
  CONSTRAINT `documentacion_ibfk_1` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`) ON DELETE CASCADE,
  CONSTRAINT `documentacion_ibfk_2` FOREIGN KEY (`id_tipo_documento`) REFERENCES `documento_tipos` (`id_tipo_documento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tablas de Ausencias y Novedades
CREATE TABLE `novedades` (
  `id_novedad` int(11) NOT NULL AUTO_INCREMENT,
  `id_personal` int(11) NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_solicitud` date NOT NULL,
  `fecha_desde` date NOT NULL,
  `fecha_hasta` date NOT NULL,
  `id_documento` int(11) DEFAULT NULL,
  `estado` enum('Aprobada','Pendiente','Rechazada') NOT NULL,
  `registrado_por` int(11) NOT NULL,
  PRIMARY KEY (`id_novedad`),
  KEY `id_personal` (`id_personal`),
  CONSTRAINT `novedades_ibfk_1` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ausencias` (
  `id_ausencia` int(11) NOT NULL AUTO_INCREMENT,
  `id_novedad` int(11) NOT NULL,
  `id_personal` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tipo_dia` varchar(50) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_ausencia`),
  UNIQUE KEY `idx_personal_fecha` (`id_personal`,`fecha`),
  KEY `id_novedad` (`id_novedad`),
  CONSTRAINT `ausencias_ibfk_1` FOREIGN KEY (`id_novedad`) REFERENCES `novedades` (`id_novedad`) ON DELETE CASCADE,
  CONSTRAINT `ausencias_ibfk_2` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Auditoría
CREATE TABLE `auditoria` (
  `id_auditoria` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `tabla_afectada` varchar(50) NOT NULL,
  `id_registro` int(11) DEFAULT NULL,
  `detalles` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_auditoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Notificaciones
CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario_destino` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_notificacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar usuario administrador por defecto
INSERT INTO `usuarios` (`username`, `password`, `nombre_completo`, `rol`, `estado`) VALUES
('admin', '$2y$10$g/y.lP9.v2Z8DRa3kGkQe.0xY.Z/w.Z/x.Z/w.Z/w.Z/w.Z/w.Z/w', 'Administrador del Sistema', 'admin', 'activo');
-- La contraseña para el usuario 'admin' es 'admin'. ¡Cámbiala inmediatamente!

```

---

## 3. Comentarios en el Código

Para mejorar la mantenibilidad, se ha comenzado a añadir comentarios a los archivos. El objetivo es explicar la lógica compleja y el propósito de cada bloque de código.

**Estándar de Comentarios:**

- **Comentarios de una línea (`//`):** Para explicaciones breves y rápidas sobre una línea específica.
  ```php
  // Validar que el ID sea numérico
  if (!is_numeric($id)) { ... }
  ```
- **Bloques de Comentarios (`/* ... */`):** Para explicaciones más largas que abarcan varias líneas.
- **PHPDoc (`/** ... */`):** Para documentar funciones, sus parámetros y lo que retornan. Esto es especialmente útil en los archivos de la API.
  ```php
  /**
   * Maneja la subida de la foto de perfil.
   * @return string|null La ruta del archivo o null si falla.
   */
  function handle_photo_upload() {
      // ...
  }
  ```

Se continuará aplicando este estándar al resto de los archivos del proyecto para facilitar su comprensión.